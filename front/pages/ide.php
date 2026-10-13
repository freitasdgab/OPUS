<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDE Java - Opus</title>
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/ide.css?v=<?= time() ?>">
</head>
<body>

    <div class="app-container">
        
        <!-- Sidebar -->
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            
            <!-- Topbar -->
            <?php include '../../back/topbar.php'; ?>

            <div class="ide-wrapper">
                
                <!-- Barra Superior com Título e Opções de Código -->
                <div class="ide-header">
                    <div class="ide-title-box">
                        <i class="fa-brands fa-java"></i>
                        <div>
                            <h1>Laboratório Java</h1>
                            <span>Escreva o código acima e veja a saída no terminal logo abaixo</span>
                        </div>
                    </div>

                    <div class="ide-controls">
                        <!-- Seletor de Códigos Prontos -->
                        <select id="selectExemplo" class="ide-select" title="Carregar exemplos rápidos">
                            <option value="hello">Exemplo: Olá Mundo</option>
                            <option value="variaveis">Exemplo: Variáveis &amp; Soma</option>
                            <option value="condicional">Exemplo: If / Else</option>
                            <option value="laco">Exemplo: Laço de Repetição</option>
                        </select>

                        <!-- Botão Reiniciar -->
                        <button id="btnResetCode" class="btn-ide btn-ide-secondary" title="Reiniciar código para o padrão">
                            <i class="fa-solid fa-rotate-left"></i> Reiniciar
                        </button>
                    </div>
                </div>

                <!-- Estrutura Vertical: Código em Cima, Terminal Embaixo -->
                <div class="ide-workspace">
                    
                    <!-- 1. Painel Superior: Editor de Código (Dracula) -->
                    <div class="ide-panel editor-panel">
                        <div class="ide-panel-header">
                            <div class="file-tab">
                                <i class="fa-solid fa-file-code"></i> Main.java
                            </div>
                            <span style="font-size: 0.75rem; color: #6272a4;">Java (OpenJDK 21) &bull; Tema Dracula</span>
                        </div>
                        <div id="editor" class="editor-container"></div>
                    </div>

                    <!-- 2. Painel Inferior: Terminal de Saída com Botão Executar no Topo -->
                    <div class="ide-panel terminal-panel">
                        <div class="ide-panel-header terminal-header">
                            <div class="terminal-title">
                                <div class="terminal-dots">
                                    <div class="terminal-dot dot-red"></div>
                                    <div class="terminal-dot dot-yellow"></div>
                                    <div class="terminal-dot dot-green"></div>
                                </div>
                                <i class="fa-solid fa-terminal" style="margin-left: 6px;"></i> Saída do Terminal
                                <span id="terminalStatus" class="terminal-status-badge status-idle">Pronto</span>
                            </div>

                            <!-- Botões de Ação da Saída (Executar no topo da saída) -->
                            <div class="terminal-actions">
                                <button id="btnClearTerminal" class="btn-ide btn-ide-secondary" style="padding: 6px 12px; font-size: 0.8rem;" title="Limpar terminal">
                                    <i class="fa-solid fa-trash-can"></i> Limpar
                                </button>

                                <span class="shortcut-hint" title="Atalho para executar">Ctrl + Enter</span>

                                <!-- Botão de Executar posicionado estrategicamente na barra de saída -->
                                <button id="btnRunCode" class="btn-ide btn-ide-run" title="Executar código (Ctrl + Enter)">
                                    <i class="fa-solid fa-play"></i> Executar
                                </button>
                            </div>
                        </div>

                        <!-- Corpo do Terminal -->
                        <div id="terminalBody" class="terminal-body">
                            <div><span class="term-line-prefix">opus@ide:~$</span> <span class="term-line-cmd">javac Main.java &amp;&amp; java Main</span></div>
                            <div style="color: #6272a4; margin-top: 6px;">
                                Terminal pronto. Clique no botão verde "Executar" acima ou pressione <kbd>Ctrl + Enter</kbd>.
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        </main>
    </div>

    <!-- Scripts do Ace Editor via CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ext-language_tools.min.js"></script>
    <script src="../assets/js/ide.js?v=<?= time() ?>"></script>

</body>
</html>
