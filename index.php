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

// 2. Caso não esteja com sessão ativa, envia sempre para a página de apresentação inicial
header("Location: front/pages/index.html");
exit();
?>
