<?php
session_start();
require_once 'conexao.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/pages/auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

// 1. LÓGICA PARA ATUALIZAR A FOTO EM BASE64
if ($action === 'atualizar_foto' && isset($_FILES['foto'])) {
    $foto = $_FILES['foto'];
    
    // Verifica se não houve erro no upload
    if ($foto['error'] === UPLOAD_ERR_OK) {
        
        // Pega o tipo da imagem (ex: image/png, image/jpeg)
        $tipo = mime_content_type($foto['tmp_name']);
        
        // Lê o arquivo e converte para Base64
        $conteudo = file_get_contents($foto['tmp_name']);
        $base64 = 'data:' . $tipo . ';base64,' . base64_encode($conteudo);
        
        // Salva a string gigante direto no banco de dados
        $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("si", $base64, $user_id);
        $stmt->execute();
    }
    
    // Redireciona de volta para o perfil
    header("Location: ../front/pages/perfil.php");
    exit();
}

// 2. LÓGICA PARA ATUALIZAR O NOME
if ($action === 'atualizar_nome' && isset($_POST['novo_nome'])) {
    $novo_nome = trim($_POST['novo_nome']);
    
    if (!empty($novo_nome)) {
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
        $stmt->bind_param("si", $novo_nome, $user_id);
        $stmt->execute();
    }
    
    // Redireciona de volta para o perfil
    header("Location: ../front/pages/perfil.php");
    exit();
}

// Se tentarem acessar a página direto, manda pro perfil
header("Location: ../front/pages/perfil.php");
exit();
?>