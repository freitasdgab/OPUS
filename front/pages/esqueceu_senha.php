<?php
session_start();
// Ajuste o caminho da conexão se necessário (depende de onde você vai salvar este arquivo)
require_once '../../back/conexao.php'; 

$mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $email = strtolower($email);
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirme_senha = $_POST['confirme_senha'] ?? '';

    if ($nova_senha !== $confirme_senha) {
        $mensagem = "<div class='alert error'>As senhas não coincidem. Tente novamente.</div>";
    } else {
        // Verifica se o e-mail existe no banco
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE LOWER(email) = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            // E-mail encontrado! Atualiza a senha.
            $stmt_update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE LOWER(email) = ?");
            $stmt_update->bind_param("ss", $nova_senha, $email);
            
            if ($stmt_update->execute()) {
                $mensagem = "<div class='alert success'>Senha alterada com sucesso! <a href='login.php'>Clique aqui para logar</a>.</div>";
                // Nota: ajuste o 'login.php' acima para o nome real do seu arquivo de login
            } else {
                $mensagem = "<div class='alert error'>Erro ao atualizar a senha.</div>";
            }
        } else {
            $mensagem = "<div class='alert error'>Nenhuma conta encontrada com este e-mail.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Opus</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/intro.css"> 
    <link rel="stylesheet" href="../assets/css/auth.css">

    <style>
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }
        .alert.error { background-color: #ff4d4d; color: white; }
        .alert.success { background-color: #4CAF50; color: white; }
        .alert a { color: white; text-decoration: underline; font-weight: bold; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #4d66f5;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>

    <div class="main-wrapper">
        <div class="auth-container" style="max-width: 500px; margin: 0 auto;">
            <h2 class="auth-title">REDEFINIR SENHA</h2>
            
            <?php echo $mensagem; ?>

            <form action="" method="POST">
                <div class="card-input">
                    <div class="input-group">
                        <label for="email"><i class="fa-solid fa-envelope"></i> SEU EMAIL CADASTRADO:</label>
                        <div class="input-with-icon">
                            <input type="email" id="email" name="email" required placeholder="seuemail@exemplo.com">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="nova_senha"><i class="fa-solid fa-lock"></i> NOVA SENHA:</label>
                        <div class="input-with-icon">
                            <input type="password" id="nova_senha" name="nova_senha" required placeholder="******">
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="confirme_senha"><i class="fa-solid fa-shield-halved"></i> CONFIRME A NOVA SENHA:</label>
                        <div class="input-with-icon">
                            <input type="password" id="confirme_senha" name="confirme_senha" required placeholder="******">
                        </div>
                    </div>
                </div>

                <div class="buttons-group" style="margin-top: 20px;">
                    <button type="submit" class="btn-auth primary" style="width: 100%;">ALTERAR SENHA</button>
                </div>
                
                <a href="auth.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para o Login</a>
            </form>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
</body>
</html>