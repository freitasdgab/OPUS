<?php
session_start();
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/email_service.php';

header('Content-Type: application/json; charset=utf-8');

// Recebe o e-mail digitado pelo usuário na tela
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?: ($_POST['email'] ?? '');
$email = strtolower(trim($email));

if (empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Por favor, informe o seu e-mail cadastrado.']);
    exit();
}

// 1. Verifica se o e-mail existe no banco
$stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE LOWER(email) = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Nenhuma conta foi encontrada com este e-mail no Opus.']);
    exit();
}

$user = $result->fetch_assoc();
$nomeUsuario = $user['nome'] ?? '';
$stmt->close();

// 2. Gera o código numérico de 6 dígitos e validade de 15 minutos
$codigo = sprintf("%06d", mt_rand(1, 999999));
$expira_em = date('Y-m-d H:i:s', strtotime('+15 minutes'));

// 3. Salva na tabela recuperacao_senha
$stmt_insert = $conn->prepare("INSERT INTO recuperacao_senha (email, codigo, expira_em) VALUES (?, ?, ?)");
$stmt_insert->bind_param("sss", $email, $codigo, $expira_em);
$stmt_insert->execute();
$stmt_insert->close();

// 4. Dispara o e-mail real via API
$envio = enviarEmailRecuperacao($email, $codigo, $nomeUsuario);

if ($envio['sucesso']) {
    // Mensagem de sucesso limpa: o código NÃO aparece aqui, vai apenas para a caixa de e-mail
    echo json_encode([
        'status'  => 'success',
        'message' => 'Código de verificação enviado com sucesso para o seu e-mail! Acesse sua caixa de entrada ou pasta de spam para copiar o código.'
    ]);
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => $envio['erro']
    ]);
}
exit();
