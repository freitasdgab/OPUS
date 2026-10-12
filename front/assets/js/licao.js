    // ====================================
    // CONTROLE DOS SLIDES DE EXPLICAÇÃO
    // ====================================
    let currentSlide = 0;
    const slidesExpl = document.querySelectorAll('.explanation-slide');
    const totalSlides = slidesExpl.length;
    const dotsContainer = document.getElementById('slideDots');
    const btnPrev = document.getElementById('btnPrevSlide');
    const btnNext = document.getElementById('btnNextSlide');

    // Inicializa os dots
    function initDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('div');
            dot.className = `dot-indicator ${i === 0 ? 'active' : ''}`;
            dot.addEventListener('click', () => showSlide(i));
            dotsContainer.appendChild(dot);
        }
    }

    function showSlide(index) {
        if (index < 0 || index >= totalSlides) return;
        
        slidesExpl[currentSlide].classList.remove('active');
        currentSlide = index;
        slidesExpl[currentSlide].classList.add('active');

        // Atualizar dots
        const dots = document.querySelectorAll('.dot-indicator');
        dots.forEach((dot, idx) => {
            if (idx === currentSlide) dot.classList.add('active');
            else dot.classList.remove('active');
        });

        // Configurar botões
        if (btnPrev) btnPrev.disabled = currentSlide === 0;
        
        if (btnNext) {
            if (currentSlide === totalSlides - 1) {
                btnNext.textContent = 'COMEÇAR EXERCÍCIOS';
                btnNext.className = 'btn-nav btn-next-slide';
            } else {
                btnNext.textContent = 'PRÓXIMO';
                btnNext.className = 'btn-nav btn-next-slide';
            }
        }
    }

    window.changeSlide = function(direction) {
        if (currentSlide === totalSlides - 1 && direction === 1) {
            startQuizPhase();
            return;
        }
        showSlide(currentSlide + direction);
    };

    function startQuizPhase() {
        if (totalQuestions === 0) {
            // Se não houver perguntas, finaliza a lição automaticamente com sucesso
            document.getElementById('input_acertos').value = 3; 
            document.getElementById('quiz-form-hidden').submit();
            return;
        }

        const explPhase = document.getElementById('explanationPhase');
        const quizPhase = document.getElementById('quizPhase');
        
        if (explPhase) {
            explPhase.classList.add('leaving-phase');
            setTimeout(() => {
                explPhase.style.display = 'none';
                if (quizPhase) {
                    quizPhase.style.display = 'flex';
                    quizPhase.style.opacity = 0;
                    setTimeout(() => {
                        quizPhase.style.opacity = 1;
                        quizPhase.style.transition = 'opacity 0.4s ease';
                    }, 50);
                }
            }, 300);
        } else if (quizPhase) {
            quizPhase.style.display = 'flex';
            quizPhase.style.opacity = 1;
        }
    }

    if (totalSlides > 0) {
        initDots();
        showSlide(0);
    } else {
        startQuizPhase();
    }


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

    // Helper seguro para tocar efeitos sonoros via OpusAudio
    function playAudio(type) {
        if (window.OpusAudio && typeof window.OpusAudio[type] === 'function') {
            window.OpusAudio[type]();
        }
    }

    // Configuração e persistência do botão de som
    const btnSoundToggle = document.getElementById('btnSoundToggle');
    function syncSoundToggleUI() {
        if (!btnSoundToggle) return;
        const muted = window.OpusAudio ? window.OpusAudio.isMuted() : false;
        const icon = btnSoundToggle.querySelector('i');
        if (icon) {
            icon.className = muted ? 'fa-solid fa-volume-xmark' : 'fa-solid fa-volume-high';
        }
        btnSoundToggle.classList.toggle('muted', muted);
        btnSoundToggle.setAttribute('title', muted ? 'Ativar som' : 'Desativar som');
    }

    if (btnSoundToggle) {
        syncSoundToggleUI();
        btnSoundToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (window.OpusAudio) {
                window.OpusAudio.toggleMute();
                syncSoundToggleUI();
            }
        });
    }
    window.addEventListener('opus:sound-toggle', syncSoundToggleUI);

    // Atualiza fala do mascote com animação pop viva
    function setMascotSpeech(text, statusClass = '') {
        if (!quizSpeechBubble) return;
        quizSpeechBubble.className = 'quiz-speech-bubble ' + statusClass;
        quizSpeechBubble.textContent = text;
        quizSpeechBubble.classList.remove('bubble-pop');
        void quizSpeechBubble.offsetWidth; // reflow para reiniciar animação
        quizSpeechBubble.classList.add('bubble-pop');
    }

    // Mascote clicável com reação divertida
    if (opiQuizImg) {
        opiQuizImg.addEventListener('click', function() {
            playAudio('playClick');
            opiQuizImg.classList.remove('opi-bounce-animation', 'opi-shake-animation', 'opi-celebrate-animation');
            void opiQuizImg.offsetWidth;
            opiQuizImg.classList.add('opi-bounce-animation');

            const frasesInterativas = [
                "Você consegue! Vamos aprender Java juntos! 🚀",
                "Mantenha o foco! Errar faz parte do aprendizado! 💡",
                "Estou aqui torcendo por você! Bora codar! 💻",
                "Analise cada opção com atenção! Você é capaz! ✨",
                "Java é poder puro! Vamos conquistar essa lição! ☕"
            ];
            setMascotSpeech(frasesInterativas[Math.floor(Math.random() * frasesInterativas.length)]);
        });
    }

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
        const pct = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
        fill.style.width = pct + '%';
        text.textContent = answeredCount + '/' + totalQuestions;
    }

    // Selecionar alternativa
    function selectOption(el) {
        if (state === 'checked') return;

        playAudio('playClick');

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

    // Handlers para o tipo interativo completar_codigo (Word Chips)
    window.selectWordChip = function(btnChip) {
        if (state === 'checked') return;
        playAudio('playClick');

        const slide = btnChip.closest('.question-slide');
        const slot = slide.querySelector('.code-blank-slot');
        const filledText = slot.querySelector('.blank-filled-text');
        
        // Se já havia um chip selecionado, devolve o anterior
        const prevSelected = slide.querySelector('.word-chip.used');
        if (prevSelected) {
            prevSelected.classList.remove('used');
        }

        // Marca o novo como used
        btnChip.classList.add('used');

        // Preenche a lacuna
        const word = btnChip.getAttribute('data-word');
        const letterVal = btnChip.getAttribute('data-value');
        filledText.textContent = word;
        slot.classList.add('filled');

        // Define a resposta selecionada
        selectedValue = letterVal;

        // Habilita o botão VERIFICAR
        const btn = document.getElementById('btnAction');
        btn.disabled = false;
    };

    window.removeBlankWord = function(slot) {
        if (state === 'checked') return;
        if (!slot.classList.contains('filled')) return;

        playAudio('playClick');
        const slide = slot.closest('.question-slide');
        const filledText = slot.querySelector('.blank-filled-text');
        filledText.textContent = '';
        slot.classList.remove('filled');

        // Libera o chip selecionado
        const usedChip = slide.querySelector('.word-chip.used');
        if (usedChip) {
            usedChip.classList.remove('used');
        }

        selectedValue = null;
        const btn = document.getElementById('btnAction');
        btn.disabled = true;
    };

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
            const rawCorrect = slide.getAttribute('data-correct') ? slide.getAttribute('data-correct').trim() : '';
            const rawCorrectText = slide.getAttribute('data-correct-text') ? slide.getAttribute('data-correct-text').trim() : '';
            const expectedCode = rawCorrectText || rawCorrect;
            const userVal = selectedValue.trim();
            
            const normalize = s => {
                let str = s.replace(/\s+/g, ' ').trim();
                str = str.replace(/println\s*\(/g, 'println(');
                str = str.replace(/\(\s+/g, '(').replace(/\s+\)/g, ')');
                return str;
            };
            const stripSemi = s => s.replace(/;\s*$/, '').trim();

            const normUser = normalize(userVal);
            const normExpected = normalize(expectedCode);

            // Validação estrita de maiúsculas/minúsculas para Java (ex: System vs system)
            isCorrect = (normUser === normExpected ||
                         stripSemi(normUser) === stripSemi(normExpected));
        } else {
            isCorrect = selectedValue === correct;
        }

        // Salvar resposta no form hidden
        document.getElementById('resp_' + perguntaId).value = selectedValue;

        if (tipo === 'multipla_escolha') {
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
        } else if (tipo === 'completar_codigo') {
            const slot = slide.querySelector('.code-blank-slot');
            const chips = slide.querySelectorAll('.word-chip');
            chips.forEach(chip => chip.disabled = true);
            if (slot) {
                if (isCorrect) {
                    slot.style.borderColor = '#58cc02';
                    slot.style.borderBottomColor = '#46a302';
                } else {
                    slot.style.borderColor = '#ff4b4b';
                    slot.style.borderBottomColor = '#cc3c3c';
                    slot.style.color = '#ff4b4b';
                }
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

            // Efeito sonoro alegre/ascendente de sucesso estilo Duolingo
            playAudio('playSuccess');

            // Mascote reativo (se existir)
            if (opiQuizImg) {
                opiQuizImg.src = MASCOTE_FELIZ;
                opiQuizImg.className = "opi-quiz-mascote opi-celebrate-animation";
            }

            const acertoFrases = [
                "Excelente! Você acertou em cheio! 🚀",
                "Sensacional! Resposta correta! 💎",
                "Perfeito! Seu conhecimento em Java está afiado! 🎉",
                "Mandou bem demais! Continue assim! ⭐",
                "Brilhante! Essa lógica foi impecável! 💡"
            ];
            setMascotSpeech(acertoFrases[Math.floor(Math.random() * acertoFrases.length)], "bubble-correct");

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
            // Efeito sonoro grave/buzz sutil amigável estilo Duolingo
            playAudio('playError');

            // Mascote reativo (se existir)
            if (opiQuizImg) {
                opiQuizImg.src = MASCOTE_TRISTE;
                opiQuizImg.className = "opi-quiz-mascote opi-shake-animation";
            }

            const erroFrases = [
                "Oops! Não foi dessa vez... Mas errar faz parte! 💡",
                "Quase lá! Veja a resposta correta para aprender! 🔍",
                "Sem problemas! A prática leva à perfeição! 💪",
                "Respira fundo, na próxima você acerta com certeza! ✨"
            ];
            setMascotSpeech(erroFrases[Math.floor(Math.random() * erroFrases.length)], "bubble-wrong");

            // Footer vermelho
            footer.className = 'quiz-footer feedback-wrong';
            feedbackMsg.className = 'feedback-message msg-wrong visible';
            
            // Encontrar texto da alternativa correta
            let correctText = correct;
            if (tipo === 'multipla_escolha') {
                const options = slide.querySelectorAll('.duo-option');
                options.forEach(opt => {
                    if (opt.getAttribute('data-value') === correct) {
                        correctText = opt.querySelector('.option-text').textContent;
                    }
                });
            } else if (tipo === 'completar_codigo') {
                const chips = slide.querySelectorAll('.word-chip');
                chips.forEach(chip => {
                    if (chip.getAttribute('data-value') === correct) {
                        correctText = chip.getAttribute('data-word');
                    }
                });
            } else {
                const expectedCode = slide.getAttribute('data-correct-text');
                if (expectedCode && expectedCode.trim() !== '') {
                    correctText = expectedCode;
                }
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
            const passou = totalAcertos > 0;

            // Reset footer (mesmo antes de enviar, evita "flash" do feedback anterior)
            footer.className = 'quiz-footer';
            feedbackMsg.className = 'feedback-message';
            feedbackMsg.innerHTML = '';

            if (gabaritou) {
                // Comemoração épica: fanfarra triunfante + mascote em vitória contínua + confete
                playAudio('playLessonComplete');
                opiQuizImg.src = MASCOTE_FELIZ;
                opiQuizImg.className = "opi-quiz-mascote opi-victory-animation";
                setMascotSpeech("Mandou muito bem! Você gabaritou a lição! 🏆", "bubble-correct");
                launchConfetti();

                setTimeout(() => {
                    document.getElementById('input_acertos').value = totalAcertos;
                    document.getElementById('quiz-form-hidden').submit();
                }, 1600);
            } else if (passou) {
                // Passou na lição: fanfarra de conclusão + mascote alegre pulando
                playAudio('playLessonComplete');
                opiQuizImg.src = MASCOTE_FELIZ;
                opiQuizImg.className = "opi-quiz-mascote opi-bounce-animation";
                setMascotSpeech("Muito bem! Lição concluída com sucesso! 📊", "bubble-correct");

                setTimeout(() => {
                    document.getElementById('input_acertos').value = totalAcertos;
                    document.getElementById('quiz-form-hidden').submit();
                }, 1200);
            } else {
                // 0 acertos: som reconfortante de derrota suave + mascote solidário com shake
                playAudio('playLessonDefeat');
                opiQuizImg.src = MASCOTE_TRISTE;
                opiQuizImg.className = "opi-quiz-mascote opi-shake-animation";
                setMascotSpeech("Não foi dessa vez... Mas errar faz parte da jornada de programação! 💪", "bubble-wrong");

                setTimeout(() => {
                    document.getElementById('input_acertos').value = totalAcertos;
                    document.getElementById('quiz-form-hidden').submit();
                }, 1300);
            }
            return;
        }

        playAudio('playClick');

        // Reset do Mascote para aguardando/pensando
        opiQuizImg.src = MASCOTE_EXPLICANDO;
        opiQuizImg.className = "opi-quiz-mascote opi-float-animation";
        
        const pensandoFrases = [
            "Estou preparando o próximo desafio... 🤔",
            "Vamos para a próxima! Mantenha o foco! 💪",
            "Pronto para a próxima pergunta? Vamos lá! 🔍",
            "Mais um desafio para testar sua lógica Java! ☕"
        ];
        setMascotSpeech(pensandoFrases[Math.floor(Math.random() * pensandoFrases.length)]);

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

    // Atalhos de teclado: Enter para verificar/continuar, 1-3 para selecionar alternativas
    document.addEventListener('keydown', function(e) {
        // Se o foco estiver em um campo de texto ou textarea, não intercepta números 1, 2, 3
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
            if (e.key === 'Enter') {
                if (e.target.tagName === 'TEXTAREA' && e.shiftKey) {
                    return; // Permite quebra de linha com Shift+Enter
                }
                const btn = document.getElementById('btnAction');
                if (!btn.disabled) {
                    e.preventDefault();
                    handleAction();
                }
            }
            return;
        }

        // Se estiver na fase de explicação, permite navegar com Enter ou Setas
        const explPhase = document.getElementById('explanationPhase');
        if (explPhase && explPhase.style.display !== 'none') {
            if (e.key === 'ArrowRight' || e.key === 'Enter') {
                window.changeSlide(1);
            } else if (e.key === 'ArrowLeft') {
                window.changeSlide(-1);
            }
            return;
        }

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

// ====================================
// AVISO DE SAÍDA (sair no meio das perguntas)
// ====================================
function abrirAvisoSaida() {
    try {
        if (typeof playAudio === 'function') playAudio('playClick');
    } catch(e){}
    const modal = document.getElementById('exitWarningModal');
    if (modal) modal.classList.add('active');
}

function fecharAvisoSaida() {
    try {
        if (typeof playAudio === 'function') playAudio('playClick');
    } catch(e){}
    const modal = document.getElementById('exitWarningModal');
    if (modal) modal.classList.remove('active');
}

async function confirmarSaida() {
    const btnConfirm = document.querySelector('.btn-exit-confirm');
    if (btnConfirm) {
        btnConfirm.disabled = true;
        btnConfirm.textContent = 'Saindo...';
    }
    try {
        if (typeof playAudio === 'function') playAudio('playClick');
    } catch(e){}

    try {
        await fetch('../../back/penalizar_saida.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            keepalive: true
        });
    } catch (e) {
        console.error('Erro ao penalizar saída:', e);
    }

    window.location.href = 'dashboard.php';
}

window.abrirAvisoSaida = abrirAvisoSaida;
window.fecharAvisoSaida = fecharAvisoSaida;
window.confirmarSaida = confirmarSaida;
