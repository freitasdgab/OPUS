<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conquistas - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <style>
        .page-container { padding: 30px; display: flex; flex-direction: column; }
        .trophy-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
            gap: 20px; 
            margin-top: 20px;
        }
        .trophy-card { 
            background: rgba(20, 20, 28, 0.7); 
            border: 1px solid rgba(26, 54, 202, 0.2); 
            padding: 25px; 
            border-radius: 15px; 
            text-align: center; 
            transition: all 0.3s ease;
        }
        .trophy-icon { font-size: 3.5rem; margin-bottom: 15px; color: #4d66f5; }
        .trophy-card h3 { font-family: 'Orbitron', sans-serif; font-size: 1rem; color: #fff; margin-bottom: 10px; }
        .trophy-card p { font-family: 'Poppins', sans-serif; font-size: 0.8rem; color: #a0a0b0; }
        
        /* Estados */
        .locked { filter: grayscale(100%) brightness(0.5); opacity: 0.6; border-color: #333; }
        .unlocked { border-color: #FFD700; box-shadow: 0 0 15px rgba(255, 215, 0, 0.2); }
        .unlocked .trophy-icon { color: #FFD700; }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>

    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">OPUS</div>
            <nav class="menu">
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Progresso</a>
                <a href="conquistas.php" class="nav-link active"><i class="fa-solid fa-award"></i> Conquistas</a>
                <a href="ranking.html" class="nav-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="stats">
                    <div class="stat-box"><i class="fa-solid fa-fire"></i> Dificuldade</div>
                </div>
            </header>

            <div class="page-container">
                <h1 style="color: white; font-family: 'Orbitron'; margin-bottom: 20px;">Minhas Conquistas</h1>
                <div class="trophy-grid" id="trophy-container">
                    </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        fetch('../../back/api_conquistas.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('trophy-container');
            data.lista.forEach(t => {
                const isUnlocked = data.conquistados.includes(t.slug);
                container.innerHTML += `
                    <div class="trophy-card ${isUnlocked ? 'unlocked' : 'locked'}">
                        <div class="trophy-icon"><i class="fa-solid fa-trophy"></i></div>
                        <h3>${t.nome}</h3>
                        <p>${t.desc}</p>
                    </div>`;
            });
        });
    </script>
</body>
</html>