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
    <title>Ligas - Opus</title>
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">

    <link rel="stylesheet" href="../assets/css/ligas.css">
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="app-container">
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../../back/topbar.php'; ?>

            <div class="liga-container">
                <div class="liga-header">
                    <div class="liga-badge">
                        <div id="badge-icon" class="liga-badge-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <div>
                            <div id="badge-nome" class="liga-badge-nome">Carregando...</div>
                            <div id="badge-sub" class="liga-badge-sub"></div>
                        </div>
                    </div>
                    <div class="liga-timer">
                        <div class="label">Vira semana em</div>
                        <div id="timer-valor" class="valor">--:--:--</div>
                    </div>
                </div>

                <div class="liga-legenda">
                    <span><i class="dot sobe"></i> Zona de subida</span>
                    <span><i class="dot neutro"></i> Permanece</span>
                    <span><i class="dot desce"></i> Zona de rebaixamento</span>
                </div>

                <div id="liga-list" class="liga-list"></div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/ligas.js"></script>
</body>
</html>