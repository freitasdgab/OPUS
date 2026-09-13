<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';
require_once 'jogador_status.php';
require_once 'ligas_logic.php';
require_once 'missoes_logic.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensagem' => 'Usuário não autenticado.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$unidade_numero = (int) ($_POST['unidade_numero'] ?? 0);

if ($unidade_numero < 1 || $unidade_numero > 5) {
    echo json_encode(['success' => false, 'mensagem' => 'Unidade inválida.']);
    exit();
}

// 1. Garante que a tabela de baús existe
$conn->query("CREATE TABLE IF NOT EXISTS `bau_recompensas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NOT NULL,
    `unidade_numero` INT(11) NOT NULL,
    `tipo_recompensa` VARCHAR(50) NOT NULL DEFAULT 'misto',
    `xp_ganho` INT(11) NOT NULL DEFAULT 50,
    `vidas_ganhas` INT(11) NOT NULL DEFAULT 0,
    `resgatado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_unidade` (`usuario_id`, `unidade_numero`),
    KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

// 2. Verifica se o usuário já resgatou este baú
$stmt_check = $conn->prepare("SELECT id, resgatado_em, xp_ganho, vidas_ganhas FROM bau_recompensas WHERE usuario_id = ? AND unidade_numero = ?");
$stmt_check->bind_param("ii", $user_id, $unidade_numero);
$stmt_check->execute();
$ja_resgatado = $stmt_check->get_result()->fetch_assoc();

if ($ja_resgatado) {
    echo json_encode([
        'success' => false,
        'ja_resgatado' => true,
        'mensagem' => 'Você já resgatou o baú desta unidade!'
    ]);
    exit();
}

// 3. Verifica se o usuário atingiu o critério (pelo menos 3 lições feitas na unidade ou unidade completa)
$stmt_prog = $conn->prepare("SELECT licoes_concluidas, status FROM progresso_usuario WHERE usuario_id = ? AND unidade_numero = ?");
$stmt_prog->bind_param("ii", $user_id, $unidade_numero);
$stmt_prog->execute();
$progresso = $stmt_prog->get_result()->fetch_assoc();

$licoes_feitas = (int) ($progresso['licoes_concluidas'] ?? 0);
$status_unidade = $progresso['status'] ?? 'trancado';

if ($licoes_feitas < 3 && $status_unidade !== 'completo') {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Você precisa concluir pelo menos 3 lições desta unidade para abrir o baú!'
    ]);
    exit();
}

// 4. Sincroniza e obtém estado do jogador
$status_jogador = opus_sincronizar_jogador($conn, $user_id);
$vidas_atual = (int) $status_jogador['vidas'];

$xp_base = 50;
$vidas_ganhas = 0;
$xp_extra = 0;
$detalhe_recompensa = "";

if ($vidas_atual < 3) {
    // Ganha +1 coração bônus
    $vidas_ganhas = 1;
    $vidas_atual++;
    $detalhe_recompensa = "+1 Coração Bônus recuperado!";

    if ($vidas_atual >= 3) {
        $up = $conn->prepare("UPDATE usuarios SET vidas = 3, vidas_proxima_em = NULL, xp = xp + ? WHERE id = ?");
        $up->bind_param("ii", $xp_base, $user_id);
    } else {
        $up = $conn->prepare("UPDATE usuarios SET vidas = ?, xp = xp + ? WHERE id = ?");
        $up->bind_param("iii", $vidas_atual, $xp_base, $user_id);
    }
    $up->execute();
} else {
    // Vidas cheias: recebe bônus de XP reforçado (+50 XP adicional, total +100 XP)
    $xp_extra = 50;
    $xp_base = 100;
    $detalhe_recompensa = "Vidas cheias! +50 XP bônus adicional concedido!";

    $up = $conn->prepare("UPDATE usuarios SET xp = xp + ? WHERE id = ?");
    $up->bind_param("ii", $xp_base, $user_id);
    $up->execute();
}

// 5. Registra XP na liga semanal e missões
liga_registrar_xp($conn, $user_id, $xp_base);
missoes_registrar_progresso($conn, $user_id, $xp_base, 0);

// 6. Grava o resgate no banco para evitar resgates múltiplos
$stmt_ins = $conn->prepare("INSERT INTO bau_recompensas (usuario_id, unidade_numero, tipo_recompensa, xp_ganho, vidas_ganhas) VALUES (?, ?, 'recompensa_meio_trilha', ?, ?)");
$stmt_ins->bind_param("iiii", $user_id, $unidade_numero, $xp_base, $vidas_ganhas);
$stmt_ins->execute();

// 7. Busca status atualizado
$status_final = opus_sincronizar_jogador($conn, $user_id);

echo json_encode([
    'success'           => true,
    'unidade_numero'    => $unidade_numero,
    'xp_ganho'          => $xp_base,
    'vidas_ganhas'      => $vidas_ganhas,
    'vidas_atual'       => (int) $status_final['vidas'],
    'xp_total'          => (int) $status_final['xp'],
    'proxima_vida_texto'=> $status_final['proxima_vida_texto'] ?? '',
    'detalhe'           => $detalhe_recompensa,
    'mensagem'          => "Parabéns! Você abriu o Baú de Recompensa da Unidade $unidade_numero!"
]);
