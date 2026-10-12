<?php
/**
 * OPUS - Lógica e Motor de IA do Chatbot Opi
 * Suporte a Groq Cloud (LLaMA 3.3 70B & 3.1 8B), Consultas Acadêmicas em Tempo Real e Base de Conhecimento Local
 */

require_once __DIR__ . '/config_ai.php';
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

    try {
        $dados_liga = liga_garantir_usuario($conn, $userId);
        $liga_divisao = $dados_liga['divisao'] ?? 'bronze';
        $liga_xp_semana = (int) ($dados_liga['xp_semana'] ?? 0);
        $cfg_divisao = liga_config($liga_divisao);
        $liga_pode_subir = (bool) ($cfg_divisao['sobe'] ?? true);

        if (!empty($dados_liga['grupo_id'])) {
            $stmt_membros = $conn->prepare("
                SELECT usuario_id, xp_semana 
                FROM ligas_usuario 
                WHERE grupo_id = ? 
                ORDER BY xp_semana DESC, usuario_id ASC
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
                    if ((int) $m['usuario_id'] === $userId) {
                        $liga_posicao = $idx + 1;
                        break;
                    }
                }

                if ($liga_posicao <= $linha_subida && $liga_pode_subir) {
                    $liga_zona = 'sobe';
                } elseif ($liga_posicao > ($liga_total_grupo - $zonas['desce']) && ($cfg_divisao['desce'] ?? false)) {
                    $liga_zona = 'desce';
                } else {
                    $liga_zona = 'neutro';
                }

                // Cálculo de quanto XP falta para subir de liga
                // O corte é a pontuação do último jogador dentro da zona de subida (posição $linha_subida)
                $idx_corte = min($linha_subida - 1, $liga_total_grupo - 1);
                $xp_corte = (int) ($membros[$idx_corte]['xp_semana'] ?? 0);
                $liga_xp_corte_subida = $xp_corte;

                if (!$liga_pode_subir) {
                    // Liga Mestre já é o topo
                    $liga_xp_necessario_subir = 0;
                } elseif ($liga_posicao <= $linha_subida) {
                    // Já está na zona de promoção!
                    $liga_xp_necessario_subir = 0;
                } else {
                    // Está fora do Top 12: precisa superar o corte
                    $diff = ($xp_corte - $liga_xp_semana);
                    $liga_xp_necessario_subir = max(10, $diff + 10);
                }
            }
        }
    } catch (\Throwable $e) {
        // Fallback gracioso
    }

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
        'estudou_hoje' => $estudou_hoje,
        'liga_divisao' => $liga_divisao,
        'liga_posicao' => $liga_posicao,
        'liga_total_grupo' => $liga_total_grupo,
        'liga_xp_semana' => $liga_xp_semana,
        'liga_zona' => $liga_zona,
        'liga_xp_necessario_subir' => $liga_xp_necessario_subir,
        'liga_xp_corte_subida' => $liga_xp_corte_subida,
        'liga_pode_subir' => $liga_pode_subir
    ];
}

/**
 * Executa requisição para a API da Groq (OpenAI-compatible) com diagnóstico acadêmico real
 */
function chamar_api_groq(string $apiKey, string $prompt, string $nome, string $contexto = '', array $relatorio = [], array $historico = []) {
    // Monta o resumo em texto para o System Prompt
    $info_progresso = "";
    if (!empty($relatorio)) {
        $info_progresso = "
RELATÓRIO ACADÊMICO DO ALUNO NO CURSO OPUS (CONSULTA AO BANCO EM TEMPO REAL):
- Estrutura Geral do Curso Opus: 40 Sessões (Módulos) com 5 lições cada = 200 lições totais no curso completo.
- Sessões Concluídas: {$relatorio['sessoes_concluidas']} de 40 sessões.
- Sessões Restantes para terminar o curso: {$relatorio['sessoes_restantes']} sessões restantes (Atenção: se ele tiver 0 sessões concluídas e estiver na primeira, faltam 40 sessões porque ele nem terminou a primeira!).
- Sessão Atual: Sessão {$relatorio['sessao_atual_num']} — {$relatorio['sessao_atual_nome']}.
- Lições Concluídas na Sessão Atual: {$relatorio['licoes_feitas_sessao_atual']} de 5 (restam {$relatorio['licoes_restantes_sessao_atual']} lições nesta sessão).
- Total de Lições no Curso Inteiro: {$relatorio['total_licoes_feitas']} de 200 concluídas.
- Porcentagem Geral do Curso: {$relatorio['porcentagem_concluida']}% do curso concluído.
- Porcentagem da Sessão Atual: {$relatorio['porcentagem_sessao_atual']}% da sessão atual concluída.
- Atividade Realizada HOJE (" . date('d/m/Y') . "): {$relatorio['licoes_hoje']} lição(ões) concluída(s) hoje, {$relatorio['acertos_hoje']} acerto(s) e +{$relatorio['xp_hoje']} XP hoje.
- XP Total Acumulado: {$relatorio['xp']} | Ofensiva: {$relatorio['dias_fogo']} Dias de Fogo | Vidas: {$relatorio['vidas']} | Troféus: {$relatorio['trofeus']}.
- Ligas do Opus (Semana Atual):
  * Divisão: " . ucfirst($relatorio['liga_divisao']) . "
  * Posição Atual no Grupo: {$relatorio['liga_posicao']}º lugar de {$relatorio['liga_total_grupo']} jogadores.
  * XP Semanal na Liga: {$relatorio['liga_xp_semana']} XP.
  * Zona Atual: " . ($relatorio['liga_zona'] === 'sobe' ? 'Zona de Promoção (sobe de divisão)' : ($relatorio['liga_zona'] === 'desce' ? 'Zona de Rebaixamento' : 'Zona Neutra')) . "
  * Regra de Subida: Os 12 primeiros colocados do grupo sobem para a próxima divisão.
  * XP Necessário para Subir: " . ($relatorio['liga_posicao'] <= 12 ? 'Já está na zona de promoção (Top 12)!' : "Precisa de pelo menos +{$relatorio['liga_xp_necessario_subir']} XP para ultrapassar o 12º colocado (que tem {$relatorio['liga_xp_corte_subida']} XP) e entrar no Top 12!") . "
- Nível Técnico Avaliado: {$relatorio['nivel_java']}.
- Diagnóstico de Empregabilidade / Vagas de Emprego: {$relatorio['resumo_mercado']}
";
    }

    $system_prompt = "Você é o Opi, o mascote robótico e tutor inteligente oficial de Inteligência Artificial da plataforma OPUS (plataforma gamificada para aprender programação em Java).

Sua personalidade:
- Amigável, incentivador, paciente, bem-humorado e didático.
- Use emojis com moderação para deixar a conversa acolhedora.
- Fale em português do Brasil com clareza.

Dados do Aluno:
- Nome do aluno: {$nome}
- Contexto da tela atual: " . (!empty($contexto) ? $contexto : "Navegando no aplicativo Opus") . "
{$info_progresso}

REGRA CRÍTICA DE ESCOPO DO SITE OPUS (PRIORIDADE MÁXIMA):
- Se o usuário perguntar sobre o valor do curso, preço, compras, mensalidade, pagamento, custo ou qualquer assunto fora do escopo de programação, Java e da plataforma Opus (ex: 'Qual é o valor do curso de Java?', 'Quanto custa?', 'Onde compro?', política, fofoca, receitas, etc.):
  Você DEVE responder EXATAMENTE e OBRIGATORIAMENTE com a seguinte mensagem padrão:
  'Oi, eu não posso responder o que você está pedindo aqui de acordo com o site.'
  (Pois o Opus é uma plataforma 100% gratuita voltada exclusivamente ao ensino e prática de Java).

DIRETRIZES FUNDAMENTAIS PARA RESPONDER CONSULTAS DO ALUNO:

1. CONSULTA DE SESSÕES / CAPÍTULOS FALTANTES (Ex: 'Quantas sessões faltam para eu terminar o curso?'):
   - Use com exatidão a base de 40 sessões!
   - Se o aluno estiver na Sessão 1 e ainda não tiver concluído a Sessão 1, diga exatamente: 'Faltam 40 sessões, porque você nem terminou a primeira (você fez {$relatorio['licoes_feitas_sessao_atual']} de 5 lições da Sessão 1).'
   - Caso ele já tenha concluído algumas sessões, informe quantas restam ({$relatorio['sessoes_restantes']} de 40) e cite em qual sessão ele está.

2. PERGUNTAS DE PORCENTAGEM OU ACOMPANHAMENTO (Ex: 'E por porcentagem?', 'Qual a porcentagem?', 'Quanto % eu fiz?'):
   - Responda prontamente informando a porcentagem do curso inteiro ({$relatorio['porcentagem_concluida']}% de 200 lições concluídas) e a porcentagem da sessão atual ({$relatorio['porcentagem_sessao_atual']}% da Sessão {$relatorio['sessao_atual_num']}).

3. CONSULTA DE LIGAS E XP PARA SUBIR (Ex: 'Nas ligas, quanto XP eu tenho que ganhar para conseguir subir de liga?', 'Como subo de divisão?'):
   - Use os dados reais da liga acima!
   - Diga a divisão dele (ex: Liga {$relatorio['liga_divisao']}) e a posição atual ({$relatorio['liga_posicao']}º lugar com {$relatorio['liga_xp_semana']} XP).
   - Explique que nas ligas do Opus os 12 primeiros do grupo sobem de divisão.
   - Se ele já estiver no Top 12 (posição <= 12): Parabenize-o e confirme que ele já está na zona de promoção para subir!
   - Se ele estiver abaixo do 12º lugar: Informe exatamente que faltam {$relatorio['liga_xp_necessario_subir']} XP para ultrapassar a linha de corte do Top 12 e subir de liga!

4. CONSULTA DE ATIVIDADE DIÁRIA (Ex: 'Hoje eu fiz quantas lições?'):
   - Use a métrica de atividade de hoje ({$relatorio['licoes_hoje']} lições hoje, {$relatorio['acertos_hoje']} acertos, {$relatorio['dias_fogo']} Dias de Fogo).

5. DÚVIDAS DE CONTEÚDO DAS SESSÕES (Ex: 'O que eu vou aprender na sessão dois?'):
   - Explique o conteúdo da sessão solicitada com didática e exemplo de código em Java.

6. MERCADO DE TRABALHO E DÚVIDAS TÉCNICAS:
   - Responda com base no relatório: Nível {$relatorio['nivel_java']} e diagnóstico de empregabilidade.";

    // Montar histórico seguro de mensagens
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

    $modelos = [GROQ_MODEL_PRIMARY, GROQ_MODEL_FALLBACK];

    foreach ($modelos as $modelo) {
        $payload = [
            'model' => $modelo,
            'messages' => $messages,
            'temperature' => 0.6,
            'max_tokens' => 1024,
            'top_p' => 0.95
        ];

        $ch = curl_init(GROQ_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 20
        ]);

        $resposta_raw = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log("Groq cURL Error: " . $curl_error);
            continue;
        }

        if ($resposta_raw) {
            $dados = json_decode($resposta_raw, true);

            if ($http_code === 200 && isset($dados['choices'][0]['message']['content'])) {
                $conteudo = trim($dados['choices'][0]['message']['content']);
                if (!empty($conteudo)) {
                    return $conteudo;
                }
            }

            if (isset($dados['error'])) {
                error_log("Groq API Error ($modelo): " . json_encode($dados['error']));
            }
        }
    }

    return false;
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
 * Motor de Conhecimento Local do Opi IA
 * Fornece respostas imediatas e inteligentes com dados reais do aluno
 */
function responder_opi_local(string $msg, string $nome, string $ctx = '', array $relatorio = [], bool $chave_configurada = false): string {
    $m = mb_strtolower($msg, 'UTF-8');

    // REGRA 0: FILTRO DE ESCOPO RÍGIDO (Preço, valor, compra ou assuntos fora do site/Java)
    if (preg_match('/(qual.*valor|quanto custa|qual.*preço|qual.*preco|preço.*curso|preco.*curso|valor.*curso|comprar.*curso|pagamento|mensalidade|boleto|cartão de crédito|cartao de credito|comprar o curso|vende o curso|custo do curso|quanto é o curso|quanto e o curso)/i', $m) ||
        (preg_match('/(preço|preco|valor|custo|comprar|pagar)/i', $m) && preg_match('/(curso|plataforma|inscrição|inscricao|matrícula|matricula)/i', $m))) {
        return "Oi, eu não posso responder o que você está pedindo aqui de acordo com o site.";
    }

    // REGRA 1: CONSULTA DE ATIVIDADE DIÁRIA (Ex: "Hoje eu fiz quantas lições?", "Quantas lições fiz hoje?")
    if (preg_match('/(hoje.*quantas.*liç|quantas.*liç.*hoje|hoje.*fiz quantas|fiz quantas.*hoje|quanto.*estudei.*hoje|lições.*feitas.*hoje|licoes.*feitas.*hoje)/i', $m) ||
        (preg_match('/(hoje)/i', $m) && preg_match('/(lição|licao|lições|licoes)/i', $m) && preg_match('/(fiz|concluí|conclui|completei|quantas|quantos)/i', $m))) {
        
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

    // REGRA 2: CONSULTA DE CONTEÚDO DA SESSÃO / SEÇÃO / CAPÍTULO (Ex: "O que eu vou aprender na sessão dois?")
    if (preg_match('/(o que.*(?:vou|vamos|irei|vai).*aprender|o que tem|o que ensina|qual o conteúdo|qual o conteudo|conteúdo d[ao]|conteudo d[ao]|ementa)/i', $m) &&
        preg_match('/(sessão|sessao|seção|secao|capítulo|capitulo|módulo|modulo)/i', $m)) {
        
        $numSessao = opus_extrair_numero_sessao($msg);
        if ($numSessao !== null) {
            $ementa = opus_obter_ementa_sessao($numSessao);
            if ($ementa) {
                return "📚 **{$ementa['titulo']} — {$ementa['nome']}**\n\n" .
                       "{$ementa['texto']}\n\n" .
                       "💻 **Exemplo do que você pratica nesta seção:**\n" .
                       "```java\n" .
                       "{$ementa['codigo']}\n" .
                       "```\n\n" .
                       "🎯 Cada seção do Opus possui **5 Lições interativas** com teoria e desafios de código. Pronto para mandar ver nos estudos?";
            }
        }
    }

    // Se perguntar genericamente sobre uma sessão específica (ex: "Sessão 2", "Me fale sobre a seção dois")
    $numDetectado = opus_extrair_numero_sessao($msg);
    if ($numDetectado !== null && preg_match('/(sessão|sessao|seção|secao)/i', $m)) {
        $ementa = opus_obter_ementa_sessao($numDetectado);
        if ($ementa) {
            return "📚 **{$ementa['titulo']} — {$ementa['nome']}**\n\n" .
                   "{$ementa['texto']}\n\n" .
                   "💻 **Código de exemplo da seção:**\n" .
                   "```java\n" .
                   "{$ementa['codigo']}\n" .
                   "```";
        }
    }

    // REGRA 3: CONSULTA DIRETA DE SESSÕES / CAPÍTULOS FALTANTES E PROGRESSO GERAL
    if (preg_match('/(quanto|quantos|quantas).*(sessão|sessao|sessões|sessoes|capítulo|capitulo|capítulos|capitulos|lição|licao|resta|restam|falta|faltam|terminar|concluir|curso|acabar|fechar)/i', $m) ||
        preg_match('/(onde parei|qual capítulo|qual capitulo|qual sessão|qual sessao|meu progresso|estou em qual capítulo|estou em qual sessão)/i', $m)) {
        
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

            // Resposta personalizada quando o aluno está na primeira sessão e ainda não a concluiu
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

    // REGRA 4: CONSULTA DE PORCENTAGEM (Ex: "E por porcentagem?", "Qual a porcentagem?", "Quantos %?")
    if (preg_match('/(porcentagem|porcento|por cento|percentual|percentual|%)/i', $m) ||
        preg_match('/(e por|e a porcentagem|qual a porcentagem|quanto em porcentagem)/i', $m)) {
        
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

    // REGRA 5: CONSULTA DAS LIGAS E XP PARA SUBIR DE LIGA
    if (preg_match('/(liga|ligas|divisão|divisao|subir de liga|subir de divisão|subir de divisao|promover|promoção|promocao|rebaixar|rebaixamento)/i', $m) &&
        preg_match('/(quanto|quantos|xp|pontos|ganhar|preciso|subir|fazer|posição|posicao)/i', $m)) {
        
        $divisao = ucfirst($relatorio['liga_divisao'] ?? 'Bronze');
        $posicao = (int) ($relatorio['liga_posicao'] ?? 1);
        $totalGrupo = (int) ($relatorio['liga_total_grupo'] ?? 1);
        $xpSemana = (int) ($relatorio['liga_xp_semana'] ?? 0);
        $xpNecessario = (int) ($relatorio['liga_xp_necessario_subir'] ?? 0);
        $xpCorte = (int) ($relatorio['liga_xp_corte_subida'] ?? 0);
        $podeSubir = (bool) ($relatorio['liga_pode_subir'] ?? true);

        if (!$podeSubir) {
            return "🏆 **Sua Situação nas Ligas, $nome:**\n\n" .
                   "Você está na **Liga Mestre**, a divisão máxima do Opus! 👑\n\n" .
                   "• **Posição atual:** **$posicao º lugar** no seu grupo com **$xpSemana XP** esta semana.\n" .
                   "• Como você já está na liga mais alta, não há divisões superiores para subir. Mantenha-se entre os melhores colocados para consolidar sua glória e ganhar troféus de liderança!";
        }

        if ($posicao <= 12) {
            return "🏆 **Sua Situação nas Ligas, $nome:**\n\n" .
                   "🎉 **Você já está na ZONA DE PROMOÇÃO para subir de liga!** 🚀\n\n" .
                   "• **Sua Divisão:** **Liga $divisao**\n" .
                   "• **Sua Posição:** **$posicao º lugar** de $totalGrupo participantes no seu grupo.\n" .
                   "• **Seu XP nesta semana:** **$xpSemana XP**\n" .
                   "• **Regra da Liga:** Os **12 primeiros colocados** do grupo sobem de divisão na virada semanal!\n\n" .
                   "💡 **Dica do Opi:** Continue fazendo lições para somar mais XP e garantir que nenhum rival te passe até o fechamento da semana!";
        } else {
            return "🏆 **Sua Situação nas Ligas, $nome:**\n\n" .
                   "Para conseguir subir da **Liga $divisao** para a próxima divisão, você precisa terminar a semana no **Top 12 (entre os 12 primeiros colocados)** do seu grupo!\n\n" .
                   "• **Sua Posição Atual:** **$posicao º lugar** de $totalGrupo participantes.\n" .
                   "• **Seu XP Semanal:** **$xpSemana XP**\n" .
                   "• **Linha de Corte (12º colocado):** Atualmente com **$xpCorte XP**.\n\n" .
                   "🎯 **Quanto XP você precisa ganhar:**\n" .
                   "Você precisa de pelo menos **+$xpNecessario XP** para ultrapassar a linha de corte e entrar na zona de promoção!\n\n" .
                   "💡 Cada lição concluída rende entre **10 e 50 XP**. Faça algumas lições hoje para subir posições no ranking!";
        }
    }

    // 2. CONSULTA DE EMPREGABILIDADE, VAGAS DE EMPREGO E NÍVEL EM JAVA
    if (preg_match('/(vaga|vagas|emprego|empregos|trabalho|estágio|estagio|estágios|estagios|mercado|contratad|currículo|curriculo|nível|nivel|com esse conhecimento|com esses conhecimentos)/i', $m)) {
        if (!empty($relatorio)) {
            $capConcluidos = $relatorio['capitulos_concluidos'];
            $capAtualNum   = $relatorio['capitulo_atual_num'];
            $nivelJava     = $relatorio['nivel_java'];
            $pct           = $relatorio['porcentagem_concluida'];

            if ($capConcluidos >= 5) {
                return "💼 **Avaliação de Mercado & Empregabilidade:**\n\n" .
                       "🎯 **Seu Nível Atual:** **$nivelJava**\n\n" .
                       "✅ **Sim! Com essa bagagem você tem a base técnica para vagas de Estágio e Júnior em Java!** 🚀\n\n" .
                       "Como você concluiu todos os 5 capítulos do Opus, você já domina o **Java Core completo**: sintaxe, controle de fluxo, vetores/coleções (`ArrayList`) e **Programação Orientada a Objetos (POO)** com herança e polimorfismo.\n\n" .
                       "📌 **Dicas do Opi para conquistar sua vaga:**\n" .
                       "1. Crie 1 ou 2 projetos autorais no **GitHub** aplicando POO (ex: um sistema bancário ou gerenciador de tarefas).\n" .
                       "2. Estude os fundamentos de **Spring Boot (APIs REST)** e conexão com banco de dados (MySQL/JPA).\n" .
                       "3. Destaque em seu LinkedIn seus projetos práticos em Java!";
            }

            if ($capConcluidos >= 3) {
                return "💼 **Avaliação de Mercado & Empregabilidade:**\n\n" .
                       "🎯 **Seu Nível Atual:** **$nivelJava** (Progresso: **$pct%** do curso)\n\n" .
                       "💡 **Você consegue uma vaga agora?**\n" .
                       "• Você já tem uma **lógica de programação muito sólida** (loops, vetores e algoritmos), o que é excelente para passar em testes de lógica de programas de **Jovem Aprendiz Técnico** ou desafios de faculdade!\n" .
                       "• No entanto, para vagas formais de **Estágio em Desenvolvimento Java ou Dev Júnior**, 99% das empresas exigem domínio absoluto de **POO (Programação Orientada a Objetos)**.\n\n" .
                       "🚀 **O que falta para você ficar pronto:**\n" .
                       "Concluir o **Capítulo 5 (Arquiteto Java - POO)**! Assim que você dominar classes, objetos, encapsulamento e herança, você terá todas as ferramentas essenciais para entrevistas técnicas!";
            }

            return "💼 **Avaliação de Mercado & Empregabilidade:**\n\n" .
                   "🎯 **Seu Nível Atual:** **$nivelJava** (Progresso: **$pct%** do curso)\n\n" .
                   "💡 **Você consegue uma vaga agora?**\n" .
                   "Ainda não, $nome. Você está dando os primeiros passos e construindo as bases fundamentais (sintaxe e lógica inicial).\n\n" .
                   "Para disputar vagas de **Estágio em Desenvolvimento ou Desenvolvedor Júnior**, o mercado exige domínio de laços de repetição, coleções (`ArrayList`) e, principalmente, **Programação Orientada a Objetos (POO)**.\n\n" .
                   "🎯 **Seu plano de ação:**\n" .
                   "Avance para os Capítulos 3 (Loops), 4 (Arrays) e 5 (POO). Concluindo a trilha do Opus, você estará muito bem preparado para concorrer a vagas!";
        }
    }

    // 3. SISTEMA E MECÂNICAS DO OPUS
    if (preg_match('/(vida|coração|coracao|perdi vida|zerar|morri)/i', $m)) {
        return "❤️ **Como funcionam as Vidas no Opus:**\n\n" .
               "• Você começa com **3 Vidas (corações)**.\n" .
               "• Se errar todas as questões de uma lição (0 acertos), você perde **1 vida**.\n" .
               "• **Como recuperar:** Uma vida é restaurada a cada **5 horas** automaticamente, ou você pode recuperar vidas abrindo o **Baú de Recompensas** no meio de cada capítulo!";
    }

    if (preg_match('/(fogo|ofensiva|dias de fogo|sequência|sequencia|chama)/i', $m)) {
        return "🔥 **Dias de Fogo (Ofensiva):**\n\n" .
               "• Os **Dias de Fogo** registram a sua sequência de dias consecutivos estudando!\n" .
               "• Para manter a sua chama acesa, conclua pelo menos **1 lição por dia**.\n" .
               "• Manter a ofensiva rende troféus especiais como **Em Chamas** e **Semana Perfeita**!";
    }

    if (preg_match('/(baú|bau|recompensa|caixa|premio|prêmio)/i', $m)) {
        return "🎁 **Baú de Recompensas:**\n\n" .
               "• Na 3ª lição de cada capítulo, você desbloqueia o **Baú de Recompensas** na Trilha de Estudos!\n" .
               "• Abrir o baú garante **Bônus de XP** e restaura seus **Corações de Vida**!";
    }

    if (preg_match('/(xp|pontuação|pontuacao|pontos|ganhar xp)/i', $m)) {
        return "⚡ **Pontuação e XP no Opus:**\n\n" .
               "• **3/3 Acertos (Perfeito):** +150 XP\n" .
               "• **2/3 Acertos:** +100 XP\n" .
               "• **1/3 Acertos:** +50 XP\n" .
               "• **Tentativa:** +10 XP\n\n" .
               "O XP acumulado define a sua posição no **Ranking Semanal** e a promoção de **Liga**!";
    }

    if (preg_match('/(liga|ligas|divisão|divisao|bronze|prata|ouro|diamante)/i', $m)) {
        return "🛡️ **Ligas e Competições no Opus:**\n\n" .
               "• As ligas dividem-se em: **Bronze, Prata, Ouro e Diamante**.\n" .
               "• Ganhe XP durante a semana realizando lições para ficar na zona de classificação e subir de divisão na virada semanal!";
    }

    if (preg_match('/(ranking|placar|líder|lider|primeiro lugar)/i', $m)) {
        return "🏆 **Ranking Semanal:**\n\n" .
               "• O **Ranking** mostra a classificação de todos os estudantes com base no XP acumulado na semana atual.\n" .
               "• Você pode acompanhar seu progresso acessando a aba **Ranking** no menu lateral!";
    }

    if (preg_match('/(troféu|trofeu|troféus|trofeus|conquista|conquistas|missão|missao|missões|missoes)/i', $m)) {
        return "🎖️ **Galeria de Conquistas & Troféus:**\n\n" .
               "• Ao concluir capítulos inteiros, bater recordes de ofensiva ou acertar 3/3 lições, você desbloqueia **Troféus**.\n" .
               "• Veja suas conquistas na aba **Missões** ou diretamente no seu **Perfil**!";
    }

    if (preg_match('/(dicionário|dicionario|glossário|glossario|termo|palavra)/i', $m)) {
        return "📚 **Dicionário Java:**\n\n" .
               "• Na aba **Dicionário** do menu lateral você encontra a definição simplificada de palavras-chave como `class`, `public`, `static`, `void`, `new`, `this` e muito mais!";
    }

    // 4. FUNDAMENTOS DE JAVA
    if (preg_match('/(variável|variavel|variáveis|variaveis|tipo primitivo|tipos primitivos|int |double |float |string |boolean |char )/i', $m)) {
        return "💻 **Variáveis e Tipos em Java:**\n\n" .
               "Em Java, toda variável precisa ter seu tipo declarado antes do nome:\n\n" .
               "```java\n" .
               "int idade = 20;               // Números inteiros\n" .
               "double preco = 49.90;         // Números com ponto decimal\n" .
               "char letra = 'J';             // Um único caractere (aspas simples)\n" .
               "boolean ativo = true;         // Verdadeiro ou falso\n" .
               "String nome = \"Opus\";         // Texto (aspas duplas, classe)\n" .
               "```\n\n" .
               "📌 **Dica do Opi:** Sempre termine as instruções com ponto e vírgula `;`!";
    }

    if (preg_match('/(if|else|else if|condicional|condicionais|decisão|decisao)/i', $m)) {
        return "🔀 **Estruturas Condicionais (if / else) em Java:**\n\n" .
               "Servem para executar blocos de código com base em condições verdadeiras ou falsas:\n\n" .
               "```java\n" .
               "int nota = 8;\n\n" .
               "if (nota >= 7) {\n" .
               "    System.out.println(\"Aprovado! Parabéns! 🚀\");\n" .
               "} else if (nota >= 5) {\n" .
               "    System.out.println(\"Recuperação! ⚠️\");\n" .
               "} else {\n" .
               "    System.out.println(\"Precisa estudar mais. 💻\");\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(switch|case|default)/i', $m)) {
        return "🔀 **Estrutura switch / case em Java:**\n\n" .
               "Ideal para comparar uma mesma variável com múltiplos valores fixos:\n\n" .
               "```java\n" .
               "int opcao = 2;\n" .
               "switch (opcao) {\n" .
               "    case 1:\n" .
               "        System.out.println(\"Iniciar Jogo\");\n" .
               "        break;\n" .
               "    case 2:\n" .
               "        System.out.println(\"Abrir Configurações\");\n" .
               "        break;\n" .
               "    default:\n" .
               "        System.out.println(\"Opção inválida\");\n" .
               "}\n" .
               "```\n" .
               "⚠️ Não esqueça o `break;` para evitar que o Java continue executando os próximos cases!";
    }

    if (preg_match('/(loop|laço|laco|repetição|repeticao|for |while|do while)/i', $m)) {
        return "🔄 **Laços de Repetição em Java:**\n\n" .
               "**1. Laço `for` (quando você sabe quantas repetições quer):**\n" .
               "```java\n" .
               "for (int i = 1; i <= 5; i++) {\n" .
               "    System.out.println(\"Passo: \" + i);\n" .
               "}\n" .
               "```\n\n" .
               "**2. Laço `while` (enquanto uma condição for verdadeira):**\n" .
               "```java\n" .
               "int vidas = 3;\n" .
               "while (vidas > 0) {\n" .
               "    System.out.println(\"Jogando com vidas: \" + vidas);\n" .
               "    vidas--;\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(arraylist|list|lista)/i', $m)) {
        return "📑 **ArrayList em Java (Tamanho Dinâmico):**\n\n" .
               "Diferente dos arrays normais, o `ArrayList` pode crescer ou diminuir de tamanho conforme a necessidade:\n\n" .
               "```java\n" .
               "import java.util.ArrayList;\n\n" .
               "ArrayList<String> amigos = new ArrayList<>();\n" .
               "amigos.add(\"Gabriele\");\n" .
               "amigos.add(\"Lucas\");\n\n" .
               "System.out.println(amigos.get(0)); // Acessa elemento: Gabriele\n" .
               "System.out.println(amigos.size());  // Quantidade total: 2\n" .
               "```";
    }

    if (preg_match('/(array|vetor|matriz|arrays)/i', $m)) {
        return "📦 **Arrays (Vetores) em Java:**\n\n" .
               "Armazenam uma sequência de elementos do mesmo tipo com tamanho fixo:\n\n" .
               "```java\n" .
               "// Criando e inicializando\n" .
               "String[] linguagens = {\"Java\", \"Python\", \"C#\"};\n\n" .
               "// Acessando pelo índice (começa em 0):\n" .
               "System.out.println(linguagens[0]); // Saída: Java\n\n" .
               "// Tamanho do array:\n" .
               "System.out.println(linguagens.length); // Saída: 3\n" .
               "```";
    }

    if (preg_match('/(poo|classe|objeto|instancia|instância|atributo|método|metodo)/i', $m)) {
        return "🏗️ **Orientação a Objetos (POO) em Java:**\n\n" .
               "• **Classe:** É a planta ou modelo (ex: classe `Jogador`).\n" .
               "• **Objeto:** É a instância criada na memória a partir da classe (`new Jogador()`).\n" .
               "• **Atributos:** Dados ou características do objeto (ex: `xp`, `vidas`).\n" .
               "• **Métodos:** Comportamentos ou ações do objeto (ex: `ganharXp()`, `perderVida()`).\n\n" .
               "```java\n" .
               "public class Jogador {\n" .
               "    String nome;\n" .
               "    int vidas = 3;\n\n" .
               "    void perderVida() {\n" .
               "        vidas--;\n" .
               "    }\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(herança|heranca|extends|polimorfismo|override|@override)/i', $m)) {
        return "🧬 **Herança e Polimorfismo em Java:**\n\n" .
               "• **Herança (`extends`):** Permite que uma subclasse herde atributos e métodos de uma superclasse (classe mãe).\n" .
               "• **Polimorfismo:** Permite que métodos com o mesmo nome se comportem de formas diferentes em classes filhas usando a anotação `@Override`.\n\n" .
               "```java\n" .
               "public class Animal {\n" .
               "    void emitirSom() { System.out.println(\"Som genérico\"); }\n" .
               "}\n\n" .
               "public class Cachorro extends Animal {\n" .
               "    @Override\n" .
               "    void emitirSom() { System.out.println(\"Au Au! 🐶\"); }\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(encapsulamento|private|public|protected|getter|setter)/i', $m)) {
        return "🛡️ **Encapsulamento em Java:**\n\n" .
               "O encapsulamento protege dados internos de uma classe tornando os atributos `private` e fornecendo métodos públicos `get` e `set`:\n\n" .
               "```java\n" .
               "public class Usuario {\n" .
               "    private String senha;\n\n" .
               "    public String getSenha() {\n" .
               "        return this.senha;\n" .
               "    }\n" .
               "    public void setSenha(String novaSenha) {\n" .
               "        if (novaSenha.length() >= 6) {\n" .
               "            this.senha = novaSenha;\n" .
               "        }\n" .
               "    }\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(print|println|system\.out|imprimir)/i', $m)) {
        return "🖨️ **Impressão e Saída de Dados no Terminal:**\n\n" .
               "• `System.out.println(\"Texto\");` — Imprime o texto e pula para a próxima linha.\n" .
               "• `System.out.print(\"Texto\");` — Imprime o texto mantendo o cursor na mesma linha.\n" .
               "• `System.out.printf(\"Olá %s, você tem %d XP\", nome, xp);` — Imprime com formatação.";
    }

    if (preg_match('/(scanner|input|teclado|ler dados)/i', $m)) {
        return "⌨️ **Entrada de Dados via Scanner:**\n\n" .
               "Para ler o que o usuário digita no terminal:\n\n" .
               "```java\n" .
               "import java.util.Scanner;\n\n" .
               "Scanner leitor = new Scanner(System.in);\n" .
               "System.out.print(\"Digite seu nome: \");\n" .
               "String nome = leitor.nextLine();\n\n" .
               "System.out.println(\"Bem-vindo(a), \" + nome);\n" .
               "leitor.close();\n" .
               "```";
    }

    if (preg_match('/(erro|exception|exceção|excecao|bug|nullpointer|indexoutofbounds|try|catch)/i', $m)) {
        return "⚠️ **Tratamento de Exceções (try / catch):**\n\n" .
               "Evita que o programa pare abruptamente quando ocorre um erro:\n\n" .
               "```java\n" .
               "try {\n" .
               "    int resultado = 10 / 0; // Erro de divisão por zero\n" .
               "} catch (ArithmeticException e) {\n" .
               "    System.out.println(\"Não é possível dividir por zero! ⚠️\");\n" .
               "} catch (Exception e) {\n" .
               "    System.out.println(\"Erro capturado: \" + e.getMessage());\n" .
               "}\n" .
               "```\n\n" .
               "• **NullPointerException:** Acontece ao tentar usar algo com valor `null`.\n" .
               "• **ArrayIndexOutOfBoundsException:** Acontece ao tentar acessar um índice inexistente de um vetor.";
    }

    // 5. SAUDAÇÕES E AGRADECIMENTOS
    if (preg_match('/(oi|olá|ola|bom dia|boa tarde|boa noite|fala|eae|hey|salve|opa)/i', $m)) {
        return "👋 Olá, **$nome**! Eu sou o **Opi**, seu mascote e tutor inteligente no Opus! 🤖✨\n\n" .
               "Como posso te ajudar agora? Você pode me perguntar:\n" .
               "• *\"Quantos capítulos faltam para eu terminar o curso?\"*\n" .
               "• *\"Com meus conhecimentos atuais eu consigo uma vaga de emprego ou estágio?\"*\n" .
               "• Dúvidas de programação Java (variáveis, loops, arrays, POO) e regras do jogo (vidas, fogo, ligas)!";
    }

    if (preg_match('/(obrigado|valeu|obrigada|vlw|tmj|tks|thanks|top|legal|show)/i', $m)) {
        return "Por nada, **$nome**! 🧡 Estou sempre por aqui torcendo pela sua evolução em Java. Bons estudos e continue com o fogo aceso! 💻🔥";
    }

    // 6. RESPOSTAS DINÂMICAS PARA QUALQUER OUTRA DÚVIDA DE PROGRAMAÇÃO OU ESTUDOS
    // Se a mensagem mencionar termos comuns de desenvolvimento
    if (preg_match('/(static|void|main|public|private|protected|final|const|pacote|package|import)/i', $m)) {
        return "💻 **Modificadores e Estrutura em Java:**\n\n" .
               "• `public`: O elemento pode ser acessado de qualquer lugar do projeto.\n" .
               "• `private`: Acessível somente dentro da própria classe (Encapsulamento).\n" .
               "• `static`: Pertence à classe em si, e não a um objeto instanciado (não precisa de `new`).\n" .
               "• `void`: Indica que o método realiza uma ação mas não retorna nenhum valor.\n" .
               "• `final`: Define uma constante (não pode ser alterada) ou impede que uma classe seja herdada.\n\n" .
               "Exemplo clássico:\n" .
               "```java\n" .
               "public static final double PI = 3.14159;\n" .
               "```";
    }

    if (preg_match('/(spring|springboot|spring boot|api|rest|banco|sql|mysql|jdbc|jpa|backend)/i', $m)) {
        return "🚀 **Evolução em Java & Backend:**\n\n" .
               "Depois de dominar o Java Core aqui no Opus, o próximo grande passo no mercado é o ecossistema **Spring Boot**!\n\n" .
               "**O que estudar a seguir:**\n" .
               "1. **Banco de Dados (SQL/MySQL):** Aprender tabelas, SELECT, INSERT, UPDATE e DELETE.\n" .
               "2. **Spring Boot (Web / REST):** Criar APIs que recebem e enviam dados em formato JSON.\n" .
               "3. **Spring Data JPA / Hibernate:** Conectar suas classes Java diretamente com o banco de dados sem escrever SQL manual.\n\n" .
               "💡 Ter 1 ou 2 projetos com Spring Boot no GitHub é o maior diferencial para conquistar vagas de Desenvolvedor Java Júnior!";
    }

    if (preg_match('/(dica|estudar|aprender|como melhorar|dicas|conselho)/i', $m)) {
        return "💡 **Dicas de Ouro do Opi para Voar em Java:**\n\n" .
               "1. **Pratique todo dia:** Conclua pelo menos 1 lição diária no Opus para manter seus **Dias de Fogo** acesos! 🔥\n" .
               "2. **Digite o código:** Em vez de só ler, abra uma IDE (como IntelliJ IDEA ou Eclipse) e digite os exemplos você mesmo.\n" .
               "3. **Entenda o erro:** Quando a JVM der um erro (como NullPointer ou IndexOutOfBounds), leia a mensagem com calma. Ela quase sempre diz a linha exata do problema!\n" .
               "4. **Foco em POO:** A Orientação a Objetos é o coração do Java. Domine o Capítulo 5 com dedicação!";
    }

    // RESPOSTA CONSULTIVA INTELIGENTE PARA QUALQUER PERGUNTA ABERTA
    $clean_msg = htmlspecialchars(trim($msg));
    return "🤖 **Opi IA:**\n\n" .
           "Sobre a sua dúvida: *\"$clean_msg\"* 💡\n\n" .
           "Na programação em Java, a melhor abordagem é sempre dividir grandes desafios em pequenas etapas lógicas:\n\n" .
           "• **Entrada:** Entenda quais dados você precisa receber (via `Scanner` ou parâmetros).\n" .
           "• **Processamento:** Aplique as regras de negócio usando estruturas como `if/else`, laços `for/while` ou classes e métodos.\n" .
           "• **Saída:** Retorne ou imprima o resultado com `System.out.println()`.\n\n" .
           "Você pode me perguntar com mais detalhes sobre a sintaxe de qualquer comando, pedir exemplos de código prático, ou consultar seu progresso no curso e seu nível para vagas de emprego!";
}
