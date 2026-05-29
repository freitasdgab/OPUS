<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/pages/auth.html");
    exit();
}

// Em um ambiente real, aqui você usaria a biblioteca PHPMailer para disparar o e-mail.
// Como estamos no Localhost (XAMPP), vamos simular o aviso.

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$email = $user['email'];

// SIMULAÇÃO DO ENVIO:
echo "<script>
    alert('Simulação: Um link de redefinição de senha seria enviado agora para o e-mail [ " . $email . " ]. \\n\\nPara funcionar de verdade no XAMPP, é necessário instalar o PHPMailer e configurar o SMTP do Gmail.');
    window.location.href='../front/pages/perfil.php';
</script>";
?>