let currentChatUser = 0;
let chatRefreshInterval;

function carregarConversas() {
    fetch('../../back/api_chat.php?action=get_conversations')
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
    
    fetch('../../back/api_chat.php?action=search_users&q=' + encodeURIComponent(q))
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
    fetch('../../back/api_chat.php', { method: 'POST', body: fd })
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
    
    fetch('../../back/api_chat.php?action=get_messages&target_id=' + currentChatUser)
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
    
    fetch('../../back/api_chat.php', { method: 'POST', body: fd })
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
    
    fetch('../../back/api_chat.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                atualizarChatWindow();
                carregarConversas();
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    carregarConversas();
    const urlParams = new URLSearchParams(window.location.search);
    const targetUserId = urlParams.get('user_id');
    if (targetUserId) {
        abrirChat(parseInt(targetUserId));
    }
});


function aceitarBatalha(amigoId) {
    const fd = new FormData();
    fd.append('action', 'desafiar');
    fd.append('amigo_id', amigoId);
    fetch('../../back/api_batalha.php', { method: 'POST', body: fd })
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
