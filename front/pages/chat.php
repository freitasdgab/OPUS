<?php
session_start();
require_once '../../back/config/conexao.php';
require_once '../../back/lib/jogador_status.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador não acessa chat de alunos
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);

$convite_url = "http://" . $_SERVER['HTTP_HOST'] . "/OPUS/front/pages/auth.html?ref=" . $user_id;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens Diretas - Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/chat.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/light-mode.css?v=<?= time() ?>">
    <script>
        function voltarPaginaAnterior() {
            if (document.referrer && document.referrer.indexOf(window.location.host) !== -1 && document.referrer.indexOf('chat.php') === -1 && document.referrer.indexOf('amigos.php') === -1 && document.referrer.indexOf('auth.html') === -1) {
                window.location.href = document.referrer;
            } else {
                window.location.href = 'dashboard.php';
            }
        }
        window.voltarPaginaAnterior = voltarPaginaAnterior;
    </script>
</head>
<body>
    <div class="app-container">
        <?php include '../../back/includes/sidebar.php'; ?>

        <main class="main-content" style="padding-top: 0; padding-bottom: 0;">
            <?php include '../../back/includes/topbar.php'; ?>
            
            <div class="chat-hub-container">
                <!-- COLUNA ESQUERDA: LISTA DE CONVERSAS (DIRECT) -->
                <div class="chat-sidebar">
                    <div class="chat-sidebar-header">
                        <button type="button" class="btn-chat-back" id="btnChatVoltar" onclick="voltarPaginaAnterior()" title="Voltar">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Voltar</span>
                        </button>
                        <div class="chat-sidebar-actions">
                            <button type="button" class="btn-invite" onclick="copiarConvite('<?php echo $convite_url; ?>')" title="Copiar link de convite">
                                <i class="fa-solid fa-link"></i> Convidar
                            </button>
                        </div>
                    </div>
                    
                    <!-- BUSCA DE AMIGOS -->
                    <div class="chat-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="search-input" placeholder="Buscar por Nome ou E-mail..." onkeyup="if(event.key==='Enter') buscarAmigos()">
                        <button class="btn-search" onclick="buscarAmigos()" title="Buscar"><i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                    
                    <div id="search-results" style="display: none;" class="search-results-panel"></div>

                    <!-- ABAS DO CHAT: CONVERSAS & NOTIFICAÇÕES -->
                    <div class="chat-tabs">
                        <button type="button" class="chat-tab-btn active" id="tabBtnConversas" onclick="alternarAbaChat('conversas')">
                            <i class="fa-solid fa-comments"></i> Conversas
                        </button>
                        <button type="button" class="chat-tab-btn" id="tabBtnNotificacoes" onclick="alternarAbaChat('notificacoes')">
                            <i class="fa-solid fa-bell"></i> Notificações
                            <span class="tab-badge" id="tabBadgeNotif" style="display: none;">0</span>
                        </button>
                    </div>

                    <!-- CONTEÚDO DA ABA CONVERSAS -->
                    <div class="chat-tab-panel active" id="tabPanelConversas">
                        <div id="pending-requests-banner" style="display: none;" class="chat-alert-banner" onclick="alternarAbaChat('notificacoes')">
                            <div class="cab-icon"><i class="fa-solid fa-user-plus"></i></div>
                            <div class="cab-text">
                                <strong id="pending-requests-count">1 pedido</strong> de amizade pendente!
                            </div>
                            <i class="fa-solid fa-chevron-right cab-arrow"></i>
                        </div>

                        <div class="chat-list" id="chat-list">
                            <div class="chat-loading"><i class="fa-solid fa-spinner fa-spin"></i> Carregando conversas...</div>
                        </div>
                    </div>

                    <!-- CONTEÚDO DA ABA NOTIFICAÇÕES -->
                    <div class="chat-tab-panel" id="tabPanelNotificacoes" style="display: none;">
                        <div class="notif-panel-header">
                            <span><i class="fa-solid fa-inbox"></i> Pedidos de Amizade</span>
                            <button type="button" class="btn-refresh-panel" onclick="carregarNotificacoesChat()" title="Atualizar">
                                <i class="fa-solid fa-rotate-right"></i>
                            </button>
                        </div>
                        <div class="notif-chat-list" id="notif-chat-list">
                            <div class="chat-loading"><i class="fa-solid fa-spinner fa-spin"></i> Carregando notificações...</div>
                        </div>
                    </div>
                </div>

                <!-- COLUNA DIREITA: JANELA DO DIRECT MESSAGE -->
                <div class="chat-main" id="chat-main">
                    <div class="chat-empty-state">
                        <div class="empty-state-icon-duo">
                            <i class="fa-regular fa-paper-plane chat-icon"></i>
                        </div>
                        <h3>Suas Mensagens</h3>
                        <p>Envie mensagens privadas e converse diretamente com seus amigos no Opus.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/chat.js?v=<?= time() ?>"></script>
    <script>
        function copiarConvite(url) {
            navigator.clipboard.writeText(url).then(() => {
                if (typeof window.opusToast === 'function') {
                    window.opusToast("Link de convite copiado para a área de transferência!", "success");
                } else {
                    alert("Link de convite copiado para a área de transferência!");
                }
            });
        }
    </script>
</body>
</html>
