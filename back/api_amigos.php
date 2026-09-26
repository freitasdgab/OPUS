<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';
require_once 'jogador_status.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'mensagem' => 'Não autenticado']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// ── Garantir que as tabelas sociais e de batalha existam ───────
$conn->query("CREATE TABLE IF NOT EXISTS `seguidores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seguidor_id` INT NOT NULL,
    `seguido_id` INT NOT NULL,
    `data_criacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_seguidor_seguido` (`seguidor_id`, `seguido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS `acertos_diarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `usuario_id` INT NOT NULL,
    `data_dia` DATE NOT NULL,
    `acertos` INT DEFAULT 0,
    UNIQUE KEY `uq_user_dia` (`usuario_id`, `data_dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS `grupos_batalha` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `codigo_convite` VARCHAR(20) NOT NULL UNIQUE,
    `criador_id` INT NOT NULL,
    `data_criacao` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS `grupo_membros` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `grupo_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `data_entrada` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_grupo_user` (`grupo_id`, `usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── 1. ESTATÍSTICAS SOCIAIS (SEGUIDORES / SEGUINDO) ────────────
if ($action === 'estatisticas_sociais') {
    $target_id = (int) ($_GET['target_id'] ?? $user_id);

    $res_seg = $conn->query("SELECT COUNT(*) as qtd FROM seguidores WHERE seguido_id = $target_id");
    $seguidores_count = (int) ($res_seg->fetch_assoc()['qtd'] ?? 0);

    $res_indo = $conn->query("SELECT COUNT(*) as qtd FROM seguidores WHERE seguidor_id = $target_id");
    $seguindo_count = (int) ($res_indo->fetch_assoc()['qtd'] ?? 0);

    echo json_encode([
        'success' => true,
        'target_id' => $target_id,
        'seguidores' => $seguidores_count,
        'seguindo' => $seguindo_count
    ]);
    exit();
}

// ── 2. BUSCAR USUÁRIOS PARA SEGUIR ─────────────────────────────
if ($action === 'buscar_usuarios') {
    $q = trim($_GET['q'] ?? '');
    $q_esc = $conn->real_escape_string($q);

    $sql = "SELECT id, nome, email, xp, dias_fogo, foto_perfil FROM usuarios 
            WHERE (nome LIKE '%$q_esc%' OR email LIKE '%$q_esc%') AND id != $user_id 
            LIMIT 20";
    
    $res = $conn->query($sql);
    $usuarios = [];

    while ($r = $res->fetch_assoc()) {
        $uid = (int) $r['id'];
        $check = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $user_id AND seguido_id = $uid");
        $is_seguindo = ($check && $check->num_rows > 0);

        $foto = !empty($r['foto_perfil']) ? $r['foto_perfil'] : '../assets/img/opi pulando feliz.png';

        $usuarios[] = [
            'id' => $uid,
            'nome' => $r['nome'],
            'username' => strtolower(str_replace(' ', '', $r['nome'])) . $uid,
            'xp' => (int) $r['xp'],
            'dias_fogo' => (int) $r['dias_fogo'],
            'foto_perfil' => $foto,
            'is_seguindo' => $is_seguindo
        ];
    }

    echo json_encode(['success' => true, 'usuarios' => $usuarios]);
    exit();
}

// ── 3. SEGUIR / DEIXAR DE SEGUIR ──────────────────────────────
if ($action === 'toggle_seguir') {
    $target_id = (int) ($_POST['target_id'] ?? 0);
    if ($target_id <= 0 || $target_id === $user_id) {
        echo json_encode(['success' => false, 'mensagem' => 'Usuário inválido']);
        exit();
    }

    $check = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $user_id AND seguido_id = $target_id");
    if ($check && $check->num_rows > 0) {
        $conn->query("DELETE FROM seguidores WHERE seguidor_id = $user_id AND seguido_id = $target_id");
        $novo_estado = false;
        $msg = 'Você deixou de seguir este usuário.';
    } else {
        $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($user_id, $target_id)");
        $novo_estado = true;
        $msg = 'Agora você está seguindo este amigo!';
    }

    // Recalcula contagens
    $res_seg = $conn->query("SELECT COUNT(*) as qtd FROM seguidores WHERE seguido_id = $target_id");
    $seguidores_count = (int) ($res_seg->fetch_assoc()['qtd'] ?? 0);

    echo json_encode([
        'success' => true,
        'is_seguindo' => $novo_estado,
        'mensagem' => $msg,
        'seguidores_count' => $seguidores_count
    ]);
    exit();
}

// ── 4. LISTAR AMIGOS (SEGUIDORES E SEGUINDO) ──────────────────
if ($action === 'listar_amigos') {
    $tipo = $_GET['tipo'] ?? 'seguindo'; // 'seguindo' ou 'seguidores'

    if ($tipo === 'seguidores') {
        $sql = "SELECT u.id, u.nome, u.xp, u.dias_fogo, u.foto_perfil 
                FROM seguidores s 
                JOIN usuarios u ON s.seguidor_id = u.id 
                WHERE s.seguido_id = $user_id";
    } else {
        $sql = "SELECT u.id, u.nome, u.xp, u.dias_fogo, u.foto_perfil 
                FROM seguidores s 
                JOIN usuarios u ON s.seguido_id = u.id 
                WHERE s.seguidor_id = $user_id";
    }

    $res = $conn->query($sql);
    $amigos = [];

    while ($r = $res->fetch_assoc()) {
        $uid = (int) $r['id'];
        $check = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $user_id AND seguido_id = $uid");
        $is_seguindo = ($check && $check->num_rows > 0);

        $foto = !empty($r['foto_perfil']) ? $r['foto_perfil'] : '../assets/img/opi pulando feliz.png';

        $amigos[] = [
            'id' => $uid,
            'nome' => $r['nome'],
            'username' => strtolower(str_replace(' ', '', $r['nome'])) . $uid,
            'xp' => (int) $r['xp'],
            'dias_fogo' => (int) $r['dias_fogo'],
            'foto_perfil' => $foto,
            'is_seguindo' => $is_seguindo
        ];
    }

    echo json_encode(['success' => true, 'amigos' => $amigos]);
    exit();
}

// ── 5. CRIAR GRUPO DE BATALHA ──────────────────────────────────
if ($action === 'criar_grupo') {
    $nome_grupo = trim($_POST['nome_grupo'] ?? '');
    if (empty($nome_grupo)) {
        echo json_encode(['success' => false, 'mensagem' => 'Digite um nome para o grupo de batalha!']);
        exit();
    }

    $nome_esc = $conn->real_escape_string($nome_grupo);
    $codigo = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    $stmt = $conn->prepare("INSERT INTO grupos_batalha (nome, codigo_convite, criador_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $nome_esc, $codigo, $user_id);

    if ($stmt->execute()) {
        $grupo_id = $stmt->insert_id;
        $conn->query("INSERT IGNORE INTO grupo_membros (grupo_id, usuario_id) VALUES ($grupo_id, $user_id)");

        // Adiciona membros selecionados no modal de criação
        $membros_ids = $_POST['membros_ids'] ?? [];
        if (!empty($membros_ids) && is_array($membros_ids)) {
            foreach ($membros_ids as $mid) {
                $mid = (int) $mid;
                if ($mid > 0 && $mid !== $user_id) {
                    $conn->query("INSERT IGNORE INTO grupo_membros (grupo_id, usuario_id) VALUES ($grupo_id, $mid)");
                }
            }
        }

        echo json_encode([
            'success' => true,
            'mensagem' => 'Grupo de Batalha criado com sucesso!',
            'grupo_id' => $grupo_id,
            'codigo_convite' => $codigo
        ]);
    } else {
        echo json_encode(['success' => false, 'mensagem' => 'Erro ao criar grupo.']);
    }
    exit();
}

// ── 6. ENTRAR EM GRUPO VIA CÓDIGO DE CONVITE ───────────────────
if ($action === 'entrar_grupo') {
    $codigo = strtoupper(trim($_POST['codigo_convite'] ?? ''));
    if (empty($codigo)) {
        echo json_encode(['success' => false, 'mensagem' => 'Código de convite inválido!']);
        exit();
    }

    $cod_esc = $conn->real_escape_string($codigo);
    $res = $conn->query("SELECT id, nome FROM grupos_batalha WHERE codigo_convite = '$cod_esc'");

    if ($grupo = $res->fetch_assoc()) {
        $grupo_id = (int) $grupo['id'];
        $conn->query("INSERT IGNORE INTO grupo_membros (grupo_id, usuario_id) VALUES ($grupo_id, $user_id)");

        echo json_encode([
            'success' => true,
            'mensagem' => 'Você entrou no grupo ' . $grupo['nome'] . '!',
            'grupo_id' => $grupo_id
        ]);
    } else {
        echo json_encode(['success' => false, 'mensagem' => 'Código de grupo não encontrado!']);
    }
    exit();
}

// ── 7. DADOS DA TELA DE BATALHA (FULANO VS CICLANO) ────────────
if ($action === 'obter_batalha') {
    $rival_id = (int) ($_GET['rival_id'] ?? 0);
    $data_hoje = date('Y-m-d');

    // Se nenhum rival foi passado, pega o amigo que você segue que tem XP mais próximo
    if ($rival_id <= 0) {
        $res_rival = $conn->query("SELECT u.id FROM seguidores s 
                                   JOIN usuarios u ON s.seguido_id = u.id 
                                   WHERE s.seguidor_id = $user_id AND u.id != $user_id 
                                   ORDER BY ABS(u.xp - (SELECT xp FROM usuarios WHERE id = $user_id)) ASC LIMIT 1");
        if ($res_rival && $r = $res_rival->fetch_assoc()) {
            $rival_id = (int) $r['id'];
        } else {
            // Fallback: pega qualquer outro usuário do sistema
            $res_alt = $conn->query("SELECT id FROM usuarios WHERE id != $user_id ORDER BY xp DESC LIMIT 1");
            if ($res_alt && $ra = $res_alt->fetch_assoc()) {
                $rival_id = (int) $ra['id'];
            }
        }
    }

    // Dados do Jogador 1 (Você)
    $p1 = opus_sincronizar_jogador($conn, $user_id);
    $res_p1_acertos = $conn->query("SELECT acertos FROM acertos_diarios WHERE usuario_id = $user_id AND data_dia = '$data_hoje'");
    $p1_acertos_hoje = (int) ($res_p1_acertos->fetch_assoc()['acertos'] ?? 0);

    // Dados do Jogador 2 (Rival)
    $p2 = [
        'nome' => 'Rival Opus',
        'xp' => 0,
        'dias_fogo' => 0,
        'foto_perfil' => '../assets/img/opi pulando feliz.png'
    ];
    $p2_acertos_hoje = 0;

    if ($rival_id > 0) {
        $p2 = opus_sincronizar_jogador($conn, $rival_id);
        $res_p2_acertos = $conn->query("SELECT acertos FROM acertos_diarios WHERE usuario_id = $rival_id AND data_dia = '$data_hoje'");
        $p2_acertos_hoje = (int) ($res_p2_acertos->fetch_assoc()['acertos'] ?? 0);
    }

    $p1_foto = !empty($p1['foto_perfil']) ? $p1['foto_perfil'] : '../assets/img/opi pulando feliz.png';
    $p2_foto = !empty($p2['foto_perfil']) ? $p2['foto_perfil'] : '../assets/img/opi pulando feliz.png';

    // Cálculo da diferença de XP
    $diferenca_xp = $p1['xp'] - $p2['xp'];
    $lider = $diferenca_xp >= 0 ? $p1['nome'] : $p2['nome'];

    // Cálculo da Barra de Poder do Duelo
    $total_xp_duelo = max(1, $p1['xp'] + $p2['xp']);
    $pct_p1 = round(($p1['xp'] / $total_xp_duelo) * 100);
    $pct_p2 = 100 - $pct_p1;

    echo json_encode([
        'success' => true,
        'batalha' => [
            'player1' => [
                'id' => $user_id,
                'nome' => $p1['nome'],
                'xp' => $p1['xp'],
                'dias_fogo' => $p1['dias_fogo'],
                'acertos_hoje' => $p1_acertos_hoje,
                'foto_perfil' => $p1_foto
            ],
            'player2' => [
                'id' => $rival_id,
                'nome' => $p2['nome'],
                'xp' => $p2['xp'],
                'dias_fogo' => $p2['dias_fogo'],
                'acertos_hoje' => $p2_acertos_hoje,
                'foto_perfil' => $p2_foto
            ],
            'diferenca_xp' => $diferenca_xp,
            'diferenca_xp_abs' => abs($diferenca_xp),
            'lider' => $lider,
            'pct_p1' => $pct_p1,
            'pct_p2' => $pct_p2
        ]
    ]);
    exit();
}

// ── 8. LISTAR GRUPOS DO USUÁRIO E GRUPOS SUGERIDOS ──────────────
if ($action === 'listar_grupos') {
    // 8a. Grupos onde o usuário é membro
    $sql_meus = "SELECT g.id, g.nome, g.codigo_convite, g.criador_id,
                 (SELECT COUNT(*) FROM grupo_membros WHERE grupo_id = g.id) as total_membros
                 FROM grupos_batalha g
                 JOIN grupo_membros gm ON g.id = gm.grupo_id
                 WHERE gm.usuario_id = $user_id ORDER BY g.id DESC";

    $res_meus = $conn->query($sql_meus);
    $meus_grupos = [];

    while ($r = $res_meus->fetch_assoc()) {
        $gid = (int) $r['id'];

        // Busca avatares dos membros para preview no quadradinho
        $res_membros_av = $conn->query("SELECT u.foto_perfil FROM grupo_membros gm 
                                         JOIN usuarios u ON gm.usuario_id = u.id 
                                         WHERE gm.grupo_id = $gid LIMIT 4");
        $avatares = [];
        while ($av = $res_membros_av->fetch_assoc()) {
            $avatares[] = !empty($av['foto_perfil']) ? $av['foto_perfil'] : '../assets/img/opi pulando feliz.png';
        }

        $meus_grupos[] = [
            'id' => $gid,
            'nome' => $r['nome'],
            'codigo_convite' => $r['codigo_convite'],
            'total_membros' => (int) $r['total_membros'],
            'avatares' => $avatares,
            'is_criador' => ((int)$r['criador_id'] === $user_id),
            'is_membro' => true
        ];
    }

    // 8b. Grupos sugeridos/públicos que o usuário ainda não é membro
    $sql_sug = "SELECT g.id, g.nome, g.codigo_convite, g.criador_id,
                (SELECT COUNT(*) FROM grupo_membros WHERE grupo_id = g.id) as total_membros
                FROM grupos_batalha g
                WHERE g.id NOT IN (SELECT grupo_id FROM grupo_membros WHERE usuario_id = $user_id) 
                ORDER BY total_membros DESC LIMIT 6";

    $res_sug = $conn->query($sql_sug);
    $grupos_sugeridos = [];

    while ($r = $res_sug->fetch_assoc()) {
        $gid = (int) $r['id'];
        $res_membros_av = $conn->query("SELECT u.foto_perfil FROM grupo_membros gm 
                                         JOIN usuarios u ON gm.usuario_id = u.id 
                                         WHERE gm.grupo_id = $gid LIMIT 4");
        $avatares = [];
        while ($av = $res_membros_av->fetch_assoc()) {
            $avatares[] = !empty($av['foto_perfil']) ? $av['foto_perfil'] : '../assets/img/opi pulando feliz.png';
        }

        $grupos_sugeridos[] = [
            'id' => $gid,
            'nome' => $r['nome'],
            'codigo_convite' => $r['codigo_convite'],
            'total_membros' => (int) $r['total_membros'],
            'avatares' => $avatares,
            'is_criador' => false,
            'is_membro' => false
        ];
    }

    echo json_encode([
        'success' => true,
        'meus_grupos' => $meus_grupos,
        'grupos_sugeridos' => $grupos_sugeridos
    ]);
    exit();
}

// ── 9. DETALHES DE UM GRUPO DE BATALHA (LEADERBOARD E DUELOS) ───
if ($action === 'detalhes_grupo') {
    $grupo_id = (int) ($_GET['grupo_id'] ?? 0);
    $data_hoje = date('Y-m-d');

    if ($grupo_id <= 0) {
        echo json_encode(['success' => false, 'mensagem' => 'Grupo inválido']);
        exit();
    }

    $res_g = $conn->query("SELECT id, nome, codigo_convite, criador_id FROM grupos_batalha WHERE id = $grupo_id");
    $grupo = $res_g->fetch_assoc();

    if (!$grupo) {
        echo json_encode(['success' => false, 'mensagem' => 'Grupo não encontrado']);
        exit();
    }

    // Busca todos os membros com seus acertos de hoje, XP total e dias de fogo
    $sql_m = "SELECT u.id, u.nome, u.xp, u.dias_fogo, u.foto_perfil,
              COALESCE((SELECT acertos FROM acertos_diarios WHERE usuario_id = u.id AND data_dia = '$data_hoje'), 0) as acertos_hoje
              FROM grupo_membros gm
              JOIN usuarios u ON gm.usuario_id = u.id
              WHERE gm.grupo_id = $grupo_id
              ORDER BY acertos_hoje DESC, u.xp DESC";

    $res_m = $conn->query($sql_m);
    $membros = [];
    $pos = 1;

    while ($r = $res_m->fetch_assoc()) {
        $foto = !empty($r['foto_perfil']) ? $r['foto_perfil'] : '../assets/img/opi pulando feliz.png';
        $membros[] = [
            'posicao' => $pos++,
            'id' => (int) $r['id'],
            'nome' => $r['nome'],
            'xp' => (int) $r['xp'],
            'dias_fogo' => (int) $r['dias_fogo'],
            'acertos_hoje' => (int) $r['acertos_hoje'],
            'foto_perfil' => $foto,
            'is_voce' => ((int)$r['id'] === $user_id)
        ];
    }

    echo json_encode([
        'success' => true,
        'grupo' => [
            'id' => (int) $grupo['id'],
            'nome' => $grupo['nome'],
            'codigo_convite' => $grupo['codigo_convite'],
            'criador_id' => (int) $grupo['criador_id'],
            'membros' => $membros
        ]
    ]);
    exit();
}

echo json_encode(['success' => false, 'mensagem' => 'Ação inválida']);
?>
