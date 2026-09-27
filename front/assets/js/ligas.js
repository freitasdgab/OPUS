// ligas.js - lógica da página de Ligas

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

// Seguir / Deixar de Seguir via botão no avatar
function toggleSeguirLiga(targetId, btn) {
    const fd = new FormData();
    fd.append('action', 'toggle_seguir');
    fd.append('target_id', targetId);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (data.is_seguindo) {
                    btn.classList.add('following');
                    btn.title = 'Seguindo';
                    btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                } else {
                    btn.classList.remove('following');
                    btn.title = 'Seguir';
                    btn.innerHTML = '<i class="fa-solid fa-user-plus"></i>';
                }
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    fetch('../../back/api_ligas.php')
        .then(r => r.json())
        .then(data => {
            if (data.error === "unauthorized") { window.location.href = "auth.html"; return; }
            if (data.error) { mostrarErro(data.mensagem || 'Erro desconhecido no servidor.'); return; }

            // Mapeamento de ícones únicos por Liga
            const iconesPorLiga = {
                'bronze': '<i class="fa-solid fa-shield"></i>',
                'prata': '<i class="fa-solid fa-shield-halved"></i>',
                'ouro': '<i class="fa-solid fa-crown"></i>',
                'diamante': '<i class="fa-solid fa-gem"></i>',
                'mestre': '<i class="fa-solid fa-dragon"></i>'
            };
            
            const nomeKey = data.divisao_nome.toLowerCase();
            const mainIconHtml = iconesPorLiga[nomeKey] || '<i class="fa-solid fa-shield-halved"></i>';

            document.getElementById('badge-icon').innerHTML = mainIconHtml;
            document.getElementById('badge-icon').style.background = data.divisao_cor;
            document.getElementById('badge-nome').innerText = data.divisao_nome;
            document.getElementById('badge-sub').innerText =
                `${data.minha_posicao}º lugar de ${data.tamanho_grupo} · ${data.meu_xp_semana} XP nesta semana`;
            document.getElementById('timer-valor').innerText = formatarTempo(data.segundos_restantes);

            // ── Track de Ligas ──
            const ligasOrdem = ['Bronze', 'Prata', 'Ouro', 'Diamante', 'Mestre'];
            const ligaAtualIndex = ligasOrdem.findIndex(l => l.toLowerCase() === data.divisao_nome.toLowerCase());
            
            let trackHTML = '';
            ligasOrdem.forEach((nomeLiga, index) => {
                let statusClass = '';
                let iconHTML = iconesPorLiga[nomeLiga.toLowerCase()] || '<i class="fa-solid fa-shield"></i>';
                
                if (index < ligaAtualIndex) {
                    iconHTML = '<i class="fa-solid fa-check"></i>';
                } else if (index === ligaAtualIndex) {
                    statusClass = 'active';
                } else {
                    statusClass = 'locked';
                    iconHTML = '<i class="fa-solid fa-lock"></i>';
                }
                
                trackHTML += `
                    <div class="league-node ${statusClass}">
                        <div class="league-node-icon">${iconHTML}</div>
                        <div class="league-node-name">${nomeLiga}</div>
                    </div>
                `;
            });
            const trackContainer = document.getElementById('league-track');
            if (trackContainer) trackContainer.innerHTML = trackHTML;

            // ── Ranking ──
            let html = '';
            data.membros.forEach(m => {
                let icon = '';
                if (m.zona === 'sobe') icon = '<i class="fa-solid fa-arrow-up" style="color:#58cc02"></i>';
                if (m.zona === 'desce') icon = '<i class="fa-solid fa-arrow-down" style="color:#ff4b4b"></i>';

                // medalha para top 3
                let medalha = '';
                if (m.posicao === 1) medalha = '🥇';
                else if (m.posicao === 2) medalha = '🥈';
                else if (m.posicao === 3) medalha = '🥉';

                html += `
                    <div class="liga-row ${m.zona} ${m.is_me ? 'is-me' : ''}">
                        <div class="row-pos">${medalha || m.posicao + 'º'}</div>
                        <div class="row-avatar-wrapper">
                            <img src="${obterFoto(m.foto_perfil)}" class="img-cover" alt="${m.nome}">
                        </div>
                        <div class="row-name">${m.nome}${m.is_me ? ' <span style="color:#1cb0f6;font-size:0.78rem">(Você)</span>' : ''}</div>
                        <div class="row-xp">${m.xp} XP</div>
                        <div class="row-icon">${icon}</div>
                    </div>`;
            });
            document.getElementById('liga-list').innerHTML = html;
        })
        .catch(err => mostrarErro('Não foi possível conectar à API (' + err.message + ').'));
});


