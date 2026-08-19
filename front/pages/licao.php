<link rel="shortcut icon" href="../assets/img/logo.png">

<?php
session_start();
require_once '../../back/conexao.php';

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

// Divide o texto explicativo por parágrafos para gerar os slides
$paragrafos = preg_split('/\R{2,}/', trim($dados_licao['texto_explicativo']));
$codigo_exemplo = trim($dados_licao['codigo_exemplo']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dados_licao['titulo']); ?> - Opus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: #0d0d0f;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #bg-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .phase-container {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.98); }
        }

        .leaving-phase {
            animation: fadeOut 0.3s ease-in forwards;
        }

        /* ======================================= */
        /* FASE 1: SLIDES DE EXPLICAÇÃO           */
        /* ======================================= */
        .phase-header {
            padding: 20px 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(13, 13, 15, 0.8);
            backdrop-filter: blur(10px);
        }

        .btn-close-phase {
            color: #8e95a1;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
            font-size: 15px;
            font-family: 'Orbitron', sans-serif;
        }
        .btn-close-phase:hover { color: #fff; }

        .lesson-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
        }

        .phase-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 50px;
            padding: 40px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .mascote-container {
            flex: 1;
            max-width: 350px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .opi-mascote-img {
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0px 10px 20px rgba(26, 54, 202, 0.3));
        }

        /* Animações do Mascote */
        @keyframes opiFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        .opi-float-animation {
            animation: opiFloat 3.5s ease-in-out infinite;
        }

        @keyframes opiBounce {
            0%, 100% { transform: scale(1) translateY(0); }
            30% { transform: scale(1.08, 0.92) translateY(0); }
            50% { transform: scale(0.92, 1.15) translateY(-25px); }
            70% { transform: scale(1.04, 0.96) translateY(0); }
        }
        .opi-bounce-animation {
            animation: opiBounce 1.2s ease-in-out infinite;
        }

        @keyframes opiShake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px) rotate(-3deg); }
            40% { transform: translateX(6px) rotate(3deg); }
            60% { transform: translateX(-4px) rotate(-1deg); }
            80% { transform: translateX(3px) rotate(1deg); }
        }
        .opi-shake-animation {
            animation: opiShake 0.6s ease-in-out;
        }

        .explanation-card-wrapper {
            flex: 2;
            background: rgba(22, 26, 36, 0.7);
            border: 1px solid rgba(26, 54, 202, 0.2);
            border-radius: 24px;
            padding: 40px;
            min-height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4), inset 0 0 20px rgba(26, 54, 202, 0.05);
            position: relative;
            backdrop-filter: blur(10px);
        }

        .explanation-slide {
            display: none;
            flex-direction: column;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .explanation-slide.active {
            display: flex;
            opacity: 1;
        }

        .explanation-text-content {
            font-size: 1.15rem;
            line-height: 1.7;
            color: #d1d5e0;
        }

        /* Caixa de Código tipo IDE */
        .code-editor {
            background-color: #0e1118;
            border-radius: 12px;
            border: 1px solid rgba(26, 54, 202, 0.3);
            padding: 20px;
            font-family: 'Courier New', Courier, monospace;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 100%;
            margin-top: 15px;
            text-align: left;
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
            overflow-x: auto;
        }

        .phase-footer {
            padding: 20px 40px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(13, 13, 15, 0.8);
            backdrop-filter: blur(10px);
        }

        .btn-nav {
            padding: 12px 30px;
            border-radius: 12px;
            font-family: 'Orbitron', sans-serif;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 1px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #2a2d38;
            color: #8e95a1;
        }
        .btn-nav:hover:not(:disabled) {
            background: #3d4155;
            color: #fff;
            transform: translateY(-2px);
        }
        .btn-nav:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }
        .btn-nav.btn-next-slide {
            background: #1a36ca;
            color: #fff;
            box-shadow: 0 4px 15px rgba(26, 54, 202, 0.4);
        }
        .btn-nav.btn-next-slide:hover {
            background: #2a4cff;
            box-shadow: 0 4px 20px rgba(26, 54, 202, 0.6);
        }

        .slide-dots {
            display: flex;
            gap: 10px;
        }
        .dot-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #2a2d38;
            transition: all 0.3s;
        }
        .dot-indicator.active {
            background: #1a36ca;
            transform: scale(1.3);
            box-shadow: 0 0 8px #1a36ca;
        }


        /* ======================================= */
        /* FASE 2: QUIZ ESTILO DUOLINGO           */
        /* ======================================= */
        .quiz-progress-bar {
            padding: 24px 40px 0 40px;
            display: flex;
            align-items: center;
            gap: 20px;
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }

        .btn-close-quiz {
            color: #5a5e6b;
            font-size: 24px;
            text-decoration: none;
            transition: color 0.2s;
        }
        .btn-close-quiz:hover { color: #fff; }

        .progress-track {
            flex: 1;
            height: 16px;
            background: #2a2d38;
            border-radius: 999px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #58cc02, #4baf00);
            border-radius: 999px;
            width: 0%;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 8px;
            right: 8px;
            height: 5px;
            background: rgba(255,255,255,0.25);
            border-radius: 999px;
        }

        .progress-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 14px;
            color: #8e95a1;
            white-space: nowrap;
            min-width: 55px;
            text-align: right;
        }

        .quiz-grid-body {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            align-items: center;
            justify-content: center;
            gap: 40px;
            padding: 30px 40px;
            max-width: 1100px;
            margin: 0 auto;
            width: 100%;
        }

        .quiz-mascote-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .quiz-speech-bubble {
            position: relative;
            background: #1a1c24;
            border: 2px solid #2a2d38;
            border-radius: 20px;
            padding: 15px 25px;
            color: #fff;
            font-size: 1.05rem;
            max-width: 320px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            line-height: 1.4;
            transition: all 0.3s ease;
        }
        
        /* Setinha do balão */
        .quiz-speech-bubble::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 12px 12px 0;
            border-style: solid;
            border-color: #1a1c24 transparent;
            display: block;
            width: 0;
        }
        .quiz-speech-bubble::before {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 13px 13px 0;
            border-style: solid;
            border-color: #2a2d38 transparent;
            display: block;
            width: 0;
            z-index: -1;
        }

        .quiz-speech-bubble.bubble-correct {
            border-color: #58cc02;
            background: rgba(88, 204, 2, 0.1);
            color: #58cc02;
        }
        .quiz-speech-bubble.bubble-correct::after {
            border-color: rgba(88, 204, 2, 0.1) transparent;
        }
        .quiz-speech-bubble.bubble-correct::before {
            border-color: #58cc02 transparent;
        }

        .quiz-speech-bubble.bubble-wrong {
            border-color: #ff4b4b;
            background: rgba(255, 75, 75, 0.1);
            color: #ff4b4b;
        }
        .quiz-speech-bubble.bubble-wrong::after {
            border-color: rgba(255, 75, 75, 0.1) transparent;
        }
        .quiz-speech-bubble.bubble-wrong::before {
            border-color: #ff4b4b transparent;
        }

        .quiz-mascote-wrapper {
            width: 250px;
            height: 250px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .opi-quiz-mascote {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: drop-shadow(0px 10px 20px rgba(0,0,0,0.5));
        }

        .quiz-question-side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 100%;
        }

        .question-slide {
            display: none;
            flex-direction: column;
            width: 100%;
            animation: slideIn 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(50px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        .question-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            color: #5a5e6b;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .question-text {
            font-size: 1.4rem;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 25px;
            color: #fff;
        }

        .duo-options {
            display: flex;
            flex-direction: column;
            gap: 12px;
            width: 100%;
        }

        .duo-option {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 20px;
            border-radius: 16px;
            border: 2px solid #2a2d38;
            background: #1a1c24;
            cursor: pointer;
            transition: all 0.18s ease;
            user-select: none;
            position: relative;
            overflow: hidden;
        }

        .duo-option:hover:not(.disabled) {
            border-color: #3d4155;
            background: #1e2030;
            transform: scale(1.01);
        }

        .duo-option.selected {
            border-color: #4b8df8;
            background: rgba(75, 141, 248, 0.08);
        }

        .option-letter {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 2px solid #3d4155;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #8e95a1;
            flex-shrink: 0;
            transition: all 0.18s;
        }

        .duo-option.selected .option-letter {
            border-color: #4b8df8;
            background: #4b8df8;
            color: #fff;
        }

        .option-text {
            font-size: 15px;
            font-weight: 500;
            color: #d1d5e0;
            line-height: 1.4;
            flex: 1;
        }

        .duo-option.selected .option-text {
            color: #fff;
        }

        /* Feedback da alternativa */
        .duo-option.correct {
            border-color: #58cc02 !important;
            background: rgba(88, 204, 2, 0.1) !important;
        }
        .duo-option.correct .option-letter {
            border-color: #58cc02;
            background: #58cc02;
            color: #fff;
        }
        .duo-option.correct .option-text {
            color: #58cc02;
        }

        .duo-option.wrong {
            border-color: #ff4b4b !important;
            background: rgba(255, 75, 75, 0.1) !important;
        }
        .duo-option.wrong .option-letter {
            border-color: #ff4b4b;
            background: #ff4b4b;
            color: #fff;
        }
        .duo-option.wrong .option-text {
            color: #ff4b4b;
        }

        .duo-option.reveal-correct {
            border-color: #58cc02 !important;
            background: rgba(88, 204, 2, 0.06) !important;
        }
        .duo-option.reveal-correct .option-letter {
            border-color: #58cc02;
            color: #58cc02;
            background: transparent;
        }
        .duo-option.reveal-correct .option-text {
            color: #58cc02;
        }

        .duo-option.disabled {
            pointer-events: none;
            opacity: 0.5;
        }
        .duo-option.correct.disabled,
        .duo-option.wrong.disabled,
        .duo-option.reveal-correct.disabled {
            opacity: 1;
        }

        /* Barra de Ação Inferior do Quiz */
        .quiz-footer {
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 96px;
            border-top: 2px solid #2a2d38;
            background: #0d0d0f;
            width: 100%;
            transition: all 0.3s ease;
        }

        .quiz-footer.feedback-correct {
            background: rgba(88, 204, 2, 0.08);
            border-top-color: #58cc02;
        }

        .quiz-footer.feedback-wrong {
            background: rgba(255, 75, 75, 0.08);
            border-top-color: #ff4b4b;
        }

        .feedback-message {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 16px;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }

        .feedback-message.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .feedback-message .feedback-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .feedback-message.msg-correct { color: #58cc02; }
        .feedback-message.msg-correct .feedback-icon {
            background: #58cc02;
            color: #fff;
        }

        .feedback-message.msg-wrong { color: #ff4b4b; }
        .feedback-message.msg-wrong .feedback-icon {
            background: #ff4b4b;
            color: #fff;
        }

        .btn-quiz-action {
            padding: 14px 40px;
            border-radius: 16px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 0 rgba(0,0,0,0.3);
            position: relative;
            top: 0;
        }

        .btn-quiz-action:active {
            box-shadow: 0 1px 0 rgba(0,0,0,0.3);
            top: 3px;
        }

        .btn-quiz-action.btn-check {
            background: #1a36ca;
            color: #fff;
            box-shadow: 0 4px 0 #102280;
        }
        .btn-quiz-action.btn-check:hover:not(:disabled) {
            background: #2a4cff;
        }
        .btn-quiz-action.btn-check:disabled {
            background: #2a2d38;
            color: #5a5e6b;
            box-shadow: 0 4px 0 #1e2028;
            cursor: not-allowed;
        }

        .btn-quiz-action.btn-continue-correct {
            background: #58cc02;
            color: #fff;
            box-shadow: 0 4px 0 #46a302;
        }
        .btn-quiz-action.btn-continue-correct:hover {
            background: #65de08;
        }

        .btn-quiz-action.btn-continue-wrong {
            background: #ff4b4b;
            color: #fff;
            box-shadow: 0 4px 0 #cc3c3c;
        }
        .btn-quiz-action.btn-continue-wrong:hover {
            background: #ff6060;
        }

        /* ============================== */
        /* CONFETES DE VITÓRIA            */
        /* ============================== */
        .confetti-container {
            position: fixed;
            top: 0;
            left: 50%;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 999;
        }

        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
        }

        @keyframes confettiFall {
            0% { transform: translateY(-20px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }

        /* Formulário oculto */
        #quiz-form-hidden {
            display: none;
        }

        /* Responsividade básica */
        @media (max-width: 900px) {
            .phase-body {
                flex-direction: column;
                gap: 20px;
                padding: 20px;
            }
            .mascote-container {
                max-width: 180px;
            }
            .explanation-card-wrapper {
                padding: 20px;
                min-height: 250px;
            }
            .quiz-grid-body {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 20px;
            }
            .quiz-mascote-wrapper {
                width: 150px;
                height: 150px;
            }
            .quiz-speech-bubble {
                font-size: 0.95rem;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>

    <canvas id="bg-canvas"></canvas>

    <!-- FASE 1: SLIDES DE EXPLICAÇÃO -->
    <div class="phase-container" id="explanationPhase">
        <div class="phase-header">
            <a href="dashboard.php" class="btn-close-phase"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <span class="lesson-title"><?php echo htmlspecialchars($dados_licao['titulo']); ?></span>
            <div style="width: 80px;"></div> <!-- Espaçador para centralizar título -->
        </div>
        
        <div class="phase-body">
            <div class="mascote-container">
                <img src="../assets/img/opi 2.png" alt="OPI" class="opi-mascote-img opi-float-animation">
            </div>
            
            <div class="explanation-card-wrapper">
                <?php foreach ($paragrafos as $idx => $p): ?>
                    <div class="explanation-slide <?php echo $idx === 0 ? 'active' : ''; ?>" data-slide-index="<?php echo $idx; ?>">
                        <div class="explanation-text-content">
                            <?php echo nl2br(htmlspecialchars($p)); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (!empty($codigo_exemplo)): ?>
                    <div class="explanation-slide" data-slide-index="<?php echo count($paragrafos); ?>">
                        <div class="explanation-text-content">
                            <p style="margin-bottom: 10px; font-weight: 600; color: #4d66f5;">Aqui está um código prático em Java para exemplo:</p>
                            <div class="code-editor">
                                <div class="code-header">
                                    <div class="dot r"></div>
                                    <div class="dot y"></div>
                                    <div class="dot g"></div>
                                </div>
                                <pre><code><?php echo htmlspecialchars($codigo_exemplo); ?></code></pre>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="phase-footer">
            <button class="btn-nav" id="btnPrevSlide" onclick="changeSlide(-1)" disabled>ANTERIOR</button>
            <div class="slide-dots" id="slideDots"></div>
            <button class="btn-nav btn-next-slide" id="btnNextSlide" onclick="changeSlide(1)">PRÓXIMO</button>
        </div>
    </div>

    <!-- FASE 2: QUIZ ESTILO DUOLINGO -->
    <div class="phase-container" id="quizPhase" style="display: none;">
        <!-- BARRA DE PROGRESSO -->
        <div class="quiz-progress-bar">
            <a href="dashboard.php" class="btn-close-quiz"><i class="fa-solid fa-xmark"></i></a>
            <div class="progress-track">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <span class="progress-text" id="progressText">0/<?php echo count($perguntas); ?></span>
        </div>
        
        <div class="quiz-grid-body">
            <!-- LADO ESQUERDO: MASCOTE REATIVO -->
            <div class="quiz-mascote-side">
                <div class="quiz-speech-bubble" id="quizSpeechBubble">
                    Olá! Vamos testar seus novos conhecimentos com algumas perguntas. Estou ansioso para ver suas respostas! 🤔
                </div>
                <div class="quiz-mascote-wrapper">
                    <img src="../img/acertou_algumas.png" alt="OPI" class="opi-quiz-mascote opi-float-animation" id="opiQuizImg">
                </div>
            </div>
            
            <!-- LADO DIREITO: PERGUNTA E ALTERNATIVAS -->
            <div class="quiz-question-side">
                <?php foreach ($perguntas as $index => $p): $q_num = $index + 1; ?>
                    <div class="question-slide" 
                         data-index="<?php echo $index; ?>" 
                         data-correct="<?php echo $p['alternativa_correta']; ?>"
                         data-pergunta-id="<?php echo $p['id']; ?>"
                         style="display: <?php echo $index === 0 ? 'flex' : 'none'; ?>;">
                        
                        <span class="question-number">Pergunta <?php echo $q_num; ?> de <?php echo count($perguntas); ?></span>
                        <h2 class="question-text"><?php echo htmlspecialchars($p['pergunta_texto']); ?></h2>
                        
                        <div class="duo-options">
                            <div class="duo-option" data-value="A" onclick="selectOption(this)">
                                <span class="option-letter">A</span>
                                <span class="option-text"><?php echo htmlspecialchars($p['alternativa_a']); ?></span>
                            </div>
                            <div class="duo-option" data-value="B" onclick="selectOption(this)">
                                <span class="option-letter">B</span>
                                <span class="option-text"><?php echo htmlspecialchars($p['alternativa_b']); ?></span>
                            </div>
                            <div class="duo-option" data-value="C" onclick="selectOption(this)">
                                <span class="option-letter">C</span>
                                <span class="option-text"><?php echo htmlspecialchars($p['alternativa_c']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- BARRA INFERIOR (VERIFICAR / CONTINUAR) -->
        <div class="quiz-footer" id="quizFooter">
            <div class="feedback-message" id="feedbackMsg"></div>
            <button class="btn-quiz-action btn-check" id="btnAction" onclick="handleAction()" disabled>
                VERIFICAR
            </button>
        </div>
    </div>

    <!-- FORMULÁRIO HIDDEN PARA ENVIAR RESULTADO -->
    <form action="resultado.php" method="POST" id="quiz-form-hidden">
        <input type="hidden" name="licao_id" value="<?php echo $licao_id; ?>">
        <input type="hidden" name="acertos" id="input_acertos" value="0">
        <input type="hidden" name="cap" value="<?php echo $unidade_atual; ?>">
        <input type="hidden" name="licao" value="<?php echo $licao_atual; ?>">
        <?php foreach ($perguntas as $p): ?>
            <input type="hidden" name="resposta[<?php echo $p['id']; ?>]" value="" id="resp_<?php echo $p['id']; ?>">
        <?php endforeach; ?>
    </form>

    <!-- CONFETTI -->
    <div class="confetti-container" id="confettiContainer"></div>

    <script src="../assets/js/script.js"></script>
    <script>
    // ====================================
    // CONTROLE DOS SLIDES DE EXPLICAÇÃO
    // ====================================
    let currentSlide = 0;
    const slidesExpl = document.querySelectorAll('.explanation-slide');
    const totalSlides = slidesExpl.length;
    const dotsContainer = document.getElementById('slideDots');
    const btnPrev = document.getElementById('btnPrevSlide');
    const btnNext = document.getElementById('btnNextSlide');

    // Inicializa os dots
    function initDots() {
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = `dot-indicator ${i === 0 ? 'active' : ''}`;
            dot.addEventListener('click', () => showSlide(i));
            dotsContainer.appendChild(dot);
        }
    }

    function showSlide(index) {
        if (index < 0 || index >= totalSlides) return;
        
        slidesExpl[currentSlide].classList.remove('active');
        currentSlide = index;
        slidesExpl[currentSlide].classList.add('active');

        // Atualizar dots
        const dots = document.querySelectorAll('.dot-indicator');
        dots.forEach((dot, idx) => {
            if (idx === currentSlide) dot.classList.add('active');
            else dot.classList.remove('active');
        });

        // Configurar botões
        btnPrev.disabled = currentSlide === 0;
        
        if (currentSlide === totalSlides - 1) {
            btnNext.textContent = 'COMEÇAR EXERCÍCIOS';
            btnNext.className = 'btn-nav btn-next-slide';
        } else {
            btnNext.textContent = 'PRÓXIMO';
            btnNext.className = 'btn-nav btn-next-slide';
        }
    }

    function changeSlide(direction) {
        if (currentSlide === totalSlides - 1 && direction === 1) {
            startQuizPhase();
            return;
        }
        showSlide(currentSlide + direction);
    }

    function startQuizPhase() {
        const explPhase = document.getElementById('explanationPhase');
        const quizPhase = document.getElementById('quizPhase');
        
        explPhase.classList.add('leaving-phase');
        setTimeout(() => {
            explPhase.style.display = 'none';
            quizPhase.style.display = 'flex';
            quizPhase.style.opacity = 0;
            setTimeout(() => {
                quizPhase.style.opacity = 1;
                quizPhase.style.transition = 'opacity 0.4s ease';
            }, 50);
        }, 300);
    }

    initDots();
    showSlide(0);


    // ====================================
    // LÓGICA DO QUIZ ESTILO DUOLINGO
    // ====================================
    const slidesQuiz = document.querySelectorAll('.question-slide');
    const totalQuestions = slidesQuiz.length;
    let currentIndex = 0;
    let totalAcertos = 0;
    let state = 'selecting'; // 'selecting' | 'checked'
    let selectedValue = null;

    const opiQuizImg = document.getElementById('opiQuizImg');
    const quizSpeechBubble = document.getElementById('quizSpeechBubble');

    // Atualiza a barra de progresso do quiz
    function updateProgress(answeredCount) {
        const fill = document.getElementById('progressFill');
        const text = document.getElementById('progressText');
        const pct = (answeredCount / totalQuestions) * 100;
        fill.style.width = pct + '%';
        text.textContent = answeredCount + '/' + totalQuestions;
    }

    // Selecionar alternativa
    function selectOption(el) {
        if (state === 'checked') return;

        const slide = el.closest('.question-slide');
        const options = slide.querySelectorAll('.duo-option');

        // Remove seleção anterior
        options.forEach(opt => opt.classList.remove('selected'));

        // Seleciona a nova
        el.classList.add('selected');
        selectedValue = el.getAttribute('data-value');

        // Habilita o botão
        const btn = document.getElementById('btnAction');
        btn.disabled = false;
    }

    // Ação do botão (VERIFICAR ou CONTINUAR)
    function handleAction() {
        if (state === 'selecting') {
            verificar();
        } else {
            continuar();
        }
    }

    // Verificar resposta
    function verificar() {
        if (!selectedValue) return;

        const slide = slidesQuiz[currentIndex];
        const correct = slide.getAttribute('data-correct').trim().toUpperCase();
        const perguntaId = slide.getAttribute('data-pergunta-id');
        const options = slide.querySelectorAll('.duo-option');
        const footer = document.getElementById('quizFooter');
        const feedbackMsg = document.getElementById('feedbackMsg');
        const btn = document.getElementById('btnAction');

        const isCorrect = selectedValue === correct;

        // Salvar resposta no form hidden
        document.getElementById('resp_' + perguntaId).value = selectedValue;

        // Desabilitar todas as opções
        options.forEach(opt => {
            opt.classList.add('disabled');
            if (opt.getAttribute('data-value') === correct && !isCorrect) {
                opt.classList.add('reveal-correct');
            }
        });

        // Marcar a selecionada
        const selectedEl = slide.querySelector('.duo-option.selected');

        if (isCorrect) {
            totalAcertos++;
            selectedEl.classList.add('correct');

            // Mascote reativo: feliz com comemoração e bounce
            opiQuizImg.src = "../img/acertoutudo.png";
            opiQuizImg.className = "opi-quiz-mascote opi-bounce-animation";

            const acertoFrases = [
                "Excelente! Você acertou em cheio! 🚀",
                "Sensacional! Resposta correta! 💎",
                "Perfeito! Seu conhecimento em Java está afiado! 🎉"
            ];
            quizSpeechBubble.textContent = acertoFrases[Math.floor(Math.random() * acertoFrases.length)];
            quizSpeechBubble.className = "quiz-speech-bubble bubble-correct";

            // Footer verde
            footer.className = 'quiz-footer feedback-correct';
            feedbackMsg.className = 'feedback-message msg-correct visible';
            feedbackMsg.innerHTML = '<div class="feedback-icon"><i class="fa-solid fa-check"></i></div><div><strong>Excelente!</strong><br><span style="font-weight:400;font-size:13px;opacity:0.85">Resposta correta!</span></div>';

            // Botão continuar verde
            btn.className = 'btn-quiz-action btn-continue-correct';
            btn.textContent = 'CONTINUAR';

            // Mini confete
            launchConfetti();
        } else {
            selectedEl.classList.add('wrong');

            // Mascote reativo: triste com shake
            opiQuizImg.src = "../img/errou_tudo.png";
            opiQuizImg.className = "opi-quiz-mascote opi-shake-animation";

            quizSpeechBubble.textContent = "Oops! Não foi dessa vez... Mas errar faz parte do aprendizado! 💡";
            quizSpeechBubble.className = "quiz-speech-bubble bubble-wrong";

            // Footer vermelho
            footer.className = 'quiz-footer feedback-wrong';
            feedbackMsg.className = 'feedback-message msg-wrong visible';
            
            // Encontrar texto da alternativa correta
            let correctText = '';
            options.forEach(opt => {
                if (opt.getAttribute('data-value') === correct) {
                    correctText = opt.querySelector('.option-text').textContent;
                }
            });
            feedbackMsg.innerHTML = '<div class="feedback-icon"><i class="fa-solid fa-xmark"></i></div><div><strong>Resposta incorreta</strong><br><span style="font-weight:400;font-size:13px;opacity:0.85">Correta: ' + correctText + '</span></div>';

            // Botão continuar vermelho
            btn.className = 'btn-quiz-action btn-continue-wrong';
            btn.textContent = 'CONTINUAR';
        }

        state = 'checked';
    }

    // Continuar para próxima pergunta
    function continuar() {
        const footer = document.getElementById('quizFooter');
        const feedbackMsg = document.getElementById('feedbackMsg');
        const btn = document.getElementById('btnAction');

        // Atualizar progresso
        updateProgress(currentIndex + 1);

        // Reset do Mascote para aguardando/pensando
        opiQuizImg.src = "../img/acertou_algumas.png";
        opiQuizImg.className = "opi-quiz-mascote opi-float-animation";
        
        const pensandoFrases = [
            "Estou preparando o próximo desafio... 🤔",
            "Vamos para a próxima! Mantenha o foco! 💪",
            "Pronto para a próxima pergunta? Vamos lá! 🔍"
        ];
        quizSpeechBubble.textContent = pensandoFrases[Math.floor(Math.random() * pensandoFrases.length)];
        quizSpeechBubble.className = "quiz-speech-bubble";

        // Reset footer
        footer.className = 'quiz-footer';
        feedbackMsg.className = 'feedback-message';
        feedbackMsg.innerHTML = '';

        // Se era a última pergunta, submeter form
        if (currentIndex >= totalQuestions - 1) {
            document.getElementById('input_acertos').value = totalAcertos;
            document.getElementById('quiz-form-hidden').submit();
            return;
        }

        // Animar saída do slide atual
        const currentSlideEl = slidesQuiz[currentIndex];
        currentSlideEl.style.display = 'none';

        currentIndex++;
        selectedValue = null;
        state = 'selecting';

        // Mostrar próxima
        slidesQuiz[currentIndex].style.display = 'flex';

        // Reset botão
        btn.className = 'btn-quiz-action btn-check';
        btn.textContent = 'VERIFICAR';
        btn.disabled = true;
    }

    // Confete animado
    function launchConfetti() {
        const container = document.getElementById('confettiContainer');
        const colors = ['#58cc02', '#4baf00', '#ffd900', '#ff9600', '#4b8df8', '#ce82ff'];
        
        for (let i = 0; i < 30; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = (Math.random() * 50 + 25) + '%';
            piece.style.top = '-10px';
            piece.style.width = (Math.random() * 8 + 5) + 'px';
            piece.style.height = (Math.random() * 8 + 5) + 'px';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            piece.style.animation = `confettiFall ${Math.random() * 2 + 1.5}s ease-out ${Math.random() * 0.3}s forwards`;
            container.appendChild(piece);

            // Limpar após animação
            setTimeout(() => piece.remove(), 3000);
        }
    }

    // Atalho de teclado: Enter para verificar/continuar
    document.addEventListener('keydown', function(e) {
        // Ignora atalhos de quiz se estiver na fase de explicação
        const explPhase = document.getElementById('explanationPhase');
        if (explPhase.style.display !== 'none') {
            if (e.key === 'ArrowRight' || e.key === 'Enter') {
                changeSlide(1);
            } else if (e.key === 'ArrowLeft') {
                changeSlide(-1);
            }
            return;
        }

        if (e.key === 'Enter') {
            const btn = document.getElementById('btnAction');
            if (!btn.disabled) {
                handleAction();
            }
        }
        // Atalhos 1, 2, 3 para selecionar alternativas
        if (state === 'selecting' && ['1', '2', '3'].includes(e.key)) {
            const slide = slidesQuiz[currentIndex];
            const options = slide.querySelectorAll('.duo-option');
            const idx = parseInt(e.key) - 1;
            if (options[idx]) {
                selectOption(options[idx]);
            }
        }
    });

    // Inicializar progresso
    updateProgress(0);
    </script>

</body>
</html>