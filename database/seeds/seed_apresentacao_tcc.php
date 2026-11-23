<?php
/**
 * Script de Seed e Reset Idempotente para Apresentação do TCC OPUS
 *
 * Prepara contas dedicadas com estados exatos para cada etapa do roteiro ao vivo:
 *  - inicio@opus.com      : Tour do Opi + Lição 1 + Conta zerada (senha: 123456)
 *  - roleta@opus.com      : Lição 3 (Pareamento) + Baú pronto para girar roleta (senha: 123456)
 *  - ide@opus.com         : IDE Online (Lição 5), 5 Desafios abertos, Ligas, Chat e Perfil (senha: 123456)
 *  - certificado@opus.com : Curso 100% (200/200), 12/12 Troféus, Liga Mestre, Certificado pronto para emitir (senha: 123456)
 *  - admin@opus.com       : Painel Administrativo isolado (senha: admin123)
 *  - semvidas@opus.com    : Conta com 0 vidas e contador de recarga de 30 min (senha: 123456)
 *
 * Uso: php database/seeds/seed_apresentacao_tcc.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../../back/config/conexao.php';
require_once __DIR__ . '/../../back/lib/ligas_logic.php';

$conn->set_charset('utf8mb4');

echo "=== SEED DE APRESENTACAO DO TCC OPUS ===\n";

$semana_ref = liga_semana_atual();
echo "Semana de referencia das ligas: {$semana_ref}\n";

/**
 * Garante um grupo da semana atual com espaco disponivel para a divisao informada.
 */
function seed_garantir_grupo_semana(mysqli $conn, string $divisao, string $semana_ref): int
{
    $stmt = $conn->prepare("
        SELECT g.id
        FROM ligas_grupos g
        WHERE g.divisao = ? AND g.semana_ref = ?
          AND (
              SELECT COUNT(*)
              FROM ligas_usuario lu
              WHERE lu.grupo_id = g.id AND lu.usuario_id NOT BETWEEN 101 AND 126
          ) <= 20
        ORDER BY g.id ASC
        LIMIT 1
    ");
    $stmt->bind_param('ss', $divisao, $semana_ref);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        return (int) $row['id'];
    }

    $stmt_ins = $conn->prepare("INSERT INTO ligas_grupos (divisao, semana_ref, capacidade) VALUES (?, ?, 30)");
    $stmt_ins->bind_param('ss', $divisao, $semana_ref);
    $stmt_ins->execute();
    $gid = (int) $conn->insert_id;
    $stmt_ins->close();

    return $gid;
}

/**
 * Cria ou atualiza um usuario por e-mail e devolve seu ID.
 */
function seed_upsert_usuario(mysqli $conn, array $u): int
{
    $stmt_find = $conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt_find->bind_param('s', $u['email']);
    $stmt_find->execute();
    $existente = $stmt_find->get_result()->fetch_assoc();
    $stmt_find->close();

    $hash = password_hash($u['senha_plana'], PASSWORD_BCRYPT);
    $vidas_proxima = $u['vidas_proxima_em'] ?? null;
    $ultima_ativ = $u['ultima_atividade'] ?? null;
    $link_github = $u['link_github'] ?? null;
    $link_linkedin = $u['link_linkedin'] ?? null;

    if ($existente) {
        $uid = (int) $existente['id'];
        $stmt_up = $conn->prepare("
            UPDATE usuarios SET
                nome = ?,
                email = ?,
                senha = ?,
                nivel_acesso = ?,
                xp = ?,
                vidas = ?,
                vidas_proxima_em = ?,
                dias_fogo = ?,
                ultima_atividade = ?,
                cor_fundo = ?,
                username = ?,
                link_github = ?,
                link_linkedin = ?,
                tour_visto = ?,
                tour_perfil_visto = ?,
                tour_licao1_visto = ?,
                tour_resultado1_visto = ?,
                tour_fim_curso_visto = ?
            WHERE id = ?
        ");
        $stmt_up->bind_param(
            'ssssiisisssssiiiiii',
            $u['nome'],
            $u['email'],
            $hash,
            $u['nivel_acesso'],
            $u['xp'],
            $u['vidas'],
            $vidas_proxima,
            $u['dias_fogo'],
            $ultima_ativ,
            $u['cor_fundo'],
            $u['username'],
            $link_github,
            $link_linkedin,
            $u['tour_visto'],
            $u['tour_perfil_visto'],
            $u['tour_licao1_visto'],
            $u['tour_resultado1_visto'],
            $u['tour_fim_curso_visto'],
            $uid
        );
        $stmt_up->execute();
        $stmt_up->close();
        return $uid;
    }

    $stmt_ins = $conn->prepare("
        INSERT INTO usuarios (
            nome, email, senha, nivel_acesso, xp, trofeus, vidas, vidas_proxima_em,
            dias_fogo, ultima_atividade, cor_fundo, username, link_github, link_linkedin,
            tour_visto, tour_perfil_visto, tour_licao1_visto, tour_resultado1_visto, tour_fim_curso_visto
        ) VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt_ins->bind_param(
        'ssssiisisssssiiiii',
        $u['nome'],
        $u['email'],
        $hash,
        $u['nivel_acesso'],
        $u['xp'],
        $u['vidas'],
        $vidas_proxima,
        $u['dias_fogo'],
        $ultima_ativ,
        $u['cor_fundo'],
        $u['username'],
        $link_github,
        $link_linkedin,
        $u['tour_visto'],
        $u['tour_perfil_visto'],
        $u['tour_licao1_visto'],
        $u['tour_resultado1_visto'],
        $u['tour_fim_curso_visto']
    );
    $stmt_ins->execute();
    $uid = (int) $conn->insert_id;
    $stmt_ins->close();

    return $uid;
}

/**
 * Limpa os registros transacionais de um usuario demo para recriar estado limpo.
 */
function seed_limpar_estado_aluno(mysqli $conn, int $uid, bool $tem_certificados): void
{
    $tabelas_usuario_id = [
        'progresso_usuario',
        'bau_recompensas',
        'missoes_diarias_usuario',
        'acertos_diarios',
        'desafios_projetos_usuario',
        'conquistas_usuario',
        'ligas_usuario',
    ];
    if ($tem_certificados) {
        $tabelas_usuario_id[] = 'certificados_emitidos';
    }

    foreach ($tabelas_usuario_id as $tabela) {
        $stmt = $conn->prepare("DELETE FROM `{$tabela}` WHERE usuario_id = ?");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $stmt->close();
    }

    $stmt_tr = $conn->prepare("DELETE FROM user_trofeus WHERE user_id = ?");
    $stmt_tr->bind_param('i', $uid);
    $stmt_tr->execute();
    $stmt_tr->close();

    $stmt_ped = $conn->prepare("DELETE FROM pedidos_conexao WHERE remetente_id = ? OR destinatario_id = ?");
    $stmt_ped->bind_param('ii', $uid, $uid);
    $stmt_ped->execute();
    $stmt_ped->close();

    $stmt_msg = $conn->prepare("DELETE FROM chat_mensagens WHERE remetente_id = ? OR destinatario_id = ?");
    $stmt_msg->bind_param('ii', $uid, $uid);
    $stmt_msg->execute();
    $stmt_msg->close();

    $stmt_seg = $conn->prepare("DELETE FROM seguidores WHERE seguidor_id = ? OR seguido_id = ?");
    $stmt_seg->bind_param('ii', $uid, $uid);
    $stmt_seg->execute();
    $stmt_seg->close();

    // Garante contador zerado quando nao houver triggers disparados em tabelas vazias
    $stmt_zero = $conn->prepare("UPDATE usuarios SET trofeus = 0 WHERE id = ?");
    $stmt_zero->bind_param('i', $uid);
    $stmt_zero->execute();
    $stmt_zero->close();
}

/**
 * Define progresso nas unidades do usuario.
 */
function seed_definir_progresso(mysqli $conn, int $uid, array $mapa_unidades): void
{
    $stmt = $conn->prepare("
        INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($mapa_unidades as $unidade => [$status, $licoes]) {
        $u_num = (int) $unidade;
        $l_num = (int) $licoes;
        $stmt->bind_param('iisi', $uid, $u_num, $status, $l_num);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Registra baus ja resgatados pelo usuario nas unidades informadas.
 */
function seed_definir_baus(mysqli $conn, int $uid, array $unidades): void
{
    if (empty($unidades)) {
        return;
    }
    $stmt = $conn->prepare("
        INSERT INTO bau_recompensas (usuario_id, unidade_numero, tipo_recompensa, xp_ganho, vidas_ganhas)
        VALUES (?, ?, 'xp10', 10, 0)
    ");
    foreach ($unidades as $unid) {
        $u_num = (int) $unid;
        $stmt->bind_param('ii', $uid, $u_num);
        $stmt->execute();
    }
    $stmt->close();
}

/**
 * Atribui trofeus e conquistas ao usuario (triggers mantem usuarios.trofeus sincronizado).
 */
function seed_definir_trofeus(mysqli $conn, int $uid, array $slugs): void
{
    if (empty($slugs)) {
        return;
    }
    $stmt_ut = $conn->prepare("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (?, ?)");
    $stmt_cq = $conn->prepare("INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (?, ?)");
    foreach ($slugs as $slug) {
        $stmt_ut->bind_param('is', $uid, $slug);
        $stmt_ut->execute();
        $stmt_cq->bind_param('is', $uid, $slug);
        $stmt_cq->execute();
    }
    $stmt_ut->close();
    $stmt_cq->close();
}

/**
 * Insere registro de missao diaria de hoje para o usuario.
 */
function seed_definir_missao_hoje(mysqli $conn, int $uid, string $hoje, int $xp, int $licoes, int $perfeitas): void
{
    $stmt = $conn->prepare("
        INSERT INTO missoes_diarias_usuario (usuario_id, data_ref, xp_ganho, licoes_concluidas, licoes_perfeitas)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('isiii', $uid, $hoje, $xp, $licoes, $perfeitas);
    $stmt->execute();
    $stmt->close();
}

/**
 * Aloca usuario em grupo e divisao da semana atual.
 */
function seed_definir_liga(mysqli $conn, int $uid, string $divisao, int $grupo_id, int $xp_semana): void
{
    $stmt = $conn->prepare("
        INSERT INTO ligas_usuario (usuario_id, divisao, grupo_id, xp_semana)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            divisao = VALUES(divisao),
            grupo_id = VALUES(grupo_id),
            xp_semana = VALUES(xp_semana)
    ");
    $stmt->bind_param('isii', $uid, $divisao, $grupo_id, $xp_semana);
    $stmt->execute();
    $stmt->close();
}

/**
 * Busca colegas de teste existentes para preencher conexoes e chat sem violar triggers.
 */
function seed_buscar_colegas_teste(mysqli $conn, int $excluir_uid): array
{
    $preferidos = [
        'helena.martins@opus-teste.com',
        'lucas.mendes@opus-teste.com',
        'felipe.oliveira@opus-teste.com',
    ];
    $colegas = [];

    $stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ? AND id <> ? AND nivel_acesso = 'comum' LIMIT 1");
    foreach ($preferidos as $email) {
        $stmt->bind_param('si', $email, $excluir_uid);
        $stmt->execute();
        if ($row = $stmt->get_result()->fetch_assoc()) {
            $colegas[] = (int) $row['id'];
        }
    }
    $stmt->close();

    if (count($colegas) < 3) {
        $stmt_fb = $conn->prepare("SELECT id FROM usuarios WHERE id <> ? AND nivel_acesso = 'comum' ORDER BY id ASC LIMIT 10");
        $stmt_fb->bind_param('i', $excluir_uid);
        $stmt_fb->execute();
        $res_fb = $stmt_fb->get_result();
        while ($r = $res_fb->fetch_assoc()) {
            $cid = (int) $r['id'];
            if (!in_array($cid, $colegas, true)) {
                $colegas[] = $cid;
                if (count($colegas) >= 3) {
                    break;
                }
            }
        }
        $stmt_fb->close();
    }

    return $colegas;
}

/**
 * Cria amizades aceitas com historico de chat (incluindo nao lida) e um pedido pendente.
 */
function seed_popular_social_ide(mysqli $conn, int $uid, array $colegas): void
{
    if (count($colegas) < 3) {
        return;
    }

    [$amigo1, $amigo2, $pendente3] = [$colegas[0], $colegas[1], $colegas[2]];

    // 2 conexoes aceitas e seguidores mutuos
    $stmt_aceito = $conn->prepare("
        INSERT INTO pedidos_conexao (remetente_id, destinatario_id, status, notificado_aceite, data_resposta, viraram_amigos)
        VALUES (?, ?, 'aceito', 1, NOW(), 1)
    ");
    foreach ([$amigo1, $amigo2] as $aid) {
        $stmt_aceito->bind_param('ii', $uid, $aid);
        $stmt_aceito->execute();
    }
    $stmt_aceito->close();

    $stmt_seg = $conn->prepare("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
    foreach ([$amigo1, $amigo2] as $aid) {
        $stmt_seg->bind_param('ii', $uid, $aid);
        $stmt_seg->execute();
        $stmt_seg->bind_param('ii', $aid, $uid);
        $stmt_seg->execute();
    }
    $stmt_seg->close();

    // 1 pedido pendente recebido (aparece no banner do chat e no sino de notificacoes)
    $stmt_pend = $conn->prepare("
        INSERT INTO pedidos_conexao (remetente_id, destinatario_id, status, notificado_aceite, viraram_amigos)
        VALUES (?, ?, 'pendente', 0, 0)
    ");
    $stmt_pend->bind_param('ii', $pendente3, $uid);
    $stmt_pend->execute();
    $stmt_pend->close();

    // Mensagens realistas no chat
    $mensagens = [
        [$amigo1, $uid, 'Oi Gabriele! Já conseguiu rodar o desafio da Calculadora na IDE?', 'lido'],
        [$uid, $amigo1, 'Sim! A compilação com OpenJDK 21 funcionou certinho aqui.', 'lido'],
        [$amigo1, $uid, 'Show demais! Bora disputar o topo da Liga Ouro essa semana!', 'nao_lido'],
        [$amigo2, $uid, 'E aí, curtiu o exercício Caçador de Bugs da seção de variáveis?', 'lido'],
        [$uid, $amigo2, 'Demais! Me ajudou a lembrar do ponto e vírgula em Java.', 'lido'],
    ];

    $stmt_msg = $conn->prepare("
        INSERT INTO chat_mensagens (remetente_id, destinatario_id, mensagem, status_leitura, data_envio)
        VALUES (?, ?, ?, ?, ?)
    ");
    $offsets_seg = ['-25 minutes', '-18 minutes', '-2 minutes', '-3 hours', '-2 hours'];
    foreach ($mensagens as $idx => [$rem, $dest, $texto, $leitura]) {
        $data_env = date('Y-m-d H:i:s', strtotime($offsets_seg[$idx]));
        $stmt_msg->bind_param('iisss', $rem, $dest, $texto, $leitura, $data_env);
        $stmt_msg->execute();
    }
    $stmt_msg->close();
}

// 1. Garante grupos da semana atual para as 5 divisoes
$grupos_semana = [];
foreach (['bronze', 'prata', 'ouro', 'diamante', 'mestre'] as $divisao) {
    $grupos_semana[$divisao] = seed_garantir_grupo_semana($conn, $divisao, $semana_ref);
}

// 2. Popula ligas da semana atual com contas @opus-teste.com (IDs 101..126) sem alterar seus outros dados
$alocacao_teste = [
    // Mestre (7)
    101 => ['mestre', 1620],
    102 => ['mestre', 1480],
    103 => ['mestre', 1310],
    104 => ['mestre', 980],
    105 => ['mestre', 840],
    106 => ['mestre', 720],
    107 => ['mestre', 590],
    // Diamante (4)
    108 => ['diamante', 790],
    109 => ['diamante', 680],
    111 => ['diamante', 540],
    112 => ['diamante', 420],
    // Ouro (6 -> + ide@opus.com = 7)
    110 => ['ouro', 560],
    113 => ['ouro', 410],
    114 => ['ouro', 330],
    115 => ['ouro', 250],
    117 => ['ouro', 170],
    118 => ['ouro', 90],
    // Prata (6 -> + roleta@opus.com = 7)
    116 => ['prata', 260],
    119 => ['prata', 195],
    120 => ['prata', 145],
    122 => ['prata', 95],
    123 => ['prata', 60],
    124 => ['prata', 25],
    // Bronze (3 -> + inicio@opus.com e semvidas@opus.com = 5+)
    121 => ['bronze', 190],
    125 => ['bronze', 110],
    126 => ['bronze', 45],
];

$stmt_check_id = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND email LIKE '%@opus-teste.com' LIMIT 1");
$alocados_teste = 0;
foreach ($alocacao_teste as $tid => [$div, $xp_sem]) {
    $stmt_check_id->bind_param('i', $tid);
    $stmt_check_id->execute();
    if ($stmt_check_id->get_result()->fetch_assoc()) {
        seed_definir_liga($conn, $tid, $div, $grupos_semana[$div], $xp_sem);
        $alocados_teste++;
    }
}
$stmt_check_id->close();
echo "Contas @opus-teste.com (IDs 101-126) alocadas nos grupos da semana: {$alocados_teste}\n";

$chk_cert_tb = $conn->query("SHOW TABLES LIKE 'certificados_emitidos'");
$tem_certificados = ($chk_cert_tb && $chk_cert_tb->num_rows > 0);
$hoje = date('Y-m-d');
$em_25_min = date('Y-m-d H:i:s', time() + (25 * 60));

// 3. Define as 6 contas dedicadas da apresentacao
$contas_demo = [
    'inicio' => [
        'nome'                  => 'Gabriele Iniciante',
        'email'                 => 'inicio@opus.com',
        'senha_plana'           => '123456',
        'nivel_acesso'          => 'comum',
        'xp'                    => 0,
        'vidas'                 => 3,
        'vidas_proxima_em'      => null,
        'dias_fogo'             => 0,
        'ultima_atividade'      => null,
        'cor_fundo'             => '#1cb0f6',
        'username'              => 'gabi.inicio',
        'link_github'           => null,
        'link_linkedin'         => null,
        'tour_visto'            => 0,
        'tour_perfil_visto'     => 0,
        'tour_licao1_visto'     => 0,
        'tour_resultado1_visto' => 0,
        'tour_fim_curso_visto'  => 0,
    ],
    'roleta' => [
        'nome'                  => 'Gabriele Prática',
        'email'                 => 'roleta@opus.com',
        'senha_plana'           => '123456',
        'nivel_acesso'          => 'comum',
        'xp'                    => 100,
        'vidas'                 => 3,
        'vidas_proxima_em'      => null,
        'dias_fogo'             => 2,
        'ultima_atividade'      => $hoje,
        'cor_fundo'             => '#ff9600',
        'username'              => 'gabi.pratica',
        'link_github'           => null,
        'link_linkedin'         => null,
        'tour_visto'            => 1,
        'tour_perfil_visto'     => 1,
        'tour_licao1_visto'     => 1,
        'tour_resultado1_visto' => 1,
        'tour_fim_curso_visto'  => 1,
    ],
    'ide' => [
        'nome'                  => 'Gabriele Dev',
        'email'                 => 'ide@opus.com',
        'senha_plana'           => '123456',
        'nivel_acesso'          => 'comum',
        'xp'                    => 1850,
        'vidas'                 => 3,
        'vidas_proxima_em'      => null,
        'dias_fogo'             => 7,
        'ultima_atividade'      => $hoje,
        'cor_fundo'             => '#ce82ff',
        'username'              => 'gabi.dev',
        'link_github'           => 'https://github.com/gabriele-opus',
        'link_linkedin'         => 'https://linkedin.com/in/gabriele-opus',
        'tour_visto'            => 1,
        'tour_perfil_visto'     => 1,
        'tour_licao1_visto'     => 1,
        'tour_resultado1_visto' => 1,
        'tour_fim_curso_visto'  => 1,
    ],
    'certificado' => [
        'nome'                  => 'Gabriele dos Santos de Freitas',
        'email'                 => 'certificado@opus.com',
        'senha_plana'           => '123456',
        'nivel_acesso'          => 'comum',
        'xp'                    => 14800,
        'vidas'                 => 3,
        'vidas_proxima_em'      => null,
        'dias_fogo'             => 30,
        'ultima_atividade'      => $hoje,
        'cor_fundo'             => '#58cc02',
        'username'              => 'gabriele.freitas',
        'link_github'           => 'https://github.com/gabriele-opus',
        'link_linkedin'         => 'https://linkedin.com/in/gabriele-opus',
        'tour_visto'            => 1,
        'tour_perfil_visto'     => 1,
        'tour_licao1_visto'     => 1,
        'tour_resultado1_visto' => 1,
        'tour_fim_curso_visto'  => 0,
    ],
    'semvidas' => [
        'nome'                  => 'Gabriele Sem Vidas',
        'email'                 => 'semvidas@opus.com',
        'senha_plana'           => '123456',
        'nivel_acesso'          => 'comum',
        'xp'                    => 40,
        'vidas'                 => 0,
        'vidas_proxima_em'      => $em_25_min,
        'dias_fogo'             => 1,
        'ultima_atividade'      => $hoje,
        'cor_fundo'             => '#ff4b4b',
        'username'              => 'gabi.semvidas',
        'link_github'           => null,
        'link_linkedin'         => null,
        'tour_visto'            => 1,
        'tour_perfil_visto'     => 1,
        'tour_licao1_visto'     => 1,
        'tour_resultado1_visto' => 1,
        'tour_fim_curso_visto'  => 1,
    ],
];

$ids_demo = [];
foreach ($contas_demo as $chave => $dados) {
    $uid = seed_upsert_usuario($conn, $dados);
    $ids_demo[$chave] = $uid;
    seed_limpar_estado_aluno($conn, $uid, $tem_certificados);
}

// --- Conta 1: inicio@opus.com ---
seed_definir_progresso($conn, $ids_demo['inicio'], [
    1 => ['corrente', 0],
    2 => ['trancado', 0],
    3 => ['trancado', 0],
    4 => ['trancado', 0],
    5 => ['trancado', 0],
]);
seed_definir_missao_hoje($conn, $ids_demo['inicio'], $hoje, 0, 0, 0);
seed_definir_liga($conn, $ids_demo['inicio'], 'bronze', $grupos_semana['bronze'], 0);
echo "Conta 1 pronta: inicio@opus.com / 123456 [ID {$ids_demo['inicio']}]\n";

// --- Conta 2: roleta@opus.com ---
seed_definir_progresso($conn, $ids_demo['roleta'], [
    1 => ['corrente', 2],
    2 => ['trancado', 0],
    3 => ['trancado', 0],
    4 => ['trancado', 0],
    5 => ['trancado', 0],
]);
seed_definir_trofeus($conn, $ids_demo['roleta'], ['primeiro_passo', 'perfeicao']);
seed_definir_missao_hoje($conn, $ids_demo['roleta'], $hoje, 40, 1, 1);
seed_definir_liga($conn, $ids_demo['roleta'], 'prata', $grupos_semana['prata'], 120);
echo "Conta 2 pronta: roleta@opus.com / 123456 [ID {$ids_demo['roleta']}]\n";

// --- Conta 3: ide@opus.com ---
seed_definir_progresso($conn, $ids_demo['ide'], [
    1 => ['completo', 5],
    2 => ['completo', 5],
    3 => ['completo', 5],
    4 => ['completo', 5],
    5 => ['completo', 5],
    6 => ['corrente', 4],
]);
seed_definir_baus($conn, $ids_demo['ide'], [1, 2, 3, 4, 5]);
seed_definir_trofeus($conn, $ids_demo['ide'], [
    'primeiro_passo',
    'perfeicao',
    'capitulo_1',
    'fogo_3',
    'precisao_absoluta',
    'sequencia_7',
]);
seed_definir_missao_hoje($conn, $ids_demo['ide'], $hoje, 90, 2, 1);
seed_definir_liga($conn, $ids_demo['ide'], 'ouro', $grupos_semana['ouro'], 480);
$colegas_ide = seed_buscar_colegas_teste($conn, $ids_demo['ide']);
seed_popular_social_ide($conn, $ids_demo['ide'], $colegas_ide);
echo "Conta 3 pronta: ide@opus.com / 123456 [ID {$ids_demo['ide']}]\n";

// --- Conta 4: certificado@opus.com ---
$progresso_completo = [];
$todas_unidades = [];
for ($u = 1; $u <= 40; $u++) {
    $progresso_completo[$u] = ['completo', 5];
    $todas_unidades[] = $u;
}
seed_definir_progresso($conn, $ids_demo['certificado'], $progresso_completo);
seed_definir_baus($conn, $ids_demo['certificado'], $todas_unidades);
seed_definir_trofeus($conn, $ids_demo['certificado'], [
    'primeiro_passo',
    'perfeicao',
    'capitulo_1',
    'capitulo_2',
    'capitulo_3',
    'capitulo_4',
    'capitulo_5',
    'fogo_3',
    'precisao_absoluta',
    'sequencia_7',
    'sequencia_30',
    'batalha_irmaos',
]);
seed_definir_missao_hoje($conn, $ids_demo['certificado'], $hoje, 150, 3, 2);
seed_definir_liga($conn, $ids_demo['certificado'], 'mestre', $grupos_semana['mestre'], 1150);

// Garante pelo menos 1 conversa aceita no chat da conta certificado@opus.com
$colegas_cert = seed_buscar_colegas_teste($conn, $ids_demo['certificado']);
if (!empty($colegas_cert)) {
    $c0 = $colegas_cert[0];
    $stmt_c = $conn->prepare("
        INSERT INTO pedidos_conexao (remetente_id, destinatario_id, status, notificado_aceite, data_resposta, viraram_amigos)
        VALUES (?, ?, 'aceito', 1, NOW(), 1)
    ");
    $stmt_c->bind_param('ii', $ids_demo['certificado'], $c0);
    $stmt_c->execute();
    $stmt_c->close();

    $stmt_s = $conn->prepare("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES (?, ?)");
    $stmt_s->bind_param('ii', $ids_demo['certificado'], $c0);
    $stmt_s->execute();
    $stmt_s->bind_param('ii', $c0, $ids_demo['certificado']);
    $stmt_s->execute();
    $stmt_s->close();

    $stmt_m = $conn->prepare("
        INSERT INTO chat_mensagens (remetente_id, destinatario_id, mensagem, status_leitura, data_envio)
        VALUES (?, ?, ?, 'lido', ?)
    ");
    $msg_parabens = 'Parabéns por concluir todas as 200 lições do OPUS! Já emitiu seu certificado?';
    $data_parabens = date('Y-m-d H:i:s', strtotime('-15 minutes'));
    $stmt_m->bind_param('iiss', $c0, $ids_demo['certificado'], $msg_parabens, $data_parabens);
    $stmt_m->execute();
    $stmt_m->close();
}
echo "Conta 4 pronta: certificado@opus.com / 123456 [ID {$ids_demo['certificado']}]\n";

// --- Conta 6 (Bonus): semvidas@opus.com ---
seed_definir_progresso($conn, $ids_demo['semvidas'], [
    1 => ['corrente', 1],
    2 => ['trancado', 0],
    3 => ['trancado', 0],
    4 => ['trancado', 0],
    5 => ['trancado', 0],
]);
seed_definir_missao_hoje($conn, $ids_demo['semvidas'], $hoje, 40, 1, 0);
seed_definir_liga($conn, $ids_demo['semvidas'], 'bronze', $grupos_semana['bronze'], 40);
echo "Conta 6 pronta: semvidas@opus.com / 123456 [ID {$ids_demo['semvidas']}]\n";

// --- Conta 5: admin@opus.com (garante existencia, nivel admin e senha admin123 sem apagar dados) ---
$hash_admin = password_hash('admin123', PASSWORD_BCRYPT);
$stmt_adm = $conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = 'admin@opus.com' LIMIT 1");
$stmt_adm->execute();
$adm_row = $stmt_adm->get_result()->fetch_assoc();
$stmt_adm->close();

if ($adm_row) {
    $admin_id = (int) $adm_row['id'];
    $stmt_upd_adm = $conn->prepare("UPDATE usuarios SET senha = ?, nivel_acesso = 'admin' WHERE id = ?");
    $stmt_upd_adm->bind_param('si', $hash_admin, $admin_id);
    $stmt_upd_adm->execute();
    $stmt_upd_adm->close();
} else {
    $stmt_ins_adm = $conn->prepare("
        INSERT INTO usuarios (nome, email, senha, nivel_acesso, xp, trofeus, vidas, dias_fogo, cor_fundo, username)
        VALUES ('Administrador OPUS', 'admin@opus.com', ?, 'admin', 0, 0, 3, 0, '#58cc02', 'admin_opus')
    ");
    $stmt_ins_adm->bind_param('s', $hash_admin);
    $stmt_ins_adm->execute();
    $admin_id = (int) $conn->insert_id;
    $stmt_ins_adm->close();
}
echo "Conta 5 pronta: admin@opus.com / admin123 [ID {$admin_id}]\n";

echo "=== SEED DE APRESENTACAO FINALIZADO COM SUCESSO! ===\n";
