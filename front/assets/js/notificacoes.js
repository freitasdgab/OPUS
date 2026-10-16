// notificacoes.js - Gerenciador de notificações globais e badge de chat

let ultimasNotificacoes = [];

function fetchNotificacoesBadge() {
    fetch('../../back/api/notificacoes.php?action=get_notificacoes')
        .then(res => res.json())
        .then(data => {
            if (data.error) return;
            ultimasNotificacoes = data.notificacoes || [];
            
            // 1. Notificações de solicitações/amizades (Avisos/Sininho)
            const pendingNotifs = ultimasNotificacoes.filter(n => n.type !== 'unread_messages').length;
            const badgeNotif = document.getElementById('badge-notif');
            if (badgeNotif) {
                if (pendingNotifs > 0) {
                    badgeNotif.innerText = pendingNotifs > 99 ? '99+' : pendingNotifs;
                    badgeNotif.style.display = 'inline-flex';
                } else {
                    badgeNotif.style.display = 'none';
                }
            }

            // 2. Mensagens não lidas do Chat
            const unreadMsgs = ultimasNotificacoes
                .filter(n => n.type === 'unread_messages')
                .reduce((acc, curr) => acc + (parseInt(curr.qtd) || 1), 0);
            const badgeChat = document.getElementById('badge-chat-msg');
            if (badgeChat) {
                if (unreadMsgs > 0) {
                    badgeChat.innerText = unreadMsgs > 99 ? '99+' : unreadMsgs;
                    badgeChat.style.display = 'inline-flex';
                } else {
                    badgeChat.style.display = 'none';
                }
            }

            // Atualiza dropdown se estiver aberto
            renderizarNotifDropdown();
        })
        .catch(() => {});
}

function handleChatClick(e) {
    if (e) e.stopPropagation();
    const isChatPage = window.location.pathname.endsWith('chat.php');
    if (!isChatPage) {
        window.location.href = 'chat.php';
    }
}

function handleNotifClick(e) {
    if (e) e.stopPropagation();
    const isChatPage = window.location.pathname.endsWith('chat.php');
    if (isChatPage) {
        if (typeof alternarAbaChat === 'function') {
            alternarAbaChat('notificacoes');
            return;
        }
    }

    const dropdown = document.getElementById('notifDropdownTopbar');
    if (dropdown && dropdown.style.display === 'flex') {
        dropdown.style.display = 'none';
    } else {
        abrirNotifDropdown();
    }
}

function abrirNotifDropdown() {
    let dropdown = document.getElementById('notifDropdownTopbar');
    const btnNotif = document.getElementById('btn-notificacoes') || document.getElementById('btn-chat');
    if (!btnNotif) return;

    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = 'notifDropdownTopbar';
        dropdown.className = 'notif-dropdown';
        btnNotif.parentElement.appendChild(dropdown);

        // Fecha ao clicar fora
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== btnNotif && !btnNotif.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    renderizarNotifDropdown();
    dropdown.style.display = 'flex';
}

function renderizarNotifDropdown() {
    const dropdown = document.getElementById('notifDropdownTopbar');
    if (!dropdown) return;

    let itemsHtml = '';
    if (ultimasNotificacoes.length === 0) {
        itemsHtml = `
            <div class="notif-empty">
                <i class="fa-regular fa-bell-slash" style="font-size: 1.5rem; margin-bottom: 8px; display: block;"></i>
                Nenhuma notificação nova no momento.<br>
                <a href="chat.php" style="color:#1cb0f6; text-decoration:none; display:inline-block; margin-top:10px; font-weight:800;">
                    Ir para o Chat <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        `;
    } else {
        ultimasNotificacoes.forEach(n => {
            const foto = n.foto || '../assets/img/opi pulando feliz.png';
            if (n.type === 'friend_request') {
                const nomeEsc = n.nome.replace(/'/g, "\\'");
                itemsHtml += `
                    <div class="notif-item" id="topbar-req-${n.id}">
                        <img src="${foto}" class="notif-avatar" alt="${n.nome}">
                        <div class="notif-content">
                            <div class="notif-text"><strong>${n.nome}</strong> enviou uma solicitação para te seguir.</div>
                            <div class="notif-actions" id="actions-topbar-req-${n.id}">
                                <button type="button" class="notif-btn notif-btn-accept" onclick="abrirOpcaoAmigoTopbar(${n.id}, '${nomeEsc}', ${n.remetente_id})">
                                    Aceitar
                                </button>
                                <button type="button" class="notif-btn notif-btn-decline" onclick="responderNotifTopbar(${n.id}, 'recusado', '${nomeEsc}', ${n.remetente_id}, 0)">
                                    Recusar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else if (n.type === 'friend_accepted') {
                itemsHtml += `
                    <div class="notif-item" style="border-left: 3px solid #58cc02;" id="topbar-acc-${n.id}">
                        <img src="${foto}" class="notif-avatar" alt="${n.nome}">
                        <div class="notif-content">
                            <div class="notif-text" style="color:#58cc02;"><strong>${n.nome}</strong> aceitou seu pedido e seguiu de volta! Vocês agora são amigos.</div>
                            <div class="notif-actions">
                                <a href="chat.php?user_id=${n.amigo_id}" class="notif-btn notif-btn-accept" onclick="dismissAcceptedTopbar(${n.id})" style="text-decoration:none; text-align:center; display:block;">
                                    <i class="fa-solid fa-comments"></i> Mandar Mensagem
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            } else if (n.type === 'follow_accepted') {
                itemsHtml += `
                    <div class="notif-item" style="border-left: 3px solid #1cb0f6;" id="topbar-acc-${n.id}">
                        <img src="${foto}" class="notif-avatar" alt="${n.nome}">
                        <div class="notif-content">
                            <div class="notif-text" style="color:#1cb0f6;"><strong>${n.nome}</strong> aceitou sua solicitação para seguir.</div>
                            <div class="notif-actions">
                                <button type="button" class="notif-btn notif-btn-neutral" onclick="dismissAcceptedTopbar(${n.id})" style="width:100%;">
                                    <i class="fa-solid fa-check"></i> Ciente
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            } else if (n.type === 'unread_messages') {
                itemsHtml += `
                    <div class="notif-item" style="border-left: 3px solid #1cb0f6;">
                        <img src="${foto}" class="notif-avatar" alt="${n.nome}">
                        <div class="notif-content">
                            <div class="notif-text"><strong>${n.nome}</strong> enviou ${n.qtd} nova(s) mensagem(ns).</div>
                            <div class="notif-actions">
                                <a href="chat.php?user_id=${n.remetente_id}" class="notif-btn notif-btn-accept" style="text-decoration:none; text-align:center; display:block;">
                                    <i class="fa-solid fa-comments"></i> Abrir Chat
                                </a>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
    }

    dropdown.innerHTML = `
        <div class="notif-header">
            <h3><i class="fa-solid fa-bell" style="color: #1cb0f6; margin-right: 6px;"></i> Notificações</h3>
            <a href="chat.php" class="notif-link">Abrir Chat <i class="fa-solid fa-arrow-right"></i></a>
        </div>
        <div class="notif-body">
            ${itemsHtml}
        </div>
    `;
}

function abrirOpcaoAmigoTopbar(id, nome, remetenteId) {
    const actBox = document.getElementById(`actions-topbar-req-${id}`);
    if (!actBox) return;
    const nomeEsc = nome.replace(/'/g, "\\'");
    actBox.innerHTML = `
        <div class="notif-ask-box">
            <div class="notif-ask-title"><i class="fa-solid fa-user-group"></i> Quer ser amigo?</div>
            <div class="notif-ask-sub">Siga <strong>${nome}</strong> de volta para se tornarem amigos e liberarem mensagens!</div>
            <div class="notif-actions">
                <button type="button" class="notif-btn notif-btn-accept" onclick="responderNotifTopbar(${id}, 'aceito', '${nomeEsc}', ${remetenteId}, 1)">
                    <i class="fa-solid fa-check"></i> Sim, ser amigos
                </button>
                <button type="button" class="notif-btn notif-btn-neutral" onclick="responderNotifTopbar(${id}, 'aceito', '${nomeEsc}', ${remetenteId}, 0)">
                    Apenas aceitar
                </button>
            </div>
        </div>
    `;
}

function responderNotifTopbar(id, response, nome, remetenteId, seguirDeVolta = 1) {
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
                        if (seguirDeVolta === 1) {
                            window.opusToast(`Você e ${nome} agora são amigos!`, 'success');
                        } else {
                            window.opusToast(`${nome} agora está te seguindo.`, 'info');
                        }
                    } else {
                        window.opusToast(`Solicitação de ${nome} recusada.`, 'info');
                    }
                }
                fetchNotificacoesBadge();
                if (response === 'aceito' && seguirDeVolta === 1 && remetenteId) {
                    window.location.href = `chat.php?user_id=${remetenteId}`;
                }
            }
        });
}

function dismissAcceptedTopbar(id) {
    const fd = new FormData();
    fd.append('action', 'dismiss_accepted');
    fd.append('id', id);
    fetch('../../back/api/notificacoes.php', { method: 'POST', body: fd })
        .then(() => fetchNotificacoesBadge())
        .catch(() => {});
}

window.fetchNotificacoesBadge = fetchNotificacoesBadge;
window.handleChatClick = handleChatClick;
window.handleNotifClick = handleNotifClick;
window.abrirOpcaoAmigoTopbar = abrirOpcaoAmigoTopbar;
window.responderNotifTopbar = responderNotifTopbar;
window.dismissAcceptedTopbar = dismissAcceptedTopbar;

document.addEventListener('DOMContentLoaded', () => {
    fetchNotificacoesBadge();
    setInterval(fetchNotificacoesBadge, 15000); // atualiza a cada 15 segundos
});
