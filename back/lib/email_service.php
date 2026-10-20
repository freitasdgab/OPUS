<?php
/**
 * OPUS - Serviço Universal de Envio de E-mails
 * Suporta Resend API, Brevo API e SMTP tradicional (PHPMailer)
 */

require_once __DIR__ . '/../config/config_email.php';

/**
 * Envia o e-mail com o código de 6 dígitos para o usuário.
 *
 * @param string $emailDestino
 * @param string $codigo
 * @param string $nomeUsuario
 * @return array ['sucesso' => bool, 'erro' => string|null]
 */
function enviarEmailRecuperacao($emailDestino, $codigo, $nomeUsuario = '') {
    $assunto = '🔑 Seu Código de Recuperação de Senha - Opus';
    $htmlCorpo = gerarTemplateEmail($codigo, $nomeUsuario);

    // 1. TENTA VIA GMAIL SMTP (PHPMAILER) - 100% Gratuito
    if (!empty(SMTP_USER) && !empty(SMTP_PASS)) {
        return enviarViaPHPMailer($emailDestino, $assunto, $htmlCorpo, $codigo);
    }

    // 2. TENTA VIA RESEND API
    if (!empty(RESEND_API_KEY)) {
        return enviarViaResend($emailDestino, $assunto, $htmlCorpo);
    }

    // 3. TENTA VIA BREVO API
    if (!empty(BREVO_API_KEY)) {
        return enviarViaBrevo($emailDestino, $assunto, $htmlCorpo);
    }

    // Nenhuma chave configurada
    return [
        'sucesso' => false,
        'erro' => 'Serviço de e-mail não configurado. Adicione seu Gmail e Senha de App no arquivo back/config/config_email.php.'
    ];
}

/**
 * Disparo via Resend API (cURL)
 */
function enviarViaResend($emailDestino, $assunto, $htmlCorpo) {
    $url = 'https://api.resend.com/emails';

    $payload = [
        'from'    => RESEND_FROM_EMAIL,
        'to'      => [$emailDestino],
        'subject' => $assunto,
        'html'    => $htmlCorpo
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . trim(RESEND_API_KEY),
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false // Seguro no XAMPP local
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['sucesso' => false, 'erro' => 'Erro de conexão cURL com o Resend: ' . $curlError];
    }

    $json = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['sucesso' => true];
    }

    $mensagemErro = $json['message'] ?? 'Código HTTP ' . $httpCode . ': ' . $response;
    return ['sucesso' => false, 'erro' => 'Erro na API do Resend: ' . $mensagemErro];
}

/**
 * Disparo via Brevo API (cURL)
 */
function enviarViaBrevo($emailDestino, $assunto, $htmlCorpo) {
    $url = 'https://api.brevo.com/v3/smtp/email';

    $remetenteEmail = !empty(BREVO_FROM_EMAIL) ? BREVO_FROM_EMAIL : 'opus.plataforma@gmail.com';

    $payload = [
        'sender'      => ['name' => 'Opus', 'email' => $remetenteEmail],
        'to'          => [['email' => $emailDestino]],
        'subject'     => $assunto,
        'htmlContent' => $htmlCorpo
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . trim(BREVO_API_KEY),
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['sucesso' => false, 'erro' => 'Erro de conexão cURL com o Brevo: ' . $curlError];
    }

    $json = json_decode($response, true);

    if ($httpCode >= 200 && $httpCode < 300) {
        return ['sucesso' => true];
    }

    $mensagemErro = $json['message'] ?? 'Código HTTP ' . $httpCode . ': ' . $response;
    return ['sucesso' => false, 'erro' => 'Erro na API do Brevo: ' . $mensagemErro];
}

/**
 * Disparo via PHPMailer (SMTP)
 */
function enviarViaPHPMailer($emailDestino, $assunto, $htmlCorpo, $codigo) {
    require_once __DIR__ . '/PHPMailer/Exception.php';
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($emailDestino);
        $mail->Subject = $assunto;

        // Anexa a logo do Opus no corpo do e-mail (visível em todos os clientes de e-mail)
        $caminhoLogo = realpath(__DIR__ . '/../../front/assets/img/LOGO.png');
        if ($caminhoLogo && file_exists($caminhoLogo)) {
            $mail->addEmbeddedImage($caminhoLogo, 'logo_opus', 'LOGO.png', 'base64', 'image/png');
        }

        $mail->isHTML(true);
        $mail->Body    = $htmlCorpo;
        $mail->AltBody = "Seu código de recuperação Opus é: $codigo (válido por 15 minutos).";

        $mail->send();
        return ['sucesso' => true];
    } catch (\Exception $e) {
        return ['sucesso' => false, 'erro' => 'Erro no envio SMTP: ' . $mail->ErrorInfo];
    }
}

/**
 * Template de E-mail com visual Futurista/Gamer do Opus
 */
function gerarTemplateEmail($codigo, $nomeUsuario = '') {
    $cumprimento = !empty($nomeUsuario) ? "Olá, <strong>" . htmlspecialchars($nomeUsuario) . "</strong>!" : "Olá!";
    
    return '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <style>
            body { margin: 0; padding: 0; background: #0c0d14; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; color: #ffffff; }
            .wrapper { width: 100%; max-width: 580px; margin: 30px auto; background: #151624; border: 1px solid #2337a6; border-radius: 14px; padding: 36px 30px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.6); }
            .logo-container { margin-bottom: 16px; }
            .logo-img { max-height: 80px; max-width: 220px; width: auto; height: auto; object-fit: contain; display: inline-block; }
            .brand { font-size: 26px; font-weight: 900; letter-spacing: 4px; color: #4d66f5; margin-bottom: 20px; text-transform: uppercase; font-family: "Segoe UI", Arial, sans-serif; }
            .title { font-size: 20px; font-weight: 700; color: #ffffff; margin-bottom: 12px; }
            .text { font-size: 15px; color: #b4beea; line-height: 1.6; margin-bottom: 24px; }
            .code-container { background: #0b0c16; border: 2px dashed #4d66f5; border-radius: 12px; padding: 18px 25px; display: inline-block; margin-bottom: 24px; }
            .code { font-size: 34px; font-weight: 800; letter-spacing: 12px; color: #ffffff; margin-left: 12px; }
            .alert-box { background: rgba(77, 102, 245, 0.08); border-left: 4px solid #4d66f5; border-radius: 6px; padding: 12px 16px; font-size: 13px; color: #9da8db; text-align: left; margin: 20px 0; }
            .footer { font-size: 12px; color: #62698a; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; margin-top: 25px; }
        </style>
    </head>
    <body>
        <div class="wrapper">
            <div class="logo-container">
                <img src="cid:logo_opus" alt="Logo Opus" class="logo-img">
            </div>
            <div class="brand">OPUS</div>
            <div class="title">Recuperação de Senha</div>
            <div class="text">' . $cumprimento . '<br>Recebemos uma solicitação para redefinir a sua senha no Opus. Copie o código de verificação abaixo e insira no site:</div>
            
            <div class="code-container">
                <span class="code">' . htmlspecialchars($codigo) . '</span>
            </div>

            <div class="alert-box">
                ⏱ <strong>Atenção:</strong> Este código é válido por <strong>15 minutos</strong>.<br>
                🔒 Se você não solicitou a alteração, ignore esta mensagem com segurança.
            </div>

            <div class="footer">
                Opus - Plataforma de Aprendizado Interativo de Programação<br>
                Mensagem automática, não responda a este e-mail.
            </div>
        </div>
    </body>
    </html>';
}
