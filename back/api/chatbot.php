<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/chatbot_logic.php';

$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true) ?? [];

// Endpoint de diagnóstico e status da IA (Groq)
if (isset($_GET['status']) || ($input['acao'] ?? '') === 'status') {
    $chave = obter_groq_api_key();
    echo json_encode([
        'success' => true,
        'configurado' => !empty($chave),
        'provedor' => 'Groq Cloud',
        'modelo_primario' => GROQ_MODEL_PRIMARY,
        'modelo_fallback' => GROQ_MODEL_FALLBACK,
        'chave_mascarada' => !empty($chave) ? substr($chave, 0, 7) . '...' . substr($chave, -4) : 'Não configurada'
    ]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'resposta' => 'Você precisa estar logado no Opus para conversar com o Opi IA.'
    ]);
    exit();
}

$mensagem = trim($input['mensagem'] ?? '');
$contexto = trim($input['contexto'] ?? '');
$historico = is_array($input['historico'] ?? null) ? $input['historico'] : [];

if (empty($mensagem)) {
    echo json_encode([
        'success' => false, 
        'resposta' => 'Por favor, envie uma mensagem para o Opi!'
    ]);
    exit();
}

$user_nome = $_SESSION['user_nome'] ?? 'aluno';
$user_id   = (int) ($_SESSION['user_id'] ?? 0);

// Consulta dados acadêmicos e progresso em tempo real no banco de dados
$relatorio_aluno = [];
if ($user_id > 0 && isset($conn)) {
    try {
        $relatorio_aluno = opus_obter_relatorio_progresso_completo($conn, $user_id);
    } catch (\Throwable $e) {
        $relatorio_aluno = [];
    }
}

// 1. TENTAR CHAMADA À API DA GROQ
$groq_key = obter_groq_api_key();

if (!empty($groq_key)) {
    $resposta_ia = chamar_api_groq($groq_key, $mensagem, $user_nome, $contexto, $relatorio_aluno, $historico);
    
    if ($resposta_ia !== false && !empty($resposta_ia)) {
        echo json_encode([
            'success' => true,
            'provedor' => 'groq',
            'resposta' => $resposta_ia
        ]);
        exit();
    }
}

// 2. MOTOR DE CONHECIMENTO LOCAL DO OPI (Fallback Inteligente com dados reais)
$resposta_local = responder_opi_local($mensagem, $user_nome, $contexto, $relatorio_aluno, !empty($groq_key));
echo json_encode([
    'success' => true,
    'provedor' => 'local',
    'resposta' => $resposta_local
]);
exit();
