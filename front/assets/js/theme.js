// theme.js - Gerencia o carregamento do tema Claro/Escuro
(function() {
    const currentTheme = localStorage.getItem('opus_theme') || 'dark';
    if (currentTheme === 'light') {
        // Tenta aplicar ao HTML e Body o mais cedo possvel
        document.documentElement.classList.add('light-mode');
        
        // Garante que o body tambm receba a classe
        const applyToBody = () => {
            if (document.body) {
                document.body.classList.add('light-mode');
            } else {
                setTimeout(applyToBody, 10);
            }
        };
        applyToBody();
    }
})();

// Lgica de inicializao dos botes de toggle (se existirem na pgina)
document.addEventListener('DOMContentLoaded', () => {
    const themeBtn = document.getElementById('themeToggleBtn');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    const body = document.body;

    // Se estiver na página com o botão, ajusta o texto e ícone (se houver)
    if (themeBtn && themeText) {
        if (body.classList.contains('light-mode')) {
            if (themeIcon) {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            themeText.innerText = 'Modo Escuro';
        }

        themeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            body.classList.toggle('light-mode');
            document.documentElement.classList.toggle('light-mode');
            
            if (body.classList.contains('light-mode')) {
                localStorage.setItem('opus_theme', 'light');
                if (themeIcon) {
                    themeIcon.classList.remove('fa-sun');
                    themeIcon.classList.add('fa-moon');
                }
                themeText.innerText = 'Modo Escuro';
            } else {
                localStorage.setItem('opus_theme', 'dark');
                if (themeIcon) {
                    themeIcon.classList.remove('fa-moon');
                    themeIcon.classList.add('fa-sun');
                }
                themeText.innerText = 'Modo Claro';
            }
        });
    }
});
