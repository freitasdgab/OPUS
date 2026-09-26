<?php
session_start();
header('Content-Type: application/json');
require_once 'conexao.php';
require_once 'ligas_logic.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Garante que a tabela seguidores existe (usada no cálculo de is_seguindo)
$conn->query("CREATE TABLE IF NOT EXISTS `seguidores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `seguidor_id` INT NOT NULL,
    `seguido_id` INT NOT NULL,
    `data_criacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_seguidor_seguido` (`seguidor_id`, `seguido_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

try {
    liga_garantir_usuario($conn, $user_id);

    $stmt = $conn->prepare("SELECT divisao, grupo_id, xp_semana FROM ligas_usuario WHERE usuario_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $minhaLiga = $stmt->get_result()->fetch_assoc();

    // Segurança extra: se por algum motivo ainda não tiver grupo, tenta de novo
    if (!$minhaLiga || empty($minhaLiga['grupo_id'])) {
        liga_garantir_usuario($conn, $user_id);
        $stmt = $conn->prepare("SELECT divisao, grupo_id, xp_semana FROM ligas_usuario WHERE usuario_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $minhaLiga = $stmt->get_result()->fetch_assoc();
    }

    if (!$minhaLiga || empty($minhaLiga['grupo_id'])) {
        throw new Exception("Não foi possível alocar o usuário em um grupo de liga.");
    }

    $stmt = $conn->prepare("
        SELECT lu.usuario_id, lu.xp_semana, u.nome, u.foto_perfil
        FROM ligas_usuario lu
        JOIN usuarios u ON u.id = lu.usuario_id
        WHERE lu.grupo_id = ?
        ORDER BY lu.xp_semana DESC, lu.usuario_id ASC
    ");
    $stmt->bind_param("i", $minhaLiga['grupo_id']);
    $stmt->execute();
    $membros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $n = count($membros);
    $zonas = liga_calcular_zonas($n);
    $cfg = liga_config($minhaLiga['divisao']);

    $lista = [];
    $minhaPosicao = 0;
    foreach ($membros as $idx => $m) {
        $posicao = $idx + 1;
        $zona = 'neutro';
        if ($posicao <= $zonas['sobe'] && $cfg['sobe']) $zona = 'sobe';
        elseif ($posicao > ($n - $zonas['desce']) && $cfg['desce']) $zona = 'desce';

        $mid = (int) $m['usuario_id'];
        if ($mid === $user_id) $minhaPosicao = $posicao;

        $check = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $user_id AND seguido_id = $mid");
        $is_seguindo = ($check && $check->num_rows > 0);

        $lista[] = [
            "id"          => $mid,
            "posicao"     => $posicao,
            "nome"        => $m['nome'],
            "xp"          => (int)$m['xp_semana'],
            "foto_perfil" => $m['foto_perfil'],
            "is_me"       => $mid === $user_id,
            "is_seguindo" => $is_seguindo,
            "zona"        => $zona,
        ];
    }

    $proximaVirada = liga_proxima_virada();
    $segundosRestantes = $proximaVirada->getTimestamp() - time();

    echo json_encode([
        "divisao"            => $minhaLiga['divisao'],
        "divisao_nome"       => $cfg['nome'],
        "divisao_cor"        => $cfg['cor'],
        "divisao_cor_clara"  => $cfg['corClara'],
        "pode_subir"         => $cfg['sobe'],
        "pode_descer"        => $cfg['desce'],
        "minha_posicao"      => $minhaPosicao,
        "meu_xp_semana"      => (int)$minhaLiga['xp_semana'],
        "tamanho_grupo"      => $n,
        "vagas_sobem"        => $zonas['sobe'],
        "vagas_descem"       => $zonas['desce'],
        "segundos_restantes" => max(0, $segundosRestantes),
        "membros"            => $lista,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "error"    => "erro_servidor",
        "mensagem" => $e->getMessage(),
    ]);
}
