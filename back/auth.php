<?php
session_start();
require_once 'conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = trim($_POST['action'] ?? '');
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $senha = trim($_POST['senha'] ?? '');

    $codigo_grupo = trim($_POST['grupo'] ?? $_GET['grupo'] ?? '');
    $convite_id = (int) ($_POST['convite'] ?? $_GET['convite'] ?? 0);

    // ----------------------------------------------------
    // MODO: LOGIN
    // ----------------------------------------------------
    if ($action === 'login') {
        
        $email = strtolower($email);

        require_once 'jogador_status.php';

        $stmt = $conn->prepare("SELECT id, nome, senha, nivel_acesso FROM usuarios WHERE LOWER(email) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if ($senha === $user['senha']) {
                $nivel = $user['nivel_acesso'] ?? 'comum';
                $uid = (int) $user['id'];
                $_SESSION['user_id'] = $uid;
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['user_nivel_acesso'] = $nivel;
                $_SESSION['is_admin'] = ($nivel === 'admin');
                $_SESSION['jornada_escolhida'] = 'Java'; 

                setcookie('opus_cadastrado', '1', time() + 31536000, '/');

                // Processa entrada em grupo ou seguir amigo via link de convite
                if (!empty($codigo_grupo)) {
                    $cod_esc = $conn->real_escape_string($codigo_grupo);
                    $res_g = $conn->query("SELECT id FROM grupos_batalha WHERE codigo_convite = '$cod_esc'");
                    if ($res_g && $g = $res_g->fetch_assoc()) {
                        $gid = (int) $g['id'];
                        $conn->query("INSERT IGNORE INTO grupo_membros (grupo_id, usuario_id) VALUES ($gid, $uid)");
                    }
                }
                if ($convite_id > 0 && $convite_id !== $uid) {
                    $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($uid, $convite_id)");
                    $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($convite_id, $uid)");
                }

                if (!empty($codigo_grupo) || $convite_id > 0) {
                    header("Location: ../front/pages/amigos.php");
                    exit();
                }

                if ($nivel === 'admin') {
                    header("Location: ../front/pages/admin_dashboard.php");
                } else {
                    header("Location: ../front/pages/dashboard.php");
                }
                exit();
            }
        }
        
        echo "<script>
            alert('Erro: Usuário ou senha incorretos!'); 
            window.history.back();
        </script>";
        exit();

    // ----------------------------------------------------
    // MODO: CADASTRO
    // ----------------------------------------------------
    } elseif ($action === 'cadastro') {
        
        $nome = htmlspecialchars(trim($_POST['nome'] ?? ''));
        $confirme_senha = trim($_POST['confirme_senha'] ?? '');
        $email = strtolower($email);

        // 1. Validação de senhas iguais
        if ($senha !== $confirme_senha) {
            echo "<script>alert('As senhas não coincidem!'); window.history.back();</script>";
            exit();
        }

        // 2. NOVA VALIDAÇÃO: Forçar Senha Forte no Servidor
        $maiuscula = preg_match('@[A-Z]@', $senha);
        $minuscula = preg_match('@[a-z]@', $senha);
        $numero    = preg_match('@[0-9]@', $senha);
        $especial  = preg_match('@[^\w]@', $senha); // Verifica se há caracteres que não sejam letras ou números

        if (!$maiuscula || !$minuscula || !$numero || !$especial || strlen($senha) < 8) {
            echo "<script>
                alert('Erro de Segurança: A senha enviada não cumpre os requisitos mínimos de força (Mínimo de 8 caracteres, contendo maiúscula, minúscula, número e caractere especial).'); 
                window.history.back();
            </script>";
            exit();
        }

        // 3. Verifica se o e-mail já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Este e-mail já está cadastrado!'); window.history.back();</script>";
            exit();
        }

        // Garante que as colunas de jogador existam
        require_once 'jogador_status.php';

        // Insere o usuário com 3 vidas e status zerado no banco
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, email, senha, xp, trofeus, dificuldade, vidas, vidas_proxima_em, dias_fogo) VALUES (?, ?, ?, 0, 0, 'Iniciante', 3, NULL, 0)");
        $stmt_insert->bind_param("sss", $nome, $email, $senha);

        if ($stmt_insert->execute()) {
            $novo_id = $stmt_insert->insert_id;
            
            $_SESSION['user_id'] = $novo_id;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['user_nivel_acesso'] = 'comum';
            $_SESSION['is_admin'] = false;
            $_SESSION['jornada_escolhida'] = 'Java';

            setcookie('opus_cadastrado', '1', time() + 31536000, '/');

            // Cria o mapa de progresso inicial
            $conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) VALUES 
                ($novo_id, 1, 'corrente', 0),
                ($novo_id, 2, 'trancado', 0),
                ($novo_id, 3, 'trancado', 0),
                ($novo_id, 4, 'trancado', 0),
                ($novo_id, 5, 'trancado', 0)");

            // Processa entrada em grupo ou seguir amigo via link de convite
            if (!empty($codigo_grupo)) {
                $cod_esc = $conn->real_escape_string($codigo_grupo);
                $res_g = $conn->query("SELECT id FROM grupos_batalha WHERE codigo_convite = '$cod_esc'");
                if ($res_g && $g = $res_g->fetch_assoc()) {
                    $gid = (int) $g['id'];
                    $conn->query("INSERT IGNORE INTO grupo_membros (grupo_id, usuario_id) VALUES ($gid, $novo_id)");
                }
            }
            if ($convite_id > 0 && $convite_id !== $novo_id) {
                $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($novo_id, $convite_id)");
                $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($convite_id, $novo_id)");
            }

            if (!empty($codigo_grupo) || $convite_id > 0) {
                header("Location: ../front/pages/amigos.php");
                exit();
            }

            header("Location: ../front/pages/dashboard.php");
            exit();
        } else {
            echo "<script>alert('Erro ao criar sua conta no banco de dados.'); window.history.back();</script>";
            exit();
        }
    }
}
?>