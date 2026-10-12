let currentChatUser = 0;
let chatRefreshInterval;

function carregarConversas() {
    fetch('../../back/api/chat.php?action=get_conversations')
        .then(r => r.json())
        .then(data => {
            const list = document.getElementById('chat-list');
            if (data.length === 0) {
                list.innerHTML = '<div style="padding:20px; text-align:center; color:#94a3b8;">Nenhum amigo ainda. Use a busca acima para adicionar conexões!</div>';
                return;
            }
            
            let html = '';
                            data.forEach(c => {
                    let unreadHtml = c.unread > 0 ? `<div class="cli-unread">${c.unread}</div>` : '';
                    let activeClass = c.id === currentChatUser ? 'active' : '';
                    
                    let ultima = c.ultima_msg;
                    if (ultima && ultima.includes('[DUEL_INVITE]')) ultima = '🔥 Convite para Batalha de Fogo';
                    
                    let battleIcon = c.is_battling ? `<div title="Batalha Ativa!" style="color:#ff0055; margin-left:5px;"><i class="fa-solid fa-fire-flame-curved"></i></div>` : '';
                    
                    html += `
                    <div class="chat-list-item ${activeClass}" onclick="abrirChat(${c.id})">
                        <img src="${c.foto}">
                        <div class="cli-info">
                            <div class="cli-name" style="display:flex; align-items:center;">${c.nome} ${battleIcon}</div>
                            <div class="cli-last">${ultima}</div>
                        </div>
                        ${unreadHtml}
                    </div>
                    `;
                });
            list.innerHTML = html;
        });
}

function buscarAmigos() {
    const q = document.getElementById('search-input').value.trim();
    if (q === '') {
        document.getElementById('search-results').style.display = 'none';
        return;
    }
    
    fetch('../../back/api/chat.php?action=search_users&q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
            const resBox = document.getElementById('search-results');
            resBox.style.display = 'block';
            
            if (data.length === 0) {
                resBox.innerHTML = '<div style="text-align:center; color:#94a3b8;">Nenhum usuário encontrado.</div>';
                return;
            }
            
            let html = '';
            data.forEach(u => {
                let btnHtml = '';
                if (u.status === 'pendente') btnHtml = '<button class="btn-add-friend" disabled style="background:#4a5568;">Pendente</button>';
                else if (u.status === 'aceito') btnHtml = '<button class="btn-add-friend" onclick="abrirChat(' + u.id + ')">Abrir Chat</button>';
                else btnHtml = `<button class="btn-add-friend" onclick="enviarPedido(${u.id})"><i class="fa-solid fa-user-plus"></i> Adicionar</button>`;
                
                html += `
                <div class="search-result-item">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="${u.foto}" style="width:30px; height:30px; border-radius:50%;">
                        <span style="font-weight:700; font-size:0.9rem;">${u.nome}</span>
                    </div>
                    ${btnHtml}
                </div>`;
            });
            resBox.innerHTML = html;
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
                alert("Pedido de amizade enviado!");
                buscarAmigos();
            }
        });
}

function abrirChat(userId) {
    currentChatUser = userId;
    carregarConversas(); // Atualiza active state
    atualizarChatWindow();
    
    if (chatRefreshInterval) clearInterval(chatRefreshInterval);
    chatRefreshInterval = setInterval(atualizarChatWindow, 5000);
}

function atualizarChatWindow() {
    if (currentChatUser === 0) return;
    
    fetch('../../back/api/chat.php?action=get_messages&target_id=' + currentChatUser)
        .then(r => r.json())
        .then(data => {
            const f = data.friend;
            const msgs = data.messages;
            
            let duelFloatingIcon = data.has_active_duel && data.duel_info ? `
                <div class="floating-duel-icon" onclick="alert('${data.duel_info.msg}')" title="Placar da Batalha de Fogo" style="background:#ff0055; box-shadow:0 0 10px #ff0055;">
                    <i class="fa-solid fa-fire"></i>
                </div>
            ` : '';
            
            const main = document.getElementById('chat-main');
            
            // Build the skeleton ONLY if we just opened this chat
            if (!document.getElementById('chat-header-' + f.id)) {
                main.innerHTML = `
                <div class="chat-header" id="chat-header-${f.id}">
                    <div class="ch-user">
                        <img src="${f.foto}">
                        <div class="ch-user-info">
                            <h3>${f.nome}</h3>
                        </div>
                    </div>
                    <button class="btn-duel" onclick="enviarConviteDuelo(${f.id})"><i class="fa-solid fa-fire"></i> Convidar p/ Batalha</button>
                </div>
                <div class="chat-messages" id="chat-messages"></div>
                <div class="chat-input-area">
                    <textarea id="chat-input" placeholder="Digite uma mensagem..." onkeypress="handleEnter(event)" style="resize:none; padding:12px; border-radius:8px; border:2px solid #2e2e42; background:#161a24; color:#fff; font-family:'Nunito',sans-serif; flex:1; height:20px; outline:none; overflow:hidden;"></textarea>
                    <button class="btn-send" onclick="enviarMensagem()"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
                `;
                setTimeout(() => {
                    if(document.getElementById('chat-input')) {
                        document.getElementById('chat-input').focus();
                    }
                }, 100);
            }

            let msgHtml = '';
            msgs.forEach(m => {
                let isMe = (m.remetente_id !== f.id);
                let rowClass = isMe ? 'me' : 'them';
                
                if (m.mensagem === '[DUEL_INVITE]') {
                    let txt = isMe ? "Você convidou para uma batalha de fogo!" : "Te convidou para uma batalha de fogo!";
                    msgHtml += `
                    <div class="msg-row ${rowClass}">
                        <div class="msg-duel" onclick="aceitarBatalha(${f.id})">
                            <i class="fa-solid fa-fire msg-duel-icon"></i>
                            <div class="msg-duel-text">
                                <span>Batalha de Fogo</span>
                                <span>${txt}</span>
                            </div>
                        </div>
                    </div>`;
                } else {
                    let text = m.mensagem.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    msgHtml += `
                    <div class="msg-row ${rowClass}">
                        <div class="msg-bubble">${text}</div>
                    </div>`;
                }
            });
            
            // Atualizar ícone de duelo se existir
            const headerActions = document.querySelector('#chat-header-' + f.id);
            if (headerActions) {
                let existingDuel = document.getElementById('duel-badge');
                if (existingDuel) existingDuel.remove();
                
                if (data.has_active_duel && data.duel_info) {
                    // Ocultar botão convidar se já tiver batalha
                    let btnConvidar = headerActions.querySelector('.btn-duel');
                    if(btnConvidar) btnConvidar.style.display = 'none';
                    
                    let badge = document.createElement('div');
                    badge.id = 'duel-badge';
                    badge.style.cssText = 'background:#ff0055; color:white; padding:8px 15px; border-radius:8px; cursor:pointer; font-weight:bold; box-shadow:0 0 10px #ff0055; margin-left: auto; display: flex; align-items: center; gap: 8px; font-size: 0.9rem;';
                    badge.innerHTML = `<i class="fa-solid fa-fire"></i> Placar Batalha`;
                    badge.onclick = () => alert(data.duel_info.msg);
                    headerActions.appendChild(badge);
                }
            }

            const msgContainer = document.getElementById('chat-messages');
            if (msgContainer) {
                // Only autoscroll if user was already at the bottom
                const wasScrolledToBottom = (msgContainer.scrollHeight - msgContainer.scrollTop <= msgContainer.clientHeight + 50);
                
                msgContainer.innerHTML = msgHtml;
                
                if (wasScrolledToBottom) {
                    msgContainer.scrollTop = msgContainer.scrollHeight;
                }
            }
        });
}

function handleEnter(e) {
    if (e.key === 'Enter') enviarMensagem();
}

function enviarMensagem() {
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if (msg === '' || currentChatUser === 0) return;
    
    input.value = '';
    
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('target_id', currentChatUser);
    fd.append('mensagem', msg);
    
    fetch('../../back/api/chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                atualizarChatWindow();
                carregarConversas();
            }
        });
}

function enviarConviteDuelo(id) {
    const fd = new FormData();
    fd.append('action', 'send_message');
    fd.append('target_id', id);
    fd.append('mensagem', '[DUEL_INVITE]');
    
    fetch('../../back/api/chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                atualizarChatWindow();
                carregarConversas();
            }
        });
}




function aceitarBatalha(amigoId) {
    const fd = new FormData();
    fd.append('action', 'desafiar');
    fd.append('amigo_id', amigoId);
    fetch('../../back/api/batalha.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(d => {
        if(d.success) {
            alert('Batalha iniciada! O XP já começou a contar!');
            atualizarChatWindow();
        } else {
            alert(d.msg || 'Erro');
        }
    });
}


// ==========================================
// INTEGRAÇÃO DE NOTIFICAÇÕES & ABAS NO CHAT
// ==========================================
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

            // Atualiza badge da aba de Notificações
            if (tabBadge) {
                if (totalPendentes > 0) {
                    tabBadge.innerText = totalPendentes;
                    tabBadge.style.display = 'inline-block';
                } else {
                    tabBadge.style.display = 'none';
                }
            }

            // Atualiza badge da topbar
            if (topbarBadge) {
                if (data.count > 0) {
                    topbarBadge.innerText = data.count > 99 ? '99+' : data.count;
                    topbarBadge.style.display = 'block';
                } else {
                    topbarBadge.style.display = 'none';
                }
            }

            // Atualiza banner na aba Conversas
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
                if (n.type === 'friend_request') {
                    html += `
                    <div class="notif-card" id="notif-req-${n.id}">
                        <div class="notif-card-header">
                            <img src="${n.foto}" class="notif-card-avatar" alt="${n.nome}">
                            <div class="notif-card-info">
                                <div class="notif-card-name">${n.nome}</div>
                                <div class="notif-card-desc">Enviou um pedido de conexão</div>
                            </div>
                        </div>
                        <div class="notif-card-actions">
                            <button class="btn-notif-action btn-notif-accept" onclick="responderPedidoChat(${n.id}, 'aceito', '${n.nome.replace(/'/g, "\\'")}', ${n.remetente_id})">
                                <i class="fa-solid fa-check"></i> Aceitar
                            </button>
                            <button class="btn-notif-action btn-notif-decline" onclick="responderPedidoChat(${n.id}, 'recusado', '${n.nome.replace(/'/g, "\\'")}')">
                                <i class="fa-solid fa-xmark"></i> Recusar
                            </button>
                        </div>
                    </div>`;
                } else if (n.type === 'unread_messages') {
                    html += `
                    <div class="notif-card" style="cursor: pointer;" onclick="abrirChat(${n.remetente_id}); alternarAbaChat('conversas');">
                        <div class="notif-card-header">
                            <img src="${n.foto}" class="notif-card-avatar" alt="${n.nome}">
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

function responderPedidoChat(id, response, nome = '', remetenteId = null) {
    const fd = new FormData();
    fd.append('action', 'respond_request');
    fd.append('id', id);
    fd.append('response', response);

    fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof window.opusToast === 'function') {
                    if (response === 'aceito') {
                        window.opusToast(`Pedido de ${nome} aceito! Vocês agora são conexões.`, 'success');
                    } else {
                        window.opusToast(`Pedido de ${nome} recusado.`, 'info');
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
            } else {
                if (typeof window.opusToast === 'function') {
                    window.opusToast('Não foi possível responder ao pedido.', 'error');
                }
            }
        })
        .catch(() => {
            if (typeof window.opusToast === 'function') {
                window.opusToast('Erro ao processar resposta.', 'error');
            }
        });
}

// Inicialização e URL params
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

    // Atualiza conversas e notificações periodicamente a cada 15s
    setInterval(() => {
        carregarConversas();
        carregarNotificacoesChat();
    }, 15000);
});
