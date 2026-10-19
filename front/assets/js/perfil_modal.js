/**
 * perfil_modal.js - Modal de Visualização de Perfil Público (Opus)
 * Permite que qualquer aluno veja o perfil detalhado de outro aluno
 * a partir do Ranking Global ou das Ligas.
 */

(function() {
    'use strict';

    const LIGAS_CORES = {
        'bronze':   { cor: '#cd7f32', corClara: '#cd7f32' },
        'prata':    { cor: '#8e8e99', corClara: '#c0c0c0' },
        'ouro':     { cor: '#d4a017', corClara: '#ffd700' },
        'diamante': { cor: '#1ec8e0', corClara: '#5be7ff' },
        'mestre':   { cor: '#8b2fd9', corClara: '#c47bff' },
    };

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderizarAvatarModal(foto, nome) {
        if (foto && String(foto).trim() !== '') {
            return `<img src="${foto}" alt="${escapeHtml(nome)}">`;
        }
        return `<div class="pmodal-avatar-empty"><i class="fa-solid fa-user"></i></div>`;
    }

    function garantirModalNoDom() {
        let overlay = document.getElementById('perfilPublicoModalOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'perfilPublicoModalOverlay';
            overlay.className = 'perfil-modal-overlay';
            overlay.innerHTML = `
                <div class="perfil-modal-card" id="perfilPublicoModalCard">
                    <button type="button" class="perfil-modal-close" onclick="fecharPerfilPublico()" title="Fechar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div id="perfilPublicoModalBody"></div>
                </div>
            `;
            document.body.appendChild(overlay);

            // Fechar ao clicar fora
            overlay.addEventListener('click', (e) => {
                if (e.target.id === 'perfilPublicoModalOverlay') fecharPerfilPublico();
            });

            // Fechar com ESC
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') fecharPerfilPublico();
            });
        }
        return overlay;
    }

    window.fecharPerfilPublico = function() {
        const overlay = document.getElementById('perfilPublicoModalOverlay');
        if (overlay) overlay.classList.remove('active');
    };

    window.abrirPerfilPublico = async function(userId) {
        if (!userId) return;
        const overlay = garantirModalNoDom();
        const body = document.getElementById('perfilPublicoModalBody');
        
        body.innerHTML = `
            <div style="padding: 60px 20px; text-align: center; color: var(--text-muted, #94a3b8);">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 2.2rem; color: #1cb0f6; margin-bottom: 14px;"></i>
                <div style="font-weight: 800; font-size: 1.1rem; color: #fff;">Carregando perfil...</div>
            </div>
        `;
        overlay.classList.add('active');

        try {
            const res = await fetch(`../../back/api/perfil_publico.php?user_id=${encodeURIComponent(userId)}`);
            const json = await res.json();

            if (!json.sucesso || !json.dados) {
                body.innerHTML = `
                    <div style="padding: 40px 20px; text-align: center; color: #ff8080;">
                        <i class="fa-solid fa-circle-exclamation" style="font-size: 2rem; margin-bottom: 10px;"></i>
                        <div style="font-weight: 800; font-size: 1.1rem;">${escapeHtml(json.mensagem || 'Não foi possível carregar o perfil.')}</div>
                    </div>
                `;
                return;
            }

            const u = json.dados;
            const divSlug = (u.divisao || 'bronze').toLowerCase();
            const ligaCfg = LIGAS_CORES[divSlug] || LIGAS_CORES.bronze;
            const avatarBoxContent = renderizarAvatarModal(u.foto_perfil, u.nome);
            const paisCode = (u.pais_codigo || 'br').toLowerCase();
            const paisNome = u.pais || 'Brasil';
            const corFundo = u.cor_fundo || '#1cb0f6';

            // Redes sociais
            let redesHtml = '';
            if (u.redes) {
                if (u.redes.github)    redesHtml += `<a href="${escapeHtml(u.redes.github)}" target="_blank" class="pmodal-social-btn gh" title="GitHub"><i class="fa-brands fa-github"></i></a>`;
                if (u.redes.linkedin)  redesHtml += `<a href="${escapeHtml(u.redes.linkedin)}" target="_blank" class="pmodal-social-btn in" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>`;
                if (u.redes.instagram) redesHtml += `<a href="${escapeHtml(u.redes.instagram)}" target="_blank" class="pmodal-social-btn ig" title="Instagram"><i class="fa-brands fa-instagram"></i></a>`;
                if (u.redes.facebook)  redesHtml += `<a href="${escapeHtml(u.redes.facebook)}" target="_blank" class="pmodal-social-btn fb" title="Facebook"><i class="fa-brands fa-facebook"></i></a>`;
                if (u.redes.youtube)   redesHtml += `<a href="${escapeHtml(u.redes.youtube)}" target="_blank" class="pmodal-social-btn yt" title="YouTube"><i class="fa-brands fa-youtube"></i></a>`;
                if (u.redes.email)     redesHtml += `<a href="mailto:${escapeHtml(u.redes.email)}" class="pmodal-social-btn mail" title="Email Público"><i class="fa-solid fa-envelope"></i></a>`;
            }

            // Conquistas
            let conquistasHtml = '';
            if (u.conquistas && u.conquistas.length > 0) {
                conquistasHtml = '<div class="pmodal-trophies-list">';
                u.conquistas.forEach(t => {
                    const img = t.imagem ? `../assets/img/${encodeURI(t.imagem)}` : '../assets/img/LOGO.png';
                    conquistasHtml += `
                        <div class="pmodal-trophy-item" title="${escapeHtml(t.desc)}">
                            <img src="${img}" alt="${escapeHtml(t.nome)}">
                            <div class="pmodal-trophy-name">${escapeHtml(t.nome)}</div>
                        </div>
                    `;
                });
                conquistasHtml += '</div>';
            } else {
                conquistasHtml = `<div class="pmodal-empty-box">Nenhuma conquista desbloqueada ainda.</div>`;
            }

            // Projetos Git
            let projetosHtml = '';
            if (u.projetos && u.projetos.length > 0) {
                projetosHtml = '<div class="pmodal-projects-list">';
                u.projetos.forEach(p => {
                    projetosHtml += `
                        <a href="${escapeHtml(p.url)}" target="_blank" class="pmodal-project-card">
                            <i class="fa-brands fa-git-alt"></i>
                            <div class="pmodal-project-info">
                                <div class="p-title">${escapeHtml(p.titulo)}</div>
                                <div class="p-sub">Ver no GitHub <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.7rem; margin-left: 4px;"></i></div>
                            </div>
                        </a>
                    `;
                });
                projetosHtml += '</div>';
            } else {
                projetosHtml = `<div class="pmodal-empty-box">Nenhum projeto Git cadastrado ainda.</div>`;
            }

            // Botões de Ação (Seguir / Solicitar / Quer ser amigo? / Chat)
            let actionsGroupHtml = '';
            if (!u.is_me) {
                let btnHtml = '';
                if (u.status_amizade === 'amigos' || u.sao_amigos) {
                    btnHtml = `
                        <button type="button" class="pmodal-btn-chat" onclick="window.location.href='chat.php?user_id=${u.id}'" title="Enviar Mensagem">
                            <i class="fa-solid fa-comments"></i>
                            <span>Mandar Mensagem</span>
                        </button>
                        <button type="button" class="pmodal-btn-seguir following" id="btnSeguirModal_${u.id}" onclick="toggleSeguirNoModal(${u.id})">
                            <i class="fa-solid fa-user-check"></i>
                            <span>Amigos</span>
                        </button>
                    `;
                } else if (u.status_amizade === 'pendente_enviado') {
                    btnHtml = `
                        <button type="button" class="pmodal-btn-seguir pending" id="btnSeguirModal_${u.id}" onclick="cancelarSolicitacaoNoModal(${u.id}, '${escapeHtml(u.nome)}')" title="Clique para cancelar solicitação">
                            <i class="fa-solid fa-clock"></i>
                            <span>Solicitação Enviada</span>
                        </button>
                    `;
                } else if (u.status_amizade === 'pendente_recebido') {
                    btnHtml = `
                        <div id="boxAcaoRecebido_${u.id}">
                            <button type="button" class="pmodal-btn-chat add" onclick="abrirPerguntaAmigoModal(${u.pedido_id}, ${u.id}, '${escapeHtml(u.nome)}')">
                                <i class="fa-solid fa-user-check"></i>
                                <span>Aceitar Solicitação</span>
                            </button>
                        </div>
                    `;
                } else if (u.status_amizade === 'ele_me_segue') {
                    btnHtml = `
                        <button type="button" class="pmodal-btn-chat add" id="btnSeguirDeVolta_${u.id}" onclick="seguirDeVoltaNoModal(${u.id}, '${escapeHtml(u.nome)}')">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Quer ser amigo? Seguir de volta</span>
                        </button>
                    `;
                } else if (u.status_amizade === 'eu_sigo' || u.is_seguindo) {
                    btnHtml = `
                        <button type="button" class="pmodal-btn-seguir following" id="btnSeguirModal_${u.id}" onclick="toggleSeguirNoModal(${u.id})">
                            <i class="fa-solid fa-check"></i>
                            <span>Seguindo</span>
                        </button>
                    `;
                } else {
                    btnHtml = `
                        <button type="button" class="pmodal-btn-seguir" id="btnSeguirModal_${u.id}" onclick="solicitarSeguirNoModal(${u.id}, '${escapeHtml(u.nome)}')">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Seguir</span>
                        </button>
                    `;
                }

                actionsGroupHtml = `
                    <div class="pmodal-actions-group" id="pmodalActionsContainer">
                        ${btnHtml}
                    </div>
                `;
            }

            body.innerHTML = `
                <!-- BANNER -->
                <div class="pmodal-banner" style="background-color: ${escapeHtml(corFundo)};"></div>

                <!-- CABEÇALHO -->
                <div class="pmodal-header">
                    <div class="pmodal-avatar-row">
                        <div class="pmodal-avatar-box" style="border-color: ${ligaCfg.corClara};">
                            ${avatarBoxContent}
                            <div class="pmodal-league-badge" style="background: ${ligaCfg.cor};" title="Divisão: ${escapeHtml(u.divisao)}">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                        </div>
                        ${actionsGroupHtml}
                    </div>

                    <div class="pmodal-info-box">
                        <div class="pmodal-name-row">
                            <h2 class="pmodal-user-name">${escapeHtml(u.nome)}</h2>
                            <i class="fa-brands fa-java" style="font-size: 1.4rem; color: #5382a1;" title="Java Developer"></i>
                            <span class="pmodal-tag-country" title="${escapeHtml(paisNome)}">
                                <img src="https://flagcdn.com/24x18/${paisCode}.png" srcset="https://flagcdn.com/48x36/${paisCode}.png 2x" width="18" height="13" alt="${escapeHtml(paisNome)}">
                                <span>${escapeHtml(paisNome)}</span>
                            </span>
                        </div>
                        <div class="pmodal-handle">@${escapeHtml(u.username)}</div>
                        <div class="pmodal-member-since">
                            <i class="fa-solid fa-calendar-day" style="color: #7a7a9d;"></i>
                            <span>${escapeHtml(u.membro_desde)}</span>
                            <span style="color: #7a7a9d; margin: 0 4px;">•</span>
                            <span id="pmodalSeguidoresCount">${u.seguidores} seguidores</span>
                        </div>
                        
                        ${redesHtml ? `<div class="pmodal-socials-row">${redesHtml}</div>` : ''}
                    </div>
                </div>

                <!-- ESTATÍSTICAS (4 CARDS) -->
                <div class="pmodal-stats-grid">
                    <div class="pmodal-stat-card">
                        <div class="pmodal-stat-icon" style="color: #ffc800;"><i class="fa-solid fa-bolt"></i></div>
                        <div class="pmodal-stat-val">${Number(u.xp).toLocaleString('pt-BR')}</div>
                        <div class="pmodal-stat-lbl">Total XP</div>
                    </div>
                    <div class="pmodal-stat-card">
                        <div class="pmodal-stat-icon" style="color: ${ligaCfg.corClara};"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="pmodal-stat-val" style="color: ${ligaCfg.corClara};">${escapeHtml(u.divisao)}</div>
                        <div class="pmodal-stat-lbl">Divisão</div>
                    </div>
                    <div class="pmodal-stat-card">
                        <div class="pmodal-stat-icon" style="color: ${u.fogo_hoje ? '#ff9600' : '#8b949e'};"><i class="fa-solid fa-fire"></i></div>
                        <div class="pmodal-stat-val">${u.dias_fogo} dias</div>
                        <div class="pmodal-stat-lbl">Ofensiva</div>
                    </div>
                    <div class="pmodal-stat-card">
                        <div class="pmodal-stat-icon" style="color: #58cc02;"><i class="fa-solid fa-trophy"></i></div>
                        <div class="pmodal-stat-val">${u.trofeus}</div>
                        <div class="pmodal-stat-lbl">Troféus</div>
                    </div>
                </div>

                <!-- PROJETOS GIT -->
                <div class="pmodal-section">
                    <div class="pmodal-sec-title">
                        <i class="fa-brands fa-git-alt" style="color: #f34f29;"></i> Projetos no Git
                    </div>
                    ${projetosHtml}
                </div>

                <!-- CONQUISTAS -->
                <div class="pmodal-section" style="padding-bottom: 26px;">
                    <div class="pmodal-sec-title">
                        <i class="fa-solid fa-award" style="color: #ffc800;"></i> Conquistas e Troféus
                    </div>
                    ${conquistasHtml}
                </div>
            `;
        } catch (err) {
            console.error("Erro ao carregar perfil:", err);
            body.innerHTML = `
                <div style="padding: 40px 20px; text-align: center; color: #ff8080;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <div style="font-weight: 800; font-size: 1.1rem;">Falha na conexão ao carregar o perfil.</div>
                </div>
            `;
        }
    };

    window.toggleSeguirNoModal = async function(targetId) {
        const btn = document.getElementById(`btnSeguirModal_${targetId}`);
        if (!btn) return;

        const fd = new FormData();
        fd.append('action', 'toggle_seguir');
        fd.append('target_id', targetId);

        try {
            const res = await fetch('../../back/api/amigos.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                if (data.is_seguindo) {
                    btn.classList.add('following');
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Seguindo</span>';
                } else {
                    btn.classList.remove('following');
                    btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> <span>Seguir</span>';
                }
                const segCount = document.getElementById('pmodalSeguidoresCount');
                if (segCount && typeof data.seguidores_count !== 'undefined') {
                    segCount.innerText = `${data.seguidores_count} seguidores`;
                }
            }
        } catch (e) {
            console.error("Erro ao alternar seguir:", e);
        }
    };

    window.solicitarSeguirNoModal = async function(targetId, nome) {
        const btn = document.getElementById(`btnSeguirModal_${targetId}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Enviando...</span>';
        }
        const fd = new FormData();
        fd.append('action', 'solicitar_seguir');
        fd.append('target_id', targetId);

        try {
            const res = await fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                if (btn) {
                    btn.className = 'pmodal-btn-seguir pending';
                    btn.disabled = false;
                    btn.setAttribute('onclick', `cancelarSolicitacaoNoModal(${targetId}, '${escapeHtml(nome)}')`);
                    btn.setAttribute('title', 'Clique para cancelar solicitação');
                    btn.innerHTML = '<i class="fa-solid fa-clock"></i> <span>Solicitação Enviada</span>';
                }
                if (typeof window.opusToast === 'function') {
                    window.opusToast(`Solicitação enviada para ${nome}! Ele precisa aceitar.`, 'success');
                } else {
                    alert(`Solicitação enviada para ${nome}!`);
                }
                if (typeof window.fetchNotificacoesBadge === 'function') {
                    window.fetchNotificacoesBadge();
                }
            } else {
                if (btn) btn.disabled = false;
            }
        } catch (e) {
            console.error('Erro ao solicitar seguir:', e);
            if (btn) btn.disabled = false;
        }
    };

    window.cancelarSolicitacaoNoModal = async function(targetId, nome) {
        const btn = document.getElementById(`btnSeguirModal_${targetId}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Cancelando...</span>';
        }

        const fd = new FormData();
        fd.append('action', 'cancelar_solicitacao');
        fd.append('target_id', targetId);

        try {
            const res = await fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                if (btn) {
                    btn.className = 'pmodal-btn-seguir';
                    btn.disabled = false;
                    btn.setAttribute('onclick', `solicitarSeguirNoModal(${targetId}, '${escapeHtml(nome)}')`);
                    btn.setAttribute('title', 'Seguir');
                    btn.innerHTML = '<i class="fa-solid fa-user-plus"></i> <span>Seguir</span>';
                }
                if (typeof window.opusToast === 'function') {
                    window.opusToast(`Solicitação para ${nome} cancelada.`, 'info');
                } else {
                    alert(`Solicitação para ${nome} cancelada.`);
                }
                if (typeof window.fetchNotificacoesBadge === 'function') {
                    window.fetchNotificacoesBadge();
                }
            } else {
                if (btn) btn.disabled = false;
            }
        } catch (e) {
            console.error('Erro ao cancelar solicitação:', e);
            if (btn) btn.disabled = false;
        }
    };

    window.abrirPerguntaAmigoModal = function(reqId, targetId, nome) {
        const box = document.getElementById(`boxAcaoRecebido_${targetId}`);
        if (!box) return;
        const nomeEsc = nome.replace(/'/g, "\\'");
        box.innerHTML = `
            <div class="notif-ask-box" style="margin-top: 4px; width: 100%; text-align: left;">
                <div class="notif-ask-title"><i class="fa-solid fa-user-group"></i> Quer ser amigo?</div>
                <div class="notif-ask-sub">Siga <strong>${escapeHtml(nome)}</strong> de volta para se tornarem amigos e liberarem mensagens!</div>
                <div style="display:flex; gap:6px; margin-top:6px;">
                    <button type="button" class="notif-btn notif-btn-accept" style="padding:6px 10px; font-size:11px;" onclick="aceitarPedidoNoModal(${reqId}, ${targetId}, '${nomeEsc}', 1)">
                        <i class="fa-solid fa-check"></i> Sim, ser amigos
                    </button>
                    <button type="button" class="notif-btn notif-btn-neutral" style="padding:6px 10px; font-size:11px;" onclick="aceitarPedidoNoModal(${reqId}, ${targetId}, '${nomeEsc}', 0)">
                        Apenas aceitar
                    </button>
                </div>
            </div>
        `;
    };

    window.aceitarPedidoNoModal = async function(reqId, remetenteId, nome, seguirDeVolta = 1) {
        const fd = new FormData();
        fd.append('action', 'respond_request');
        fd.append('id', reqId);
        fd.append('response', 'aceito');
        fd.append('seguir_de_volta', seguirDeVolta);

        try {
            const res = await fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                if (typeof window.opusToast === 'function') {
                    if (seguirDeVolta === 1) {
                        window.opusToast(`Você e ${nome} agora são amigos!`, 'success');
                    } else {
                        window.opusToast(`${nome} agora está te seguindo.`, 'info');
                    }
                }
                if (typeof window.fetchNotificacoesBadge === 'function') {
                    window.fetchNotificacoesBadge();
                }
                if (seguirDeVolta === 1) {
                    window.location.href = `chat.php?user_id=${remetenteId}`;
                } else {
                    abrirPerfilPublico(remetenteId);
                }
            }
        } catch (e) {
            console.error('Erro ao aceitar pedido:', e);
        }
    };

    window.seguirDeVoltaNoModal = async function(targetId, nome) {
        const btn = document.getElementById(`btnSeguirDeVolta_${targetId}`);
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Conectando...</span>';
        }
        const fd = new FormData();
        fd.append('action', 'seguir_de_volta');
        fd.append('target_id', targetId);

        try {
            const res = await fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                if (typeof window.opusToast === 'function') {
                    window.opusToast(`Você seguiu ${nome} de volta! Vocês agora são amigos.`, 'success');
                }
                if (typeof window.fetchNotificacoesBadge === 'function') {
                    window.fetchNotificacoesBadge();
                }
                window.location.href = `chat.php?user_id=${targetId}`;
            }
        } catch (e) {
            console.error('Erro ao seguir de volta:', e);
        }
    };

    window.toggleSeguirNoModal = async function(targetId) {
        const btn = document.getElementById(`btnSeguirModal_${targetId}`);
        if (!btn) return;

        const fd = new FormData();
        fd.append('action', 'toggle_seguir');
        fd.append('target_id', targetId);

        try {
            const res = await fetch('../../back/api/amigos.php', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) {
                abrirPerfilPublico(targetId);
            }
        } catch (e) {
            console.error("Erro ao alternar seguir:", e);
        }
    };
})();
