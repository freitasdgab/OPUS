<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexao.php';
require_once 'jogador_status.php';

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

// Toda a lógica (checar resgate duplicado, checar requisito de progresso,
// aplicar recompensa de vidas/XP, registrar liga/missão e gravar o
// resgate) mora em sp_resgatar_bau (back/sql/opus_procedures.sql).
$stmt = $conn->prepare("CALL sp_resgatar_bau(?, ?)");
$stmt->bind_param("ii", $user_id, $unidade_numero);
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->next_result();

if (!$resultado || $resultado['status'] === 'ja_resgatado') {
    echo json_encode([
        'success' => false,
        'ja_resgatado' => true,
        'mensagem' => 'Você já resgatou o baú desta unidade!'
    ]);
    exit();
}

if ($resultado['status'] === 'requisito_pendente') {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Você precisa concluir pelo menos 3 lições desta unidade para abrir o baú!'
    ]);
    exit();
}

$status_final = opus_sincronizar_jogador($conn, $user_id);

echo json_encode([
    'success'            => true,
    'unidade_numero'     => $unidade_numero,
    'xp_ganho'           => (int) $resultado['xp_ganho'],
    'vidas_ganhas'       => (int) $resultado['vidas_ganhas'],
    'vidas_atual'        => (int) $status_final['vidas'],
    'xp_total'           => (int) $status_final['xp'],
    'proxima_vida_texto' => $status_final['proxima_vida_texto'] ?? '',
    'detalhe'            => $resultado['detalhe'],
    'mensagem'           => "Parabéns! Você abriu o Baú de Recompensa da Unidade $unidade_numero!"
]);
