<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensagem' => 'Usuário não autenticado.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Desconto de XP por sair no meio das perguntas. Lógica em
// sp_penalizar_saida_licao (back/sql/opus_procedures.sql).
$resultado = opus_call($conn, "CALL sp_penalizar_saida_licao(?)", "i", [$user_id]);

echo json_encode([
    'success'    => true,
    'xp_total'   => (int) ($resultado['xp'] ?? 0),
    'xp_perdido' => (int) ($resultado['xp_perdido'] ?? 0),
]);
