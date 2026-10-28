<?php
session_start();
require_once __DIR__ . '/../config/conexao.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../front/pages/auth.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    
    // Fetch current to use as fallback
    $stmt_user = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $dados = $stmt_user->get_result()->fetch_assoc();

    $action = $_POST['action'] ?? '';

    $avatar = $dados['foto_perfil'] ?? '';
    $cor = !empty($_POST['cor_selecionada']) ? trim($_POST['cor_selecionada']) : ($dados['cor_fundo'] ?? '#1cb0f6');

    // Validação de cor hexadecimal segura
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $cor)) {
        $cor = $dados['cor_fundo'] ?? '#1cb0f6';
    }

    if ($action === 'atualizar_aparencia') {
        // 1. O usuário solicitou a remoção da foto?
        if (!empty($_POST['remover_foto']) && $_POST['remover_foto'] === '1') {
            if (!empty($avatar) && strpos($avatar, '../assets/uploads/avatars/') === 0) {
                $old_file = __DIR__ . '/../../front/assets/uploads/avatars/' . basename($avatar);
                if (file_exists($old_file)) {
                    @unlink($old_file);
                }
            }
            $avatar = '';
        }

        // 2. Novo upload de foto enviado via multipart?
        if (isset($_FILES['foto_upload']) && $_FILES['foto_upload']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['foto_upload'];
            $max_size = 5 * 1024 * 1024; // 5 MB
            
            if ($file['size'] <= $max_size) {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed_exts)) {
                    // Valida se o arquivo é realmente uma imagem
                    $check_img = @getimagesize($file['tmp_name']);
                    if ($check_img !== false) {
                        $upload_dir = __DIR__ . '/../../front/assets/uploads/avatars/';
                        if (!is_dir($upload_dir)) {
                            @mkdir($upload_dir, 0755, true);
                        }
                        
                        $new_filename = 'avatar_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $destination = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            // Se havia foto enviada anteriormente, remove para não acumular
                            if (!empty($avatar) && strpos($avatar, '../assets/uploads/avatars/') === 0) {
                                $old_file = $upload_dir . basename($avatar);
                                if (file_exists($old_file)) {
                                    @unlink($old_file);
                                }
                            }
                            $avatar = '../assets/uploads/avatars/' . $new_filename;
                        }
                    }
                }
            }
        } elseif (isset($_POST['avatar_selecionado']) && empty($_POST['remover_foto'])) {
            $avatar = trim($_POST['avatar_selecionado']);
        }
    }
    
    $paises_map = [
        'br' => 'Brasil',
        'de' => 'Alemanha',
        'us' => 'Estados Unidos',
        'pt' => 'Portugal',
        'ar' => 'Argentina',
        'ca' => 'Canadá',
        'es' => 'Espanha',
        'fr' => 'França',
        'it' => 'Itália',
        'jp' => 'Japão',
        'gb' => 'Reino Unido',
        'mx' => 'México',
        'cl' => 'Chile',
        'co' => 'Colômbia',
        'uy' => 'Uruguai'
    ];
    $pais_codigo = !empty($_POST['pais_codigo']) ? strtolower(trim($_POST['pais_codigo'])) : (!empty($dados['pais_codigo']) ? $dados['pais_codigo'] : 'br');
    $pais = $paises_map[$pais_codigo] ?? ($dados['pais'] ?? 'Brasil');
    
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

    $stmt = $conn->prepare("UPDATE usuarios SET foto_perfil = ?, cor_fundo = ?, pais = ?, pais_codigo = ?, link_github = ?, link_instagram = ?, link_facebook = ?, link_youtube = ?, link_linkedin = ?, link_email = ?, projeto_titulo_1 = ?, projeto_url_1 = ?, projeto_titulo_2 = ?, projeto_url_2 = ?, projeto_titulo_3 = ?, projeto_url_3 = ? WHERE id = ?");
    
    $stmt->bind_param("ssssssssssssssssi", $avatar, $cor, $pais, $pais_codigo, $link_github, $link_instagram, $link_facebook, $link_youtube, $link_linkedin, $link_email, $projeto_titulo_1, $projeto_url_1, $projeto_titulo_2, $projeto_url_2, $projeto_titulo_3, $projeto_url_3, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['foto_perfil'] = $avatar;
        $_SESSION['cor_fundo'] = $cor;
        $_SESSION['pais'] = $pais;
        $_SESSION['pais_codigo'] = $pais_codigo;

        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            header("Location: " . $_SERVER['HTTP_REFERER']); 
        } else {
            header("Location: ../../front/pages/perfil.php"); 
        }
        exit();
    } else {
        echo "Erro ao atualizar o perfil.";
    }
}
?>