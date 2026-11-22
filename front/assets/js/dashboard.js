// dashboard.js - logica da pagina Dashboard
        let capAtualBau = 0;
        let tituloAtualBau = '';
        let chestConfettiAnimId = null;

        // Disparo de Confetes Festivos no Modal
        function dispararConfetesBau() {
            const canvas = document.getElementById('chestConfettiCanvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            const box = canvas.parentElement;
            canvas.width = box.clientWidth || 440;
            canvas.height = box.clientHeight || 520;

            const colors = ['#ffd700', '#ff9600', '#58cc02', '#1cb0f6', '#ff4b4b', '#ffffff', '#e066ff'];
            const particles = [];
            const count = 70;

            for (let i = 0; i < count; i++) {
                const angle = -Math.PI / 2 + (Math.random() - 0.5) * 1.5;
                const speed = Math.random() * 9 + 4;
                particles.push({
                    x: canvas.width / 2 + (Math.random() - 0.5) * 40,
                    y: 110,
                    vx: Math.cos(angle) * speed,
                    vy: Math.sin(angle) * speed,
                    gravity: 0.22,
                    size: Math.random() * 7 + 4,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    rotation: Math.random() * Math.PI * 2,
                    rotSpeed: (Math.random() - 0.5) * 0.25,
                    opacity: 1,
                    shape: Math.random() > 0.3 ? 'rect' : 'circle'
                });
            }

            if (chestConfettiAnimId) cancelAnimationFrame(chestConfettiAnimId);

            function step() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                let active = 0;
                particles.forEach(p => {
                    p.x += p.vx;
                    p.y += p.vy;
                    p.vy += p.gravity;
                    p.vx *= 0.985;
                    p.rotation += p.rotSpeed;
                    if (p.y > canvas.height * 0.5) {
                        p.opacity -= 0.018;
                    }
                    if (p.opacity > 0 && p.y < canvas.height + 20) {
                        active++;
                        ctx.save();
                        ctx.globalAlpha = Math.max(0, p.opacity);
                        ctx.translate(p.x, p.y);
                        ctx.rotate(p.rotation);
                        ctx.fillStyle = p.color;
                        if (p.shape === 'circle') {
                            ctx.beginPath();
                            ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
                            ctx.fill();
                        } else {
                            ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.7);
                        }
                        ctx.restore();
                    }
                });
                if (active > 0) {
                    chestConfettiAnimId = requestAnimationFrame(step);
                } else {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
            }
            chestConfettiAnimId = requestAnimationFrame(step);
        }

        // Animação de Contador Numérico Suave
        function animarContadorXP(el, valorFinal) {
            if (!el) return;
            const duracao = 900;
            const inicio = performance.now();
            el.innerText = '+0 XP';
            function frame(agora) {
                const progresso = Math.min((agora - inicio) / duracao, 1);
                const facil = 1 - Math.pow(1 - progresso, 3);
                const atual = Math.round(facil * valorFinal);
                el.innerText = '+' + atual + ' XP';
                if (progresso < 1) {
                    requestAnimationFrame(frame);
                } else {
                    el.innerText = '+' + valorFinal + ' XP';
                }
            }
            requestAnimationFrame(frame);
        }

        // ==========================================================
        // SISTEMA DE ROLETA LATERALIZADA DO BAÚ (HORIZONTAL REEL)
        // ==========================================================
        let roletaGirarAtiva = false;
        let roletaAnimTickId = null;

        // Catálogo dos 6 Prêmios da Roleta Lateral
        const OPUS_ROLETA_PREMIOS = [
            { id: 'xp25',    slice_index: 0, tipo: 'xp',   valor: 25, label: '+25 XP',     tag: 'MÁXIMO', sub: 'Prêmio Máximo', icon: 'fa-solid fa-crown', theme: 'gold' },
            { id: 'xp5',     slice_index: 1, tipo: 'xp',   valor: 5,  label: '+5 XP',      tag: 'BÔNUS',  sub: 'XP Bônus',      icon: 'fa-solid fa-bolt',  theme: 'blue' },
            { id: 'coracao', slice_index: 2, tipo: 'vida', valor: 1,  label: '+1 Coração', tag: 'VIDA',   sub: 'Recupera Vida', icon: 'fa-solid fa-heart', theme: 'red' },
            { id: 'xp10',    slice_index: 3, tipo: 'xp',   valor: 10, label: '+10 XP',     tag: 'BÔNUS',  sub: 'Belo Prêmio',   icon: 'fa-solid fa-bolt',  theme: 'orange' },
            { id: 'xp3',     slice_index: 4, tipo: 'xp',   valor: 3,  label: '+3 XP',      tag: 'BÔNUS',  sub: 'XP Bônus',      icon: 'fa-solid fa-bolt',  theme: 'green' },
            { id: 'xp20',    slice_index: 5, tipo: 'xp',   valor: 20, label: '+20 XP',     tag: 'SUPER',  sub: 'Super Bônus',   icon: 'fa-solid fa-bolt',  theme: 'purple' }
        ];

        function criarCardRoletaHtml(p, index) {
            return `
                <div class="roleta-item-card card-theme-${p.theme}" data-index="${index}" data-id="${p.id}">
                    <span class="ric-tag"><i class="${p.icon}"></i> ${p.tag}</span>
                    <div class="ric-icon-box">
                        <i class="${p.icon}"></i>
                    </div>
                    <div class="ric-val">${p.label}</div>
                    <div class="ric-sub">${p.sub}</div>
                </div>
            `;
        }

        function popularTrilhoRoleta(winningItem = null, targetIndex = 48) {
            const track = document.getElementById('roletaCardsTrack');
            if (!track) return;

            const totalCards = 65;
            let html = '';
            for (let i = 0; i < totalCards; i++) {
                let p;
                if (i === targetIndex && winningItem) {
                    p = winningItem;
                } else {
                    const baseIdx = (i * 7 + 3) % OPUS_ROLETA_PREMIOS.length;
                    p = OPUS_ROLETA_PREMIOS[baseIdx];
                }
                html += criarCardRoletaHtml(p, i);
            }
            track.innerHTML = html;
        }

        function abrirModalBau(cap, titulo) {
            if (window.OpusAudio) window.OpusAudio.playClick();
            capAtualBau = cap;
            tituloAtualBau = titulo || ('Unidade ' + cap);

            const modalTitle = document.getElementById('modalBauTitle');
            const modalSub = document.getElementById('modalBauSub');
            if (modalTitle) modalTitle.innerText = 'Roleta de Recompensas!';
            if (modalSub) modalSub.innerHTML = '<i class="fa-solid fa-crown"></i> Unidade ' + cap + (titulo ? (' · ' + titulo) : '') + ' · Baú da Trilha';

            // Reseta visual e posição do trilho lateral
            roletaGirarAtiva = false;
            if (roletaAnimTickId) {
                cancelAnimationFrame(roletaAnimTickId);
                roletaAnimTickId = null;
            }

            const track = document.getElementById('roletaCardsTrack');
            if (track) {
                track.style.transition = 'none';
                track.style.transform = 'translateX(0px)';
            }

            // Gera a fita horizontal inicial de cartões legíveis
            popularTrilhoRoleta(null);

            const needle = document.getElementById('roletaCenterNeedle');
            if (needle) needle.classList.remove('tick');

            const canvas = document.getElementById('chestConfettiCanvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }

            const containerPos = document.getElementById('rewardContainerPos');
            if (containerPos) containerPos.style.display = 'none';

            const btnResgatar = document.getElementById('btnResgatarBau');
            if (btnResgatar) {
                btnResgatar.style.display = 'flex';
                btnResgatar.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> GIRAR ROLETA!';
                btnResgatar.disabled = false;
            }

            const btnFechar = document.getElementById('btnFecharBauPronto');
            if (btnFechar) btnFechar.style.display = 'none';

            const closeBtn = document.getElementById('btnCloseModalBau');
            if (closeBtn) closeBtn.style.pointerEvents = 'auto';

            const modal = document.getElementById('modalBauRecompensa');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function fecharModalBau() {
            if (roletaGirarAtiva) return; // Não fecha se a roleta estiver girando
            if (window.OpusAudio) window.OpusAudio.playClick();
            if (chestConfettiAnimId) {
                cancelAnimationFrame(chestConfettiAnimId);
                chestConfettiAnimId = null;
            }
            if (roletaAnimTickId) {
                cancelAnimationFrame(roletaAnimTickId);
                roletaAnimTickId = null;
            }
            const modal = document.getElementById('modalBauRecompensa');
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        }

        function fecharAvisoBau() {
            if (window.OpusAudio) window.OpusAudio.playClick();
            const modal = document.getElementById('modalAvisoBau');
            if (!modal) return;
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 250);
        }

        function avisoBauColetado(cap, titulo) {
            const modal = document.getElementById('modalAvisoBau');
            if (!modal) return;
            const iconBox = document.getElementById('avisoBauIconBox');
            const tplClaimed = document.getElementById('tplBauClaimedModalSvg');
            if (iconBox) {
                iconBox.className = 'modal-aviso-icon-wrap modal-aviso-bau-svg state-claimed';
                iconBox.removeAttribute('style');
                if (tplClaimed) {
                    iconBox.innerHTML = tplClaimed.innerHTML;
                } else {
                    iconBox.innerHTML = '<i class="fa-solid fa-box-open" id="avisoBauIcone"></i>';
                }
            }

            document.getElementById('avisoBauTitulo').innerText = 'Baú Já Coletado!';
            document.getElementById('avisoBauSub').innerHTML = '<i class="fa-solid fa-circle-check"></i> Unidade ' + cap + (titulo ? (' · ' + titulo) : '');
            
            const progressBox = document.getElementById('avisoBauProgressoBox');
            if (progressBox) progressBox.style.display = 'none';

            document.getElementById('avisoBauTexto').innerHTML = 'Você já girou a roleta e resgatou as recompensas deste baú da <strong>Unidade ' + cap + '</strong>! Continue sua trilha para encontrar novos baús nas próximas unidades.';
            
            const btn = document.getElementById('btnAvisoAcao');
            if (btn) {
                btn.className = 'btn-claim-chest';
                btn.style.background = '#58cc02';
                btn.style.border = 'none';
                btn.style.borderBottom = '5px solid #46a302';
                btn.style.boxShadow = 'none';
                btn.innerHTML = '<i class="fa-solid fa-arrow-right"></i> CONTINUAR TRILHA';
                btn.onclick = fecharAvisoBau;
            }

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function avisoBauTrancado(cap, titulo, concluidas) {
            const modal = document.getElementById('modalAvisoBau');
            if (!modal) return;
            concluidas = Number(concluidas) || 0;
            if (concluidas > 3) concluidas = 3;

            const iconBox = document.getElementById('avisoBauIconBox');
            const tplLocked = document.getElementById('tplBauLockedModalSvg');
            if (iconBox) {
                iconBox.className = 'modal-aviso-icon-wrap modal-aviso-bau-svg state-locked';
                iconBox.removeAttribute('style');
                if (tplLocked) {
                    iconBox.innerHTML = tplLocked.innerHTML;
                } else {
                    iconBox.innerHTML = '<i class="fa-solid fa-lock" id="avisoBauIcone"></i>';
                }
            }

            document.getElementById('avisoBauTitulo').innerText = 'Baú Trancado';
            document.getElementById('avisoBauSub').innerHTML = '<i class="fa-solid fa-lock"></i> Unidade ' + cap + (titulo ? (' · ' + titulo) : '');
            
            // Configura Trilha Visual de Passos (1 -> 2 -> Baú)
            const progressBox = document.getElementById('avisoBauProgressoBox');
            if (progressBox) progressBox.style.display = 'block';

            const step1 = document.getElementById('avisoStep1');
            const step2 = document.getElementById('avisoStep2');
            const step3 = document.getElementById('avisoStep3');
            const stepFill = document.getElementById('avisoStepFill');

            if (step1) {
                step1.className = 'step-point' + (concluidas >= 1 ? ' completed' : (concluidas === 0 ? ' target' : ''));
                step1.querySelector('.step-dot').innerHTML = concluidas >= 1 ? '<i class="fa-solid fa-check"></i>' : '1';
            }
            if (step2) {
                step2.className = 'step-point' + (concluidas >= 2 ? ' completed' : (concluidas === 1 ? ' target' : ''));
                step2.querySelector('.step-dot').innerHTML = concluidas >= 2 ? '<i class="fa-solid fa-check"></i>' : '2';
            }
            if (step3) {
                step3.className = 'step-point' + (concluidas >= 3 ? ' completed' : ' target');
            }

            const percent = Math.round((concluidas / 3) * 100);
            if (stepFill) {
                const fillWidth = concluidas === 0 ? 0 : (concluidas === 1 ? 50 : (concluidas === 2 ? 80 : 100));
                stepFill.style.width = fillWidth + '%';
            }

            const txtMeta = document.getElementById('avisoProgressoTexto');
            if (txtMeta) txtMeta.innerText = concluidas + ' de 3 lições concluídas';
            const badgeMeta = document.getElementById('avisoProgressoPorcentagem');
            if (badgeMeta) badgeMeta.innerText = percent + '%';

            // Mensagem motivacional de acordo com o progresso
            let desc = '';
            if (concluidas === 0) {
                desc = 'Conclua as 3 primeiras lições da <strong>Unidade ' + cap + '</strong> para desbloquear este baú mágico de recompensas com prêmios de <strong>até +25 XP</strong> ou <strong>+1 Coração de Vida</strong> na Roleta da Sorte!';
            } else if (concluidas === 1) {
                desc = 'Faltam apenas <strong>2 lições</strong> nesta unidade para abrir o baú e garantir seu bônus de XP e corações!';
            } else {
                desc = 'Quase lá! Falta só <strong>1 lição</strong> para desbloquear o baú de ouro desta unidade!';
            }
            document.getElementById('avisoBauTexto').innerHTML = desc;

            const btn = document.getElementById('btnAvisoAcao');
            if (btn) {
                btn.className = 'btn-claim-chest btn-aviso-primario';
                btn.style.background = '#1cb0f6';
                btn.style.border = 'none';
                btn.style.borderBottom = '5px solid #148bc4';
                btn.style.boxShadow = 'none';
                btn.innerHTML = '<i class="fa-solid fa-play"></i> CONTINUAR LIÇÃO';
                btn.onclick = function() {
                    fecharAvisoBau();
                    setTimeout(rolarAteLicaoAtual, 120);
                };
            }

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function girarRoletaBau() {
            if (roletaGirarAtiva) return;
            roletaGirarAtiva = true;

            const btn = document.getElementById('btnResgatarBau');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-arrows-rotate fa-spin"></i> GIRANDO...';
            }

            const closeBtn = document.getElementById('btnCloseModalBau');
            if (closeBtn) closeBtn.style.pointerEvents = 'none';

            // Sons de início do giro
            if (window.OpusAudio) {
                if (typeof window.OpusAudio.playClick === 'function') window.OpusAudio.playClick();
                if (typeof window.OpusAudio.playRouletteSpin === 'function') window.OpusAudio.playRouletteSpin();
            }

            const track = document.getElementById('roletaCardsTrack');
            const viewport = document.getElementById('roletaReelViewport');
            const needle = document.getElementById('roletaCenterNeedle');

            fetch('../../back/api/resgatar_bau.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'unidade_numero=' + encodeURIComponent(capAtualBau)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    roletaGirarAtiva = false;
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> TENTAR NOVAMENTE';
                    }
                    if (closeBtn) closeBtn.style.pointerEvents = 'auto';

                    if (typeof opusToast === 'function') {
                        opusToast(data.mensagem || 'Não foi possível resgatar o baú.', 'error');
                    } else {
                        alert(data.mensagem || 'Não foi possível resgatar o baú.');
                    }
                    return;
                }

                // Identifica o prêmio sorteado
                const premioSorteado = data.premio || {};
                let winningItem = OPUS_ROLETA_PREMIOS.find(p => p.id === premioSorteado.id);
                if (!winningItem) {
                    winningItem = OPUS_ROLETA_PREMIOS.find(p => p.slice_index === premioSorteado.slice_index) || OPUS_ROLETA_PREMIOS[3];
                }

                // Coloca o cartão sorteado em um índice avançado do trilho (ex: 48 de 65)
                const targetIndex = 48;
                popularTrilhoRoleta(winningItem, targetIndex);

                // Força reflow para garantir que as posições sejam computadas
                void track.offsetWidth;

                // Medidas dinâmicas adaptáveis a mobile e desktop
                const sampleCard = track.querySelector('.roleta-item-card');
                const cardWidth = sampleCard ? sampleCard.offsetWidth : 125;
                const cardGap = 12;
                const step = cardWidth + cardGap;
                const trackPaddingLeft = 12;
                const viewportWidth = viewport ? viewport.clientWidth : 540;

                // Centro do card targetIndex
                const cardCenter = trackPaddingLeft + (targetIndex * step) + (cardWidth / 2);
                const centerOffset = viewportWidth / 2;

                // Pequeno jitter natural (±12px) dentro do card
                const jitter = (Math.random() - 0.5) * Math.min(24, cardWidth * 0.2);
                const distanceToScroll = cardCenter - centerOffset + jitter;

                // Animação horizontal desacelerando de 5 segundos
                const spinDuration = 5000;
                track.style.transition = `transform ${spinDuration}ms cubic-bezier(0.12, 0.88, 0.2, 1)`;
                track.style.transform = `translateX(-${distanceToScroll}px)`;

                // Efeito sonoro tátil de cliques mecânicos a cada cartão que cruza o centro
                const startTime = performance.now();
                let lastCardPassed = 0;

                function updateTicks(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(1, elapsed / spinDuration);
                    const ease = 1 - Math.pow(1 - progress, 4.2);
                    const currentDist = distanceToScroll * ease;

                    const currentCard = Math.floor(currentDist / step);
                    if (currentCard > lastCardPassed) {
                        lastCardPassed = currentCard;
                        if (needle) {
                            needle.classList.remove('tick');
                            void needle.offsetWidth;
                            needle.classList.add('tick');
                        }
                        if (window.OpusAudio && typeof window.OpusAudio.playRouletteTick === 'function') {
                            const isSpecial = (currentCard % 6 === 0);
                            window.OpusAudio.playRouletteTick(isSpecial);
                        }
                    }

                    if (progress < 1) {
                        roletaAnimTickId = requestAnimationFrame(updateTicks);
                    }
                }
                roletaAnimTickId = requestAnimationFrame(updateTicks);

                // Ao terminar a rolagem horizontal
                setTimeout(() => {
                    roletaGirarAtiva = false;
                    if (roletaAnimTickId) cancelAnimationFrame(roletaAnimTickId);

                    // Destaca o card vencedor
                    const winnerCard = track.querySelector(`.roleta-item-card[data-index="${targetIndex}"]`);
                    if (winnerCard) {
                        winnerCard.classList.add('winner-highlight');
                    }

                    // Som triunfal e confetes
                    if (window.OpusAudio && typeof window.OpusAudio.playChest === 'function') {
                        window.OpusAudio.playChest();
                    }
                    dispararConfetesBau();

                    // Preenche e exibe o Card de Celebração
                    const containerPos = document.getElementById('rewardContainerPos');
                    if (containerPos) containerPos.style.display = 'block';

                    const winCard = document.getElementById('roletaWinCard');
                    const winVal = document.getElementById('winVal');
                    const winSub = document.getElementById('winSub');
                    const winIcon = document.getElementById('winIcon');
                    const winIconWrap = document.getElementById('winIconWrap');
                    const winDetalheTxt = document.getElementById('winDetalheTxt');

                    if (winVal) winVal.innerText = winningItem.label;
                    if (winSub) winSub.innerText = winningItem.sub;
                    if (winDetalheTxt) winDetalheTxt.innerText = data.detalhe || data.mensagem;

                    if (winIconWrap && winIcon) {
                        winIconWrap.className = 'win-icon-wrap';
                        if (winningItem.tipo === 'vida' || data.vidas_ganhas > 0) {
                            winIconWrap.classList.add('win-vida');
                            winIcon.className = 'fa-solid fa-heart';
                        } else if (winningItem.valor === 25 || data.xp_ganho >= 25) {
                            winIconWrap.classList.add('win-xp-max');
                            winIcon.className = 'fa-solid fa-crown';
                        } else {
                            winIconWrap.classList.add('win-xp');
                            winIcon.className = 'fa-solid fa-bolt';
                        }
                    }

                    if (winCard) {
                        winCard.classList.remove('reveal-bounce');
                        void winCard.offsetWidth;
                        winCard.classList.add('reveal-bounce');
                    }

                    // Alterna botões
                    if (btn) btn.style.display = 'none';
                    const btnFechar = document.getElementById('btnFecharBauPronto');
                    if (btnFechar) btnFechar.style.display = 'flex';
                    if (closeBtn) closeBtn.style.pointerEvents = 'auto';

                    // Atualiza o nó do baú na trilha para status "Coletado" com o SVG do baú pirata aberto
                    const chestNode = document.getElementById('chestNode_' + capAtualBau);
                    if (chestNode) {
                        const tplClaimedTrail = document.getElementById('tplBauClaimedSvg');
                        const claimedSvgHtml = tplClaimedTrail ? tplClaimedTrail.innerHTML : '';
                        chestNode.className = 'chest-node claimed';
                        chestNode.setAttribute('aria-label', 'Baú da Unidade ' + capAtualBau + ' já coletado');
                        chestNode.onclick = function() { avisoBauColetado(capAtualBau, tituloAtualBau); };
                        chestNode.innerHTML = claimedSvgHtml + `
                            <span class="chest-tag-done"><i class="fa-solid fa-check"></i> Coletado</span>
                        `;
                        if (chestNode.parentElement) {
                            chestNode.parentElement.classList.remove('available');
                            chestNode.parentElement.classList.add('claimed');
                            chestNode.parentElement.setAttribute('data-chest-state', 'claimed');
                            const balloon = chestNode.parentElement.querySelector('.chest-balloon');
                            if (balloon) balloon.remove();
                        }
                        const aura = chestNode.querySelector('.chest-aura-glow');
                        if (aura) aura.remove();
                        setTimeout(renderizarTrilhasConexao, 60);
                    }

                    // Atualiza Topbar Vidas e XP em tempo real
                    const topbarXp = document.querySelector('.topbar-stat.stat-xp span');
                    if (topbarXp && data.xp_total) {
                        topbarXp.innerText = Number(data.xp_total).toLocaleString('pt-BR');
                    }
                    const topbarVida = document.querySelector('.topbar-stat.stat-vida span');
                    if (topbarVida && typeof data.vidas_atual !== 'undefined') {
                        topbarVida.innerText = data.vidas_atual;
                    }
                }, spinDuration + 120);
            })
            .catch(err => {
                console.error(err);
                roletaGirarAtiva = false;
                if (roletaAnimTickId) cancelAnimationFrame(roletaAnimTickId);
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> GIRAR ROLETA!';
                }
                if (closeBtn) closeBtn.style.pointerEvents = 'auto';

                if (typeof opusToast === 'function') {
                    opusToast('Erro de conexão ao girar a roleta do baú.', 'error');
                } else {
                    alert('Erro de conexão ao girar a roleta do baú.');
                }
            });
        }

        // Mantém compatibilidade caso outro script chame resgatarRecompensaBau
        function resgatarRecompensaBau() {
            girarRoletaBau();
        }

        function abrirGuia(btn) {
            if (window.OpusAudio) window.OpusAudio.playClick();
            const titulo = btn.getAttribute('data-titulo');
            const nome = btn.getAttribute('data-nome');
            const cor = btn.getAttribute('data-cor');
            const texto = btn.getAttribute('data-texto');
            const codigo = btn.getAttribute('data-codigo');

            document.getElementById('modalUnidadeTitle').innerText = titulo;
            document.getElementById('modalUnidadeSub').innerText = nome;
            document.getElementById('modalHeaderBg').style.backgroundColor = cor;
            document.getElementById('modalCodeBox').style.borderLeftColor = cor;
            
            document.getElementById('modalGuiaTexto').innerText = texto;
            document.getElementById('modalCodeBox').innerHTML = codigo;
            
            const modal = document.getElementById('modalGuia');
            modal.style.display = 'flex';
            
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        function fecharGuia() {
            if (window.OpusAudio) window.OpusAudio.playClick();
            const modal = document.getElementById('modalGuia');
            modal.classList.remove('active');
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        window.onclick = function(event) {
            const modalG = document.getElementById('modalGuia');
            if (event.target == modalG) {
                fecharGuia();
            }
            const modalB = document.getElementById('modalBauRecompensa');
            if (event.target == modalB) {
                fecharModalBau();
            }
        };

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const overlay = document.getElementById('islandVoyageOverlay');
                if (overlay && overlay.classList.contains('active')) {
                    tratarAcaoTopoMapa();
                }
            }
        });

        // ══════════════════════════════════════════════════════════════════════
        // NAVEGAÇÃO POR CAPÍTULO (3 ILHAS) + ANIMAÇÃO EM TELA INTEIRA DO BARCO
        // ══════════════════════════════════════════════════════════════════════
        let divisaoAtiva = (typeof window.OPUS_DIVISAO_INICIAL !== 'undefined') ? Number(window.OPUS_DIVISAO_INICIAL) : 1;
        const totalCapitulosCurso = Object.keys(window.OPUS_DIVISOES_DADOS || { 1: 1, 2: 2, 3: 3 }).length || 3;

        let viagemEmAndamento = false;
        let mapaModoAtual = 'idle'; // 'idle' | 'explorer' | 'voyage'
        let viagemRafId = null;
        let viagemTimers = [];
        let viagemDestinoPendente = null;
        let viagemCallbackPosChegada = null;

        // Coordenadas verificadas sobre o mar da imagem mapa_ilhas.jpg (viewBox 1000x1000)
        const OPUS_ILHAS_MAPA = {
            1: { dock: { x: 390, y: 390 }, cam: { x: 300, y: 300 }, nomeCurto: 'Ilha dos Fundamentos', cor: '#1cb0f6' },
            2: { dock: { x: 780, y: 520 }, cam: { x: 700, y: 360 }, nomeCurto: 'Ilha do Código',      cor: '#ce82ff' },
            3: { dock: { x: 520, y: 750 }, cam: { x: 360, y: 690 }, nomeCurto: 'Ilha dos Desafios',    cor: '#ffc800' }
        };

        // Rotas náuticas em curva Bezier cúbica (100% sobre o oceano) com pontos para cálculo puro em JS (zero getPointAtLength por frame)
        const OPUS_ROTAS_NAUTICAS = {
            '1-2': { d: 'M 390 390 C 500 440, 620 600, 780 520', pts: [[390, 390], [500, 440], [620, 600], [780, 520]] },
            '2-1': { d: 'M 780 520 C 620 600, 500 440, 390 390', pts: [[780, 520], [620, 600], [500, 440], [390, 390]] },
            '2-3': { d: 'M 780 520 C 740 650, 650 750, 520 750', pts: [[780, 520], [740, 650], [650, 750], [520, 750]] },
            '3-2': { d: 'M 520 750 C 650 750, 740 650, 780 520', pts: [[520, 750], [650, 750], [740, 650], [780, 520]] },
            '1-3': { d: 'M 390 390 C 510 470, 540 620, 520 750', pts: [[390, 390], [510, 470], [540, 620], [520, 750]] },
            '3-1': { d: 'M 520 750 C 540 620, 510 470, 390 390', pts: [[520, 750], [540, 620], [510, 470], [390, 390]] }
        };

        // Pré-calcula tabela de comprimento de arco da curva Bezier (velocidade 100% constante e zero custo de DOM no rAF)
        function criarTabelaCurvaBezier(pts, passos = 160) {
            const [p0, p1, p2, p3] = pts;
            const tabela = new Array(passos + 1);
            let distAcumulada = 0;
            let prevX = p0[0];
            let prevY = p0[1];

            for (let i = 0; i <= passos; i++) {
                const u = i / passos;
                const mu = 1 - u;
                const mu2 = mu * mu;
                const u2 = u * u;

                const x = (mu2 * mu * p0[0]) + (3 * mu2 * u * p1[0]) + (3 * mu * u2 * p2[0]) + (u2 * u * p3[0]);
                const y = (mu2 * mu * p0[1]) + (3 * mu2 * u * p1[1]) + (3 * mu * u2 * p2[1]) + (u2 * u * p3[1]);
                const dx = 3 * mu2 * (p1[0] - p0[0]) + 6 * mu * u * (p2[0] - p1[0]) + 3 * u2 * (p3[0] - p2[0]);

                if (i > 0) {
                    distAcumulada += Math.hypot(x - prevX, y - prevY);
                }
                tabela[i] = { x, y, dx, dist: distAcumulada };
                prevX = x;
                prevY = y;
            }
            return { tabela, totalDist: distAcumulada || 1 };
        }

        function amostrarCurvaPorDistancia(curva, fracao) {
            const alvo = Math.max(0, Math.min(1, fracao)) * curva.totalDist;
            const tab = curva.tabela;
            let lo = 0;
            let hi = tab.length - 1;
            while (lo < hi - 1) {
                const mid = (lo + hi) >> 1;
                if (tab[mid].dist < alvo) lo = mid;
                else hi = mid;
            }
            const a = tab[lo];
            const b = tab[hi];
            const span = (b.dist - a.dist) || 1;
            const f = Math.max(0, Math.min(1, (alvo - a.dist) / span));
            return {
                x: a.x + (b.x - a.x) * f,
                y: a.y + (b.y - a.y) * f,
                dx: a.dx + (b.dx - a.dx) * f
            };
        }

        function obterDivisaoDaSecao(numSecao) {
            const dados = window.OPUS_DIVISOES_DADOS || {};
            const n = Number(numSecao) || 1;
            for (const key of Object.keys(dados)) {
                const d = dados[key];
                if (n >= Number(d.secao_inicio) && n <= Number(d.secao_fim)) {
                    return Number(d.id || key);
                }
            }
            return 1;
        }

        function obterInfoCapitulo(divId) {
            const dados = window.OPUS_DIVISOES_DADOS || {};
            const id = Math.max(1, Math.min(totalCapitulosCurso, Number(divId) || 1));
            return dados[id] || {
                id,
                nome: 'Capítulo ' + id,
                tema: 'Capítulo ' + id,
                secao_inicio: 1,
                secao_fim: 15,
                concluidas: 0,
                total_secoes: 15,
                porcentagem: 0
            };
        }

        function limparTimersViagem() {
            if (viagemRafId) {
                cancelAnimationFrame(viagemRafId);
                viagemRafId = null;
            }
            viagemTimers.forEach(t => clearTimeout(t));
            viagemTimers = [];
        }

        function atualizarBarraTopoCapitulo(divId) {
            const d = obterInfoCapitulo(divId);
            const worldPill = document.getElementById('witWorldPill');
            const titleEl = document.getElementById('witIslandTitle');
            const progFill = document.getElementById('witProgressFill');
            const progMeta = document.getElementById('witProgressMeta');

            if (worldPill) {
                const ilhaInfo = OPUS_ILHAS_MAPA[Number(d.id)] || OPUS_ILHAS_MAPA[1];
                const ilhaNome = ilhaInfo ? ilhaInfo.nomeCurto.toUpperCase() : 'ILHA';
                worldPill.innerHTML = `<i class="fa-solid fa-compass"></i> CAPÍTULO ${d.id} · ${ilhaNome} · SEÇÕES ${d.secao_inicio}–${d.secao_fim}`;
            }
            if (titleEl) {
                titleEl.innerText = d.tema || d.nome;
            }
            if (progFill) {
                const pct = Number(d.porcentagem || 0);
                progFill.style.width = Math.max(6, pct) + '%';
            }
            if (progMeta) {
                progMeta.innerText = `${d.concluidas || 0}/${d.total_secoes || 0} seções`;
            }

            const btnAnt = document.getElementById('btnCapituloAnterior');
            const btnProx = document.getElementById('btnCapituloProximo');
            if (btnAnt) btnAnt.disabled = (Number(d.id) <= 1);
            if (btnProx) btnProx.disabled = (Number(d.id) >= totalCapitulosCurso);

            // Destaca o capítulo ativo no widget lateral "Meu Progresso"
            document.querySelectorAll('.prog-div-item').forEach((item, idx) => {
                item.classList.toggle('atual', (idx + 1) === Number(d.id));
            });

            // Atualiza pinos do mapa das ilhas
            document.querySelectorAll('.ivo-island-pin').forEach(pin => {
                const pId = Number(pin.getAttribute('data-capitulo'));
                pin.classList.toggle('is-current', pId === Number(d.id));
            });
        }

        // Exibe TODAS as seções pertencentes ao capítulo escolhido (sem pular nenhuma seção!)
        function aplicarCapituloVisivel(divId, rolarParaTopo = false) {
            const targetId = Math.max(1, Math.min(totalCapitulosCurso, Number(divId) || 1));
            divisaoAtiva = targetId;
            if (document.body) {
                document.body.setAttribute('data-active-island', String(targetId));
            }

            try {
                localStorage.setItem('opus_divisao_ativa', String(targetId));
            } catch (e) {}

            // Exibe todas as seções do capítulo ativo e oculta as dos outros capítulos
            document.querySelectorAll('.capitulo-container').forEach(cap => {
                const capDiv = Number(cap.getAttribute('data-divisao') || 1);
                const visivel = (capDiv === targetId);
                cap.style.display = visivel ? 'flex' : 'none';
                cap.classList.toggle('island-active', visivel);
            });

            // Exibe apenas o cartão de marco correspondente ao final do capítulo ativo
            document.querySelectorAll('.division-milestone-card').forEach(card => {
                const cDiv = Number(card.getAttribute('data-divisao') || 1);
                card.style.display = (cDiv === targetId) ? 'flex' : 'none';
            });

            atualizarBarraTopoCapitulo(targetId);

            if (rolarParaTopo) {
                const container = document.querySelector('.main-content');
                if (container) container.scrollTop = 0;
                window.scrollTo({ top: 0, left: 0, behavior: 'auto' });
            }

            requestAnimationFrame(() => {
                renderizarTrilhasConexao();
                setTimeout(renderizarTrilhasConexao, 160);
                monitorarVisibilidadeLicaoAtual();
            });
        }

        let wakePoolIdx = 0;
        let boatFlippedState = null;

        function obterDimensoesPalcoMapa() {
            const stage = document.getElementById('ivoMapStage');
            if (!stage) return { w: 800, h: 800 };
            return {
                w: stage.offsetWidth || 800,
                h: stage.offsetHeight || 800
            };
        }

        function posicionarBarcoNoMapa(x, y, virarEsquerda = false, stageW = null, stageH = null) {
            const boat = document.getElementById('ivoVoyageBoat');
            if (!boat) return;
            const w = stageW || (obterDimensoesPalcoMapa().w);
            const h = stageH || (obterDimensoesPalcoMapa().h);
            const px = (x * 0.001) * w;
            const py = (y * 0.001) * h;
            boat.style.transform = `translate3d(${px.toFixed(2)}px, ${py.toFixed(2)}px, 0)`;

            if (boatFlippedState !== virarEsquerda) {
                boatFlippedState = virarEsquerda;
                const flip = document.getElementById('ivoBoatFlip');
                if (flip) {
                    flip.style.transform = virarEsquerda ? 'scaleX(-1)' : 'scaleX(1)';
                }
            }
        }

        function aplicarCameraMapa(focusX, focusY, scale, stageEl = null) {
            const stage = stageEl || document.getElementById('ivoMapStage');
            if (!stage) return;
            // Deslocamento suave mantendo as ilhas sempre enquadradas
            const maxShift = 6.5;
            const rawX = ((500 - focusX) / 500) * (scale - 1) * 54;
            const rawY = ((500 - focusY) / 500) * (scale - 1) * 54;
            const tx = Math.max(-maxShift, Math.min(maxShift, rawX));
            const ty = Math.max(-maxShift, Math.min(maxShift, rawY));
            stage.style.transform = `translate3d(${tx.toFixed(2)}%, ${ty.toFixed(2)}%, 0) scale(${scale.toFixed(3)})`;
        }

        function gerarOndaEspumaBarco(x, y, stageW, stageH, dots) {
            if (!dots || dots.length === 0) return;
            const dot = dots[wakePoolIdx % dots.length];
            wakePoolIdx++;
            const px = ((x * 0.001) * stageW).toFixed(1) + 'px';
            const py = (((y + 12) * 0.001) * stageH).toFixed(1) + 'px';
            dot.style.setProperty('--wx', px);
            dot.style.setProperty('--wy', py);
            dot.classList.remove('is-pop');
            // Reinicia a animação no próximo frame sem forçar layout síncrono (zero reflow!)
            requestAnimationFrame(() => dot.classList.add('is-pop'));
        }

        function limparEspumaBarco() {
            const pool = document.getElementById('ivoWakePool');
            if (!pool) return;
            pool.querySelectorAll('.ivo-wake-dot').forEach(d => d.classList.remove('is-pop'));
        }

        function atualizarFarolPortos(origemId, destinoId) {
            [1, 2, 3].forEach(id => {
                const dock = document.getElementById('ivoDock' + id);
                const pin = document.getElementById('ivoPin' + id);
                if (dock) {
                    dock.classList.toggle('is-origin', id === origemId);
                    dock.classList.toggle('is-dest', id === destinoId);
                }
                if (pin) {
                    pin.classList.toggle('is-origin', id === origemId);
                    pin.classList.toggle('is-dest', id === destinoId);
                    pin.classList.remove('is-arrived');
                }
            });
        }

        function fecharOverlayViagem() {
            const overlay = document.getElementById('islandVoyageOverlay');
            if (!overlay) return;
            overlay.classList.remove('active', 'mode-voyage', 'mode-explorer', 'is-docked');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('voyage-lock-scroll');
            mapaModoAtual = 'idle';
            viagemEmAndamento = false;
        }

        function finalizarViagemImediato() {
            limparTimersViagem();
            const destino = viagemDestinoPendente || divisaoAtiva;
            const cb = viagemCallbackPosChegada;
            viagemDestinoPendente = null;
            viagemCallbackPosChegada = null;

            aplicarCapituloVisivel(destino, true);
            fecharOverlayViagem();

            if (typeof cb === 'function') {
                setTimeout(cb, 120);
            }
        }
        window.finalizarViagemImediato = finalizarViagemImediato;

        function tratarAcaoTopoMapa() {
            if (mapaModoAtual === 'voyage') {
                finalizarViagemImediato();
            } else {
                fecharMapaIlhas();
            }
        }
        window.tratarAcaoTopoMapa = tratarAcaoTopoMapa;

        // ── ANIMAÇÃO EM TELA INTEIRA DO BARQUINHO ENTRE CAPÍTULOS (ILHAS) ───
        function viajarParaCapitulo(destinoDiv, animar = true, origemForcada = null, onComplete = null) {
            const destId = Math.max(1, Math.min(totalCapitulosCurso, Number(destinoDiv) || 1));
            const origId = origemForcada
                ? Math.max(1, Math.min(totalCapitulosCurso, Number(origemForcada)))
                : Number(divisaoAtiva || 1);

            if (!animar || destId === origId) {
                aplicarCapituloVisivel(destId, true);
                if (mapaModoAtual === 'explorer') fecharMapaIlhas();
                if (typeof onComplete === 'function') onComplete();
                return;
            }

            if (viagemEmAndamento) return;

            const overlay = document.getElementById('islandVoyageOverlay');
            const stageEl = document.getElementById('ivoMapStage');
            const routeBg = document.getElementById('ivoActiveRouteBg');
            const routeOuter = document.getElementById('ivoActiveRouteOuter');
            const routeGlow = document.getElementById('ivoActiveRouteGlow');
            const wakePool = document.getElementById('ivoWakePool');
            const burstEl = document.getElementById('ivoArrivalBurst');
            const boatEl = document.getElementById('ivoVoyageBoat');

            if (!overlay || !stageEl || !routeBg || !routeGlow || !boatEl) {
                aplicarCapituloVisivel(destId, true);
                if (typeof onComplete === 'function') onComplete();
                return;
            }

            limparTimersViagem();
            viagemEmAndamento = true;
            mapaModoAtual = 'voyage';
            viagemDestinoPendente = destId;
            viagemCallbackPosChegada = onComplete;
            boatFlippedState = null;

            if (window.OpusAudio && typeof window.OpusAudio.playClick === 'function') {
                window.OpusAudio.playClick();
            }

            const infoDest = obterInfoCapitulo(destId);
            const ilhaOrig = OPUS_ILHAS_MAPA[origId] || OPUS_ILHAS_MAPA[1];
            const ilhaDest = OPUS_ILHAS_MAPA[destId] || OPUS_ILHAS_MAPA[2];
            const rotaKey = `${origId}-${destId}`;
            const rotaCfg = OPUS_ROTAS_NAUTICAS[rotaKey] || OPUS_ROTAS_NAUTICAS['1-2'];
            const curva = criarTabelaCurvaBezier(rotaCfg.pts, 160);
            const totalLen = curva.totalDist;

            // Atualiza HUDs do fullscreen (fora do loop rAF)
            const badgeText = document.getElementById('ivoRouteBadgeText');
            const compassIcon = document.getElementById('ivoCompassIcon');
            const topActionLabel = document.getElementById('ivoTopActionLabel');
            const topActionIcon = document.getElementById('ivoTopActionIcon');
            const kickerEl = document.getElementById('ivoTravelKicker');
            const titleEl = document.getElementById('ivoTravelTitle');
            const subEl = document.getElementById('ivoTravelSub');
            const progWrap = document.getElementById('ivoProgressWrap');
            const progFill = document.getElementById('ivoProgressFill');
            const progPct = document.getElementById('ivoProgressPct');

            if (badgeText) badgeText.innerText = `CAPÍTULO ${origId}  ➜  CAPÍTULO ${destId}`;
            if (compassIcon) compassIcon.className = 'fa-solid fa-compass fa-spin';
            if (topActionLabel) topActionLabel.innerText = 'Pular animação';
            if (topActionIcon) topActionIcon.className = 'fa-solid fa-forward-step';
            if (kickerEl) kickerEl.innerHTML = `<i class="fa-solid fa-sailboat"></i> <span>NAVEGANDO PARA A ${ilhaDest.nomeCurto.toUpperCase()}</span>`;
            if (titleEl) titleEl.innerText = `Capítulo ${destId} · ${infoDest.tema || infoDest.nome}`;
            if (subEl) subEl.innerText = `Saindo do Capítulo ${origId} rumo às Seções ${infoDest.secao_inicio} a ${infoDest.secao_fim}...`;
            if (progWrap) progWrap.style.display = 'flex';
            if (progFill) progFill.style.transform = 'scaleX(0)';
            if (progPct) progPct.textContent = '0%';

            // Configura rota SVG leve (sem filtro blur)
            limparEspumaBarco();
            routeBg.setAttribute('d', rotaCfg.d);
            routeGlow.setAttribute('d', rotaCfg.d);
            if (routeOuter) routeOuter.setAttribute('d', rotaCfg.d);

            // Usa o comprimento real do SVG apenas uma vez para dashoffset perfeito
            const svgLen = routeGlow.getTotalLength() || totalLen;
            routeGlow.style.strokeDasharray = `${svgLen}`;
            routeGlow.style.strokeDashoffset = `${svgLen}`;
            if (routeOuter) {
                routeOuter.style.strokeDasharray = `${svgLen}`;
                routeOuter.style.strokeDashoffset = `${svgLen}`;
            }

            if (burstEl) {
                burstEl.classList.remove('active');
                burstEl.setAttribute('transform', `translate(${ilhaDest.dock.x}, ${ilhaDest.dock.y})`);
            }

            atualizarFarolPortos(origId, destId);

            // Abre o overlay em tela inteira antes de medir stageW/stageH
            document.body.classList.add('voyage-lock-scroll');
            overlay.classList.remove('mode-explorer', 'is-docked');
            overlay.classList.add('mode-voyage', 'active');
            overlay.setAttribute('aria-hidden', 'false');

            const dim = obterDimensoesPalcoMapa();
            const stageW = dim.w;
            const stageH = dim.h;
            const wakeDots = wakePool ? Array.from(wakePool.querySelectorAll('.ivo-wake-dot')) : [];

            const pStart = amostrarCurvaPorDistancia(curva, 0);
            posicionarBarcoNoMapa(pStart.x, pStart.y, pStart.dx < 0, stageW, stageH);
            boatEl.classList.add('is-sailing');

            const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            aplicarCameraMapa(ilhaOrig.cam.x, ilhaOrig.cam.y, reduceMotion ? 1.0 : 1.12, stageEl);

            const duracaoViagem = reduceMotion ? 600 : 2400;
            const atrasoInicio = reduceMotion ? 80 : 260;
            let ultimoWakeTime = 0;
            let lastPct = -1;

            const tStart = setTimeout(() => {
                // Reconfirma dimensões uma única vez fora do loop caso a viewport tenha se ajustado
                const dNow = obterDimensoesPalcoMapa();
                const sW = dNow.w || stageW;
                const sH = dNow.h || stageH;
                const startPerf = performance.now();

                function animarFrame(now) {
                    if (!viagemEmAndamento) return;

                    const elapsed = now - startPerf;
                    const rawT = Math.min(1, elapsed / duracaoViagem);
                    // Curva suave aceleração -> cruzeiro constante -> atracação macia
                    const easedT = rawT < 0.5
                        ? 4 * rawT * rawT * rawT
                        : 1 - Math.pow(-2 * rawT + 2, 3) / 2;

                    const pt = amostrarCurvaPorDistancia(curva, easedT);
                    const goingLeft = pt.dx < -0.25;

                    // 100% GPU compositor (zero reflow / zero layout / zero getPointAtLength)
                    posicionarBarcoNoMapa(pt.x, pt.y, goingLeft, sW, sH);

                    const dashRem = (svgLen * (1 - easedT)).toFixed(1);
                    routeGlow.style.strokeDashoffset = dashRem;
                    if (routeOuter) routeOuter.style.strokeDashoffset = dashRem;

                    if (progFill) {
                        progFill.style.transform = `scaleX(${easedT.toFixed(4)})`;
                    }
                    const pct = Math.round(easedT * 100);
                    if (progPct && pct !== lastPct) {
                        lastPct = pct;
                        progPct.textContent = pct + '%';
                    }

                    // Espuma pré-alocada (zero criação/remoção de nós DOM)
                    if (!reduceMotion && rawT > 0.04 && rawT < 0.95 && (now - ultimoWakeTime) > 155) {
                        ultimoWakeTime = now;
                        gerarOndaEspumaBarco(pt.x, pt.y, sW, sH, wakeDots);
                    }

                    // Câmera panorâmica suave em 60fps
                    if (!reduceMotion) {
                        const camX = ilhaOrig.cam.x + (ilhaDest.cam.x - ilhaOrig.cam.x) * easedT;
                        const camY = ilhaOrig.cam.y + (ilhaDest.cam.y - ilhaOrig.cam.y) * easedT;
                        const zoomArc = 1.03 + 0.09 * Math.pow(2 * easedT - 1, 2);
                        aplicarCameraMapa(camX, camY, zoomArc, stageEl);
                    }

                    if (rawT < 1) {
                        viagemRafId = requestAnimationFrame(animarFrame);
                    } else {
                        // Atracou na ilha de destino! Só agora troca as seções no fundo (zero engasgo durante a travessia!)
                        boatEl.classList.remove('is-sailing');
                        overlay.classList.add('is-docked');

                        if (compassIcon) compassIcon.className = 'fa-solid fa-flag-checkered';
                        if (kickerEl) kickerEl.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>CHEGAMOS À ${ilhaDest.nomeCurto.toUpperCase()}!</span>`;
                        if (subEl) subEl.innerText = `Todas as seções (${infoDest.secao_inicio} a ${infoDest.secao_fim}) prontas para explorar!`;

                        const destPin = document.getElementById('ivoPin' + destId);
                        if (destPin) destPin.classList.add('is-arrived');

                        if (burstEl) {
                            burstEl.classList.remove('active');
                            requestAnimationFrame(() => burstEl.classList.add('active'));
                        }

                        if (window.OpusAudio && typeof window.OpusAudio.playSuccess === 'function') {
                            window.OpusAudio.playSuccess();
                        }

                        // Troca as seções do capítulo por trás do overlay opaco enquanto o barco comemora a chegada
                        const tSwap = setTimeout(() => {
                            aplicarCapituloVisivel(destId, true);
                        }, 120);
                        viagemTimers.push(tSwap);

                        const tClose = setTimeout(() => {
                            const cb = viagemCallbackPosChegada;
                            viagemDestinoPendente = null;
                            viagemCallbackPosChegada = null;
                            fecharOverlayViagem();
                            requestAnimationFrame(renderizarTrilhasConexao);
                            if (typeof cb === 'function') {
                                setTimeout(cb, 140);
                            }
                        }, reduceMotion ? 320 : 780);
                        viagemTimers.push(tClose);
                    }
                }

                viagemRafId = requestAnimationFrame(animarFrame);
            }, atrasoInicio);
            viagemTimers.push(tStart);
        }
        window.viajarParaCapitulo = viajarParaCapitulo;

        function navegarCapitulo(delta) {
            const alvo = Number(divisaoAtiva || 1) + Number(delta);
            if (alvo < 1 || alvo > totalCapitulosCurso) return;
            viajarParaCapitulo(alvo, true);
        }
        window.navegarCapitulo = navegarCapitulo;

        // ── MODO INTERATIVO: MAPA EM TELA INTEIRA DAS 3 ILHAS ───────────────
        function abrirMapaIlhas() {
            if (viagemEmAndamento) return;
            if (window.OpusAudio && typeof window.OpusAudio.playClick === 'function') {
                window.OpusAudio.playClick();
            }

            const overlay = document.getElementById('islandVoyageOverlay');
            const routeBg = document.getElementById('ivoActiveRouteBg');
            const routeOuter = document.getElementById('ivoActiveRouteOuter');
            const routeGlow = document.getElementById('ivoActiveRouteGlow');
            const burstEl = document.getElementById('ivoArrivalBurst');
            const boatEl = document.getElementById('ivoVoyageBoat');
            if (!overlay) return;

            limparTimersViagem();
            mapaModoAtual = 'explorer';
            boatFlippedState = null;

            const atualId = Number(divisaoAtiva || 1);
            const infoAtual = obterInfoCapitulo(atualId);
            const ilhaAtual = OPUS_ILHAS_MAPA[atualId] || OPUS_ILHAS_MAPA[1];

            const badgeText = document.getElementById('ivoRouteBadgeText');
            const compassIcon = document.getElementById('ivoCompassIcon');
            const topActionLabel = document.getElementById('ivoTopActionLabel');
            const topActionIcon = document.getElementById('ivoTopActionIcon');
            const kickerEl = document.getElementById('ivoTravelKicker');
            const titleEl = document.getElementById('ivoTravelTitle');
            const subEl = document.getElementById('ivoTravelSub');
            const progWrap = document.getElementById('ivoProgressWrap');

            if (badgeText) badgeText.innerText = 'MAPA DO ARQUIPÉLAGO OPUS · 3 CAPÍTULOS';
            if (compassIcon) compassIcon.className = 'fa-solid fa-map-location-dot';
            if (topActionLabel) topActionLabel.innerText = 'Fechar mapa';
            if (topActionIcon) topActionIcon.className = 'fa-solid fa-xmark';
            if (kickerEl) kickerEl.innerHTML = `<i class="fa-solid fa-anchor"></i> <span>VOCÊ ESTÁ NO CAPÍTULO ${atualId} (${ilhaAtual.nomeCurto.toUpperCase()})</span>`;
            if (titleEl) titleEl.innerText = infoAtual.tema || infoAtual.nome;
            if (subEl) subEl.innerText = 'Clique em qualquer outra ilha acima para navegar de barco até o próximo capítulo!';
            if (progWrap) progWrap.style.display = 'none';

            if (routeBg) routeBg.setAttribute('d', '');
            if (routeOuter) routeOuter.setAttribute('d', '');
            if (routeGlow) routeGlow.setAttribute('d', '');
            limparEspumaBarco();
            if (burstEl) burstEl.classList.remove('active');
            if (boatEl) boatEl.classList.remove('is-sailing');

            atualizarFarolPortos(atualId, 0);
            aplicarCameraMapa(500, 500, 1.0);

            document.body.classList.add('voyage-lock-scroll');
            overlay.classList.remove('mode-voyage', 'is-docked');
            overlay.classList.add('mode-explorer', 'active');
            overlay.setAttribute('aria-hidden', 'false');

            const dim = obterDimensoesPalcoMapa();
            posicionarBarcoNoMapa(ilhaAtual.dock.x, ilhaAtual.dock.y, atualId === 2, dim.w, dim.h);
        }
        window.abrirMapaIlhas = abrirMapaIlhas;
        window.abrirMapaArquipelago = abrirMapaIlhas;

        function fecharMapaIlhas() {
            if (viagemEmAndamento) return;
            if (window.OpusAudio && typeof window.OpusAudio.playClick === 'function') {
                window.OpusAudio.playClick();
            }
            fecharOverlayViagem();
        }
        window.fecharMapaIlhas = fecharMapaIlhas;
        window.fecharMapaArquipelago = fecharMapaIlhas;

        function selecionarIlhaCapituloMapa(numCapitulo) {
            if (viagemEmAndamento) return;
            const alvo = Number(numCapitulo) || 1;
            if (alvo === Number(divisaoAtiva)) {
                fecharMapaIlhas();
                return;
            }
            viajarParaCapitulo(alvo, true);
        }
        window.selecionarIlhaCapituloMapa = selecionarIlhaCapituloMapa;

        // Compatibilidade com botões das Divisões (sidebar "Meu Progresso" e cartões de fim de capítulo)
        function irParaDivisao(divId) {
            selecionarDivisao(divId, true);
        }
        window.irParaDivisao = irParaDivisao;

        function selecionarDivisao(divId, animar = true) {
            if (divId === 'todas') {
                abrirMapaIlhas();
                return;
            }
            viajarParaCapitulo(Number(divId) || 1, animar);
        }
        window.selecionarDivisao = selecionarDivisao;

        // ── Scroll para a lição atual e Botão Flutuante de Seta ───────────
        function rolarAteLicaoAtual(tocarSom = false) {
            if (tocarSom && window.OpusAudio) window.OpusAudio.playClick();

            const secaoUsuario = Number(window.OPUS_ILHA_ATUAL_USUARIO || 1);
            const divisaoUsuario = obterDivisaoDaSecao(secaoUsuario);

            const focarNoAtual = () => {
                const capAtivo = document.getElementById('capitulo_' + secaoUsuario);
                const licaoAtual = (capAtivo && capAtivo.querySelector('.modulo-node.current'))
                                || document.getElementById('currentLessonNode')
                                || (capAtivo && capAtivo.querySelector('.modulo-node.completed'))
                                || capAtivo;

                if (licaoAtual && licaoAtual.offsetParent !== null) {
                    licaoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    licaoAtual.classList.add('pulse-highlight');
                    setTimeout(() => licaoAtual.classList.remove('pulse-highlight'), 2200);
                }
            };

            if (Number(divisaoAtiva) !== Number(divisaoUsuario)) {
                viajarParaCapitulo(divisaoUsuario, true, null, focarNoAtual);
            } else {
                setTimeout(focarNoAtual, 80);
            }
        }

        // Monitora o scroll para orientar a direção da seta (cima ou baixo) e ajustar a opacidade quando visível
        function monitorarVisibilidadeLicaoAtual() {
            const btn = document.getElementById('btnScrollToLesson');
            const container = document.querySelector('.main-content');
            if (!btn) return;

            const icone = btn.querySelector('i');

            const checar = () => {
                const alvo = document.getElementById('currentLessonNode') 
                          || document.querySelector('.modulo-node.current')
                          || document.querySelector('.capitulo-container.current-chapter');
                
                if (!alvo || alvo.offsetParent === null) {
                    // Alvo está em outra divisão: convida a ir até a lição atual
                    btn.style.opacity = '1';
                    btn.style.transform = 'scale(1)';
                    if (icone && !icone.classList.contains('fa-crosshairs')) {
                        icone.className = 'fa-solid fa-crosshairs';
                    }
                    btn.setAttribute('title', 'Ir para a sua lição atual na divisão ativa');
                    return;
                }

                const rect = alvo.getBoundingClientRect();
                const containerRect = container ? container.getBoundingClientRect() : { top: 0, bottom: window.innerHeight };
                
                // Posição central do elemento alvo e do container de rolagem
                const alvoCentro = (rect.top + rect.bottom) / 2;
                const containerCentro = (containerRect.top + containerRect.bottom) / 2;
                
                // Verifica se a lição/capítulo está no campo de visão
                const visivel = (rect.top >= containerRect.top + 60 && rect.bottom <= containerRect.bottom - 60);

                if (icone) {
                    if (alvoCentro < containerCentro) {
                        if (!icone.classList.contains('fa-arrow-up')) {
                            icone.className = 'fa-solid fa-arrow-up';
                        }
                        btn.setAttribute('title', 'Rolar para cima até a lição atual');
                    } else {
                        if (!icone.classList.contains('fa-arrow-down')) {
                            icone.className = 'fa-solid fa-arrow-down';
                        }
                        btn.setAttribute('title', 'Rolar para baixo até a lição atual');
                    }
                }

                if (visivel) {
                    btn.style.opacity = '0.35';
                    btn.style.transform = 'scale(0.92)';
                } else {
                    btn.style.opacity = '1';
                    btn.style.transform = 'scale(1)';
                }
            };

            if (container) {
                container.addEventListener('scroll', checar, { passive: true });
            }
            window.addEventListener('scroll', checar, { passive: true });
            checar();
        }

        // Animação e interação viva dos mascotes laterais no Dashboard
        function inicializarMascotesDashboard() {
            const mascotes = document.querySelectorAll('.mascote-lateral');
            const falas = [
                "Bora codar em Java hoje! 🔥",
                "Mantenha seus dias de fogo! 💪",
                "Você está mandando muito bem! 🚀",
                "Cada lição te deixa mais perto de mestre! ☕",
                "Vamos rumo ao topo da liga! 🏆"
            ];

            mascotes.forEach(m => {
                m.style.pointerEvents = 'auto';
                m.style.cursor = 'pointer';
                m.setAttribute('title', 'Clique no mascote para incentivo!');

                m.addEventListener('click', function(e) {
                    if (window.OpusAudio) window.OpusAudio.playClick();

                    // Animação de pulo/spin festivo
                    m.classList.remove('mascote-jump-active');
                    void m.offsetWidth;
                    m.classList.add('mascote-jump-active');

                    // Remove balão anterior se existir
                    const balaoAntigo = m.parentElement.querySelector('.mascote-balao-dash');
                    if (balaoAntigo) balaoAntigo.remove();

                    // Cria balãozinho cartoon temporário
                    const balao = document.createElement('div');
                    balao.className = 'mascote-balao-dash';
                    balao.innerText = falas[Math.floor(Math.random() * falas.length)];
                    m.parentElement.appendChild(balao);

                    // Posiciona conforme lateral
                    if (m.classList.contains('mascote-esquerda')) {
                        balao.style.left = '20px';
                    } else {
                        balao.style.right = '20px';
                    }

                    setTimeout(() => {
                        balao.classList.add('fade-out');
                        setTimeout(() => balao.remove(), 300);
                    }, 2400);
                });
            });

            // Adiciona feedback sonoro sutil aos nós de lição clicáveis
            document.querySelectorAll('.modulo-node.completed, .modulo-node.current').forEach(node => {
                node.addEventListener('click', function() {
                    if (window.OpusAudio) window.OpusAudio.playClick();
                });
            });
        }

        // ── TRILHA VISUAL CONECTANDO OS MÓDULOS (ESTILO WOOFZ S-CURVE ROAD + KUMI) ──
        function getElementCenterStatic(targetEl, containerEl) {
            if (!targetEl || !containerEl) return { x: 0, y: 0 };
            
            const tRect = targetEl.getBoundingClientRect();
            const cRect = containerEl.getBoundingClientRect();

            // Normaliza a escala caso um ancestral (.capitulo-container) esteja com transform: scale(...) durante animação de chegada
            const scaleX = (containerEl.offsetWidth > 0 && cRect.width > 0) ? (cRect.width / containerEl.offsetWidth) : 1;
            const scaleY = (containerEl.offsetHeight > 0 && cRect.height > 0) ? (cRect.height / containerEl.offsetHeight) : 1;
            
            // Cancela o deslocamento vertical temporário de qualquer animação floatNode
            let animOffsetY = 0;
            let curr = targetEl;
            while (curr && curr !== containerEl) {
                const tr = window.getComputedStyle(curr).transform;
                if (tr && tr !== 'none') {
                    const m = tr.match(/matrix.*\((.+)\)/);
                    if (m) {
                        const parts = m[1].split(',').map(v => parseFloat(v.trim()));
                        if (parts.length === 6) animOffsetY += parts[5];
                        else if (parts.length === 16) animOffsetY += parts[13];
                    }
                }
                curr = curr.parentElement;
            }

            const x = ((tRect.left + tRect.right) / 2 - cRect.left) / scaleX;
            const y = (((tRect.top + tRect.bottom) / 2 - cRect.top) / scaleY) - animOffsetY;
            return { x, y };
        }

        function renderizarTrilhasConexao() {
            const trails = document.querySelectorAll('.trail-flex');
            if (!trails || trails.length === 0) return;

            trails.forEach(trail => {
                let svg = trail.querySelector('.trail-svg');
                if (!svg) {
                    svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                    svg.classList.add('trail-svg');
                    svg.setAttribute('aria-hidden', 'true');
                    trail.insertBefore(svg, trail.firstChild);
                }

                const trailW = trail.clientWidth;
                const trailH = trail.clientHeight;
                if (trailW === 0 || trailH === 0) return;

                svg.setAttribute('width', trailW);
                svg.setAttribute('height', trailH);
                svg.setAttribute('viewBox', `0 0 ${trailW} ${trailH}`);

                // Busca todos os marcos na ordem do percurso (nós de lição e baús)
                const milestoneEls = Array.from(trail.querySelectorAll('.modulo-node, .reward-chest-container'));
                if (milestoneEls.length < 2) {
                    svg.innerHTML = '';
                    return;
                }

                const capStatus = trail.getAttribute('data-status') || '';
                const unitColor = trail.getAttribute('data-cor') || '#1cb0f6';
                const capitulo = trail.closest('.capitulo-container');

                // Coleta coordenadas e status de cada marco
                const milestones = milestoneEls.map(el => {
                    const isChest = el.classList.contains('reward-chest-container');
                    const target = isChest 
                        ? (el.querySelector('.chest-node') || el)
                        : (el.querySelector('.circle-button') || el);

                    const pos = getElementCenterStatic(target, trail);

                    let status = 'locked';
                    if (isChest) {
                        const chestNode = el.querySelector('.chest-node');
                        if (chestNode && chestNode.classList.contains('claimed')) status = 'completed';
                        else if (chestNode && chestNode.classList.contains('available')) status = 'current';
                    } else {
                        if (el.classList.contains('completed')) status = 'completed';
                        else if (el.classList.contains('current')) status = 'current';
                    }

                    return { el, pos, status, isChest };
                });

                // Constrói os caminhos Bezier fluidos e orgânicos entre os marcos da trilha
                const segments = [];
                let fullPathD = '';

                for (let i = 0; i < milestones.length - 1; i++) {
                    const p1 = milestones[i].pos;
                    const p2 = milestones[i + 1].pos;
                    const dy = p2.y - p1.y;

                    // Curva S suave e orgânica (estilo trilha natural da ilha)
                    const cp1x = p1.x;
                    const cp1y = p1.y + dy * 0.54;
                    const cp2x = p2.x;
                    const cp2y = p2.y - dy * 0.54;

                    const segmentD = `M ${p1.x.toFixed(1)} ${p1.y.toFixed(1)} C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`;

                    if (i === 0) {
                        fullPathD = segmentD;
                    } else {
                        fullPathD += ` C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`;
                    }

                    // Determina se este segmento específico está concluído ou futuro
                    let segState = 'future';
                    if (capStatus === 'completo') {
                        segState = 'completed';
                    } else if (capStatus === 'trancado') {
                        segState = 'locked';
                    } else {
                        const mNext = milestones[i + 1];
                        const mCurr = milestones[i];

                        if (mNext.status === 'completed') {
                            segState = 'completed';
                        } else if (mNext.status === 'current') {
                            segState = 'completed';
                        } else if (mCurr.status === 'completed' && mNext.status === 'locked' && i === 0) {
                            segState = 'completed';
                        } else {
                            segState = 'future';
                        }
                    }

                    segments.push({ d: segmentD, state: segState });
                }

                // Monta o SVG minimalista e orgânico da trilha da ilha
                let html = '';
                const isTrancado = (capStatus === 'trancado');

                // 1. Brilho/leito orgânico suave sob o caminho
                if (fullPathD) {
                    html += `<path class="trail-path-shadow" d="${fullPathD}"></path>`;
                }

                // 2. Linha base delicada da trilha (contínua ou tracejada quando trancada)
                const baseClass = isTrancado ? 'trail-path-locked' : 'trail-path-base';
                if (fullPathD) {
                    html += `<path class="${baseClass}" d="${fullPathD}"></path>`;
                }

                // 3. Trechos concluídos / ativos com fluidez orgânica
                segments.forEach(seg => {
                    if (seg.state === 'completed') {
                        html += `<path class="trail-path-completed" d="${seg.d}" style="stroke: ${unitColor}; color: ${unitColor};"></path>`;
                    }
                });

                // 4. Pontilhado sutil de passos orgânicos sobre a trilha
                if (fullPathD) {
                    html += `<path class="trail-path-centerline" d="${fullPathD}"></path>`;
                }

                svg.innerHTML = html;
            });
        }

        // Aguarda o carregamento completo para calcular posições e desenhar trilhas
        window.addEventListener('load', function () {
            const params = new URLSearchParams(window.location.search);
            const bauNum = Number(params.get('abrir_bau') || 0);
            const viajarCap = Number(params.get('viajar_capitulo') || 0);
            const deCap = Number(params.get('de_capitulo') || 0);

            if (viajarCap >= 1 && viajarCap <= totalCapitulosCurso && deCap >= 1 && deCap <= totalCapitulosCurso && deCap !== viajarCap) {
                aplicarCapituloVisivel(deCap, false);
                setTimeout(() => viajarParaCapitulo(viajarCap, true, deCap), 220);
            } else if (bauNum >= 1) {
                const divBau = obterDivisaoDaSecao(bauNum);
                aplicarCapituloVisivel(divBau, false);
            } else {
                const divInicial = Number(window.OPUS_DIVISAO_INICIAL || 1);
                aplicarCapituloVisivel(divInicial, false);
            }

            setTimeout(monitorarVisibilidadeLicaoAtual, 300);
            inicializarMascotesDashboard();
            setTimeout(renderizarTrilhasConexao, 60);
            setTimeout(renderizarTrilhasConexao, 400); // Garante após webfonts e layout final
        });

        document.addEventListener('DOMContentLoaded', function () {
            renderizarTrilhasConexao();
            popularTrilhoRoleta();
        });

        // Atualização reativa com redimensionamento de tela
        let resizeTimerTrail;
        window.addEventListener('resize', function () {
            cancelAnimationFrame(resizeTimerTrail);
            resizeTimerTrail = requestAnimationFrame(function () {
                renderizarTrilhasConexao();
                if (mapaModoAtual === 'explorer') {
                    const ilhaAtual = OPUS_ILHAS_MAPA[Number(divisaoAtiva || 1)] || OPUS_ILHAS_MAPA[1];
                    const dim = obterDimensoesPalcoMapa();
                    posicionarBarcoNoMapa(ilhaAtual.dock.x, ilhaAtual.dock.y, Number(divisaoAtiva) === 2, dim.w, dim.h);
                }
            });
        }, { passive: true });

        const observerCurriculum = (window.ResizeObserver && document.querySelector('.curriculum-column'))
            ? new ResizeObserver(function () {
                requestAnimationFrame(renderizarTrilhasConexao);
            })
            : null;

        if (observerCurriculum) {
            observerCurriculum.observe(document.querySelector('.curriculum-column'));
        }

        // Abertura automática do baú quando redirecionado da tela de resultado (?abrir_bau=X)
        (function() {
            try {
                const params = new URLSearchParams(window.location.search);
                const bauNum = params.get('abrir_bau');
                if (bauNum) {
                    setTimeout(() => {
                        const chestNode = document.getElementById('chestNode_' + bauNum);
                        if (chestNode) {
                            chestNode.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            chestNode.classList.add('chest-spotlight-focus');
                            // Se estiver disponível para abrir, aciona o modal com animação
                            if (chestNode.classList.contains('available')) {
                                setTimeout(() => {
                                    chestNode.click();
                                }, 600);
                            }
                        }
                    }, 450);
                }

                // Spotlight e Animação de Fogo quando redirecionado da lição (?fogo_aceso=1 ou ?spotlight_fogo=1)
                const fogoAtivado = params.get('fogo_aceso') || params.get('spotlight_fogo') || params.get('streak');
                if (fogoAtivado) {
                    setTimeout(() => {
                        const widgetFogo = document.getElementById('widgetOfensiva') || document.querySelector('.widget-ofensiva');
                        if (widgetFogo) {
                            widgetFogo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            widgetFogo.classList.add('ofensiva-spotlight-focus');
                            if (window.OpusAudio && window.OpusAudio.playSuccess) {
                                window.OpusAudio.playSuccess();
                            }
                        }
                    }, 450);
                }

                // Interação com o Quadro de Ofensiva (clique para animar durante o vídeo)
                const widgetOfensiva = document.getElementById('widgetOfensiva') || document.querySelector('.widget-ofensiva');
                if (widgetOfensiva) {
                    widgetOfensiva.addEventListener('click', function() {
                        if (window.OpusAudio && window.OpusAudio.playClick) {
                            window.OpusAudio.playClick();
                        }
                        widgetOfensiva.classList.remove('ofensiva-spotlight-focus');
                        void widgetOfensiva.offsetWidth;
                        widgetOfensiva.classList.add('ofensiva-spotlight-focus');
                    });
                }

                // Acessibilidade por teclado nos baús da trilha (Enter / Espaço)
                document.addEventListener('keydown', function(ev) {
                    if (ev.key !== 'Enter' && ev.key !== ' ') return;
                    const activeEl = document.activeElement;
                    if (activeEl && activeEl.classList && activeEl.classList.contains('chest-node')) {
                        ev.preventDefault();
                        activeEl.click();
                    }
                });
            } catch (e) {}
        })();


