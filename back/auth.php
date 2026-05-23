<?php
session_start();
require_once 'conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $action = trim($_POST['action'] ?? '');
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $senha = trim($_POST['senha'] ?? '');

    // ----------------------------------------------------
    // MODO: LOGIN
    // ----------------------------------------------------
    if ($action === 'login') {
        
        $email = strtolower($email);

        $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE LOWER(email) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verificação direta para seu teste local
            if ($senha === $user['senha']) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nome'] = $user['nome'];
                $_SESSION['jornada_escolhida'] = 'Java'; // Força a jornada como Java

                // Vai direto para o Painel de Java!
                header("Location: ../front/dashboard.php");
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

        // Validação básica de senhas iguais antes de ir pro banco
        if ($senha !== $confirme_senha) {
            echo "<script>alert('As senhas não coincidem!'); window.history.back();</script>";
            exit();
        }

        // Verifica se o e-mail já existe para não quebrar a regra UNIQUE do banco
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo "<script>alert('Este e-mail já está cadastrado!'); window.history.back();</script>";
            exit();
        }

        // Insere o usuário zerado no banco
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, email, senha, linguagem, xp, trofeus, dificuldade) VALUES (?, ?, ?, 'Java', 0, 0, 1)");
        $stmt_insert->bind_param("sss", $nome, $email, $senha);

        if ($stmt_insert->execute()) {
            $novo_id = $stmt_insert->insert_id;
            
            $_SESSION['user_id'] = $novo_id;
            $_SESSION['user_nome'] = $nome;
            $_SESSION['jornada_escolhida'] = 'Java';

            // Cria o mapa de progresso inicial do jogador (Unidade 1 aberta, resto trancado)
            $conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) VALUES 
                ($novo_id, 1, 'corrente', 0),
                ($novo_id, 2, 'trancado', 0),
                ($novo_id, 3, 'trancado', 0),
                ($novo_id, 4, 'trancado', 0)");

            // Manda direto pro novo painel de Java!
            header("Location: ../front/dashboard.php");
            exit();
        } else {
            echo "<script>alert('Erro ao criar sua conta no banco de dados.'); window.history.back();</script>";
            exit();
        }
    }
}
?>