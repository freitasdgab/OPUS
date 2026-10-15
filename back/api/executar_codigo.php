<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

// Recebe os dados (JSON ou POST tradicional)
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true);

$codigo = '';
if (is_array($json_data) && isset($json_data['codigo'])) {
    $codigo = trim($json_data['codigo']);
} elseif (isset($_POST['codigo'])) {
    $codigo = trim($_POST['codigo']);
}

if (empty($codigo)) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Nenhum código foi enviado para execução.'
    ]);
    exit();
}

// Limitação estrita para Java:
// Se o usuário não definiu classe nem main (ex: apenas digitou System.out.println("...")):
// Envolvemos automaticamente em uma classe para facilitar o aprendizado.
$codigo_para_executar = $codigo;
$possui_class = (bool) preg_match('/\bclass\s+\w+/i', $codigo_para_executar);
$possui_main  = (bool) preg_match('/\bvoid\s+main\b/i', $codigo_para_executar);

if (!$possui_class && !$possui_main) {
    $codigo_para_executar = "class Main {\n    public static void main(String[] args) {\n        " . $codigo_para_executar . "\n    }\n}";
} else {
    // No Wandbox, a classe pública no arquivo padrão precisa ser 'class' sem 'public'
    // para evitar o erro de arquivo com nome diferente (Main vs prog.java).
    $codigo_para_executar = preg_replace('/\bpublic\s+class\b/', 'class', $codigo_para_executar);
}

// Monta o payload para a API de compilação Wandbox (OpenJDK 21)
$payload = json_encode([
    'compiler' => 'openjdk-jdk-21+35',
    'code' => $codigo_para_executar,
    'save' => false
]);

$ch = curl_init('https://wandbox.org/api/compile.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'User-Agent: OPUS-IDE/1.0'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// Se o servidor externo falhar ou der timeout, usa o compilador/analisador didático Java local como fallback resiliente
if ($response === false || $http_code !== 200) {
    // 1. Verifica se 'System' tem S maiúsculo
    if (preg_match('/\bsystem\.out\b/i', $codigo) && !preg_match('/\bSystem\.out\b/', $codigo)) {
        echo json_encode([
            'sucesso' => false,
            'erro' => "HelloWorld.java: error: package system does not exist (Dica: 'System' deve começar com 'S' maiúsculo)",
            'erro_compilacao' => "error: package system does not exist\nsystem.out.println(...);\n^"
        ]);
        exit();
    }

    // 2. Verifica ausência de ponto e vírgula
    if (preg_match('/System\.out\.println\s*\([^;)]*$/m', $codigo)) {
        echo json_encode([
            'sucesso' => false,
            'erro' => "HelloWorld.java: error: ';' expected no final da instrução",
            'erro_compilacao' => "error: ';' expected\nSystem.out.println(...)\n                      ^"
        ]);
        exit();
    }

    // 3. Captura impressões System.out.println(...)
    if (preg_match_all('/System\.out\.println\s*\(\s*([^;]+?)\s*\)\s*;/s', $codigo, $matches)) {
        $saida_local = "";
        $erro_local = null;
        foreach ($matches[1] as $conteudo) {
            $conteudo = trim($conteudo);
            if (preg_match('/^"(.*)"$/s', $conteudo, $strMatch)) {
                $saida_local .= $strMatch[1] . "\n";
            } elseif (is_numeric($conteudo)) {
                $saida_local .= $conteudo . "\n";
            } else {
                $erro_local = "HelloWorld.java: error: cannot find symbol '$conteudo' (Dica: textos literais devem estar entre aspas duplas \"...\")";
                break;
            }
        }

        if ($erro_local) {
            echo json_encode([
                'sucesso' => false,
                'erro' => $erro_local,
                'erro_compilacao' => $erro_local
            ]);
            exit();
        }

        echo json_encode([
            'sucesso' => true,
            'status' => '0',
            'saida' => rtrim($saida_local),
            'program_output' => rtrim($saida_local),
            'erro_compilacao' => '',
            'erro_execucao' => ''
        ]);
        exit();
    }

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Não foi possível compilar o código. Verifique se a instrução System.out.println("..."); está correta.',
        'detalhes' => $curl_error
    ]);
    exit();
}

$dados = json_decode($response, true);
if (!$dados) {
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Resposta inválida do compilador.'
    ]);
    exit();
}

$status = isset($dados['status']) ? (string)$dados['status'] : '0';
$compiler_error = trim($dados['compiler_error'] ?? '');
$program_error = trim($dados['program_error'] ?? '');
$program_output = $dados['program_output'] ?? '';

// Limpa o nome do arquivo interno "prog.java:" para ficar mais amigável como "Main.java:"
$compiler_error = str_replace('prog.java:', 'Main.java:', $compiler_error);
$program_error = str_replace('prog.java:', 'Main.java:', $program_error);

$sucesso = ($status === '0' && empty($compiler_error) && empty($program_error));

echo json_encode([
    'sucesso' => $sucesso,
    'status' => $status,
    'saida' => $program_output,
    'erro_compilacao' => $compiler_error,
    'erro_execucao' => $program_error,
    'erro_completo' => !empty($compiler_error) ? $compiler_error : $program_error
]);

