<?php
// Toda a lógica de sessão, proteção de acesso e busca de dados no banco
// mora em back/licao_logic.php.
require_once '../../back/licao_logic.php';

$desafios_por_unidade = [
    1 => [
        'badge' => 'Java / Ola Mundo',
        'heading' => 'O Seu Primeiro Programa Java',
        'intro' => 'Agora que você já aprendeu os conceitos fundamentais nas lições anteriores, chegou a hora de colocar o conhecimento em prática!',
        'missao' => 'Escreva um código em Java para exibir exatamente no terminal a seguinte mensagem:',
        'alvo_visual' => 'Ola Mundo',
        'dica_texto' => 'Para imprimir uma mensagem no console em Java, utilizamos a instrução:',
        'dica_codigo' => 'System.out.println("Ola Mundo");',
        'arquivo' => 'HelloWorld.java',
        'codigo_inicial' => "public class HelloWorld {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}",
        'validador_js' => "(normStdout.includes('ola mundo') || normStdout.includes('olá mundo') || normStdout.includes('hello world'))",
        'mensagem_ajuste' => 'Seu programa executou, mas a saída esperada é exatamente "Ola Mundo". Ajuste o texto no editor e tente novamente!'
    ],
    2 => [
        'badge' => 'Java / Variáveis',
        'heading' => 'Armazenando e Exibindo Variáveis',
        'intro' => 'Agora que você já conheceu como funcionam as variáveis e o tipo inteiro, chegou a hora de declarar sua primeira variável na prática!',
        'missao' => 'Declare uma variável inteira chamada <strong>pontos</strong> com o valor <strong>100</strong> e exiba o seu valor no terminal:',
        'alvo_visual' => '100',
        'dica_texto' => 'Para declarar uma variável inteira e imprimir seu valor, utilize:',
        'dica_codigo' => "int pontos = 100;\nSystem.out.println(pontos);",
        'arquivo' => 'Pontuacao.java',
        'codigo_inicial' => "public class Pontuacao {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}",
        'validador_js' => "(normStdout.includes('100'))",
        'mensagem_ajuste' => 'Seu programa executou, mas a saída esperada é o valor 100 da variável pontos. Verifique a declaração e tente novamente!'
    ],
];
$desafio_atual = $desafios_por_unidade[$unidade_atual] ?? $desafios_por_unidade[1];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($dados_licao['titulo']); ?> - Opus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;900&family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/licao.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/opus_alerta.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/light-mode.css?v=<?= time() ?>">
    <script src="../assets/js/theme.js"></script>
    <?php if ($licao_atual == 5): ?>
        <link rel="stylesheet" href="../assets/css/codedex.css?v=<?= time() ?>">
        <!-- Ace Editor para o Desafio estilo Codédex na Lição 5 -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ext-language_tools.min.js"></script>
        <style>
            /* Garantia de layout split-screen e editor Dracula mesmo com cache do navegador */
            .codedex-container {
                position: fixed !important;
                inset: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                display: flex !important;
                flex-direction: column !important;
                background: #0d0d12 !important;
                z-index: 9999 !important;
                font-family: 'Poppins', sans-serif !important;
            }
            .codedex-topbar {
                height: 56px !important;
                min-height: 56px !important;
                background: #13151f !important;
                border-bottom: 1px solid #232738 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
                padding: 0 24px !important;
            }
            .codedex-workspace {
                flex: 1 !important;
                display: grid !important;
                grid-template-columns: 44% 56% !important;
                height: calc(100vh - 56px) !important;
                overflow: hidden !important;
            }
            .codedex-instructions-panel {
                background: #13151f !important;
                border-right: 1px solid #232738 !important;
                padding: 28px 32px !important;
                overflow-y: auto !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 20px !important;
            }
            .codedex-ide-panel {
                display: flex !important;
                flex-direction: column !important;
                background: #0d0d12 !important;
                height: 100% !important;
                overflow: hidden !important;
            }
            .codedex-editor-area {
                height: 52% !important;
                min-height: 260px !important;
                position: relative !important;
                background: #0d0d12 !important;
            }
            #codedexAceEditor {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                right: 0 !important;
                bottom: 0 !important;
                width: 100% !important;
                height: 100% !important;
                font-size: 15px !important;
                font-family: 'Courier New', Courier, monospace !important;
            }
            .codedex-terminal-panel {
                flex: 1 !important;
                min-height: 180px !important;
                background: #08090c !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            }
            .codedex-terminal-output {
                flex: 1 !important;
                padding: 16px 20px !important;
                font-family: 'Courier New', Courier, monospace !important;
                font-size: 0.95rem !important;
                color: #f1f5f9 !important;
                overflow-y: auto !important;
                white-space: pre-wrap !important;
                line-height: 1.5 !important;
                background: #08090c !important;
            }
            .codedex-success-card {
                display: none !important;
            }
            .codedex-success-card.active {
                display: flex !important;
            }
            /* Reposiciona Chatbot Opi IA na lateral para não cobrir o XP no topo direito */
            body.licao-page .opi-chat-trigger,
            .licao-page .opi-chat-trigger {
                top: 50% !important;
                bottom: auto !important;
                right: 18px !important;
                transform: translateY(-50%) !important;
                z-index: 100000 !important;
            }
            body.licao-page .opi-chat-trigger:hover,
            .licao-page .opi-chat-trigger:hover {
                transform: translateY(-50%) scale(1.08) !important;
            }
            body.licao-page .opi-chat-window,
            .licao-page .opi-chat-window {
                top: 50% !important;
                bottom: auto !important;
                right: 76px !important;
                transform: translateY(-50%) !important;
                max-height: 85vh !important;
                z-index: 100001 !important;
            }
            #confettiContainer,
            .confetti-container {
                z-index: 100002 !important;
                pointer-events: none !important;
            }
            .codedex-btn-run {
                border: none !important;
                border-bottom: 4px solid #0c1724 !important;
                outline: none !important;
            }
            .codedex-btn-run:hover:not(:disabled) {
                border: none !important;
                border-bottom: 4px solid #0c1724 !important;
            }
            .codedex-btn-run:active:not(:disabled) {
                border: none !important;
                border-bottom: 2px solid #0c1724 !important;
            }
            .codedex-btn-continue {
                border: none !important;
                border-bottom: 4px solid #1480b3 !important;
                outline: none !important;
            }
            .codedex-btn-continue:hover {
                border: none !important;
                border-bottom: 4px solid #1480b3 !important;
            }
            .codedex-btn-continue:active {
                border: none !important;
                border-bottom: 2px solid #1480b3 !important;
            }
            @media (max-width: 900px) {
                .codedex-workspace {
                    grid-template-columns: 1fr !important;
                    height: auto !important;
                    overflow-y: auto !important;
                }
                .codedex-instructions-panel {
                    border-right: none !important;
                    border-bottom: 2px solid #232738 !important;
                }
                .codedex-editor-area {
                    height: 320px !important;
                }
            }
        </style>
    <?php endif; ?>
</head>
<body class="licao-page">

<?php if ($licao_atual == 5): ?>
    <!-- ================================================================ -->
    <!-- LIÇÃO 5: INTERFACE ESTILO CODÉDEX (IDE SPLIT-SCREEN + TERMINAL) -->
    <!-- ================================================================ -->
    <div class="codedex-container">
        
        <!-- Topbar Codédex -->
        <div class="codedex-topbar">
            <div class="codedex-topbar-left">
                <a href="dashboard.php" class="codedex-btn-back"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
                <div class="codedex-topbar-title">
                    <i class="fa-brands fa-java" style="color: #1cb0f6;"></i>
                    <span>Seção <?php echo $unidade_atual; ?> &bull; Desafio Prático</span>
                </div>
            </div>
            <div class="codedex-topbar-right" style="display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(255, 200, 0, 0.12); border: 1px solid rgba(255, 200, 0, 0.3); padding: 5px 14px; border-radius: 20px; display: flex; align-items: center; gap: 7px; font-weight: 700; color: #ffc800; font-size: 0.9rem;">
                    <i class="fa-solid fa-bolt"></i> <span id="codedexUserXp"><?php echo number_format((int)($status_jogador['xp'] ?? 0), 0, ',', '.'); ?> XP</span>
                </div>
            </div>
        </div>

        <!-- Área de Trabalho Split-Screen (Codédex) -->
        <div class="codedex-workspace">
            
            <!-- Painel Esquerdo: Instruções e Teoria -->
            <div class="codedex-instructions-panel">
                <span class="codedex-lesson-badge"><i class="fa-brands fa-java"></i> <?php echo htmlspecialchars($desafio_atual['badge']); ?></span>
                <h1 class="codedex-lesson-heading"><?php echo htmlspecialchars($desafio_atual['heading']); ?></h1>
                
                <div class="codedex-text-content">
                    <p><?php echo $desafio_atual['intro']; ?></p>
                </div>

                <!-- Card de Instruções da Missão -->
                <div class="codedex-card-box">
                    <h4><i class="fa-solid fa-bullseye" style="color: #1cb0f6;"></i> Missão do Desafio</h4>
                    <p style="font-size: 1.05rem; line-height: 1.6; margin: 0 0 10px 0;">
                        <?php echo $desafio_atual['missao']; ?>
                    </p>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #1cb0f6; font-family: monospace; background: rgba(28, 176, 246, 0.12); padding: 8px 16px; border-radius: 8px; border: 1.5px dashed rgba(28, 176, 246, 0.45); display: inline-block;">
                        <?php echo htmlspecialchars($desafio_atual['alvo_visual']); ?>
                    </div>
                </div>

                <!-- Seção de Dica Interativa Expansível (Gasta 10 XP) -->
                <div class="codedex-hint-section">
                    <button type="button" class="codedex-btn-hint" id="btnToggleHint" onclick="solicitarDicaCodedex()">
                        <i class="fa-solid fa-lightbulb"></i> <span id="hintBtnText">Ver Dica (-10 XP)</span>
                    </button>
                    <div class="codedex-hint-card" id="codedexHintCard" style="display: none;">
                        <div class="codedex-hint-header">
                            <i class="fa-solid fa-lightbulb" style="color: #ffc800;"></i>
                            <strong>Dica de Sintaxe:</strong>
                        </div>
                        <p><?php echo htmlspecialchars($desafio_atual['dica_texto']); ?></p>
                        <div class="codedex-hint-code"><?php echo nl2br(htmlspecialchars($desafio_atual['dica_codigo'])); ?></div>
                    </div>
                </div>

                <div class="codedex-text-content" style="font-size: 0.9rem; margin-top: auto;">
                    <p>1. Digite a instrução necessária no editor.<br>
                    2. Clique em <strong>EXECUTAR</strong> para compilar e rodar.<br>
                    3. Assim que a saída correta for exibida, o botão <strong>CONTINUAR</strong> será liberado!</p>
                </div>
            </div>

            <!-- Painel Direito: Editor Ace + Terminal -->
            <div class="codedex-ide-panel">
                
                <!-- Aba do Arquivo -->
                <div class="codedex-editor-top">
                    <div class="codedex-tab-file">
                        <i class="fa-brands fa-java"></i> <?php echo htmlspecialchars($desafio_atual['arquivo']); ?>
                    </div>
                </div>

                <!-- Container do Ace Editor com esqueleto inicial -->
                <div class="codedex-editor-area">
                    <div id="codedexAceEditor"><?php echo htmlspecialchars($desafio_atual['codigo_inicial']); ?></div>
                </div>

                <!-- Barra de Ações do Editor -->
                <div class="codedex-ide-actions">
                    <button type="button" class="codedex-btn-reset" onclick="resetarCodigoCodedex()" title="Restaurar código padrão">
                        <i class="fa-solid fa-rotate-left"></i> Repor código
                    </button>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <button type="button" class="codedex-btn-run" id="btnCodedexRun" onclick="executarCodigoCodedex()">
                            <i class="fa-solid fa-play"></i> EXECUTAR
                        </button>
                        <button type="button" class="codedex-btn-continue" id="btnCodedexContinue" onclick="concluirCodedex()" style="display: none;">
                            CONTINUAR <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Painel do Terminal -->
                <div class="codedex-terminal-panel">
                    <div class="codedex-terminal-header">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="codedex-term-dots">
                                <span class="dot-r"></span>
                                <span class="dot-y"></span>
                                <span class="dot-g"></span>
                            </div>
                            <span class="codedex-term-title"><i class="fa-solid fa-terminal"></i> Terminal</span>
                        </div>
                        <span class="codedex-term-status" id="codedexTermStatus">Pronto</span>
                    </div>

                    <div class="codedex-terminal-output" id="codedexTermOutput">
                        <div style="color: #6272a4;">Clique no botão "EXECUTAR" ou pressione Ctrl + Enter para testar seu código.</div>
                    </div>
                </div>

            </div>

        </div>

    </div>

<?php else: ?>
    <!-- ================================================================ -->
    <!-- LIÇÕES 1 A 4: SLIDES DE EXPLICAÇÃO + QUIZ DE 6 QUESTÕES          -->
    <!-- ================================================================ -->
    
    <!-- FASE 1: SLIDES DE EXPLICAÇÃO -->
    <div class="phase-container" id="explanationPhase" style="display: flex;">
        <div class="phase-header">
            <a href="dashboard.php" class="btn-close-phase"><i class="fa-solid fa-arrow-left"></i> Voltar</a>
            <span class="lesson-title"><?php echo htmlspecialchars($dados_licao['titulo']); ?></span>
            <div style="width: 80px;"></div>
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

    <!-- FASE 2: QUIZ ESTILO DUOLINGO / MIMO (6 PERGUNTAS) -->
    <div class="phase-container" id="quizPhase" style="display: none;">
        <!-- BARRA DE PROGRESSO -->
        <div class="quiz-progress-bar">
            <a href="dashboard.php" class="btn-close-quiz" onclick="event.preventDefault(); abrirAvisoSaida();" title="Sair da lição"><i class="fa-solid fa-xmark"></i></a>
            <div class="progress-track">
                <div class="progress-fill" id="progressFill" style="background: linear-gradient(90deg, <?php echo htmlspecialchars($cor_capitulo); ?>, <?php echo htmlspecialchars($cor_capitulo); ?>);"></div>
            </div>
            <button type="button" class="btn-sound-toggle" id="btnSoundToggle" title="Ativar/Desativar som" aria-label="Som">
                <i class="fa-solid fa-volume-high"></i>
            </button>
            <span class="progress-text" id="progressText">0/<?php echo count($perguntas); ?></span>
        </div>
        
        <div class="quiz-grid-body"
             data-mascote-explicando="../assets/img/<?php echo htmlspecialchars($img_mascote_explicando); ?>"
             data-mascote-feliz="../assets/img/<?php echo htmlspecialchars($img_mascote_feliz); ?>"
             data-mascote-triste="../assets/img/<?php echo htmlspecialchars($img_mascote_triste); ?>"
             data-cor-capitulo="<?php echo htmlspecialchars($cor_capitulo); ?>">
            
            <div class="quiz-question-side">
                <?php foreach ($perguntas as $index => $p): $q_num = $index + 1; 
                    $tipo = $p['tipo'] ?? 'multipla_escolha';
                    $correct_val = $p['alternativa_correta'] ?? '';
                    $correct_text = ($tipo === 'digitar_codigo' || $tipo === 'completar_codigo_escrito')
                        ? $correct_val
                        : ($p['alternativa_' . strtolower($correct_val)] ?? $correct_val);
                ?>
                    <div class="question-slide" 
                         data-index="<?php echo $index; ?>" 
                         data-tipo="<?php echo htmlspecialchars($tipo); ?>"
                         data-correct="<?php echo htmlspecialchars($correct_val); ?>"
                         data-correct-text="<?php echo htmlspecialchars($correct_text); ?>"
                         data-pergunta-id="<?php echo $p['id']; ?>"
                         style="display: <?php echo $index === 0 ? 'flex' : 'none'; ?>;">
                        
                        <span class="question-number">Exercício <?php echo $q_num; ?> de <?php echo count($perguntas); ?></span>
                        <h2 class="question-text"><?php echo htmlspecialchars($p['pergunta_texto']); ?></h2>
                        
                        <?php if (!empty($p['codigo_contexto']) && $tipo !== 'completar_codigo'): ?>
                            <div class="code-editor" style="margin-top: 5px; margin-bottom: 20px;">
                                <div class="code-header" style="margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                                    <div style="display: flex; gap: 6px;">
                                        <div class="dot r"></div>
                                        <div class="dot y"></div>
                                        <div class="dot g"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #569cd6; letter-spacing: 1px;">JAVA</span>
                                </div>
                                <div class="code-snippet-box" style="padding: 0; border: none; background: transparent;">
                                    <pre style="margin: 0; font-size: 1.15rem; color: #f8f8f2; font-family: 'Courier New', monospace; font-weight: 600;"><code><?php echo htmlspecialchars($p['codigo_contexto']); ?></code></pre>
                                </div>
                            </div>
                        <?php endif; ?>

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
                                <?php if (!empty($p['alternativa_c'])): ?>
                                    <div class="duo-option" data-value="C" onclick="selectOption(this)">
                                        <span class="option-letter">C</span>
                                        <span class="option-text"><?php echo htmlspecialchars($p['alternativa_c']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        
                        <?php elseif ($tipo === 'completar_codigo'): ?>
                            <?php 
                                $code_template = !empty($p['codigo_contexto']) ? $p['codigo_contexto'] : '___';
                                $parts = explode('___', $code_template);
                                $before_part = $parts[0] ?? '';
                                $after_part = $parts[1] ?? '';
                            ?>
                            <div class="complete-code-container">
                                <div class="code-editor" style="margin-top: 10px; margin-bottom: 25px;">
                                    <div class="code-header" style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; gap: 6px;">
                                            <div class="dot r"></div>
                                            <div class="dot y"></div>
                                            <div class="dot g"></div>
                                        </div>
                                        <span style="font-size: 0.8rem; font-weight: 700; color: #569cd6; letter-spacing: 1px;">JAVA</span>
                                    </div>
                                    <div class="code-line-interactive" style="font-family: 'Courier New', monospace; font-size: 1.25rem; font-weight: 700; color: #f8f8f2; display: flex; align-items: center; flex-wrap: wrap; gap: 8px;">
                                        <span class="code-gutter" style="color: #6272a4; margin-right: 4px; user-select: none;">1</span>
                                        <span class="code-prefix" style="color: #569cd6;"><?php echo htmlspecialchars($before_part); ?></span>
                                        <span class="code-blank-slot" onclick="removeBlankWord(this)" title="Clique para remover">
                                            <span class="blank-placeholder">_____</span>
                                            <span class="blank-filled-text"></span>
                                        </span>
                                        <span class="code-suffix" style="color: #569cd6;"><?php echo htmlspecialchars($after_part); ?></span>
                                    </div>
                                </div>

                                <div class="word-bank-chips">
                                    <button type="button" class="word-chip" data-value="A" data-word="<?php echo htmlspecialchars($p['alternativa_a']); ?>" onclick="selectWordChip(this)">
                                        <?php echo htmlspecialchars($p['alternativa_a']); ?>
                                    </button>
                                    <button type="button" class="word-chip" data-value="B" data-word="<?php echo htmlspecialchars($p['alternativa_b']); ?>" onclick="selectWordChip(this)">
                                        <?php echo htmlspecialchars($p['alternativa_b']); ?>
                                    </button>
                                    <?php if (!empty($p['alternativa_c'])): ?>
                                        <button type="button" class="word-chip" data-value="C" data-word="<?php echo htmlspecialchars($p['alternativa_c']); ?>" onclick="selectWordChip(this)">
                                            <?php echo htmlspecialchars($p['alternativa_c']); ?>
                                        </button>
                                    <?php endif; ?>
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

    <!-- AVISO DE SAÍDA -->
    <div class="exit-warning-overlay" id="exitWarningModal">
        <div class="exit-warning-card">
            <i class="fa-solid fa-triangle-exclamation exit-warning-icon"></i>
            <h3 class="exit-warning-title">Sair da lição agora?</h3>
            <p class="exit-warning-text">Se você sair no meio dos exercícios, seu progresso nesta lição não será salvo e você perderá 15 XP.</p>
            <div class="exit-warning-actions">
                <button type="button" class="btn-exit-warning btn-exit-cancel" onclick="fecharAvisoSaida()">Continuar respondendo</button>
                <button type="button" class="btn-exit-warning btn-exit-confirm" onclick="confirmarSaida()">Sair mesmo assim</button>
            </div>
        </div>
    </div>

<?php endif; ?>

    <!-- FORMULÁRIO HIDDEN PARA ENVIAR RESULTADO -->
    <form action="resultado.php" method="POST" id="quiz-form-hidden">
        <input type="hidden" name="licao_id" value="<?php echo $licao_id; ?>">
        <input type="hidden" name="acertos" id="input_acertos" value="0">
        <input type="hidden" name="total_questoes" id="input_total_questoes" value="<?php echo ($licao_atual == 5) ? 6 : count($perguntas); ?>">
        <input type="hidden" name="cap" value="<?php echo $unidade_atual; ?>">
        <input type="hidden" name="licao" value="<?php echo $licao_atual; ?>">
        <input type="hidden" name="attempt_token" value="<?php echo htmlspecialchars($attempt_token); ?>">
        <?php foreach ($perguntas as $p): ?>
            <input type="hidden" name="resposta[<?php echo $p['id']; ?>]" value="" id="resp_<?php echo $p['id']; ?>">
        <?php endforeach; ?>
    </form>

    <!-- CONFETTI -->
    <div class="confetti-container" id="confettiContainer"></div>

    <?php include '../../back/chatbot.php'; ?>

    <script src="../assets/js/opus_audio.js?v=<?= time() ?>"></script>
    <script src="../assets/js/opus_alerta.js?v=<?= time() ?>"></script>
    <script src="../assets/js/script.js?v=<?= time() ?>"></script>

    <?php if ($licao_atual == 5): ?>
        <!-- Script Específico da Lição 5 (Codédex) -->
        <script>
            let codedexEditor = null;
            const DEFAULT_CODEDEX_CODE = <?php echo json_encode($desafio_atual['codigo_inicial']); ?>;
            let dicaDesbloqueada = false;
            let userXpAtual = <?php echo (int)($status_jogador['xp'] ?? 0); ?>;

            document.addEventListener('DOMContentLoaded', function() {
                // Inicializa Ace Editor
                if (window.ace) {
                    codedexEditor = ace.edit("codedexAceEditor");
                    codedexEditor.setTheme("ace/theme/dracula");
                    codedexEditor.session.setMode("ace/mode/java");
                    codedexEditor.setOptions({
                        fontSize: "15px",
                        showPrintMargin: false,
                        showGutter: true,
                        highlightActiveLine: true,
                        tabSize: 4,
                        useSoftTabs: true,
                        enableBasicAutocompletion: true,
                        enableLiveAutocompletion: true
                    });
                    codedexEditor.setValue(DEFAULT_CODEDEX_CODE, -1);
                    codedexEditor.gotoLine(4, 9, true);
                    codedexEditor.resize();
                    setTimeout(() => { 
                        if (codedexEditor) {
                            codedexEditor.resize();
                            codedexEditor.gotoLine(4, 9, true);
                        } 
                    }, 100);
                    setTimeout(() => { if (codedexEditor) codedexEditor.resize(); }, 300);
                }
            });

            function resetarCodigoCodedex() {
                if (codedexEditor) {
                    codedexEditor.setValue(DEFAULT_CODEDEX_CODE, -1);
                    codedexEditor.gotoLine(4, 9, true);
                    codedexEditor.focus();
                }
                const btnContinue = document.getElementById('btnCodedexContinue');
                if (btnContinue) btnContinue.style.display = 'none';
                if (window.OpusAudio) window.OpusAudio.playClick();
            }

            async function solicitarDicaCodedex() {
                const card = document.getElementById('codedexHintCard');
                const txt = document.getElementById('hintBtnText');
                if (!card) return;

                // Se já desbloqueou nesta sessão, apenas alterna a visualização sem cobrar novamente
                if (dicaDesbloqueada) {
                    if (card.style.display === 'none' || !card.style.display) {
                        card.style.display = 'block';
                        if (txt) txt.textContent = 'Ocultar Dica';
                    } else {
                        card.style.display = 'none';
                        if (txt) txt.textContent = 'Ver Dica';
                    }
                    if (window.OpusAudio) window.OpusAudio.playClick();
                    return;
                }

                // Desbloqueia direto deduzindo 10 XP sem confirmação intermediária
                await executarDesbloqueioDica();
            }

            async function executarDesbloqueioDica() {
                const card = document.getElementById('codedexHintCard');
                const txt = document.getElementById('hintBtnText');
                const xpSpan = document.getElementById('codedexUserXp');

                try {
                    const response = await fetch('../../back/api/usar_dica.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' }
                    });
                    const data = await response.json();

                    if (data.sucesso) {
                        dicaDesbloqueada = true;
                        userXpAtual = data.novo_xp;
                        if (xpSpan) xpSpan.textContent = userXpAtual.toLocaleString('pt-BR') + ' XP';
                        if (card) card.style.display = 'block';
                        if (txt) txt.textContent = 'Ocultar Dica';

                        try {
                            if (window.OpusAudio && window.OpusAudio.playCorrect) window.OpusAudio.playCorrect();
                        } catch (e) {}
                        if (window.opusToast) {
                            window.opusToast(data.mensagem || 'Dica desbloqueada! (-10 XP)', 'success', 3000);
                        }
                    } else {
                        // Se falhou por saldo de XP insuficiente
                        try {
                            if (window.OpusAudio && window.OpusAudio.playError) window.OpusAudio.playError();
                        } catch (e) {}
                        if (window.opusAlerta) {
                            window.opusAlerta({
                                titulo: 'Atenção',
                                mensagem: data.erro || 'XP insuficiente para ver a dica.',
                                tipo: 'warning'
                            });
                        } else {
                            alert(data.erro || 'XP insuficiente para ver a dica.');
                        }
                    }
                } catch (err) {
                    // Em caso de falha de conexão, libera a dica silenciosamente para o usuário continuar estudando
                    dicaDesbloqueada = true;
                    if (card) card.style.display = 'block';
                    if (txt) txt.textContent = 'Ocultar Dica';
                }
            }

            async function executarCodigoCodedex() {
                if (!codedexEditor) return;
                const codigo = codedexEditor.getValue().trim();
                const btnRun = document.getElementById('btnCodedexRun');
                const btnContinue = document.getElementById('btnCodedexContinue');
                const statusBadge = document.getElementById('codedexTermStatus');
                const termOutput = document.getElementById('codedexTermOutput');

                // Garante que o botão continuar esteja oculto durante nova execução
                if (btnContinue) btnContinue.style.display = 'none';

                btnRun.disabled = true;
                btnRun.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> EXECUTANDO...';
                statusBadge.className = 'codedex-term-status running';
                statusBadge.innerText = 'Compilando...';

                termOutput.innerHTML = `
                    <div style="color: #38bdf8; font-family: monospace; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-spinner fa-spin"></i> Compilando e executando código Java...
                    </div>
                `;

                try {
                    const response = await fetch('../../back/api/executar_codigo.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ codigo: codigo })
                    });
                    const data = await response.json();

                    btnRun.disabled = false;
                    btnRun.innerHTML = '<i class="fa-solid fa-play"></i> EXECUTAR';

                    const stdout = (data.saida || data.program_output || '').trim();
                    const normStdout = stdout.toLowerCase().replace(/[,!.]/g, '').trim();
                    const acertou = data.sucesso && <?php echo $desafio_atual['validador_js']; ?>;

                    if (acertou) {
                        statusBadge.className = 'codedex-term-status success';
                        statusBadge.innerText = 'Sucesso';
                        
                        termOutput.innerHTML = `
                            <div class="codedex-output-result" style="color: #38bdf8; font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">${escapeHtml(stdout)}</div>
                            <div style="color: #1cb0f6; font-weight: 700; margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-circle-check"></i> Parabéns! Desafio concluído com sucesso!
                            </div>
                        `;

                        // Revela o botão CONTINUAR ao lado de EXECUTAR
                        if (btnContinue) {
                            btnContinue.style.display = 'inline-flex';
                        }

                        // Feedback comemorativo: som de vitória e confetes
                        try {
                            if (window.OpusAudio) {
                                if (typeof window.OpusAudio.playLessonVictory === 'function') {
                                    window.OpusAudio.playLessonVictory();
                                } else if (typeof window.OpusAudio.playSuccess === 'function') {
                                    window.OpusAudio.playSuccess();
                                }
                            }
                        } catch (audioErr) {
                            console.warn('OpusAudio notice:', audioErr);
                        }

                        launchConfetti();

                    } else if (data.sucesso) {
                        if (btnContinue) btnContinue.style.display = 'none';
                        statusBadge.className = 'codedex-term-status running';
                        statusBadge.innerText = 'Ajuste';

                        termOutput.innerHTML = `
                            <div class="codedex-output-result" style="color: #cbd5e1; margin-bottom: 8px;">${escapeHtml(stdout)}</div>
                            <div style="color: #ffc800; font-weight: 600; margin-top: 8px;">
                                <?php echo htmlspecialchars($desafio_atual['mensagem_ajuste']); ?>
                            </div>
                        `;
                        try {
                            if (window.OpusAudio && window.OpusAudio.playError) window.OpusAudio.playError();
                        } catch (e) {}
                    } else {
                        if (btnContinue) btnContinue.style.display = 'none';
                        statusBadge.className = 'codedex-term-status error';
                        statusBadge.innerText = 'Erro';

                        const erroMsg = data.erro || data.compiler_error || data.program_error || 'Erro na compilação do código.';
                        termOutput.innerHTML = `
                            <div style="color: #ff5f56; font-weight: bold; margin-bottom: 6px;">[ERRO DE COMPILAÇÃO]:</div>
                            <div style="color: #ff8585; font-family: monospace; white-space: pre-wrap;">${escapeHtml(erroMsg)}</div>
                        `;
                        try {
                            if (window.OpusAudio && window.OpusAudio.playError) window.OpusAudio.playError();
                        } catch (e) {}
                    }
                } catch (err) {
                    if (btnContinue) btnContinue.style.display = 'none';
                    btnRun.disabled = false;
                    btnRun.innerHTML = '<i class="fa-solid fa-play"></i> EXECUTAR';
                    statusBadge.className = 'codedex-term-status error';
                    statusBadge.innerText = 'Falha';
                    termOutput.innerHTML = `
                        <div style="color: #ff5f56; font-family: monospace;">Erro ao executar código: ${escapeHtml(err.message)}</div>
                    `;
                    try {
                        if (window.OpusAudio && window.OpusAudio.playError) window.OpusAudio.playError();
                    } catch (e) {}
                }
            }

            function concluirCodedex() {
                try {
                    if (window.OpusAudio && window.OpusAudio.playLessonVictory) {
                        window.OpusAudio.playLessonVictory();
                    }
                } catch (e) {}
                document.getElementById('input_acertos').value = 6;
                document.getElementById('input_total_questoes').value = 6;
                setTimeout(() => {
                    document.getElementById('quiz-form-hidden').submit();
                }, 400);
            }

            function launchConfetti() {
                const container = document.getElementById('confettiContainer');
                if (!container) return;
                const colors = ['#58cc02', '#4baf00', '#ffd900', '#ff9600', '#4b8df8', '#ce82ff'];

                for (let i = 0; i < 70; i++) {
                    const piece = document.createElement('div');
                    piece.className = 'confetti-piece';
                    piece.style.left = (Math.random() * 100) + '%';
                    piece.style.top = '-10px';
                    piece.style.width = (Math.random() * 8 + 5) + 'px';
                    piece.style.height = (Math.random() * 8 + 5) + 'px';
                    piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                    piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
                    piece.style.animation = `confettiFall ${Math.random() * 2 + 1.5}s ease-out ${Math.random() * 0.5}s forwards`;
                    container.appendChild(piece);

                    setTimeout(() => piece.remove(), 3500);
                }
            }

            function escapeHtml(text) {
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            // Atalho Ctrl+Enter para executar no Codédex
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    executarCodigoCodedex();
                }
            });
        </script>
    <?php else: ?>
        <!-- Script Padrão para as Lições 1 a 4 -->
        <script src="../assets/js/licao.js?v=<?= time() ?>"></script>
    <?php endif; ?>

</body>
</html>
