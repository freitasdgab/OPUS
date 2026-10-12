function fetchNotificacoesBadge() {
    fetch('../../back/api/notificacoes.php?action=get_notificacoes')
        .then(res => res.json())
        .then(data => {
            if (data.error) return;
            
            const badge = document.getElementById('badge-notif');
            if (badge) {
                if (data.count > 0) {
                    badge.innerText = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(() => {});
}

window.fetchNotificacoesBadge = fetchNotificacoesBadge;

document.addEventListener('DOMContentLoaded', () => {
    fetchNotificacoesBadge();
    setInterval(fetchNotificacoesBadge, 20000); // atualiza a cada 20 segundos
});

