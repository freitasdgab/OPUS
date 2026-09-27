<?php
session_start();
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$invite_code = $user_id; // Código de convite único do usuário
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos & Batalhas - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;800;900&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    
    <link rel="stylesheet" href="../assets/css/amigos.css">
</head>
<body>
    
    <div class="app-container">
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../../back/topbar.php'; ?>

            <div class="amigos-container">
                
                <!-- CABEÇALHO DA PÁGINA -->
                <div class="header-amigos">
                    <div class="header-amigos-info">
                        <h1>Central de Amigos & Batalhas ⚔️</h1>
                        <p>Acompanhe o duelo em tempo real, veja acertos de hoje, diferença de XP e desafie seus amigos!</p>
                    </div>
                    <div class="header-amigos-actions">
                        <button class="btn-invite-link" onclick="copiarLinkConvite(<?php echo $user_id; ?>)">
                            <i class="fa-solid fa-link"></i> Copiar Link de Convite
                        </button>
                    </div>
                </div>

                <!-- BOTÃO VOLTAR (Oculto inicialmente) -->
                <div id="btnVoltarMenuAmigos" class="btn-voltar-amigos" style="display: none;" onclick="voltarMenuAmigos()">
                    <i class="fa-solid fa-arrow-left"></i> Voltar ao Menu Principal
                </div>

                <!-- MENU INICIAL EM GRADE -->
                <div class="amigos-menu-grid" id="amigosMenuGrid">
                    <div class="amigos-menu-card" onclick="abrirTelaAmigos('grupos')">
                        <div class="amc-icon" style="color: #58cc02; background: rgba(88,204,2,0.1);"><i class="fa-solid fa-shield-halved"></i></div>
                        <h3>Grupos de Duelo</h3>
                        <p>Crie ou participe de batalhas em grupo.</p>
                    </div>
                    <div class="amigos-menu-card" onclick="abrirTelaAmigos('batalha')">
                        <div class="amc-icon" style="color: #ff4b4b; background: rgba(255,75,75,0.1);"><i class="fa-solid fa-swords"></i></div>
                        <h3>Minhas Batalhas 1v1</h3>
                        <p>Acompanhe e desafie seus rivais diretos.</p>
                    </div>
                    <div class="amigos-menu-card" onclick="abrirTelaAmigos('encontrar')">
                        <div class="amc-icon" style="color: #1cb0f6; background: rgba(28,176,246,0.1);"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h3>Encontrar Amigos</h3>
                        <p>Busque perfis e veja recomendações da liga.</p>
                    </div>
                    <div class="amigos-menu-card" onclick="abrirTelaAmigos('chat')">
                        <div class="amc-icon" style="color: #ffc800; background: rgba(255,200,0,0.1);"><i class="fa-solid fa-message"></i></div>
                        <h3>Chat & Pedidos</h3>
                        <p>Converse e gerencie suas solicitações.</p>
                    </div>
                </div>

                <!-- CONTEÚDO DA ABA 1: GRUPOS DE DUELO -->
                <div class="tab-content" id="tabContentGrupos">
                    <div class="grupos-actions-bar">
                        <button class="btn-create-group" onclick="abrirModalCriarGrupo()">
                            <i class="fa-solid fa-plus"></i> Criar Grupo de Batalha
                        </button>
                        <div class="join-group-box">
                            <input type="text" id="inputCodigoGrupo" placeholder="Digite o Código do Grupo (ex: A1B2C3D4)">
                            <button onclick="entrarGrupoPorCodigo()"><i class="fa-solid fa-right-to-bracket"></i> Entrar no Grupo</button>
                        </div>
                    </div>

                    <h3 style="color: #fff; font-size: 1.15rem; font-weight: 900; margin-bottom: 16px;">MEUS GRUPOS DE BATALHA</h3>
                    <div class="grupos-grid" id="containerMeusGrupos">
                        <!-- Quadradinhos de Meus Grupos -->
                    </div>

                    <h3 style="color: #fff; font-size: 1.15rem; font-weight: 900; margin: 35px 0 16px 0;">GRUPOS SUGERIDOS DA COMUNIDADE</h3>
                    <div class="grupos-grid" id="containerGruposSugeridos">
                        <!-- Quadradinhos de Grupos Sugeridos -->
                    </div>
                </div>

                <!-- CONTEÚDO DA ABA 2: MINHAS BATALHAS 1V1 (FULANO VS CICLANO) -->
                <div class="tab-content" id="tabContentBatalha">
                    <h3 style="color: #fff; font-size: 1.15rem; font-weight: 900; margin-bottom: 16px;">MINHAS BATALHAS INDIVIDUAIS</h3>
                    <div class="batalhas-1v1-grid" id="containerBatalhas1v1">
                        <!-- Quadradinhos de Batalhas com José, Victor, Pedrinho... -->
                    </div>

                    <!-- ARENA DE DETALHES DA BATALHA SELECIONADA -->
                    <div class="batalha-arena-card" id="areaDetalhesBatalha" style="margin-top: 30px;">
                        <div class="batalha-top-selector">
                            <span><i class="fa-solid fa-bolt" style="color:#ffc800;"></i> CONFRONTO DIRETO DE HOJE</span>
                        </div>

                        <!-- ARENA DE CONFRONTO FULANO VS CICLANO -->
                        <div class="arena-confronto">
                            <!-- JOGADOR 1 (VOCÊ) -->
                            <div class="fighter-card fighter-left">
                                <div class="fighter-avatar-frame">
                                    <img src="../assets/img/opi pulando feliz.png" id="p1Foto" alt="Você" class="fighter-img">
                                </div>
                                <h3 class="fighter-name" id="p1Nome">Você</h3>
                                <span class="fighter-badge" id="p1Fogo"><i class="fa-solid fa-fire" style="color:#ff9600;"></i> 0 Dias</span>
                            </div>

                            <!-- CENTRO: EMBLEMA VS -->
                            <div class="vs-center-badge">
                                <div class="vs-circle">VS</div>
                                <span class="vs-label">BATALHA</span>
                            </div>

                            <!-- JOGADOR 2 (RIVAL) -->
                            <div class="fighter-card fighter-right">
                                <div class="fighter-avatar-frame">
                                    <img src="../assets/img/opi pulando feliz.png" id="p2Foto" alt="Rival" class="fighter-img">
                                </div>
                                <h3 class="fighter-name" id="p2Nome">Rival</h3>
                                <span class="fighter-badge" id="p2Fogo"><i class="fa-solid fa-fire" style="color:#ff9600;"></i> 0 Dias</span>
                            </div>
                        </div>

                        <!-- BARRA DE PODER DA BATALHA -->
                        <div class="battle-power-container">
                            <div class="battle-power-header">
                                <span id="p1PctTxt" style="color:#1cb0f6; font-weight:800;">50%</span>
                                <span class="battle-power-title">DOMÍNIO DA BATALHA</span>
                                <span id="p2PctTxt" style="color:#ff4b4b; font-weight:800;">50%</span>
                            </div>
                            <div class="battle-power-bar">
                                <div class="power-fill p1-fill" id="p1BarFill" style="width: 50%;"></div>
                                <div class="power-fill p2-fill" id="p2BarFill" style="width: 50%;"></div>
                            </div>
                        </div>

                        <!-- CARDS DE ESTATÍSTICAS DO DUELO -->
                        <div class="battle-metrics-grid">
                            <!-- CARD 1: ACERTOS DE HOJE -->
                            <div class="metric-card">
                                <div class="metric-icon" style="color: #58cc02;"><i class="fa-solid fa-bullseye"></i></div>
                                <div class="metric-info">
                                    <span class="metric-label">ACERTOS DE HOJE</span>
                                    <div class="metric-vs-val">
                                        <span id="p1Acertos" style="color: #58cc02;">0</span>
                                        <span class="metric-divider">VS</span>
                                        <span id="p2Acertos" style="color: #ff4b4b;">0</span>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD 2: DIFERENÇA DE XP -->
                            <div class="metric-card">
                                <div class="metric-icon" style="color: #ffc800;"><i class="fa-solid fa-bolt"></i></div>
                                <div class="metric-info">
                                    <span class="metric-label">DIFERENÇA DE XP</span>
                                    <div class="metric-val" id="xpGapText" style="color: #ffc800;">0 XP</div>
                                    <span class="metric-sub" id="xpLeaderText">Empatados!</span>
                                </div>
                            </div>

                            <!-- CARD 3: DIAS DE FOGO -->
                            <div class="metric-card">
                                <div class="metric-icon" style="color: #ff9600;"><i class="fa-solid fa-fire"></i></div>
                                <div class="metric-info">
                                    <span class="metric-label">SEQUÊNCIA DE FOGO</span>
                                    <div class="metric-vs-val">
                                        <span id="p1Streak" style="color: #ff9600;">0d</span>
                                        <span class="metric-divider">VS</span>
                                        <span id="p2Streak" style="color: #ff9600;">0d</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- AÇÕES DO DUELO -->
                        <div class="battle-actions">
                            <button class="btn-battle primary" onclick="provocarRival()">
                                <i class="fa-solid fa-face-grin-squint-tears"></i> Provocar Rival
                            </button>
                            <a href="dashboard.php" class="btn-battle secondary">
                                <i class="fa-solid fa-gamepad"></i> Fazer Lição para Ganhar XP
                            </a>
                        </div>
                    </div>
                </div>

                <!-- CONTEÚDO DA ABA 3: ENCONTRAR AMIGOS -->
                <div class="tab-content" id="tabContentEncontrar">
                    <h3 style="color: #fff; font-size: 1.15rem; font-weight: 900; margin-bottom: 16px;">ENCONTRAR POR E-MAIL OU ID</h3>
                    <div class="search-bar-box">
                        <i class="fa-solid fa-magnifying-glass search-icon"></i>
                        <input type="text" id="inputBuscaAmigo" placeholder="Digite o E-mail ou ID exato do amigo" onkeyup="if(event.key==='Enter') buscarAmigosExato()">
                    </div>
                    <div class="amigos-grid" id="containerResultadoBusca"></div>

                    <h3 style="color: #fff; font-size: 1.15rem; font-weight: 900; margin: 35px 0 16px 0;">RECOMENDAÇÕES DA SUA LIGA</h3>
                    <div class="amigos-grid" id="containerRecomendacoesLiga">
                        <!-- Carregados via JS -->
                    </div>
                </div>

                <!-- CONTEÚDO DA ABA 4: CHAT & PEDIDOS -->
                <div class="tab-content" id="tabContentChat">
                    <div class="chat-pedidos-layout">
                        <div class="chat-sidebar">
                            <h4 style="color: #fff; font-size: 1rem; margin-bottom: 12px; font-weight: 800;">PEDIDOS PENDENTES</h4>
                            <div id="listaPedidosPendentes" class="pedidos-list">
                                <!-- JS -->
                            </div>
                            
                            <h4 style="color: #fff; font-size: 1rem; margin: 20px 0 12px 0; font-weight: 800;">SEUS AMIGOS</h4>
                            <div id="listaAmigosChat" class="amigos-chat-list">
                                <!-- JS -->
                            </div>
                        </div>
                        <div class="chat-main" id="chatMainArea">
                            <div class="chat-empty-state">
                                <i class="fa-solid fa-message" style="font-size: 2.5rem; color: #2e2e42; margin-bottom: 10px;"></i>
                                <p>Selecione um amigo para conversar</p>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </main>
    </div>

    <!-- MODAL DE CRIAR GRUPO DE BATALHA COM ADIÇÃO DE AMIGOS -->
    <div class="modal-overlay" id="modalCriarGrupo">
        <div class="modal-content" style="max-width: 460px;">
            <div class="modal-title">Criar Grupo de Batalha</div>
            <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 20px;">
                Crie um grupo exclusivo para você e seus amigos competirem acertos e XP!
            </p>
            <div style="margin-bottom: 18px; text-align: left;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 8px;">NOME DO GRUPO:</label>
                <input type="text" id="inputNomeGrupo" placeholder="Ex: Esquadrão Java Devs" style="width: 100%; padding: 12px; border-radius: 12px; background: #12121a; border: 2px solid #2e2e42; color: #fff; outline: none;">
            </div>

            <div style="margin-bottom: 20px; text-align: left;">
                <label style="font-size: 0.85rem; font-weight: 700; color: #cbd5e1; display: block; margin-bottom: 8px;">ADICIONAR AMIGOS AO GRUPO:</label>
                <div class="select-friends-list" id="listaAmigosParaGrupo">
                    <!-- Amigos para marcar carregados via JS -->
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="fecharModalCriarGrupo()">Cancelar</button>
                <button type="button" class="btn-modal btn-save" onclick="confirmarCriarGrupo()">Criar Grupo</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE VISUALIZAR BATALHA DO GRUPO (LEADERBOARD E DUELOS DO GRUPO) -->
    <div class="modal-overlay" id="modalVisualizarGrupo">
        <div class="modal-content" style="max-width: 580px; text-align: left;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h2 class="modal-title" id="visGrupoTitulo" style="margin: 0; text-align: left;">Grupo de Batalha</h2>
                    <span id="visGrupoCodigo" style="font-size: 0.85rem; color: #ffc800; font-family: monospace; font-weight: 700;">CÓDIGO: -</span>
                </div>
                <button class="close-btn-modal" onclick="fecharModalVisualizarGrupo()" style="position: relative; top: 0; right: 0;"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <!-- OPÇÕES DE COMPARTILHAMENTO DO GRUPO -->
            <div class="group-share-box">
                <button class="btn-share-zap" id="btnShareZapGroup" onclick="">
                    <i class="fa-brands fa-whatsapp"></i> Convidar pelo WhatsApp
                </button>
                <button class="btn-share-chat" id="btnShareChatGroup" onclick="">
                    <i class="fa-solid fa-paper-plane"></i> Mandar no Chat Opi
                </button>
            </div>

            <h4 style="color: #fff; font-size: 1rem; font-weight: 800; margin: 20px 0 12px 0;">CLASSIFICAÇÃO DO DUELO HOJE:</h4>
            <div class="group-members-list" id="visGrupoMembrosList">
                <!-- Membros e pontuação carregados via JS -->
            </div>

            <div class="modal-actions" style="margin-top: 24px;">
                <button type="button" class="btn-modal btn-cancel" onclick="fecharModalVisualizarGrupo()">Fechar</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/amigos.js"></script>
</body>
</html>

