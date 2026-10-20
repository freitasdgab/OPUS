<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

require_once __DIR__ . '/../config/conexao.php';

$user_id = (int)$_SESSION['user_id'];
$custo_xp = 10;

// Busca XP atual do usuário
$stmt = $conn->prepare("SELECT xp FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res) {
    echo json_encode(['sucesso' => false, 'erro' => 'Usuário não encontrado.']);
    exit();
}

$xp_atual = (int)$res['xp'];
if ($xp_atual < $custo_xp) {
    echo json_encode([
        'sucesso' => false,
        'erro' => "Você não possui XP suficiente para ver a dica. (Necessário: {$custo_xp} XP, você tem: {$xp_atual} XP)"
    ]);
    exit();
}

// Deduz 10 de XP do usuário na tabela principal
$stmt_deduz = $conn->prepare("UPDATE usuarios SET xp = GREATEST(0, xp - ?) WHERE id = ?");
$stmt_deduz->bind_param("ii", $custo_xp, $user_id);
$stmt_deduz->execute();
$stmt_deduz->close();

// Se o usuário participa de ligas, tenta deduzir também do progresso semanal da liga de forma segura
try {
    $stmt_liga = $conn->prepare("UPDATE ligas_usuario SET xp_semana = GREATEST(0, xp_semana - ?) WHERE usuario_id = ?");
    if ($stmt_liga) {
        $stmt_liga->bind_param("ii", $custo_xp, $user_id);
        $stmt_liga->execute();
        $stmt_liga->close();
    }
} catch (Throwable $e) {
    // Tabela ligas_usuario pode não existir ou não estar configurada; ignora silenciosamente para não interromper a dica
}

$novo_xp = max(0, $xp_atual - $custo_xp);

echo json_encode([
    'sucesso' => true,
    'xp_gasto' => $custo_xp,
    'novo_xp' => $novo_xp,
    'mensagem' => "Dica desbloqueada com sucesso! (-{$custo_xp} XP)"
]);
