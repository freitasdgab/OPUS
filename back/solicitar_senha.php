<?php
session_start();
require_once 'conexao.php'; // conexao.php no mesmo diretório backend

header('Content-Type: application/json; charset=utf-8');

// Recebe o e-mail digitado pelo usuário na tela de recuperação
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$email = strtolower(trim($email));

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, informe o e-mail.']);
    exit();
}

// 1. Verifica se o e-mail realmente existe no banco
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhuma conta encontrada com este e-mail.']);
    exit();
}
$stmt->close();

// 2. Gera o código de 6 dígitos e a validade (15 minutos)
$codigo = sprintf("%06d", mt_rand(1, 999999));
$expira_em = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// 3. Salva o código na tabela 'recuperacao_senha'
$stmt_insert = $conn->prepare("INSERT INTO recuperacao_senha (email, codigo, expira_em) VALUES (?, ?, ?)");
$stmt_insert->bind_param("sss", $email, $codigo, $expira_em);
$stmt_insert->execute();
$stmt_insert->close();

/* * SIMULAÇÃO NO LOCALHOST (XAMPP):
 * Como estamos sem PHPMailer configurado no momento, retornamos o código na mensagem 
 * para você conseguir testar o fluxo no navegador.
 */
echo json_encode([
    'status' => 'success', 
    'message' => 'Código enviado para seu e-mail! (SIMULAÇÃO LOCALHOST: Seu código é ' . $codigo . ')'
]);