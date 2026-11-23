<?php
date_default_timezone_set('America/Sao_Paulo');
session_start();

require_once __DIR__ . '/../config/conexao.php';

$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    || (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

function cert_json_response(int $status, array $payload): void {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function cert_formatar_data_extenso(int $timestamp): string {
    $meses_pt = [
        1 => 'janeiro',
        2 => 'fevereiro',
        3 => 'março',
        4 => 'abril',
        5 => 'maio',
        6 => 'junho',
        7 => 'julho',
        8 => 'agosto',
        9 => 'setembro',
        10 => 'outubro',
        11 => 'novembro',
        12 => 'dezembro',
    ];
    $dia = (int) date('j', $timestamp);
    $mes = $meses_pt[(int) date('n', $timestamp)] ?? '';
    $ano = date('Y', $timestamp);
    return sprintf('%d de %s de %s', $dia, $mes, $ano);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cert_json_response(405, ['ok' => false, 'erro' => 'Método não permitido.']);
}

if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        cert_json_response(401, ['ok' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    }
    header('Location: ../../front/pages/auth.html');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

$raw = file_get_contents('php://input');
$json_data = json_decode((string) $raw, true);
$input = is_array($json_data) ? $json_data : $_POST;

$csrf_recebido = (string) ($input['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
$csrf_sessao = (string) ($_SESSION['cert_csrf_token'] ?? '');
if ($csrf_sessao === '' || $csrf_recebido === '' || !hash_equals($csrf_sessao, $csrf_recebido)) {
    cert_json_response(403, ['ok' => false, 'erro' => 'Token de segurança inválido. Recarregue a página e tente novamente.']);
}

$nome_completo = strip_tags((string) ($input['nome_completo'] ?? ''));
$nome_completo = trim((string) preg_replace('/\s+/u', ' ', $nome_completo));

$len = mb_strlen($nome_completo, 'UTF-8');
$palavras = preg_split('/\s+/u', $nome_completo, -1, PREG_SPLIT_NO_EMPTY) ?: [];

if ($len < 5 || $len > 120) {
    cert_json_response(422, ['ok' => false, 'erro' => 'Informe seu nome completo (entre 5 e 120 caracteres).']);
}

if (count($palavras) < 2) {
    cert_json_response(422, ['ok' => false, 'erro' => 'Digite nome e sobrenome completos como devem aparecer no certificado.']);
}

if (!preg_match('/^[\p{L}\p{M}\s\'\.\-]+$/u', $nome_completo)) {
    cert_json_response(422, ['ok' => false, 'erro' => 'O nome deve conter apenas letras, espaços, hífen, ponto ou apóstrofo.']);
}

try {
    $conn->query("CREATE TABLE IF NOT EXISTS `certificados_emitidos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `usuario_id` INT NOT NULL,
        `nome_completo` VARCHAR(140) NOT NULL,
        `codigo_validacao` VARCHAR(40) NOT NULL,
        `emitido_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_cert_usuario` (`usuario_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    $stmt_chk = $conn->prepare('SELECT nome_completo, codigo_validacao, emitido_em FROM certificados_emitidos WHERE usuario_id = ? LIMIT 1');
    $stmt_chk->bind_param('i', $user_id);
    $stmt_chk->execute();
    $existente = $stmt_chk->get_result()->fetch_assoc();
    $stmt_chk->close();

    if ($existente) {
        if (!$is_ajax) {
            $destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../front/pages/resultado.php?preview_fim_curso=1';
            header('Location: ' . $destino);
            exit();
        }

        $ts_exist = strtotime((string) $existente['emitido_em']) ?: time();
        cert_json_response(409, [
            'ok' => false,
            'ja_emitido' => true,
            'erro' => 'Seu certificado já foi emitido. O nome completo é definitivo e não pode mais ser alterado.',
            'nome_completo' => (string) $existente['nome_completo'],
            'codigo_validacao' => (string) $existente['codigo_validacao'],
            'emitido_em' => (string) $existente['emitido_em'],
            'data_emissao_extenso' => cert_formatar_data_extenso($ts_exist),
            'ano_emissao' => date('Y', $ts_exist),
        ]);
    }

    $agora = date('Y-m-d H:i:s');
    $ano = date('Y');
    $username = 'aluno.opus';

    $stmt_u = $conn->prepare('SELECT username FROM usuarios WHERE id = ? LIMIT 1');
    $stmt_u->bind_param('i', $user_id);
    $stmt_u->execute();
    $row_u = $stmt_u->get_result()->fetch_assoc();
    $stmt_u->close();

    if ($row_u && !empty($row_u['username'])) {
        $username = (string) $row_u['username'];
    }
    if ($username[0] !== '@') {
        $username = '@' . $username;
    }

    $hash_verif = strtoupper(substr(hash('sha256', 'OPUS-CERT-' . $user_id . '-' . $username), 0, 6));
    $codigo_validacao = sprintf('OPUS-%s-%04d-%s', $ano, $user_id, $hash_verif);
    $emitido_em = $agora;
    $primeira_emissao = true;

    $stmt_ins = $conn->prepare('INSERT INTO certificados_emitidos (usuario_id, nome_completo, codigo_validacao, emitido_em) VALUES (?, ?, ?, ?)');
    $stmt_ins->bind_param('isss', $user_id, $nome_completo, $codigo_validacao, $emitido_em);
    $stmt_ins->execute();
    $stmt_ins->close();

    $ts = strtotime($emitido_em) ?: time();
    $data_extenso = cert_formatar_data_extenso($ts);
    $ano_emissao = date('Y', $ts);

    if ($is_ajax) {
        cert_json_response(200, [
            'ok' => true,
            'nome_completo' => $nome_completo,
            'codigo_validacao' => $codigo_validacao,
            'emitido_em' => $emitido_em,
            'data_emissao_extenso' => $data_extenso,
            'ano_emissao' => $ano_emissao,
            'primeira_emissao' => $primeira_emissao,
        ]);
    }

    $destino = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../../front/pages/resultado.php?preview_fim_curso=1';
    header('Location: ' . $destino);
    exit();
} catch (Throwable $e) {
    cert_json_response(500, [
        'ok' => false,
        'erro' => 'Não foi possível salvar os dados do certificado. Tente novamente.',
    ]);
}
