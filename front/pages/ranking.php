<?php
session_start();
// Proteção de login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador não disputa ranking de jogadores
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking Global - Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    
    <link rel="stylesheet" href="../assets/css/ranking.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/perfil_modal.css?v=<?= time() ?>">
</head>
<body>
        <div class="app-container">
        <?php include '../../back/includes/sidebar.php'; ?>

        <main class="main-content">
            
            <?php include '../../back/includes/topbar.php'; ?>

            <div class="ranking-container">
                <!-- Hero do Ranking com Mascote Opi Comemorando -->
                <section class="ranking-hero">
                    <div class="ranking-hero-content">
                        <div class="ranking-hero-badge">
                            <i class="fa-solid fa-trophy"></i> Liga Competitiva
                        </div>
                        <h1 class="ranking-hero-title">
                            Ranking <span>Global</span>
                        </h1>
                        <p class="ranking-hero-desc">
                            Suba de n&iacute;vel resolvendo li&ccedil;&otilde;es, acumule XP com seus acertos e dispute o lugar mais alto do p&oacute;dio com desenvolvedores de toda a comunidade!
                        </p>
                    </div>
                    <div class="ranking-hero-mascote">
                        <img src="../assets/img/opi_ranking.png" alt="Mascote Opi Comemorando">
                    </div>
                </section>

                <div id="my-status" class="my-status-alert"></div>
                <div id="podium-container" class="podium"></div>
                <div id="ranking-list" class="ranking-list"></div>
            </div>
        </main>
    </div>

    <!-- BOTÃO FLUTUANTE COM FORMATO DE SETA PARA ROLAGEM DIRETA AO USUÁRIO ATUAL -->
    <button class="btn-scroll-to-lesson" id="btnScrollToLesson" onclick="rolarAteUsuarioAtual(true)" title="Ir direto para a sua posição no ranking">
        <i class="fa-solid fa-arrow-right"></i>
    </button>
    
    <script src="../assets/js/script.js?v=<?= time() ?>"></script>
    <script src="../assets/js/opus_audio.js"></script>
    <script src="../assets/js/perfil_modal.js?v=<?= time() ?>"></script>
    <script src="../assets/js/ranking.js?v=<?= time() ?>"></script>
</body>
</html>

