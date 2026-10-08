<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'resposta' => 'Você precisa estar logado para conversar com o Opi IA.']);
    exit();
}

require_once __DIR__ . '/conexao.php';

$input = json_decode(file_get_contents('php://input'), true);
$mensagem = trim($input['mensagem'] ?? '');
$contexto = trim($input['contexto'] ?? '');

if (empty($mensagem)) {
    echo json_encode(['success' => false, 'resposta' => 'Por favor, envie uma mensagem válida!']);
    exit();
}

$user_nome = $_SESSION['user_nome'] ?? 'dev';

// Você pode configurar sua chave de API do Groq, Gemini ou OpenAI aqui para que o chatbot responda qualquer dúvida usando IA real.
// Descomente a linha abaixo e coloque sua chave:
$api_key = '';

// Se não estiver definida acima, tenta buscar das variáveis de ambiente:
if (!isset($api_key) || empty($api_key)) {
    $api_key = getenv('OPENAI_API_KEY') ?: getenv('GEMINI_API_KEY');
}

if (!empty($api_key)) {
    // Integração remota se chave estiver disponível
    $resposta_ia = chamar_api_llm($api_key, $mensagem, $user_nome, $contexto);
    if (!empty($resposta_ia)) {
        echo json_encode(['success' => true, 'resposta' => $resposta_ia]);
        exit();
    }
}

// Resposta Inteligente Local via Motor de Conhecimento do Opi
$resposta = responder_opi_local($mensagem, $user_nome, $contexto);
echo json_encode(['success' => true, 'resposta' => $resposta]);
exit();


/**
 * Motor de Conhecimento Local do Opi IA
 */
function responder_opi_local($msg, $nome, $ctx) {
    $msg_lower = mb_strtolower($msg, 'UTF-8');

    // 1. DÚVIDAS SOBRE O SISTEMA OPUS
    if (preg_match('/(vida|coração|coracao|perdi vida|morri)/i', $msg_lower)) {
        return "❤️ **Como funcionam as Vidas no Opus:**\n\n" .
               "• Você começa com **3 Vidas (corações)**.\n" .
               "• Se errar todas as perguntas de uma lição (0 acertos), você perde **1 vida**.\n" .
               "• **Como recuperar:** Cada vida é regenerada automaticamente a cada **5 horas**, ou você pode resgatar vidas extras abrindo o **Baú de Recompensas** no meio de cada capítulo!";
    }

    if (preg_match('/(fogo|ofensiva|dias de fogo|sequência|sequencia)/i', $msg_lower)) {
        return "🔥 **Dias de Fogo (Ofensiva):**\n\n" .
               "• Os **Dias de Fogo** contam a sua sequência diária de estudos no Opus!\n" .
               "• Para não apagar a sua chama, você precisa concluir pelo menos **1 lição por dia**.\n" .
               "• Manter sua ofensiva ativa rende conquistas exclusivas e mostra seu foco na programação!";
    }

    if (preg_match('/(baú|bau|recompensa|caixa)/i', $msg_lower)) {
        return "🎁 **Baú de Recompensas:**\n\n" .
               "• Na 3ª lição de cada capítulo, você desbloqueia o **Baú de Recompensas** na sua trilha!\n" .
               "• Abrir o baú garante **Bônus de XP** e pode restaurar seus **Corações de Vida** se você tiver perdido algum.";
    }

    if (preg_match('/(xp|ponto|pontuação|pontuacao|ganhar xp)/i', $msg_lower)) {
        return "⚡ **Pontuação e XP no Opus:**\n\n" .
               "• **3/3 Acertos:** +150 XP (Perfeito!)\n" .
               "• **2/3 Acertos:** +100 XP\n" .
               "• **1/3 Acertos:** +50 XP\n" .
               "• **Tentativa:** +10 XP\n" .
               "O XP serve para subir no **Ranking semanal** e avançar de **Liga**!";
    }

    if (preg_match('/(liga|divisão|divisao|bronze|prata|ouro|diamante)/i', $msg_lower)) {
        return "🛡️ **Ligas e Divisões:**\n\n" .
               "• No Opus você compete nas ligas **Bronze, Prata, Ouro e Diamante**.\n" .
               "• Ganhe XP durante a semana para ficar entre os primeiros e subir para a próxima liga na virada de rodada!";
    }

    if (preg_match('/(ranking|placar|líder|lider)/i', $msg_lower)) {
        return "🏆 **Ranking Opus:**\n\n" .
               "• O **Ranking** exibe a classificação dos alunos com mais XP.\n" .
               "• Você pode acompanhar seu lugar em tempo real na aba *Ranking* do menu lateral.";
    }

    if (preg_match('/(troféu|trofeu|troféus|trofeus|conquista|conquistas|missão|missao|missões|missoes)/i', $msg_lower)) {
        return "🎖️ **Conquistas e Troféus:**\n\n" .
               "• Complete capítulos e metas (como alcançar 3 Dias de Fogo ou tirar nota máxima) para desbloquear **Troféus**.\n" .
               "• Você pode visualizar toda a sua galeria de troféus na aba **Missões** ou no seu **Perfil**!";
    }

    if (preg_match('/(dicionário|dicionario|termo|glossário|glossario)/i', $msg_lower)) {
        return "📚 **Dicionário Java:**\n\n" .
               "• Acesse a aba **Dicionário** no menu lateral para consultar a explicação simples e exemplos de código de qualquer palavra-chave do Java!";
    }

    if (preg_match('/(como funciona|o que é o opus|sobre o opus|ajuda)/i', $msg_lower)) {
        return "👋 Olá, **$nome**! Eu sou o **Opi**, seu mascote e assistente IA no Opus!\n\n" .
               "O **Opus** é uma plataforma interativa de aprendizado de programação em **Java**.\n\n" .
               "**Aqui você encontra:**\n" .
               "1. 📖 **Lições Práticas:** Explicação simples + desafios rápidos.\n" .
               "2. 🔥 **Dias de Fogo:** Mantenha sua rotina diária.\n" .
               "3. 🛡️ **Ligas e Ranking:** Dispute posições com XP.\n" .
               "4. 🎁 **Baú de Recompensas:** Ganhe bônus de XP e corações.\n" .
               "5. 💬 **Eu (Opi IA):** Estou aqui para tirar qualquer dúvida de código!";
    }

    // 2. DÚVIDAS DE PROGRAMAÇÃO EM JAVA
    if (preg_match('/(variável|variavel|variáveis|variaveis|int |double |string |boolean |char )/i', $msg_lower)) {
        return "💻 **Variáveis em Java:**\n\n" .
               "Variáveis são 'caixas' na memória para guardar dados.\n\n" .
               "**Principais tipos:**\n" .
               "• `int`: números inteiros (ex: `int idade = 20;`)\n" .
               "• `double`: números decimais (ex: `double preco = 19.90;`)\n" .
               "• `String`: textos (ex: `String nome = \"Opus\";`)\n" .
               "• `boolean`: verdadeiro ou falso (`true` / `false`)\n" .
               "• `char`: um único caractere (ex: `char letra = 'A';`)";
    }

    if (preg_match('/(if|else|condicional|decisão|decisao)/i', $msg_lower)) {
        return "🔀 **Estruturas Condicionais (if / else):**\n\n" .
               "Servem para tomar decisões no código com base em uma condição.\n\n" .
               "```java\n" .
               "int nota = 8;\n" .
               "if (nota >= 7) {\n" .
               "    System.out.println(\"Aprovado!\");\n" .
               "} else {\n" .
               "    System.out.println(\"Estude mais!\");\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(loop|laço|laco|for|while|repetição|repeticao)/i', $msg_lower)) {
        return "🔄 **Loops e Laços de Repetição em Java:**\n\n" .
               "Permitem repetir um bloco de código várias vezes.\n\n" .
               "**Exemplo com `for`:**\n" .
               "```java\n" .
               "for (int i = 1; i <= 5; i++) {\n" .
               "    System.out.println(\"Contagem: \" + i);\n" .
               "}\n" .
               "```\n\n" .
               "**Exemplo com `while`:**\n" .
               "```java\n" .
               "int i = 1;\n" .
               "while (i <= 5) {\n" .
               "    System.out.println(i);\n" .
               "    i++;\n" .
               "}\n" .
               "```";
    }

    if (preg_match('/(array|vetor|matriz|lista)/i', $msg_lower)) {
        return "📦 **Arrays (Vetores) em Java:**\n\n" .
               "Estruturas que armazenam múltiplos valores do mesmo tipo em uma só variável.\n\n" .
               "```java\n" .
               "String[] frutas = {\"Maçã\", \"Banana\", \"Laranja\"};\n" .
               "System.out.println(frutas[0]); // Imprime Maçã\n" .
               "```\n" .
               "Lembre-se: os índices em Java começam no **zero (0)**!";
    }

    if (preg_match('/(poo|objeto|classe|atributo|método|metodo|herança|heranca|polimorfismo)/i', $msg_lower)) {
        return "🏗️ **Orientação a Objetos (POO):**\n\n" .
               "• **Classe:** O 'molde' ou projeto (ex: Classe `Carro`).\n" .
               "• **Objeto:** A instância real criada a partir do molde (ex: `Carro meuCarro = new Carro();`).\n" .
               "• **Atributos:** As características (ex: `cor`, `modelo`).\n" .
               "• **Métodos:** As ações que ele pode realizar (ex: `acelerar()`, `frear()`).";
    }

    if (preg_match('/(print|println|system\.out)/i', $msg_lower)) {
        return "🖨️ **Saída de Dados em Java:**\n\n" .
               "Para exibir mensagens no terminal:\n" .
               "• `System.out.println(\"Texto\");` -> Imprime o texto e pula uma linha.\n" .
               "• `System.out.print(\"Texto\");` -> Imprime o texto na mesma linha.";
    }

    if (preg_match('/(erro|exception|bug|nullpointer|indexout)/i', $msg_lower)) {
        return "⚠️ **Dica de Depuração:**\n\n" .
               "• **NullPointerException:** Ocorre quando você tenta usar uma variável ou objeto que está nulo (`null`).\n" .
               "• **ArrayIndexOutOfBoundsException:** Ocorre quando você tenta acessar uma posição do Array que não existe (ex: posição 5 num array de tamanho 3).\n" .
               "• Dica: Verifique os nomes das variáveis, ponto e vírgula `;` no final das linhas e se as chaves `{}` estão fechadas!";
    }

    if (preg_match('/(oi|olá|ola|bom dia|boa tarde|boa noite|fala|eae|hey)/i', $msg_lower)) {
        return "👋 Olá, **$nome**! Que bom ter você aqui no Opus! 🚀\n\nComo posso te ajudar hoje? Você pode me perguntar sobre matérias de Java ou sobre como funciona o sistema (Vidas, Fogo, Baú, Ligas, etc.)!";
    }

    if (preg_match('/(obrigado|valeu|obrigada|vlw|tmj|tks|thanks)/i', $msg_lower)) {
        return "De nada, **$nome**! 🧡 Estou sempre aqui para apoiar seus estudos em Java. Continue firme e bons códigos! 💻🔥";
    }

    // RESPOSTA PADRÃO INTELIGENTE
    return "💡 **Opi IA responde:**\n\n" .
           "Entendi sua dúvida sobre *\"" . htmlspecialchars($msg) . "\"*!\n\n" .
           "Atualmente, estou operando no meu modo de respostas pré-definidas. Para que eu possa responder a **qualquer dúvida** usando Inteligência Artificial real, o desenvolvedor precisa configurar uma **Chave de API (Gemini ou OpenAI)** no arquivo `api_chatbot.php`.\n\n" .
           "Até lá, tente refrasear sua pergunta ou escolher um dos assuntos conhecidos (como variáveis, if/else, loops, arrays, classes, vidas, dias de fogo, etc.)!";
}

/**
 * Chamada Opcional para LLM Externa se houver API Key
 */
function chamar_api_llm($key, $prompt, $nome, $contexto) {
    // Se a chave começar com 'sk-' ou 'gsk_', não é Gemini.
    $is_gemini = (strpos($key, 'sk-') !== 0 && strpos($key, 'gsk_') !== 0);

    // Buscar informações reais do progresso do jogador
    global $conn;
    $user_id = $_SESSION['user_id'] ?? 0;
    $contexto_progresso = "";
    if ($user_id > 0 && isset($conn)) {
        require_once __DIR__ . '/jogador_status.php';
        $jogador = opus_sincronizar_jogador($conn, $user_id);
        $xp = $jogador['xp'];
        $vidas = $jogador['vidas'];
        $fogo = $jogador['dias_fogo'];
        $trofeus = $jogador['trofeus'];
        $contexto_progresso = " Progresso atual do aluno: $xp XP, $vidas Vidas restantes, Ofensiva de $fogo Dias de Fogo, e $trofeus Troféus.";
    }

    $system_prompt = "Você é o Opi, o mascote e assistente IA da plataforma Opus, uma plataforma de ensino de Java. Você é amigável, encorajador e ajuda o aluno '$nome' com dúvidas de programação ou sobre a plataforma Opus. Responda de forma direta, simples e concisa. Evite textos excessivamente longos a menos que o aluno peça uma explicação detalhada. Contexto da página: $contexto.$contexto_progresso";

    if ($is_gemini) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=' . $key;
        $data = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $system_prompt]
                ]
            ],
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $max_retries = 2;
        for ($i = 0; $i < $max_retries; $i++) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $json = json_decode($response, true);
                if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
                    return $json['candidates'][0]['content']['parts'][0]['text'];
                }
                
                if (isset($json['error']['code']) && $json['error']['code'] == 503 && $i < ($max_retries - 1)) {
                    sleep(1);
                    continue;
                }

                if (isset($json['error']) && isset($json['error']['message'])) {
                    return "⚠️ O servidor do Google retornou um erro temporário:\n\n" . $json['error']['message'] . "\n\nPor favor, tente perguntar novamente em alguns minutos.";
                }
            }
            break;
        }
    } else {
        // OpenAI ou Groq (mesmo formato de API)
        if (strpos($key, 'gsk_') === 0) {
            $url = 'https://api.groq.com/openai/v1/chat/completions';
            $model = 'openai/gpt-oss-20b'; // Modelo OpenAI OSS 20B via Groq
        } else {
            $url = 'https://api.openai.com/v1/chat/completions';
            $model = 'gpt-3.5-turbo';
        }

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $json = json_decode($response, true);
            if (isset($json['choices'][0]['message']['content'])) {
                return $json['choices'][0]['message']['content'];
            }
        }
    }

    return false;
}
?>
