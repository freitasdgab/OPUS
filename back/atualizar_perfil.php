<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ATUALIZAR NOME
    if ($action === 'atualizar_nome') {
        $novo_nome = htmlspecialchars(trim($_POST['novo_nome']));
        
        if (!empty($novo_nome)) {
            $stmt = $conn->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
            $stmt->bind_param("si", $novo_nome, $user_id);
            $stmt->execute();
            $_SESSION['user_nome'] = $novo_nome; // Atualiza a sessão também
            echo "<script>alert('Nome atualizado com sucesso!'); window.location.href='../front/perfil.php';</script>";
        }
    }

    // ATUALIZAR FOTO DE PERFIL
    elseif ($action === 'atualizar_foto') {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            
            $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($extensao, $permitidos)) {
                // Cria um nome único para não sobrepor outras imagens
                $novo_nome_img = uniqid("perfil_") . "." . $extensao;
                // Define o caminho (garanta que a pasta 'uploads' exista na raiz do projeto)
                $caminho_destino = "../uploads/" . $novo_nome_img;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
                    // Salva no banco o caminho relativo
                    $caminho_banco = "uploads/" . $novo_nome_img;
                    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
                    $stmt->bind_param("si", $caminho_banco, $user_id);
                    $stmt->execute();
                    
                    echo "<script>alert('Foto atualizada com sucesso!'); window.location.href='../front/perfil.php';</script>";
                } else {
                    echo "<script>alert('Erro ao salvar a imagem na pasta.'); window.location.href='../front/perfil.php';</script>";
                }
            } else {
                echo "<script>alert('Formato inválido. Use JPG, PNG ou GIF.'); window.location.href='../front/perfil.php';</script>";
            }
        }
    }
}
?>