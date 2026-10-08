<?php
session_start();
require_once 'conexao.php'; 

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/auth.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    $avatar = $_POST['avatar_selecionado'] ?? '';
    $cor = $_POST['cor_selecionada'] ?? '';
    
    $link_github = $_POST['link_github'] ?? '';
    $link_instagram = $_POST['link_instagram'] ?? '';
    $link_facebook = $_POST['link_facebook'] ?? '';
    $link_youtube = $_POST['link_youtube'] ?? '';
    $link_email = $_POST['link_email'] ?? '';

    // Atualiza a foto, cor de fundo e redes sociais no banco de dados
    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ?, cor_fundo = ?, link_github = ?, link_instagram = ?, link_facebook = ?, link_youtube = ?, link_email = ? WHERE id = ?");
    $stmt->bind_param("sssssssi", $avatar, $cor, $link_github, $link_instagram, $link_facebook, $link_youtube, $link_email, $user_id);
    
    if ($stmt->execute()) {
        // Atualiza a sessão para refletir imediatamente na Topbar
        $_SESSION['foto_perfil'] = $avatar;
        $_SESSION['cor_fundo'] = $cor;

        // Redireciona de volta de onde o formulário foi enviado com segurança
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']); 
        } else {
            // Caminho de fallback
            header("Location: ../front/pages/perfil.php"); 
        }
        exit();
    } else {
        echo "Erro ao atualizar o perfil.";
    }
}
?>