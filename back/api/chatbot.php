<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/chatbot_logic.php';

$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true) ?? [];

// Endpoint de diagnóstico e status da IA (Multi-LLM Cloud + Consultas Híbridas Web/DB)
if (isset($_GET['status']) || ($input['acao'] ?? '') === 'status') {
    $chave = obter_groq_api_key();
    echo json_encode([
        'success' => true,
        'configurado' => true,
        'groq_configurado' => !empty($chave),
        'provedor' => !empty($chave) ? 'Groq Cloud + Multi-LLM + RAG Web/DB' : 'Motor LLM Generativo Cloud (Multi-Provedor + RAG Web/DB em Tempo Real)',
        'comandos' => ['/browser', '/goal', '/boost'],
        'modelo_primario' => GROQ_MODEL_PRIMARY,
        'modelo_fallback' => GROQ_MODEL_FALLBACK,
        'chave_mascarada' => !empty($chave) ? substr($chave, 0, 7) . '...' . substr($chave, -4) : 'Cloud Multi-LLM Ativo'
    ]);
    exit();
}

$mensagem = trim($input['mensagem'] ?? ($_GET['mensagem'] ?? ($_GET['q'] ?? '')));
$contexto = trim($input['contexto'] ?? '');
$historico = is_array($input['historico'] ?? null) ? $input['historico'] : [];

if (empty($mensagem)) {
    echo json_encode([
        'success' => false, 
        'resposta' => 'Por favor, envie uma mensagem ou comando (/browser, /goal, /boost) para o Opi!'
    ]);
    exit();
}

$user_nome = $_SESSION['user_nome'] ?? 'Estudante';
$user_id   = (int) ($_SESSION['user_id'] ?? 0);

// Se não houver sessão ativa (ex: chamada direta de teste), seleciona o primeiro usuário ativo como referência de consulta
if ($user_id <= 0 && isset($conn)) {
    try {
        $resDef = $conn->query("SELECT id, nome FROM usuarios ORDER BY xp DESC LIMIT 1");
        if ($resDef && ($rowDef = $resDef->fetch_assoc())) {
            $user_id = (int) $rowDef['id'];
            $user_nome = $rowDef['nome'] ?: 'Estudante';
        }
    } catch (\Throwable $e) {}
}

// Consulta dados acadêmicos, metas, ranking, baús e progresso em tempo real no banco de dados
$relatorio_aluno = [];
if ($user_id > 0 && isset($conn)) {
    try {
        $relatorio_aluno = opus_obter_relatorio_progresso_completo($conn, $user_id);
    } catch (\Throwable $e) {
        $relatorio_aluno = [];
    }
}

// Identifica se o usuário acionou um comando especial (/browser, /goal, /boost)
$comando_detectado = null;
if (preg_match('/^\/(browser|goal|boost|busca|web|pesquisar|search|metas|objetivos|meta|turbo|impulso)\b/iu', $mensagem, $mCmd)) {
    $cmdRaw = strtolower($mCmd[1]);
    if (in_array($cmdRaw, ['browser', 'busca', 'web', 'pesquisar', 'search'], true)) $comando_detectado = '/browser';
    elseif (in_array($cmdRaw, ['goal', 'metas', 'objetivos', 'meta'], true)) $comando_detectado = '/goal';
    elseif (in_array($cmdRaw, ['boost', 'turbo', 'impulso'], true)) $comando_detectado = '/boost';
}

$groq_key = obter_groq_api_key();

// 1. COMANDOS ESPECIAIS (/goal, /boost, /browser) E CONSULTAS DIRETAS DO ALUNO NO BANCO OPUS
$resposta_plataforma = opus_resposta_rapida_plataforma($mensagem, $user_nome, $contexto, $relatorio_aluno, $conn ?? null, $historico);
if ($resposta_plataforma !== null && trim($resposta_plataforma) !== '') {
    echo json_encode([
        'success' => true,
        'provedor' => $comando_detectado === '/browser' ? 'llm_browser_rag' : 'opus_db_realtime',
        'comando' => $comando_detectado,
        'resposta' => $resposta_plataforma
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit();
}

// 2. MOTOR LLM GENERATIVO MULTI-PROVEDOR (Groq / Gemini / OpenAI / Cloud LLM Integrado + RAG Web/Tabelas/Notícias/DB)
$resposta_ia = chamar_api_groq($groq_key, $mensagem, $user_nome, $contexto, $relatorio_aluno, $historico, $conn ?? null);
if ($resposta_ia !== null && trim($resposta_ia) !== '') {
    echo json_encode([
        'success' => true,
        'provedor' => !empty($groq_key) ? 'groq' : 'cloud_llm_rag',
        'comando' => $comando_detectado,
        'resposta' => $resposta_ia
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit();
}

// 3. FALLBACK CONTINGENCIAL OFFLINE (Pesquisa Direta Web + Tabelas + Banco de Dados)
$resposta_local = responder_opi_local($mensagem, $user_nome, $contexto, $relatorio_aluno, !empty($groq_key), $conn ?? null, true, $historico);
echo json_encode([
    'success' => true,
    'provedor' => 'local_fallback',
    'comando' => $comando_detectado,
    'resposta' => $resposta_local
], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
exit();


