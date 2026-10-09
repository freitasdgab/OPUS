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
    <link rel="stylesheet" href="../assets/css/opus_alerta.css">
    <link rel="stylesheet" href="../assets/css/light-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>
<body class="licao-page">

    
    <!-- FASE 1: SLIDES DE EXPLICAÇÃO (Ocultado a pedido do usuário) -->
    <div class="phase-container" id="explanationPhase" style="display: none;">
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
    <div class="phase-container" id="quizPhase" style="display: flex;">
        <!-- BARRA DE PROGRESSO -->
        <div class="quiz-progress-bar">
            <a href="dashboard.php" class="btn-close-quiz" onclick="event.preventDefault(); abrirAvisoSaida();"><i class="fa-solid fa-xmark"></i></a>
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
                <?php foreach ($perguntas as $index => $p): $q_num = $index + 1; 
                    $tipo = $p['tipo'] ?? 'multipla_escolha';
                ?>
                    <div class="question-slide" 
                         data-index="<?php echo $index; ?>" 
                         data-tipo="<?php echo htmlspecialchars($tipo); ?>"
                         data-correct="<?php echo htmlspecialchars($p['alternativa_correta'] ?? ''); ?>"
                         data-correct-text="<?php echo htmlspecialchars($p['alternativa_' . strtolower($p['alternativa_correta'] ?? '')] ?? ''); ?>"
                         data-pergunta-id="<?php echo $p['id']; ?>"
                         style="display: <?php echo $index === 0 ? 'flex' : 'none'; ?>;">
                        
                        <span class="question-number">Pergunta <?php echo $q_num; ?> de <?php echo count($perguntas); ?> - <?php echo ucfirst(str_replace('_', ' ', $tipo)); ?></span>
                        <h2 class="question-text"><?php echo htmlspecialchars($p['pergunta_texto']); ?></h2>
                        
                        <?php if ($tipo === 'multipla_escolha'): ?>
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
                        
                        <?php elseif ($tipo === 'digitar_codigo'): ?>
                            <div class="typing-container">
                                <p style="font-size: 0.95rem; color: #a5a5ac; margin-bottom: 15px;"><i class="fa-solid fa-terminal"></i> Terminal: Digite o código para resolver o problema:</p>
                                <div class="code-editor" style="margin-top: 0; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                                    <div class="code-header" style="margin-bottom: 10px;">
                                        <div class="dot r"></div>
                                        <div class="dot y"></div>
                                        <div class="dot g"></div>
                                    </div>
                                    <span class="code-text" style="color: #6a9955; display:block; margin-bottom: 10px;">// Dica: A resposta correta deve ser '<?php echo htmlspecialchars($p['alternativa_' . strtolower($p['alternativa_correta'])]); ?>'</span>
                                    <span class="code-text" style="color: #569cd6; font-size:1.15rem;">> </span>
                                    <textarea class="typing-input" id="typeInput_<?php echo $index; ?>" placeholder="Escreva seu código aqui..." oninput="checkTypingInput(this)"></textarea>
                                </div>
                                <div style="margin-top: 15px; font-size: 0.85rem; color: #8e95a1; background: #1a1c24; padding: 10px; border-radius: 8px;">
                                    <strong>Opções (Use o conteúdo de uma delas para acertar):</strong><br>
                                    <span style="color:#d1d5e0;">A) <?php echo htmlspecialchars($p['alternativa_a']); ?></span><br>
                                    <span style="color:#d1d5e0;">B) <?php echo htmlspecialchars($p['alternativa_b']); ?></span><br>
                                    <span style="color:#d1d5e0;">C) <?php echo htmlspecialchars($p['alternativa_c']); ?></span>
                                </div>
                            </div>
                        
                        <?php elseif ($tipo === 'completar_codigo'): ?>
                            <div class="complete-code-container">
                                <p style="font-size: 0.95rem; color: #a5a5ac; margin-bottom: 15px;"><i class="fa-solid fa-code"></i> Escolha a alternativa correta para preencher a lacuna abaixo:</p>
                                
                                <div class="code-editor" style="margin-top: 0; box-shadow: 0 5px 15px rgba(0,0,0,0.3); margin-bottom: 20px;">
                                    <div class="code-header" style="margin-bottom: 10px;">
                                        <div class="dot r"></div>
                                        <div class="dot y"></div>
                                        <div class="dot g"></div>
                                    </div>
                                    <div class="code-snippet-box" style="padding: 0; border: none; background: transparent;">
                                        <span class="code-text" style="color: #6a9955; display:block; margin-bottom: 10px;">// <?php echo htmlspecialchars($p['pergunta_texto']); ?></span>
                                        <span class="code-text" style="color: #ce82ff; font-weight: bold;">_________</span>
                                    </div>
                                </div>

                                <div class="duo-options" style="display: flex; flex-direction: column; gap: 10px;">
                                    <div class="duo-option" data-value="A" onclick="selectOption(this)">
                                        <span class="option-text"><?php echo htmlspecialchars($p['alternativa_a']); ?></span>
                                    </div>
                                    <div class="duo-option" data-value="B" onclick="selectOption(this)">
                                        <span class="option-text"><?php echo htmlspecialchars($p['alternativa_b']); ?></span>
                                    </div>
                                    <div class="duo-option" data-value="C" onclick="selectOption(this)">
                                        <span class="option-text"><?php echo htmlspecialchars($p['alternativa_c']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                        <?php elseif ($tipo === 'completar_codigo_escrito'): ?>
                            <div class="complete-code-container">
                                <p style="font-size: 0.95rem; color: #a5a5ac; margin-bottom: 15px;"><i class="fa-solid fa-keyboard"></i> Digite a resposta exata para completar o código:</p>
                                
                                <div class="code-editor" style="margin-top: 0; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                                    <div class="code-header" style="margin-bottom: 10px;">
                                        <div class="dot r"></div>
                                        <div class="dot y"></div>
                                        <div class="dot g"></div>
                                    </div>
                                    <div class="code-snippet-box" style="padding: 0; border: none; background: transparent;">
                                        <span class="code-text" style="color: #6a9955; display:block; margin-bottom: 10px;">// Complete a lacuna com 'A', 'B', 'C' ou a frase correta</span>
                                        <span class="code-text" style="color: #569cd6;">> </span>
                                        <input type="text" class="code-input-inline" placeholder="digite a resposta..." oninput="checkInlineInput(this)">
                                    </div>
                                </div>
                                <div style="margin-top: 15px; font-size: 0.85rem; color: #8e95a1; background: #1a1c24; padding: 10px; border-radius: 8px;">
                                    <strong>Possíveis Respostas:</strong><br>
                                    <span style="color:#d1d5e0;">A) <?php echo htmlspecialchars($p['alternativa_a']); ?></span><br>
                                    <span style="color:#d1d5e0;">B) <?php echo htmlspecialchars($p['alternativa_b']); ?></span><br>
                                    <span style="color:#d1d5e0;">C) <?php echo htmlspecialchars($p['alternativa_c']); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

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

    <!-- AVISO DE SAÍDA (mostrado ao tentar sair no meio das perguntas) -->
    <div class="exit-warning-overlay" id="exitWarningModal">
        <div class="exit-warning-card">
            <i class="fa-solid fa-triangle-exclamation exit-warning-icon"></i>
            <h3 class="exit-warning-title">Sair da lição agora?</h3>
            <p class="exit-warning-text">Se você sair no meio das perguntas, seu progresso nesta lição não será salvo e você vai perder 15 XP.</p>
            <div class="exit-warning-actions">
                <button type="button" class="btn-exit-warning btn-exit-cancel" onclick="fecharAvisoSaida()">Continuar respondendo</button>
                <button type="button" class="btn-exit-warning btn-exit-confirm" onclick="confirmarSaida()">Sair mesmo assim</button>
            </div>
        </div>
    </div>

    <?php include '../../back/chatbot.php'; ?>

    <script src="../assets/js/opus_alerta.js?v=<?= time() ?>"></script>
    <script src="../assets/js/script.js?v=<?= time() ?>"></script>
    <script src="../assets/js/licao.js?v=<?= time() ?>"></script>

</body>
</html>
