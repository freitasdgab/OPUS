<?php
/**
 * OPUS - Configuração de Inteligência Artificial (Groq / Opi IA)
 * 
 * Você pode definir sua chave da Groq diretamente neste arquivo ou
 * através de um arquivo .env na raiz do projeto (c:\xampp\htdocs\OPUS\.env).
 * Obtenha sua chave gratuita em: https://console.groq.com/keys
 */

// Chave da API da Groq (deixe vazio para carregar automaticamente do .env ou ambiente)
// Exemplo: 'gsk_abcdef1234567890...'
if (!defined('GROQ_API_KEY_CONFIG')) {
    define('GROQ_API_KEY_CONFIG', '');
}

// Modelo principal e modelo alternativo de fallback da Groq
if (!defined('GROQ_MODEL_PRIMARY')) {
    define('GROQ_MODEL_PRIMARY', 'llama-3.3-70b-versatile');
}
if (!defined('GROQ_MODEL_FALLBACK')) {
    define('GROQ_MODEL_FALLBACK', 'llama-3.1-8b-instant');
}

// URL da API da Groq (compatível com a especificação OpenAI Chat Completions)
if (!defined('GROQ_API_URL')) {
    define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
}

/**
 * Retorna a chave da API da Groq ativa procurando nas fontes:
 * 1. Constante GROQ_API_KEY_CONFIG definida acima
 * 2. Arquivo .env na raiz do projeto (OPUS/.env)
 * 3. Variáveis de ambiente do servidor / PHP (getenv, $_ENV, $_SERVER)
 */
function obter_groq_api_key(): string {
    // 1. Constante direta neste arquivo
    if (defined('GROQ_API_KEY_CONFIG') && !empty(trim(GROQ_API_KEY_CONFIG))) {
        return trim(GROQ_API_KEY_CONFIG);
    }

    // 2. Variáveis de ambiente do PHP / Apache
    $envVar = getenv('GROQ_API_KEY') ?: ($_ENV['GROQ_API_KEY'] ?? ($_SERVER['GROQ_API_KEY'] ?? ''));
    if (!empty(trim((string)$envVar))) {
        return trim((string)$envVar);
    }

    // 3. Arquivo .env na raiz
    $caminhos_env = [
        __DIR__ . '/../.env',
        __DIR__ . '/.env'
    ];

    foreach ($caminhos_env as $envFile) {
        if (file_exists($envFile) && is_readable($envFile)) {
            $linhas = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($linhas !== false) {
                foreach ($linhas as $linha) {
                    $linha = trim($linha);
                    if ($linha === '' || strpos($linha, '#') === 0) {
                        continue;
                    }
                    if (strpos($linha, 'GROQ_API_KEY=') === 0) {
                        $valor = trim(substr($linha, strlen('GROQ_API_KEY=')));
                        $valor = trim($valor, "\"' \t\n\r\0\x0B");
                        if (!empty($valor)) {
                            return $valor;
                        }
                    }
                }
            }
        }
    }

    return '';
}
