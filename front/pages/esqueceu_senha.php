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

    <link rel="stylesheet" href="../assets/css/esqueceu_senha.css">
</head>
<body>
    <canvas id="bg-canvas"></canvas>

    <div class="main-wrapper">
        <div class="auth-container" style="max-width: 500px; margin: 0 auto;">
            <h2 class="auth-title">REDEFINIR SENHA</h2>
            
            <div id="mensagem" class="alert"></div>

            <div id="etapa-1">
                <div class="card-input">
                    <div class="input-group">
                        <label for="email"><i class="fa-solid fa-envelope"></i> SEU EMAIL CADASTRADO:</label>
                        <div class="input-with-icon">
                            <input type="email" id="email" required placeholder="seuemail@exemplo.com">
                        </div>
                    </div>
                </div>
                <div class="buttons-group" style="margin-top: 20px;">
                    <button type="button" class="btn-auth primary" style="width: 100%;" onclick="solicitarCodigo()">ENVIAR CÓDIGO</button>
                </div>
            </div>

            <div id="etapa-2" style="display: none;">
                <p style="color: #a0a0b0; font-family: 'Poppins'; font-size: 0.85rem; text-align: center; margin-bottom: 15px;">
                    Enviamos um código de 6 dígitos para o seu e-mail.
                </p>
                <div class="card-input">
                    <div class="input-group">
                        <label for="codigo"><i class="fa-solid fa-key"></i> CÓDIGO DE VERIFICAÇÃO:</label>
                        <div class="input-with-icon">
                            <input type="text" id="codigo" required placeholder="000000" maxlength="6" style="letter-spacing: 8px; text-align: center; font-family: 'Orbitron'; font-size: 1.2rem;">
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="nova_senha"><i class="fa-solid fa-lock"></i> NOVA SENHA:</label>
                        <div class="input-with-icon">
                            <input type="password" id="nova_senha" required placeholder="******">
                        </div>
                    </div>
                </div>
                <div class="buttons-group" style="margin-top: 20px;">
                    <button type="button" class="btn-auth primary" style="width: 100%;" onclick="redefinirSenha()">ALTERAR SENHA</button>
                </div>
            </div>
            
            <a href="auth.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para o Login</a>
        </div>
    </div>

    <script src="../assets/js/auth.js"></script>
    <script src="../assets/js/esqueceu_senha.js"></script>
</body>
</html>