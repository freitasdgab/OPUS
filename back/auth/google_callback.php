<?php
// ============================================================
//  OPUS – Google OAuth Callback
//  Recebe credential (JWT ID-token) OU access_token do front-end,
//  valida com o Google, cria ou autentica o usuário e retorna JSON.
// ============================================================
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/jogador_status.php';

// Função para requisições seguras à API do Google com suporte a XAMPP/SSL local
function requisitarGoogleApi(string $url, array $headers = []): ?string {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        // Procura bundle de certificados CA comum no XAMPP
        $possibleCas = [
            'C:\\xampp\\apache\\bin\\curl-ca-bundle.crt',
            'C:\\xampp\\php\\extras\\ssl\\cacert.pem',
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile')
        ];
        foreach ($possibleCas as $ca) {
            if (!empty($ca) && file_exists($ca)) {
                curl_setopt($ch, CURLOPT_CAINFO, $ca);
                break;
            }
        }

        $response  = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        curl_close($ch);

        // Se falhar por erro de certificado local no XAMPP da faculdade (erro 60 ou 77), tenta com bypass
        if ($response === false && in_array($curlErrNo, [60, 77])) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
        }

        if ($response !== false) {
            return $response;
        }
    }

    // Fallback via file_get_contents
    $opts = [
        'http' => [
            'timeout' => 12,
            'header'  => implode("\r\n", $headers)
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ]
    ];
    $context = stream_context_create($opts);
    $res = @file_get_contents($url, false, $context);
    return $res !== false ? $res : null;
}

// ── 1. Lê o token enviado pelo front ────────────────────────
$input        = json_decode(file_get_contents('php://input'), true);
$credential   = trim($input['credential'] ?? '');
$access_token = trim($input['access_token'] ?? '');

if (empty($credential) && empty($access_token)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Token do Google não recebido.']);
    exit();
}

$payload = null;

// ── 2. Valida com a API do Google (Access Token ou ID Token) ─
if (!empty($access_token)) {
    // Via OAuth 2.0 Access Token (userinfo)
    $url = 'https://www.googleapis.com/oauth2/v3/userinfo';
    $resp = requisitarGoogleApi($url, ["Authorization: Bearer $access_token"]);
    if ($resp) {
        $payload = json_decode($resp, true);
    }
}

if (!$payload && !empty($credential)) {
    // Via ID-Token (tokeninfo)
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
    $resp = requisitarGoogleApi($url);
    if ($resp) {
        $payload = json_decode($resp, true);
    }
}

if (!$payload) {
    http_response_code(502);
    echo json_encode([
        'ok'   => false,
        'erro' => 'Não foi possível validar as credenciais com os servidores do Google. Verifique a conexão com a internet ou se a rede da faculdade bloqueia APIs externas.'
    ]);
    exit();
}

// ── 3. Valida campos obrigatórios do payload ──────────────────
if (empty($payload['sub']) || empty($payload['email'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Token inválido.']);
    exit();
}

$verified = $payload['email_verified'] ?? 'false';
if ($verified !== 'true' && $verified !== true) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'E-mail da conta Google não verificado.']);
    exit();
}

$google_id = $payload['sub'];
$email     = strtolower(trim($payload['email']));
$nome      = htmlspecialchars(trim($payload['name'] ?? explode('@', $email)[0]));
$foto      = isset($payload['picture']) ? $conn->real_escape_string($payload['picture']) : null;

// ── 4. Garante que as colunas google_id / foto_google existem ─
$conn->query("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS google_id VARCHAR(100) DEFAULT NULL");
$conn->query("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS foto_google VARCHAR(500) DEFAULT NULL");

// ── 5. Busca usuário por google_id OU e-mail ──────────────────
$email_esc = $conn->real_escape_string($email);
$gid_esc   = $conn->real_escape_string($google_id);

$stmt = $conn->prepare("SELECT id, nome, nivel_acesso FROM usuarios WHERE google_id = ? OR LOWER(email) = ? LIMIT 1");
$stmt->bind_param("ss", $google_id, $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if ($usuario) {
    // 5a. Já existe → atualiza google_id e foto
    $uid      = (int) $usuario['id'];
    $foto_sql = $foto ? "'$foto'" : "NULL";
    $conn->query("UPDATE usuarios SET google_id = '$gid_esc', foto_google = $foto_sql WHERE id = $uid");
} else {
    // 5b. Novo usuário → cria conta
    $stmt_insert = $conn->prepare(
        "INSERT INTO usuarios (nome, email, senha, google_id, foto_google, xp, trofeus, dificuldade, vidas, vidas_proxima_em, dias_fogo)
         VALUES (?, ?, '', ?, ?, 0, 0, 'Iniciante', 3, NULL, 0)"
    );
    $stmt_insert->bind_param("ssss", $nome, $email, $google_id, $foto);

    if (!$stmt_insert->execute()) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'erro' => 'Erro ao criar conta no banco de dados: ' . $stmt_insert->error]);
        exit();
    }

    $uid = (int) $stmt_insert->insert_id;

    // Cria progresso inicial
    $conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) VALUES
        ($uid, 1, 'corrente', 0),
        ($uid, 2, 'trancado',  0),
        ($uid, 3, 'trancado',  0),
        ($uid, 4, 'trancado',  0),
        ($uid, 5, 'trancado',  0)");

    $usuario = ['id' => $uid, 'nome' => $nome, 'nivel_acesso' => 'comum'];
}

// ── 6. Inicia sessão ──────────────────────────────────────────
$nivel                         = $usuario['nivel_acesso'] ?? 'comum';
$_SESSION['user_id']           = $uid;
$_SESSION['user_nome']         = $usuario['nome'] ?? $nome;
$_SESSION['user_nivel_acesso'] = $nivel;
$_SESSION['is_admin']          = ($nivel === 'admin');
$_SESSION['jornada_escolhida'] = 'Java';

// ── 7. Retorna URL de redirecionamento ────────────────────────
echo json_encode([
    'ok'           => true,
    'redirect_url' => 'dashboard.php',
]);
?>
