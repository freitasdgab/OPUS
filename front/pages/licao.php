<link rel="shortcut icon" href="../assets/img/logo.png">

<?php
// Toda a lógica de sessão, proteção de acesso e busca de dados no banco
// foi movida para back/licao_logic.php (mesma funcionalidade, apenas separada
// para deixar este arquivo focado na apresentação/HTML).
require_once '../../back/licao_logic.php';
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
    <link rel="stylesheet" href="../assets/css/licao.css">
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
                <img src="../assets/img/<?php echo htmlspecialchars($img_mascote_explicando); ?>" alt="Mascote do capítulo" class="opi-mascote-img opi-float-animation">
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
                <div class="progress-fill" id="progressFill" style="background: linear-gradient(90deg, <?php echo htmlspecialchars($cor_capitulo); ?>, <?php echo htmlspecialchars($cor_capitulo); ?>);"></div>
            </div>
            <span class="progress-text" id="progressText">0/<?php echo count($perguntas); ?></span>
        </div>
        
        <div class="quiz-grid-body"
             data-mascote-explicando="../assets/img/<?php echo htmlspecialchars($img_mascote_explicando); ?>"
             data-mascote-feliz="../assets/img/<?php echo htmlspecialchars($img_mascote_feliz); ?>"
             data-mascote-triste="../assets/img/<?php echo htmlspecialchars($img_mascote_triste); ?>"
             data-cor-capitulo="<?php echo htmlspecialchars($cor_capitulo); ?>">
            <!-- LADO ESQUERDO: MASCOTE REATIVO -->
            <div class="quiz-mascote-side">
                <div class="quiz-speech-bubble" id="quizSpeechBubble">
                    Olá! Vamos testar seus novos conhecimentos com algumas perguntas. Estou ansioso para ver suas respostas! 🤔
                </div>
                <div class="quiz-mascote-wrapper">
                    <img src="../assets/img/<?php echo htmlspecialchars($img_mascote_explicando); ?>" alt="Mascote do capítulo" class="opi-quiz-mascote opi-float-animation" id="opiQuizImg">
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
        <input type="hidden" name="attempt_token" value="<?php echo htmlspecialchars($attempt_token); ?>">
        <?php foreach ($perguntas as $p): ?>
            <input type="hidden" name="resposta[<?php echo $p['id']; ?>]" value="" id="resp_<?php echo $p['id']; ?>">
        <?php endforeach; ?>
    </form>

    <!-- CONFETTI -->
    <div class="confetti-container" id="confettiContainer"></div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/licao.js"></script>

</body>
</html>