<?php
session_start();
// Proteção de login
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
    <title>Ranking Global - Opus</title>
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    
    <link rel="stylesheet" href="../assets/css/ranking.css">
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="app-container">
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            
            <?php include '../../back/topbar.php'; ?>

            <div class="ranking-container">
                <div class="ranking-header">
                    <h1>Ranking Global</h1>
                    <p>Suba de nível resolvendo lições e garanta seu lugar no pódio!</p>
                </div>
                <div id="my-status" class="my-status-alert"></div>
                <div id="podium-container" class="podium"></div>
                <div id="ranking-list" class="ranking-list"></div>
            </div>
        </main>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/ranking.js"></script>
</body>
</html>
