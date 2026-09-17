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

// Consumo da API de Conquistas e Criação dos Cards
fetch('../../back/api_conquistas.php')
.then(r => r.json())
.then(data => {
    const container = document.getElementById('trophy-container');

    // Garante que fique em 2 colunas, um do lado do outro
    container.style.gridTemplateColumns = 'repeat(2, 1fr)';

    if (data.lista) {
        data.lista.forEach(t => {
            const isUnlocked = data.conquistados.includes(t.slug);

            const filterStyle = isUnlocked ? '' : 'filter: grayscale(100%); opacity: 0.5;';

            let nomeT = (t.nome + " " + t.slug).toLowerCase();
            let imgSrc = '../assets/img/LOGO.png';

            if (nomeT.includes('primeiro')) imgSrc = '../assets/img/primeirospassos.png';
            else if (nomeT.includes('fogo') || nomeT.includes('três') || nomeT.includes('3')) imgSrc = '../assets/img/alcancos3diasdefogo.png';
            else if (nomeT.includes('arquiteto')) imgSrc = '../assets/img/arquitetojava.png';
            else if (nomeT.includes('lógico') || nomeT.includes('caminho')) imgSrc = '../assets/img/caminhoslogicos-capitulo2.png';
            else if (nomeT.includes('fundamento')) imgSrc = '../assets/img/fundamentos-capitulo1.png';
            else if (nomeT.includes('repetição')) imgSrc = '../assets/img/mestredarepeticao.png';
            else if (nomeT.includes('brilhante') || nomeT.includes('acerto')) imgSrc = '../assets/img/mentebrilhante-3de3acertos.png';
            else if (nomeT.includes('array')) imgSrc = '../assets/img/senhor dos arrays.png';

            // Imagem aumentada (75x75) e organização lado a lado dentro da grade de 2 colunas
            container.innerHTML += `
                <div class="stat-card" style="padding: 18px; gap: 15px; display: flex; align-items: center;">
                    <div class="stat-card-icon" style="width: 75px; height: 75px; flex-shrink: 0; display: flex; justify-content: center; align-items: center;">
                        <img src="${imgSrc}" style="width: 100%; height: 100%; object-fit: contain; ${filterStyle}" alt="${t.nome}">
                    </div>
                    <div class="stat-card-content" style="display: flex; flex-direction: column; gap: 4px;">
                        <span class="stat-card-value" style="font-size: 1.1rem; line-height: 1.2;">${t.nome}</span>
                        <span class="stat-card-label" style="font-size: 0.85rem; line-height: 1.3;">${t.desc}</span>
                    </div>
                </div>`;
        });
    }
});
