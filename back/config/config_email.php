<?php
/**
 * OPUS - Configurações de Envio de E-mail
 * 100% Gratuito, sem pedir cartão de crédito.
 */

// ============================================================================
// MÉTODO 100% GRÁTIS: GMAIL COM SENHA DE APLICATIVO
// Não pede cartão de crédito, não cobra nada e envia para QUALQUER e-mail (até 500/dia).
//
// COMO PEGAR A SENHA DE APP DO GOOGLE EM 1 MINUTO:
// 1. Acesse: https://myaccount.google.com/apppasswords
//    (Ou abra sua Conta Google -> guia "Segurança" -> digite "Senhas de app" na busca).
//    *Observação: É necessário que a "Verificação em duas etapas" da sua conta Google esteja ativada.
// 2. No campo "Nome do app", digite: OPUS
// 3. Clique em "Criar". O Google vai gerar uma senha de 16 letras (ex: abcd efgh ijkl mnop).
// 4. Cole o seu e-mail do Gmail e essa senha de 16 letras abaixo:
// ============================================================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'gabsantosbag@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'fnpxvwxuglbcydfr');
define('SMTP_FROM_NAME', 'Opus - Plataforma de Programação');


// ============================================================================
// (OPCIONAL) APIs ALTERNATIVAS CASO TENHA CONTA
// ============================================================================
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: '');
define('RESEND_FROM_EMAIL', 'Opus <onboarding@resend.dev>');

define('BREVO_API_KEY', getenv('BREVO_API_KEY') ?: '');
define('BREVO_FROM_EMAIL', getenv('BREVO_FROM_EMAIL') ?: '');
