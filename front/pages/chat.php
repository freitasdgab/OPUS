<?php
session_start();
require_once '../../back/conexao.php';
require_once '../../back/jogador_status.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
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
    <title>Chat e Amigos - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    <link rel="stylesheet" href="../assets/css/chat.css">
</head>
<body>
    <div class="app-container">
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content" style="padding-top: 0; padding-bottom: 0;">
            <?php include '../../back/topbar.php'; ?>
            
            <div class="chat-hub-container">
                <!-- COLUNA ESQUERDA: AMIGOS E CONVERSAS -->
                <div class="chat-sidebar">
                    <div class="chat-sidebar-header">
                        <h2>Conexões</h2>
                        <div class="chat-actions" style="display:flex; gap:8px;">
                            <button class="btn-invite" style="background:#2e2e42; color:#fff;" onclick="window.history.back()"><i class="fa-solid fa-arrow-left"></i> Voltar</button>
                            <button class="btn-invite" onclick="copiarConvite('<?php echo $convite_url; ?>')"><i class="fa-solid fa-link"></i> Convidar</button>
                        </div>
                    </div>
                    
                    <div class="chat-search">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" id="search-input" placeholder="Buscar por ID ou Email...">
                        <button class="btn-search" onclick="buscarAmigos()"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </div>
                    
                    <div id="search-results" style="display: none; margin: 10px; padding: 10px; background: #232330; border-radius: 12px; border: 2px solid #2e2e3e;"></div>

                    <div class="chat-list" id="chat-list">
                        <div class="chat-loading"><i class="fa-solid fa-spinner fa-spin"></i> Carregando conversas...</div>
                    </div>
                </div>

                <!-- COLUNA DIREITA: JANELA DE CHAT -->
                <div class="chat-main" id="chat-main">
                    <div class="chat-empty-state">
                        <i class="fa-regular fa-comments"></i>
                        <h3>Selecione uma conversa</h3>
                        <p>Envie mensagens, convide para duelos e aprenda junto!</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/chat.js?v=1791479069,11463"></script>
    <script>
        function copiarConvite(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert("Link de convite copiado para a área de transferência!");
            });
        }
    </script>
</body>
</html>
