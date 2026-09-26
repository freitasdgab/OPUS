<?php
session_start();

// 1. Se o usuário já possui sessão ativa, vai direto para a Dashboard correspondente
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        header("Location: front/pages/admin_dashboard.php");
    } else {
        header("Location: front/pages/dashboard.php");
    }
    exit();
}

// 2. Se o usuário já foi cadastrado anteriormente (cookie), vai direto para a tela de login
if (isset($_COOKIE['opus_cadastrado']) && $_COOKIE['opus_cadastrado'] === '1') {
    header("Location: front/pages/auth.html?mode=login");
    exit();
}

// 3. Caso contrário, envia para a página inicial
header("Location: front/pages/index.html");
exit();
?>
