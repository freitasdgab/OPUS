<?php
session_start();
$linguagem = $_GET['lang'] ?? '';

if (!empty($linguagem)) {
    // 1. Salva a escolha na sessão para usar no programa
    $_SESSION['jornada_escolhida'] = $linguagem;

    // 2. Aqui você salvaria no Banco de Dados (MySQL)
    // $sql = "UPDATE usuarios SET trilha = '$linguagem' WHERE id = " . $_SESSION['user_id'];

    // 3. Leva para a tela inicial do programa
    header("Location: ../front/pages/dashboard.php"); 
    exit();
} else {
    header("Location: ../front/pages/selecao.html");
}
?>