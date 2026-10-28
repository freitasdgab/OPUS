<?php
/**
 * OPUS - Lógica e Motor de IA do Chatbot Opi
 * Suporte a Groq Cloud (LLaMA 3.3 70B & 3.1 8B), Consultas Acadêmicas em Tempo Real e Base de Conhecimento Local
 */

require_once __DIR__ . '/../config/config_ai.php';
require_once __DIR__ . '/ligas_logic.php';

/**
 * Consulta o banco de dados em tempo real e monta o diagnóstico acadêmico
 * de progresso e prontidão para o mercado de trabalho do aluno.
 */
function opus_obter_relatorio_progresso_completo(mysqli $conn, int $userId): array {
    $catalogo_capitulos = [
        1 => [
            'nome' => 'Fundamentos do Java', 
            'tema' => 'Sintaxe básica, variáveis, tipos primitivos, Scanner e casting',
            'fase' => 'Iniciante (Bases da Linguagem)'
        ],
        2 => [
            'nome' => 'Caminhos Lógicos', 
            'tema' => 'Estruturas de decisão, if/else, switch-case e operadores lógicos',
            'fase' => 'Iniciante (Lógica de Decisão)'
        ],
        3 => [
            'nome' => 'Mestre da Repetição', 
            'tema' => 'Laços for, while, do-while, controles de fluxo e laços aninhados',
            'fase' => 'Iniciante Avançado (Lógica Algorítmica)'
        ],
        4 => [
            'nome' => 'Senhor dos Arrays', 
            'tema' => 'Vetores, matrizes, enhanced for (for-each) e coleções dinâmicas (ArrayList)',
            'fase' => 'Intermediário (Estruturas de Dados e Coleções)'
        ],
        5 => [
            'nome' => 'Arquiteto Java (POO)', 
            'tema' => 'Orientação a Objetos: Classes, Objetos, Construtores, Encapsulamento, Herança, Polimorfismo e Classes Abstratas',
            'fase' => 'Avançado / Java Core Completo'
        ]
    ];

    // Busca dados cadastrais e gamificação
    $xp = 0; $vidas = 3; $dias_fogo = 0; $trofeus = 0;
    if ($stmt = $conn->prepare("SELECT xp, vidas, dias_fogo, trofeus FROM usuarios WHERE id = ?")) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $uData = $stmt->get_result()->fetch_assoc();
        if ($uData) {
            $xp = (int) ($uData['xp'] ?? 0);
            $vidas = (int) ($uData['vidas'] ?? 3);
            $dias_fogo = (int) ($uData['dias_fogo'] ?? 0);
            $trofeus = (int) ($uData['trofeus'] ?? 0);
        }
        $stmt->close();
    }

    // Busca progresso em cada capítulo (unidade)
    $unidades_prog = [];
    if ($stmt_prog = $conn->prepare("SELECT unidade_numero, status, licoes_concluidas FROM progresso_usuario WHERE usuario_id = ? ORDER BY unidade_numero ASC")) {
        $stmt_prog->bind_param("i", $userId);
        $stmt_prog->execute();
        $res_prog = $stmt_prog->get_result();
        while ($r = $res_prog->fetch_assoc()) {
            $unidades_prog[(int)$r['unidade_numero']] = $r;
        }
        $stmt_prog->close();
    }

    $total_sessoes = 40;
    $total_licoes_curso = 200;
    $sessoes_concluidas = 0;
    $total_licoes_feitas = 0;
    $sessao_atual_num = 1;
    $licoes_feitas_sessao_atual = 0;
    $encontrou_corrente = false;

    for ($i = 1; $i <= $total_sessoes; $i++) {
        $u = $unidades_prog[$i] ?? ['status' => ($i === 1 ? 'corrente' : 'trancado'), 'licoes_concluidas' => 0];
        $st = $u['status'] ?? 'trancado';
        $lf = min(5, (int) ($u['licoes_concluidas'] ?? 0));

        if ($st === 'completo' || $lf >= 5) {
            $sessoes_concluidas++;
            $total_licoes_feitas += 5;
        } else {
            $total_licoes_feitas += $lf;
            if (!$encontrou_corrente) {
                $sessao_atual_num = $i;
                $licoes_feitas_sessao_atual = $lf;
                $encontrou_corrente = true;
            }
        }
    }

    if ($sessoes_concluidas >= $total_sessoes) {
        $sessao_atual_num = 40;
        $licoes_feitas_sessao_atual = 5;
        $total_licoes_feitas = 200;
    }

    // Regra explícita: Se o aluno ainda não concluiu a primeira sessão (ex: está na Sessão 1 com 0 ou 1 lição),
    // faltam 40 sessões para terminar o curso porque ele nem terminou a primeira!
    if ($sessoes_concluidas === 0) {
        $sessoes_restantes = 40;
    } else {
        $sessoes_restantes = max(0, $total_sessoes - $sessoes_concluidas);
    }

    $licoes_restantes_total = max(0, $total_licoes_curso - $total_licoes_feitas);
    $licoes_restantes_sessao_atual = max(0, 5 - $licoes_feitas_sessao_atual);
    $porcentagem = min(100, round(($total_licoes_feitas / $total_licoes_curso) * 100, 1));
    $porcentagem_sessao_atual = min(100, (int) round(($licoes_feitas_sessao_atual / 5) * 100));

    // Nome e tema da sessão atual
    $ementaAtual = opus_obter_ementa_sessao($sessao_atual_num);
    $nome_sessao_atual = $ementaAtual['nome'] ?? "Sessão $sessao_atual_num";
    $tema_sessao_atual = $ementaAtual['texto'] ?? "";

    // Nível técnico e análise de mercado
    if ($sessoes_concluidas >= 27) {
        $nivel_java = "Avançado / POO Completa (Nível Júnior / Estágio Pronto)";
        $apto_emprego = true;
        $resumo_mercado = "O aluno já avançou nos módulos de Programação Orientada a Objetos (Classes, Herança, Polimorfismo e Coleções). Já possui a base técnica exigida para disputar vagas de Estágio em Desenvolvimento Java ou Desenvolvedor Júnior.";
    } elseif ($sessoes_concluidas >= 10) {
        $nivel_java = "Intermediário (Estruturas de Dados, Arrays e Coleções)";
        $apto_emprego = false;
        $resumo_mercado = "O aluno domina controle de fluxo, loops, métodos e coleções. Falta avançar para a trilha de POO (a partir da Seção 27) para estar 100% elegível para entrevistas técnicas.";
    } elseif ($sessoes_concluidas >= 5) {
        $nivel_java = "Iniciante Consistente (Lógica Estruturada e Algoritmos)";
        $apto_emprego = false;
        $resumo_mercado = "O aluno já domina raciocínio lógico, condições e laços de repetição.";
    } else {
        $nivel_java = "Iniciante / Fundamentos Iniciais do Java";
        $apto_emprego = false;
        $resumo_mercado = "O aluno está construindo os alicerces da programação (sintaxe, variáveis e decisões).";
    }

    // Consulta atividade diária de hoje (missoes_diarias_usuario e acertos_diarios)
    $licoes_hoje = 0;
    $acertos_hoje = 0;
    $xp_hoje = 0;
    $estudou_hoje = false;

    if ($stmt_missao = $conn->prepare("SELECT licoes_concluidas, xp_ganho FROM missoes_diarias_usuario WHERE usuario_id = ? AND data_ref = CURDATE()")) {
        $stmt_missao->bind_param("i", $userId);
        $stmt_missao->execute();
        $row_missao = $stmt_missao->get_result()->fetch_assoc();
        if ($row_missao) {
            $licoes_hoje = (int) ($row_missao['licoes_concluidas'] ?? 0);
            $xp_hoje = (int) ($row_missao['xp_ganho'] ?? 0);
        }
        $stmt_missao->close();
    }

    if ($stmt_acertos = $conn->prepare("SELECT acertos FROM acertos_diarios WHERE usuario_id = ? AND data_dia = CURDATE()")) {
        $stmt_acertos->bind_param("i", $userId);
        $stmt_acertos->execute();
        $row_acertos = $stmt_acertos->get_result()->fetch_assoc();
        if ($row_acertos) {
            $acertos_hoje = (int) ($row_acertos['acertos'] ?? 0);
            if ($licoes_hoje === 0 && $acertos_hoje > 0) {
                $licoes_hoje = (int) ceil($acertos_hoje / 3);
            }
        }
        $stmt_acertos->close();
    }

    if ($stmt_user = $conn->prepare("SELECT ultima_atividade FROM usuarios WHERE id = ?")) {
        $stmt_user->bind_param("i", $userId);
        $stmt_user->execute();
        $row_u = $stmt_user->get_result()->fetch_assoc();
        if ($row_u && !empty($row_u['ultima_atividade'])) {
            $estudou_hoje = ($row_u['ultima_atividade'] === date('Y-m-d'));
        }
        $stmt_user->close();
    }

    if ($estudou_hoje && $licoes_hoje === 0) {
        $licoes_hoje = 1;
    }

    // CONSULTA EM TEMPO REAL DAS LIGAS
    $liga_divisao = 'bronze';
    $liga_posicao = 1;
    $liga_total_grupo = 1;
    $liga_xp_semana = 0;
    $liga_zona = 'neutro';
    $liga_xp_necessario_subir = 0;
    $liga_xp_corte_subida = 0;
    $liga_pode_subir = true;
    $liga_top5 = [];

    try {
        $dados_liga = liga_garantir_usuario($conn, $userId);
        $liga_divisao = $dados_liga['divisao'] ?? 'bronze';
        $liga_xp_semana = (int) ($dados_liga['xp_semana'] ?? 0);
        $cfg_divisao = liga_config($liga_divisao);
        $liga_pode_subir = (bool) ($cfg_divisao['sobe'] ?? true);

        if (!empty($dados_liga['grupo_id'])) {
            $stmt_membros = $conn->prepare("
                SELECT lu.usuario_id, lu.xp_semana, u.nome 
                FROM ligas_usuario lu
                LEFT JOIN usuarios u ON u.id = lu.usuario_id
                WHERE lu.grupo_id = ? 
                ORDER BY lu.xp_semana DESC, lu.usuario_id ASC
            ");
            $stmt_membros->bind_param("i", $dados_liga['grupo_id']);
            $stmt_membros->execute();
            $membros = $stmt_membros->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_membros->close();

            $liga_total_grupo = count($membros);
            if ($liga_total_grupo > 0) {
                $zonas = liga_calcular_zonas($liga_total_grupo);
                $linha_subida = $zonas['sobe']; // Top 12 (ou total de membros)

                foreach ($membros as $idx => $m) {
                    if ($idx < 5) {
                        $liga_top5[] = [
                            'posicao' => $idx + 1,
                            'nome' => $m['nome'] ?? ('Jogador #' . $m['usuario_id']),
                            'xp_semana' => (int) ($m['xp_semana'] ?? 0)
                        ];
                    }
                    if ((int) $m['usuario_id'] === $userId) {
                        $liga_posicao = $idx + 1;
                    }
                }

                if ($liga_posicao <= $linha_subida && $liga_pode_subir) {
                    $liga_zona = 'sobe';
                } elseif ($liga_posicao > ($liga_total_grupo - $zonas['desce']) && ($cfg_divisao['desce'] ?? false)) {
                    $liga_zona = 'desce';
                } else {
                    $liga_zona = 'neutro';
                }

                $idx_corte = min($linha_subida - 1, $liga_total_grupo - 1);
                $xp_corte = (int) ($membros[$idx_corte]['xp_semana'] ?? 0);
                $liga_xp_corte_subida = $xp_corte;

                if (!$liga_pode_subir || $liga_posicao <= $linha_subida) {
                    $liga_xp_necessario_subir = 0;
                } else {
                    $diff = ($xp_corte - $liga_xp_semana);
                    $liga_xp_necessario_subir = max(10, $diff + 10);
                }
            }
        }
    } catch (\Throwable $e) {
        // Fallback gracioso
    }

    // CONSULTA DE MISSÕES DIÁRIAS (/goal)
    $perfeitas_hoje = 0;
    if ($stmt_mp = $conn->prepare("SELECT licoes_perfeitas FROM missoes_diarias_usuario WHERE usuario_id = ? AND data_ref = CURDATE()")) {
        $stmt_mp->bind_param("i", $userId);
        $stmt_mp->execute();
        $row_mp = $stmt_mp->get_result()->fetch_assoc();
        if ($row_mp) {
            $perfeitas_hoje = (int) ($row_mp['licoes_perfeitas'] ?? 0);
        }
        $stmt_mp->close();
    }

    $missoes_diarias = [
        [
            'id' => 'xp_50',
            'titulo' => 'Ganhe 50 de XP hoje',
            'atual' => $xp_hoje,
            'meta' => 50,
            'completo' => ($xp_hoje >= 50),
            'porcentagem' => min(100, (int) round(($xp_hoje / 50) * 100))
        ],
        [
            'id' => 'licoes_2',
            'titulo' => 'Conclua 2 lições hoje',
            'atual' => $licoes_hoje,
            'meta' => 2,
            'completo' => ($licoes_hoje >= 2),
            'porcentagem' => min(100, (int) round(($licoes_hoje / 2) * 100))
        ],
        [
            'id' => 'perfeita_1',
            'titulo' => 'Acerte 1 lição com 100%',
            'atual' => $perfeitas_hoje,
            'meta' => 1,
            'completo' => ($perfeitas_hoje >= 1),
            'porcentagem' => min(100, (int) round(($perfeitas_hoje / 1) * 100))
        ]
    ];
    $missoes_concluidas_hoje = 0;
    foreach ($missoes_diarias as $md) {
        if ($md['completo']) $missoes_concluidas_hoje++;
    }

    // CONSULTA DE RANKING GLOBAL
    $ranking_global_posicao = 1;
    $ranking_global_total = 1;
    $ranking_top5 = [];
    try {
        $res_rk = $conn->query("SELECT id, nome, xp, trofeus, dias_fogo FROM usuarios ORDER BY xp DESC, id ASC");
        if ($res_rk) {
            $todos_rk = $res_rk->fetch_all(MYSQLI_ASSOC);
            $ranking_global_total = count($todos_rk);
            foreach ($todos_rk as $idx => $uRk) {
                if ($idx < 5) {
                    $ranking_top5[] = [
                        'posicao' => $idx + 1,
                        'nome' => $uRk['nome'],
                        'xp' => (int) $uRk['xp'],
                        'trofeus' => (int) $uRk['trofeus'],
                        'dias_fogo' => (int) $uRk['dias_fogo']
                    ];
                }
                if ((int) $uRk['id'] === $userId) {
                    $ranking_global_posicao = $idx + 1;
                }
            }
        }
    } catch (\Throwable $e) {}

    // CONSULTA DE TROFÉUS E CONQUISTAS
    $trofeus_desbloqueados = [];
    $trofeus_pendentes = [];
    $proximo_trofeu = null;
    try {
        $slugs_user = [];
        if ($stmt_ut = $conn->prepare("SELECT trofeu_slug AS slug FROM user_trofeus WHERE user_id = ? UNION SELECT conquista_slug AS slug FROM conquistas_usuario WHERE usuario_id = ?")) {
            $stmt_ut->bind_param("ii", $userId, $userId);
            $stmt_ut->execute();
            $res_ut = $stmt_ut->get_result();
            while ($rUt = $res_ut->fetch_assoc()) {
                $slugs_user[$rUt['slug']] = true;
            }
            $stmt_ut->close();
        }
        $res_cat = $conn->query("SELECT slug, nome, descricao, raridade FROM catalogo_trofeus ORDER BY ordem ASC");
        if ($res_cat) {
            while ($cRow = $res_cat->fetch_assoc()) {
                if (isset($slugs_user[$cRow['slug']])) {
                    $trofeus_desbloqueados[] = $cRow;
                } else {
                    $trofeus_pendentes[] = $cRow;
                    if ($proximo_trofeu === null) {
                        $proximo_trofeu = $cRow;
                    }
                }
            }
        }
    } catch (\Throwable $e) {}

    // CONSULTA DE BAÚS DE RECOMPENSA
    $baus_resgatados = [];
    $baus_disponiveis = [];
    try {
        if ($stmt_bau = $conn->prepare("SELECT unidade_numero FROM bau_recompensas WHERE usuario_id = ?")) {
            $stmt_bau->bind_param("i", $userId);
            $stmt_bau->execute();
            $res_bau = $stmt_bau->get_result();
            while ($bRow = $res_bau->fetch_assoc()) {
                $baus_resgatados[(int)$bRow['unidade_numero']] = true;
            }
            $stmt_bau->close();
        }
        foreach ($unidades_prog as $unidNum => $uProg) {
            $lfU = (int) ($uProg['licoes_concluidas'] ?? 0);
            $stU = $uProg['status'] ?? 'trancado';
            if (($lfU >= 3 || $stU === 'completo') && !isset($baus_resgatados[$unidNum])) {
                $baus_disponiveis[] = (int) $unidNum;
            }
        }
    } catch (\Throwable $e) {}

    // CONSULTA DE SEGUIDORES E AMIGOS
    $seguidores_qtd = 0;
    $seguindo_qtd = 0;
    try {
        if ($stmt_seg = $conn->prepare("SELECT (SELECT COUNT(*) FROM seguidores WHERE seguido_id = ?) AS seguidores, (SELECT COUNT(*) FROM seguidores WHERE seguidor_id = ?) AS seguindo")) {
            $stmt_seg->bind_param("ii", $userId, $userId);
            $stmt_seg->execute();
            $rSeg = $stmt_seg->get_result()->fetch_assoc();
            if ($rSeg) {
                $seguidores_qtd = (int) ($rSeg['seguidores'] ?? 0);
                $seguindo_qtd = (int) ($rSeg['seguindo'] ?? 0);
            }
            $stmt_seg->close();
        }
    } catch (\Throwable $e) {}

    // CONSULTA DA LIÇÃO E DESAFIO ATUAL NO BANCO (/boost)
    $licao_atual_num = min(5, $licoes_feitas_sessao_atual + 1);
    $licao_atual_db = null;
    $desafio_atual_db = null;
    try {
        if ($stmt_l = $conn->prepare("SELECT titulo, texto_explicativo, codigo_exemplo FROM licoes WHERE unidade_numero = ? AND licao_numero = ? LIMIT 1")) {
            $stmt_l->bind_param("ii", $sessao_atual_num, $licao_atual_num);
            $stmt_l->execute();
            $licao_atual_db = $stmt_l->get_result()->fetch_assoc() ?: null;
            $stmt_l->close();
        }
        if ($stmt_d = $conn->prepare("SELECT badge, heading, intro, missao, alvo_visual, dica_texto, dica_codigo, arquivo, codigo_inicial FROM desafios WHERE unidade_numero = ? LIMIT 1")) {
            $stmt_d->bind_param("i", $sessao_atual_num);
            $stmt_d->execute();
            $desafio_atual_db = $stmt_d->get_result()->fetch_assoc() ?: null;
            $stmt_d->close();
        }
    } catch (\Throwable $e) {}

    return [
        'xp' => $xp,
        'vidas' => $vidas,
        'dias_fogo' => $dias_fogo,
        'trofeus' => $trofeus,
        'total_capitulos' => $total_sessoes,
        'total_sessoes' => $total_sessoes,
        'capitulos_concluidos' => $sessoes_concluidas,
        'sessoes_concluidas' => $sessoes_concluidas,
        'capitulos_restantes' => $sessoes_restantes,
        'sessoes_restantes' => $sessoes_restantes,
        'capitulo_atual_num' => $sessao_atual_num,
        'sessao_atual_num' => $sessao_atual_num,
        'capitulo_atual_nome' => $nome_sessao_atual,
        'sessao_atual_nome' => $nome_sessao_atual,
        'capitulo_atual_tema' => $tema_sessao_atual,
        'sessao_atual_tema' => $tema_sessao_atual,
        'licoes_feitas_cap_atual' => $licoes_feitas_sessao_atual,
        'licoes_feitas_sessao_atual' => $licoes_feitas_sessao_atual,
        'licoes_restantes_cap_atual' => $licoes_restantes_sessao_atual,
        'licoes_restantes_sessao_atual' => $licoes_restantes_sessao_atual,
        'licao_atual_num' => $licao_atual_num,
        'licao_atual_db' => $licao_atual_db,
        'desafio_atual_db' => $desafio_atual_db,
        'total_licoes_curso' => $total_licoes_curso,
        'total_licoes_feitas' => $total_licoes_feitas,
        'licoes_restantes_total' => $licoes_restantes_total,
        'porcentagem_concluida' => $porcentagem,
        'porcentagem_sessao_atual' => $porcentagem_sessao_atual,
        'nivel_java' => $nivel_java,
        'apto_emprego' => $apto_emprego,
        'resumo_mercado' => $resumo_mercado,
        'licoes_hoje' => $licoes_hoje,
        'acertos_hoje' => $acertos_hoje,
        'xp_hoje' => $xp_hoje,
        'perfeitas_hoje' => $perfeitas_hoje,
        'estudou_hoje' => $estudou_hoje,
        'missoes_diarias' => $missoes_diarias,
        'missoes_concluidas_hoje' => $missoes_concluidas_hoje,
        'liga_divisao' => $liga_divisao,
        'liga_posicao' => $liga_posicao,
        'liga_total_grupo' => $liga_total_grupo,
        'liga_xp_semana' => $liga_xp_semana,
        'liga_zona' => $liga_zona,
        'liga_xp_necessario_subir' => $liga_xp_necessario_subir,
        'liga_xp_corte_subida' => $liga_xp_corte_subida,
        'liga_pode_subir' => $liga_pode_subir,
        'liga_top5' => $liga_top5,
        'ranking_global_posicao' => $ranking_global_posicao,
        'ranking_global_total' => $ranking_global_total,
        'ranking_top5' => $ranking_top5,
        'trofeus_desbloqueados' => $trofeus_desbloqueados,
        'trofeus_pendentes' => $trofeus_pendentes,
        'proximo_trofeu' => $proximo_trofeu,
        'baus_resgatados_qtd' => count($baus_resgatados),
        'baus_disponiveis' => $baus_disponiveis,
        'seguidores_qtd' => $seguidores_qtd,
        'seguindo_qtd' => $seguindo_qtd
    ];
}

/**
 * Extrai dados da Infobox, parágrafos introdutórios e tabelas estruturadas (wikitable) do HTML da Wikipédia.
 * Prioriza dados oficiais de campeões, vencedores, titulares de cargos, resultados, finalistas e temporadas.
 */
function opus_extrair_intro_e_tabelas_wiki(string $html): array {
    $limpoHtml = preg_replace('/<(style|script)[^>]*>.*?<\/\1>/si', '', $html);
    $limpoHtml = preg_replace('/<sup[^>]*class="[^"]*reference[^"]*"[^>]*>.*?<\/sup>/si', '', $limpoHtml);

    // 0. Extrai dados-chave da Infobox (<table class="infobox ...">) antes de removê-la
    $linhasInfobox = [];
    if (preg_match('/<table[^>]*class="[^"]*infobox[^"]*"[^>]*>(.*?)<\/table>/si', $limpoHtml, $mInfo)) {
        $infoHtml = preg_replace('/<br\s*\/?>/i', ' ', $mInfo[1]);
        if (preg_match_all('/<tr[^>]*>(.*?)<\/tr>/si', $infoHtml, $mTr)) {
            foreach ($mTr[1] as $trHtml) {
                if (preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/si', $trHtml, $mTd)) {
                    $cells = [];
                    foreach ($mTd[1] as $tdH) {
                        $txtC = html_entity_decode(strip_tags($tdH), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                        $txtC = trim(preg_replace('/\[[^\]]*\]/', '', $txtC));
                        $txtC = trim(preg_replace('/\s+/u', ' ', $txtC));
                        if ($txtC !== '') {
                            $cells[] = $txtC;
                        }
                    }
                    if (count($cells) >= 2) {
                        $k = $cells[0];
                        $v = implode(' | ', array_slice($cells, 1, 2));
                        if (mb_strlen($k, 'UTF-8') <= 45 && mb_strlen($v, 'UTF-8') <= 140 &&
                            preg_match('/(Campe[ãa]o|Vice|Vencedor|Ganhador|Colocado|Melhor|Incumbente|No cargo|Presidente|Primeiro-ministro|Monarca|Capital|Popula[çc][ãa]o|Moeda|Fundador|Criador|Autor|Diretor|Lançamento|Pr[êe]mio|Apresentador|Transmiss[ãa]o)/iu', $k)) {
                            $linhasInfobox[] = "{$k}: {$v}";
                        }
                    } elseif (count($cells) === 1 && preg_match('/^(No cargo|Incumbente|Campe[ãa]o|Vencedor)\b/iu', $cells[0])) {
                        $linhasInfobox[] = $cells[0];
                    }
                }
                if (count($linhasInfobox) >= 8) break;
            }
        }
    }

    // 1. Extrai parágrafos introdutórios (antes do primeiro <h2>)
    $partesH2 = preg_split('/<h2\b|<div[^>]*class="[^"]*mw-heading2/si', $limpoHtml, 2);
    $introHtml = $partesH2[0] ?? $limpoHtml;
    $introSemTabelas = preg_replace('/<table[^>]*>.*?<\/table>/si', '', $introHtml);
    $paragrafos = [];
    if (!empty($linhasInfobox)) {
        $paragrafos[] = "Ficha Oficial: " . implode(" | ", $linhasInfobox) . ".";
    }
    if (preg_match_all('/<p\b[^>]*>(.*?)<\/p>/si', $introSemTabelas, $mP)) {
        foreach ($mP[1] as $pHtml) {
            $txtP = html_entity_decode(strip_tags($pHtml), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $txtP = trim(preg_replace('/\[[^\]]*\]/', '', $txtP));
            $txtP = trim(preg_replace('/\s+/u', ' ', $txtP));
            if (mb_strlen($txtP, 'UTF-8') >= 40) {
                $paragrafos[] = $txtP;
            }
            if (count($paragrafos) >= 5) break;
        }
    }
    $extrato = implode("\n\n", $paragrafos);
    if (mb_strlen($extrato, 'UTF-8') > 1350) {
        $extrato = mb_substr($extrato, 0, 1350, 'UTF-8') . '...';
    }

    // 2. Extrai tabelas wikitable validando cabeçalhos reais <th> (evita falsos positivos de tabelas de pré-seleção/classificação)
    $prioritarias = [];
    $participantesTemporada = [];
    if (preg_match_all('/<table[^>]*class="[^"]*wikitable[^"]*"[^>]*>(.*?)<\/table>/si', $limpoHtml, $matches)) {
        foreach ($matches[1] as $tableHtml) {
            if (!preg_match_all('/<tr[^>]*>(.*?)<\/tr>/si', $tableHtml, $rowMatches)) continue;

            // Extrai exclusivamente células <th> das 2 primeiras linhas para identificar o cabeçalho real da tabela
            $thTextos = [];
            foreach (array_slice($rowMatches[1], 0, 2) as $rHeaderHtml) {
                if (preg_match_all('/<th[^>]*>(.*?)<\/th>/si', $rHeaderHtml, $mTh)) {
                    foreach ($mTh[1] as $thCell) {
                        $thLimpo = preg_replace('/<(small|sup|style|script)[^>]*>.*?<\/\1>/si', '', $thCell);
                        $thLimpo = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($thLimpo), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                        $thLimpo = trim(preg_replace('/\[[^\]]*\]/', '', $thLimpo));
                        if ($thLimpo !== '') {
                            $thTextos[] = $thLimpo;
                        }
                    }
                }
            }
            $cabecalhoTh = implode(' | ', $thTextos);
            if ($cabecalhoTh === '') continue;

            // Descarta tabelas secundárias de dinâmicas semanais, pré-seleção ("Candidato"), ou cotas de classificação estadual/regional
            if (preg_match('/^(Semana|Candidato|Associa[çc][ãa]o|Estado|Federa[çc][ãa]o|Confedera[çc][ãa]o|Pote|Grupo|Data de transmiss[ãa]o)\b/iu', $cabecalhoTh) ||
                preg_match('/(Forma de Entrada|Classifica[çc][ãa]o|Qualifica[çc][ãa]o)/iu', $cabecalhoTh)) {
                continue;
            }

            $ehTabelaParticipantes = (bool) (preg_match('/^Participante\b/iu', $cabecalhoTh) && preg_match('/\bResultado\b/iu', $cabecalhoTh));
            $ehTabelaCampeoes = (bool) preg_match('/(Vencedor|Campe[ãa]o|Campe[õo]es|Ganhador|T[íi]tulos|Vice|2\.º lugar|Finalista|Medalha|Ouro)/iu', $cabecalhoTh);

            if (!$ehTabelaCampeoes && !$ehTabelaParticipantes) {
                continue;
            }

            $linhasTabela = [];
            foreach ($rowMatches[1] as $rowHtml) {
                if (!preg_match_all('/<t[hd][^>]*>(.*?)<\/t[hd]>/si', $rowHtml, $cellMatches)) continue;
                $celulas = [];
                foreach ($cellMatches[1] as $cellHtml) {
                    $cLimpo = preg_replace('/<(small|sup|style|script)[^>]*>.*?<\/\1>/si', '', $cellHtml);
                    $cLimpo = html_entity_decode(strip_tags($cLimpo), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cLimpo = trim(preg_replace('/\s+/u', ' ', $cLimpo));
                    $cLimpo = preg_replace('/\[[^\]]*\]/', '', $cLimpo);
                    $cLimpo = trim($cLimpo);
                    if ($cLimpo !== '' && !preg_match('/^(Final dupla|Final tripla|Eliminados|Ref\.)$/iu', $cLimpo)) {
                        $celulas[] = $cLimpo;
                    }
                }
                if (count($celulas) >= 2) {
                    if ($ehTabelaParticipantes && count($celulas) >= 4) {
                        // Preserva o nome do participante (col 0) e o resultado final (última coluna útil)
                        $ultCol = $celulas[count($celulas) - 1];
                        $primeiras = [$celulas[0], $celulas[2] ?? $celulas[1], $ultCol];
                    } else {
                        $primeiras = array_slice($celulas, 0, 3);
                    }
                    $linhaStr = implode(' | ', $primeiras);
                    if (mb_strlen($linhaStr, 'UTF-8') <= 140) {
                        $linhasTabela[] = $linhaStr;
                    }
                }
            }

            if (count($linhasTabela) >= 3) {
                // Se a tabela tiver mais de 34 linhas, preserva o cabeçalho + primeiras 12 + últimas 22 (garantindo as edições recentes!)
                if (count($linhasTabela) > 34) {
                    $blocoLinhas = array_merge(
                        array_slice($linhasTabela, 0, 12),
                        ['...'],
                        array_slice($linhasTabela, -22)
                    );
                } else {
                    $blocoLinhas = $linhasTabela;
                }
                $bloco = implode("\n", $blocoLinhas);

                if ($ehTabelaCampeoes && !preg_match('/R\$\s*\d+/iu', $linhasTabela[2] ?? '')) {
                    $prioritarias[] = $bloco;
                } elseif ($ehTabelaParticipantes) {
                    $participantesTemporada[] = $bloco;
                }
            }
        }
    }
    $tabelas = !empty($prioritarias) ? $prioritarias[0] : (!empty($participantesTemporada) ? $participantesTemporada[0] : '');

    return ['extrato' => $extrato, 'tabelas' => $tabelas];
}

/**
 * Realiza consulta em tempo real na Web combinando:
 * 1. Wikipédia (PT e EN) com extração de Infobox, artigo E tabelas estruturadas (wikitable)
 * 2. Google News RSS (notícias e resultados recentes em tempo real)
 * 3. Bing Web Search RSS (snippets da web em tempo real)
 * Executa buscas em paralelo via curl_multi para máxima velocidade.
 */
function opus_consultar_web(string $consulta): array {
    static $memCache = [];

    $consultaSemSlash = trim((string) preg_replace('/^(?:\/browser|\/busca|\/web|\/pesquisar|\/search)\b[:\s-]*/iu', '', trim($consulta)));
    if ($consultaSemSlash === '') {
        $consultaSemSlash = trim($consulta);
    }

    $termo = $consultaSemSlash;
    $termo = preg_replace('/^(?:pesquise|pesquisar|busque|buscar|consultar|consulta)\b[:\s-]*/iu', '', $termo);
    $termo = preg_replace('/^(?:me\s+(?:diga|fale|explique|conta|conte|responda|dê|de|da|dá|passe|mostre|ensine)\s+(?:uma?\s+)?(?:receita\s+de\s+|dica\s+de\s+|resumo\s+de\s+|exemplo\s+de\s+|sobre\s+)?|(?:fale|explique|diga|responda|conta|conte|mostre|ensine)\s+(?:um\s+pouco\s+)?(?:sobre\s+|para\s+mim\s+)?)/iu', '', $termo);
    $termo = preg_replace('/^quem\s+descobriu\s+(?:o|a|os|as)?\s*/iu', 'Descobrimento do ', $termo);
    $termo = preg_replace('/^(?:como\s+(?:criar|fazer|preparar|implementar|usar|declarar)\s+(?:um|uma|o|a|os|as)?\s*)/iu', '', $termo);
    $termo = preg_replace('/^(?:o\s+que\s+(?:é|e|são|sao|significa|foi|eram)|quem\s+(?:foi|é|e|são|sao|ganhou|venceu|criou|inventou)|qual\s+(?:(?:é|e|foi|era)\s+)?(?:a|o|os|as)?|quais\s+(?:(?:são|sao|foram|eram)\s+)?(?:as|os)?|como\s+(?:funciona|funcionam|surgiu|fazer|é|e)|onde\s+(?:fica|está|esta|foi)|quando\s+(?:foi|aconteceu|surgiu|foi\s+lançado|foi\s+lancado)|por\s*que\s+(?:o|a|os|as)?)\s+/iu', '', $termo);
    $termo = preg_replace('/^(?:receita\s+de|porcentagem\s+de|líder\s+d[aoe]|lider\s+d[aoe]|cada\s+edi[çc][ãa]o\s+d[aoe]|todas\s+as\s+edi[çc][õo]es\s+d[aoe]|todos\s+os\s+campe[õo]es\s+d[aoe]|todos\s+os\s+vencedores\s+d[aoe])\s+/iu', '', $termo);
    $termo = preg_replace('/^(?:um|uma|o|a|os|as)\s+/iu', '', $termo);
    $termo = trim($termo, "?!.,:;\"' ");

    // Normalizações de nomes de torneios e termos comuns na Wikipédia PT
    if (preg_match('/\bbbb\s*\d+\s*(?:e|,|ou|a)\s*(?:bbb\s*)?\d+/iu', $termo)) {
        $termo = 'Big Brother Brasil';
    } elseif (preg_match('/\bchampions\s+league\b/iu', $termo) && !preg_match('/(basquete|basketball|am[eé]ricas|volei|v[oô]lei)/iu', $termo)) {
        $termo = preg_replace('/\b(?:uefa\s+)?champions\s+league\b/iu', 'Liga dos Campeões da UEFA', $termo);
    } elseif (preg_match('/^presidente\s+atual\s+d/iu', $termo)) {
        $termo = preg_replace('/^presidente\s+atual\s+/iu', 'Presidente ', $termo);
    }

    if ($termo === '') {
        $termo = trim($consultaSemSlash, "?!.,:;\"' /");
    }

    if ($termo === '' || mb_strlen($termo, 'UTF-8') < 2) {
        return ['sucesso' => false, 'termo_limpo' => $termo, 'titulo' => '', 'extrato' => '', 'tabelas' => '', 'noticias' => [], 'web_snippets' => [], 'snippets' => [], 'url' => '', 'fonte' => ''];
    }

    $cacheKey = mb_strtolower($consultaSemSlash . '|' . $termo, 'UTF-8');
    if (isset($memCache[$cacheKey])) {
        return $memCache[$cacheKey];
    }

    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'opus_web_cache_v5.json';
    $diskCache = [];
    if (is_file($cacheFile) && is_readable($cacheFile)) {
        $decodedCache = json_decode((string) @file_get_contents($cacheFile), true);
        if (is_array($decodedCache)) {
            $diskCache = $decodedCache;
            if (isset($diskCache[$cacheKey]) && !empty($diskCache[$cacheKey]['sucesso'])) {
                $memCache[$cacheKey] = $diskCache[$cacheKey];
                return $diskCache[$cacheKey];
            }
        }
    }

    $userAgent = 'OpusEducationalPlatform/3.0 (https://localhost/OPUS; suporte@opus.edu.br) PHP-cURL';
    $browserUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36';

    // Executa em paralelo: (1) Busca Wikipédia PT, (2) Google News RSS, (3) Bing Web Search RSS
    $mh = curl_multi_init();

    $urlWikiSearchPt = "https://pt.wikipedia.org/w/api.php?action=query&list=search&srsearch=" . urlencode($termo) . "&utf8=1&format=json&srlimit=4";
    $chWikiPt = curl_init($urlWikiSearchPt);
    curl_setopt_array($chWikiPt, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $userAgent,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 4
    ]);

    $urlNews = "https://news.google.com/rss/search?q=" . urlencode($consultaSemSlash) . "&hl=pt-BR&gl=BR&ceid=BR:pt-419";
    $chNews = curl_init($urlNews);
    curl_setopt_array($chNews, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => $browserUA,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 4
    ]);

    $urlBingRss = "https://www.bing.com/search?q=" . urlencode($consultaSemSlash) . "&format=rss&setlang=pt-BR";
    $chBingRss = curl_init($urlBingRss);
    curl_setopt_array($chBingRss, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => $browserUA,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 4
    ]);

    curl_multi_add_handle($mh, $chWikiPt);
    curl_multi_add_handle($mh, $chNews);
    curl_multi_add_handle($mh, $chBingRss);

    $running = null;
    do {
        $status = curl_multi_exec($mh, $running);
        if ($running) {
            curl_multi_select($mh, 0.2);
        }
    } while ($running > 0 && $status === CURLM_OK);

    $rawWikiPt = curl_multi_getcontent($chWikiPt);
    $rawNews = curl_multi_getcontent($chNews);
    $rawBingRss = curl_multi_getcontent($chBingRss);

    curl_multi_remove_handle($mh, $chWikiPt);
    curl_multi_remove_handle($mh, $chNews);
    curl_multi_remove_handle($mh, $chBingRss);
    curl_close($chWikiPt);
    curl_close($chNews);
    curl_close($chBingRss);
    curl_multi_close($mh);

    // Processa Google News RSS
    $noticias = [];
    if ($rawNews && preg_match_all('/<item>.*?<title>(.*?)<\/title>.*?<\/item>/si', $rawNews, $mNews)) {
        foreach (array_slice($mNews[1], 0, 5) as $nTit) {
            $tLimpo = trim(html_entity_decode(strip_tags($nTit), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($tLimpo !== '') {
                $noticias[] = $tLimpo;
            }
        }
    }

    // Processa Bing Web Search RSS Snippets
    $webSnippets = [];
    if ($rawBingRss && preg_match_all('/<item>.*?<title>(.*?)<\/title>.*?<description>(.*?)<\/description>.*?<\/item>/si', $rawBingRss, $mBing)) {
        $cnt = min(count($mBing[1] ?? []), count($mBing[2] ?? []), 5);
        for ($i = 0; $i < $cnt; $i++) {
            $t = trim(html_entity_decode(strip_tags($mBing[1][$i]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $s = trim(html_entity_decode(strip_tags($mBing[2][$i]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($s !== '' && !preg_match('/UOL Economia.*D[óo]lar Comercial/iu', "{$t} {$s}")) {
                $webSnippets[] = "• {$t}: {$s}";
            }
        }
    }

    // Processa Wikipédia (PT e fallback EN)
    $idiomas = [
        ['lang' => 'pt', 'nome' => 'Wikipédia (PT)', 'raw' => $rawWikiPt],
        ['lang' => 'en', 'nome' => 'Wikipedia (EN)', 'raw' => null]
    ];

    foreach ($idiomas as $idCfg) {
        $lang = $idCfg['lang'];
        $rawSearch = $idCfg['raw'];

        if ($rawSearch === null) {
            $urlSearch = "https://{$lang}.wikipedia.org/w/api.php?action=query&list=search&srsearch=" . urlencode($termo) . "&utf8=1&format=json&srlimit=3";
            $ch = curl_init($urlSearch);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => $userAgent,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 4
            ]);
            $rawSearch = curl_exec($ch);
            curl_close($ch);
        }

        if ($rawSearch) {
            $dadosSearch = json_decode($rawSearch, true);
            $hits = $dadosSearch['query']['search'] ?? [];
            if (!empty($hits)) {
                $topHit = $hits[0]['title'];
                $snippets = [];
                foreach ($hits as $h) {
                    $snip = trim(html_entity_decode(strip_tags($h['snippet'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                    if ($snip !== '') {
                        $snippets[] = "• **{$h['title']}:** {$snip}...";
                    }
                }

                // Busca HTML do artigo para extrair Infobox, introdução E tabelas estruturadas (wikitable) em 1 requisição
                $urlParse = "https://{$lang}.wikipedia.org/w/api.php?action=parse&page=" . urlencode($topHit) . "&prop=text&format=json&redirects=1";
                $ch2 = curl_init($urlParse);
                curl_setopt_array($ch2, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_USERAGENT => $userAgent,
                    CURLOPT_HTTPHEADER => ['Accept: application/json'],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_TIMEOUT => 5
                ]);
                $rawParse = curl_exec($ch2);
                curl_close($ch2);

                $extrato = '';
                $tabelas = '';
                if ($rawParse) {
                    $dadosParse = json_decode($rawParse, true);
                    $htmlArtigo = $dadosParse['parse']['text']['*'] ?? '';
                    if ($htmlArtigo !== '') {
                        $parsedWiki = opus_extrair_intro_e_tabelas_wiki($htmlArtigo);
                        $extrato = $parsedWiki['extrato'];
                        $tabelas = $parsedWiki['tabelas'];
                    }
                }

                // Se action=parse falhar por qualquer motivo, usa prop=extracts como fallback
                if ($extrato === '') {
                    $urlExtract = "https://{$lang}.wikipedia.org/w/api.php?action=query&prop=extracts&exintro=1&explaintext=1&titles=" . urlencode($topHit) . "&format=json&redirects=1";
                    $chExt = curl_init($urlExtract);
                    curl_setopt_array($chExt, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_USERAGENT => $userAgent,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_TIMEOUT => 4
                    ]);
                    $rawExt = curl_exec($chExt);
                    curl_close($chExt);
                    if ($rawExt) {
                        $pages = json_decode($rawExt, true)['query']['pages'] ?? [];
                        $firstPage = reset($pages);
                        $extrato = trim($firstPage['extract'] ?? '');
                        if (mb_strlen($extrato, 'UTF-8') > 1200) {
                            $extrato = mb_substr($extrato, 0, 1200, 'UTF-8') . '...';
                        }
                    }
                }

                if ($extrato !== '' || $tabelas !== '' || !empty($snippets)) {
                    $wikiUrl = "https://{$lang}.wikipedia.org/wiki/" . rawurlencode(str_replace(' ', '_', $topHit));
                    $resultado = [
                        'sucesso' => true,
                        'termo_limpo' => $termo,
                        'titulo' => $topHit,
                        'extrato' => $extrato,
                        'tabelas' => $tabelas,
                        'noticias' => $noticias,
                        'web_snippets' => $webSnippets,
                        'snippets' => $snippets,
                        'url' => $wikiUrl,
                        'fonte' => $idCfg['nome']
                    ];
                    $memCache[$cacheKey] = $resultado;
                    $diskCache[$cacheKey] = $resultado;
                    if (count($diskCache) > 150) {
                        $diskCache = array_slice($diskCache, -100, null, true);
                    }
                    @file_put_contents($cacheFile, json_encode($diskCache, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
                    return $resultado;
                }
            }
        }
    }

    // Se a Wikipédia não encontrou artigo exato, mas Bing RSS ou Google News retornaram dados reais da Web:
    if (!empty($webSnippets) || !empty($noticias)) {
        $extratoCombinado = implode("\n", array_merge($webSnippets, array_map(fn($n) => "• Notícia: {$n}", $noticias)));
        $resultado = [
            'sucesso' => true,
            'termo_limpo' => $termo,
            'titulo' => ucfirst($consultaSemSlash),
            'extrato' => $extratoCombinado,
            'tabelas' => '',
            'noticias' => $noticias,
            'web_snippets' => $webSnippets,
            'snippets' => $webSnippets,
            'url' => 'https://www.bing.com/search?q=' . urlencode($consultaSemSlash),
            'fonte' => 'Pesquisa Web em Tempo Real'
        ];
        $memCache[$cacheKey] = $resultado;
        return $resultado;
    }

    return ['sucesso' => false, 'termo_limpo' => $termo, 'titulo' => '', 'extrato' => '', 'tabelas' => '', 'noticias' => [], 'web_snippets' => [], 'snippets' => [], 'url' => '', 'fonte' => ''];
}

/**
 * Realiza consultas dinâmicas diretas no banco de dados MySQL do OPUS
 * (Unidades, Lições, Desafios, Jogadores, Ranking, Troféus e Estatísticas).
 */
function opus_consultar_banco_dinamico(?mysqli $conn, int $userId, string $mensagem): string {
    if (!$conn) return '';
    $m = mb_strtolower($mensagem, 'UTF-8');
    $partes = [];

    try {
        // 1. Se mencionar seção/sessão/unidade/capítulo específico do curso, busca no banco real (`unidades`, `licoes`, `desafios`)
        $numSessao = opus_extrair_numero_sessao($mensagem);
        if ($numSessao !== null && $numSessao >= 1 && $numSessao <= 40 && !preg_match('/(livro|novela|filme|série|serie|bíblia|biblia|artigo|lei|bbb|edição|edicao|temporada|one piece|naruto|manga|mangá|terapia)/iu', $m)) {
            if ($stmt = $conn->prepare("SELECT numero, titulo, nome, descricao, guia_texto, guia_codigo FROM unidades WHERE numero = ? LIMIT 1")) {
                $stmt->bind_param("i", $numSessao);
                $stmt->execute();
                $uRow = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($uRow) {
                    $codLimpo = strip_tags(str_replace(['<br>', '<br/>', '&nbsp;'], ["\n", "\n", ' '], html_entity_decode($uRow['guia_codigo'] ?? '')));
                    $partes[] = "UNIDADE {$uRow['numero']} NO BANCO: {$uRow['titulo']} - {$uRow['nome']} | Guia: {$uRow['guia_texto']} | Exemplo: {$codLimpo}";
                }
            }
            if ($stmtL = $conn->prepare("SELECT licao_numero, titulo, texto_explicativo, codigo_exemplo FROM licoes WHERE unidade_numero = ? ORDER BY licao_numero ASC")) {
                $stmtL->bind_param("i", $numSessao);
                $stmtL->execute();
                $resL = $stmtL->get_result();
                $listaL = [];
                while ($lRow = $resL->fetch_assoc()) {
                    $listaL[] = "Lição {$lRow['licao_numero']} ({$lRow['titulo']}): " . mb_substr(str_replace("\n", " ", $lRow['texto_explicativo']), 0, 180) . "...";
                }
                $stmtL->close();
                if (!empty($listaL)) {
                    $partes[] = "LIÇÕES DA SEÇÃO {$numSessao} NO BANCO:\n- " . implode("\n- ", $listaL);
                }
            }
            if ($stmtD = $conn->prepare("SELECT heading, missao, alvo_visual, dica_texto, dica_codigo FROM desafios WHERE unidade_numero = ? LIMIT 1")) {
                $stmtD->bind_param("i", $numSessao);
                $stmtD->execute();
                $dRow = $stmtD->get_result()->fetch_assoc();
                $stmtD->close();
                if ($dRow) {
                    $partes[] = "DESAFIO PRÁTICO DA SEÇÃO {$numSessao} NO BANCO: {$dRow['heading']} | Missão: " . strip_tags($dRow['missao']) . " | Saída esperada: {$dRow['alvo_visual']} | Dica de código: {$dRow['dica_codigo']}";
                }
            }
        }

        // 2. Se perguntar especificamente sobre estatísticas gerais do sistema / banco de dados / usuários do OPUS
        if (preg_match('/(quantos usu[aá]rios|quantos alunos|quantos jogadores|estat[ií]sticas do (?:banco|opus|sistema)|banco de dados do opus|tabelas do banco|total de alunos|quem est[aá] em primeiro no ranking|l[ií]der do ranking|ranking do opus|top 10 do opus)/iu', $m)) {
            $resStats = $conn->query("SELECT 
                (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
                (SELECT COUNT(*) FROM unidades) AS total_unidades,
                (SELECT COUNT(*) FROM licoes) AS total_licoes,
                (SELECT COUNT(*) FROM perguntas) AS total_perguntas,
                (SELECT COUNT(*) FROM desafios) AS total_desafios,
                (SELECT COUNT(*) FROM catalogo_trofeus) AS total_trofeus
            ");
            if ($resStats && ($stRow = $resStats->fetch_assoc())) {
                $partes[] = "ESTATÍSTICAS DO BANCO DE DADOS OPUS: {$stRow['total_usuarios']} usuários cadastrados, {$stRow['total_unidades']} unidades/seções, {$stRow['total_licoes']} lições, {$stRow['total_perguntas']} questões de quiz, {$stRow['total_desafios']} desafios de IDE, {$stRow['total_trofeus']} troféus no catálogo.";
            }
        }
    } catch (\Throwable $e) {}

    return implode("\n\n", $partes);
}

/**
 * Monta o contexto unificado (Banco de Dados OPUS + Pesquisa Web/Infobox/Tabelas/Notícias em Tempo Real)
 * e retorna ['system_prompt' => ..., 'rag_compacto' => ..., 'web_res' => ...]
 */
function opus_montar_contexto_ia(string $prompt, string $nome, string $contexto = '', array $relatorio = [], ?mysqli $conn = null, array $historico = []): array {
    $info_progresso = "";
    if (!empty($relatorio)) {
        $topRkTexto = "";
        if (!empty($relatorio['ranking_top5'])) {
            $itensRk = [];
            foreach ($relatorio['ranking_top5'] as $rk) {
                $itensRk[] = "{$rk['posicao']}º {$rk['nome']} ({$rk['xp']} XP)";
            }
            $topRkTexto = implode(", ", $itensRk);
        }

        $missoesTexto = "";
        if (!empty($relatorio['missoes_diarias'])) {
            $itensM = [];
            foreach ($relatorio['missoes_diarias'] as $md) {
                $st = $md['completo'] ? "CONCLUÍDA" : "PENDENTE";
                $itensM[] = "{$md['titulo']}: {$md['atual']}/{$md['meta']} ({$md['porcentagem']}% - {$st})";
            }
            $missoesTexto = implode(" | ", $itensM);
        }

        $trofeusTexto = !empty($relatorio['trofeus_desbloqueados'])
            ? implode(", ", array_map(fn($t) => $t['nome'], $relatorio['trofeus_desbloqueados']))
            : "Nenhum ainda";

        $proxTrofeuInfo = !empty($relatorio['proximo_trofeu'])
            ? "{$relatorio['proximo_trofeu']['nome']} ({$relatorio['proximo_trofeu']['descricao']})"
            : "Todos desbloqueados";

        $bausResgatadosQtd = $relatorio['baus_resgatados_qtd'] ?? 0;
        $bausDispTexto = !empty($relatorio['baus_disponiveis'])
            ? "Baús liberados esperando resgate nas Seções: " . implode(", ", $relatorio['baus_disponiveis'])
            : "Nenhum baú pendente no momento ({$bausResgatadosQtd} já resgatados)";

        $licaoAtualInfo = "";
        if (!empty($relatorio['licao_atual_db'])) {
            $lDb = $relatorio['licao_atual_db'];
            $licaoAtualInfo = "\n- Lição Atual no Banco (/boost): {$lDb['titulo']} | Exemplo: " . str_replace("\n", " ", $lDb['codigo_exemplo'] ?? '');
        }
        $desafioAtualInfo = "";
        if (!empty($relatorio['desafio_atual_db'])) {
            $dDb = $relatorio['desafio_atual_db'];
            $desafioAtualInfo = "\n- Desafio IDE da Seção Atual (/boost): {$dDb['heading']} | Missão: " . strip_tags($dDb['missao'] ?? '') . " | Saída esperada: {$dDb['alvo_visual']} | Dica: {$dDb['dica_codigo']}";
        }

        $sessaoAtualNum = $relatorio['sessao_atual_num'] ?? ($relatorio['fase_id'] ?? 1);
        $porcentagemConcluida = $relatorio['porcentagem_concluida'] ?? ($relatorio['percentual'] ?? 0);
        $xpTotal = $relatorio['xp'] ?? ($relatorio['xp_total'] ?? 0);
        $ligaDivisao = ucfirst((string)($relatorio['liga_divisao'] ?? 'Bronze'));
        $ligaPosicao = $relatorio['liga_posicao'] ?? 1;

        $mPergunta = mb_strtolower($prompt, 'UTF-8');
        $perguntaSobreOpus = (bool) preg_match('/\b(opus|meu progresso|minha conta|minha liga|meus trof[eé]us|meus ba[úu]s|minhas vidas|dias de fogo|ofensiva|minhas metas|miss[õo]es di[áa]rias)\b/iu', $mPergunta);

        if ($perguntaSobreOpus && isset($relatorio['sessoes_concluidas'])) {
            $info_progresso = "
RELATÓRIO DO ALUNO E CONSULTA AO BANCO DE DADOS OPUS EM TEMPO REAL:
- Estrutura Geral do Curso Opus: 40 Sessões (Módulos) com 5 lições cada = 200 lições totais (100% gratuito, R$ 0,00).
- Sessões Concluídas: {$relatorio['sessoes_concluidas']} de 40 sessões (Restam {$relatorio['sessoes_restantes']} sessões).
- Sessão Atual: Sessão {$sessaoAtualNum} — {$relatorio['sessao_atual_nome']} (Lição atual: {$relatorio['licao_atual_num']} de 5).
- Lições Concluídas na Sessão Atual: {$relatorio['licoes_feitas_sessao_atual']} de 5 (restam {$relatorio['licoes_restantes_sessao_atual']} lições nesta sessão).
- Total de Lições no Curso Inteiro: {$relatorio['total_licoes_feitas']} de 200 concluídas ({$porcentagemConcluida}% do curso concluído; {$relatorio['porcentagem_sessao_atual']}% da sessão atual).
- Atividade Realizada HOJE (" . date('d/m/Y') . "): {$relatorio['licoes_hoje']} lição(ões) concluída(s) hoje, {$relatorio['acertos_hoje']} acerto(s) e +{$relatorio['xp_hoje']} XP hoje.
- Missões Diárias de Hoje (/goal): {$missoesTexto}.
- XP Total Acumulado: {$xpTotal} | Ofensiva: {$relatorio['dias_fogo']} Dias de Fogo | Vidas: {$relatorio['vidas']}/3 | Troféus: {$relatorio['trofeus']}.
- Troféus Desbloqueados: {$trofeusTexto} | Próximo Troféu: {$proxTrofeuInfo}.
- Baús de Recompensa (/boost): {$bausDispTexto}.
- Ranking Global do Opus: {$relatorio['ranking_global_posicao']}º lugar de {$relatorio['ranking_global_total']} jogadores (Top 5 Global: {$topRkTexto}).
- Ligas do Opus (Semana Atual): Divisão {$ligaDivisao} | {$ligaPosicao}º de {$relatorio['liga_total_grupo']} | {$relatorio['liga_xp_semana']} XP semanal.
- Nível Técnico Avaliado: {$relatorio['nivel_java']}{$licaoAtualInfo}{$desafioAtualInfo}
";
        } else {
            $info_progresso = "- Resumo do Aluno no OPUS: Sessão {$sessaoAtualNum}/40 ({$porcentagemConcluida}% do curso) | {$xpTotal} XP | Liga {$ligaDivisao} ({$ligaPosicao}º lugar).";
        }
    }

    $consulta_db_extra = opus_consultar_banco_dinamico($conn, (int)($_SESSION['user_id'] ?? 1), $prompt);
    $webRes = ['sucesso' => false];
    $consulta_web_info = "";
    $rag_compacto = "";

    $mLimpo = mb_strtolower(trim($prompt), 'UTF-8');
    $ehSaudacaoCurta = (bool) @preg_match('/^(oi|olá|ola|bom dia|boa tarde|boa noite|fala|eae|hey|salve|opa|tudo bem|como vai|obrigado|valeu|obrigada)[\?!\.\s]*$/iu', $mLimpo);
    $ehPedidoCodigoOuPiada = (bool) @preg_match('/^(crie um|crie uma|escreva um|escreva uma|faça um|faca um|implemente|gere um|me conta uma piada|conte uma piada)\b/iu', $mLimpo);
    $ehDeclaracaoPessoal = (bool) @preg_match('/^(meu nome [eé]|minha linguagem favorita|meu prato favorito|eu gosto de|lembre-se que|guarde que)\b/iu', $mLimpo);

    if (!$ehSaudacaoCurta && !$ehPedidoCodigoOuPiada && !$ehDeclaracaoPessoal && mb_strlen($mLimpo, 'UTF-8') >= 3) {
        $termoParaWeb = $prompt;
        // Se for pergunta curta de acompanhamento ("E o 24?", "E quem ficou em segundo?", "De onde ela é?"), combina com o tópico da pergunta anterior do usuário
        if (!empty($historico) && (mb_strlen($mLimpo, 'UTF-8') <= 28 || @preg_match('/^(e\s+(?:o|a|os|as|em|no|na|quem)|qual\s+foi|de\s+onde\s+(?:ele|ela)|quantos\s+anos\s+(?:ele|ela))/iu', $mLimpo))) {
            for ($i = count($historico) - 1; $i >= 0; $i--) {
                if (($historico[$i]['role'] ?? '') === 'user' && !empty(trim($historico[$i]['content'] ?? ''))) {
                    $termoParaWeb = trim($historico[$i]['content']) . ' ' . $prompt;
                    break;
                }
            }
        }

        $webRes = opus_consultar_web($termoParaWeb);
        if (!empty($webRes['sucesso'])) {
            $blocosWeb = [];
            $temTabela = !empty($webRes['tabelas']);
            if (!empty($webRes['extrato'])) {
                $blocosWeb[] = "Resumo e Ficha Enciclopédica ({$webRes['titulo']} - {$webRes['fonte']}):\n" . mb_substr($webRes['extrato'], 0, 1250, 'UTF-8');
            }
            if ($temTabela) {
                $blocosWeb[] = "Tabela Oficial ({$webRes['titulo']}):\n" . mb_substr($webRes['tabelas'], 0, 1300, 'UTF-8');
            }
            if (!empty($webRes['noticias'])) {
                $blocosWeb[] = "Notícias Recentes (Google News):\n- " . implode("\n- ", array_slice($webRes['noticias'], 0, 3));
            }
            if (!empty($webRes['web_snippets'])) {
                $blocosWeb[] = "Trechos da Web em Tempo Real:\n" . implode("\n", array_slice($webRes['web_snippets'], 0, 3));
            }
            $consulta_web_info = "\nFATOS E PESQUISA EM TEMPO REAL NA WEB / WIKIPÉDIA (USE ESTES DADOS REAIS PARA RESPONDER COM PRECISÃO):\n" . implode("\n\n", $blocosWeb) . "\n";

            // Monta versão compacta de alta densidade para provedores com limite de tamanho
            $partesCompactas = [];
            if (!empty($webRes['extrato'])) {
                if (@preg_match('/(Ficha Oficial:[^\n]+)/iu', $webRes['extrato'], $mFicha)) {
                    $partesCompactas[] = trim($mFicha[1]);
                }
                $extratoSemFicha = trim((string) preg_replace('/^Ficha Oficial:[^\n]+\n*/iu', '', $webRes['extrato']));
                if (@preg_match('/([^.\n]*(?:vitória|vencedor|vencedora|campe[ãa]o|campe[ãa]|ganhou|conquistou|No cargo|Incumbente)[^.\n]*\.)/iu', $extratoSemFicha, $mSent)) {
                    $partesCompactas[] = trim($mSent[1]);
                }
            }
            if ($temTabela) {
                $blocoPrincipalTab = explode("\n---\n", $webRes['tabelas'])[0];
                $linhasTab = array_values(array_filter(array_map('trim', explode("\n", $blocoPrincipalTab))));
                $paresCompactos = [];
                foreach ($linhasTab as $lt) {
                    $cols = array_map('trim', explode('|', $lt));
                    if (count($cols) >= 2 && is_numeric($cols[0])) {
                        $paresCompactos[] = "{$cols[0]}: {$cols[1]}";
                    }
                }
                if (!empty($paresCompactos)) {
                    $partesCompactas[] = "Campeões oficiais por edição (" . implode(", ", $paresCompactos) . ")";
                } else {
                    $partesCompactas[] = implode(" ; ", array_slice($linhasTab, 0, 5));
                }
            }
            if (!empty($webRes['extrato']) && empty($partesCompactas)) {
                $partesCompactas[] = mb_substr($webRes['extrato'], 0, 380, 'UTF-8');
            }
            if (mb_strlen(implode(" | ", $partesCompactas), 'UTF-8') < 420) {
                if (!empty($webRes['noticias'])) {
                    $partesCompactas[] = implode(" | ", array_slice($webRes['noticias'], 0, 2));
                }
                if (!empty($webRes['web_snippets'])) {
                    $partesCompactas[] = implode(" | ", array_slice($webRes['web_snippets'], 0, 2));
                }
            }
            $rag_compacto = implode(" | ", $partesCompactas);
        }
    }

    $system_prompt = "Você é o Opi IA, uma inteligência artificial generativa completa (nível Google Gemini e ChatGPT) integrada à plataforma OPUS.
REGRAS:
1. Responda QUALQUER pergunta sobre QUALQUER assunto (realities como BBB, esportes, cultura pop, ciência, história, matemática, economia, receitas, programação em qualquer linguagem, conversa livre e dúvidas sobre o OPUS).
2. NUNCA diga que seu conhecimento é limitado a 2023/2024 quando houver dados em 'FATOS E PESQUISA EM TEMPO REAL NA WEB / WIKIPÉDIA' abaixo: use todos os nomes, vencedores, fichas e fatos fornecidos ali como verdade atual!
3. Se o usuário perguntar sobre vencedores/campeões de um programa ou torneio (ex: 'Quem ganhou o BBB') sem especificar um único ano, apresente os vencedores mais recentes e liste também os campeões das outras edições presentes na tabela!
4. Responda sempre em português do Brasil, de forma natural, clara, completa e bem formatada.

Contexto: Data de hoje " . date('d/m/Y') . " | Usuário {$nome} | Tela: " . (!empty($contexto) ? $contexto : "OPUS") . "
{$info_progresso}
" . (!empty($consulta_db_extra) ? "\nDADOS DO BANCO OPUS:\n{$consulta_db_extra}\n" : "") . "
{$consulta_web_info}";

    return [
        'system_prompt' => $system_prompt,
        'rag_compacto' => $rag_compacto,
        'web_res' => $webRes
    ];
}

/**
 * Provedor Cloud LLM 1: Yqcloud (Modelo classe GPT-4 gratuito sem chave, suporta contexto + histórico + RAG)
 */
function chamar_llm_yqcloud(string $systemPrompt, string $userPrompt, array $historico = [], string $ragCompacto = ''): ?string {
    $partesHist = [];
    if (!empty($historico)) {
        foreach (array_slice($historico, -3) as $h) {
            $r = ($h['role'] ?? '') === 'assistant' ? 'Assistente' : 'Usuário';
            $c = trim($h['content'] ?? '');
            if ($c !== '') {
                $partesHist[] = "{$r}: " . mb_substr($c, 0, 220, 'UTF-8');
            }
        }
    }

    // O endpoint Yqcloud aceita até ~1400 chars no total; prioriza o RAG compacto de alta densidade quando o systemPrompt é grande
    if (mb_strlen($systemPrompt, 'UTF-8') > 1150 && $ragCompacto !== '') {
        $sysEnxuto = "Você é o Opi IA da plataforma OPUS (Data: " . date('d/m/Y') . "). Responda em português do Brasil de forma completa, natural e precisa usando estes fatos reais atualizados (se perguntarem quem ganhou um programa/torneio sem especificar o ano, cite o campeão mais recente e liste também todos os outros campeões das edições abaixo):\n"
            . mb_substr($ragCompacto, 0, 920, 'UTF-8');
    } else {
        $sysEnxuto = mb_strlen($systemPrompt, 'UTF-8') > 1150
            ? mb_substr($systemPrompt, 0, 1150, 'UTF-8')
            : $systemPrompt;
    }

    $promptCompleto = $sysEnxuto . "\n\n"
        . (!empty($partesHist) ? "Histórico recente da conversa:\n" . implode("\n", $partesHist) . "\n\n" : "")
        . "Pergunta do usuário: " . $userPrompt . "\n\n"
        . "Responda diretamente à pergunta do usuário com base nos dados acima:";

    $payload = [
        'prompt' => $promptCompleto,
        'userId' => '#/chat/' . (string) round(microtime(true) * 1000) . mt_rand(100, 999),
        'network' => false,
        'system' => '',
        'withoutContext' => false,
        'stream' => true
    ];

    $ch = curl_init(OPI_CLOUD_YQ_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8',
            'Content-Type: application/json',
            'Origin: https://chat9.yqcloud.top',
            'Referer: https://chat9.yqcloud.top/',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 14
    ]);

    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw && $code === 200) {
        $texto = trim((string) $raw);
        if ($texto !== '' && mb_strlen($texto, 'UTF-8') >= 5 && strpos($texto, '<!DOCTYPE html') === false) {
            return $texto;
        }
    }

    return null;
}

/**
 * Provedor Cloud LLM 2: Pollinations OpenAI-Compatible API (Gratuito sem chave, suporta contexto longo)
 */
function chamar_llm_pollinations(array $messages): ?string {
    $payload = [
        'model' => 'openai-fast',
        'reasoning_effort' => 'low',
        'messages' => $messages
    ];

    $ch = curl_init(OPI_CLOUD_POLLINATIONS_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT => 9
    ]);

    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw && $code === 200) {
        $dados = json_decode($raw, true);
        $conteudo = trim($dados['choices'][0]['message']['content'] ?? '');
        if ($conteudo !== '') {
            return $conteudo;
        }
    }

    return null;
}

/**
 * Provedor Cloud LLM 3: DeepAI (Ultra-rápido ~1.5s, gratuito sem chave, suporta histórico e RAG em blocos de <= 400 chars)
 */
function chamar_llm_deepai(string $userPrompt, string $ragCompacto = '', array $historico = []): ?string {
    $ua = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36';
    $rnd = (string) mt_rand(10000000000, 99999999999);
    $h1 = strrev(md5($ua . $rnd . 'hackers_become_a_little_stinkier_every_time_they_hack'));
    $h2 = strrev(md5($ua . $h1));
    $h3 = strrev(md5($ua . $h2));
    $apiKey = "tryit-{$rnd}-{$h3}";

    $chatHistory = [];

    // Inclui histórico recente da conversa (respeitando o limite de ~400 chars por turno do DeepAI)
    if (!empty($historico)) {
        foreach (array_slice($historico, -4) as $h) {
            $r = ($h['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $c = trim($h['content'] ?? '');
            if ($c !== '') {
                $chatHistory[] = ['role' => $r, 'content' => mb_substr($c, 0, 340, 'UTF-8')];
            }
        }
    }

    // Divide o RAG em blocos de até 340 chars, colocando o bloco principal (mais importante) por último antes da pergunta
    if ($ragCompacto !== '') {
        $fatias = [];
        $lenRag = mb_strlen($ragCompacto, 'UTF-8');
        $offset = 0;
        while ($offset < $lenRag && count($fatias) < 3) {
            $fatias[] = mb_substr($ragCompacto, $offset, 340, 'UTF-8');
            $offset += 340;
        }
        foreach (array_reverse($fatias) as $idx => $fatia) {
            $chatHistory[] = ['role' => 'user', 'content' => "Fatos reais atualizados: {$fatia}"];
            $chatHistory[] = ['role' => 'assistant', 'content' => "Registrado, usarei esses fatos atualizados."];
        }
    }

    $msgFinal = "Responda diretamente em portugues (sem se apresentar como DeepSeek), usando todos os fatos atualizados acima: " . mb_substr(trim($userPrompt), 0, 320, 'UTF-8');
    $chatHistory[] = ['role' => 'user', 'content' => $msgFinal];

    foreach (['deepseek-v3.2', 'gemini-2.5-flash-lite', 'standard'] as $modelo) {
        $uuid1 = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        $uuid2 = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        $postFields = [
            'chat_style' => 'chat',
            'chatHistory' => json_encode($chatHistory, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            'model' => $modelo,
            'session_uuid' => $uuid1,
            'sensitivity_request_id' => $uuid2,
            'hacker_is_stinky' => 'very_stinky',
            'enabled_tools' => '[]'
        ];

        $ch = curl_init(OPI_CLOUD_DEEPAI_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "api-key: {$apiKey}",
                "user-agent: {$ua}",
                'origin: https://deepai.org',
                'referer: https://deepai.org/chat'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8
        ]);

        $raw = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw && $code === 200) {
            $limpo = trim(explode("\x1c", (string) $raw)[0]);
            $limpo = trim((string) preg_replace('/^Sou (?:o )?DeepSeek[^\n]*\n+/iu', '', $limpo));
            if ($limpo !== '' && mb_strlen($limpo, 'UTF-8') >= 5) {
                return $limpo;
            }
        }
    }

    return null;
}

/**
 * Executa requisição para a API da Groq, Google Gemini, OpenAI ou Provedores Cloud LLM Nativos (Yqcloud, DeepAI, Pollinations)
 * Funciona 100% imediatamente sem exigir chave manual, respondendo QUALQUER pergunta como Gemini / ChatGPT!
 */
function chamar_api_groq(string $apiKey, string $prompt, string $nome, string $contexto = '', array $relatorio = [], array $historico = [], ?mysqli $conn = null) {
    @set_time_limit(60);
    $ctxIa = opus_montar_contexto_ia($prompt, $nome, $contexto, $relatorio, $conn, $historico);
    $system_prompt = $ctxIa['system_prompt'];
    $rag_compacto = $ctxIa['rag_compacto'];

    $messages = [
        ['role' => 'system', 'content' => $system_prompt]
    ];

    if (!empty($historico) && is_array($historico)) {
        $ultimas = array_slice($historico, -6);
        foreach ($ultimas as $msg) {
            $r = $msg['role'] ?? '';
            $c = trim($msg['content'] ?? '');
            if (($r === 'user' || $r === 'assistant') && !empty($c)) {
                $messages[] = ['role' => $r, 'content' => $c];
            }
        }
    }

    $messages[] = ['role' => 'user', 'content' => $prompt];

    // 1. Se houver chave da Groq configurada, prioriza a Groq (LLaMA 3.3 70B / 3.1 8B)
    $groqKey = !empty($apiKey) ? $apiKey : obter_groq_api_key();
    if (!empty($groqKey)) {
        $modelos = [GROQ_MODEL_PRIMARY, GROQ_MODEL_FALLBACK];
        foreach ($modelos as $modelo) {
            $payload = [
                'model' => $modelo,
                'messages' => $messages,
                'temperature' => 0.6,
                'max_tokens' => 1400,
                'top_p' => 0.95
            ];

            $ch = curl_init(GROQ_API_URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $groqKey
                ],
                CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 15
            ]);

            $resposta_raw = curl_exec($ch);
            $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($resposta_raw && $http_code === 200) {
                $dados = json_decode($resposta_raw, true);
                $conteudo = trim($dados['choices'][0]['message']['content'] ?? '');
                if ($conteudo !== '') {
                    return $conteudo;
                }
            }
        }
    }

    // 2. Se houver chave do Google Gemini configurada (com suporte a histórico multi-turno)
    $geminiKey = obter_gemini_api_key();
    if (!empty($geminiKey)) {
        $urlGem = GEMINI_API_URL . '?key=' . urlencode($geminiKey);
        $gemContents = [];
        if (!empty($historico) && is_array($historico)) {
            foreach (array_slice($historico, -6) as $msg) {
                $r = ($msg['role'] ?? '') === 'assistant' ? 'model' : 'user';
                $c = trim($msg['content'] ?? '');
                if ($c !== '') {
                    $gemContents[] = ['role' => $r, 'parts' => [['text' => $c]]];
                }
            }
        }
        $gemContents[] = ['role' => 'user', 'parts' => [['text' => $prompt]]];

        $payloadGem = [
            'systemInstruction' => ['parts' => [['text' => $system_prompt]]],
            'contents' => $gemContents,
            'generationConfig' => ['temperature' => 0.6, 'maxOutputTokens' => 1400]
        ];
        $chGem = curl_init($urlGem);
        curl_setopt_array($chGem, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payloadGem, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);
        $rawGem = curl_exec($chGem);
        $codeGem = (int) curl_getinfo($chGem, CURLINFO_HTTP_CODE);
        curl_close($chGem);
        if ($rawGem && $codeGem === 200) {
            $dadosGem = json_decode($rawGem, true);
            $txtGem = trim($dadosGem['candidates'][0]['content']['parts'][0]['text'] ?? '');
            if ($txtGem !== '') {
                return $txtGem;
            }
        }
    }

    // 3. Se houver chave da OpenAI configurada
    $openaiKey = obter_openai_api_key();
    if (!empty($openaiKey)) {
        $payloadOai = [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
            'temperature' => 0.6,
            'max_tokens' => 1400
        ];
        $chOai = curl_init(OPENAI_API_URL);
        curl_setopt_array($chOai, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openaiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payloadOai, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15
        ]);
        $rawOai = curl_exec($chOai);
        $codeOai = (int) curl_getinfo($chOai, CURLINFO_HTTP_CODE);
        curl_close($chOai);
        if ($rawOai && $codeOai === 200) {
            $dadosOai = json_decode($rawOai, true);
            $txtOai = trim($dadosOai['choices'][0]['message']['content'] ?? '');
            if ($txtOai !== '') {
                return $txtOai;
            }
        }
    }

    // 4. Motor LLM Generativo Cloud Nativo 1: Yqcloud (Contexto completo + RAG + Histórico, sem chave)
    $respYq = chamar_llm_yqcloud($system_prompt, $prompt, $historico, $rag_compacto);
    if ($respYq !== null && $respYq !== '') {
        return $respYq;
    }

    // 5. Motor LLM Generativo Cloud Nativo 2: DeepAI (Gemini 2.5 Flash Lite / DeepSeek V3.2, resposta ~1.5s sem chave)
    $respDeep = chamar_llm_deepai($prompt, $rag_compacto, $historico);
    if ($respDeep !== null && $respDeep !== '') {
        return $respDeep;
    }

    // 6. Motor LLM Generativo Cloud Nativo 3: Pollinations OpenAI (Contexto completo, sem chave)
    $respPoll = chamar_llm_pollinations($messages);
    if ($respPoll !== null && $respPoll !== '') {
        return $respPoll;
    }

    return null;
}


/**
 * Retorna o conteúdo programático de uma seção ou capítulo
 */
function opus_obter_ementa_sessao(int $num): ?array {
    $catalogo = [
        1 => [
            'titulo' => 'Seção 1: Introdução',
            'nome' => 'Introdução aos Fundamentos de Java',
            'texto' => 'Na Seção 1, você dá seus primeiros passos na programação Java! Você aprenderá a estrutura básica de um programa (`public class Main`, método `main`), a exibição de dados na tela com `System.out.println()`, a regra de ouro das aspas duplas para textos e a obrigatoriedade do ponto e vírgula `;`.',
            'codigo' => "public class Main {\n    public static void main(String[] args) {\n        System.out.println(\"Olá, Mundo!\");\n    }\n}"
        ],
        2 => [
            'titulo' => 'Seção 2: Variáveis Parte 1',
            'nome' => 'Variáveis, Tipos Inteiros e Texto',
            'texto' => 'Na Seção 2, você aprenderá o conceito fundamental de variáveis em Java! As variáveis funcionam como caixas de memória onde guardamos dados. Você aprenderá como declarar variáveis para números inteiros (`int`) e textos (`String`), além de como atribuir valores, fazer concatenação com o operador `+` e exibir as informações com `System.out.println()`.',
            'codigo' => "int idade = 20;\nString nome = \"Opus\";\n\nSystem.out.println(nome + \" tem \" + idade + \" anos.\");"
        ],
        3 => [
            'titulo' => 'Seção 3: Variáveis Parte 2',
            'nome' => 'Decimais (double) e Valores Lógicos (boolean)',
            'texto' => 'Na Seção 3, você expandirá os tipos de variáveis conhecendo números com ponto flutuante (`double`) para representar preços ou alturas, e o tipo lógico (`boolean`), que aceita apenas `true` ou `false`.',
            'codigo' => "double preco = 49.90;\nboolean disponivel = true;\n\nSystem.out.println(\"Preço: R$ \" + preco + \" | Em estoque: \" + disponivel);"
        ],
        4 => [
            'titulo' => 'Seção 4: Operadores Parte 1',
            'nome' => 'Operadores Aritméticos',
            'texto' => 'Na Seção 4, você aprenderá a realizar cálculos matemáticos com operadores aritméticos: adição (`+`), subtração (`-`), multiplicação (`*`), divisão (`/`) e resto da divisão (`%`).',
            'codigo' => "int a = 10;\nint b = 3;\nint soma = a + b;\nint resto = a % b;\nSystem.out.println(\"Soma: \" + soma + \" | Resto: \" + resto);"
        ],
        5 => [
            'titulo' => 'Seção 5: Operadores Parte 2',
            'nome' => 'Operadores Relacionais e Lógicos',
            'texto' => 'Na Seção 5, você aprenderá a comparar dados com operadores relacionais (`==`, `!=`, `>`, `<`, `>=`, `<=`) e a combinar condições com operadores lógicos (`&&` para E, `||` para OU, `!` para NÃO).',
            'codigo' => "int nota = 8;\nboolean aprovado = (nota >= 7) && (nota <= 10);\nSystem.out.println(\"Aprovado: \" + aprovado);"
        ],
        6 => [
            'titulo' => 'Seção 6: Tomada de Decisão',
            'nome' => 'Estruturas Condicionais (if, else e switch)',
            'texto' => 'Na Seção 6, você aprenderá como direcionar o fluxo do seu código usando `if`, `else if`, `else` e a estrutura `switch-case`.',
            'codigo' => "int hora = 14;\nif (hora < 12) {\n    System.out.println(\"Bom dia!\");\n} else {\n    System.out.println(\"Boa tarde!\");\n}"
        ],
        7 => [
            'titulo' => 'Seção 7: I/O Básico',
            'nome' => 'Entrada de Dados com Scanner',
            'texto' => 'Na Seção 7, você aprenderá a ler dados digitados pelo usuário no teclado utilizando a classe `java.util.Scanner` (`nextInt()`, `nextLine()`, etc.).',
            'codigo' => "Scanner scanner = new Scanner(System.in);\nSystem.out.print(\"Digite seu nome: \");\nString nome = scanner.nextLine();"
        ],
        8 => [
            'titulo' => 'Seção 8: Aplicativo de Calculadora',
            'nome' => 'Projeto Prático de Calculadora',
            'texto' => 'Na Seção 8, você integrará variáveis, entrada de dados com Scanner, operadores e estruturas condicionais para construir uma calculadora interativa no terminal.',
            'codigo' => "// Mini-projeto interativo combinando Scanner e switch-case!"
        ],
        9 => [
            'titulo' => 'Seção 9: Laços',
            'nome' => 'Estruturas de Repetição (for, while, do-while)',
            'texto' => 'Na Seção 9, você aprenderá como automatizar tarefas repetitivas usando laços `for`, `while` e `do-while`, além de entender comandos de interrupção como `break` e `continue`.',
            'codigo' => "for (int i = 1; i <= 5; i++) {\n    System.out.println(\"Contagem: \" + i);\n}"
        ],
        10 => [
            'titulo' => 'Seção 10: Métodos e Funções',
            'nome' => 'Modularização, Parâmetros e Retornos',
            'texto' => 'Na Seção 10, você aprenderá a organizar seu código em métodos reutilizáveis, declarando parâmetros e valores de retorno.',
            'codigo' => "public static int somar(int a, int b) {\n    return a + b;\n}"
        ],
        11 => [
            'titulo' => 'Seção 11: Funcionamento de Arrays',
            'nome' => 'Vetores Unidimensionais em Java',
            'texto' => 'Na Seção 11, você aprenderá a armazenar múltiplos valores do mesmo tipo em uma única estrutura de array indexada de tamanho fixo.',
            'codigo' => "int[] numeros = {10, 20, 30, 40};\nSystem.out.println(numeros[0]); // 10"
        ],
        12 => [
            'titulo' => 'Seção 12: Iterando sobre Arrays',
            'nome' => 'Laço for-each e Manipulação de Arrays',
            'texto' => 'Na Seção 12, você aprenderá a percorrer arrays com elegância usando o laço melhorado `for-each` e métodos da classe `java.util.Arrays`.',
            'codigo' => "for (int n : numeros) {\n    System.out.println(n);\n}"
        ],
        17 => [
            'titulo' => 'Seção 17: HashMap',
            'nome' => 'Coleções Chave-Valor com HashMap',
            'texto' => 'Na Seção 17, você aprenderá a armazenar pares de dados chave-valor para buscas instantâneas usando `HashMap<K, V>`.',
            'codigo' => "HashMap<String, Integer> idades = new HashMap<>();\nidades.put(\"Maria\", 22);\nSystem.out.println(idades.get(\"Maria\"));"
        ],
        27 => [
            'titulo' => 'Seção 27: Fundamentos de POO',
            'nome' => 'Programação Orientada a Objetos: Classes e Objetos',
            'texto' => 'Na Seção 27, você entrará no coração do Java! Aprenderá a criar classes com atributos e métodos, e instanciar objetos no heap com a palavra-chave `new`.',
            'codigo' => "class Carro {\n    String modelo;\n    void acelerar() { System.out.println(\"Vrum!\"); }\n}"
        ],
        28 => [
            'titulo' => 'Seção 28: Encapsulamento',
            'nome' => 'Modificadores de Acesso e Encapsulamento',
            'texto' => 'Na Seção 28, você aprenderá a proteger os atributos das classes usando `private`, criando métodos acessores `getters` e `setters`.',
            'codigo' => "private double saldo;\npublic double getSaldo() { return saldo; }"
        ],
        30 => [
            'titulo' => 'Seção 30: Herança',
            'nome' => 'Reutilização com Herança (extends)',
            'texto' => 'Na Seção 30, você aprenderá a criar hierarquias de classes com a palavra-chave `extends` e redefinir comportamentos com `@Override`.',
            'codigo' => "class Dev extends Funcionario {\n    @Override void trabalhar() { ... }\n}"
        ],
        31 => [
            'titulo' => 'Seção 31: Polimorfismo',
            'nome' => 'Múltiplas Formas e Classes Abstratas',
            'texto' => 'Na Seção 31, você aprenderá polimorfismo dinâmico e classes abstratas (`abstract`), tratando objetos derivados de forma genérica e flexível.',
            'codigo' => "Animal meuPet = new Gato();\nmeuPet.emitirSom(); // Miau"
        ]
    ];

    if (isset($catalogo[$num])) {
        return $catalogo[$num];
    }

    if ($num >= 1 && $num <= 40) {
        return [
            'titulo' => "Seção $num",
            'nome' => "Módulo da Seção $num de Java",
            'texto' => "Na Seção $num do Opus, você continuará sua trilha prática em Java! Você terá lições interativas, desafios de código e quizzes conceituais para consolidar seu aprendizado.",
            'codigo' => "// Acesse as 5 lições da Seção $num para avançar na Trilha Opus!\nSystem.out.println(\"Bons estudos no Opus!\");"
        ];
    }

    return null;
}

/**
 * Converte palavras numéricas em português para inteiro (ex: 'dois' -> 2, 'segunda' -> 2)
 */
function opus_extrair_numero_sessao(string $texto): ?int {
    $mapa = [
        'um' => 1, 'uma' => 1, 'primeira' => 1, 'primeiro' => 1, '1' => 1,
        'dois' => 2, 'duas' => 2, 'segunda' => 2, 'segundo' => 2, '2' => 2,
        'tres' => 3, 'três' => 3, 'terceira' => 3, 'terceiro' => 3, '3' => 3,
        'quatro' => 4, 'quarta' => 4, 'quarto' => 4, '4' => 4,
        'cinco' => 5, 'quinta' => 5, 'quinto' => 5, '5' => 5,
        'seis' => 6, 'sexta' => 6, 'sexto' => 6, '6' => 6,
        'sete' => 7, 'setima' => 7, 'sétima' => 7, 'setimo' => 7, 'sétimo' => 7, '7' => 7,
        'oito' => 8, 'oitava' => 8, 'oitavo' => 8, '8' => 8,
        'nove' => 9, 'nona' => 9, 'nono' => 9, '9' => 9,
        'dez' => 10, 'decima' => 10, 'décima' => 10, 'decimo' => 10, 'décimo' => 10, '10' => 10,
        'onze' => 11, '11' => 11, 'doze' => 12, '12' => 12, 'treze' => 13, '13' => 13,
        'quatorze' => 14, 'catorze' => 14, '14' => 14, 'quinze' => 15, '15' => 15,
        'dezesseis' => 16, '16' => 16, 'dezessete' => 17, '17' => 17, 'dezoito' => 18, '18' => 18,
        'dezenove' => 19, '19' => 19, 'vinte' => 20, '20' => 20,
        'trinta' => 30, '30' => 30, 'quarenta' => 40, '40' => 40
    ];

    if (preg_match('/(?:seção|sessão|secao|sessao|capítulo|capitulo|módulo|modulo|unidade)\s*(?:de\s+número\s+|número\s+|n[ºo]\s*)?([a-z0-9à-ú]+)/iu', $texto, $matches)) {
        $termo = mb_strtolower(trim($matches[1]), 'UTF-8');
        if (isset($mapa[$termo])) {
            return $mapa[$termo];
        }
        if (is_numeric($termo)) {
            return (int) $termo;
        }
    }

    if (preg_match('/(?:na|no|da|do)\s+([a-z0-9à-ú]+)\s+(?:seção|sessão|secao|sessao|capítulo|capitulo)/iu', $texto, $matches)) {
        $termo = mb_strtolower(trim($matches[1]), 'UTF-8');
        if (isset($mapa[$termo])) {
            return $mapa[$termo];
        }
    }

    return null;
}

/**
 * Processa os comandos especiais /browser, /goal e /boost (e consultas equivalentes)
 * integrando consulta ao banco de dados do OPUS em tempo real e pesquisa na Web.
 */
function opus_processar_comando_especial(string $msg, string $nome, array $relatorio = [], ?mysqli $conn = null, string $ctx = '', array $historico = []): ?string {
    $trimMsg = trim($msg);
    $m = mb_strtolower($trimMsg, 'UTF-8');

    // =========================================================================
    // 1. COMANDO /goal (Consulta de Metas, Missões Diárias, Liga e Conquistas)
    // =========================================================================
    if (preg_match('/^(\/goal|\/metas|\/objetivos|\/meta)\b(.*)$/iu', $trimMsg, $matches) ||
        preg_match('/(quais.*minhas metas no opus|minhas missões de hoje|minhas missoes de hoje|consultar metas|status das missões|status das missoes)/iu', $m)) {
        
        $complemento = trim($matches[2] ?? '');

        $sessConcluidas = (int) ($relatorio['sessoes_concluidas'] ?? 0);
        $sessRestantes  = (int) ($relatorio['sessoes_restantes'] ?? 40);
        $sessAtualNum   = (int) ($relatorio['sessao_atual_num'] ?? 1);
        $sessAtualNome  = $relatorio['sessao_atual_nome'] ?? "Sessão $sessAtualNum";
        $licSessFeitas  = (int) ($relatorio['licoes_feitas_sessao_atual'] ?? 0);
        $licSessRestam  = (int) ($relatorio['licoes_restantes_sessao_atual'] ?? 5);
        $pctSessao      = (int) ($relatorio['porcentagem_sessao_atual'] ?? 0);
        $totalFeitas    = (int) ($relatorio['total_licoes_feitas'] ?? 0);
        $pctGeral       = $relatorio['porcentagem_concluida'] ?? 0;
        $diasFogo       = (int) ($relatorio['dias_fogo'] ?? 0);
        $xpTotal        = (int) ($relatorio['xp'] ?? 0);

        // Missões diárias
        $missoes = $relatorio['missoes_diarias'] ?? [];
        $qtdMissoesOk = (int) ($relatorio['missoes_concluidas_hoje'] ?? 0);
        $linhasMissoes = "";
        if (!empty($missoes)) {
            foreach ($missoes as $md) {
                $icone = $md['completo'] ? "✅" : "⏳";
                $linhasMissoes .= "• {$icone} **{$md['titulo']}:** {$md['atual']}/{$md['meta']} (**{$md['porcentagem']}%**)\n";
            }
        } else {
            $xpHoje = (int) ($relatorio['xp_hoje'] ?? 0);
            $licHoje = (int) ($relatorio['licoes_hoje'] ?? 0);
            $linhasMissoes .= "• " . ($xpHoje >= 50 ? "✅" : "⏳") . " **Ganhe 50 de XP hoje:** {$xpHoje}/50\n";
            $linhasMissoes .= "• " . ($licHoje >= 2 ? "✅" : "⏳") . " **Conclua 2 lições hoje:** {$licHoje}/2\n";
        }

        // Liga e Ranking Global
        $divisao = ucfirst($relatorio['liga_divisao'] ?? 'Bronze');
        $posLiga = (int) ($relatorio['liga_posicao'] ?? 1);
        $totLiga = (int) ($relatorio['liga_total_grupo'] ?? 1);
        $xpLiga  = (int) ($relatorio['liga_xp_semana'] ?? 0);
        $xpSubir = (int) ($relatorio['liga_xp_necessario_subir'] ?? 0);
        $posGlobal = (int) ($relatorio['ranking_global_posicao'] ?? 1);
        $totGlobal = (int) ($relatorio['ranking_global_total'] ?? 1);

        $statusLiga = ($posLiga <= 12)
            ? "✅ No **Top 12 (Zona de Promoção)** — {$posLiga}º lugar de {$totLiga} na **Liga {$divisao}** ({$xpLiga} XP semanal)"
            : "⏳ **{$posLiga}º lugar** de {$totLiga} na **Liga {$divisao}** — faltam **+{$xpSubir} XP** para entrar no Top 12";

        // Próximo Troféu
        $proxTrofeu = $relatorio['proximo_trofeu'] ?? null;
        $qtdTrofeus = count($relatorio['trofeus_desbloqueados'] ?? []);
        $txtTrofeu = $proxTrofeu
            ? "🏅 **{$proxTrofeu['nome']}** — *{$proxTrofeu['descricao']}* ({$qtdTrofeus}/12 desbloqueados)"
            : "🏆 Todos os troféus do catálogo foram desbloqueados!";

        $resp = "🎯 **Consulta `/goal` — Painel de Metas & Objetivos de {$nome}:**\n\n" .
                "**1. Missões Diárias de Hoje ({$qtdMissoesOk}/3 concluídas):**\n" .
                $linhasMissoes . "\n" .
                "**2. Meta da Sessão Atual (Sessão {$sessAtualNum}: {$sessAtualNome}):**\n" .
                "• Progresso na sessão: **{$licSessFeitas} de 5 lições** (**{$pctSessao}%**) — faltam **{$licSessRestam} lições** para fechar a seção.\n" .
                "• Progresso no curso completo: **{$totalFeitas} de 200 lições** (**{$pctGeral}%** concluído | restam **{$sessRestantes} de 40 sessões**).\n\n" .
                "**3. Meta de Competição (Liga & Ranking Global):**\n" .
                "• {$statusLiga}\n" .
                "• Ranking Global OPUS: **{$posGlobal}º lugar** de {$totGlobal} estudantes (**{$xpTotal} XP** total | 🔥 **{$diasFogo} Dias de Fogo**).\n\n" .
                "**4. Próxima Conquista na Mira:**\n" .
                "• {$txtTrofeu}";

        if ($complemento !== '') {
            $webExtra = opus_consultar_web($complemento);
            if (!empty($webExtra['sucesso'])) {
                $resp .= "\n\n📌 **Sobre \"{$webExtra['titulo']}\":**\n{$webExtra['extrato']}";
            }
        }

        return $resp;
    }

    // =========================================================================
    // 2. COMANDO /boost (Impulso de XP, Baús Liberados, Dica e Resumo da Lição)
    // =========================================================================
    if (preg_match('/^(\/boost|\/turbo|\/impulso)\b(.*)$/iu', $trimMsg, $matches) ||
        preg_match('/(me d[aá] um boost|ativar boost|ganhar xp r[aá]pido|como ganhar muito xp|dica do desafio atual|me ajuda nessa lição|me ajuda nessa licao)/iu', $m)) {
        
        $foco = trim($matches[2] ?? '');

        $sessAtualNum = (int) ($relatorio['sessao_atual_num'] ?? 1);
        $sessAtualNome = $relatorio['sessao_atual_nome'] ?? "Sessão $sessAtualNum";
        $licAtualNum = (int) ($relatorio['licao_atual_num'] ?? 1);
        $vidas = (int) ($relatorio['vidas'] ?? 3);
        $diasFogo = (int) ($relatorio['dias_fogo'] ?? 0);
        $xpHoje = (int) ($relatorio['xp_hoje'] ?? 0);
        $bausDisp = $relatorio['baus_disponiveis'] ?? [];
        $licaoDb = $relatorio['licao_atual_db'] ?? null;
        $desafioDb = $relatorio['desafio_atual_db'] ?? null;

        // Se o usuário pediu boost de uma seção específica ou está navegando em uma seção/lição no $ctx
        $numPedido = opus_extrair_numero_sessao($foco);
        $licPedida = null;
        if ($numPedido === null && $foco === '' && !empty($ctx)) {
            $numCtx = opus_extrair_numero_sessao($ctx);
            if ($numCtx !== null) {
                $numPedido = $numCtx;
                if (preg_match('/li[çc][ãa]o\s*(\d+)/iu', $ctx, $mLicCtx)) {
                    $licPedida = max(1, min(5, (int) $mLicCtx[1]));
                }
            }
        }

        if ($numPedido !== null && $conn) {
            $sessAtualNum = $numPedido;
            if ($licPedida !== null) {
                $licAtualNum = $licPedida;
            }
            $ementaPedida = opus_obter_ementa_sessao($numPedido);
            $sessAtualNome = $ementaPedida['nome'] ?? "Seção $numPedido";
            if ($stmtL = $conn->prepare("SELECT titulo, texto_explicativo, codigo_exemplo FROM licoes WHERE unidade_numero = ? AND licao_numero = ? LIMIT 1")) {
                $stmtL->bind_param("ii", $numPedido, $licAtualNum);
                $stmtL->execute();
                $licaoDb = $stmtL->get_result()->fetch_assoc() ?: $licaoDb;
                $stmtL->close();
            }
            if ($stmtD = $conn->prepare("SELECT badge, heading, intro, missao, alvo_visual, dica_texto, dica_codigo, arquivo, codigo_inicial FROM desafios WHERE unidade_numero = ? LIMIT 1")) {
                $stmtD->bind_param("i", $numPedido);
                $stmtD->execute();
                $desafioDb = $stmtD->get_result()->fetch_assoc() ?: $desafioDb;
                $stmtD->close();
            }
        }

        $txtBaus = !empty($bausDisp)
            ? "🎁 **BAÚS DISPONÍVEIS AGORA:** Você tem baú(s) liberado(s) sem resgatar na(s) **Seção(ões) " . implode(", ", $bausDisp) . "**! Abra na Trilha para ganhar **+100 XP e Vidas** instantaneamente!"
            : "🎁 **Baús de Recompensa:** Conclua a 3ª lição da **Sessão {$sessAtualNum}** para destravar o próximo Baú de bônus (+XP e corações)!";

        $xpFaltaMeta = max(0, 50 - $xpHoje);
        $txtMissaoBoost = ($xpFaltaMeta > 0)
            ? "⚡ **Boost Diário:** Faltam apenas **+{$xpFaltaMeta} XP** hoje para bater sua meta diária de XP (1 lição perfeita já garante **+50 XP**!)."
            : "🔥 **Meta de XP batida hoje (+{$xpHoje} XP)!** Cada nova lição 3/3 soma +50 XP na sua Liga Semanal!";

        $blocoLicao = "";
        if ($licaoDb) {
            $resumoTeoria = trim(preg_replace('/\s+/', ' ', $licaoDb['texto_explicativo'] ?? ''));
            if (mb_strlen($resumoTeoria, 'UTF-8') > 320) {
                $resumoTeoria = mb_substr($resumoTeoria, 0, 320, 'UTF-8') . '...';
            }
            $codEx = trim($licaoDb['codigo_exemplo'] ?? '');
            $blocoLicao = "📘 **Resumo Turbo — {$licaoDb['titulo']} (Sessão {$sessAtualNum}, Lição {$licAtualNum}):**\n" .
                          "{$resumoTeoria}\n" .
                          ($codEx !== '' ? "```java\n{$codEx}\n```\n" : "");
        } else {
            $em = opus_obter_ementa_sessao($sessAtualNum);
            if ($em) {
                $blocoLicao = "📘 **Resumo Turbo — {$em['titulo']} ({$em['nome']}):**\n{$em['texto']}\n```java\n{$em['codigo']}\n```\n";
            }
        }

        $blocoDesafio = "";
        if ($desafioDb) {
            $missaoLimpa = strip_tags($desafioDb['missao'] ?? '');
            $dicaCod = trim($desafioDb['dica_codigo'] ?? '');
            $alvo = trim($desafioDb['alvo_visual'] ?? '');
            $blocoDesafio = "\n💻 **Cola Inteligente do Desafio IDE (Lição 5 — {$desafioDb['heading']}):**\n" .
                            "• **Missão:** {$missaoLimpa}\n" .
                            "• **Saída esperada no terminal:** `{$alvo}`\n" .
                            ($dicaCod !== '' ? "• **Código-chave da solução:**\n```java\n{$dicaCod}\n```" : "");
        }

        $blocoExtra = "";
        if ($foco !== '' && $numPedido === null) {
            $webBoost = opus_consultar_web($foco);
            if (!empty($webBoost['sucesso'])) {
                $blocoExtra = "\n\n🌐 **Boost de Conhecimento — {$webBoost['titulo']} ({$webBoost['fonte']}):**\n{$webBoost['extrato']}";
            }
        }

        return "🚀 **Modo `/boost` Ativado para {$nome}!**\n\n" .
               "**1. Oportunidades Imediatas de XP & Status:**\n" .
               "• {$txtBaus}\n" .
               "• {$txtMissaoBoost}\n" .
               "• **Status Atual:** ❤️ **{$vidas}/3 Vidas** | 🔥 **{$diasFogo} Dias de Fogo**\n\n" .
               $blocoLicao .
               $blocoDesafio .
               $blocoExtra;
    }

    // =========================================================================
    // 3. COMANDO /browser (Pesquisa Web Livre + Consulta ao Banco de Dados)
    // =========================================================================
    if (preg_match('/^(\/browser|\/busca|\/web|\/pesquisar|\/search)\b(.*)$/iu', $trimMsg, $matches)) {
        $termoBusca = trim($matches[2] ?? '');

        if ($termoBusca === '') {
            $sessAtualNum = (int) ($relatorio['sessao_atual_num'] ?? 1);
            $sessAtualNome = $relatorio['sessao_atual_nome'] ?? "Java";
            $webDefault = opus_consultar_web("Linguagem de programação Java");
            $resumoWiki = !empty($webDefault['extrato']) ? $webDefault['extrato'] : "Java é uma linguagem de programação orientada a objetos, multiplataforma e uma das mais utilizadas no mundo corporativo.";

            return "🌐 **Modo `/browser` — Pesquisa Livre na Web & Banco de Dados OPUS:**\n\n" .
                   "O comando `/browser` permite consultar **qualquer assunto da internet, enciclopédia, tecnologia, ciência, história ou dados internos do banco OPUS** sem nenhuma restrição!\n\n" .
                   "🔎 **Como usar:**\n" .
                   "• Digite `/browser <qualquer tema>` (ex: `/browser Inteligência Artificial`, `/browser Python`, `/browser Albert Einstein`, `/browser Seção 5`).\n" .
                   "• Ou simplesmente faça **qualquer pergunta direta** no chat!\n\n" .
                   "📖 **Destaque em Tempo Real (Wikipédia — Java & Seu Módulo Atual: {$sessAtualNome}):**\n" .
                   "{$resumoWiki}";
        }

        $infoMat = opus_resolver_matematica($termoBusca);
        $infoData = opus_resolver_data_hora($termoBusca);
        $infoDb = opus_consultar_banco_dinamico($conn, (int)($_SESSION['user_id'] ?? 1), $termoBusca);
        $infoWeb = opus_consultar_web($termoBusca);

        // Utiliza o Motor LLM Generativo com RAG completo (Web + Tabelas + Notícias + Banco OPUS) para responder com inteligência total
        $groqKeyCfg = obter_groq_api_key();
        $respostaLlmBrowser = chamar_api_groq($groqKeyCfg, $termoBusca, $nome, $ctx, $relatorio, $historico, $conn);
        if ($respostaLlmBrowser !== null && trim($respostaLlmBrowser) !== '') {
            $cabecalho = "🌐 **Pesquisa `/browser` — *\"" . htmlspecialchars($termoBusca) . "\"***\n\n";
            $rodapeFonte = (!empty($infoWeb['url']) && mb_stripos($respostaLlmBrowser, $infoWeb['url']) === false)
                ? "\n\n🔗 **Fonte consultada em tempo real:** {$infoWeb['url']}"
                : "";
            return $cabecalho . trim($respostaLlmBrowser) . $rodapeFonte;
        }

        $partesResp = ["🌐 **Consulta `/browser` para: *\"" . htmlspecialchars($termoBusca) . "\"***\n"];

        if ($infoMat !== null) {
            $partesResp[] = $infoMat;
        }
        if ($infoData !== null) {
            $partesResp[] = $infoData;
        }

        if (!empty($infoDb)) {
            $partesResp[] = "🗄️ **Dados Encontrados no Banco de Dados OPUS:**\n" . $infoDb;
        }

        if (!empty($infoWeb['sucesso'])) {
            if (!empty($infoWeb['extrato'])) {
                $partesResp[] = "📖 **{$infoWeb['titulo']} ({$infoWeb['fonte']}):**\n" . $infoWeb['extrato'];
            }
            if (!empty($infoWeb['tabelas'])) {
                $partesResp[] = "📊 **Dados & Vencedores / Tabelas Oficiais ({$infoWeb['titulo']}):**\n" . $infoWeb['tabelas'];
            }
            if (!empty($infoWeb['noticias']) && count($infoWeb['noticias']) > 0) {
                $partesResp[] = "📰 **Notícias & Atualizações Recentes:**\n" . implode("\n", array_slice($infoWeb['noticias'], 0, 5));
            }
            if (!empty($infoWeb['web_snippets']) && count($infoWeb['web_snippets']) > 0) {
                $partesResp[] = "🔎 **Resultados da Web:**\n" . implode("\n", array_slice($infoWeb['web_snippets'], 0, 4));
            } elseif (!empty($infoWeb['snippets']) && count($infoWeb['snippets']) > 1) {
                $partesResp[] = "🔎 **Tópicos Relacionados Encontrados:**\n" . implode("\n", array_slice($infoWeb['snippets'], 0, 3));
            }
            if (!empty($infoWeb['url'])) {
                $partesResp[] = "🔗 **Fonte consultada:** {$infoWeb['url']}";
            }
        }

        if (count($partesResp) === 1) {
            $partesResp[] = "Não encontrei um artigo específico com esse nome exato na enciclopédia externa, mas posso te ajudar com qualquer explicação, cálculo ou código sobre **\"" . htmlspecialchars($termoBusca) . "\"**! Tente especificar o termo ou pergunte diretamente o que deseja saber.";
        }

        return implode("\n\n", $partesResp);
    }

    return null;
}

/**
 * Avalia expressões matemáticas diretas e isoladas na pergunta do usuário (ex: "Quanto é 25 * 48?", "raiz quadrada de 144", "20% de 350")
 */
function opus_resolver_matematica(string $msg): ?string {
    $m = mb_strtolower(trim($msg), 'UTF-8');

    // Porcentagem isolada: "quanto é X% de Y?"
    if (preg_match('/^(?:quanto\s+(?:é|e|dá|da|vale)\s+|calcule\s+|calcular\s+|qual\s+(?:é\s+|e\s+)?)?(\d+(?:[.,]\d+)?)\s*%\s*de\s*(\d+(?:[.,]\d+)?)[\?!\.\s]*$/iu', $m, $mat)) {
        $pct = (float) str_replace(',', '.', $mat[1]);
        $val = (float) str_replace(',', '.', $mat[2]);
        $res = ($pct / 100.0) * $val;
        $resFmt = rtrim(rtrim(number_format($res, 4, ',', '.'), '0'), ',');
        return "🧮 **Cálculo Matemático:**\n\n**{$mat[1]}% de {$mat[2]}** é igual a **{$resFmt}**!\n\n• **Fórmula:** `({$pct} / 100) × {$val} = {$res}`";
    }

    // Raiz quadrada isolada: "qual a raiz quadrada de X?"
    if (preg_match('/^(?:quanto\s+(?:é|e|dá|da|vale)\s+|calcule\s+|calcular\s+|qual\s+(?:é\s+|e\s+)?(?:a\s+)?)?raiz\s*(?:quadrada)?\s*de\s*(\d+(?:[.,]\d+)?)[\?!\.\s]*$/iu', $m, $mat)) {
        $val = (float) str_replace(',', '.', $mat[1]);
        $res = sqrt($val);
        $resFmt = rtrim(rtrim(number_format($res, 4, ',', '.'), '0'), ',');
        return "🧮 **Cálculo Matemático:**\n\nA raiz quadrada de **{$mat[1]}** (`√{$mat[1]}`) é igual a **{$resFmt}**!\n\nEm Java: `Math.sqrt({$val}); // {$res}`";
    }

    // Expressão aritmética pura: ex "quanto é 25 * 48", "125 + 375", "100 / 4"
    $exprCandidate = preg_replace('/^(?:quanto\s+(?:é|e|dá|da|vale)|calcule|calcular|resolva|resolver|qual\s+o\s+resultado\s+de)\s+/iu', '', $m);
    $exprCandidate = trim($exprCandidate, "?!.= ");
    $exprCandidate = str_replace(['x', '×', '÷', ','], ['*', '*', '/', '.'], $exprCandidate);

    if (preg_match('/^[\d\s\+\-\*\/\(\)\.\^]+$/', $exprCandidate) && preg_match('/[\+\-\*\/\^]/', $exprCandidate) && preg_match('/\d/', $exprCandidate)) {
        $safeExpr = str_replace('^', '**', $exprCandidate);
        try {
            $resultado = @eval("return ({$safeExpr});");
            if (is_numeric($resultado) && !is_nan((float)$resultado) && !is_infinite((float)$resultado)) {
                $resFmt = is_float($resultado) && floor($resultado) != $resultado
                    ? rtrim(rtrim(number_format((float)$resultado, 4, ',', '.'), '0'), ',')
                    : (string) $resultado;
                return "🧮 **Resultado do Cálculo:**\n\n`{$exprCandidate}` = **{$resFmt}**";
            }
        } catch (\Throwable $e) {}
    }

    return null;
}

/**
 * Resolve consultas diretas de data, hora e contagem regressiva de calendário (ex: Natal, Ano Novo, dia de hoje)
 */
function opus_resolver_data_hora(string $msg): ?string {
    $m = mb_strtolower(trim($msg), 'UTF-8');

    $diasSemana = [
        0 => 'Domingo', 1 => 'Segunda-feira', 2 => 'Terça-feira',
        3 => 'Quarta-feira', 4 => 'Quinta-feira', 5 => 'Sexta-feira', 6 => 'Sábado'
    ];
    $hoje = new \DateTime('today');
    $agora = new \DateTime('now');
    $anoAtual = (int) $hoje->format('Y');
    $diaSemanaNome = $diasSemana[(int) $hoje->format('w')] ?? '';

    if (preg_match('/^(?:quantos dias.*(?:natal)|falta.*quanto.*natal)[\?!\.\s]*$/iu', $m)) {
        $natal = new \DateTime("{$anoAtual}-12-25");
        if ($natal < $hoje) {
            $natal = new \DateTime(($anoAtual + 1) . "-12-25");
        }
        $dias = (int) $hoje->diff($natal)->days;
        return "🎄 **Contagem Regressiva para o Natal:**\n\n" .
               "Hoje é **{$hoje->format('d/m/Y')} ({$diaSemanaNome})** e faltam exatamente **{$dias} dias** para o Natal (**25/12/{$natal->format('Y')}**)! 🎅✨";
    }

    if (preg_match('/^(?:quantos dias.*(?:ano novo|fim do ano|acabar o ano|reveillon|réveillon))[\?!\.\s]*$/iu', $m)) {
        $anoNovo = new \DateTime(($anoAtual + 1) . "-01-01");
        $dias = (int) $hoje->diff($anoNovo)->days;
        return "🎆 **Contagem Regressiva para o Ano Novo:**\n\n" .
               "Hoje é **{$hoje->format('d/m/Y')} ({$diaSemanaNome})** e faltam **{$dias} dias** para a chegada de **" . ($anoAtual + 1) . "**! 🥂✨";
    }

    if (preg_match('/^(?:que dia [eé] hoje|qual (?:[eé] )?a data de hoje|que horas s[aã]o(?: agora)?|qual o hor[aá]rio atual)[\?!\.\s]*$/iu', $m)) {
        return "🕒 **Data e Hora Atual:**\n\n" .
               "• **Data:** **{$agora->format('d/m/Y')} ({$diaSemanaNome})**\n" .
               "• **Horário do Servidor:** **{$agora->format('H:i:s')}**";
    }

    return null;
}

/**
 * Verifica se a mensagem é um comando especial (/goal, /boost, /browser) ou uma consulta
 * direta sobre os dados internos da conta do estudante no banco OPUS (sessões restantes no curso,
 * porcentagem do curso, XP para subir de liga no OPUS, lições feitas hoje, troféus do aluno, baús do aluno, vidas).
 * Retorna null para qualquer pergunta aberta/geral para que o Motor LLM Generativo responda livremente.
 */
function opus_resposta_rapida_plataforma(string $msg, string $nome, string $ctx = '', array $relatorio = [], ?mysqli $conn = null, array $historico = []): ?string {
    $m = mb_strtolower(trim($msg), 'UTF-8');

    // 0. COMANDOS ESPECIAIS (/browser, /goal, /boost)
    $respostaComando = opus_processar_comando_especial($msg, $nome, $relatorio, $conn, $ctx, $historico);
    if ($respostaComando !== null) {
        return $respostaComando;
    }

    // 0.1 PERGUNTAS ESPECIFICAMENTE SOBRE VALOR, PREÇO OU GRATUIDADE DA PLATAFORMA / CURSO OPUS
    $mencionaOpusOuCurso = (bool) preg_match('/\b(opus|esse curso|este curso|o curso|plataforma)\b/iu', $m);
    if (($mencionaOpusOuCurso && preg_match('/\b(valor|quanto custa|preço|preco|comprar|pagamento|mensalidade|custo|pago|grátis|gratis|gratuito)\b/iu', $m)) ||
        preg_match('/(preço do curso|preco do curso|valor do curso|custo do curso|comprar o curso|quanto custa o curso|quanto é o curso|quanto e o curso)/iu', $m)) {
        return "💚 **O OPUS é 100% Gratuito, $nome!**\n\n" .
               "Você não paga absolutamente nada (R$ 0,00) para estudar aqui:\n" .
               "• **Sem mensalidades ou taxas ocultas:** Todas as **40 Seções** e **200 Lições interativas** são totalmente abertas.\n" .
               "• **IDE Java Online Integrada:** Pratique código real no navegador gratuitamente.\n" .
               "• **Tutor Opi IA, Ligas, Ranking e Certificados:** Tudo liberado para apoiar a sua jornada como desenvolvedor(a)!\n\n" .
               "💡 Se você quiser consultar médias salariais do mercado de TI ou preços de certificações oficiais (como Oracle OCP), é só me perguntar!";
    }

    // 0.2 CÁLCULOS MATEMÁTICOS E DATAS / CALENDÁRIO DIRETOS E ISOLADOS
    $respostaMat = opus_resolver_matematica($msg);
    if ($respostaMat !== null) {
        return $respostaMat;
    }
    $respostaData = opus_resolver_data_hora($msg);
    if ($respostaData !== null) {
        return $respostaData;
    }

    // REGRA 1: CONSULTA DE ATIVIDADE DIÁRIA DO ALUNO (Ex: "Hoje eu fiz quantas lições?", "Quantas lições fiz hoje?")
    if (preg_match('/(hoje.*quantas.*liç|quantas.*liç.*hoje|hoje.*fiz quantas|fiz quantas.*hoje|quanto.*estudei.*hoje|lições.*feitas.*hoje|licoes.*feitas.*hoje)/iu', $m) ||
        (preg_match('/\b(hoje)\b/iu', $m) && preg_match('/\b(lição|licao|lições|licoes)\b/iu', $m) && preg_match('/\b(fiz|concluí|conclui|completei|quantas|quantos)\b/iu', $m))) {
        
        $licoes_hoje = (int) ($relatorio['licoes_hoje'] ?? 0);
        $acertos_hoje = (int) ($relatorio['acertos_hoje'] ?? 0);
        $xp_hoje = (int) ($relatorio['xp_hoje'] ?? 0);
        $dias_fogo = (int) ($relatorio['dias_fogo'] ?? 0);

        if ($licoes_hoje > 0) {
            $msgLi = $licoes_hoje === 1 ? "1 lição" : "$licoes_hoje lições";
            return "📅 **Suas Lições de Hoje, $nome:**\n\n" .
                   "Você concluiu **$msgLi** hoje! 🚀\n\n" .
                   "• **Acertos acumulados hoje:** **$acertos_hoje acertos**\n" .
                   ($xp_hoje > 0 ? "• **XP conquistado hoje:** **+$xp_hoje XP**\n" : "") .
                   "• **Sequência ativa:** **$dias_fogo Dias de Fogo** 🔥\n\n" .
                   "Excelente dedicação! Continue praticando para manter sua ofensiva viva e subir no ranking da sua Liga!";
        } else {
            return "📅 **Suas Lições de Hoje, $nome:**\n\n" .
                   "Você ainda **não concluiu nenhuma lição hoje** (0 lições concluídas até agora). ⏳\n\n" .
                   "• **Sua sequência atual:** **$dias_fogo Dias de Fogo** 🔥\n" .
                   "• **Dica do Opi:** Conclua pelo menos 1 lição ainda hoje para manter a sua chama de fogo acesa e não perder a ofensiva!\n\n" .
                   "Bora abrir uma lição e praticar Java agora? 💻✨";
        }
    }

    // REGRA 2: CONSULTA DE CONTEÚDO DA SESSÃO / SEÇÃO DO CURSO OPUS (Ex: "O que eu vou aprender na sessão dois?")
    $ehTopicoExternoSessao = (bool) preg_match('/(livro|novela|filme|série|serie|anime|mang[aá]|one piece|naruto|eleitoral|zona|terapia|cinema|tarde|câmara|camara|senado|congresso|lei|artigo|constitui[çc][ãa]o|clt|código penal|codigo penal|pol[íi]cia|ex[ée]rcito)/iu', $m);
    if (!$ehTopicoExternoSessao &&
        preg_match('/(o que.*(?:vou|vamos|irei|vai).*aprender|o que tem|o que ensina|qual o conteúdo|qual o conteudo|conteúdo d[ao]|conteudo d[ao]|ementa)/iu', $m) &&
        preg_match('/\b(sessão|sessao|seção|secao|capítulo|capitulo|módulo|modulo|unidade)\b/iu', $m)) {
        
        $numSessao = opus_extrair_numero_sessao($msg);
        if ($numSessao !== null) {
            $ementa = opus_obter_ementa_sessao($numSessao);
            $extraDb = opus_consultar_banco_dinamico($conn, (int)($_SESSION['user_id'] ?? 1), "seção $numSessao");
            if ($ementa) {
                return "📚 **{$ementa['titulo']} — {$ementa['nome']}**\n\n" .
                       "{$ementa['texto']}\n\n" .
                       "💻 **Exemplo do que você pratica nesta seção:**\n" .
                       "```java\n" .
                       "{$ementa['codigo']}\n" .
                       "```\n\n" .
                       (!empty($extraDb) ? "🗂️ **Detalhes no Banco de Dados:**\n{$extraDb}\n\n" : "") .
                       "🎯 Cada seção do Opus possui **5 Lições interativas** com teoria e desafios de código. Pronto para mandar ver nos estudos?";
            }
        }
    }

    // Se perguntar diretamente sobre uma sessão/seção numerada do curso Opus (ex: "Sessão 2", "Me fale sobre a seção dois")
    $numDetectado = opus_extrair_numero_sessao($msg);
    if (!$ehTopicoExternoSessao && $numDetectado !== null &&
        preg_match('/^(?:me\s+(?:fale|explique|mostre|diga)\s+(?:sobre\s+)?(?:a\s+|o\s+)?)?(?:sessão|sessao|seção|secao|unidade)\s+[a-z0-9à-ú]+(?:\s+do\s+(?:opus|curso))?[\?!\.\s]*$/iu', $m)) {
        $ementa = opus_obter_ementa_sessao($numDetectado);
        $extraDb = opus_consultar_banco_dinamico($conn, (int)($_SESSION['user_id'] ?? 1), "seção $numDetectado");
        if ($ementa) {
            return "📚 **{$ementa['titulo']} — {$ementa['nome']}**\n\n" .
                   "{$ementa['texto']}\n\n" .
                   "💻 **Código de exemplo da seção:**\n" .
                   "```java\n" .
                   "{$ementa['codigo']}\n" .
                   "```" .
                   (!empty($extraDb) ? "\n\n🗂️ **Lições Cadastradas no Banco:**\n{$extraDb}" : "");
        }
    }

    // REGRA 3: CONSULTA DIRETA DE SESSÕES / LIÇÕES FALTANTES PARA TERMINAR O CURSO E PROGRESSO PESSOAL NO OPUS
    $mencionaUnidadeOpus = (bool) preg_match('/\b(sessão|sessao|sessões|sessoes|seção|secao|seções|secoes|lição|licao|lições|licoes|curso|opus|trilha|módulo|modulo|módulos|modulos)\b/iu', $m);
    $mencionaFaltaTerminar = (bool) (
        preg_match('/\b(falta|faltam|resta|restam)\b.*\b(terminar|concluir|acabar|fechar|curso|opus|trilha|eu|para mim)\b/iu', $m) ||
        preg_match('/\b(quantas|quantos)\s+(?:sessões|sessoes|seções|secoes|lições|licoes|capítulos|capitulos|módulos|modulos)\s+(?:faltam|restam|eu\s+fiz|eu\s+conclu[íi]|já\s+fiz|ja\s+fiz)\b/iu', $m)
    );
    if ((!$ehTopicoExternoSessao && $mencionaUnidadeOpus && $mencionaFaltaTerminar) ||
        preg_match('/(onde parei|qual (?:minha|meu) (?:sessão|sessao|capítulo|capitulo|seção|secao)|meu progresso|estou em qual capítulo|estou em qual sessão|meu relatório|meu relatorio)/iu', $m)) {
        
        if (!empty($relatorio)) {
            $sessConcluidas = (int) ($relatorio['sessoes_concluidas'] ?? 0);
            $sessRestantes  = (int) ($relatorio['sessoes_restantes'] ?? 40);
            $sessAtualNum   = (int) ($relatorio['sessao_atual_num'] ?? 1);
            $sessAtualNome  = $relatorio['sessao_atual_nome'] ?? "Sessão $sessAtualNum";
            $licSessFeitas  = (int) ($relatorio['licoes_feitas_sessao_atual'] ?? 0);
            $licSessRestam  = (int) ($relatorio['licoes_restantes_sessao_atual'] ?? 5);
            $totalFeitas    = (int) ($relatorio['total_licoes_feitas'] ?? 0);
            $totalRestam    = (int) ($relatorio['licoes_restantes_total'] ?? 200);
            $pct            = $relatorio['porcentagem_concluida'] ?? 0;

            if ($sessConcluidas >= 40) {
                return "🎓 **Parabéns, $nome! Você concluiu todas as 40 sessões do curso!** 🚀\n\n" .
                       "• **Sessões concluídas:** 40 de 40 (100% do curso Opus completo!)\n" .
                       "• **Total de lições:** 200 de 200 lições dominadas.\n\n" .
                       "Você é agora um verdadeiro mestre e **Arquiteto Java**! Continue praticando lições diárias para manter seus Dias de Fogo e liderar sua Liga!";
            }

            if ($sessConcluidas === 0 && $sessAtualNum === 1) {
                return "📊 **Seu Progresso no Opus, $nome:**\n\n" .
                       "Faltam **40 sessões** para você terminar o curso, porque você nem terminou a primeira! ⏳\n\n" .
                       "• **Sessão atual:** Você está na **Sessão 1: $sessAtualNome**.\n" .
                       "• **Nesta sessão:** Você concluiu **$licSessFeitas de 5 lições** (faltam **$licSessRestam lições** para fechar a 1ª sessão e destravar a próxima).\n" .
                       "• **Total no curso:** **$totalFeitas de 200 lições** concluídas (**$pct%** do curso).\n\n" .
                       "🔥 Bora fazer uma lição agora para concluir sua primeira sessão e manter seus **{$relatorio['dias_fogo']} Dias de Fogo** acesos?";
            }

            return "📊 **Seu Progresso no Opus, $nome:**\n\n" .
                   "• **Sessões restantes:** Restam **$sessRestantes sessões** para você terminar o curso (você já concluiu **$sessConcluidas de 40**).\n" .
                   "• **Sessão atual:** Você está na **Sessão $sessAtualNum: $sessAtualNome**.\n" .
                   "• **Nesta sessão:** Você concluiu **$licSessFeitas de 5 lições** (faltam **$licSessRestam lições** para avançar).\n" .
                   "• **Progresso geral:** **$totalFeitas de 200 lições** concluídas (**$pct%** do curso completo, faltam **$totalRestam lições**).\n\n" .
                   "🔥 Continue firme nos estudos para não apagar seus **{$relatorio['dias_fogo']} Dias de Fogo**!";
        }

        return "📊 O curso Opus possui **40 Sessões** com **5 Lições cada** (total de 200 lições).\n\nConsulte o seu **Painel de Progresso** no menu principal para visualizar a sua trilha completa em tempo real!";
    }

    // REGRA 4: CONSULTA DE PORCENTAGEM DO PROGRESSO DO ALUNO NO OPUS (Ex: "E por porcentagem?", "Qual a minha porcentagem?")
    $ehFollowupPorcentagem = (bool) preg_match('/^(?:e\s+)?(?:por|em|na|qual\s+a|quanto\s+em|e\s+a)\s*(?:porcentagem|porcento|por\s+cento|percentual|%)\s*[\?!\.]*$/iu', $m);
    $ehPorcentagemCurso = (bool) (
        preg_match('/(porcentagem|porcento|por cento|percentual|%)/iu', $m) &&
        preg_match('/\b(meu|minha|progresso|opus|trilha|sessão|sessao|seção|secao|concluí|conclui|completei)\b/iu', $m) &&
        !preg_match('/\b(bbb|votos|ibge|brasil|mundo|imposto|juros|infla[çc][ãa]o|desconto|bateria)\b/iu', $m)
    );
    if ($ehFollowupPorcentagem || $ehPorcentagemCurso) {
        
        $pctGeral = $relatorio['porcentagem_concluida'] ?? 0;
        $pctSessao = $relatorio['porcentagem_sessao_atual'] ?? 0;
        $totalFeitas = (int) ($relatorio['total_licoes_feitas'] ?? 0);
        $totalCurso = (int) ($relatorio['total_licoes_curso'] ?? 200);
        $sessAtualNum = (int) ($relatorio['sessao_atual_num'] ?? 1);
        $licFeitasSessao = (int) ($relatorio['licoes_feitas_sessao_atual'] ?? 0);

        return "📈 **Seu Progresso em Porcentagem, $nome:**\n\n" .
               "• **Curso Completo:** Você já concluiu **$pctGeral%** do curso Opus ($totalFeitas de $totalCurso lições totais concluídas).\n" .
               "• **Sessão Atual (Sessão $sessAtualNum):** Você está com **$pctSessao%** concluído nesta sessão ($licFeitasSessao de 5 lições feitas).\n\n" .
               "Cada lição que você conclui adiciona +0.5% ao seu progresso total do curso! 🚀";
    }

    // REGRA 5: CONSULTA DAS LIGAS DO OPUS E XP PARA SUBIR DE LIGA
    $ehLigaExterna = (bool) preg_match('/(campeões|campeoes|uefa|futebol|basquete|nba|justiça|justica|metálica|metalica|celular|mitose|meiose|fração|fracao|frações|fracoes|matemática|matematica|política|politica|territorial|lenda|legends|premier|la liga|bundesliga|libertadores|v[oô]lei|rocket|valorant|lol|fifa)/iu', $m);
    $ehLigaOpus = !$ehLigaExterna && (
        preg_match('/(subir de liga|subir de divisão|subir de divisao|minha liga|minha divisão|minha divisao|nas ligas|na liga|liga do opus|ligas do opus|liga semanal|zona de promoção|zona de promocao|zona de rebaixamento)/iu', $m) ||
        (preg_match('/\b(liga|ligas)\b/iu', $m) && preg_match('/\b(xp|subir|descer|rebaixar|promover|divisão|divisao|bronze|prata|ouro|safira|rubi|esmeralda|mestre|diamante|opus|meu|minha|estou|posição|posicao|grupo)\b/iu', $m))
    );
    if ($ehLigaOpus) {
        $divisao = ucfirst($relatorio['liga_divisao'] ?? 'Bronze');
        $posicao = (int) ($relatorio['liga_posicao'] ?? 1);
        $totalGrupo = (int) ($relatorio['liga_total_grupo'] ?? 1);
        $xpSemana = (int) ($relatorio['liga_xp_semana'] ?? 0);
        $xpNecessario = (int) ($relatorio['liga_xp_necessario_subir'] ?? 0);
        $xpCorte = (int) ($relatorio['liga_xp_corte_subida'] ?? 0);
        $podeSubir = (bool) ($relatorio['liga_pode_subir'] ?? true);

        $txtTopLiga = "";
        if (!empty($relatorio['liga_top5'])) {
            $linhasL = [];
            foreach ($relatorio['liga_top5'] as $lt) {
                $linhasL[] = "  {$lt['posicao']}º {$lt['nome']} — **{$lt['xp_semana']} XP**";
            }
            $txtTopLiga = "\n\n🏅 **Top Jogadores do Seu Grupo da Liga:**\n" . implode("\n", $linhasL);
        }

        if (!$podeSubir) {
            return "🏆 **Sua Situação nas Ligas, $nome:**\n\n" .
                   "Você está na **Liga Mestre**, a divisão máxima do Opus! 👑\n\n" .
                   "• **Posição atual:** **$posicao º lugar** no seu grupo com **$xpSemana XP** esta semana.\n" .
                   "• Como você já está na liga mais alta, não há divisões superiores para subir. Mantenha-se entre os melhores colocados para consolidar sua glória!" . $txtTopLiga;
        }

        if ($posicao <= 12) {
            return "🏆 **Sua Situação nas Ligas, $nome:**\n\n" .
                   "🎉 **Você já está na ZONA DE PROMOÇÃO para subir de liga!** 🚀\n\n" .
                   "• **Sua Divisão:** **Liga $divisao**\n" .
                   "• **Sua Posição:** **$posicao º lugar** de $totalGrupo participantes no seu grupo.\n" .
                   "• **Seu XP nesta semana:** **$xpSemana XP**\n" .
                   "• **Regra da Liga:** Os **12 primeiros colocados** do grupo sobem de divisão na virada semanal!\n\n" .
                   "💡 **Dica do Opi:** Continue fazendo lições para somar mais XP e garantir que nenhum rival te passe até o fechamento da semana!" . $txtTopLiga;
        } else {
            return "🏆 **Sua Situação nas Ligas, $nome:**\n\n" .
                   "Para conseguir subir da **Liga $divisao** para a próxima divisão, você precisa terminar a semana no **Top 12 (entre os 12 primeiros colocados)** do seu grupo!\n\n" .
                   "• **Sua Posição Atual:** **$posicao º lugar** de $totalGrupo participantes.\n" .
                   "• **Seu XP Semanal:** **$xpSemana XP**\n" .
                   "• **Linha de Corte (12º colocado):** Atualmente com **$xpCorte XP**.\n\n" .
                   "🎯 **Quanto XP você precisa ganhar:**\n" .
                   "Você precisa de pelo menos **+$xpNecessario XP** para ultrapassar a linha de corte e entrar na zona de promoção!\n\n" .
                   "💡 Cada lição concluída rende entre **10 e 50 XP**. Faça algumas lições hoje para subir posições no ranking!" . $txtTopLiga;
        }
    }

    // REGRA 6: CONSULTA DE RANKING GLOBAL DO OPUS (Exige referência ao OPUS ou à posição/XP do aluno)
    if (preg_match('/(ranking do opus|ranking global do opus|meu ranking|minha posi[çc][ãa]o no ranking|minha posi[çc][ãa]o global|quem tem mais xp|placar do opus|l[ií]der do ranking do opus|primeiro lugar no ranking do opus|top jogadores do opus)/iu', $m) ||
        (preg_match('/\branking\b/iu', $m) && preg_match('/\b(opus|meu|minha|estou|xp|alunos|estudantes)\b/iu', $m))) {
        $posGlobal = (int) ($relatorio['ranking_global_posicao'] ?? 1);
        $totGlobal = (int) ($relatorio['ranking_global_total'] ?? 1);
        $meuXp = (int) ($relatorio['xp'] ?? 0);
        $top5 = $relatorio['ranking_top5'] ?? [];

        $listaTop = "";
        foreach ($top5 as $rk) {
            $medalha = $rk['posicao'] === 1 ? "🥇" : ($rk['posicao'] === 2 ? "🥈" : ($rk['posicao'] === 3 ? "🥉" : "🏅"));
            $listaTop .= "• {$medalha} **{$rk['posicao']}º {$rk['nome']}** — **{$rk['xp']} XP** (🏆 {$rk['trofeus']} troféus | 🔥 {$rk['dias_fogo']} dias)\n";
        }

        return "🏆 **Consulta ao Ranking Global do OPUS:**\n\n" .
               "• **Sua Posição Global:** Você está em **{$posGlobal}º lugar** entre **{$totGlobal} estudantes** cadastrados, com **{$meuXp} XP** acumulados!\n\n" .
               "**Top 5 Líderes Globais em Tempo Real:**\n" .
               ($listaTop !== '' ? $listaTop : "• Consulte a aba Ranking no menu lateral!") . "\n" .
               "💡 Dica: Use `/boost` para descobrir como ganhar XP extra agora mesmo!";
    }

    // REGRA 7: CONSULTA REAL DE TROFÉUS E CONQUISTAS DO ALUNO NO BANCO OPUS
    if (preg_match('/(meus trof[eé]us|minhas conquistas|minhas medalhas|trof[eé]us do opus|conquistas do opus|desbloquear trof[eé]u|ganhar trof[eé]u)/iu', $m) ||
        (preg_match('/\b(troféu|trofeu|troféus|trofeus|conquistas)\b/iu', $m) && preg_match('/\b(meu|minha|meus|minhas|eu|tenho|desbloqueei|ganhei|faltam|opus|plataforma|curso|catálogo|catalogo)\b/iu', $m))) {
        $desbloq = $relatorio['trofeus_desbloqueados'] ?? [];
        $pend = $relatorio['trofeus_pendentes'] ?? [];

        $txtDesbloq = "";
        if (!empty($desbloq)) {
            foreach ($desbloq as $td) {
                $txtDesbloq .= "• ✅ **{$td['nome']}** ({$td['raridade']}) — *{$td['descricao']}*\n";
            }
        } else {
            $txtDesbloq = "• Você ainda não desbloqueou troféus. Conclua sua primeira lição para ganhar o troféu **Primeiro Passo**!\n";
        }

        $txtPend = "";
        foreach (array_slice($pend, 0, 5) as $tp) {
            $txtPend .= "• 🔒 **{$tp['nome']}** ({$tp['raridade']}) — *{$tp['descricao']}*\n";
        }

        return "🎖️ **Consulta de Conquistas & Troféus de $nome:**\n\n" .
               "**Troféus Desbloqueados (" . count($desbloq) . "):**\n" .
               $txtDesbloq . "\n" .
               (!empty($pend) ? "**Próximos Troféus para Desbloquear (" . count($pend) . " restantes):**\n" . $txtPend : "🎉 Parabéns! Você conquistou todos os troféus do catálogo!");
    }

    // REGRA 8: CONSULTA DE BAÚS DE RECOMPENSA DO OPUS EM TEMPO REAL
    if (!preg_match('/(felicidade|silvio santos|tesouro|pirata|caminh[ãa]o|moto|m[óo]vel|minecraft|clash|fortnite|zelda)/iu', $m) &&
        (preg_match('/(meus ba[úu]s|ba[úu]s? do opus|ba[úu]s? de recompensa|abrir ba[úu]|resgatar ba[úu]|ba[úu]s? dispon[íi]ve|ba[úu]s? liberad)/iu', $m) ||
         (preg_match('/\b(baú|bau|baús|baus|recompensa|recompensas)\b/iu', $m) && preg_match('/\b(opus|trilha|seção|secao|sessão|sessao|lição|licao|abrir|resgatar|meu|meus|minha|minhas|tenho|xp)\b/iu', $m)))) {
        $bausDisp = $relatorio['baus_disponiveis'] ?? [];
        $bausResg = (int) ($relatorio['baus_resgatados_qtd'] ?? 0);

        $statusBau = !empty($bausDisp)
            ? "🎉 **Você tem " . count($bausDisp) . " baú(s) liberado(s) esperando resgate na(s) Seção(ões): " . implode(", ", $bausDisp) . "!** Vá até a Trilha de Estudos e clique no baú para ganhar XP e Vidas!"
            : "No momento você não tem baús pendentes (você já resgatou **{$bausResg} baú(s)**). Complete a 3ª lição da sua seção atual para abrir o próximo!";

        return "🎁 **Consulta de Baús de Recompensa:**\n\n" .
               "• {$statusBau}\n\n" .
               "**Como funcionam os Baús no Opus:**\n" .
               "• Ao concluir a **3ª lição** de cada seção, o Baú daquela seção é destravado.\n" .
               "• Abrir o baú concede **+50 a +100 XP de bônus** e recupera **Corações de Vida**!";
    }

    // REGRA 9: CONSULTA DE EMPREGABILIDADE DO ALUNO COM BASE NO NÍVEL EM JAVA NO OPUS
    if (preg_match('/(com esse conhecimento|com esses conhecimentos|meu n[ií]vel|n[ií]vel em java|trabalhar com java|consigo (?:um|uma) (?:vaga|emprego|estágio|estagio)|conseguir (?:um|uma) (?:vaga|emprego|estágio|estagio)|vaga de (?:emprego|estágio|estagio))/iu', $m) ||
        (preg_match('/\b(vaga|vagas|emprego|estágio|estagio)\b/iu', $m) && preg_match('/\b(opus|curso|java|eu|meu|minha|consigo|posso)\b/iu', $m) && !preg_match('/(mitose|meiose|sono|doen[çc]a|c[âa]ncer|gravidez|desenvolvimento humano|piaget|freud|foguete|larva|borboleta)/iu', $m))) {
        if (!empty($relatorio)) {
            $capConcluidos = $relatorio['capitulos_concluidos'];
            $nivelJava     = $relatorio['nivel_java'];
            $pct           = $relatorio['porcentagem_concluida'];

            if ($capConcluidos >= 27) {
                return "💼 **Avaliação de Mercado & Empregabilidade:**\n\n" .
                       "🎯 **Seu Nível Atual:** **$nivelJava**\n\n" .
                       "✅ **Sim! Com essa bagagem você já tem a base técnica para vagas de Estágio e Júnior em Java!** 🚀\n\n" .
                       "Você já avançou até os módulos de **Programação Orientada a Objetos (POO)**, dominando sintaxe, controle de fluxo, coleções e arquitetura de classes.\n\n" .
                       "📌 **Dicas do Opi para conquistar sua vaga:**\n" .
                       "1. Publique 1 ou 2 projetos autorais no **GitHub** aplicando POO.\n" .
                       "2. Estude os fundamentos de **Spring Boot (APIs REST)** e **SQL (MySQL)**.\n" .
                       "3. Destaque seus certificados do Opus no seu **LinkedIn**!";
            }

            if ($capConcluidos >= 5) {
                return "💼 **Avaliação de Mercado & Empregabilidade:**\n\n" .
                       "🎯 **Seu Nível Atual:** **$nivelJava** (Progresso: **$pct%** do curso — **$capConcluidos de 40 seções**)\n\n" .
                       "💡 **Você consegue uma vaga agora?**\n" .
                       "• Você já tem uma **lógica de programação muito sólida**, ideal para processos seletivos de **Jovem Aprendiz de TI**, suporte técnico ou bolsas de iniciação!\n" .
                       "• Para vagas formais de **Estágio em Desenvolvimento Java ou Dev Júnior**, as empresas pedem domínio de **Coleções (HashMap/HashSet)** e **POO (Seções 27 a 40)**.\n\n" .
                       "🚀 Continue avançando na trilha para destravar seus certificados de etapa!";
            }

            return "💼 **Avaliação de Mercado & Empregabilidade:**\n\n" .
                   "🎯 **Seu Nível Atual:** **$nivelJava** (Progresso: **$pct%** do curso)\n\n" .
                   "💡 **Análise Sincera do Opi:**\n" .
                   "Você está construindo a base inicial de sintaxe e lógica, $nome. Para disputar vagas de **Estágio ou Dev Júnior**, o mercado exige domínio de laços de repetição, arrays/coleções e **Programação Orientada a Objetos (POO)**.\n\n" .
                   "🎯 **Plano de Ação:** Use o comando `/goal` para acompanhar suas metas diárias e `/boost` para acelerar seu avanço rumo às seções avançadas!";
        }
    }

    // REGRA 10: SISTEMA E MECÂNICAS ESPECÍFICAS DO OPUS (Vidas, Fogo, XP do aluno, Dicionário do Opus, Amigos)
    if (preg_match('/(minhas vidas|quantas vidas eu tenho|perdi vida|perder vida|recuperar vida|ganhar vida|sem vidas|vidas no opus|cora[çc][õo]es no opus)/iu', $m)) {
        $vidasAtuais = (int) ($relatorio['vidas'] ?? 3);
        return "❤️ **Status de Vidas de $nome: {$vidasAtuais}/3 Corações**\n\n" .
               "• Você possui até **3 Vidas (corações)**.\n" .
               "• Se errar todas as questões de uma lição (0 acertos), você perde **1 vida**.\n" .
               "• **Como recuperar:** Uma vida é restaurada a cada **5 horas** automaticamente, ou abrindo um **Baú de Recompensas** na trilha!";
    }

    if (preg_match('/(dias de fogo|minha ofensiva|minha chama|meu fogo|perder o fogo|manter o fogo)/iu', $m)) {
        $fogoAtual = (int) ($relatorio['dias_fogo'] ?? 0);
        return "🔥 **Sua Ofensiva Atual: {$fogoAtual} Dias de Fogo!**\n\n" .
               "• Os **Dias de Fogo** registram a sua sequência de dias consecutivos estudando no Opus.\n" .
               "• Conclua pelo menos **1 lição por dia** para manter a chama acesa e desbloquear os troféus **Em Chamas (3 dias)**, **Semana Perfeita (7 dias)** e **Mês Épico (30 dias)**!";
    }

    if (!preg_match('/(windows\s*xp|extreme\s*programming|xp\s*inc|xp\s*investimentos)/iu', $m) &&
        preg_match('/(meu xp|quantos? (?:de )?xp eu tenho|como ganhar xp|ganhar xp|meus pontos no opus|pontua[çc][ãa]o no opus|tabela de xp)/iu', $m)) {
        $meuXp = (int) ($relatorio['xp'] ?? 0);
        $xpHoje = (int) ($relatorio['xp_hoje'] ?? 0);
        return "⚡ **Seu XP Atual: {$meuXp} XP Total (+{$xpHoje} XP hoje)**\n\n" .
               "**Tabela de Pontuação no Opus:**\n" .
               "• **Lição Perfeita (100% de acertos):** +50 XP\n" .
               "• **2 Acertos na lição:** +25 XP\n" .
               "• **1 Acerto ou Revisão de lição concluída:** +10 XP\n" .
               "• **Baú de Recompensas:** +50 a +100 XP de bônus!\n\n" .
               "Digite `/boost` para ver onde ganhar XP rápido agora mesmo!";
    }

    if (preg_match('/(meus amigos|meus seguidores|quantos seguidores|batalha de amigos|adicionar amigo|amigos no opus)/iu', $m)) {
        $seg = (int) ($relatorio['seguidores_qtd'] ?? 0);
        $seguindo = (int) ($relatorio['seguindo_qtd'] ?? 0);
        return "🤝 **Sua Rede Social no Opus, $nome:**\n\n" .
               "• **Seguidores:** **{$seg}** | **Seguindo:** **{$seguindo}**\n" .
               "• Você pode adicionar amigos, conversar em tempo real na aba **Chat** e enviar convites de **Batalha de Amigos** para competir quem ganha mais XP na semana!";
    }

    if (preg_match('/(dicion[aá]rio do opus|dicion[aá]rio java do opus|gloss[aá]rio do opus|onde fica o dicion[aá]rio)/iu', $m)) {
        return "📚 **Dicionário Java do Opus:**\n\n" .
               "Na aba **Dicionário** do menu lateral você encontra a definição rápida de todos os termos de Java (`class`, `public`, `static`, `void`, `new`, `this`, `extends`, `interface`, `try/catch`). Você também pode perguntar o significado de qualquer comando diretamente aqui no chat!";
    }

    // REGRA 10.5: CONSULTA DIRETA SOBRE ESTATÍSTICAS DO BANCO DE DADOS DO OPUS
    $statsBanco = opus_consultar_banco_dinamico($conn, (int)($_SESSION['user_id'] ?? 1), $msg);
    if (!empty($statsBanco) && preg_match('/(quantos usu[aá]rios|quantos alunos|quantos jogadores|banco de dados do opus|tabelas do banco|total de alunos)/iu', $m)) {
        return "🗄️ **Consulta em Tempo Real ao Banco de Dados OPUS:**\n\n" . $statsBanco;
    }

    return null;
}

/**
 * Motor de Conhecimento Híbrido (Multi-LLM + Banco + Web) do Opi IA
 * Responde QUALQUER pergunta sobre qualquer assunto e faz consultas em tempo real no banco e na web.
 */
function responder_opi_local(string $msg, string $nome, string $ctx = '', array $relatorio = [], bool $chave_configurada = false, ?mysqli $conn = null, bool $pularLlm = false, array $historico = []): string {
    $m = mb_strtolower(trim($msg), 'UTF-8');

    // 1. Verifica primeiro se é comando especial (/goal, /boost, /browser) ou consulta direta da conta OPUS
    $respostaPlataforma = opus_resposta_rapida_plataforma($msg, $nome, $ctx, $relatorio, $conn, $historico);
    if ($respostaPlataforma !== null) {
        return $respostaPlataforma;
    }

    // 2. Aciona o Motor LLM Generativo Multi-Provedor (com RAG Web + Tabelas + Notícias + Banco)
    if (!$pularLlm) {
        $groqKeyCfg = obter_groq_api_key();
        $respostaLlm = chamar_api_groq($groqKeyCfg, $msg, $nome, $ctx, $relatorio, $historico, $conn);
        if ($respostaLlm !== null && trim($respostaLlm) !== '') {
            return $respostaLlm;
        }
    }

    // =========================================================================
    // 3. FALLBACK CONTINGENCIAL: PESQUISA EM TEMPO REAL NA WEB (WIKIPÉDIA + TABELAS + NOTÍCIAS) E BANCO
    // =========================================================================
    $consultaDb = opus_consultar_banco_dinamico($conn, (int)($_SESSION['user_id'] ?? 1), $msg);
    $consultaWeb = opus_consultar_web($msg);

    if (!empty($consultaDb) || !empty($consultaWeb['sucesso'])) {
        $blocos = [];
        if (!empty($consultaDb)) {
            $blocos[] = "🗄️ **Consulta ao Banco de Dados OPUS:**\n" . $consultaDb;
        }
        if (!empty($consultaWeb['sucesso'])) {
            if (!empty($consultaWeb['extrato'])) {
                $blocos[] = "🌐 **{$consultaWeb['titulo']} ({$consultaWeb['fonte']}):**\n" . $consultaWeb['extrato'];
            }
            if (!empty($consultaWeb['tabelas'])) {
                $blocos[] = "📊 **Dados & Vencedores / Tabelas Oficiais ({$consultaWeb['titulo']}):**\n" . $consultaWeb['tabelas'];
            }
            if (!empty($consultaWeb['noticias']) && count($consultaWeb['noticias']) > 0) {
                $blocos[] = "📰 **Notícias & Atualizações em Tempo Real:**\n" . implode("\n", array_slice($consultaWeb['noticias'], 0, 5));
            }
            if (!empty($consultaWeb['web_snippets']) && count($consultaWeb['web_snippets']) > 0) {
                $blocos[] = "🔎 **Resultados da Web:**\n" . implode("\n", array_slice($consultaWeb['web_snippets'], 0, 4));
            } elseif (!empty($consultaWeb['snippets']) && count($consultaWeb['snippets']) > 1 && mb_strlen($consultaWeb['extrato'] ?? '', 'UTF-8') < 250) {
                $blocos[] = "📌 **Informações Complementares:**\n" . implode("\n", array_slice($consultaWeb['snippets'], 0, 3));
            }
            if (!empty($consultaWeb['url'])) {
                $blocos[] = "🔗 **Referência:** {$consultaWeb['url']}";
            }
        }
        if (!empty($blocos)) {
            return implode("\n\n", $blocos);
        }
    }

    // =========================================================================
    // 4. BASES LOCAIS DE APOIO OFFLINE (Programação Multi-Linguagem, Receitas e Saudações)
    // =========================================================================
    if (preg_match('/(c#|csharp|c-sharp|\.net|dotnet)/iu', $m)) {
        return "🟣 **C# (.NET) — Desenvolvimento & Exemplo Prático:**\n\n" .
               "**C# (C-Sharp)** é a linguagem orientada a objetos criada pela Microsoft para a plataforma **.NET**, amplamente usada em sistemas corporativos, APIs Web (ASP.NET Core) e jogos (Unity).\n\n" .
               "```csharp\n" .
               "using System;\n" .
               "using System.Collections.Generic;\n\n" .
               "public class Produto {\n" .
               "    public int Id { get; set; }\n" .
               "    public string Nome { get; set; }\n" .
               "    public decimal Preco { get; set; }\n" .
               "}\n\n" .
               "class Program {\n" .
               "    static void Main() {\n" .
               "        var produtos = new List<Produto> {\n" .
               "            new Produto { Id = 1, Nome = \"Notebook\", Preco = 3500.00m },\n" .
               "            new Produto { Id = 2, Nome = \"Mouse Gamer\", Preco = 120.50m }\n" .
               "        };\n" .
               "        foreach (var p in produtos) {\n" .
               "            Console.WriteLine($\"{p.Id} | {p.Nome} | R$ {p.Preco}\");\n" .
               "        }\n" .
               "    }\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(\bc\+\+\b|linguagem c\b)/iu', $m)) {
        return "⚙️ **C / C++ — Programação de Alto Desempenho:**\n\n" .
               "**C** e **C++** são linguagens compiladas de altíssima performance, usadas em sistemas operacionais, motores gráficos (Unreal Engine), sistemas embarcados e compiladores.\n\n" .
               "```cpp\n" .
               "#include <iostream>\n" .
               "using namespace std;\n\n" .
               "int main() {\n" .
               "    cout << \"Olá, C++ no Opus!\" << endl;\n" .
               "    return 0;\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/\b(python)\b/iu', $m)) {
        return "🐍 **Python — Visão Geral & Comparação com Java:**\n\n" .
               "**Python** é uma linguagem de alto nível famosa pela sintaxe enxuta (usa indentação em vez de chaves `{}`) e forte presença em **Ciência de Dados, Inteligência Artificial, Automação e Backend (Django/FastAPI)**.\n\n" .
               "💻 **Exemplo comparativo (Python vs Java):**\n" .
               "```python\n" .
               "# Em Python:\n" .
               "nome = \"Opus\"\n" .
               "for i in range(1, 4):\n" .
               "    print(f\"Olá {nome} - Passo {i}\")\n" .
               "```\n" .
               "```java\n" .
               "// Equivalente em Java:\n" .
               "String nome = \"Opus\";\n" .
               "for (int i = 1; i <= 3; i++) {\n" .
               "    System.out.println(\"Olá \" + nome + \" - Passo \" + i);\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/\b(javascript|typescript|node\.?js|react)\b/iu', $m)) {
        return "🌐 **JavaScript / TypeScript & Desenvolvimento Web:**\n\n" .
               "O **JavaScript (JS)** é a linguagem padrão dos navegadores web, permitindo criar interfaces dinâmicas no **Frontend** (com HTML/CSS, React, Vue) e servidores no **Backend** (com Node.js).\n\n" .
               "```javascript\n" .
               "// Exemplo em JavaScript:\n" .
               "const saudar = (nome) => `Olá, ${nome}! Bem-vindo ao Opus!`;\n" .
               "console.log(saudar('Dev'));\n" .
               "```";
    }

    if (preg_match('/^(oi|olá|ola|bom dia|boa tarde|boa noite|fala|eae|hey|salve|opa|tudo bem|como vai)[\?!\.\s]*$/iu', $m)) {
        return "👋 Olá, **$nome**! Eu sou o **Opi IA**, seu assistente de Inteligência Artificial no Opus! 🤖✨\n\n" .
               "Pode me perguntar **qualquer coisa sobre qualquer assunto** (programação, ciência, história, cultura pop, matemática, receitas, etc.) ou usar os comandos:\n" .
               "• 🌐 `/browser <assunto>` — Pesquisa em tempo real na Web e no banco de dados.\n" .
               "• 🎯 `/goal` — Consulta suas missões diárias, metas da sessão, liga e troféus.\n" .
               "• 🚀 `/boost` — Impulso imediato de XP, baús liberados e resumo da sua lição/desafio atual.\n\n" .
               "Como posso te ajudar agora?";
    }

    if (preg_match('/^(obrigado|valeu|obrigada|vlw|tmj|tks|thanks|top|legal|show)[\?!\.\s]*$/iu', $m)) {
        return "Por nada, **$nome**! 🧡 Estou sempre por aqui para responder qualquer dúvida ou fazer consultas para você. Use `/browser`, `/goal` ou `/boost` quando quiser! 💻🔥";
    }

    // Tentativa secundária buscando as palavras mais relevantes da frase na enciclopédia
    $palavras = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $msg), -1, PREG_SPLIT_NO_EMPTY);
    $stopWords = ['como', 'para', 'qual', 'quais', 'quem', 'onde', 'quando', 'porque', 'por', 'que', 'isso', 'esta', 'este', 'voce', 'você', 'pode', 'fazer', 'sobre', 'mais', 'muito', 'uma', 'uns', 'umas', 'dos', 'das', 'nos', 'nas', 'com', 'sem', 'funciona', 'ganhou', 'criou'];
    $relevantes = [];
    foreach ($palavras as $p) {
        $pl = mb_strtolower($p, 'UTF-8');
        if (mb_strlen($pl, 'UTF-8') >= 3 && !in_array($pl, $stopWords, true)) {
            $relevantes[] = $p;
        }
    }
    if (!empty($relevantes)) {
        $termoChave = implode(' ', array_slice($relevantes, -3));
        $consultaWeb2 = opus_consultar_web($termoChave);
        if (!empty($consultaWeb2['sucesso'])) {
            $partes2 = ["🌐 **{$consultaWeb2['titulo']} ({$consultaWeb2['fonte']}):**\n\n" . $consultaWeb2['extrato']];
            if (!empty($consultaWeb2['tabelas'])) {
                $partes2[] = "📊 **Dados & Tabelas:**\n" . $consultaWeb2['tabelas'];
            }
            if (!empty($consultaWeb2['noticias'])) {
                $partes2[] = "📰 **Notícias Recentes:**\n" . implode("\n", array_slice($consultaWeb2['noticias'], 0, 4));
            }
            if (!empty($consultaWeb2['url'])) {
                $partes2[] = "🔗 **Referência:** {$consultaWeb2['url']}";
            }
            return implode("\n\n", $partes2);
        }
    }

    $clean_msg = htmlspecialchars(trim($msg));
    return "🤖 **Opi IA — Assistente Livre & Consultas:**\n\n" .
           "Recebi sua mensagem: *\"$clean_msg\"* 💡\n\n" .
           "Posso responder qualquer assunto ou realizar consultas em tempo real para você! Experimente:\n" .
           "• 🌐 **`/browser <termo>`** — Para pesquisar qualquer pessoa, país, tecnologia, conceito científico ou histórico na web.\n" .
           "• 🎯 **`/goal`** — Para consultar o status em tempo real das suas Missões Diárias, Sessões, Liga e Troféus.\n" .
           "• ⚡ **`/boost`** — Para receber dicas do desafio atual, ver baús disponíveis e acelerar seu XP!";
}


