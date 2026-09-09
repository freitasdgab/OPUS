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
            padding: 10px; margin-bottom: 15px; border-radius: 5px;
            text-align: center; font-family: 'Poppins', sans-serif; font-size: 0.9rem;
            display: none; /* Escondido por padrão, ativado via JS */
        }
        .alert.error { background-color: #ff4d4d; color: white; display: block; }
        .alert.success { background-color: #4CAF50; color: white; display: block; }
        .back-link {
            display: block; text-align: center; margin-top: 15px; color: #4d66f5;
            text-decoration: none; font-family: 'Poppins', sans-serif; font-weight: 600;
        }
    </style>
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
    <script>
        function mostrarMensagem(texto, tipo) {
            const div = document.getElementById('mensagem');
            div.innerHTML = texto;
            div.className = 'alert ' + tipo;
        }

        function solicitarCodigo() {
            const email = document.getElementById('email').value.trim();
            if(!email) return mostrarMensagem("Por favor, digite seu e-mail.", "error");

            mostrarMensagem("Aguarde, enviando e-mail...", "success");

            fetch('../../back/solicitar_codigo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    mostrarMensagem(data.message, 'success');
                    document.getElementById('etapa-1').style.display = 'none';
                    document.getElementById('etapa-2').style.display = 'block';
                } else {
                    mostrarMensagem(data.message, 'error');
                }
            }).catch(() => mostrarMensagem("Erro na comunicação com o servidor.", "error"));
        }

        function redefinirSenha() {
            const email = document.getElementById('email').value.trim();
            const codigo = document.getElementById('codigo').value.trim();
            const novaSenha = document.getElementById('nova_senha').value;

            if(!codigo || !novaSenha) return mostrarMensagem("Preencha o código e a nova senha.", "error");

            fetch('../../back/redefinir_senha_acao.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&codigo=${encodeURIComponent(codigo)}&nova_senha=${encodeURIComponent(novaSenha)}`
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    mostrarMensagem(data.message + " <a href='auth.html'>Clique aqui para fazer login</a>", 'success');
                    document.getElementById('etapa-2').style.display = 'none';
                } else {
                    mostrarMensagem(data.message, 'error');
                }
            }).catch(() => mostrarMensagem("Erro ao alterar senha.", "error"));
        }
    </script>
</body>
</html>