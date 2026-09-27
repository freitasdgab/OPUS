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
                if (ultima.includes('[DUEL_INVITE]')) ultima = '⚔️ Convite para Duelo!';
                
                html += `
                <div class="chat-list-item ${activeClass}" onclick="abrirChat(${c.id})">
                    <img src="${c.foto}">
                    <div class="cli-info">
                        <div class="cli-name">${c.nome}</div>
                        <div class="cli-last">${ultima}</div>
                    </div>
                    ${unreadHtml}
                </div>`;
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
            
            let duelFloatingIcon = data.has_active_duel ? `
                <div class="floating-duel-icon" onclick="window.location.href='batalha.php?target=${f.id}'" title="Ir para o Duelo!">
                    <i class="fa-solid fa-khanda"></i>
                </div>
            ` : '';
            
            let html = `
            <div class="chat-header">
                <div class="ch-user">
                    <img src="${f.foto}">
                    <div class="ch-user-info">
                        <h3>${f.nome}</h3>
                    </div>
                </div>
                <button class="btn-duel" onclick="enviarConviteDuelo(${f.id})"><i class="fa-solid fa-khanda"></i> Convidar Duelo</button>
            </div>
            <div class="chat-messages" id="chat-messages">`;
            
            msgs.forEach(m => {
                let isMe = (m.remetente_id !== f.id);
                let rowClass = isMe ? 'me' : 'them';
                
                if (m.mensagem === '[DUEL_INVITE]') {
                    let txt = isMe ? "Você convidou para um duelo!" : "Te convidou para um duelo!";
                    html += `
                    <div class="msg-row ${rowClass}">
                        <div class="msg-duel" onclick="window.location.href='batalha.php?target=${f.id}'">
                            <i class="fa-solid fa-khanda msg-duel-icon"></i>
                            <div class="msg-duel-text">
                                <span>Duelo!</span>
                                <span>${txt}</span>
                            </div>
                        </div>
                    </div>`;
                } else {
                    // Escape HTML basic
                    let text = m.mensagem.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                    html += `
                    <div class="msg-row ${rowClass}">
                        <div class="msg-bubble">${text}</div>
                    </div>`;
                }
            });
            
            html += `</div>
            <div class="chat-input-area">
                <input type="text" id="chat-input" placeholder="Digite uma mensagem..." onkeypress="handleEnter(event)">
                <button class="btn-send" onclick="enviarMensagem()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
            ${duelFloatingIcon}
            `;
            
            const main = document.getElementById('chat-main');
            const wasScrolledToBottom = (main.scrollHeight - main.scrollTop === main.clientHeight) || true; // hacky mas funciona p/ prototipo
            
            main.innerHTML = html;
            
            const msgContainer = document.getElementById('chat-messages');
            if (msgContainer && wasScrolledToBottom) {
                msgContainer.scrollTop = msgContainer.scrollHeight;
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
});
