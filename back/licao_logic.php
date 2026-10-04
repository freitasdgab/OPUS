<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/jogador_status.php';
require_once __DIR__ . '/mascotes_capitulos.php';

// Proteção de login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador não pode jogar lições
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: ../front/pages/admin_dashboard.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);
if ((int) $status_jogador['vidas'] <= 0) {
    header("Location: dashboard.php?sem_vidas=1");
    exit();
}

$_SESSION['attempt_token'] = bin2hex(random_bytes(16));
$attempt_token = $_SESSION['attempt_token'];

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
    header("Location: dashboard.php?aviso=" . urlencode("Esta lição ainda não está disponível ou está em desenvolvimento!"));
    exit();
}

$licao_id = $dados_licao['id'];

// 2. Busca as perguntas vinculadas a esta lição específica
$perguntas = [];
// Seleciona as perguntas (se a coluna 'tipo' não existir, o PHP não quebra, apenas não a trará)
$result_perguntas = $conn->query("SELECT * FROM perguntas WHERE licao_id = $licao_id LIMIT 10");

$tipos_intercalados = ['multipla_escolha', 'digitar_codigo', 'completar_codigo', 'completar_codigo_escrito'];
$idx_tipo = 0;

while ($row = $result_perguntas->fetch_assoc()) {
    // Se o banco ainda não tiver a coluna 'tipo', ou se todas estiverem como o padrão 'multipla_escolha',
    // mockamos para intercalar os tipos e demonstrar as novas lições.
    if (!isset($row['tipo']) || $row['tipo'] === 'multipla_escolha' || empty($row['tipo'])) {
        $row['tipo'] = $tipos_intercalados[$idx_tipo % 4];
    }
    
    // Para 'digitar_codigo' e 'completar_codigo_escrito', garantir que alternativa_correta seja o alvo.
    if (($row['tipo'] === 'digitar_codigo' || $row['tipo'] === 'completar_codigo_escrito') && !isset($row['codigo_esperado'])) {
        $row['codigo_esperado'] = $row['alternativa_correta']; // mock
    }
    
    $perguntas[] = $row;
    $idx_tipo++;
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
