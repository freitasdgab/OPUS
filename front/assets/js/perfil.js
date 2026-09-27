// perfil.js - logica da pagina de Perfil
// Os valores dinamicos vindos do PHP (avatar/cor atuais do usuario) chegam
// via window.OPUS_PERFIL, definido em um pequeno <script> inline no perfil.php

function openLogoutModal() { document.getElementById('logoutModal').classList.add('active'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('active'); }

const profileModal = document.getElementById('profileModal');
const avatarOptions = document.querySelectorAll('.avatar-option');
const colorOptions = document.querySelectorAll('.color-option');
const inputAvatar = document.getElementById('input-avatar');
const inputCor = document.getElementById('input-cor');

const currentAvatar = window.OPUS_PERFIL.avatar;
const currentColor = window.OPUS_PERFIL.cor;

function openProfileModal() {
    profileModal.classList.add('active');
    avatarOptions.forEach(opt => opt.classList.toggle('selected', opt.dataset.src === currentAvatar));
    colorOptions.forEach(opt => opt.classList.toggle('selected', opt.dataset.color === currentColor));
}
function closeProfileModal() { profileModal.classList.remove('active'); }

avatarOptions.forEach(opt => {
    opt.addEventListener('click', function() {
        avatarOptions.forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        inputAvatar.value = this.dataset.src;
    });
});

colorOptions.forEach(opt => {
    opt.addEventListener('click', function() {
        colorOptions.forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        inputCor.value = this.dataset.color;
    });
});

// Busca estatísticas de Seguidores e Seguindo
fetch('../../back/api_amigos.php?action=estatisticas_sociais')
.then(r => r.json())
.then(data => {
    if (data.success) {
        const seguindoElem = document.getElementById('lblSeguindoCount');
        const seguidoresElem = document.getElementById('lblSeguidoresCount');
        if (seguindoElem) seguindoElem.innerText = data.seguindo;
        if (seguidoresElem) seguidoresElem.innerText = data.seguidores;
    }
})
.catch(err => console.error('Erro ao buscar seguidores:', err));

function copiarConvitePerfil(userId) {
    const link = `${window.location.origin}/OPUS/front/pages/auth.html?convite=${userId}`;
    navigator.clipboard.writeText(link).then(() => {
        if (typeof opusAlerta === 'function') {
            opusAlerta({
                tipo: 'success',
                titulo: 'Convite Pronto! 🎉',
                mensagem: 'Seu link de convite foi copiado para a área de transferência. Compartilhe com seus amigos para aprenderem juntos no Opus!',
                linkCopia: link
            });
        } else {
            alert('🎉 Link de convite copiado!\n\n' + link);
        }
    }).catch(() => {
        if (typeof opusAlerta === 'function') {
            opusAlerta({
                tipo: 'info',
                titulo: 'Link de Convite',
                mensagem: 'Copie seu link de convite abaixo:',
                linkCopia: link
            });
        } else {
            alert('Seu link de convite: ' + link);
        }
    });
}

// Consumo da API de Conquistas e Criação dos Cards no Perfil
fetch('../../back/api_conquistas.php')
.then(r => r.json())
.then(data => {
    const container = document.getElementById('trophy-container');
    if (!container) return;

    container.innerHTML = '';

    if (data.lista) {
        data.lista.forEach(t => {
            const isUnlocked = data.conquistados.includes(t.slug);
            const imgSrc = t.imagem ? `../assets/img/${encodeURI(t.imagem)}` : '../assets/img/LOGO.png';
            const badgeIcon = isUnlocked ? '<i class="fa-solid fa-check"></i> Conquistado' : '<i class="fa-solid fa-lock"></i> Bloqueado';

            container.innerHTML += `
                <div class="trophy-profile-card ${isUnlocked ? 'unlocked' : 'locked'}">
                    <div class="trophy-badge-status">${badgeIcon}</div>
                    <div class="trophy-img-box">
                        <img src="${imgSrc}" class="trophy-img" alt="${t.nome}">
                    </div>
                    <div class="trophy-details">
                        <div class="trophy-title">${t.nome}</div>
                        <div class="trophy-desc">${t.desc}</div>
                    </div>
                </div>`;
        });
    }
})
.catch(err => console.error('Erro ao carregar conquistas no perfil:', err));

// Busca pedidos pendentes para a Caixa de Aprovação
fetch('../../back/api_amigos.php?action=listar_pedidos')
.then(r => r.json())
.then(data => {
    if (data.success && data.pedidos.length > 0) {
        document.getElementById('notificacoesAreaPerfil').style.display = 'block';
        const container = document.getElementById('listaPedidosPerfil');
        container.innerHTML = '';
        data.pedidos.forEach(p => {
            container.innerHTML += `
                <div style="display:flex; align-items:center; gap:10px; background:#12121a; padding:8px 12px; border-radius:10px;" id="pedidoBox_${p.pedido_id}">
                    <img src="${p.foto_perfil}" style="width:30px; height:30px; border-radius:50%;">
                    <div style="flex:1;">
                        <span style="color:#fff; font-size:0.85rem; font-weight:700;">${p.nome}</span>
                        <span style="display:block; color:var(--text-muted); font-size:0.75rem;">Quer conectar</span>
                    </div>
                    <button onclick="responderPedidoPerfil(${p.pedido_id}, 'aceitar')" style="background:#58cc02; border:none; border-radius:6px; color:#fff; width:26px; height:26px; cursor:pointer;"><i class="fa-solid fa-check"></i></button>
                    <button onclick="responderPedidoPerfil(${p.pedido_id}, 'recusar')" style="background:#ff4b4b; border:none; border-radius:6px; color:#fff; width:26px; height:26px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
                </div>
            `;
        });
    }
});

function responderPedidoPerfil(pedidoId, resposta) {
    const fd = new FormData();
    fd.append('action', 'responder_pedido');
    fd.append('pedido_id', pedidoId);
    fd.append('resposta', resposta);

    fetch('../../back/api_amigos.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`pedidoBox_${pedidoId}`).remove();
            if (document.getElementById('listaPedidosPerfil').children.length === 0) {
                document.getElementById('notificacoesAreaPerfil').style.display = 'none';
            }
            if (resposta === 'aceitar') {
                const segElem = document.getElementById('lblSeguindoCount');
                if (segElem) segElem.innerText = parseInt(segElem.innerText) + 1;
            }
        }
    });
}
