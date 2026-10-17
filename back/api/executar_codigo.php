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
            'erro' => "Main.java: error: package system does not exist (Dica: 'System' deve começar com 'S' maiúsculo)",
            'erro_compilacao' => "error: package system does not exist\nsystem.out.println(...);\n^"
        ]);
        exit();
    }

    // 2. Verifica ausência de ponto e vírgula
    if (preg_match('/System\.out\.print(?:ln)?\s*\([^;)]*$/m', $codigo)) {
        echo json_encode([
            'sucesso' => false,
            'erro' => "Main.java: error: ';' expected no final da instrução",
            'erro_compilacao' => "error: ';' expected\nSystem.out.println(...)\n                      ^"
        ]);
        exit();
    }

    // 3. Mapeia variáveis declaradas e reatribuídas no código (int, double, float, long, boolean, String, char)
    $vars = [];
    if (preg_match_all('/(?:final\s+)?(?:(?:int|double|float|long|boolean|String|char|var)\s+)?([a-zA-Z_]\w*)\s*=\s*([^;]+);/', $codigo, $varMatches, PREG_SET_ORDER)) {
        foreach ($varMatches as $vm) {
            $vName = $vm[1];
            if ($vName === 'args') continue;
            $vExpr = trim($vm[2]);
            if (preg_match('/^"(.*)"$/s', $vExpr, $sm)) {
                $vars[$vName] = ['type' => 'string', 'val' => $sm[1]];
            } elseif (preg_match("/^'(.)'$/s", $vExpr, $cm)) {
                $vars[$vName] = ['type' => 'string', 'val' => $cm[1]];
            } elseif (strtolower($vExpr) === 'true' || strtolower($vExpr) === 'false') {
                $vars[$vName] = ['type' => 'bool', 'val' => (strtolower($vExpr) === 'true' ? 'true' : 'false')];
            } elseif (is_numeric($vExpr)) {
                $vars[$vName] = ['type' => 'num', 'val' => $vExpr + 0];
            } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*([+\-*\/])\s*(\d+(?:\.\d+)?)$/', $vExpr, $am)) {
                $a = $am[1] + 0;
                $b = $am[3] + 0;
                $op = $am[2];
                $res = ($op === '+') ? ($a + $b) : (($op === '-') ? ($a - $b) : (($op === '*') ? ($a * $b) : ($b != 0 ? $a / $b : 0)));
                $vars[$vName] = ['type' => 'num', 'val' => $res];
            } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(>|>=|<|<=|==|!=)\s*(\d+(?:\.\d+)?)$/', $vExpr, $bm)) {
                $a = $bm[1] + 0;
                $b = $bm[3] + 0;
                $op = $bm[2];
                $bRes = ($op === '>') ? ($a > $b) : (($op === '>=') ? ($a >= $b) : (($op === '<') ? ($a < $b) : (($op === '<=') ? ($a <= $b) : (($op === '==') ? ($a == $b) : ($a != $b)))));
                $vars[$vName] = ['type' => 'bool', 'val' => $bRes ? 'true' : 'false'];
            }
        }
    }

    // 4. Captura impressões System.out.println(...) ou System.out.print(...)
    if (preg_match_all('/System\.out\.(println|print)\s*\(\s*([^;]*?)\s*\)\s*;/s', $codigo, $matches, PREG_SET_ORDER)) {
        $saida_local = "";
        $erro_local = null;

        foreach ($matches as $m) {
            $metodo = $m[1];
            $conteudo = trim($m[2]);

            if ($conteudo === '') {
                if ($metodo === 'println') $saida_local .= "\n";
                continue;
            }

            if (preg_match('/^"(.*)"$/s', $conteudo, $strMatch)) {
                $saida_local .= $strMatch[1] . ($metodo === 'println' ? "\n" : "");
            } elseif (is_numeric($conteudo)) {
                $saida_local .= $conteudo . ($metodo === 'println' ? "\n" : "");
            } elseif (strtolower($conteudo) === 'true' || strtolower($conteudo) === 'false') {
                $saida_local .= strtolower($conteudo) . ($metodo === 'println' ? "\n" : "");
            } elseif (isset($vars[$conteudo])) {
                $saida_local .= $vars[$conteudo]['val'] . ($metodo === 'println' ? "\n" : "");
            } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*([+\-*\/])\s*(\d+(?:\.\d+)?)$/', $conteudo, $am)) {
                $a = $am[1] + 0;
                $b = $am[3] + 0;
                $op = $am[2];
                $res = ($op === '+') ? ($a + $b) : (($op === '-') ? ($a - $b) : (($op === '*') ? ($a * $b) : ($b != 0 ? $a / $b : 0)));
                $saida_local .= $res . ($metodo === 'println' ? "\n" : "");
            } elseif (preg_match('/^(\d+(?:\.\d+)?)\s*(>|>=|<|<=|==|!=)\s*(\d+(?:\.\d+)?)$/', $conteudo, $bm)) {
                $a = $bm[1] + 0;
                $b = $bm[3] + 0;
                $op = $bm[2];
                $bRes = ($op === '>') ? ($a > $b) : (($op === '>=') ? ($a >= $b) : (($op === '<') ? ($a < $b) : (($op === '<=') ? ($a <= $b) : (($op === '==') ? ($a == $b) : ($a != $b)))));
                $saida_local .= ($bRes ? 'true' : 'false') . ($metodo === 'println' ? "\n" : "");
            } elseif (strpos($conteudo, '+') !== false) {
                // Concatenação com +
                $partes = preg_split('/\s*\+\s*/', $conteudo);
                $linha_concat = '';
                foreach ($partes as $pt) {
                    $pt = trim($pt);
                    if (preg_match('/^"(.*)"$/s', $pt, $psm)) {
                        $linha_concat .= $psm[1];
                    } elseif (isset($vars[$pt])) {
                        $linha_concat .= $vars[$pt]['val'];
                    } elseif (is_numeric($pt)) {
                        $linha_concat .= $pt;
                    } else {
                        $erro_local = "Main.java: error: cannot find symbol '$pt'";
                        break 2;
                    }
                }
                $saida_local .= $linha_concat . ($metodo === 'println' ? "\n" : "");
            } else {
                $erro_local = "Main.java: error: cannot find symbol '$conteudo' (Dica: textos literais devem estar entre aspas duplas \"...\" ou verifique o nome da variável)";
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
            'saida' => rtrim($saida_local, "\r\n"),
            'program_output' => rtrim($saida_local, "\r\n"),
            'erro_compilacao' => '',
            'erro_execucao' => ''
        ]);
        exit();
    }

    echo json_encode([
        'sucesso' => false,
        'erro' => 'Não foi possível compilar o código. Verifique se a instrução System.out.println(...); está correta.',
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

