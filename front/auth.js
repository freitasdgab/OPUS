const authAction = document.getElementById('authAction');
const groupNome = document.getElementById('group-nome');
const groupConfirmar = document.getElementById('group-confirmar');
const btnModoLogin = document.getElementById('btnModoLogin');
const btnSubmit = document.getElementById('btnSubmit');
const inputNome = document.getElementById('nome');
const inputConfirmar = document.getElementById('confirme_senha');

btnModoLogin.addEventListener('click', () => {
    if (authAction.value === 'cadastro') {
        // MUDANDO PARA MODO: LOGIN
        authAction.value = 'login';
        groupNome.style.display = 'none';
        groupConfirmar.style.display = 'none';
        
        // Remove obrigatoriedade para o navegador não barrar o envio do login
        inputNome.removeAttribute('required');
        inputConfirmar.removeAttribute('required');
        
        btnSubmit.textContent = 'LOGAR';
        btnModoLogin.textContent = 'QUERO ME CADASTRAR';
    } else {
        // MUDANDO PARA MODO: CADASTRO
        authAction.value = 'cadastro';
        groupNome.style.display = 'flex';
        groupConfirmar.style.display = 'flex';
        
        // Ativa obrigatoriedade para o cadastro
        inputNome.setAttribute('required', '');
        inputConfirmar.setAttribute('required', '');
        
        btnSubmit.textContent = 'ENTRAR';
        btnModoLogin.textContent = 'LOGAR';
    }
});

// --- CANVAS DE PARTÍCULAS DO FUNDO ---
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');
let particles = [];

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
window.addEventListener('resize', resizeCanvas);
resizeCanvas();

class Particle {
    constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 3 + 1;
        this.speedY = -Math.random() * 0.5 - 0.2;
        this.alpha = Math.random() * 0.3 + 0.1;
    }
    update() {
        this.y += this.speedY;
        if (this.y < 0) { this.y = canvas.height; this.x = Math.random() * canvas.width; }
    }
    draw() {
        ctx.fillStyle = `rgba(26, 54, 202, ${this.alpha})`;
        ctx.fillRect(this.x, this.y, this.size, this.size);
    }
}

for (let i = 0; i < 30; i++) particles.push(new Particle());

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    let gradient = ctx.createRadialGradient(canvas.width/2, canvas.height/2, 10, canvas.width/2, canvas.height/2, canvas.width);
    gradient.addColorStop(0, '#16151a');
    gradient.addColorStop(1, '#0c0c0e');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
}
animate();