<?php
session_start();
require_once 'conexao.php'; 

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/auth.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'atualizar_aparencia') {
    $user_id = $_SESSION['user_id'];
    $avatar = $_POST['avatar_selecionado'];
    $cor = $_POST['cor_selecionada'];

    // Atualiza a foto e a cor de fundo no banco de dados
    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ?, cor_fundo = ? WHERE id = ?");
    $stmt->bind_param("ssi", $avatar, $cor, $user_id);
    
    if ($stmt->execute()) {
        // Atualiza a sessão para refletir imediatamente na Topbar
        $_SESSION['foto_perfil'] = $avatar;
        $_SESSION['cor_fundo'] = $cor;

        // Redireciona de volta de onde o formulário foi enviado com segurança
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']); 
        } else {
            // Caminho de fallback caso o navegador não envie o REFERER
            header("Location: perfil.php"); 
        }
        exit();
    } else {
        echo "Erro ao atualizar o perfil.";
    }
}
?>