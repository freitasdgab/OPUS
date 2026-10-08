<?php
session_start();
require_once 'conexao.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/auth.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Fetch current to use as fallback
    $stmt_user = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $dados = $stmt_user->get_result()->fetch_assoc();

    $avatar = !empty($_POST['avatar_selecionado']) ? $_POST['avatar_selecionado'] : $dados['foto_perfil'];
    $cor = !empty($_POST['cor_selecionada']) ? $_POST['cor_selecionada'] : $dados['cor_fundo'];
    
    $action = $_POST['action'] ?? '';
    
    $link_github = $dados['link_github'];
    $link_instagram = $dados['link_instagram'];
    $link_facebook = $dados['link_facebook'];
    $link_youtube = $dados['link_youtube'];
    $link_linkedin = $dados['link_linkedin'];
    $link_email = $dados['link_email'];

    $projeto_titulo_1 = $dados['projeto_titulo_1'];
    $projeto_url_1 = $dados['projeto_url_1'];
    $projeto_titulo_2 = $dados['projeto_titulo_2'];
    $projeto_url_2 = $dados['projeto_url_2'];
    $projeto_titulo_3 = $dados['projeto_titulo_3'];
    $projeto_url_3 = $dados['projeto_url_3'];
    
    if ($action === 'update_social') {
        $link_github = $_POST['link_github'] ?? '';
        $link_instagram = $_POST['link_instagram'] ?? '';
        $link_facebook = $_POST['link_facebook'] ?? '';
        $link_youtube = $_POST['link_youtube'] ?? '';
        $link_linkedin = $_POST['link_linkedin'] ?? '';
        $link_email = $_POST['link_email'] ?? '';
    } elseif ($action === 'update_projetos') {
        $projeto_titulo_1 = $_POST['projeto_titulo_1'] ?? '';
        $projeto_url_1 = $_POST['projeto_url_1'] ?? '';
        $projeto_titulo_2 = $_POST['projeto_titulo_2'] ?? '';
        $projeto_url_2 = $_POST['projeto_url_2'] ?? '';
        $projeto_titulo_3 = $_POST['projeto_titulo_3'] ?? '';
        $projeto_url_3 = $_POST['projeto_url_3'] ?? '';
    }

    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ?, cor_fundo = ?, link_github = ?, link_instagram = ?, link_facebook = ?, link_youtube = ?, link_linkedin = ?, link_email = ?, projeto_titulo_1 = ?, projeto_url_1 = ?, projeto_titulo_2 = ?, projeto_url_2 = ?, projeto_titulo_3 = ?, projeto_url_3 = ? WHERE id = ?");
    
    $stmt->bind_param("ssssssssssssssi", $avatar, $cor, $link_github, $link_instagram, $link_facebook, $link_youtube, $link_linkedin, $link_email, $projeto_titulo_1, $projeto_url_1, $projeto_titulo_2, $projeto_url_2, $projeto_titulo_3, $projeto_url_3, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['foto_perfil'] = $avatar;
        $_SESSION['cor_fundo'] = $cor;

        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']); 
        } else {
            header("Location: ../front/pages/perfil.php"); 
        }
        exit();
    } else {
        echo "Erro ao atualizar o perfil.";
    }
}
?>