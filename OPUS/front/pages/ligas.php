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

    <style>
        .liga-container { background: rgba(20,20,28,0.7); border: 1px solid rgba(26,54,202,0.2); border-radius: 16px; padding: 30px; max-width: 900px; margin: 0 auto; color: #fff; box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
        .liga-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .liga-badge { display: flex; align-items: center; gap: 14px; }
        .liga-badge-icon { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #fff; box-shadow: 0 4px 14px rgba(0,0,0,0.4); }
        .liga-badge-nome { font-family: 'Orbitron', sans-serif; font-size: 1.4rem; text-transform: uppercase; letter-spacing: 1px; }
        .liga-badge-sub { color: #a0a0b0; font-size: 0.85rem; }
        .liga-timer { text-align: right; font-family: 'Poppins', sans-serif; }
        .liga-timer .label { color: #a0a0b0; font-size: 0.8rem; }
        .liga-timer .valor { font-family: 'Orbitron', sans-serif; font-size: 1.1rem; color: #4d66f5; }

        .liga-legenda { display: flex; gap: 18px; margin-bottom: 18px; font-size: 0.82rem; color: #a0a0b0; flex-wrap: wrap; }
        .liga-legenda span { display: flex; align-items: center; gap: 6px; }
        .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .dot.sobe { background: #58cc02; } .dot.neutro { background: #707080; } .dot.desce { background: #ff4b4b; }

        .liga-list { display: flex; flex-direction: column; gap: 8px; max-height: 480px; overflow-y: auto; padding-right: 5px; }
        .liga-row { display: flex; align-items: center; background: #1e1e28; padding: 12px 18px; border-radius: 10px; border-left: 4px solid transparent; }
        .liga-row.sobe { border-left-color: #58cc02; }
        .liga-row.neutro { border-left-color: #707080; }
        .liga-row.desce { border-left-color: #ff4b4b; }
        .liga-row.is-me { background: rgba(26,54,202,0.15); }
        .row-pos { font-family: 'Orbitron', sans-serif; font-weight: bold; width: 45px; color: #707080; }
        .row-avatar { width: 32px; height: 32px; border-radius: 50%; overflow: hidden; margin-right: 12px; background: #2a2a35; }
        .row-name { flex-grow: 1; font-family: 'Poppins', sans-serif; font-weight: 500; }
        .row-xp { font-family: 'Poppins', sans-serif; font-weight: bold; color: #FFD700; }
        .row-icon { width: 20px; text-align: center; margin-left: 10px; }
        .img-cover { width: 100%; height: 100%; object-fit: cover; }
    </style>
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
    <script>
        function obterFoto(fotoBase64) {
            if (fotoBase64 && fotoBase64.startsWith('data:image')) return fotoBase64;
            return '../assets/img/opi pulando feliz.png';
        }

        function formatarTempo(segundos) {
            const d = Math.floor(segundos / 86400);
            const h = Math.floor((segundos % 86400) / 3600);
            const m = Math.floor((segundos % 3600) / 60);
            return `${d}d ${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m`;
        }

        function mostrarErro(msg) {
            document.getElementById('badge-nome').innerText = 'Erro ao carregar';
            document.getElementById('badge-sub').innerText = msg;
            document.getElementById('liga-list').innerHTML =
                `<div style="padding:20px;text-align:center;color:#ff8080">${msg}</div>`;
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetch('../../back/api_ligas.php')
                .then(r => r.json())
                .then(data => {
                    if (data.error === "unauthorized") { window.location.href = "auth.html"; return; }
                    if (data.error) { mostrarErro(data.mensagem || 'Erro desconhecido no servidor.'); return; }

                    document.getElementById('badge-icon').style.background = data.divisao_cor;
                    document.getElementById('badge-nome').innerText = data.divisao_nome;
                    document.getElementById('badge-sub').innerText =
                        `${data.minha_posicao}º lugar de ${data.tamanho_grupo} · ${data.meu_xp_semana} XP nesta semana`;
                    document.getElementById('timer-valor').innerText = formatarTempo(data.segundos_restantes);

                    let html = '';
                    data.membros.forEach(m => {
                        let icon = '';
                        if (m.zona === 'sobe') icon = '<i class="fa-solid fa-arrow-up" style="color:#58cc02"></i>';
                        if (m.zona === 'desce') icon = '<i class="fa-solid fa-arrow-down" style="color:#ff4b4b"></i>';
                        html += `<div class="liga-row ${m.zona} ${m.is_me ? 'is-me' : ''}">
                            <div class="row-pos">${m.posicao}º</div>
                            <div class="row-avatar"><img src="${obterFoto(m.foto_perfil)}" class="img-cover"></div>
                            <div class="row-name">${m.nome}${m.is_me ? ' (Você)' : ''}</div>
                            <div class="row-xp">${m.xp} XP</div>
                            <div class="row-icon">${icon}</div>
                        </div>`;
                    });
                    document.getElementById('liga-list').innerHTML = html;
                })
                .catch(err => mostrarErro('Não foi possível conectar à API (' + err.message + ').'));
        });
    </script>
</body>
</html>