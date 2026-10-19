// ligas.js - lógica da página de Ligas

function renderizarAvatarLiga(foto, nome) {
    if (foto && String(foto).trim() !== '') {
        return `<img src="${foto}" class="img-cover" alt="${nome || 'Avatar'}">`;
    }
    return `<div class="img-cover avatar-placeholder"><i class="fa-solid fa-user"></i></div>`;
}

function formatarTempo(segundos) {
    const d = Math.floor(segundos / 86400);
    const h = Math.floor((segundos % 86400) / 3600);
    const m = Math.floor((segundos % 3600) / 60);
    return `${d}d ${String(h).padStart(2,'0')}h ${String(m).padStart(2,'0')}m`;
}

function obterBandeira(codigo, nome) {
    const code = (codigo || 'br').toLowerCase();
    const countryName = nome || 'Brasil';
    return `<img src="https://flagcdn.com/24x18/${code}.png" srcset="https://flagcdn.com/48x36/${code}.png 2x" width="18" height="13" alt="${countryName}" title="${countryName}" class="ranking-flag" style="border-radius:2px; vertical-align:middle;">`;
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

    fetch('../../back/api/amigos.php', { method: 'POST', body: fd })
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
    fetch('../../back/api/ligas.php')
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

            const badgeElem = document.getElementById('badge-icon');
            if (badgeElem) {
                badgeElem.innerHTML = mainIconHtml;
                badgeElem.style.background = data.divisao_cor;
                badgeElem.setAttribute('data-divisao', nomeKey);
                badgeElem.className = `liga-badge-icon liga-${nomeKey}`;
            }
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
                    statusClass = 'completed';
                    iconHTML = '<i class="fa-solid fa-check"></i>';
                } else if (index === ligaAtualIndex) {
                    statusClass = 'active';
                } else {
                    statusClass = 'locked';
                    iconHTML = '<i class="fa-solid fa-lock"></i>';
                }
                
                trackHTML += `
                    <div class="league-node ${statusClass}" data-league="${nomeLiga.toLowerCase()}">
                        <div class="league-node-icon">${iconHTML}</div>
                        <div class="league-node-name">${nomeLiga}</div>
                    </div>
                `;
            });
            const trackContainer = document.getElementById('league-track');
            if (trackContainer) trackContainer.innerHTML = trackHTML;

            // Ajusta a legenda de rebaixamento caso a divisão não tenha queda (ex: Bronze ou Mestre)
            const legendaDesce = document.getElementById('legenda-desce');
            if (legendaDesce) {
                legendaDesce.style.display = data.pode_descer ? 'inline-flex' : 'none';
            }

            // ── Ranking ──
            let html = '';
            data.membros.forEach(m => {
                let tooltipZona = 'Permanece na divisão';
                if (m.zona === 'sobe') tooltipZona = 'Zona de subida (Sobe de divisão)';
                else if (m.zona === 'desce') tooltipZona = 'Zona de rebaixamento (Cai de divisão)';

                html += `
                    <div class="liga-row ${m.zona} ${m.is_me ? 'is-me' : ''}">
                        <div class="row-pos">
                            <span class="pos-num">${m.posicao}</span>
                            <span class="pos-status-dot ${m.zona}" title="${tooltipZona}"></span>
                        </div>
                        <div class="row-avatar-wrapper" onclick="abrirPerfilPublico(${m.id})" style="cursor:pointer;" title="Ver Perfil">
                            ${renderizarAvatarLiga(m.foto_perfil, m.nome)}
                        </div>
                        <div class="row-name">
                            <span class="row-user-text">${m.nome}${m.is_me ? ' <span style="color:#1cb0f6;font-size:0.78rem">(Você)</span>' : ''}</span>
                            <span class="user-country-tag" title="${m.pais || 'Brasil'}">
                                ${obterBandeira(m.pais_codigo, m.pais)}
                                <span class="country-name-label">${m.pais || 'Brasil'}</span>
                            </span>
                        </div>
                        <div class="row-xp">${m.xp} XP</div>
                        <button type="button" class="btn-user-action" onclick="abrirPerfilPublico(${m.id})" title="Ver Perfil">
                            <i class="fa-solid fa-plus"></i>
                            <span>Ver Perfil</span>
                        </button>
                    </div>`;
            });
            document.getElementById('liga-list').innerHTML = html;
        })
        .catch(err => mostrarErro('Não foi possível conectar à API (' + err.message + ').'));
});


