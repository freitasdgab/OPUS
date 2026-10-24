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

        function abrirModalBau(cap, titulo) {
            if (window.OpusAudio) window.OpusAudio.playClick();
            capAtualBau = cap;
            tituloAtualBau = titulo || ('Unidade ' + cap);

            const modalTitle = document.getElementById('modalBauTitle');
            const modalSub = document.getElementById('modalBauSub');
            if (modalTitle) modalTitle.innerText = 'Baú de Recompensas!';
            if (modalSub) modalSub.innerHTML = '<i class="fa-solid fa-crown"></i> Unidade ' + cap + (titulo ? (' · ' + titulo) : '') + ' · Meio de Trilha';

            // Reseta visual do baú no modal
            const chestGraphic = document.getElementById('modalChestGraphic');
            if (chestGraphic) {
                chestGraphic.classList.remove('chest-modal-rumble', 'chest-open-burst');
            }
            const sunburst = document.getElementById('chestSunburst');
            if (sunburst) {
                sunburst.classList.remove('sunburst-active');
            }

            const canvas = document.getElementById('chestConfettiCanvas');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }

            document.getElementById('rewardContainerPre').style.display = 'block';
            document.getElementById('rewardContainerPos').style.display = 'none';
            const btnResgatar = document.getElementById('btnResgatarBau');
            btnResgatar.style.display = 'flex';
            btnResgatar.innerHTML = '<i class="fa-solid fa-box-open"></i> ABRIR BAÚ DE RECOMPENSA';
            btnResgatar.disabled = false;
            document.getElementById('btnFecharBauPronto').style.display = 'none';
            
            const modal = document.getElementById('modalBauRecompensa');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function fecharModalBau() {
            if (window.OpusAudio) window.OpusAudio.playClick();
            if (chestConfettiAnimId) {
                cancelAnimationFrame(chestConfettiAnimId);
                chestConfettiAnimId = null;
            }
            const modal = document.getElementById('modalBauRecompensa');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
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
            iconBox.style.background = '#58cc02';
            iconBox.style.border = '3px solid #2e1800';
            iconBox.style.borderBottom = '6px solid #225c01';
            iconBox.style.boxShadow = 'none';
            
            const icon = document.getElementById('avisoBauIcone');
            icon.className = 'fa-solid fa-box-open';
            icon.style.color = '#fff';

            document.getElementById('avisoBauTitulo').innerText = 'Baú Já Coletado!';
            document.getElementById('avisoBauSub').innerHTML = '<i class="fa-solid fa-circle-check"></i> Unidade ' + cap + (titulo ? (' · ' + titulo) : '');
            
            const progressBox = document.getElementById('avisoBauProgressoBox');
            if (progressBox) progressBox.style.display = 'none';

            document.getElementById('avisoBauTexto').innerHTML = 'Você já resgatou as recompensas deste baú da <strong>Unidade ' + cap + '</strong> (+100 XP e Corações de Vida extras)! Continue sua trilha para encontrar novos baús nas próximas unidades.';
            
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
            iconBox.style.background = '#ff9600';
            iconBox.style.border = '3px solid #2e1800';
            iconBox.style.borderBottom = '6px solid #b45309';
            iconBox.style.boxShadow = 'none';

            const icon = document.getElementById('avisoBauIcone');
            icon.className = 'fa-solid fa-lock';
            icon.style.color = '#fff';

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
                desc = 'Conclua as 3 primeiras lições da <strong>Unidade ' + cap + '</strong> para desbloquear este baú mágico de recompensas com <strong>+100 XP</strong> e <strong>Corações de Vida</strong>!';
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

        function resgatarRecompensaBau() {
            const btn = document.getElementById('btnResgatarBau');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Abrindo Baú...';

            const chestGraphic = document.getElementById('modalChestGraphic');
            const sunburst = document.getElementById('chestSunburst');
            if (chestGraphic) chestGraphic.classList.add('chest-modal-rumble');
            if (sunburst) sunburst.classList.add('sunburst-active');

            // Suspense sonoro da abertura
            if (window.OpusAudio && typeof window.OpusAudio.playChestRumble === 'function') {
                window.OpusAudio.playChestRumble();
            }

            fetch('../../back/api/resgatar_bau.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'unidade_numero=' + encodeURIComponent(capAtualBau)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    if (chestGraphic) chestGraphic.classList.remove('chest-modal-rumble');
                    if (sunburst) sunburst.classList.remove('sunburst-active');

                    if (typeof opusToast === 'function') {
                        opusToast(data.mensagem || 'Não foi possível resgatar o baú.', 'error');
                    } else {
                        alert(data.mensagem || 'Não foi possível resgatar o baú.');
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> TENTAR NOVAMENTE';
                    return;
                }

                // Efeito triunfal de abertura com fanfarra e confetes
                if (chestGraphic) {
                    chestGraphic.classList.remove('chest-modal-rumble');
                    chestGraphic.classList.add('chest-open-burst');
                }
                if (window.OpusAudio) {
                    window.OpusAudio.playChest();
                }
                dispararConfetesBau();

                document.getElementById('rewardContainerPre').style.display = 'none';
                const containerPos = document.getElementById('rewardContainerPos');
                containerPos.style.display = 'block';

                // Animação de entrada dos cards
                const cardXp = document.getElementById('resgateXpCard');
                const cardVida = document.getElementById('resgateVidaCard');
                if (cardXp) {
                    cardXp.classList.remove('reveal-bounce');
                    void cardXp.offsetWidth;
                    cardXp.classList.add('reveal-bounce');
                }
                if (cardVida) {
                    cardVida.classList.remove('reveal-bounce');
                    void cardVida.offsetWidth;
                    setTimeout(() => cardVida.classList.add('reveal-bounce'), 120);
                }

                animarContadorXP(document.getElementById('resgateXpVal'), data.xp_ganho);
                
                if (data.vidas_ganhas > 0) {
                    document.getElementById('resgateVidaVal').innerText = '+1 Coração';
                    document.getElementById('resgateVidaLbl').innerText = 'Vida Recuperada';
                    document.getElementById('resgateVidaIcon').className = 'fa-solid fa-heart rc-icon';
                    document.getElementById('resgateVidaBadge').innerHTML = '<i class="fa-solid fa-shield-heart"></i> Recuperação';
                    cardVida.classList.remove('vida-max');
                } else {
                    document.getElementById('resgateVidaVal').innerText = '+100 XP Extra';
                    document.getElementById('resgateVidaLbl').innerText = 'Vidas no Máximo';
                    document.getElementById('resgateVidaIcon').className = 'fa-solid fa-shield-heart rc-icon';
                    document.getElementById('resgateVidaBadge').innerHTML = '<i class="fa-solid fa-star"></i> Bônus Máximo';
                    cardVida.classList.add('vida-max');
                }

                document.getElementById('resgateDetalheTxt').innerText = data.detalhe || data.mensagem;
                btn.style.display = 'none';
                document.getElementById('btnFecharBauPronto').style.display = 'flex';

                // Atualiza o nó 3D do baú na trilha
                const chestNode = document.getElementById('chestNode_' + capAtualBau);
                if (chestNode) {
                    chestNode.className = 'chest-node claimed';
                    chestNode.onclick = function() { avisoBauColetado(capAtualBau, tituloAtualBau); };
                    chestNode.innerHTML = `
                        <div class="chest-sprite open">
                            <div class="chest-lid"></div>
                            <div class="chest-body"><span class="chest-interior-glow"></span></div>
                        </div>
                        <span class="chest-tag-done"><i class="fa-solid fa-check"></i> Coletado</span>
                    `;
                    const balloon = chestNode.parentElement.querySelector('.chest-balloon');
                    if (balloon) balloon.remove();
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
            })
            .catch(err => {
                console.error(err);
                if (chestGraphic) chestGraphic.classList.remove('chest-modal-rumble');
                if (sunburst) sunburst.classList.remove('sunburst-active');

                if (typeof opusToast === 'function') {
                    opusToast('Erro de conexão ao abrir o baú.', 'error');
                } else {
                    alert('Erro de conexão ao abrir o baú.');
                }
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-box-open"></i> ABRIR BAÚ DE RECOMPENSA';
            });
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
        }

        // ── GERENCIAMENTO DAS DIVISÕES (Divisão 1, 2 e 3) ──────────────────
        let divisaoAtiva = (typeof window.OPUS_DIVISAO_INICIAL !== 'undefined') ? window.OPUS_DIVISAO_INICIAL : 1;

        function irParaDivisao(divId) {
            if (window.OpusAudio) window.OpusAudio.playClick();
            selecionarDivisao(divId, false);

            // Rola imediatamente para o topo do container e da janela
            const container = document.querySelector('.main-content');
            if (container) {
                container.scrollTop = 0;
            }
            window.scrollTo({ top: 0, left: 0, behavior: 'auto' });

            requestAnimationFrame(() => {
                if (container) container.scrollTop = 0;
                window.scrollTo(0, 0);

                const primeiroCap = Array.from(document.querySelectorAll('.capitulo-container'))
                    .find(c => c.style.display !== 'none');
                if (primeiroCap) {
                    primeiroCap.scrollIntoView({ behavior: 'auto', block: 'start' });
                }
            });
        }
        window.irParaDivisao = irParaDivisao;

        function selecionarDivisao(divId, tocarSom = true) {
            if (tocarSom && window.OpusAudio) window.OpusAudio.playClick();
            divisaoAtiva = divId;

            try {
                localStorage.setItem('opus_divisao_ativa', String(divId));
            } catch (e) {}

            // Atualiza botões discretos de voltar no topo
            const btnV2 = document.getElementById('btnVoltarDivisao2');
            const btnV3 = document.getElementById('btnVoltarDivisao3');
            if (btnV2) btnV2.style.display = (String(divId) === '2') ? 'block' : 'none';
            if (btnV3) btnV3.style.display = (String(divId) === '3') ? 'block' : 'none';

            // Atualiza classes ativas nas abas (se existirem)
            document.querySelectorAll('.divisao-tab-btn').forEach(btn => {
                const bId = btn.getAttribute('data-divisao-id');
                const isActive = (String(bId) === String(divId));
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            // Atualiza o Banner da Divisão (se existir)
            atualizarHeroBannerDivisao(divId);

            // Filtra os capítulos da trilha
            const caps = document.querySelectorAll('.capitulo-container');
            caps.forEach(cap => {
                const capDiv = cap.getAttribute('data-divisao');
                if (divId === 'todas' || String(capDiv) === String(divId)) {
                    cap.style.display = 'flex';
                } else {
                    cap.style.display = 'none';
                }
            });

            // Filtra os cartões de marco da divisão
            const milestones = document.querySelectorAll('.division-milestone-card');
            milestones.forEach(m => {
                const mDiv = m.getAttribute('data-divisao');
                if (divId === 'todas' || String(mDiv) === String(divId)) {
                    m.style.display = 'flex';
                } else {
                    m.style.display = 'none';
                }
            });

            // Atualiza destaque no mini widget de progresso
            document.querySelectorAll('.prog-div-item').forEach((item, idx) => {
                item.classList.toggle('atual', (idx + 1) === Number(divId));
            });

            // Redesenha caminhos SVG de conexão entre módulos visíveis
            requestAnimationFrame(() => {
                if (typeof renderizarTrilhasConexao === 'function') {
                    renderizarTrilhasConexao();
                }
            });
        }

        function atualizarHeroBannerDivisao(divId) {
            const heroIcon = document.getElementById('divHeroIcon');
            const heroIconTag = document.getElementById('divHeroIconTag');
            const heroPill = document.getElementById('divHeroPill');
            const heroNivel = document.getElementById('divHeroNivel');
            const heroRange = document.getElementById('divHeroRange');
            const heroTitulo = document.getElementById('divHeroTitulo');
            const heroDesc = document.getElementById('divHeroDesc');
            const heroProgressFill = document.getElementById('divHeroProgressFill');
            const heroProgressTxt = document.getElementById('divHeroProgressTxt');

            const dados = window.OPUS_DIVISOES_DADOS || {};

            if (divId === 'todas') {
                if (heroIcon) heroIcon.style.background = '#1cb0f6';
                if (heroIconTag) heroIconTag.className = 'fa-solid fa-globe';
                if (heroPill) heroPill.innerText = 'TRILHA COMPLETA';
                if (heroNivel) heroNivel.innerText = 'Geral';
                if (heroRange) heroRange.innerText = 'Seções 1 a 40';
                if (heroTitulo) heroTitulo.innerText = 'Jornada Completa Java Master';
                if (heroDesc) heroDesc.innerText = 'Visualizando todas as 40 seções integradas do curso (Fundamentos, Coleções e Orientação a Objetos).';

                let totalConc = 0;
                Object.values(dados).forEach(d => totalConc += (d.concluidas || 0));
                let pct = Math.round((totalConc / 40) * 100);

                if (heroProgressFill) {
                    heroProgressFill.style.width = pct + '%';
                    heroProgressFill.style.background = 'linear-gradient(90deg, #1cb0f6, #ce82ff, #ffc800)';
                }
                if (heroProgressTxt) {
                    heroProgressTxt.innerText = `${totalConc} de 40 seções concluídas (${pct}%)`;
                }
                return;
            }

            const d = dados[divId];
            if (!d) return;

            if (heroIcon) heroIcon.style.background = d.cor;
            if (heroIconTag) heroIconTag.className = 'fa-solid ' + d.icone;
            if (heroPill) heroPill.innerText = 'DIVISÃO ' + d.id;
            if (heroNivel) heroNivel.innerText = d.badge;
            if (heroRange) heroRange.innerText = `Seções ${d.secao_inicio} a ${d.secao_fim}`;
            if (heroTitulo) heroTitulo.innerText = d.tema;
            if (heroDesc) heroDesc.innerText = d.descricao;

            if (heroProgressFill) {
                heroProgressFill.style.width = d.porcentagem + '%';
                heroProgressFill.style.background = d.cor;
            }
            if (heroProgressTxt) {
                heroProgressTxt.innerText = `${d.concluidas} de ${d.total_secoes} seções concluídas (${d.porcentagem}%)`;
            }
        }

        window.selecionarDivisao = selecionarDivisao;

        // ── Scroll para a lição atual e Botão Flutuante de Seta ───────────
        function rolarAteLicaoAtual(tocarSom = false) {
            if (tocarSom && window.OpusAudio) window.OpusAudio.playClick();
            
            // Se a lição atual estiver em uma divisão oculta, muda automaticamente para a divisão dela
            const licaoAlvo = document.getElementById('currentLessonNode');
            if (licaoAlvo) {
                const capPai = licaoAlvo.closest('.capitulo-container');
                if (capPai && capPai.style.display === 'none') {
                    const divAlvo = capPai.getAttribute('data-divisao');
                    if (divAlvo) {
                        selecionarDivisao(divAlvo, false);
                    }
                }
            }

            setTimeout(() => {
                const licaoAtual = document.getElementById('currentLessonNode') 
                                || document.querySelector('.modulo-node.current')
                                || document.querySelector('.capitulo-container.current-chapter')
                                || document.querySelector('.modulo-node.completed');
                
                if (licaoAtual && licaoAtual.offsetParent !== null) {
                    licaoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Animação de pulso para evidenciar onde o jogador está
                    licaoAtual.classList.add('pulse-highlight');
                    setTimeout(() => licaoAtual.classList.remove('pulse-highlight'), 2200);
                } else {
                    const primeiroCapVisivel = Array.from(document.querySelectorAll('.capitulo-container'))
                        .find(c => c.style.display !== 'none');
                    if (primeiroCapVisivel) {
                        primeiroCapVisivel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }, 120);
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

        // ── TRILHA VISUAL CONECTANDO OS MÓDULOS (ESTILO DUOLINGO / CANVA) ──
        function getElementCenterStatic(targetEl, containerEl) {
            if (!targetEl || !containerEl) return { x: 0, y: 0 };
            
            const tRect = targetEl.getBoundingClientRect();
            const cRect = containerEl.getBoundingClientRect();
            
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

            const x = (tRect.left + tRect.right) / 2 - cRect.left;
            const y = ((tRect.top + tRect.bottom) / 2 - cRect.top) - animOffsetY;
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

                // Conexão 1: Do cabeçalho da seção atual até o primeiro módulo (Módulo 1)
                let headerToFirstD = '';
                const currHeader = capitulo ? capitulo.querySelector('.capitulo-header') : null;
                if (currHeader && milestones.length > 0) {
                    const chRect = currHeader.getBoundingClientRect();
                    const tRect = trail.getBoundingClientRect();
                    const hX = (chRect.left + chRect.right) / 2 - tRect.left;
                    const hY = chRect.bottom - tRect.top - 8;
                    const firstM = milestones[0];
                    const dyH = firstM.pos.y - hY;
                    if (dyH > 0) {
                        headerToFirstD = `M ${hX.toFixed(1)} ${hY.toFixed(1)} C ${hX.toFixed(1)} ${(hY + dyH * 0.5).toFixed(1)}, ${firstM.pos.x.toFixed(1)} ${(firstM.pos.y - dyH * 0.5).toFixed(1)}, ${firstM.pos.x.toFixed(1)} ${firstM.pos.y.toFixed(1)}`;
                    }
                }

                // Constrói os caminhos Bezier perfeitamente simétricos entre os marcos
                const segments = [];
                let fullPathD = '';

                for (let i = 0; i < milestones.length - 1; i++) {
                    const p1 = milestones[i].pos;
                    const p2 = milestones[i + 1].pos;
                    const dy = p2.y - p1.y;

                    // Curva S natural e simétrica entre os pontos alternados
                    const cp1x = p1.x;
                    const cp1y = p1.y + dy * 0.5;
                    const cp2x = p2.x;
                    const cp2y = p2.y - dy * 0.5;

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
                            // Trecho até a lição atual: azulzinho sólido (ou cor da seção), sem animação tracejada
                            segState = 'completed';
                        } else if (mCurr.status === 'completed' && mNext.status === 'locked' && i === 0) {
                            segState = 'completed';
                        } else {
                            segState = 'future';
                        }
                    }

                    segments.push({ d: segmentD, state: segState });
                }

                // Conexão 2: Do último módulo (Módulo 5) diretamente ao cabeçalho da PRÓXIMA seção
                let lastToNextD = '';
                if (capitulo && milestones.length > 0) {
                    let nextCap = capitulo.nextElementSibling;
                    while (nextCap && (nextCap.style.display === 'none' || !nextCap.classList.contains('capitulo-container'))) {
                        if (nextCap.classList.contains('division-milestone-card')) {
                            nextCap = null;
                            break;
                        }
                        nextCap = nextCap.nextElementSibling;
                    }
                    if (nextCap && nextCap.classList.contains('capitulo-container') && nextCap.style.display !== 'none') {
                        const nextHeader = nextCap.querySelector('.capitulo-header');
                        if (nextHeader) {
                            const nhRect = nextHeader.getBoundingClientRect();
                            const tRect = trail.getBoundingClientRect();
                            const nextX = (nhRect.left + nhRect.right) / 2 - tRect.left;
                            const nextY = nhRect.top - tRect.top + 16;
                            const lastM = milestones[milestones.length - 1];
                            const dyNext = nextY - lastM.pos.y;
                            if (dyNext > 0) {
                                lastToNextD = `M ${lastM.pos.x.toFixed(1)} ${lastM.pos.y.toFixed(1)} C ${lastM.pos.x.toFixed(1)} ${(lastM.pos.y + dyNext * 0.5).toFixed(1)}, ${nextX.toFixed(1)} ${(nextY - dyNext * 0.5).toFixed(1)}, ${nextX.toFixed(1)} ${nextY.toFixed(1)}`;
                            }
                        }
                    }
                }

                // Monta o SVG limpo, simétrico e contínuo
                let html = '';

                // 1. Sombras 3D de profundidade
                if (headerToFirstD) {
                    html += `<path class="trail-path-shadow" d="${headerToFirstD}"></path>`;
                }
                if (fullPathD) {
                    html += `<path class="trail-path-shadow" d="${fullPathD}"></path>`;
                }
                if (lastToNextD) {
                    html += `<path class="trail-path-shadow" d="${lastToNextD}"></path>`;
                }

                // 2. Trilha base conectando os módulos (cinza clarinho elegante)
                const baseClass = (capStatus === 'trancado') ? 'trail-path-locked' : 'trail-path-base';
                if (headerToFirstD) {
                    html += `<path class="${baseClass}" d="${headerToFirstD}"></path>`;
                }
                if (fullPathD) {
                    html += `<path class="${baseClass}" d="${fullPathD}"></path>`;
                }
                if (lastToNextD) {
                    html += `<path class="${baseClass}" d="${lastToNextD}"></path>`;
                }

                // 3. Segmentos concluídos na cor específica da seção (azul na seção azul, rosa na seção rosa, etc.)
                // Conector do cabeçalho ao módulo 1
                if (headerToFirstD && (capStatus === 'completo' || (milestones[0] && (milestones[0].status === 'completed' || milestones[0].status === 'current')))) {
                    html += `<path class="trail-path-completed" d="${headerToFirstD}" style="stroke: ${unitColor}; color: ${unitColor};"></path>`;
                }

                // Segmentos entre os módulos
                segments.forEach(seg => {
                    if (seg.state === 'completed') {
                        html += `<path class="trail-path-completed" d="${seg.d}" style="stroke: ${unitColor}; color: ${unitColor};"></path>`;
                    }
                });

                // Conector da última lição ao cabeçalho da próxima seção (colorido se a seção atual estiver completa)
                if (lastToNextD && capStatus === 'completo') {
                    html += `<path class="trail-path-completed" d="${lastToNextD}" style="stroke: ${unitColor}; color: ${unitColor};"></path>`;
                }

                svg.innerHTML = html;
            });
        }

        // Aguarda o carregamento completo para calcular posições e desenhar trilhas
        window.addEventListener('load', function () {
            try {
                const divSalva = localStorage.getItem('opus_divisao_ativa');
                if (divSalva && (divSalva === '1' || divSalva === '2' || divSalva === '3' || divSalva === 'todas')) {
                    selecionarDivisao(divSalva, false);
                }
            } catch (e) {}

            setTimeout(rolarAteLicaoAtual, 150);
            setTimeout(monitorarVisibilidadeLicaoAtual, 300);
            inicializarMascotesDashboard();
            setTimeout(renderizarTrilhasConexao, 60);
            setTimeout(renderizarTrilhasConexao, 400); // Garante após webfonts e layout final
        });

        document.addEventListener('DOMContentLoaded', function () {
            renderizarTrilhasConexao();
        });

        // Atualização reativa com redimensionamento de tela
        let resizeTimerTrail;
        window.addEventListener('resize', function () {
            cancelAnimationFrame(resizeTimerTrail);
            resizeTimerTrail = requestAnimationFrame(renderizarTrilhasConexao);
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
            } catch (e) {}
        })();


