<?php
// ============================================================
//  OPUS – Google OAuth Callback
//  Recebe o credential JWT do front-end, valida com o Google,
//  cria ou autentica o usuário e retorna JSON com redirect.
// ============================================================
session_start();
header('Content-Type: application/json');
require_once 'conexao.php';
require_once 'jogador_status.php';

// ── 1. Lê o token enviado pelo front ────────────────────────
$input      = json_decode(file_get_contents('php://input'), true);
$credential = trim($input['credential'] ?? '');

if (empty($credential)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Token não recebido.']);
    exit();
}

// ── 2. Verifica o token com a API do Google ──────────────────
//    Endpoint tokeninfo aceita o ID-token diretamente.
$url      = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
$response = @file_get_contents($url);

if ($response === false) {
    // Fallback via cURL (servidores sem allow_url_fopen)
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
}

if (!$response) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'erro' => 'Não foi possível validar o token com o Google.']);
    exit();
}

$payload = json_decode($response, true);

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
        echo json_encode(['ok' => false, 'erro' => 'Erro ao criar conta.']);
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
