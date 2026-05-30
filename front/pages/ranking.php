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
    
    <style>
        .ranking-container { background: rgba(20, 20, 28, 0.7); border: 1px solid rgba(26, 54, 202, 0.2); border-radius: 16px; padding: 30px; max-width: 900px; margin: 0 auto; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        .ranking-header { text-align: center; margin-bottom: 35px; }
        .ranking-header h1 { font-family: 'Orbitron', sans-serif; color: #fff; font-size: 1.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        .ranking-header p { color: #a0a0b0; font-size: 0.95rem; }
        
        .my-status-alert { display: none; background: rgba(26, 54, 202, 0.2); border: 1px solid #1a36ca; border-radius: 8px; padding: 12px; text-align: center; font-weight: 600; margin-bottom: 30px; font-size: 1rem; color: #4d66f5; }

        .podium { display: flex; justify-content: center; align-items: flex-end; gap: 25px; margin-bottom: 40px; min-height: 220px; }
        .podium-item { display: flex; flex-direction: column; align-items: center; width: 120px; text-align: center; }
        .podium-avatar { width: 65px; height: 65px; background: #2a2a35; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 3px solid; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.3); overflow: hidden; }
        .podium-name { font-family: 'Poppins', sans-serif; font-size: 0.9rem; font-weight: 600; margin-top: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; }
        .podium-xp { font-family: 'Poppins', sans-serif; font-size: 0.8rem; color: #a0a0b0; margin-bottom: 8px; }
        .podium-bar { width: 100%; border-radius: 8px 8px 0 0; display: flex; justify-content: center; align-items: center; font-family: 'Orbitron', sans-serif; font-size: 1.6rem; font-weight: bold; color: #000; box-shadow: inset 0 -5px 0 rgba(0,0,0,0.2); }

        .rank-2 .podium-avatar { border-color: #C0C0C0; color: #C0C0C0; }
        .rank-2 .podium-bar { height: 100px; background: linear-gradient(to bottom, #E0E0E0, #C0C0C0); }
        .rank-1 .podium-avatar { border-color: #FFD700; color: #FFD700; width: 75px; height: 75px; font-size: 28px; overflow: visible; }
        .rank-1 .podium-avatar img { border-radius: 50%; width: 100%; height: 100%; object-fit: cover; }
        .rank-1 .podium-bar { height: 140px; background: linear-gradient(to bottom, #FFE066, #FFD700); }
        .rank-1 .crown { position: absolute; top: -22px; color: #FFD700; font-size: 20px; z-index: 10; }
        .rank-3 .podium-avatar { border-color: #CD7F32; color: #CD7F32; }
        .rank-3 .podium-bar { height: 75px; background: linear-gradient(to bottom, #E6984C, #CD7F32); }

        .ranking-list { display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto; padding-right: 5px; }
        .ranking-list::-webkit-scrollbar { width: 6px; }
        .ranking-list::-webkit-scrollbar-thumb { background: rgba(26, 54, 202, 0.4); border-radius: 4px; }
        .ranking-row { display: flex; align-items: center; background: #1e1e28; padding: 15px 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); }
        .ranking-row.is-me { background: rgba(26, 54, 202, 0.1); border: 1px solid #1a36ca; }
        .row-pos { font-family: 'Orbitron', sans-serif; font-size: 1.1rem; font-weight: bold; width: 50px; color: #707080; }
        .ranking-row.is-me .row-pos { color: #4d66f5; }
        .row-avatar { width: 35px; height: 35px; background: #2a2a35; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: #a0a0b0; overflow: hidden; }
        .row-name { flex-grow: 1; font-family: 'Poppins', sans-serif; font-weight: 500; }
        .row-xp { font-family: 'Poppins', sans-serif; font-weight: bold; color: #FFD700; font-size: 0.95rem; }
        
        .img-cover { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">OPUS</div>
            <nav class="menu">
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Progresso</a>
                <a href="conquistas.php" class="nav-link"><i class="fa-solid fa-award"></i> Conquistas</a>
                <a href="ranking.php" class="nav-link active"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

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
    <script>
        // Função para resolver a foto (Base64 ou Padrão)
        function obterFoto(fotoBase64) {
            if (fotoBase64 && fotoBase64.startsWith('data:image')) {
                return fotoBase64;
            }
            return '../assets/img/opi pulando feliz.png'; // Imagem padrão
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../back/api_ranking.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error === "unauthorized") { window.location.href = "auth.html"; return; }

                    if (data.minha_posicao > 0) {
                        const statusDiv = document.getElementById('my-status');
                        statusDiv.innerText = `Você está atualmente na ${data.minha_posicao}ª posição!`;
                        statusDiv.style.display = 'block';
                    }

                    let podiumHTML = '';
                    
                    // Segundo Colocado
                    if (data.top3[1]) {
                        podiumHTML += `<div class="podium-item rank-2"><div class="podium-avatar"><img src="${obterFoto(data.top3[1].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[1].nome}</div><div class="podium-xp">${Number(data.top3[1].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">2</div></div>`;
                    }
                    
                    // Primeiro Colocado (Rei do pedaço!)
                    if (data.top3[0]) {
                        podiumHTML += `<div class="podium-item rank-1"><div class="podium-avatar"><i class="fa-solid fa-crown crown"></i><img src="${obterFoto(data.top3[0].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[0].nome}</div><div class="podium-xp">${Number(data.top3[0].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">1</div></div>`;
                    }
                    
                    // Terceiro Colocado
                    if (data.top3[2]) {
                        podiumHTML += `<div class="podium-item rank-3"><div class="podium-avatar"><img src="${obterFoto(data.top3[2].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[2].nome}</div><div class="podium-xp">${Number(data.top3[2].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">3</div></div>`;
                    }
                    
                    document.getElementById('podium-container').innerHTML = podiumHTML;

                    // Lista do Restante
                    let listHTML = '';
                    data.lista_resto.forEach(user => {
                        listHTML += `<div class="ranking-row ${user.is_me ? 'is-me' : ''}"><div class="row-pos">${user.posicao}º</div><div class="row-avatar"><img src="${obterFoto(user.foto_perfil)}" class="img-cover"></div><div class="row-name">${user.nome}${user.is_me ? ' (Você)' : ''}</div><div class="row-xp">${Number(user.xp).toLocaleString('pt-BR')} XP</div></div>`;
                    });
                    
                    document.getElementById('ranking-list').innerHTML = listHTML;
                })
                .catch(err => console.error("Erro:", err));
        });
    </script>
</body>
</html><?php
session_start();
// Conexão com o banco adicionada para abastecer a topbar
require_once '../../back/conexao.php';

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
    
    <style>
        .ranking-container { background: rgba(20, 20, 28, 0.7); border: 1px solid rgba(26, 54, 202, 0.2); border-radius: 16px; padding: 30px; max-width: 900px; margin: 0 auto; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        .ranking-header { text-align: center; margin-bottom: 35px; }
        .ranking-header h1 { font-family: 'Orbitron', sans-serif; color: #fff; font-size: 1.8rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 5px; }
        .ranking-header p { color: #a0a0b0; font-size: 0.95rem; }
        
        .my-status-alert { display: none; background: rgba(26, 54, 202, 0.2); border: 1px solid #1a36ca; border-radius: 8px; padding: 12px; text-align: center; font-weight: 600; margin-bottom: 30px; font-size: 1rem; color: #4d66f5; }

        .podium { display: flex; justify-content: center; align-items: flex-end; gap: 25px; margin-bottom: 40px; min-height: 220px; }
        .podium-item { display: flex; flex-direction: column; align-items: center; width: 120px; text-align: center; }
        .podium-avatar { width: 65px; height: 65px; background: #2a2a35; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 3px solid; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.3); overflow: hidden; }
        .podium-name { font-family: 'Poppins', sans-serif; font-size: 0.9rem; font-weight: 600; margin-top: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; }
        .podium-xp { font-family: 'Poppins', sans-serif; font-size: 0.8rem; color: #a0a0b0; margin-bottom: 8px; }
        .podium-bar { width: 100%; border-radius: 8px 8px 0 0; display: flex; justify-content: center; align-items: center; font-family: 'Orbitron', sans-serif; font-size: 1.6rem; font-weight: bold; color: #000; box-shadow: inset 0 -5px 0 rgba(0,0,0,0.2); }

        .rank-2 .podium-avatar { border-color: #C0C0C0; color: #C0C0C0; }
        .rank-2 .podium-bar { height: 100px; background: linear-gradient(to bottom, #E0E0E0, #C0C0C0); }
        .rank-1 .podium-avatar { border-color: #FFD700; color: #FFD700; width: 75px; height: 75px; font-size: 28px; overflow: visible; }
        .rank-1 .podium-avatar img { border-radius: 50%; width: 100%; height: 100%; object-fit: cover; }
        .rank-1 .podium-bar { height: 140px; background: linear-gradient(to bottom, #FFE066, #FFD700); }
        .rank-1 .crown { position: absolute; top: -22px; color: #FFD700; font-size: 20px; z-index: 10; }
        .rank-3 .podium-avatar { border-color: #CD7F32; color: #CD7F32; }
        .rank-3 .podium-bar { height: 75px; background: linear-gradient(to bottom, #E6984C, #CD7F32); }

        .ranking-list { display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto; padding-right: 5px; }
        .ranking-list::-webkit-scrollbar { width: 6px; }
        .ranking-list::-webkit-scrollbar-thumb { background: rgba(26, 54, 202, 0.4); border-radius: 4px; }
        .ranking-row { display: flex; align-items: center; background: #1e1e28; padding: 15px 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); }
        .ranking-row.is-me { background: rgba(26, 54, 202, 0.1); border: 1px solid #1a36ca; }
        .row-pos { font-family: 'Orbitron', sans-serif; font-size: 1.1rem; font-weight: bold; width: 50px; color: #707080; }
        .ranking-row.is-me .row-pos { color: #4d66f5; }
        .row-avatar { width: 35px; height: 35px; background: #2a2a35; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px; color: #a0a0b0; overflow: hidden; }
        .row-name { flex-grow: 1; font-family: 'Poppins', sans-serif; font-weight: 500; }
        .row-xp { font-family: 'Poppins', sans-serif; font-weight: bold; color: #FFD700; font-size: 0.95rem; }
        
        .img-cover { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">OPUS</div>
            <nav class="menu">
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Progresso</a>
                <a href="conquistas.php" class="nav-link"><i class="fa-solid fa-award"></i> Conquistas</a>
                <a href="ranking.php" class="nav-link active"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

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
    <script>
        // Função JS que decide se renderiza a Base64 ou o Opi feliz padrão
        function obterFoto(fotoBase64) {
            if (fotoBase64 && fotoBase64.startsWith('data:image')) {
                return fotoBase64;
            }
            return '../assets/img/opi pulando feliz.png';
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../back/api_ranking.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error === "unauthorized") { window.location.href = "auth.html"; return; }

                    if (data.minha_posicao > 0) {
                        const statusDiv = document.getElementById('my-status');
                        statusDiv.innerText = `Você está atualmente na ${data.minha_posicao}ª posição!`;
                        statusDiv.style.display = 'block';
                    }

                    let podiumHTML = '';
                    
                    // 2º Lugar
                    if (data.top3[1]) {
                        podiumHTML += `<div class="podium-item rank-2"><div class="podium-avatar"><img src="${obterFoto(data.top3[1].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[1].nome}</div><div class="podium-xp">${Number(data.top3[1].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">2</div></div>`;
                    }
                    
                    // 1º Lugar
                    if (data.top3[0]) {
                        podiumHTML += `<div class="podium-item rank-1"><div class="podium-avatar"><i class="fa-solid fa-crown crown"></i><img src="${obterFoto(data.top3[0].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[0].nome}</div><div class="podium-xp">${Number(data.top3[0].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">1</div></div>`;
                    }
                    
                    // 3º Lugar
                    if (data.top3[2]) {
                        podiumHTML += `<div class="podium-item rank-3"><div class="podium-avatar"><img src="${obterFoto(data.top3[2].foto_perfil)}" class="img-cover"></div><div class="podium-name">${data.top3[2].nome}</div><div class="podium-xp">${Number(data.top3[2].xp).toLocaleString('pt-BR')} XP</div><div class="podium-bar">3</div></div>`;
                    }
                    
                    document.getElementById('podium-container').innerHTML = podiumHTML;

                    // Restante do Ranking
                    let listHTML = '';
                    data.lista_resto.forEach(user => {
                        listHTML += `<div class="ranking-row ${user.is_me ? 'is-me' : ''}"><div class="row-pos">${user.posicao}º</div><div class="row-avatar"><img src="${obterFoto(user.foto_perfil)}" class="img-cover"></div><div class="row-name">${user.nome}${user.is_me ? ' (Você)' : ''}</div><div class="row-xp">${Number(user.xp).toLocaleString('pt-BR')} XP</div></div>`;
                    });
                    
                    document.getElementById('ranking-list').innerHTML = listHTML;
                })
                .catch(err => console.error("Erro:", err));
        });
    </script>
</body>
</html>