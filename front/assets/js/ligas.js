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

            document.getElementById('badge-icon').style.background = data.divisao_cor;
            document.getElementById('badge-nome').innerText = data.divisao_nome;
            document.getElementById('badge-sub').innerText =
                `${data.minha_posicao}º lugar de ${data.tamanho_grupo} · ${data.meu_xp_semana} XP nesta semana`;
            document.getElementById('timer-valor').innerText = formatarTempo(data.segundos_restantes);

            // ── Ranking com botão de seguir em cima da foto ──
            let html = '';
            data.membros.forEach(m => {
                let icon = '';
                if (m.zona === 'sobe') icon = '<i class="fa-solid fa-arrow-up" style="color:#58cc02"></i>';
                if (m.zona === 'desce') icon = '<i class="fa-solid fa-arrow-down" style="color:#ff4b4b"></i>';

                const followBtn = m.is_me ? '' : `
                    <button
                        class="btn-follow-avatar ${m.is_seguindo ? 'following' : ''}"
                        title="${m.is_seguindo ? 'Seguindo' : 'Seguir'}"
                        onclick="toggleSeguirLiga(${m.id}, this)">
                        <i class="fa-solid ${m.is_seguindo ? 'fa-check' : 'fa-user-plus'}"></i>
                    </button>`;

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
                            ${followBtn}
                        </div>
                        <div class="row-name">${m.nome}${m.is_me ? ' <span style="color:#1cb0f6;font-size:0.78rem">(Você)</span>' : ''}</div>
                        <div class="row-xp">${m.xp} XP</div>
                        <div class="row-icon">${icon}</div>
                    </div>`;
            });
            document.getElementById('liga-list').innerHTML = html;

            // ── Seção de Perfis Recomendados (quem não está seguindo ainda) ──
            const recomendados = data.membros.filter(m => !m.is_me && !m.is_seguindo);
            const container = document.getElementById('perfis-recomendados');

            if (!container) return;

            if (recomendados.length === 0) {
                container.innerHTML = `<div style="color:#64748b; text-align:center; padding:20px; font-size:0.9rem; grid-column:1/-1;">
                    🎉 Você já segue todos os da sua liga! Continue competindo!
                </div>`;
                return;
            }

            container.innerHTML = '';
            recomendados.forEach(m => {
                const card = document.createElement('div');
                card.className = 'recomendado-card';
                card.innerHTML = `
                    <div class="rec-avatar-wrap">
                        <img src="${obterFoto(m.foto_perfil)}" class="rec-avatar" alt="${m.nome}">
                        <span class="rec-zona-dot ${m.zona}" title="Zona: ${m.zona}"></span>
                    </div>
                    <div class="rec-info">
                        <strong>${m.nome}</strong>
                        <span>${m.xp} XP esta semana · #${m.posicao}º lugar</span>
                    </div>
                    <button
                        class="btn-rec-seguir"
                        id="recBtn${m.id}"
                        onclick="toggleSeguirRecomendado(${m.id}, this)">
                        <i class="fa-solid fa-user-plus"></i> Seguir
                    </button>`;
                container.appendChild(card);
            });
        })
        .catch(err => mostrarErro('Não foi possível conectar à API (' + err.message + ').'));
});

function toggleSeguirRecomendado(targetId, btn) {
    const fd = new FormData();
    fd.append('action', 'toggle_seguir');
    fd.append('target_id', targetId);

    btn.disabled = true;
    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            if (data.success) {
                if (data.is_seguindo) {
                    btn.className = 'btn-rec-seguir following';
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Seguindo';
                    // Anima o card
                    btn.closest('.recomendado-card').style.opacity = '0.65';
                } else {
                    btn.className = 'btn-rec-seguir';
                    btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> Seguir';
                    btn.closest('.recomendado-card').style.opacity = '1';
                }
            }
        })
        .catch(() => { btn.disabled = false; });
}
