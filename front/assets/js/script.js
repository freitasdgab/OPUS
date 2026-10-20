// Canvas desativado — fundo preto liso vem do CSS
const canvas = document.getElementById('bg-canvas');
if (canvas) {
    canvas.style.display = 'none';
}

// Efeito Máquina de Escrever (Typewriter Effect) na palavra Opus
(function() {
    function initTypewriter() {
        const el = document.getElementById('typewriter-text');
        if (!el || el.dataset.typewriterStarted) return;
        el.dataset.typewriterStarted = 'true';

        const word = 'Opus';
        let charIndex = word.length;
        let isDeleting = true;

        const typingSpeed = 160;
        const deletingSpeed = 90;
        const pauseFull = 1800;
        const pauseEmpty = 450;

        function tick() {
            if (isDeleting) {
                charIndex--;
                el.textContent = word.substring(0, charIndex);
            } else {
                charIndex++;
                el.textContent = word.substring(0, charIndex);
            }

            let delay = isDeleting ? deletingSpeed : typingSpeed;

            if (!isDeleting && charIndex === word.length) {
                isDeleting = true;
                delay = pauseFull;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                delay = pauseEmpty;
            }

            setTimeout(tick, delay);
        }

        setTimeout(tick, pauseFull);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTypewriter);
    } else {
        initTypewriter();
    }
})();
