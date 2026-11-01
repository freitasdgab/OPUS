<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/jogador_status.php';

// Catálogo de Prêmios da Roleta da Sorte do Baú
$premios_roleta = [
    'xp25' => [
        'id'          => 'xp25',
        'slice_index' => 0,
        'tipo'        => 'xp',
        'valor'       => 25,
        'label'       => '+25 XP',
        'sub'         => 'Prêmio Máximo!',
        'icone'       => 'fa-bolt',
        'cor'         => '#ffc800',
        'cor_fundo'   => '#d97706',
        'destaque'    => true,
        'peso'        => 10, // 10% de chance
        'detalhe'     => 'Incrível! Você tirou o prêmio máximo da roleta: +25 XP!'
    ],
    'xp5' => [
        'id'          => 'xp5',
        'slice_index' => 1,
        'tipo'        => 'xp',
        'valor'       => 5,
        'label'       => '+5 XP',
        'sub'         => 'XP Bônus',
        'icone'       => 'fa-bolt',
        'cor'         => '#1cb0f6',
        'cor_fundo'   => '#0284c7',
        'destaque'    => false,
        'peso'        => 25, // 25% de chance
        'detalhe'     => 'Muito bom! Você ganhou +5 XP na roleta do baú!'
    ],
    'coracao' => [
        'id'          => 'coracao',
        'slice_index' => 2,
        'tipo'        => 'vida',
        'valor'       => 1,
        'label'       => '+1 Coração',
        'sub'         => 'Recupera Vida',
        'icone'       => 'fa-heart',
        'cor'         => '#ff4b4b',
        'cor_fundo'   => '#dc2626',
        'destaque'    => false,
        'peso'        => 15, // 15% de chance
        'detalhe'     => 'Sensacional! Você recuperou +1 Coração de Vida!'
    ],
    'xp10' => [
        'id'          => 'xp10',
        'slice_index' => 3,
        'tipo'        => 'xp',
        'valor'       => 10,
        'label'       => '+10 XP',
        'sub'         => 'Belo Prêmio!',
        'icone'       => 'fa-bolt',
        'cor'         => '#ff9600',
        'cor_fundo'   => '#ea580c',
        'destaque'    => false,
        'peso'        => 20, // 20% de chance
        'detalhe'     => 'Boa! Você ganhou +10 XP na roleta do baú!'
    ],
    'xp3' => [
        'id'          => 'xp3',
        'slice_index' => 4,
        'tipo'        => 'xp',
        'valor'       => 3,
        'label'       => '+3 XP',
        'sub'         => 'XP Bônus',
        'icone'       => 'fa-bolt',
        'cor'         => '#58cc02',
        'cor_fundo'   => '#16a34a',
        'destaque'    => false,
        'peso'        => 15, // 15% de chance
        'detalhe'     => 'Você garantiu +3 XP na roleta do baú!'
    ],
    'xp20' => [
        'id'          => 'xp20',
        'slice_index' => 5,
        'tipo'        => 'xp',
        'valor'       => 20,
        'label'       => '+20 XP',
        'sub'         => 'Super Bônus!',
        'icone'       => 'fa-bolt',
        'cor'         => '#a855f7',
        'cor_fundo'   => '#7c3aed',
        'destaque'    => false,
        'peso'        => 15, // 15% de chance
        'detalhe'     => 'Espetacular! Você ganhou +20 XP na roleta do baú!'
    ]
];

// Endpoint para consultar os prêmios da roleta
if (isset($_GET['listar_premios'])) {
    echo json_encode([
        'success' => true,
        'premios' => array_values($premios_roleta)
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

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

// Verifica antecipadamente se o baú já foi resgatado
$stmt_check = $conn->prepare("SELECT id FROM bau_recompensas WHERE usuario_id = ? AND unidade_numero = ? LIMIT 1");
$stmt_check->bind_param("ii", $user_id, $unidade_numero);
$stmt_check->execute();
$ja_resgatou = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if ($ja_resgatou) {
    echo json_encode([
        'success' => false,
        'ja_resgatado' => true,
        'mensagem' => 'Você já resgatou o baú desta unidade!'
    ]);
    exit();
}

// Verifica requisito de progresso (pelo menos 3 lições ou capítulo completo)
$stmt_prog = $conn->prepare("SELECT licoes_concluidas, status FROM progresso_usuario WHERE usuario_id = ? AND unidade_numero = ? LIMIT 1");
$stmt_prog->bind_param("ii", $user_id, $unidade_numero);
$stmt_prog->execute();
$prog = $stmt_prog->get_result()->fetch_assoc();
$stmt_prog->close();

$concluidas = (int) ($prog['licoes_concluidas'] ?? 0);
$status_cap = (string) ($prog['status'] ?? '');

if ($concluidas < 3 && $status_cap !== 'completo') {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Você precisa concluir pelo menos 3 lições desta unidade para abrir o baú!'
    ]);
    exit();
}

// Sorteio seguro ponderado na roleta
$sorteio = random_int(1, 100);
$acumulado = 0;
$premio_escolhido = null;
foreach ($premios_roleta as $p) {
    $acumulado += $p['peso'];
    if ($sorteio <= $acumulado) {
        $premio_escolhido = $p;
        break;
    }
}
if (!$premio_escolhido) {
    $premio_escolhido = $premios_roleta['xp10'];
}

// Sincroniza dados atuais do jogador para aplicar o prêmio
$status_inicial = opus_sincronizar_jogador($conn, $user_id);
$vidas_atuais = (int) ($status_inicial['vidas'] ?? 3);

$xp_ganho = 0;
$vidas_ganhas = 0;
$detalhe = $premio_escolhido['detalhe'];

if ($premio_escolhido['tipo'] === 'vida') {
    if ($vidas_atuais < 3) {
        $vidas_ganhas = 1;
        $xp_ganho = 0;
        $detalhe = 'Sensacional! Você tirou +1 Coração e recuperou sua vida!';
    } else {
        // Se as vidas já estão cheias (3/3), converte para bônus de XP
        $vidas_ganhas = 0;
        $xp_ganho = 10;
        $detalhe = 'Você tirou +1 Coração! Como suas vidas já estão cheias (3/3), você recebeu +10 XP bônus!';
    }
} else {
    $xp_ganho = (int) $premio_escolhido['valor'];
    $vidas_ganhas = 0;
}

// Executa a procedure no banco
$resultado = opus_call(
    $conn,
    "CALL sp_resgatar_bau(?, ?, ?, ?, ?, ?)",
    "iisiis",
    [$user_id, $unidade_numero, $premio_escolhido['id'], $xp_ganho, $vidas_ganhas, $detalhe]
);

$status_res = $resultado['status'] ?? (((int)($resultado['resgatado'] ?? 0) === 1) ? 'ok' : 'erro');

if ($status_res === 'ja_resgatado') {
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

if ($status_res !== 'ok') {
    echo json_encode([
        'success' => false,
        'mensagem' => 'Não foi possível registrar o resgate da roleta.'
    ]);
    exit();
}

// Obtém status final atualizado
$status_final = opus_sincronizar_jogador($conn, $user_id);

echo json_encode([
    'success'            => true,
    'unidade_numero'     => $unidade_numero,
    'premio'             => [
        'id'          => $premio_escolhido['id'],
        'slice_index' => (int) $premio_escolhido['slice_index'],
        'tipo'        => $premio_escolhido['tipo'],
        'valor'       => (int) $premio_escolhido['valor'],
        'label'       => $premio_escolhido['label'],
        'sub'         => $premio_escolhido['sub'],
        'icone'       => $premio_escolhido['icone'],
        'cor'         => $premio_escolhido['cor'],
        'cor_fundo'   => $premio_escolhido['cor_fundo'],
        'destaque'    => !empty($premio_escolhido['destaque']),
    ],
    'xp_ganho'           => (int) ($resultado['xp_ganho'] ?? $xp_ganho),
    'vidas_ganhas'       => (int) ($resultado['vidas_ganhas'] ?? $vidas_ganhas),
    'vidas_atual'        => (int) $status_final['vidas'],
    'xp_total'           => (int) $status_final['xp'],
    'proxima_vida_texto' => $status_final['proxima_vida_texto'] ?? '',
    'detalhe'            => !empty($resultado['detalhe']) ? $resultado['detalhe'] : $detalhe,
    'mensagem'           => "Parabéns! Você ganhou {$premio_escolhido['label']} na Roleta da Sorte!"
], JSON_UNESCAPED_UNICODE);
