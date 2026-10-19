// ranking.js
function renderizarAvatarRanking(foto) {
    if (foto && String(foto).trim() !== '') {
        return `<img src="${foto}" class="img-cover" alt="Avatar">`;
    }
    return `<i class="fa-solid fa-user"></i>`;
}

// Função para resolver a bandeira do país
function obterBandeira(codigo, nome) {
    const code = (codigo || 'br').toLowerCase();
    const countryName = nome || 'Brasil';
    return `<img src="https://flagcdn.com/24x18/${code}.png" srcset="https://flagcdn.com/48x36/${code}.png 2x" width="20" height="15" alt="${countryName}" title="${countryName}" class="ranking-flag">`;
}

document.addEventListener('DOMContentLoaded', () => {
    fetch('../../back/api/ranking.php')
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
                const u2 = data.top3[1];
                podiumHTML += `<div class="podium-item rank-2 ${u2.is_me ? 'is-me' : ''}" ${u2.is_me ? 'id="currentUserRankingNode"' : ''}>
                    <div class="podium-avatar" onclick="abrirPerfilPublico(${u2.id})" style="cursor:pointer;" title="Ver Perfil">
                        ${renderizarAvatarRanking(u2.foto_perfil)}
                    </div>
                    <div class="podium-xp">${Number(u2.xp).toLocaleString('pt-BR')} XP</div>
                    <div class="podium-name" title="${u2.nome} (${u2.pais || 'Brasil'})">
                        <span class="podium-user-text">${u2.nome}</span>
                        ${obterBandeira(u2.pais_codigo, u2.pais)}
                    </div>
                    <div class="podium-country-badge">${u2.pais || 'Brasil'}</div>
                    <button type="button" class="podium-btn-action" onclick="abrirPerfilPublico(${u2.id})" title="Ver Perfil">
                        <i class="fa-solid fa-plus"></i> Ver Perfil
                    </button>
                    <div class="podium-bar">2</div>
                </div>`;
            }
            
            // Primeiro Colocado (Rei do pedaço!)
            if (data.top3[0]) {
                const u1 = data.top3[0];
                podiumHTML += `<div class="podium-item rank-1 ${u1.is_me ? 'is-me' : ''}" ${u1.is_me ? 'id="currentUserRankingNode"' : ''}>
                    <div class="podium-avatar" onclick="abrirPerfilPublico(${u1.id})" style="cursor:pointer;" title="Ver Perfil">
                        <i class="fa-solid fa-crown crown"></i>
                        ${renderizarAvatarRanking(u1.foto_perfil)}
                    </div>
                    <div class="podium-xp">${Number(u1.xp).toLocaleString('pt-BR')} XP</div>
                    <div class="podium-name" title="${u1.nome} (${u1.pais || 'Brasil'})">
                        <span class="podium-user-text">${u1.nome}</span>
                        ${obterBandeira(u1.pais_codigo, u1.pais)}
                    </div>
                    <div class="podium-country-badge">${u1.pais || 'Brasil'}</div>
                    <button type="button" class="podium-btn-action" onclick="abrirPerfilPublico(${u1.id})" title="Ver Perfil">
                        <i class="fa-solid fa-plus"></i> Ver Perfil
                    </button>
                    <div class="podium-bar">1</div>
                </div>`;
            }
            
            // Terceiro Colocado
            if (data.top3[2]) {
                const u3 = data.top3[2];
                podiumHTML += `<div class="podium-item rank-3 ${u3.is_me ? 'is-me' : ''}" ${u3.is_me ? 'id="currentUserRankingNode"' : ''}>
                    <div class="podium-avatar" onclick="abrirPerfilPublico(${u3.id})" style="cursor:pointer;" title="Ver Perfil">
                        ${renderizarAvatarRanking(u3.foto_perfil)}
                    </div>
                    <div class="podium-xp">${Number(u3.xp).toLocaleString('pt-BR')} XP</div>
                    <div class="podium-name" title="${u3.nome} (${u3.pais || 'Brasil'})">
                        <span class="podium-user-text">${u3.nome}</span>
                        ${obterBandeira(u3.pais_codigo, u3.pais)}
                    </div>
                    <div class="podium-country-badge">${u3.pais || 'Brasil'}</div>
                    <button type="button" class="podium-btn-action" onclick="abrirPerfilPublico(${u3.id})" title="Ver Perfil">
                        <i class="fa-solid fa-plus"></i> Ver Perfil
                    </button>
                    <div class="podium-bar">3</div>
                </div>`;
            }
            
            document.getElementById('podium-container').innerHTML = podiumHTML;

            // Lista do Restante
            let listHTML = '';
            data.lista_resto.forEach(user => {
                listHTML += `<div class="ranking-row ${user.is_me ? 'is-me' : ''}" ${user.is_me ? 'id="currentUserRankingNode"' : ''}>
                    <div class="row-pos">${user.posicao}º</div>
                    <div class="row-avatar" onclick="abrirPerfilPublico(${user.id})" style="cursor:pointer;" title="Ver Perfil">
                        ${renderizarAvatarRanking(user.foto_perfil)}
                    </div>
                    <div class="row-name">
                        <span class="row-user-text">${user.nome}${user.is_me ? ' (Você)' : ''}</span>
                        <span class="user-country-tag" title="${user.pais || 'Brasil'}">
                            ${obterBandeira(user.pais_codigo, user.pais)}
                            <span class="country-name-label">${user.pais || 'Brasil'}</span>
                        </span>
                    </div>
                    <div class="row-xp">${Number(user.xp).toLocaleString('pt-BR')} XP</div>
                    <button type="button" class="btn-user-action" onclick="abrirPerfilPublico(${user.id})" title="Ver Perfil">
                        <i class="fa-solid fa-plus"></i>
                        <span>Ver Perfil</span>
                    </button>
                </div>`;
            });
            
            document.getElementById('ranking-list').innerHTML = listHTML;

            // Nasce direto em cima do usuário logado e inicia o monitoramento da seta flutuante
            requestAnimationFrame(() => {
                rolarAteUsuarioAtual(false, true);
                setTimeout(() => {
                    monitorarVisibilidadeUsuarioAtual();
                }, 100);
            });
        })
        .catch(err => console.error("Erro:", err));
});

// Calcula a posição exata de scroll para focar no usuário sem exibir o pódio quando ele está na lista
function calcularScrollAlvoUsuario(usuarioAtual, container) {
    if (!usuarioAtual || !container) return 0;
    const containerRect = container.getBoundingClientRect();
    const userRect = usuarioAtual.getBoundingClientRect();
    const elemTopRel = (userRect.top - containerRect.top) + container.scrollTop;
    const elemHeight = userRect.height;

    // Centraliza o card do usuário na tela
    let targetScroll = elemTopRel - (container.clientHeight / 2) + (elemHeight / 2);

    // Se o usuário estiver na lista (fora do Top 3), garante que o pódio fique oculto acima
    // para nascer direto no usuário e permitir arrastar para cima até os 3 primeiros
    if (usuarioAtual.classList.contains('ranking-row')) {
        const rankingList = document.getElementById('ranking-list');
        if (rankingList) {
            const listRect = rankingList.getBoundingClientRect();
            const listTopRel = (listRect.top - containerRect.top) + container.scrollTop;
            const minScrollSemPodio = listTopRel - 30;
            if (targetScroll < minScrollSemPodio) {
                targetScroll = minScrollSemPodio;
            }
        }
    }

    const maxScroll = Math.max(0, container.scrollHeight - container.clientHeight);
    return Math.min(maxScroll, Math.max(0, targetScroll));
}

// ── Scroll para o usuário atual e Botão Flutuante de Seta ───────────
function rolarAteUsuarioAtual(tocarSom = false, instantaneo = false) {
    if (tocarSom && window.OpusAudio) window.OpusAudio.playClick();
    const usuarioAtual = document.getElementById('currentUserRankingNode')
                      || document.querySelector('.ranking-row.is-me')
                      || document.querySelector('.podium-item.is-me');
    const container = document.querySelector('.main-content');

    if (usuarioAtual && container) {
        const targetScroll = calcularScrollAlvoUsuario(usuarioAtual, container);

        if (instantaneo) {
            const prevBehavior = container.style.scrollBehavior;
            container.style.scrollBehavior = 'auto';
            container.scrollTop = targetScroll;
            requestAnimationFrame(() => {
                container.style.scrollBehavior = prevBehavior;
            });
        } else {
            container.scrollTo({ top: targetScroll, behavior: 'smooth' });
        }

        // Animação de pulso para evidenciar onde o jogador está
        usuarioAtual.classList.remove('pulse-highlight');
        void usuarioAtual.offsetWidth;
        usuarioAtual.classList.add('pulse-highlight');
        setTimeout(() => usuarioAtual.classList.remove('pulse-highlight'), 2200);
    } else if (usuarioAtual) {
        usuarioAtual.scrollIntoView({ behavior: instantaneo ? 'instant' : 'smooth', block: 'center' });
    }
}

// Monitora o scroll para orientar a direção da seta (cima ou baixo) e ajustar a opacidade/escala quando visível
function monitorarVisibilidadeUsuarioAtual() {
    const btn = document.getElementById('btnScrollToLesson');
    const alvo = document.getElementById('currentUserRankingNode')
              || document.querySelector('.ranking-row.is-me')
              || document.querySelector('.podium-item.is-me');
    const container = document.querySelector('.main-content');
    if (!btn || !alvo) return;

    const icone = btn.querySelector('i');

    const checar = () => {
        const rect = alvo.getBoundingClientRect();
        const containerRect = container ? container.getBoundingClientRect() : { top: 0, bottom: window.innerHeight };

        const targetScroll = container ? calcularScrollAlvoUsuario(alvo, container) : 0;
        const currentScroll = container ? container.scrollTop : window.scrollY;
        const diffScroll = currentScroll - targetScroll;

        // Verifica se o card do usuário está visível e próximo do ponto de foco
        const dentroDaJanela = (rect.top >= containerRect.top + 60 && rect.bottom <= containerRect.bottom - 60);
        const pertoDoAlvo = Math.abs(diffScroll) <= 140;
        const visivel = dentroDaJanela && pertoDoAlvo;

        if (icone) {
            if (diffScroll > 0) {
                // O usuário rolou para baixo (o card principal ficou para cima): seta aponta para CIMA
                if (!icone.classList.contains('fa-arrow-up')) {
                    icone.className = 'fa-solid fa-arrow-up';
                }
                btn.setAttribute('title', 'Rolar para cima até a sua posição no ranking');
            } else {
                // O usuário rolou para cima (ex: vendo o Top 3, o card principal está para baixo): seta aponta para BAIXO
                if (!icone.classList.contains('fa-arrow-down')) {
                    icone.className = 'fa-solid fa-arrow-down';
                }
                btn.setAttribute('title', 'Rolar para baixo até a sua posição no ranking');
            }
        }

        if (visivel) {
            btn.style.opacity = '0.35';
            btn.style.transform = 'scale(0.92)';
        } else {
            btn.style.opacity = '1';
            btn.style.transform = 'scale(1)';
        }
    };

    if (container) {
        container.addEventListener('scroll', checar, { passive: true });
    }
    window.addEventListener('scroll', checar, { passive: true });
    window.addEventListener('resize', checar, { passive: true });
    checar();
}

