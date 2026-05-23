const modal = document.getElementById('modalConfirmacao');
const btnConfirmar = document.getElementById('btnConfirmarFinal');

function abrirModal(nome) {
    modal.style.display = "flex";
    // Pequeno delay para a animação de fade/scale do CSS funcionar
    setTimeout(() => modal.classList.add('active'), 10);
}

function fecharModal() {
    modal.classList.remove('active');
    setTimeout(() => modal.style.display = "none", 300);
}

// Quando confirmar, vai para o dashboard (ou back-end para salvar)
btnConfirmar.addEventListener('click', () => {
    window.location.href = "../back/salvar_escolha.php?lang=Java";
});

// Reutilizando seu efeito de partículas do fundo
const canvas = document.getElementById('bg-canvas');
const ctx = canvas.getContext('2d');

function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
window.addEventListener('resize', resize);
resize();

let particles = [];
class Particle {
    constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 2 + 1;
        this.speedY = -Math.random() * 0.5 - 0.2;
        this.alpha = Math.random() * 0.4 + 0.1;
    }
    update() {
        this.y += this.speedY;
        if (this.y < 0) this.y = canvas.height;
    }
    draw() {
        ctx.fillStyle = `rgba(26, 54, 202, ${this.alpha})`;
        ctx.fillRect(this.x, this.y, this.size, this.size);
    }
}

for(let i=0; i<40; i++) particles.push(new Particle());

function animate() {
    ctx.clearRect(0,0,canvas.width, canvas.height);
    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
}
animate();