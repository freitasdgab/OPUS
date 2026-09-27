    // ====================================
    // CONTROLE DOS SLIDES DE EXPLICAÇÃO
    // ====================================
    let currentSlide = 0;
    const slidesExpl = document.querySelectorAll('.explanation-slide');
    const totalSlides = slidesExpl.length;
    const dotsContainer = document.getElementById('slideDots');
    const btnPrev = document.getElementById('btnPrevSlide');
    const btnNext = document.getElementById('btnNextSlide');

    // Como o usuário pediu para remover o "textão" inicial:
    // Nós ignoramos os slides de explicação e exibimos a fase de Quiz diretamente.
    // function initDots() { ... }
    // function showSlide(index) { ... }
    // function changeSlide(direction) { ... }

    // Oculta a fase de explicação caso ela ainda exista (por precaução)
    const explPhase = document.getElementById('explanationPhase');
    if (explPhase) explPhase.style.display = 'none';

    // startQuizPhase inicializa e confere as perguntas
    function startQuizPhase() {
        if (totalQuestions === 0) {
            // Se não houver perguntas, finaliza a lição automaticamente com sucesso
            document.getElementById('input_acertos').value = 3; 
            document.getElementById('quiz-form-hidden').submit();
            return;
        }

        const quizPhase = document.getElementById('quizPhase');
        quizPhase.style.display = 'flex';
        quizPhase.style.opacity = 1;
    }

    // A chamada para startQuizPhase foi movida para depois da inicialização das variáveis.


    // ====================================
    // LÓGICA DO QUIZ ESTILO DUOLINGO
    // ====================================
    const slidesQuiz = document.querySelectorAll('.question-slide');
    const totalQuestions = slidesQuiz.length;
    let currentIndex = 0;
    let totalAcertos = 0;
    let state = 'selecting'; // 'selecting' | 'checked'
    let selectedValue = null;

    const opiQuizImg = document.getElementById('opiQuizImg');
    const quizSpeechBubble = document.getElementById('quizSpeechBubble');

    // Imagens do mascote do capítulo atual (definidas pelo PHP via data-attributes
    // em .quiz-grid-body, de acordo com a cor/capítulo da lição)
    const quizGridBody = document.querySelector('.quiz-grid-body');
    const MASCOTE_EXPLICANDO = quizGridBody.dataset.mascoteExplicando;
    const MASCOTE_FELIZ = quizGridBody.dataset.mascoteFeliz;
    const MASCOTE_TRISTE = quizGridBody.dataset.mascoteTriste;

    // Atualiza a barra de progresso do quiz
    function updateProgress(answeredCount) {
        const fill = document.getElementById('progressFill');
        const text = document.getElementById('progressText');
        const pct = (answeredCount / totalQuestions) * 100;
        fill.style.width = pct + '%';
        text.textContent = answeredCount + '/' + totalQuestions;
    }

    // Selecionar alternativa
    function selectOption(el) {
        if (state === 'checked') return;

        const slide = el.closest('.question-slide');
        const options = slide.querySelectorAll('.duo-option');

        // Remove seleção anterior
        options.forEach(opt => opt.classList.remove('selected'));

        // Seleciona a nova
        el.classList.add('selected');
        selectedValue = el.getAttribute('data-value');

        // Habilita o botão
        const btn = document.getElementById('btnAction');
        btn.disabled = false;
    }

    // Ação do botão (VERIFICAR ou CONTINUAR)
    function handleAction() {
        if (state === 'selecting') {
            verificar();
        } else {
            continuar();
        }
    }

    // Handlers para novos tipos
    window.checkTypingInput = function(el) {
        if (state === 'checked') return;
        const val = el.value.trim();
        selectedValue = val ? val : null;
        const btn = document.getElementById('btnAction');
        btn.disabled = !selectedValue;
    };

    window.checkSelectInput = function(el) {
        if (state === 'checked') return;
        selectedValue = el.value;
        const btn = document.getElementById('btnAction');
        btn.disabled = !selectedValue;
    };

    window.checkInlineInput = function(el) {
        if (state === 'checked') return;
        const val = el.value.trim();
        selectedValue = val ? val : null;
        const btn = document.getElementById('btnAction');
        btn.disabled = !selectedValue;
    };

    // Verificar resposta
    function verificar() {
        if (!selectedValue) return;

        const slide = slidesQuiz[currentIndex];
        const correct = slide.getAttribute('data-correct').trim().toUpperCase();
        const tipo = slide.getAttribute('data-tipo') || 'multipla_escolha';
        const perguntaId = slide.getAttribute('data-pergunta-id');
        const footer = document.getElementById('quizFooter');
        const feedbackMsg = document.getElementById('feedbackMsg');
        const btn = document.getElementById('btnAction');

        let isCorrect = false;

        if (tipo === 'digitar_codigo' || tipo === 'completar_codigo_escrito') {
            const expectedText = correct; // A, B, C
            const expectedCode = slide.getAttribute('data-correct-text') ? slide.getAttribute('data-correct-text').trim().toUpperCase() : '';
            const userVal = selectedValue.trim().toUpperCase();
            
            isCorrect = (userVal === expectedText || userVal === expectedCode);
        } else {
            isCorrect = selectedValue === correct;
        }

        // Salvar resposta no form hidden
        document.getElementById('resp_' + perguntaId).value = selectedValue;

        if (tipo === 'multipla_escolha' || tipo === 'completar_codigo') {
            const options = slide.querySelectorAll('.duo-option');
            // Desabilitar todas as opções
            options.forEach(opt => {
                opt.classList.add('disabled');
                if (opt.getAttribute('data-value') === correct && !isCorrect) {
                    opt.classList.add('reveal-correct');
                }
            });
            // Marcar a selecionada
            const selectedEl = slide.querySelector('.duo-option.selected');
            if (selectedEl) {
                if (isCorrect) selectedEl.classList.add('correct');
                else selectedEl.classList.add('wrong');
            }
        } else if (tipo === 'digitar_codigo') {
            const input = slide.querySelector('.typing-input');
            input.disabled = true;
            if (isCorrect) input.style.borderColor = '#58cc02';
            else input.style.borderColor = '#ff4b4b';
        } else if (tipo === 'completar_codigo_escrito') {
            const inputInline = slide.querySelector('.code-input-inline');
            inputInline.disabled = true;
            if (isCorrect) inputInline.style.borderColor = '#58cc02';
            else inputInline.style.borderColor = '#ff4b4b';
        }

        if (isCorrect) {
            totalAcertos++;

            // Mascote reativo: feliz com comemoração e bounce
            opiQuizImg.src = MASCOTE_FELIZ;
            opiQuizImg.className = "opi-quiz-mascote opi-bounce-animation";

            const acertoFrases = [
                "Excelente! Você acertou em cheio! 🚀",
                "Sensacional! Resposta correta! 💎",
                "Perfeito! Seu conhecimento em Java está afiado! 🎉"
            ];
            quizSpeechBubble.textContent = acertoFrases[Math.floor(Math.random() * acertoFrases.length)];
            quizSpeechBubble.className = "quiz-speech-bubble bubble-correct";

            // Footer verde
            footer.className = 'quiz-footer feedback-correct';
            feedbackMsg.className = 'feedback-message msg-correct visible';
            feedbackMsg.innerHTML = '<div class="feedback-icon"><i class="fa-solid fa-check"></i></div><div><strong>Excelente!</strong><br><span style="font-weight:400;font-size:13px;opacity:0.85">Resposta correta!</span></div>';

            // Botão continuar verde
            btn.className = 'btn-quiz-action btn-continue-correct';
            btn.textContent = 'CONTINUAR';

            // Mini confete
            launchConfetti();
        } else {
            // Mascote reativo: triste com shake
            opiQuizImg.src = MASCOTE_TRISTE;
            opiQuizImg.className = "opi-quiz-mascote opi-shake-animation";

            quizSpeechBubble.textContent = "Oops! Não foi dessa vez... Mas errar faz parte do aprendizado! 💡";
            quizSpeechBubble.className = "quiz-speech-bubble bubble-wrong";

            // Footer vermelho
            footer.className = 'quiz-footer feedback-wrong';
            feedbackMsg.className = 'feedback-message msg-wrong visible';
            
            // Encontrar texto da alternativa correta
            let correctText = correct;
            if (tipo === 'multipla_escolha' || tipo === 'completar_codigo') {
                const options = slide.querySelectorAll('.duo-option');
                options.forEach(opt => {
                    if (opt.getAttribute('data-value') === correct) {
                        correctText = opt.querySelector('.option-text').textContent;
                    }
                });
            }
            feedbackMsg.innerHTML = '<div class="feedback-icon"><i class="fa-solid fa-xmark"></i></div><div><strong>Resposta incorreta</strong><br><span style="font-weight:400;font-size:13px;opacity:0.85">Correta: ' + correctText + '</span></div>';

            // Botão continuar vermelho
            btn.className = 'btn-quiz-action btn-continue-wrong';
            btn.textContent = 'CONTINUAR';
        }

        state = 'checked';
    }

    // Continuar para próxima pergunta
    function continuar() {
        const footer = document.getElementById('quizFooter');
        const feedbackMsg = document.getElementById('feedbackMsg');
        const btn = document.getElementById('btnAction');

        // Atualizar progresso
        updateProgress(currentIndex + 1);

        // Se era a última pergunta, decide o desfecho
        if (currentIndex >= totalQuestions - 1) {
            const gabaritou = totalAcertos === totalQuestions;

            // Reset footer (mesmo antes de enviar, evita "flash" do feedback anterior)
            footer.className = 'quiz-footer';
            feedbackMsg.className = 'feedback-message';
            feedbackMsg.innerHTML = '';

            if (gabaritou) {
                // Comemoração especial: mascote feliz + confete extra antes de enviar
                opiQuizImg.src = MASCOTE_FELIZ;
                opiQuizImg.className = "opi-quiz-mascote opi-bounce-animation";
                quizSpeechBubble.textContent = "Mandou muito bem! Você gabaritou a lição! 🏆";
                quizSpeechBubble.className = "quiz-speech-bubble bubble-correct";
                launchConfetti();

                setTimeout(() => {
                    document.getElementById('input_acertos').value = totalAcertos;
                    document.getElementById('quiz-form-hidden').submit();
                }, 1400);
            } else {
                document.getElementById('input_acertos').value = totalAcertos;
                document.getElementById('quiz-form-hidden').submit();
            }
            return;
        }

        // Reset do Mascote para aguardando/pensando
        opiQuizImg.src = MASCOTE_EXPLICANDO;
        opiQuizImg.className = "opi-quiz-mascote opi-float-animation";
        
        const pensandoFrases = [
            "Estou preparando o próximo desafio... 🤔",
            "Vamos para a próxima! Mantenha o foco! 💪",
            "Pronto para a próxima pergunta? Vamos lá! 🔍"
        ];
        quizSpeechBubble.textContent = pensandoFrases[Math.floor(Math.random() * pensandoFrases.length)];
        quizSpeechBubble.className = "quiz-speech-bubble";

        // Reset footer
        footer.className = 'quiz-footer';
        feedbackMsg.className = 'feedback-message';
        feedbackMsg.innerHTML = '';

        // Animar saída do slide atual
        const currentSlideEl = slidesQuiz[currentIndex];
        currentSlideEl.style.display = 'none';

        currentIndex++;
        selectedValue = null;
        state = 'selecting';

        // Mostrar próxima
        slidesQuiz[currentIndex].style.display = 'flex';

        // Reset botão
        btn.className = 'btn-quiz-action btn-check';
        btn.textContent = 'VERIFICAR';
        btn.disabled = true;
    }

    // Confete animado (cobre a tela inteira)
    function launchConfetti() {
        const container = document.getElementById('confettiContainer');
        const colors = ['#58cc02', '#4baf00', '#ffd900', '#ff9600', '#4b8df8', '#ce82ff'];

        for (let i = 0; i < 80; i++) {
            const piece = document.createElement('div');
            piece.className = 'confetti-piece';
            piece.style.left = (Math.random() * 100) + '%';
            piece.style.top = '-10px';
            piece.style.width = (Math.random() * 8 + 5) + 'px';
            piece.style.height = (Math.random() * 8 + 5) + 'px';
            piece.style.background = colors[Math.floor(Math.random() * colors.length)];
            piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            piece.style.animation = `confettiFall ${Math.random() * 2 + 1.5}s ease-out ${Math.random() * 0.5}s forwards`;
            container.appendChild(piece);

            // Limpar após animação
            setTimeout(() => piece.remove(), 3500);
        }
    }

    // Atalho de teclado: Enter para verificar/continuar
    document.addEventListener('keydown', function(e) {
        // Como removemos a explanationPhase, não precisa mais do atalho dela.

        if (e.key === 'Enter') {
            const btn = document.getElementById('btnAction');
            if (!btn.disabled) {
                handleAction();
            }
        }
        // Atalhos 1, 2, 3 para selecionar alternativas
        if (state === 'selecting' && ['1', '2', '3'].includes(e.key)) {
            const slide = slidesQuiz[currentIndex];
            const options = slide.querySelectorAll('.duo-option');
            const idx = parseInt(e.key) - 1;
            if (options[idx]) {
                selectOption(options[idx]);
            }
        }
    });

    // Inicializar progresso
    updateProgress(0);

    // Inicia a fase de quiz imediatamente sem os slides explicativos
    startQuizPhase();

// ====================================
// AVISO DE SAÍDA (sair no meio das perguntas)
// ====================================
function abrirAvisoSaida() {
    document.getElementById('exitWarningModal').classList.add('active');
}

function fecharAvisoSaida() {
    document.getElementById('exitWarningModal').classList.remove('active');
}

function confirmarSaida() {
    // Aplica a penalidade de XP de verdade antes de sair (não é só aviso).
    fetch('../../back/penalizar_saida.php', { method: 'POST' })
        .catch(() => {})
        .finally(() => {
            window.location.href = 'dashboard.php';
        });
}
