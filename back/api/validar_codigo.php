<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

header('Content-Type: application/json; charset=utf-8');

$email  = trim(strtolower($_POST['email'] ?? ''));
$codigo = trim($_POST['codigo'] ?? '');

if (empty($email) || empty($codigo)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Por favor, digite o código de 6 dígitos recebido no e-mail.'
    ]);
    exit();
}

// 1. Verifica se o código existe, ainda é válido e não foi usado
$stmt = $conn->prepare("SELECT id FROM recuperacao_senha WHERE LOWER(email) = ? AND codigo = ? AND usado = 0 AND expira_em >= NOW() ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $email, $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Código de verificação incorreto ou expirado. Verifique os 6 dígitos recebidos no seu e-mail e tente novamente.'
    ]);
    exit();
}
$stmt->close();

echo json_encode([
    'status'  => 'success',
    'message' => 'Código aprovado com sucesso! Agora defina a sua nova senha.'
]);
exit();
