let currentChatUser = 0;
let chatRefreshInterval;
let activeChatData = null;

function renderAvatarChat(foto, nome, extraClass = '') {
    if (foto && String(foto).trim() !== '') {
        return `<img src="${foto}" class="${extraClass}" alt="${escapeHtml(nome)}">`;
    }
    return `<div class="chat-avatar-empty ${extraClass}"><i class="fa-solid fa-user"></i></div>`;
}

// ── 1. CARREGAR CONVERSAS DO DIRECT ──
function carregarConversas() {
    fetch('../../back/api/chat.php?action=get_conversations')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('chat-list');
            if (!list) return;
            if (!Array.isArray(data) || data.length === 0) {
                list.innerHTML = `
                    <div style="padding: 40px 20px; text-align: center; color: #94a3b8; font-size: 0.9rem;">
                        <i class="fa-regular fa-paper-plane" style="font-size: 2.2rem; color: #3b3b4f; margin-bottom: 12px; display: block;"></i>
                        Nenhuma conversa iniciada.<br>Busque por <strong>Nome</strong> ou <strong>E-mail</strong> para conversar!
                    </div>`;
                return;
            }
            
            let html = '';
            data.forEach(c => {
                const unreadHtml = c.unread > 0 ? `<div class="cli-unread">${c.unread}</div>` : '';
                const activeClass = c.id === currentChatUser ? 'active' : '';
                
                let ultima = c.ultima_msg || 'Nova conversa iniciada. Diga olá!';

                let timeStr = '';
                if (c.ultima_data) {
                    const d = new Date(c.ultima_data);
                    timeStr = formatarHoraDirect(d);
                }
                
                html += `
                <div class="chat-list-item ${activeClass}" onclick="abrirChat(${c.id})">
                    <div class="cli-avatar-wrap">
                        ${renderAvatarChat(c.foto, c.nome)}
                    </div>
                    <div class="cli-info">
                        <div class="cli-top-row">
                            <span class="cli-name">${c.nome}</span>
                            <span class="cli-time">${timeStr}</span>
                        </div>
                        <div class="cli-bottom-row">
                            <span class="cli-last">${escapeHtml(ultima)}</span>
                            ${unreadHtml}
                        </div>
                    </div>
                </div>
                `;
            });
            list.innerHTML = html;
        })
        .catch(err => {
            console.error("Erro ao carregar conversas:", err);
        });
}

// ── 2. BUSCA DE AMIGOS POR NOME OU E-MAIL ──
function buscarAmigos() {
    const q = document.getElementById('search-input').value.trim();
    const resBox = document.getElementById('search-results');
    
    if (q === '') {
        resBox.style.display = 'none';
        return;
    }
    
    resBox.style.display = 'block';
    resBox.innerHTML = '<div style="text-align:center; color:#94a3b8; font-size:0.85rem;"><i class="fa-solid fa-spinner fa-spin"></i> Buscando...</div>';
    
    fetch('../../back/api/chat.php?action=search_users&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                resBox.innerHTML = '<div style="text-align:center; color:#94a3b8; font-size:0.85rem; padding: 6px;">Nenhum usuário encontrado por esse nome ou e-mail.</div>';
                return;
            }
            
            let html = '';
            data.forEach(u => {
                let btnHtml = '';
                if (u.status === 'pendente') {
                    btnHtml = '<button class="btn-add-friend" disabled style="background:#4a5568; border-color:#333;">Pendente</button>';
                } else if (u.status === 'aceito') {
                    btnHtml = `<button class="btn-add-friend" onclick="abrirChat(${u.id}); document.getElementById('search-results').style.display='none';"><i class="fa-solid fa-comments"></i> Conversar</button>`;
                } else {
                    btnHtml = `<button class="btn-add-friend" onclick="enviarPedido(${u.id})"><i class="fa-solid fa-user-plus"></i> Conectar</button>`;
                }
                
                html += `
                <div class="search-result-item">
                    <div class="sri-info">
                        ${renderAvatarChat(u.foto, u.nome)}
                        <div class="sri-details">
                            <span class="sri-name">${u.nome}</span>
                            <span class="sri-email">${u.email}</span>
                        </div>
                    </div>
                    ${btnHtml}
                </div>`;
            });
            resBox.innerHTML = html;
        })
        .catch(() => {
            resBox.innerHTML = '<div style="text-align:center; color:#ff4b4b; font-size:0.85rem;">Erro na busca.</div>';
        });
}

function enviarPedido(id) {
    const fd = new FormData();
    fd.append('action', 'send_request');
    fd.append('target_id', id);
    fetch('../../back/api/chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                if (typeof window.opusToast === 'function') {
                    window.opusToast("Solicitação de amizade enviada!", "success");
                } else {
                    alert("Solicitação de amizade enviada!");
                }
                buscarAmigos();
            }
        });
}

// ── 3. ABRIR E ATUALIZAR JANELA DO DIRECT ──
function abrirChat(userId) {
    currentChatUser = userId;
    carregarConversas(); // Atualiza o item ativo na lista
    atualizarChatWindow(true);
    
    if (chatRefreshInterval) clearInterval(chatRefreshInterval);
    chatRefreshInterval = setInterval(() => atualizarChatWindow(false), 3000);
}

function atualizarChatWindow(isFirstOpen = false) {
    if (currentChatUser === 0) return;
    
    fetch('../../back/api/chat.php?action=get_messages&target_id=' + currentChatUser)
        .then(r => r.json())
        .then(data => {
            if (data.error) return;
            activeChatData = data;
            
            const f = data.friend;
            const msgs = data.messages || [];
            const main = document.getElementById('chat-main');
            
            // Renderiza o cabeçalho e esqueleto se for a primeira vez ou trocou de conversa
            const currentHeader = document.getElementById('chat-header-' + f.id);
            if (!currentHeader) {
                main.innerHTML = `
                <!-- CABEÇALHO DO DIRECT -->
                <div class="chat-header" id="chat-header-${f.id}">
                    <div class="ch-user">
                        <div class="ch-avatar-wrap" id="ch-avatar-wrap-${f.id}">
                            ${renderAvatarChat(f.foto, f.nome)}
                        </div>
                        <div class="ch-user-info">
                            <h3>${escapeHtml(f.nome)}</h3>
                            <div class="ch-user-subtitle">
                                <span class="ch-status-online"><i class="fa-solid fa-circle"></i> Direto no Opus</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MENSAGENS -->
                <div class="chat-messages" id="chat-messages"></div>

                <!-- BARRA DE ENVIO DE MENSAGEM (DIRECT) -->
                <div class="chat-input-area">
                    <div class="chat-textarea-wrap">
                        <input type="text" id="chat-input" placeholder="Mensagem..." autocomplete="off" onkeydown="handleKeyDown(event)">
                    </div>
                    <button type="button" class="btn-send" onclick="enviarMensagem()" title="Enviar mensagem">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
                `;
                
                setTimeout(() => {
                    const inp = document.getElementById('chat-input');
                    if (inp) inp.focus();
                }, 100);
            } else {
                const avWrap = document.getElementById('ch-avatar-wrap-' + f.id);
                if (avWrap) avWrap.innerHTML = renderAvatarChat(f.foto, f.nome);
            }

            // Renderiza mensagens estilo Direct
            let msgHtml = '';
            msgs.forEach(m => {
                const isMe = (m.remetente_id !== f.id);
                const rowClass = isMe ? 'me' : 'them';
                
                let timeStr = '';
                if (m.data) {
                    const d = new Date(m.data);
                    timeStr = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                }

                // Status de visualização na hora que enviou a mensagem (Direct)
                let statusIcon = '';
                if (isMe) {
                    if (m.status === 'lido') {
                        statusIcon = '<span class="msg-status read" title="Visualizado"><i class="fa-solid fa-check-double"></i></span>';
                    } else if (m.status === 'entregue') {
                        statusIcon = '<span class="msg-status delivered" title="Entregue"><i class="fa-solid fa-check-double"></i></span>';
                    } else {
                        statusIcon = '<span class="msg-status sent" title="Enviado"><i class="fa-solid fa-check"></i></span>';
                    }
                }
                
                let text = escapeHtml(m.mensagem || '');

                msgHtml += `
                <div class="msg-row ${rowClass}">
                    <div class="msg-bubble">
                        <span class="msg-text">${text}</span>
                        <div class="msg-meta">
                            <span class="msg-time">${timeStr}</span>
                            ${statusIcon}
                        </div>
                    </div>
                </div>`;
            });

            const msgContainer = document.getElementById('chat-messages');
            if (msgContainer) {
                const wasScrolledToBottom = (msgContainer.scrollHeight - msgContainer.scrollTop <= msgContainer.clientHeight + 80);
                msgContainer.innerHTML = msgHtml;
                if (isFirstOpen || wasScrolledToBottom) {
                    msgContainer.scrollTop = msgContainer.scrollHeight;
                }
            }
        })
        .catch(err => console.error("Erro ao atualizar direct window:", err));
}

function handleKeyDown(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        enviarMensagem();
    }
}

function enviarMensagem() {
    const input = document.getElementById('chat-input');
    if (!input) return;
    const msg = input.value.trim();
    if (msg === '' || currentChatUser === 0) return;
    
    // Limpa o input imediatamente
    input.value = '';

    // Renderização otimista imediata no balão
    const msgContainer = document.getElementById('chat-messages');
    if (msgContainer) {
        const timeNow = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const optimHtml = `
            <div class="msg-row me sending-optimistic">
                <div class="msg-bubble">
                    <span class="msg-text">${escapeHtml(msg)}</span>
                    <div class="msg-meta">
                        <span class="msg-time">${timeNow}</span>
                        <span class="msg-status sent" title="Enviado"><i class="fa-solid fa-check"></i></span>
                    </div>
                </div>
            </div>`;
        msgContainer.insertAdjacentHTML('beforeend', optimHtml);
        msgContainer.scrollTop = msgContainer.scrollHeight;
    }
    
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('target_id', currentChatUser);
    fd.append('mensagem', msg);
    
    fetch('../../back/api/chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                atualizarChatWindow(false);
                carregarConversas();
            }
        })
        .catch(err => console.error("Erro ao enviar mensagem:", err));
}

// ── 4. FORMATADORES & HELPERS ──
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function formatarHoraDirect(date) {
    const hoje = new Date();
    const isSameDay = hoje.toDateString() === date.toDateString();
    if (isSameDay) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return date.toLocaleDateString([], { day: '2-digit', month: '2-digit' });
}

// ── 5. ABAS DE CONVERSAS & NOTIFICAÇÕES ──
let currentChatTab = 'conversas';

function alternarAbaChat(aba) {
    currentChatTab = aba;
    const btnConv = document.getElementById('tabBtnConversas');
    const btnNotif = document.getElementById('tabBtnNotificacoes');
    const panelConv = document.getElementById('tabPanelConversas');
    const panelNotif = document.getElementById('tabPanelNotificacoes');

    if (aba === 'conversas') {
        if (btnConv) btnConv.classList.add('active');
        if (btnNotif) btnNotif.classList.remove('active');
        if (panelConv) { panelConv.classList.add('active'); panelConv.style.display = 'flex'; }
        if (panelNotif) { panelNotif.classList.remove('active'); panelNotif.style.display = 'none'; }
    } else {
        if (btnConv) btnConv.classList.remove('active');
        if (btnNotif) btnNotif.classList.add('active');
        if (panelConv) { panelConv.classList.remove('active'); panelConv.style.display = 'none'; }
        if (panelNotif) { panelNotif.classList.add('active'); panelNotif.style.display = 'flex'; }
        carregarNotificacoesChat();
    }
}

function carregarNotificacoesChat() {
    fetch('../../back/api/notificacoes.php?action=get_notificacoes')
        .then(res => res.json())
        .then(data => {
            if (data.error) return;

            const listaElem = document.getElementById('notif-chat-list');
            const tabBadge = document.getElementById('tabBadgeNotif');
            const banner = document.getElementById('pending-requests-banner');
            const bannerCount = document.getElementById('pending-requests-count');
            const topbarBadge = document.getElementById('badge-notif');

            const friendRequests = (data.notificacoes || []).filter(n => n.type === 'friend_request');
            const totalPendentes = friendRequests.length;

            if (tabBadge) {
                if (totalPendentes > 0) {
                    tabBadge.innerText = totalPendentes;
                    tabBadge.style.display = 'inline-block';
                } else {
                    tabBadge.style.display = 'none';
                }
            }

            if (topbarBadge) {
                const pendingNotifs = (data.notificacoes || []).filter(n => n.type !== 'unread_messages').length;
                if (pendingNotifs > 0) {
                    topbarBadge.innerText = pendingNotifs > 99 ? '99+' : pendingNotifs;
                    topbarBadge.style.display = 'inline-flex';
                } else {
                    topbarBadge.style.display = 'none';
                }
            }

            if (banner && bannerCount) {
                if (totalPendentes > 0) {
                    bannerCount.innerText = totalPendentes === 1 ? '1 pedido' : `${totalPendentes} pedidos`;
                    banner.style.display = 'flex';
                } else {
                    banner.style.display = 'none';
                }
            }

            if (!listaElem) return;

            if (!data.notificacoes || data.notificacoes.length === 0) {
                listaElem.innerHTML = `
                    <div class="notif-empty-state">
                        <i class="fa-regular fa-bell-slash"></i>
                        <p>Nenhuma notificação ou pedido pendente no momento.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.notificacoes.forEach(n => {
                const avatarHtml = renderAvatarChat(n.foto, n.nome, 'notif-card-avatar');
                if (n.type === 'friend_request') {
                    const nomeEsc = n.nome.replace(/'/g, "\\'");
                    html += `
                    <div class="notif-card" id="notif-req-${n.id}">
                        <div class="notif-card-header">
                            ${avatarHtml}
                            <div class="notif-card-info">
                                <div class="notif-card-name">${n.nome}</div>
                                <div class="notif-card-desc"><i class="fa-solid fa-user-plus" style="color:#1cb0f6;"></i> Enviou um pedido de amizade</div>
                            </div>
                        </div>
                        <div class="notif-card-actions" id="actions-chat-req-${n.id}">
                            <button class="btn-notif-action btn-notif-accept" onclick="responderPedidoChat(${n.id}, 'aceito', '${nomeEsc}', ${n.remetente_id}, 1)">
                                <i class="fa-solid fa-check"></i> Aceitar
                            </button>
                            <button class="btn-notif-action btn-notif-decline" onclick="responderPedidoChat(${n.id}, 'recusado', '${nomeEsc}', ${n.remetente_id}, 0)">
                                <i class="fa-solid fa-xmark"></i> Recusar
                            </button>
                        </div>
                    </div>`;
                } else if (n.type === 'friend_accepted') {
                    html += `
                    <div class="notif-card" style="border-left: 4px solid #58cc02;">
                        <div class="notif-card-header">
                            ${avatarHtml}
                            <div class="notif-card-info">
                                <div class="notif-card-name">${n.nome}</div>
                                <div class="notif-card-desc" style="color: #58cc02; font-weight: 700;">
                                    <i class="fa-solid fa-circle-check"></i> Aceitou seu pedido! Vocês agora são amigos.
                                </div>
                            </div>
                        </div>
                        <div class="notif-card-actions">
                            <button class="btn-notif-action btn-notif-accept" onclick="abrirChatEFecharNotif(${n.amigo_id}, ${n.id})">
                                <i class="fa-solid fa-comments"></i> Conversar
                            </button>
                        </div>
                    </div>`;
                } else if (n.type === 'unread_messages') {
                    html += `
                    <div class="notif-card" style="cursor: pointer;" onclick="abrirChat(${n.remetente_id}); alternarAbaChat('conversas');">
                        <div class="notif-card-header">
                            ${avatarHtml}
                            <div class="notif-card-info">
                                <div class="notif-card-name">${n.nome}</div>
                                <div class="notif-card-desc"><i class="fa-solid fa-message" style="color:#1cb0f6;"></i> Enviou <strong>${n.qtd}</strong> nova(s) mensagem(ns)</div>
                            </div>
                        </div>
                    </div>`;
                }
            });

            listaElem.innerHTML = html;
        })
        .catch(() => {});
}

function abrirChatEFecharNotif(amigoId, notifId) {
    if (notifId) {
        const fd = new FormData();
        fd.append('action', 'dismiss_accepted');
        fd.append('id', notifId);
        fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd }).catch(() => {});
    }
    abrirChat(amigoId);
    alternarAbaChat('conversas');
    if (typeof window.fetchNotificacoesBadge === 'function') {
        window.fetchNotificacoesBadge();
    }
}

function responderPedidoChat(id, response, nome = '', remetenteId = null, seguirDeVolta = 1) {
    const fd = new FormData();
    fd.append('action', 'respond_request');
    fd.append('id', id);
    fd.append('response', response);
    fd.append('seguir_de_volta', seguirDeVolta);

    fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof window.opusToast === 'function') {
                    if (response === 'aceito') {
                        window.opusToast(`Você e ${nome} agora são amigos!`, 'success');
                    } else {
                        window.opusToast(`Pedido recusado.`, 'info');
                    }
                }
                carregarNotificacoesChat();
                carregarConversas();
                if (typeof window.fetchNotificacoesBadge === 'function') {
                    window.fetchNotificacoesBadge();
                }

                if (response === 'aceito' && remetenteId) {
                    abrirChat(remetenteId);
                    alternarAbaChat('conversas');
                }
            }
        });
}

// ── 6. INICIALIZAÇÃO ──
document.addEventListener('DOMContentLoaded', () => {
    carregarConversas();
    carregarNotificacoesChat();

    const urlParams = new URLSearchParams(window.location.search);
    const targetUserId = urlParams.get('user_id') || urlParams.get('user');
    if (targetUserId) {
        abrirChat(parseInt(targetUserId));
    }
    if (urlParams.get('tab') === 'notificacoes') {
        alternarAbaChat('notificacoes');
    }

    // Polling regular das conversas e notificações
    setInterval(() => {
        carregarConversas();
        carregarNotificacoesChat();
    }, 10000);
});
