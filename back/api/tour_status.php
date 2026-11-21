<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Não autenticado']);
    exit();
}

require_once __DIR__ . '/../config/conexao.php';

$user_id = (int) $_SESSION['user_id'];
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$tour = isset($data['tour']) ? trim((string) $data['tour']) : 'principal';
$visto = isset($data['visto']) ? ((int) $data['visto'] ? 1 : 0) : 1;

$mapa_colunas = [
    'principal'  => 'tour_visto',
    'perfil'     => 'tour_perfil_visto',
    'licao1'     => 'tour_licao1_visto',
    'resultado1' => 'tour_resultado1_visto',
    'fim_curso'  => 'tour_fim_curso_visto',
];
$coluna = $mapa_colunas[$tour] ?? 'tour_visto';

// Garante que as colunas existam de forma segura
try {
    foreach (array_values($mapa_colunas) as $col_nome) {
        $chk = $conn->query("SHOW COLUMNS FROM usuarios LIKE '{$col_nome}'");
        if ($chk && $chk->num_rows === 0) {
            $conn->query("ALTER TABLE usuarios ADD COLUMN {$col_nome} TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    $stmt = $conn->prepare("UPDATE usuarios SET {$coluna} = ? WHERE id = ?");
    $stmt->bind_param("ii", $visto, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode([
        'ok' => true,
        'tour' => $tour,
        'visto' => $visto
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    // Fallback amigável caso o banco não permita DDL
    echo json_encode([
        'ok' => false,
        'erro' => 'Não foi possível persistir no banco (salvo localmente).'
    ], JSON_UNESCAPED_UNICODE);
}
