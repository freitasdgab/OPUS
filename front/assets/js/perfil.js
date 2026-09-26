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
