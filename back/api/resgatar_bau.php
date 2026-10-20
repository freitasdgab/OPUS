<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/jogador_status.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensagem' => 'Usuário não autenticado.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$unidade_numero = (int) ($_POST['unidade_numero'] ?? 0);

if ($unidade_numero < 1 || $unidade_numero > 40) {
    echo json_encode(['success' => false, 'mensagem' => 'Unidade inválida.']);
    exit();
}

// Toda a lógica (checar resgate duplicado, checar requisito de progresso,
// aplicar recompensa de vidas/XP, registrar liga/missão e gravar o
// resgate) mora em sp_resgatar_bau. Usa opus_call() para capturar o result set.
$resultado = opus_call($conn, "CALL sp_resgatar_bau(?, ?)", "ii", [$user_id, $unidade_numero]);
$status_res = $resultado['status'] ?? (((int)($resultado['resgatado'] ?? 0) === 1) ? 'ok' : 'ja_resgatado');

if (!$resultado || $status_res === 'ja_resgatado') {
    echo json_encode([
        'success' => false,
        'ja_resgatado' => true,
        'mensagem' => 'Você já resgatou o baú desta unidade!'
    ]);
    exit();
}

if ($status_res === 'requisito_pendente') {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Você precisa concluir pelo menos 3 lições desta unidade para abrir o baú!'
    ]);
    exit();
}

$status_final = opus_sincronizar_jogador($conn, $user_id);
$xp_ganho = (int) ($resultado['xp_ganho'] ?? 100);
$vidas_ganhas = (int) ($resultado['vidas_ganhas'] ?? 0);
$detalhe_padrao = $vidas_ganhas > 0
    ? "Você ganhou +{$xp_ganho} XP e recuperou +{$vidas_ganhas} coração de vida!"
    : "Suas vidas já estão cheias! Você recebeu +{$xp_ganho} XP de bônus!";

echo json_encode([
    'success'            => true,
    'unidade_numero'     => $unidade_numero,
    'xp_ganho'           => $xp_ganho,
    'vidas_ganhas'       => $vidas_ganhas,
    'vidas_atual'        => (int) $status_final['vidas'],
    'xp_total'           => (int) $status_final['xp'],
    'proxima_vida_texto' => $status_final['proxima_vida_texto'] ?? '',
    'detalhe'            => !empty($resultado['detalhe']) ? $resultado['detalhe'] : $detalhe_padrao,
    'mensagem'           => "Parabéns! Você abriu o Baú de Recompensa da Unidade $unidade_numero!"
]);
