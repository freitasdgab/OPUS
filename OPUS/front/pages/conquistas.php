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
    <link rel="shortcut icon" href="../assets/img/logo.png">
    
    <style>
        /* Base e Estrutura */
        .page-container { 
            padding: 40px; 
            max-width: 1200px; 
            margin: 0 auto; 
            width: 100%; 
        }
        
        /* Cabeçalho da Página */
        .header-conquistas {
            background: linear-gradient(135deg, rgba(28, 176, 246, 0.15) 0%, rgba(20, 20, 28, 0) 100%);
            border: 1px solid rgba(28, 176, 246, 0.2);
            border-radius: 20px;
            padding: 30px 40px;
            margin-bottom: 40px;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .header-icon {
            font-size: 3rem;
            color: #1cb0f6;
            text-shadow: 0 0 20px rgba(28, 176, 246, 0.5);
        }

        .header-text h1 {
            color: #fff;
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }

        .header-text p {
            color: #a0a0b0;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            font-size: 1rem;
        }

        /* Grid de Troféus */
        .trophy-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
            gap: 25px; 
        }

        /* Cartões de Conquista */
        .trophy-card { 
            background: linear-gradient(145deg, #181824, #12121a); 
            border: 1px solid #2a2a35; 
            padding: 35px 25px; 
            border-radius: 24px; 
            text-align: center; 
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .icon-wrapper {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
            font-size: 2.5rem;
            transition: all 0.3s ease;
        }

        .trophy-card h3 { 
            font-family: 'Orbitron', sans-serif; 
            font-size: 1.1rem; 
            color: #fff; 
            margin: 0 0 12px 0; 
            font-weight: 700;
        }

        .trophy-card p { 
            font-family: 'Poppins', sans-serif; 
            font-size: 0.85rem; 
            color: #8c8c9e; 
            margin: 0;
            line-height: 1.5;
        }

        /* Badge de Status (Trava) */
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #2a2a35;
            color: #6a6a75;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 0.8rem;
        }

        /* ESTADO: Bloqueado */
        .locked { 
            filter: grayscale(80%); 
            opacity: 0.7;
        }
        .locked .icon-wrapper {
            background: #1e1e28;
            color: #4a4a5a;
        }
        .locked:hover {
            opacity: 1;
            transform: translateY(-3px);
            border-color: #3a3a45;
        }

        /* ESTADO: Desbloqueado (Conquistado) */
        .unlocked { 
            background: linear-gradient(145deg, #1f1f2e, #161622);
            border-color: rgba(255, 215, 0, 0.4); 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .unlocked .icon-wrapper { 
            background: rgba(255, 215, 0, 0.15);
            color: #FFD700; 
            box-shadow: 0 0 25px rgba(255, 215, 0, 0.3) inset, 0 0 15px rgba(255, 215, 0, 0.2);
        }

        .unlocked .status-badge {
            background: rgba(255, 215, 0, 0.2);
            color: #FFD700;
        }

        .unlocked:hover {
            transform: translateY(-8px);
            border-color: #FFD700;
            box-shadow: 0 15px 35px rgba(255, 215, 0, 0.15);
        }
        
        .unlocked:hover .icon-wrapper {
            transform: scale(1.1);
        }

        /* Efeito de brilho de fundo na carta desbloqueada */
        .unlocked::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,215,0,0.05) 0%, rgba(0,0,0,0) 70%);
            z-index: 0;
            pointer-events: none;
        }
        .unlocked * { z-index: 1; position: relative; }
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
                <a href="ranking.php" class="nav-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <?php include '../../back/topbar.php'; ?>

            <div class="page-container">
                
                <!-- Novo Cabeçalho -->
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
    <script>
        fetch('../../back/api_conquistas.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('trophy-container');
            
            data.lista.forEach(t => {
                const isUnlocked = data.conquistados.includes(t.slug);
                
                // Define o ícone de status (cadeado fechado ou check/estrela)
                const statusIcon = isUnlocked ? '<i class="fa-solid fa-check"></i>' : '<i class="fa-solid fa-lock"></i>';
                
                // Permite usar um ícone personalizado vindo do banco, ou cai no troféu padrão
                const iconeTrofeu = t.icone ? t.icone : 'fa-solid fa-trophy';

                container.innerHTML += `
                    <div class="trophy-card ${isUnlocked ? 'unlocked' : 'locked'}">
                        <div class="status-badge">${statusIcon}</div>
                        <div class="icon-wrapper">
                            <i class="${iconeTrofeu}"></i>
                        </div>
                        <h3>${t.nome}</h3>
                        <p>${t.desc}</p>
                    </div>`;
            });
        })
        .catch(error => console.error('Erro ao carregar conquistas:', error));
    </script>
</body>
</html>