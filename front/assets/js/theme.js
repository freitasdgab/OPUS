// theme.js - Gerencia o carregamento e sincronização do tema Claro/Escuro
(function () {
    const currentTheme = localStorage.getItem('opus_theme') || 'dark';
    if (currentTheme === 'light') {
        document.documentElement.classList.add('light-mode');
        const applyToBody = () => {
            if (document.body) {
                document.body.classList.add('light-mode');
            } else {
                setTimeout(applyToBody, 10);
            }
        };
        applyToBody();
    }

    function syncSidebarThemeButton(isLight) {
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');
        if (themeIcon) {
            themeIcon.classList.toggle('fa-moon', isLight);
            themeIcon.classList.toggle('fa-sun', !isLight);
        }
        if (themeText) {
            themeText.innerText = isLight ? 'Modo Escuro' : 'Modo Claro';
        }
    }

    window.opusIsLightMode = function () {
        return document.documentElement.classList.contains('light-mode') ||
            (document.body && document.body.classList.contains('light-mode'));
    };

    window.opusSetTheme = function (mode) {
        const isLight = mode === 'light';
        document.documentElement.classList.toggle('light-mode', isLight);
        if (document.body) {
            document.body.classList.toggle('light-mode', isLight);
        }
        try {
            localStorage.setItem('opus_theme', isLight ? 'light' : 'dark');
        } catch (e) {}
        syncSidebarThemeButton(isLight);
        window.dispatchEvent(new CustomEvent('opus:theme-change', { detail: { theme: isLight ? 'light' : 'dark', isLight } }));
    };

    window.opusToggleTheme = function () {
        window.opusSetTheme(window.opusIsLightMode() ? 'dark' : 'light');
    };

    document.addEventListener('DOMContentLoaded', () => {
        const themeBtn = document.getElementById('themeToggleBtn');
        syncSidebarThemeButton(window.opusIsLightMode());

        if (themeBtn) {
            themeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.opusToggleTheme();
            });
        }
    });
})();
