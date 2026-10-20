<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';

header('Content-Type: application/json; charset=utf-8');

$email          = trim(strtolower($_POST['email'] ?? ''));
$codigo         = trim($_POST['codigo'] ?? '');
$nova_senha     = trim($_POST['nova_senha'] ?? '');
$confirme_senha = trim($_POST['confirme_senha'] ?? '');

if (empty($email) || empty($codigo) || empty($nova_senha)) {
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos obrigatórios.']);
    exit();
}

if (!empty($confirme_senha) && $nova_senha !== $confirme_senha) {
    echo json_encode(['status' => 'error', 'message' => 'A confirmação de senha não coincide com a nova senha digitada.']);
    exit();
}

// 1. Validação de campos
if (empty($nova_senha)) {
    echo json_encode(['status' => 'error', 'message' => 'Digite a nova senha.']);
    exit();
}

// 2. Valida se o código de verificação é válido e não expirou
$stmt = $conn->prepare("SELECT id FROM recuperacao_senha WHERE LOWER(email) = ? AND codigo = ? AND usado = 0 AND expira_em >= NOW() ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $email, $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'O código de verificação expirou ou é inválido. Por favor, solicite um novo código.'
    ]);
    exit();
}

$registro = $result->fetch_assoc();
$id_recuperacao = $registro['id'];
$stmt->close();

// 3. Atualiza a senha do usuário
$stmt_update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE LOWER(email) = ?");
$stmt_update->bind_param("ss", $nova_senha, $email);
$stmt_update->execute();
$stmt_update->close();

// 4. Marca o código como utilizado para não ser reutilizado
$stmt_usado = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?");
$stmt_usado->bind_param("i", $id_recuperacao);
$stmt_usado->execute();
$stmt_usado->close();

echo json_encode([
    'status'  => 'success',
    'message' => 'Senha alterada com sucesso! Você já pode fazer login com a sua nova senha.'
]);
exit();
