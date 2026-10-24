// =========================================================================
// LICAO.JS - FLUXO DE SLIDES DE EXPLICAÇÃO + QUIZ INTERATIVO COM CHIPS
// =========================================================================

(function() {
    // 1. Elementos e Contadores Globais do Quiz e Slides
    const slidesExpl = document.querySelectorAll('.explanation-slide');
    const totalSlides = slidesExpl.length;
    const dotsContainer = document.getElementById('slideDots');
    const btnPrev = document.getElementById('btnPrevSlide');
    const btnNext = document.getElementById('btnNextSlide');

    const slidesQuiz = document.querySelectorAll('.question-slide');
    const totalQuestions = slidesQuiz.length;
    let currentSlide = 0;
    let currentIndex = 0;
    let totalAcertos = 0;
    let state = 'selecting'; // 'selecting' | 'checked'
    let selectedValue = null;

    const opiQuizImg = document.getElementById('opiQuizImg');
    const quizSpeechBubble = document.getElementById('quizSpeechBubble');
    const quizGridBody = document.querySelector('.quiz-grid-body');

    const MASCOTE_EXPLICANDO = quizGridBody ? (quizGridBody.dataset.mascoteExplicando || '') : '';
    const MASCOTE_FELIZ = quizGridBody ? (quizGridBody.dataset.mascoteFeliz || '') : '';
    const MASCOTE_TRISTE = quizGridBody ? (quizGridBody.dataset.mascoteTriste || '') : '';

    // Helper de áudio
    function playAudio(type) {
        if (window.OpusAudio && typeof window.OpusAudio[type] === 'function') {
            try { window.OpusAudio[type](); } catch(e) {}
        }
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // ====================================
    // CONTROLE DOS SLIDES DE EXPLICAÇÃO
    // ====================================
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

        const dots = document.querySelectorAll('.dot-indicator');
        dots.forEach((dot, idx) => {
            if (idx === currentSlide) dot.classList.add('active');
            else dot.classList.remove('active');
        });

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
            const elAcertos = document.getElementById('input_acertos');
            if (elAcertos) elAcertos.value = 6;
            const form = document.getElementById('quiz-form-hidden');
            if (form) form.submit();
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
    // LÓGICA DO QUIZ (PERGUNTAS / ALTERNATIVAS)
    // ====================================
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

    function setMascotSpeech(text, statusClass = '') {
        if (!quizSpeechBubble) return;
        quizSpeechBubble.className = 'quiz-speech-bubble ' + statusClass;
        quizSpeechBubble.textContent = text;
        quizSpeechBubble.classList.remove('bubble-pop');
        void quizSpeechBubble.offsetWidth;
        quizSpeechBubble.classList.add('bubble-pop');
    }

    if (opiQuizImg) {
        opiQuizImg.addEventListener('click', function() {
            playAudio('playClick');
            opiQuizImg.classList.remove('opi-bounce-animation', 'opi-shake-animation', 'opi-celebrate-animation');
            void opiQuizImg.offsetWidth;
            opiQuizImg.classList.add('opi-bounce-animation');
            const frases = [
                "Você consegue! Vamos aprender Java juntos! 🚀",
                "Mantenha o foco! Errar faz parte do aprendizado! 💡",
                "Estou aqui torcendo por você! Bora codar! 💻",
                "Java é poder puro! Vamos conquistar essa lição! ☕"
            ];
            setMascotSpeech(frases[Math.floor(Math.random() * frases.length)]);
        });
    }

    function updateProgress(answeredCount) {
        const fill = document.getElementById('progressFill');
        const text = document.getElementById('progressText');
        const pct = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
        if (fill) fill.style.width = pct + '%';
        if (text) text.textContent = answeredCount + '/' + totalQuestions;
    }

    // Selecionar alternativa (múltipla escolha)
    window.selectOption = function(el) {
        if (state === 'checked') return;

        playAudio('playClick');

        const slide = el.closest('.question-slide');
        const options = slide.querySelectorAll('.duo-option');
        options.forEach(opt => opt.classList.remove('selected'));

        el.classList.add('selected');
        selectedValue = el.getAttribute('data-value');

        const btn = document.getElementById('btnAction');
        if (btn) btn.disabled = false;
    };

    // Handlers para o tipo interativo completar_codigo (Word Chips)
    window.selectWordChip = function(btnChip) {
        if (state === 'checked') return;
        playAudio('playClick');

        const slide = btnChip.closest('.question-slide');
        const slot = slide.querySelector('.code-blank-slot');
        if (!slot) return;
        const filledText = slot.querySelector('.blank-filled-text');
        
        const prevSelected = slide.querySelector('.word-chip.used');
        if (prevSelected) {
            prevSelected.classList.remove('used');
        }

        btnChip.classList.add('used');

        const word = btnChip.getAttribute('data-word');
        const letterVal = btnChip.getAttribute('data-value');
        if (filledText) filledText.textContent = word;
        slot.classList.add('filled');

        selectedValue = letterVal;

        const btn = document.getElementById('btnAction');
        if (btn) btn.disabled = false;
    };

    window.removeBlankWord = function(slot) {
        if (state === 'checked') return;
        if (!slot || !slot.classList.contains('filled')) return;

        playAudio('playClick');
        const slide = slot.closest('.question-slide');
        const filledText = slot.querySelector('.blank-filled-text');
        if (filledText) filledText.textContent = '';
        slot.classList.remove('filled');

        const usedChip = slide ? slide.querySelector('.word-chip.used') : null;
        if (usedChip) {
            usedChip.classList.remove('used');
        }

        selectedValue = null;
        const btn = document.getElementById('btnAction');
        if (btn) btn.disabled = true;
    };

    // ==========================================
    // 1. PROBLEMAS DE PARSONS (ORDENAÇÃO DE CÓDIGO)
    // ==========================================
    window.parsonsBlockClick = function(btnChip) {
        if (state === 'checked') return;
        playAudio('playClick');

        const wrapper = btnChip.closest('.parsons-wrapper');
        if (!wrapper) return;
        const dropzone = wrapper.querySelector('.parsons-dropzone');
        const bank = wrapper.querySelector('.parsons-bank');
        if (!dropzone || !bank) return;

        if (btnChip.closest('.parsons-bank')) {
            const placeholder = dropzone.querySelector('.parsons-placeholder');
            if (placeholder) placeholder.style.display = 'none';

            const targetItem = document.createElement('div');
            targetItem.className = 'parsons-target-item';
            targetItem.setAttribute('data-block-id', btnChip.getAttribute('data-block-id'));
            
            targetItem.innerHTML = `
                <span class="parsons-line-num"></span>
                <span class="parsons-target-code">${btnChip.querySelector('.parsons-chip-code').innerHTML}</span>
                <div class="parsons-item-controls">
                    <button type="button" class="parsons-ctrl-btn" title="Subir" onclick="parsonsShiftBlock(event, this, -1)"><i class="fa-solid fa-chevron-up"></i></button>
                    <button type="button" class="parsons-ctrl-btn" title="Descer" onclick="parsonsShiftBlock(event, this, 1)"><i class="fa-solid fa-chevron-down"></i></button>
                    <button type="button" class="parsons-ctrl-btn parsons-remove-btn" title="Remover" onclick="parsonsRemoveFromTarget(event, this)"><i class="fa-solid fa-xmark"></i></button>
                </div>
            `;
            
            targetItem.addEventListener('click', function(e) {
                if (e.target.closest('.parsons-ctrl-btn')) return;
                parsonsReturnChip(targetItem, btnChip, wrapper);
            });

            dropzone.appendChild(targetItem);
            btnChip.classList.add('used');
        }

        parsonsUpdateState(wrapper);
    };

    window.parsonsShiftBlock = function(e, btn, direction) {
        e.stopPropagation();
        if (state === 'checked') return;
        playAudio('playClick');

        const item = btn.closest('.parsons-target-item');
        const dropzone = item.parentElement;
        const wrapper = item.closest('.parsons-wrapper');

        if (direction === -1 && item.previousElementSibling && !item.previousElementSibling.classList.contains('parsons-placeholder')) {
            dropzone.insertBefore(item, item.previousElementSibling);
        } else if (direction === 1 && item.nextElementSibling) {
            dropzone.insertBefore(item.nextElementSibling, item);
        }

        parsonsUpdateState(wrapper);
    };

    window.parsonsRemoveFromTarget = function(e, btn) {
        e.stopPropagation();
        if (state === 'checked') return;
        playAudio('playClick');

        const item = btn.closest('.parsons-target-item');
        const wrapper = item.closest('.parsons-wrapper');
        const blockId = item.getAttribute('data-block-id');
        const chip = wrapper.querySelector(`.parsons-chip[data-block-id="${blockId}"]`);
        
        parsonsReturnChip(item, chip, wrapper);
    };

    function parsonsReturnChip(targetItem, chip, wrapper) {
        if (targetItem) targetItem.remove();
        if (chip) chip.classList.remove('used');
        parsonsUpdateState(wrapper);
    }

    function parsonsUpdateState(wrapper) {
        const dropzone = wrapper.querySelector('.parsons-dropzone');
        const items = dropzone.querySelectorAll('.parsons-target-item');
        const total = parseInt(wrapper.getAttribute('data-total') || '0', 10);
        const placeholder = dropzone.querySelector('.parsons-placeholder');

        if (items.length === 0) {
            if (placeholder) placeholder.style.display = 'flex';
        } else {
            if (placeholder) placeholder.style.display = 'none';
        }

        items.forEach((it, idx) => {
            const num = it.querySelector('.parsons-line-num');
            if (num) num.textContent = (idx + 1);
        });

        const btn = document.getElementById('btnAction');

        if (items.length === total && total > 0) {
            const currentOrder = Array.from(items).map(it => it.getAttribute('data-block-id')).join(',');
            selectedValue = currentOrder;
            if (btn) btn.disabled = false;
        } else {
            selectedValue = null;
            if (btn) btn.disabled = true;
        }
    }

    // ==========================================
    // 2. CAÇADOR DE BUGS (SPOT THE BUG)
    // ==========================================
    window.selectBugLine = function(lineEl) {
        if (state === 'checked') return;
        playAudio('playClick');

        const wrapper = lineEl.closest('.spot-bug-wrapper');
        if (!wrapper) return;

        const allLines = wrapper.querySelectorAll('.spot-bug-line');
        allLines.forEach(l => l.classList.remove('selected'));

        lineEl.classList.add('selected');
        selectedValue = lineEl.getAttribute('data-line');

        const btn = document.getElementById('btnAction');
        if (btn) btn.disabled = false;
    };

    // ==========================================
    // 3. PREDIÇÃO DE SAÍDA (OUTPUT PREDICTION)
    // ==========================================
    window.selectPredictionOption = function(optionEl) {
        if (state === 'checked') return;
        playAudio('playClick');

        const slide = optionEl.closest('.question-slide');
        const options = slide.querySelectorAll('.duo-option');
        options.forEach(opt => opt.classList.remove('selected'));

        optionEl.classList.add('selected');
        selectedValue = optionEl.getAttribute('data-value');

        const termOutput = slide.querySelector('.terminal-sim-output');
        const textVal = optionEl.getAttribute('data-text') || optionEl.querySelector('.option-text')?.textContent || selectedValue;
        if (termOutput) {
            termOutput.innerHTML = `<span class="term-predicted-val">${escapeHtml(textVal)}</span> <span class="term-blinking-cursor">█</span>`;
        }

        const btn = document.getElementById('btnAction');
        if (btn) btn.disabled = false;
    };

    // ==========================================
    // 4. PAREAMENTO DE CONCEITOS (PAIR MATCHING)
    // ==========================================
    let firstPairCard = null;

    window.selectPairCard = function(cardEl) {
        if (state === 'checked' || cardEl.classList.contains('pair-matched') || cardEl.classList.contains('pair-wrong')) return;

        const wrapper = cardEl.closest('.pair-match-wrapper');
        if (!wrapper) return;

        if (cardEl === firstPairCard) {
            cardEl.classList.remove('pair-selected');
            firstPairCard = null;
            playAudio('playClick');
            return;
        }

        if (!firstPairCard) {
            playAudio('playClick');
            firstPairCard = cardEl;
            firstPairCard.classList.add('pair-selected');
            return;
        }

        if (firstPairCard.getAttribute('data-side') === cardEl.getAttribute('data-side')) {
            playAudio('playClick');
            firstPairCard.classList.remove('pair-selected');
            firstPairCard = cardEl;
            firstPairCard.classList.add('pair-selected');
            return;
        }

        const id1 = firstPairCard.getAttribute('data-pair-id');
        const id2 = cardEl.getAttribute('data-pair-id');
        const card1 = firstPairCard;
        const card2 = cardEl;
        firstPairCard = null;

        if (id1 === id2) {
            playAudio('playClick');
            card1.classList.remove('pair-selected');
            card1.classList.add('pair-matched');
            card2.classList.add('pair-matched');

            let matchedCount = parseInt(wrapper.getAttribute('data-matched-count') || '0', 10) + 1;
            const totalPairs = parseInt(wrapper.getAttribute('data-total-pairs') || '0', 10);
            wrapper.setAttribute('data-matched-count', matchedCount);

            const badgeRemaining = wrapper.querySelector('.pair-remaining-num');
            if (badgeRemaining) badgeRemaining.textContent = (totalPairs - matchedCount);

            if (matchedCount >= totalPairs) {
                selectedValue = 'ALL_MATCHED';
                const slide = wrapper.closest('.question-slide');
                if (slide) slide.setAttribute('data-correct', 'ALL_MATCHED');

                setTimeout(() => {
                    verificar();
                }, 350);
            }
        } else {
            playAudio('playError');
            card1.classList.remove('pair-selected');
            card1.classList.add('pair-wrong');
            card2.classList.add('pair-wrong');

            setTimeout(() => {
                card1.classList.remove('pair-wrong');
                card2.classList.remove('pair-wrong');
            }, 600);
        }
    };

    // Ação do botão principal (VERIFICAR ou CONTINUAR)
    window.handleAction = function() {
        if (state === 'selecting') {
            verificar();
        } else {
            continuar();
        }
    };

    // Verificar resposta
    function verificar() {
        if (!selectedValue) return;

        const slide = slidesQuiz[currentIndex];
        if (!slide) return;

        const correct = (slide.getAttribute('data-correct') || '').trim().toUpperCase();
        const tipo = slide.getAttribute('data-tipo') || 'multipla_escolha';
        const perguntaId = slide.getAttribute('data-pergunta-id');
        const footer = document.getElementById('quizFooter');
        const feedbackMsg = document.getElementById('feedbackMsg');
        const btn = document.getElementById('btnAction');

        let isCorrect = false;

        if (tipo === 'spot_bug') {
            const targetLine = slide.querySelector('.spot-bug-wrapper')?.getAttribute('data-target-line') || correct;
            isCorrect = (String(selectedValue).trim() === String(targetLine).trim());
        } else if (tipo === 'ordenar_codigo' || tipo === 'parsons') {
            const expectedOrder = slide.querySelector('.parsons-wrapper')?.getAttribute('data-expected-order') || correct;
            isCorrect = (String(selectedValue).trim() === String(expectedOrder).trim());
        } else if (tipo === 'pareamento') {
            isCorrect = (selectedValue === 'ALL_MATCHED' || selectedValue === correct);
        } else {
            isCorrect = (String(selectedValue).trim().toUpperCase() === correct);
        }

        // Salva resposta no form hidden se o input existir
        const inputResp = document.getElementById('resp_' + perguntaId);
        if (inputResp) inputResp.value = selectedValue;

        if (tipo === 'multipla_escolha') {
            const options = slide.querySelectorAll('.duo-option');
            options.forEach(opt => {
                opt.classList.add('disabled');
                if (opt.getAttribute('data-value') === correct && !isCorrect) {
                    opt.classList.add('reveal-correct');
                }
            });
            const selectedEl = slide.querySelector('.duo-option.selected');
            if (selectedEl) {
                if (isCorrect) selectedEl.classList.add('correct');
                else selectedEl.classList.add('wrong');
            }
        } else if (tipo === 'predicao_saida') {
            const options = slide.querySelectorAll('.duo-option');
            options.forEach(opt => {
                opt.classList.add('disabled');
                if (opt.getAttribute('data-value') === correct && !isCorrect) {
                    opt.classList.add('reveal-correct');
                }
            });
            const selectedEl = slide.querySelector('.duo-option.selected');
            if (selectedEl) {
                if (isCorrect) selectedEl.classList.add('correct');
                else selectedEl.classList.add('wrong');
            }
            const termOutput = slide.querySelector('.terminal-sim-output');
            if (termOutput) {
                if (isCorrect) {
                    termOutput.innerHTML = `<span style="color:#50fa7b; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Saída confirmada:</span><br>` + termOutput.innerHTML;
                } else {
                    let corrText = correct;
                    slide.querySelectorAll('.duo-option').forEach(o => {
                        if (o.getAttribute('data-value') === correct) corrText = o.getAttribute('data-text') || correct;
                    });
                    termOutput.innerHTML = `<span style="color:#ff5555; font-weight:700;"><i class="fa-solid fa-circle-xmark"></i> Previsão incorreta!</span><br><span style="color:#50fa7b;">Saída esperada no terminal:</span> <strong>${escapeHtml(corrText)}</strong>`;
                }
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
        } else if (tipo === 'spot_bug') {
            const targetLine = slide.querySelector('.spot-bug-wrapper')?.getAttribute('data-target-line') || correct;
            const lines = slide.querySelectorAll('.spot-bug-line');
            lines.forEach(l => {
                l.classList.add('disabled');
                if (l.getAttribute('data-line') === targetLine.toString()) {
                    l.classList.add('target-bug-line');
                }
            });
            const selectedLineEl = slide.querySelector(`.spot-bug-line[data-line="${selectedValue}"]`);
            if (selectedLineEl) {
                if (isCorrect) selectedLineEl.classList.add('bug-correct');
                else selectedLineEl.classList.add('bug-wrong');
            }
        } else if (tipo === 'ordenar_codigo' || tipo === 'parsons') {
            const dropzone = slide.querySelector('.parsons-dropzone');
            if (dropzone) {
                if (isCorrect) dropzone.classList.add('parsons-correct');
                else dropzone.classList.add('parsons-wrong');
            }
            const ctrlBtns = slide.querySelectorAll('.parsons-ctrl-btn');
            ctrlBtns.forEach(b => b.disabled = true);
        } else if (tipo === 'pareamento') {
            const wrapper = slide.querySelector('.pair-match-wrapper');
            if (wrapper) wrapper.classList.add('pair-complete');
        }

        if (isCorrect) {
            totalAcertos++;
            playAudio('playSuccess');

            if (opiQuizImg && MASCOTE_FELIZ) {
                opiQuizImg.src = MASCOTE_FELIZ;
                opiQuizImg.className = "opi-quiz-mascote opi-celebrate-animation";
            }

            setMascotSpeech("Excelente! Resposta correta! 🚀", "bubble-correct");

            if (footer) footer.className = 'quiz-footer feedback-correct';
            if (feedbackMsg) {
                feedbackMsg.className = 'feedback-message msg-correct visible';
                feedbackMsg.innerHTML = '<div class="feedback-icon"><i class="fa-solid fa-check"></i></div><div><strong>Excelente!</strong><br><span style="font-weight:400;font-size:13px;opacity:0.85">Resposta correta!</span></div>';
            }

            if (btn) {
                btn.className = 'btn-quiz-action btn-continue-correct';
                btn.textContent = 'CONTINUAR';
                btn.disabled = false;
            }

            launchConfetti();
        } else {
            playAudio('playError');

            if (opiQuizImg && MASCOTE_TRISTE) {
                opiQuizImg.src = MASCOTE_TRISTE;
                opiQuizImg.className = "opi-quiz-mascote opi-shake-animation";
            }

            setMascotSpeech("Quase lá! Veja a resposta correta para aprender! 💡", "bubble-wrong");

            if (footer) footer.className = 'quiz-footer feedback-wrong';
            
            let correctText = correct;
            if (tipo === 'spot_bug') {
                const targetLine = slide.querySelector('.spot-bug-wrapper')?.getAttribute('data-target-line') || correct;
                correctText = "Linha " + targetLine;
            } else if (tipo === 'ordenar_codigo' || tipo === 'parsons') {
                correctText = "Ordem correta de cima para baixo";
            } else if (tipo === 'pareamento') {
                correctText = "Todos os pares correspondentes";
            } else if (tipo === 'multipla_escolha' || tipo === 'predicao_saida') {
                const options = slide.querySelectorAll('.duo-option');
                options.forEach(opt => {
                    if (opt.getAttribute('data-value') === correct) {
                        const t = opt.querySelector('.option-text');
                        if (t) correctText = t.textContent;
                    }
                });
            } else if (tipo === 'completar_codigo') {
                const chips = slide.querySelectorAll('.word-chip');
                chips.forEach(chip => {
                    if (chip.getAttribute('data-value') === correct) {
                        correctText = chip.getAttribute('data-word') || correct;
                    }
                });
            }

            if (feedbackMsg) {
                feedbackMsg.className = 'feedback-message msg-wrong visible';
                feedbackMsg.innerHTML = '<div class="feedback-icon"><i class="fa-solid fa-xmark"></i></div><div><strong>Resposta incorreta</strong><br><span style="font-weight:400;font-size:13px;opacity:0.85">Correta: ' + correctText + '</span></div>';
            }

            if (btn) {
                btn.className = 'btn-quiz-action btn-continue-wrong';
                btn.textContent = 'CONTINUAR';
                btn.disabled = false;
            }
        }

        state = 'checked';
    }

    // Continuar para próxima pergunta ou submeter resultado
    function continuar() {
        const footer = document.getElementById('quizFooter');
        const feedbackMsg = document.getElementById('feedbackMsg');
        const btn = document.getElementById('btnAction');

        updateProgress(currentIndex + 1);

        // SE ERA A ÚLTIMA PERGUNTA -> FINALIZA A LIÇÃO
        if (currentIndex >= totalQuestions - 1) {
            const gabaritou = (totalAcertos === totalQuestions);
            const passou = (totalAcertos > 0);

            if (footer) footer.className = 'quiz-footer';
            if (feedbackMsg) {
                feedbackMsg.className = 'feedback-message';
                feedbackMsg.innerHTML = '';
            }

            const submeterResultado = () => {
                const elAcertos = document.getElementById('input_acertos');
                if (elAcertos) elAcertos.value = totalAcertos;
                const elTotal = document.getElementById('input_total_questoes');
                if (elTotal) elTotal.value = totalQuestions;
                const form = document.getElementById('quiz-form-hidden');
                if (form) form.submit();
            };

            if (gabaritou) {
                playAudio('playLessonComplete');
                if (opiQuizImg && MASCOTE_FELIZ) {
                    opiQuizImg.src = MASCOTE_FELIZ;
                    opiQuizImg.className = "opi-quiz-mascote opi-victory-animation";
                }
                setMascotSpeech("Mandou muito bem! Você gabaritou a lição! 🏆", "bubble-correct");
                launchConfetti();
                setTimeout(submeterResultado, 1200);
            } else if (passou) {
                playAudio('playLessonComplete');
                if (opiQuizImg && MASCOTE_FELIZ) {
                    opiQuizImg.src = MASCOTE_FELIZ;
                    opiQuizImg.className = "opi-quiz-mascote opi-bounce-animation";
                }
                setMascotSpeech("Muito bem! Lição concluída com sucesso! 📊", "bubble-correct");
                setTimeout(submeterResultado, 1000);
            } else {
                playAudio('playLessonDefeat');
                if (opiQuizImg && MASCOTE_TRISTE) {
                    opiQuizImg.src = MASCOTE_TRISTE;
                    opiQuizImg.className = "opi-quiz-mascote opi-shake-animation";
                }
                setMascotSpeech("Não foi dessa vez... Mas errar faz parte! 💪", "bubble-wrong");
                setTimeout(submeterResultado, 1000);
            }
            return;
        }

        // SE HOUVER PRÓXIMA PERGUNTA -> AVANÇA O SLIDE
        playAudio('playClick');

        if (opiQuizImg && MASCOTE_EXPLICANDO) {
            opiQuizImg.src = MASCOTE_EXPLICANDO;
            opiQuizImg.className = "opi-quiz-mascote opi-float-animation";
        }
        setMascotSpeech("Vamos para o próximo desafio! Mantenha o foco! ☕");

        if (footer) footer.className = 'quiz-footer';
        if (feedbackMsg) {
            feedbackMsg.className = 'feedback-message';
            feedbackMsg.innerHTML = '';
        }

        // Esconde a pergunta atual
        const currentSlideEl = slidesQuiz[currentIndex];
        if (currentSlideEl) {
            currentSlideEl.style.display = 'none';
        }

        // Avança o índice
        currentIndex++;
        selectedValue = null;
        firstPairCard = null;
        state = 'selecting';

        // Mostra a próxima pergunta
        if (slidesQuiz[currentIndex]) {
            slidesQuiz[currentIndex].style.display = 'flex';
        }

        // Reseta o botão para "VERIFICAR"
        if (btn) {
            btn.className = 'btn-quiz-action btn-check';
            btn.textContent = 'VERIFICAR';
            btn.disabled = true;
        }
    }

    // Confetes
    function launchConfetti() {
        const container = document.getElementById('confettiContainer');
        if (!container) return;
        const colors = ['#58cc02', '#4baf00', '#ffd900', '#ff9600', '#4b8df8', '#ce82ff'];

        for (let i = 0; i < 60; i++) {
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

            setTimeout(() => piece.remove(), 3500);
        }
    }

    // Atalhos de teclado (Enter para Verificar/Continuar, 1-3 para alternativas)
    document.addEventListener('keydown', function(e) {
        if (e.target && (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA')) {
            return;
        }

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
            if (btn && !btn.disabled) {
                window.handleAction();
            }
        }

        if (state === 'selecting' && ['1', '2', '3'].includes(e.key)) {
            const slide = slidesQuiz[currentIndex];
            if (slide) {
                const options = slide.querySelectorAll('.duo-option');
                const idx = parseInt(e.key) - 1;
                if (options[idx]) {
                    window.selectOption(options[idx]);
                }
            }
        }
    });

    // Inicia progresso
    updateProgress(0);

    // Aviso de saída
    window.abrirAvisoSaida = function() {
        playAudio('playClick');
        const modal = document.getElementById('exitWarningModal');
        if (modal) modal.classList.add('active');
    };

    window.fecharAvisoSaida = function() {
        playAudio('playClick');
        const modal = document.getElementById('exitWarningModal');
        if (modal) modal.classList.remove('active');
    };

    window.confirmarSaida = async function() {
        const btnConfirm = document.querySelector('.btn-exit-confirm');
        if (btnConfirm) {
            btnConfirm.disabled = true;
            btnConfirm.textContent = 'Saindo...';
        }
        playAudio('playClick');

        try {
            await fetch('../../back/api/penalizar_saida.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                keepalive: true
            });
        } catch (e) {}

        window.location.href = 'dashboard.php';
    };

})();
