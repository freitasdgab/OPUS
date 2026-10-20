<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Recuperar Senha - Opus</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=3">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/intro.css"> 
    <link rel="stylesheet" href="../assets/css/auth.css?v=20261020_6">
    <link rel="stylesheet" href="../assets/css/esqueceu_senha.css">
    <link rel="stylesheet" href="../assets/css/opus_alerta.css">
</head>
<body>
    
    <div class="main-wrapper">
        <div class="auth-container" style="max-width: 480px; margin: 0 auto;">
            <h2 class="auth-title">REDEFINIR SENHA</h2>
            
            <div id="mensagem" class="alert"></div>

            <!-- ETAPA 1: DIGITAR E-MAIL -->
            <div id="etapa-1">
                <p style="color: #a0a0b0; font-family: 'Poppins', sans-serif; font-size: 0.85rem; text-align: center; margin-bottom: 18px; line-height: 1.5;">
                    Informe seu e-mail cadastrado. Enviaremos um código de verificação para a sua caixa de entrada.
                </p>
                <div class="card-input">
                    <div class="input-group">
                        <label for="email"><i class="fa-solid fa-envelope"></i> SEU EMAIL CADASTRADO:</label>
                        <div class="input-with-icon">
                            <input type="email" id="email" required placeholder="seuemail@exemplo.com" autocomplete="email">
                        </div>
                    </div>
                </div>
                <div class="buttons-group" style="margin-top: 20px;">
                    <button type="button" class="btn-auth primary" style="width: 100%;" id="btnEnviarCodigo" onclick="solicitarCodigo()">ENVIAR CÓDIGO</button>
                </div>
            </div>

            <!-- ETAPA 2: DIGITAR APENAS O CÓDIGO RECEBIDO NO E-MAIL -->
            <div id="etapa-2" style="display: none;">
                <p style="color: #a0a0b0; font-family: 'Poppins', sans-serif; font-size: 0.85rem; text-align: center; margin-bottom: 18px; line-height: 1.5;">
                    Abra o seu e-mail e copie o código numérico de 6 dígitos que acabou de chegar.
                </p>
                <div class="card-input">
                    <div class="input-group">
                        <label for="codigo"><i class="fa-solid fa-key"></i> CÓDIGO RECEBIDO NO E-MAIL:</label>
                        <div class="input-with-icon">
                            <input type="text" id="codigo" required placeholder="000000" maxlength="6" style="letter-spacing: 8px; text-align: center; font-family: 'Orbitron', sans-serif; font-size: 1.3rem;">
                        </div>
                    </div>
                </div>
                <div class="buttons-group" style="margin-top: 20px;">
                    <button type="button" class="btn-auth primary" style="width: 100%;" id="btnValidarCodigo" onclick="validarCodigo()">VERIFICAR CÓDIGO</button>
                </div>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="javascript:void(0)" onclick="voltarParaEtapa1()" style="color: #4d66f5; font-size: 0.82rem; text-decoration: none;">
                        <i class="fa-solid fa-rotate-left"></i> Digitar outro e-mail ou reenviar
                    </a>
                </div>
            </div>

            <!-- ETAPA 3: SÓ ABRE APÓS O CÓDIGO SER APROVADO -->
            <div id="etapa-3" style="display: none;">
                <p style="color: #2ed573; font-family: 'Poppins', sans-serif; font-size: 0.85rem; text-align: center; margin-bottom: 18px; line-height: 1.5;">
                    <i class="fa-solid fa-circle-check"></i> Código aprovado! Defina sua nova senha:
                </p>
                <div class="card-input">
                    <div class="input-group">
                        <label for="nova_senha"><i class="fa-solid fa-lock"></i> NOVA SENHA:</label>
                        <div class="input-with-icon">
                            <input type="password" id="nova_senha" required placeholder="Digite sua nova senha" autocomplete="new-password">
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('nova_senha', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="confirme_nova_senha"><i class="fa-solid fa-shield-halved"></i> CONFIRME A NOVA SENHA:</label>
                        <div class="input-with-icon">
                            <input type="password" id="confirme_nova_senha" required placeholder="Repita a nova senha" autocomplete="new-password">
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirme_nova_senha', this)">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="buttons-group" style="margin-top: 20px;">
                    <button type="button" class="btn-auth primary" style="width: 100%;" id="btnSalvarSenha" onclick="redefinirSenha()">ALTERAR SENHA</button>
                </div>
            </div>
            
            <a href="auth.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Voltar para o Login</a>
        </div>
    </div>

    <script src="../assets/js/opus_alerta.js"></script>
    <script src="../assets/js/esqueceu_senha.js?v=20261020_7"></script>
    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
