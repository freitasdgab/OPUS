// perfil.js - logica da pagina de Perfil
// Os valores dinamicos vindos do PHP (avatar/cor atuais do usuario) chegam
// via window.OPUS_PERFIL, definido em um pequeno <script> inline no perfil.php

function openLogoutModal() { document.getElementById('logoutModal').classList.add('active'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('active'); }

const profileModal = document.getElementById('profileModal');
const colorOptions = document.querySelectorAll('.color-option');
const inputAvatar = document.getElementById('input-avatar');
const inputCor = document.getElementById('input-cor');
const inputRemoverFoto = document.getElementById('input-remover-foto');
const inputFotoUpload = document.getElementById('input-foto-upload');
const modalCirclePreview = document.getElementById('modalCirclePreview') || document.getElementById('modalOvalPreview');
const btnRemoverFoto = document.getElementById('btnRemoverFoto');

function getPerfilState() {
    return {
        avatar: (window.OPUS_PERFIL && window.OPUS_PERFIL.avatar) ? window.OPUS_PERFIL.avatar : '',
        cor: (window.OPUS_PERFIL && window.OPUS_PERFIL.cor) ? window.OPUS_PERFIL.cor : '#1cb0f6'
    };
}

function resetModalPreview() {
    const state = getPerfilState();

    if (inputFotoUpload) inputFotoUpload.value = '';
    if (inputRemoverFoto) inputRemoverFoto.value = '0';
    if (inputAvatar) inputAvatar.value = state.avatar;
    if (inputCor) inputCor.value = state.cor;

    const preview = document.getElementById('modalCirclePreview') || document.getElementById('modalOvalPreview');
    if (preview) {
        preview.style.borderColor = state.cor;
        preview.style.backgroundColor = state.cor;
    }

    colorOptions.forEach(opt => {
        const isMatch = opt.dataset.color && (opt.dataset.color.toLowerCase() === state.cor.toLowerCase());
        opt.classList.toggle('selected', isMatch);
    });

    const box = document.getElementById('modalAvatarBox');
    let img = document.getElementById('modalAvatarImg');
    let placeholder = document.getElementById('modalAvatarPlaceholder');

    if (state.avatar && state.avatar.trim() !== '') {
        if (!img && box) {
            img = document.createElement('img');
            img.id = 'modalAvatarImg';
            img.className = 'modal-avatar-img';
            img.alt = 'Avatar';
            box.insertBefore(img, box.firstChild);
        }
        if (img) {
            img.src = state.avatar;
            img.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
        if (btnRemoverFoto) btnRemoverFoto.style.display = 'inline-flex';
    } else {
        if (img) img.style.display = 'none';
        if (!placeholder && box) {
            placeholder = document.createElement('div');
            placeholder.id = 'modalAvatarPlaceholder';
            placeholder.className = 'modal-avatar-placeholder';
            placeholder.innerHTML = '<i class="fa-solid fa-user"></i>';
            box.insertBefore(placeholder, box.firstChild);
        }
        if (placeholder) placeholder.style.display = 'flex';
        if (btnRemoverFoto) btnRemoverFoto.style.display = 'none';
    }
}

function openProfileModal() {
    if (!profileModal) return;
    resetModalPreview();
    profileModal.classList.add('active');
}

function closeProfileModal() { 
    if (profileModal) profileModal.classList.remove('active'); 
    resetModalPreview();
}

// Fechar ao clicar fora do modal
if (profileModal) {
    profileModal.addEventListener('click', function(e) {
        if (e.target === profileModal) closeProfileModal();
    });
}

// Seleção de cor do fundo
colorOptions.forEach(opt => {
    opt.addEventListener('click', function() {
        colorOptions.forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        const novaCor = this.dataset.color;
        if (inputCor) inputCor.value = novaCor;
        const preview = document.getElementById('modalCirclePreview') || document.getElementById('modalOvalPreview');
        if (preview) {
            preview.style.borderColor = novaCor;
            preview.style.backgroundColor = novaCor;
        }
    });
});

// Manipulação do upload de foto
window.aoSelecionarFoto = function(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    // Limite de 5MB
    if (file.size > 5 * 1024 * 1024) {
        if (typeof opusAlerta === 'function') {
            opusAlerta({
                tipo: 'erro',
                titulo: 'Arquivo Muito Grande',
                mensagem: 'Por favor, selecione uma imagem de até 5MB.'
            });
        } else {
            alert('Por favor, selecione uma imagem de até 5MB.');
        }
        input.value = '';
        return;
    }

    if (!file.type.match(/^image\/(png|jpe?g|webp|gif)$/i)) {
        if (typeof opusAlerta === 'function') {
            opusAlerta({
                tipo: 'erro',
                titulo: 'Formato Não Suportado',
                mensagem: 'Selecione uma imagem válida nos formatos PNG, JPG ou WEBP.'
            });
        } else {
            alert('Selecione uma imagem válida nos formatos PNG, JPG ou WEBP.');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const box = document.getElementById('modalAvatarBox');
        let img = document.getElementById('modalAvatarImg');
        let placeholder = document.getElementById('modalAvatarPlaceholder');

        if (!img && box) {
            img = document.createElement('img');
            img.id = 'modalAvatarImg';
            img.className = 'modal-avatar-img user-upload-img';
            img.alt = 'Avatar';
            box.insertBefore(img, box.firstChild);
        }

        if (img) {
            img.src = e.target.result;
            img.className = 'modal-avatar-img user-upload-img';
            img.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';

        if (inputRemoverFoto) inputRemoverFoto.value = '0';
        if (btnRemoverFoto) btnRemoverFoto.style.display = 'inline-flex';
    };
    reader.readAsDataURL(file);
};

// Remover foto e voltar para o avatar padrão
window.removerFotoPerfil = function() {
    if (inputFotoUpload) inputFotoUpload.value = '';
    if (inputAvatar) inputAvatar.value = '';
    if (inputRemoverFoto) inputRemoverFoto.value = '1';

    const img = document.getElementById('modalAvatarImg');
    if (img) img.style.display = 'none';

    const box = document.getElementById('modalAvatarBox');
    let placeholder = document.getElementById('modalAvatarPlaceholder');
    if (!placeholder && box) {
        placeholder = document.createElement('div');
        placeholder.id = 'modalAvatarPlaceholder';
        placeholder.className = 'modal-avatar-placeholder';
        placeholder.innerHTML = '<i class="fa-solid fa-user"></i>';
        box.insertBefore(placeholder, box.firstChild);
    }
    if (placeholder) placeholder.style.display = 'flex';

    if (btnRemoverFoto) btnRemoverFoto.style.display = 'none';
};

// Busca estatísticas de Seguidores e Seguindo
fetch('../../back/api/amigos.php?action=estatisticas_sociais')
.then(r => r.json())
.then(data => {
    if (data.success) {
        const seguindoElem = document.getElementById('lblSeguindoCount');
        const seguidoresElem = document.getElementById('lblSeguidoresCount');
        const amigosElem = document.getElementById('lblAmigosCount');
        if (seguindoElem) seguindoElem.innerText = data.seguindo;
        if (seguidoresElem) seguidoresElem.innerText = data.seguidores;
        if (amigosElem) amigosElem.innerText = data.amigos || 0;
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

// Modal de Troféu Ampliado (Fullscreen)
window.OPUS_TROFEUS = {};

function getOrCreateTrophyModal() {
    let m = document.getElementById('trophyModal');
    if (!m) {
        m = document.createElement('div');
        m.className = 'trophy-fullscreen-modal';
        m.id = 'trophyModal';
        m.style.display = 'none';
        m.innerHTML = `
            <div class="trophy-fullscreen-backdrop" onclick="closeTrophyModal()"></div>
            <div class="trophy-fullscreen-container">
                <button type="button" class="trophy-fullscreen-close" onclick="closeTrophyModal()" aria-label="Fechar visualização" title="Fechar visualização">
                    <i class="fa-solid fa-xmark"></i>
                    <span>Fechar</span>
                </button>
                <div class="trophy-fullscreen-img-wrap" id="trophyModalImgWrapper">
                    <div class="trophy-fullscreen-glow"></div>
                    <img id="trophyModalImg" src="" alt="Troféu Ampliado" class="trophy-fullscreen-img">
                </div>
                <div class="trophy-fullscreen-info">
                    <div id="trophyModalStatus" class="trophy-fullscreen-status"></div>
                    <h2 id="trophyModalTitle" class="trophy-fullscreen-title"></h2>
                    <p id="trophyModalDesc" class="trophy-fullscreen-desc"></p>
                </div>
                <div class="trophy-fullscreen-actions">
                    <button type="button" class="trophy-fullscreen-btn-close" onclick="closeTrophyModal()">
                        <i class="fa-solid fa-xmark"></i> Fechar
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(m);
    }
    return m;
}

function openTrophyModal(slug) {
    const modal = getOrCreateTrophyModal();
    const t = window.OPUS_TROFEUS ? window.OPUS_TROFEUS[slug] : null;
    if (!t) return;

    const imgEl = document.getElementById('trophyModalImg');
    const titleEl = document.getElementById('trophyModalTitle');
    const descEl = document.getElementById('trophyModalDesc');
    const statusEl = document.getElementById('trophyModalStatus');
    const wrapperEl = document.getElementById('trophyModalImgWrapper');

    if (imgEl) {
        imgEl.src = t.imgSrc;
        imgEl.alt = t.nome;
    }
    if (titleEl) titleEl.innerText = t.nome;
    if (descEl) descEl.innerText = t.desc;

    if (statusEl) {
        if (t.isUnlocked) {
            statusEl.className = 'trophy-fullscreen-status unlocked';
            statusEl.innerHTML = '<i class="fa-solid fa-trophy"></i> Conquista Desbloqueada!';
        } else {
            statusEl.className = 'trophy-fullscreen-status locked';
            const falta = 100 - (t.progress || 0);
            statusEl.innerHTML = `<i class="fa-solid fa-lock"></i> Bloqueado (Falta ${falta}% para conseguir isso)`;
        }
    }

    if (wrapperEl) {
        wrapperEl.classList.toggle('locked-wrap', !t.isUnlocked);
    }

    modal.classList.add('active');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTrophyModal() {
    const modal = document.getElementById('trophyModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    document.body.style.overflow = '';
}

window.openTrophyModal = openTrophyModal;
window.closeTrophyModal = closeTrophyModal;
window.getOrCreateTrophyModal = getOrCreateTrophyModal;

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTrophyModal();
        closeProfileModal();
        closeSocialModal();
        closeLogoutModal();
    }
});

// Consumo da API de Conquistas e Criação dos Cards no Perfil
fetch('../../back/api/conquistas.php?v=' + Date.now())
.then(r => r.json())
.then(data => {
    const container = document.getElementById('trophy-container');
    if (!container) return;

    container.innerHTML = '';
    window.OPUS_TROFEUS = {};

    if (data.lista) {
        data.lista.forEach(t => {
            const isUnlocked = Array.isArray(data.conquistados) && data.conquistados.includes(t.slug);
            const imgSrc = t.imagem ? `../assets/img/${encodeURI(t.imagem)}` : '../assets/img/LOGO.png';
            
            const progress = isUnlocked ? 100 : (t.progresso_percent || 0);

            window.OPUS_TROFEUS[t.slug] = {
                slug: t.slug,
                nome: t.nome,
                desc: t.desc,
                imagem: t.imagem,
                imgSrc: imgSrc,
                isUnlocked: isUnlocked,
                progress: progress
            };
            
            const progressTextHtml = !isUnlocked 
                ? `<div style="font-size: 0.75rem; color: var(--text-muted); margin-bottom: 4px; font-weight: 500;">Falta ${100 - progress}% para conseguir isso</div>` 
                : `<div style="font-size: 0.75rem; color: #58cc02; margin-bottom: 4px; font-weight: bold;">Concluído</div>`;

            container.innerHTML += `
                <div class="trophy-profile-card ${isUnlocked ? 'unlocked' : 'locked'}" onclick="openTrophyModal('${t.slug}')" role="button" tabindex="0" title="Clique para ver o troféu em tela cheia">
                    <div class="trophy-img-box">
                        <img src="${imgSrc}" class="trophy-img" alt="${t.nome}">
                    </div>
                    <div class="trophy-details">
                        <div class="trophy-desc">${t.desc.toUpperCase()}</div>
                        ${progressTextHtml}
                        <div class="trophy-progress">
                            <div class="trophy-progress-fill" style="width: ${progress}%;"></div>
                        </div>
                        <button type="button" class="btn-card-zoom" onclick="event.stopPropagation(); openTrophyModal('${t.slug}')">
                            <i class="fa-solid fa-up-right-and-down-left-from-center"></i>
                            <span>Ampliar Troféu</span>
                        </button>
                    </div>
                </div>`;
        });
    }
})
.catch(err => console.error('Erro ao carregar conquistas no perfil:', err));


// Busca pedidos pendentes para a Caixa de Aprovação
fetch('../../back/api/amigos.php?action=listar_pedidos')
.then(r => r.json())
.then(data => {
    if (data.success && data.pedidos.length > 0) {
        document.getElementById('notificacoesAreaPerfil').style.display = 'block';
        const container = document.getElementById('listaPedidosPerfil');
        container.innerHTML = '';
        data.pedidos.forEach(p => {
            const avatarHtml = p.foto_perfil
                ? `<img src="${p.foto_perfil}" style="width:30px; height:30px; border-radius:50%; object-fit:contain; background:#191924;">`
                : `<div style="width:30px; height:30px; border-radius:50%; background:#191924; border:1px solid #2e2e42; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:0.8rem;"><i class="fa-solid fa-user"></i></div>`;
            container.innerHTML += `
                <div style="display:flex; align-items:center; gap:10px; background:#12121a; padding:8px 12px; border-radius:10px;" id="pedidoBox_${p.pedido_id}">
                    ${avatarHtml}
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

    fetch('../../back/api/amigos.php', { method: 'POST', body: fd })
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
                carregarAmigosPerfil(); // Recarrega a lista de amigos se aceitou
            }
        }
    });
}

function carregarAmigosPerfil() {
    fetch('../../back/api/amigos.php?action=listar_amigos&tipo=amigos')
    .then(r => r.json())
    .then(data => {
        const container = document.getElementById('listaAmigosPerfil');
        if (!container) return;
        
        if (!data.success || data.amigos.length === 0) {
            container.innerHTML = '<div style="color:var(--text-muted); font-size:0.85rem;">Você ainda não tem amigos. Adicione conexões!</div>';
            return;
        }
        
        container.innerHTML = '';
        data.amigos.forEach(a => {
            const avatarHtml = a.foto_perfil
                ? `<img src="${a.foto_perfil}" class="friend-avatar" style="object-fit:contain; background:#191924;">`
                : `<div class="friend-avatar" style="background:#191924; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:1rem;"><i class="fa-solid fa-user"></i></div>`;
            container.innerHTML += `
                <div class="friend-item" onclick="window.location.href='chat.php?user_id=${a.id}'">
                    ${avatarHtml}
                    <div class="friend-name">
                        ${a.nome}
                    </div>
                    <div class="friend-chat-icon">
                        <i class="fa-regular fa-comment-dots"></i>
                    </div>
                </div>
            `;
        });
    });
}

function openSocialModal(type) {
    document.getElementById('socialModal').classList.add('active');
    
    let title = 'Amigos';
    if (type === 'seguidores') title = 'Seguidores';
    else if (type === 'seguindo') title = 'Seguindo';
    
    document.getElementById('socialModalTitle').innerText = title;
    
    const container = document.getElementById('socialModalList');
    container.innerHTML = '<div style="color:var(--text-muted); text-align:center;">Carregando...</div>';
    
    fetch(`../../back/api/amigos.php?action=listar_amigos&tipo=${type}`)
    .then(r => r.json())
    .then(data => {
        if (!data.success || data.amigos.length === 0) {
            container.innerHTML = `<div style="color:var(--text-muted); text-align:center; font-size:0.85rem;">Nenhum usuário encontrado.</div>`;
            return;
        }
        
        container.innerHTML = '';
        data.amigos.forEach(a => {
            const avatarHtml = a.foto_perfil
                ? `<img src="${a.foto_perfil}" style="width:40px; height:40px; border-radius:50%; object-fit:contain; background:#191924;">`
                : `<div style="width:40px; height:40px; border-radius:50%; background:#191924; border:1px solid #2e2e42; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:1rem;"><i class="fa-solid fa-user"></i></div>`;
            container.innerHTML += `
                <div style="display:flex; align-items:center; gap:10px; background:#12121a; padding:12px; border-radius:12px; border: 1px solid #2e2e42;">
                    ${avatarHtml}
                    <div style="flex:1;">
                        <span style="color:#fff; font-size:0.95rem; font-weight:800; display:block;">${a.nome}</span>
                        <span style="color:#ffc800; font-size:0.8rem; font-weight:700;"><i class="fa-solid fa-bolt"></i> ${a.xp} XP</span>
                    </div>
                </div>
            `;
        });
    });
}

function closeSocialModal() {
    document.getElementById('socialModal').classList.remove('active');
}

// Carrega os amigos ao iniciar
document.addEventListener('DOMContentLoaded', () => {
    carregarAmigosPerfil();
});

