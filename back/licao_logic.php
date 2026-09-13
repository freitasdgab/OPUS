<?php
session_start();
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/jogador_status.php';
require_once __DIR__ . '/mascotes_capitulos.php';

// Proteção de login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);
if ((int) $status_jogador['vidas'] <= 0) {
    header("Location: dashboard.php?sem_vidas=1");
    exit();
}

$attempt_token = bin2hex(random_bytes(16));

// Captura dinâmica dos parâmetros passados via URL (com fallback seguro para Cap 1, Lição 1)
$unidade_atual = isset($_GET['cap']) ? intval($_GET['cap']) : 1;
$licao_atual = isset($_GET['licao']) ? intval($_GET['licao']) : 1;

// 1. Busca os dados da lição correspondente no banco
$stmt_licao = $conn->prepare("SELECT id, titulo, texto_explicativo, codigo_exemplo FROM licoes WHERE unidade_numero = ? AND licao_numero = ?");
$stmt_licao->bind_param("ii", $unidade_atual, $licao_atual);
$stmt_licao->execute();
$result_licao = $stmt_licao->get_result();
$dados_licao = $result_licao->fetch_assoc();

if (!$dados_licao) {
    die("Lição não encontrada ou ainda não cadastrada no banco!");
}

$licao_id = $dados_licao['id'];

// 2. Busca as 3 perguntas vinculadas a esta lição específica
$perguntas = [];
$result_perguntas = $conn->query("SELECT * FROM perguntas WHERE licao_id = $licao_id LIMIT 3");
while ($row = $result_perguntas->fetch_assoc()) {
    $perguntas[] = $row;
}

// Divide o texto explicativo por parágrafos para gerar os slides
$paragrafos = preg_split('/\R{2,}/', trim($dados_licao['texto_explicativo']));
$codigo_exemplo = trim($dados_licao['codigo_exemplo']);

// 3. Mascote e cor do capítulo atual (mesma cor usada no dashboard)
$mascote_capitulo = opus_mascote_do_capitulo($unidade_atual);
$cor_capitulo = $mascote_capitulo['cor'];
$img_mascote_explicando = $mascote_capitulo['explicando'];
$img_mascote_feliz = $mascote_capitulo['feliz'];
$img_mascote_triste = $mascote_capitulo['triste'];
?>
