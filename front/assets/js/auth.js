const authAction = document.getElementById('authAction');
const groupNome = document.getElementById('group-nome');
const groupConfirmar = document.getElementById('group-confirmar');
const btnModoLogin = document.getElementById('btnModoLogin');
const btnSubmit = document.getElementById('btnSubmit');
const inputNome = document.getElementById('nome');
const inputConfirmar = document.getElementById('confirme_senha');
const authForm = document.getElementById('authForm');
const inputSenha = document.getElementById('senha');

btnModoLogin.addEventListener('click', () => {
    if (authAction.value === 'cadastro') {
        authAction.value = 'login';
        groupNome.style.display = 'none';
        groupConfirmar.style.display = 'none';
        
        inputNome.removeAttribute('required');
        inputConfirmar.removeAttribute('required');
        
        btnSubmit.textContent = 'LOGAR';
        btnModoLogin.textContent = 'QUERO ME CADASTRAR';
    } else {
        authAction.value = 'cadastro';
        groupNome.style.display = 'flex';
        groupConfirmar.style.display = 'flex';
        
        inputNome.setAttribute('required', '');
        inputConfirmar.setAttribute('required', '');
        
        btnSubmit.textContent = 'CADASTRAR-SE';
        btnModoLogin.textContent = 'LOGAR';
    }
});

// --- VALIDAÇÃO DE SENHA FORTE NO CADASTRO ---
authForm.addEventListener('submit', (e) => {
    if (authAction.value === 'cadastro') {
        const senha = inputSenha.value;
        
        // Expressão regular para validar os critérios da senha
        const regexLetraMaiuscula = /[A-Z]/;
        const regexLetraMinuscula = /[a-z]/;
        const regexNumero = /[0-9]/;
        const regexCaractereEspecial = /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\\/]/;

        let erros = [];

        if (senha.length < 8) erros.push("Ter pelo menos 8 caracteres.");
        if (!regexLetraMaiuscula.test(senha)) erros.push("Conter pelo menos uma letra maiúscula.");
        if (!regexLetraMinuscula.test(senha)) erros.push("Conter pelo menos uma letra minúscula.");
        if (!regexNumero.test(senha)) erros.push("Conter pelo menos um número.");
        if (!regexCaractereEspecial.test(senha)) erros.push("Conter pelo menos um caractere especial (ex: @, #, $, %, !, *).");

        if (erros.length > 0) {
            e.preventDefault(); // Impede o envio do formulário
            alert("Sua senha precisa melhorar nos seguintes pontos:\n\n- " + erros.join("\n- "));
        }
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