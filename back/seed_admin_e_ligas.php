<?php
/**
 * Script de Seed: Criação/Atualização de Login de Administrador e Usuários de Teste em Diferentes Ligas
 * Projeto: OPUS Gamificação
 */

require_once __DIR__ . '/conexao.php';

echo "=== INICIANDO SEED DO BANCO DE DADOS OPUS ===\n";

// 1. Hashes de Senha
$hash_admin = password_hash('admin123', PASSWORD_BCRYPT);
$hash_user  = password_hash('123456', PASSWORD_BCRYPT);

// 2. Garante a tabela ligas_grupos e grupos da semana atual
$semana_sql = $conn->query("SELECT DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AS sem")->fetch_assoc()['sem'];
echo "Semana de referência de ligas: $semana_sql\n";

$divisoes = ['bronze', 'prata', 'ouro', 'diamante', 'mestre'];
$grupos_ids = [];

foreach ($divisoes as $div) {
    $stmt = $conn->prepare("SELECT id FROM ligas_grupos WHERE divisao = ? AND semana_ref = ? LIMIT 1");
    $stmt->bind_param("ss", $div, $semana_sql);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res) {
        $grupos_ids[$div] = (int) $res['id'];
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO ligas_grupos (divisao, semana_ref, capacidade) VALUES (?, ?, 30)");
        $stmt_ins->bind_param("ss", $div, $semana_sql);
        $stmt_ins->execute();
        $grupos_ids[$div] = $conn->insert_id;
    }
}
echo "Grupos de ligas prontos.\n";

// 3. Lista de Usuários a Cadastrar / Atualizar
$usuarios_dados = [
    // --- ADMINISTRADORES ---
    [
        'nome' => 'Administrador OPUS',
        'email' => 'admin@opus.com',
        'senha' => $hash_admin,
        'nivel_acesso' => 'admin',
        'xp' => 2500,
        'trofeus' => 12,
        'dias_fogo' => 14,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#58cc02',
        'username' => 'admin_opus',
        'divisao' => 'mestre',
        'xp_semana' => 950,
        'caps_completos' => [1, 2, 3, 4]
    ],
    [
        'nome' => 'Gabriel Freitas',
        'email' => 'gabriel@opus.com',
        'senha' => $hash_admin,
        'nivel_acesso' => 'admin',
        'xp' => 3800,
        'trofeus' => 15,
        'dias_fogo' => 30,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#1cb0f6',
        'username' => 'gabriel_dev',
        'divisao' => 'mestre',
        'xp_semana' => 1200,
        'caps_completos' => [1, 2, 3, 4, 5]
    ],

    // --- LIGA BRONZE ---
    [
        'nome' => 'Ana Silva',
        'email' => 'ana.silva@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 180,
        'trofeus' => 2,
        'dias_fogo' => 2,
        'vidas' => 3,
        'dificuldade' => 'Iniciante',
        'cor_fundo' => '#ff4b4b',
        'username' => 'ana_silva',
        'divisao' => 'bronze',
        'xp_semana' => 45,
        'caps_completos' => []
    ],
    [
        'nome' => 'Pedro Rocha',
        'email' => 'pedro.rocha@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 90,
        'trofeus' => 1,
        'dias_fogo' => 1,
        'vidas' => 1,
        'dificuldade' => 'Iniciante',
        'cor_fundo' => '#1cb0f6',
        'username' => 'pedro_dev',
        'divisao' => 'bronze',
        'xp_semana' => 25,
        'caps_completos' => []
    ],
    [
        'nome' => 'Júlia Santos',
        'email' => 'julia.santos@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 50,
        'trofeus' => 0,
        'dias_fogo' => 0,
        'vidas' => 0,
        'dificuldade' => 'Iniciante',
        'cor_fundo' => '#ff9600',
        'username' => 'ju_santos',
        'divisao' => 'bronze',
        'xp_semana' => 15,
        'caps_completos' => []
    ],

    // --- LIGA PRATA ---
    [
        'nome' => 'Lucas Mendes',
        'email' => 'lucas@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 450,
        'trofeus' => 4,
        'dias_fogo' => 4,
        'vidas' => 2,
        'dificuldade' => 'Iniciante',
        'cor_fundo' => '#ce82ff',
        'username' => 'lucas_mendes',
        'divisao' => 'prata',
        'xp_semana' => 140,
        'caps_completos' => [1]
    ],
    [
        'nome' => 'Felipe Oliveira',
        'email' => 'felipe.oliveira@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 530,
        'trofeus' => 5,
        'dias_fogo' => 6,
        'vidas' => 3,
        'dificuldade' => 'Intermediário',
        'cor_fundo' => '#00cd9c',
        'username' => 'felipe_oli',
        'divisao' => 'prata',
        'xp_semana' => 180,
        'caps_completos' => [1]
    ],
    [
        'nome' => 'Camila Ribeiro',
        'email' => 'camila.ribeiro@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 390,
        'trofeus' => 3,
        'dias_fogo' => 3,
        'vidas' => 3,
        'dificuldade' => 'Iniciante',
        'cor_fundo' => '#e11d48',
        'username' => 'cami_rib',
        'divisao' => 'prata',
        'xp_semana' => 110,
        'caps_completos' => [1]
    ],

    // --- LIGA OURO ---
    [
        'nome' => 'Mariana Costa',
        'email' => 'mariana@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 950,
        'trofeus' => 7,
        'dias_fogo' => 8,
        'vidas' => 3,
        'dificuldade' => 'Intermediário',
        'cor_fundo' => '#ff9600',
        'username' => 'mari_costa',
        'divisao' => 'ouro',
        'xp_semana' => 320,
        'caps_completos' => [1, 2]
    ],
    [
        'nome' => 'Bruno Carvalho',
        'email' => 'bruno.carvalho@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 1100,
        'trofeus' => 8,
        'dias_fogo' => 12,
        'vidas' => 2,
        'dificuldade' => 'Intermediário',
        'cor_fundo' => '#d97706',
        'username' => 'bruno_code',
        'divisao' => 'ouro',
        'xp_semana' => 410,
        'caps_completos' => [1, 2]
    ],
    [
        'nome' => 'Larissa Souza',
        'email' => 'larissa.souza@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 880,
        'trofeus' => 6,
        'dias_fogo' => 7,
        'vidas' => 3,
        'dificuldade' => 'Intermediário',
        'cor_fundo' => '#9333ea',
        'username' => 'lari_souza',
        'divisao' => 'ouro',
        'xp_semana' => 290,
        'caps_completos' => [1, 2]
    ],
    [
        'nome' => 'Rodrigo Lima',
        'email' => 'rodrigo.lima@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 820,
        'trofeus' => 6,
        'dias_fogo' => 5,
        'vidas' => 1,
        'dificuldade' => 'Intermediário',
        'cor_fundo' => '#2563eb',
        'username' => 'rodrigo_l',
        'divisao' => 'ouro',
        'xp_semana' => 260,
        'caps_completos' => [1, 2]
    ],

    // --- LIGA DIAMANTE ---
    [
        'nome' => 'Rafael Duarte',
        'email' => 'rafael.duarte@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 2100,
        'trofeus' => 10,
        'dias_fogo' => 18,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#06b6d4',
        'username' => 'rafa_duarte',
        'divisao' => 'diamante',
        'xp_semana' => 650,
        'caps_completos' => [1, 2, 3]
    ],
    [
        'nome' => 'Beatriz Almeida',
        'email' => 'beatriz.almeida@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 2450,
        'trofeus' => 11,
        'dias_fogo' => 21,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#3b82f6',
        'username' => 'bea_almeida',
        'divisao' => 'diamante',
        'xp_semana' => 780,
        'caps_completos' => [1, 2, 3]
    ],
    [
        'nome' => 'Thiago Ferreira',
        'email' => 'thiago.ferreira@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 1950,
        'trofeus' => 9,
        'dias_fogo' => 15,
        'vidas' => 2,
        'dificuldade' => 'Intermediário',
        'cor_fundo' => '#84cc16',
        'username' => 'thiago_f',
        'divisao' => 'diamante',
        'xp_semana' => 590,
        'caps_completos' => [1, 2, 3]
    ],

    // --- LIGA MESTRE ---
    [
        'nome' => 'Helena Martins',
        'email' => 'helena.martins@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 4800,
        'trofeus' => 18,
        'dias_fogo' => 45,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#a855f7',
        'username' => 'helena_m',
        'divisao' => 'mestre',
        'xp_semana' => 1550,
        'caps_completos' => [1, 2, 3, 4]
    ],
    [
        'nome' => 'Vinicius Prado',
        'email' => 'vinicius.prado@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 3900,
        'trofeus' => 14,
        'dias_fogo' => 28,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#ec4899',
        'username' => 'vini_prado',
        'divisao' => 'mestre',
        'xp_semana' => 1180,
        'caps_completos' => [1, 2, 3, 4]
    ],
    [
        'nome' => 'Sophia Castro',
        'email' => 'sophia.castro@email.com',
        'senha' => $hash_user,
        'nivel_acesso' => 'comum',
        'xp' => 4350,
        'trofeus' => 16,
        'dias_fogo' => 35,
        'vidas' => 3,
        'dificuldade' => 'Avançado',
        'cor_fundo' => '#6366f1',
        'username' => 'sophia_c',
        'divisao' => 'mestre',
        'xp_semana' => 1340,
        'caps_completos' => [1, 2, 3, 4, 5]
    ]
];

$user_ids_criados = [];

foreach ($usuarios_dados as $u) {
    // Verifica se já existe pelo e-mail
    $stmt_busca = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_busca->bind_param("s", $u['email']);
    $stmt_busca->execute();
    $existente = $stmt_busca->get_result()->fetch_assoc();

    $usuario_id = null;
    if ($existente) {
        $usuario_id = (int) $existente['id'];
        $stmt_up = $conn->prepare("
            UPDATE usuarios 
            SET nome = ?, senha = ?, nivel_acesso = ?, xp = ?, trofeus = ?, dias_fogo = ?, vidas = ?, dificuldade = ?, cor_fundo = ?, username = ?
            WHERE id = ?
        ");
        $stmt_up->bind_param("sssiiiisssi", 
            $u['nome'], $u['senha'], $u['nivel_acesso'], $u['xp'], $u['trofeus'], 
            $u['dias_fogo'], $u['vidas'], $u['dificuldade'], $u['cor_fundo'], $u['username'], $usuario_id
        );
        $stmt_up->execute();
        echo "Usuário atualizado: {$u['nome']} ({$u['email']}) [ID $usuario_id]\n";
    } else {
        $stmt_ins = $conn->prepare("
            INSERT INTO usuarios (nome, email, senha, nivel_acesso, xp, trofeus, dias_fogo, vidas, dificuldade, cor_fundo, username)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_ins->bind_param("ssssiiiisss", 
            $u['nome'], $u['email'], $u['senha'], $u['nivel_acesso'], $u['xp'], $u['trofeus'], 
            $u['dias_fogo'], $u['vidas'], $u['dificuldade'], $u['cor_fundo'], $u['username']
        );
        $stmt_ins->execute();
        $usuario_id = $conn->insert_id;
        echo "Novo usuário inserido: {$u['nome']} ({$u['email']}) [ID $usuario_id]\n";
    }

    $user_ids_criados[$u['email']] = $usuario_id;
    $div = $u['divisao'];
    $gid = $grupos_ids[$div] ?? null;

    // Aloca / Atualiza na tabela ligas_usuario
    $stmt_liga = $conn->prepare("
        INSERT INTO ligas_usuario (usuario_id, divisao, grupo_id, xp_semana)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE divisao = VALUES(divisao), grupo_id = VALUES(grupo_id), xp_semana = VALUES(xp_semana)
    ");
    $stmt_liga->bind_param("isii", $usuario_id, $div, $gid, $u['xp_semana']);
    $stmt_liga->execute();

    // Atualiza progresso nos 5 capítulos
    for ($cap = 1; $cap <= 5; $cap++) {
        $status = 'trancado';
        $licoes = 0;
        if (in_array($cap, $u['caps_completos'])) {
            $status = 'completo';
            $licoes = 3;
        } elseif ($cap === (count($u['caps_completos']) + 1)) {
            $status = 'corrente';
            $licoes = 1;
        }

        $stmt_prog = $conn->prepare("
            INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status), licoes_concluidas = VALUES(licoes_concluidas)
        ");
        $stmt_prog->bind_param("iisi", $usuario_id, $cap, $status, $licoes);
        $stmt_prog->execute();
    }
}

// 4. Criação de Histórico de Ligas de exemplo (para visualização no Modal de Detalhes)
echo "Gerando histórico de ligas...\n";
$semana_passada = date('Y-m-d', strtotime("$semana_sql -7 days"));

$historico_exemplos = [
    ['email' => 'helena.martins@email.com', 'ant' => 'diamante', 'nova' => 'mestre', 'res' => 'subiu', 'pos' => 1, 'xp' => 1650],
    ['email' => 'rafael.duarte@email.com', 'ant' => 'ouro', 'nova' => 'diamante', 'res' => 'subiu', 'pos' => 2, 'xp' => 890],
    ['email' => 'bruno.carvalho@email.com', 'ant' => 'prata', 'nova' => 'ouro', 'res' => 'subiu', 'pos' => 3, 'xp' => 520],
    ['email' => 'felipe.oliveira@email.com', 'ant' => 'bronze', 'nova' => 'prata', 'res' => 'subiu', 'pos' => 2, 'xp' => 310],
    ['email' => 'pedro.rocha@email.com', 'ant' => 'prata', 'nova' => 'bronze', 'res' => 'desceu', 'pos' => 28, 'xp' => 40],
];

foreach ($historico_exemplos as $h) {
    if (isset($user_ids_criados[$h['email']])) {
        $uid = $user_ids_criados[$h['email']];
        $conn->query("DELETE FROM ligas_historico WHERE usuario_id = $uid AND semana_ref = '$semana_passada'");
        $stmt_h = $conn->prepare("
            INSERT INTO ligas_historico (usuario_id, divisao_anterior, divisao_nova, resultado, posicao_final, xp_final, semana_ref)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt_h->bind_param("isssiis", $uid, $h['ant'], $h['nova'], $h['res'], $h['pos'], $h['xp'], $semana_passada);
        $stmt_h->execute();
    }
}

// 5. Conquistas / Troféus para os usuários de teste
echo "Atribuindo conquistas e troféus...\n";
$trofeus_distribuir = [
    'primeiro_passo', 'perfeicao', 'fogo_3', 'sequencia_7', 'capitulo_1'
];
foreach ($user_ids_criados as $email => $uid) {
    foreach ($trofeus_distribuir as $slug) {
        $conn->query("INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES ($uid, '$slug')");
        $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($uid, '$slug')");
    }
}

// 6. Grupos de Batalha e Seguidores
echo "Configurando grupos de batalha e rede social...\n";
if (isset($user_ids_criados['admin@opus.com'])) {
    $admin_id = $user_ids_criados['admin@opus.com'];
    $conn->query("INSERT INTO grupos_batalha (nome, codigo_convite, criador_id) VALUES ('Devs Mestre Java', 'OPUSJAVA2026', $admin_id) ON DUPLICATE KEY UPDATE nome = VALUES(nome)");
    $grupo_id = (int) ($conn->query("SELECT id FROM grupos_batalha WHERE codigo_convite = 'OPUSJAVA2026'")->fetch_assoc()['id'] ?? 0);

    if ($grupo_id > 0) {
        foreach ($user_ids_criados as $uid) {
            $conn->query("INSERT IGNORE INTO grupo_membros (grupo_id, usuario_id) VALUES ($grupo_id, $uid)");
        }
    }
}

// Cria seguidores mútuos entre os primeiros usuários
$ids_arr = array_values($user_ids_criados);
for ($i = 0; $i < count($ids_arr); $i++) {
    for ($j = $i + 1; $j < min(count($ids_arr), $i + 4); $j++) {
        $u1 = $ids_arr[$i];
        $u2 = $ids_arr[$j];
        $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($u1, $u2)");
        $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($u2, $u1)");
    }
}

echo "=== SEED FINALIZADO COM SUCESSO! ===\n";
echo "Total de usuários processados: " . count($usuarios_dados) . "\n";
?>
