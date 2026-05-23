<?php
session_start();
require_once '../back/conexao.php';

// Proteção de login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dados_licao['titulo']); ?> - Opus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: #0d0d0f;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        /* CONTAINER DIVIDIDO AO MEIO */
        .workspace-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }

        /* COLUNA DA ESQUERDA: TEORIA E CÓDIGO */
        .theory-column {
            background-color: #0d0d0f;
            padding: 40px;
            overflow-y: auto;
            border-right: 2px solid rgba(255, 255, 255, 0.05);
        }

        .btn-voltar {
            color: #8e95a1;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            transition: 0.3s;
        }
        .btn-voltar:hover { color: #fff; }

        .theory-column h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 24px;
            margin-bottom: 20px;
            color: #fff;
        }

        .text-box {
            color: #b3b9c4;
            line-height: 1.6;
            font-size: 15px;
            margin-bottom: 30px;
        }

        /* CAIXA DE CÓDIGO TIPO IDE */
        .code-editor {
            background-color: #161a24;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .code-header {
            display: flex;
            gap: 6px;
            margin-bottom: 15px;
        }
        .dot { width: 12px; height: 12px; border-radius: 50%; }
        .dot.r { background: #ff5f56; }
        .dot.y { background: #ffbd2e; }
        .dot.g { background: #27c93f; }

        pre {
            color: #a7b7e6;
            font-size: 14px;
            white-space: pre-wrap;
        }

        /* COLUNA DA DIREITA: QUIZ */
        .quiz-column {
            background-color: #121116;
            padding: 40px;
            overflow-y: auto;
        }

        .quiz-column h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 20px;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
        }

        .question-box {
            background: #161a24;
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .question-box p {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 15px;
        }

        .options-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .option-label {
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 12px 15px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 14px;
            transition: 0.2s;
        }
        .option-label:hover {
            background: rgba(26, 54, 202, 0.1);
            border-color: #1a36ca;
        }

        .option-label input[type="radio"] {
            accent-color: #1a36ca;
            width: 18px;
            height: 18px;
        }

        .btn-enviar {
            background: #1a36ca;
            color: #fff;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 900;
            cursor: pointer;
            letter-spacing: 2px;
            box-shadow: 0 0 15px rgba(26, 54, 202, 0.4);
            transition: 0.3s;
        }
        .btn-enviar:hover {
            background: #2b4ae3;
            box-shadow: 0 0 25px #1a36ca;
        }
    </style>
</head>
<body>

    <div class="workspace-container">
        
        <div class="theory-column">
            <a href="dashboard.php" class="btn-voltar"><i class="fa-solid fa-arrow-left"></i> Voltar ao Painel</a>
            <h1><?php echo htmlspecialchars($dados_licao['titulo']); ?></h1>
            <div class="text-box">
                <?php echo nl2br(htmlspecialchars($dados_licao['texto_explicativo'])); ?>
            </div>

            <div class="code-editor">
                <div class="code-header">
                    <div class="dot r"></div>
                    <div class="dot y"></div>
                    <div class="dot g"></div>
                </div>
                <pre><code><?php echo htmlspecialchars($dados_licao['codigo_exemplo']); ?></code></pre>
            </div>
        </div>

        <div class="quiz-column">
            <h2>DESAFIO DE FIXAÇÃO</h2>

            <form action="../back/validar_respostas.php" method="POST">
                <input type="hidden" name="licao_id" value="<?php echo $licao_id; ?>">

                <?php foreach ($perguntas as $index => $p): $q_num = $index + 1; ?>
                    <div class="question-box">
                        <p><?php echo $q_num . ". " . htmlspecialchars($p['pergunta_texto']); ?></p>
                        
                        <div class="options-group">
                            <label class="option-label">
                                <input type="radio" name="resposta[<?php echo $p['id']; ?>]" value="A" required>
                                <span><?php echo htmlspecialchars($p['alternativa_a']); ?></span>
                            </label>
                            
                            <label class="option-label">
                                <input type="radio" name="resposta[<?php echo $p['id']; ?>]" value="B">
                                <span><?php echo htmlspecialchars($p['alternativa_b']); ?></span>
                            </label>
                            
                            <label class="option-label">
                                <input type="radio" name="resposta[<?php echo $p['id']; ?>]" value="C">
                                <span><?php echo htmlspecialchars($p['alternativa_c']); ?></span>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn-enviar">ENVIAR RESPOSTAS</button>
            </form>
        </div>

    </div>

</body>
</html>