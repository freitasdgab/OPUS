<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json; charset=utf-8');

$email = trim(strtolower($_POST['email'] ?? ''));
$codigo = trim($_POST['codigo'] ?? '');
$nova_senha = $_POST['nova_senha'] ?? '';

if (empty($email) || empty($codigo) || empty($nova_senha)) {
    echo json_encode(['status' => 'error', 'message' => 'Preencha todos os campos.']);
    exit();
}

// 1. Verifica se o código é válido, não foi usado e ainda não expirou
$stmt = $conn->prepare("SELECT id FROM recuperacao_senha WHERE LOWER(email) = ? AND codigo = ? AND usado = 0 AND expira_em >= NOW() ORDER BY id DESC LIMIT 1");
$stmt->bind_param("ss", $email, $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Código inválido ou expirado. Tente novamente.']);
    exit();
}

$registro = $result->fetch_assoc();
$id_recuperacao = $registro['id'];
$stmt->close();

// 2. Atualiza a senha do usuário
$stmt_update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE LOWER(email) = ?");
$stmt_update->bind_param("ss", $nova_senha, $email);
$stmt_update->execute();
$stmt_update->close();

// 3. Marca o código como utilizado
$stmt_usado = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?");
$stmt_usado->bind_param("i", $id_recuperacao);
$stmt_usado->execute();
$stmt_usado->close();

echo json_encode(['status' => 'success', 'message' => 'Senha alterada com sucesso!']);