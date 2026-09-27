function toggleNotificacoes() {
    const dropdown = document.getElementById('notif-dropdown');
    if (dropdown.style.display === 'none') {
        dropdown.style.display = 'flex';
        fetchNotificacoes();
    } else {
        dropdown.style.display = 'none';
    }
}

function fetchNotificacoes() {
    fetch('../../back/api_notificacoes.php?action=get_notificacoes')
        .then(res => res.json())
        .then(data => {
            if (data.error) return;
            
            const badge = document.getElementById('badge-notif');
            if (data.count > 0) {
                badge.innerText = data.count;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
            
            const list = document.getElementById('notif-list');
            if (data.count === 0) {
                list.innerHTML = '<div class="notif-empty">Nenhuma notificação nova</div>';
                return;
            }
            
            let html = '';
            data.notificacoes.forEach(n => {
                if (n.type === 'friend_request') {
                    html += `
                    <div class="notif-item">
                        <img src="${n.foto}" class="notif-avatar">
                        <div class="notif-content">
                            <div class="notif-text"><strong>${n.nome}</strong> enviou um pedido de conexão.</div>
                            <div class="notif-actions">
                                <button class="notif-btn notif-btn-accept" onclick="responderPedido(${n.id}, 'aceito')">Aceitar</button>
                                <button class="notif-btn notif-btn-decline" onclick="responderPedido(${n.id}, 'recusado')">Recusar</button>
                            </div>
                        </div>
                    </div>`;
                } else if (n.type === 'unread_messages') {
                    html += `
                    <div class="notif-item" style="cursor: pointer;" onclick="window.location.href='chat.php?user=${n.remetente_id}'">
                        <img src="${n.foto}" class="notif-avatar">
                        <div class="notif-content">
                            <div class="notif-text"><strong>${n.nome}</strong> enviou ${n.qtd} mensagem(ns).</div>
                        </div>
                    </div>`;
                }
            });
            list.innerHTML = html;
        });
}

function responderPedido(id, response) {
    const fd = new FormData();
    fd.append('action', 'respond_request');
    fd.append('id', id);
    fd.append('response', response);
    
    fetch('../../back/api_notificacoes.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                fetchNotificacoes();
            }
        });
}

document.addEventListener('DOMContentLoaded', () => {
    fetchNotificacoes();
    setInterval(fetchNotificacoes, 30000); // atualiza a cada 30 segundos
});

document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('notif-dropdown');
    const btn = document.getElementById('btn-notificacoes');
    if (dropdown && btn) {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    }
});
