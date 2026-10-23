// Canvas desativado — fundo preto liso vem do CSS
const canvas = document.getElementById('bg-canvas');
if (canvas) {
    canvas.style.display = 'none';
}

// Efeito Máquina de Escrever (Typewriter Effect) nas palavras dinâmicas
(function() {
    function initTypewriter() {
        const el = document.getElementById('typewriter-text');
        if (!el || el.dataset.typewriterStarted) return;
        el.dataset.typewriterStarted = 'true';

        const words = ['simples', 'interativa', 'divertida'];
        let wordIndex = 0;
        let charIndex = words[0].length;
        let isDeleting = true;

        const typingSpeed = 130;
        const deletingSpeed = 70;
        const pauseFull = 1800;
        const pauseEmpty = 350;

        function tick() {
            const currentWord = words[wordIndex];

            if (isDeleting) {
                charIndex--;
                el.textContent = currentWord.substring(0, charIndex);
            } else {
                charIndex++;
                el.textContent = currentWord.substring(0, charIndex);
            }

            let delay = isDeleting ? deletingSpeed : typingSpeed;

            if (!isDeleting && charIndex === currentWord.length) {
                isDeleting = true;
                delay = pauseFull;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length;
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
