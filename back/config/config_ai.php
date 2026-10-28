<?php
/**
 * OPUS - Configuração de Inteligência Artificial (Multi-LLM: Groq, Gemini, OpenAI & Cloud LLM Nativo)
 *
 * O Opi IA funciona 100% imediatamente com provedores de LLM generativos em nuvem integrados
 * (sem necessidade obrigatória de chave) e também aceita chaves opcionais da Groq, Google Gemini
 * ou OpenAI diretamente neste arquivo ou via .env na raiz do projeto (c:\xampp\htdocs\OPUS\.env).
 */

// Chaves opcionais de API (deixe vazio para carregar do .env ou usar o motor LLM Cloud integrado)
if (!defined('GROQ_API_KEY_CONFIG')) {
    define('GROQ_API_KEY_CONFIG', '');
}
if (!defined('GEMINI_API_KEY_CONFIG')) {
    define('GEMINI_API_KEY_CONFIG', '');
}
if (!defined('OPENAI_API_KEY_CONFIG')) {
    define('OPENAI_API_KEY_CONFIG', '');
}

// Modelos da Groq
if (!defined('GROQ_MODEL_PRIMARY')) {
    define('GROQ_MODEL_PRIMARY', 'llama-3.3-70b-versatile');
}
if (!defined('GROQ_MODEL_FALLBACK')) {
    define('GROQ_MODEL_FALLBACK', 'llama-3.1-8b-instant');
}

// Endpoints de API
if (!defined('GROQ_API_URL')) {
    define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
}
if (!defined('GEMINI_API_URL')) {
    define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
}
if (!defined('OPENAI_API_URL')) {
    define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
}

// Endpoints de LLM Generativos Gratuitos Integrados (Funcionam sem chave de API)
if (!defined('OPI_CLOUD_YQ_URL')) {
    define('OPI_CLOUD_YQ_URL', 'https://api.binjie.fun/api/generateStream');
}
if (!defined('OPI_CLOUD_POLLINATIONS_URL')) {
    define('OPI_CLOUD_POLLINATIONS_URL', 'https://text.pollinations.ai/openai');
}
if (!defined('OPI_CLOUD_DEEPAI_URL')) {
    define('OPI_CLOUD_DEEPAI_URL', 'https://api.deepai.org/hacking_is_a_serious_crime');
}

/**
 * Lê uma variável do ambiente ou do arquivo .env na raiz do projeto
 */
function opus_ler_env_var(string $nomeChave, string $constantePadrao = ''): string {
    if ($constantePadrao !== '' && defined($constantePadrao)) {
        $valConst = trim((string) constant($constantePadrao));
        if ($valConst !== '') {
            return $valConst;
        }
    }

    $envVar = getenv($nomeChave) ?: ($_ENV[$nomeChave] ?? ($_SERVER[$nomeChave] ?? ''));
    if (!empty(trim((string) $envVar))) {
        return trim((string) $envVar);
    }

    $caminhos_env = [
        __DIR__ . '/../../.env',
        __DIR__ . '/../.env'
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
                    if (strpos($linha, $nomeChave . '=') === 0) {
                        $valor = trim(substr($linha, strlen($nomeChave . '=')));
                        $valor = trim($valor, "\"' \t\n\r\0\x0B");
                        if ($valor !== '') {
                            return $valor;
                        }
                    }
                }
            }
        }
    }

    return '';
}

function obter_groq_api_key(): string {
    return opus_ler_env_var('GROQ_API_KEY', 'GROQ_API_KEY_CONFIG');
}

function obter_gemini_api_key(): string {
    return opus_ler_env_var('GEMINI_API_KEY', 'GEMINI_API_KEY_CONFIG');
}

function obter_openai_api_key(): string {
    return opus_ler_env_var('OPENAI_API_KEY', 'OPENAI_API_KEY_CONFIG');
}

