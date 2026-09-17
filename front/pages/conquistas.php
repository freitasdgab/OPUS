<?php
session_start();
require_once '../../back/conexao.php';

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
    <title>Conquistas - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    
    <link rel="stylesheet" href="../assets/css/conquistas.css">
</head>
<body>
    <canvas id="bg-canvas"></canvas>

    <div class="app-container">
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../../back/topbar.php'; ?>

            <div class="page-container">
                
                <div class="header-conquistas">
                    <div class="header-icon">
                        <i class="fa-solid fa-medal"></i>
                    </div>
                    <div class="header-text">
                        <h1>Sala de Troféus</h1>
                        <p>Desbloqueie conquistas completando lições e mantendo sua ofensiva.</p>
                    </div>
                </div>

                <div class="trophy-grid" id="trophy-container">
                    <!-- Os troféus serão carregados via JS aqui -->
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/conquistas.js"></script>
</body>
</html>