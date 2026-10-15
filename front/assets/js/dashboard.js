// dashboard.js - logica da pagina Dashboard
        let capAtualBau = 0;

        function abrirModalBau(cap, titulo) {
            if (window.OpusAudio) window.OpusAudio.playClick();
            capAtualBau = cap;
            document.getElementById('modalBauTitle').innerText = 'Baú de Recompensas!';
            document.getElementById('modalBauSub').innerText = titulo + ' · Recompensa de Meio de Trilha';
            document.getElementById('rewardContainerPre').style.display = 'block';
            document.getElementById('rewardContainerPos').style.display = 'none';
            document.getElementById('btnResgatarBau').style.display = 'block';
            document.getElementById('btnResgatarBau').innerHTML = '<i class="fa-solid fa-box-open"></i> ABRIR BAÚ DE RECOMPENSA';
            document.getElementById('btnResgatarBau').disabled = false;
            document.getElementById('btnFecharBauPronto').style.display = 'none';
            
            const modal = document.getElementById('modalBauRecompensa');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function fecharModalBau() {
            if (window.OpusAudio) window.OpusAudio.playClick();
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

        function avisoBauColetado(cap) {
            const modal = document.getElementById('modalAvisoBau');
            if (!modal) return;
            document.getElementById('avisoBauIconBox').style.background = 'linear-gradient(135deg, #1cb0f6, #0088cc)';
            document.getElementById('avisoBauIconBox').style.borderBottomColor = '#0070a8';
            document.getElementById('avisoBauIcone').className = 'fa-solid fa-box-open';
            document.getElementById('avisoBauIcone').style.color = '#fff';
            document.getElementById('avisoBauTitulo').innerText = 'Baú Já Coletado!';
            document.getElementById('avisoBauTexto').innerText = 'Você já resgatou as recompensas de XP e vida deste baú da Unidade ' + cap + '!';
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function avisoBauTrancado(cap) {
            const modal = document.getElementById('modalAvisoBau');
            if (!modal) return;
            document.getElementById('avisoBauIconBox').style.background = 'linear-gradient(135deg, #ff9600, #e67e00)';
            document.getElementById('avisoBauIconBox').style.borderBottomColor = '#b36200';
            document.getElementById('avisoBauIcone').className = 'fa-solid fa-lock';
            document.getElementById('avisoBauIcone').style.color = '#fff';
            document.getElementById('avisoBauTitulo').innerText = 'Baú Trancado!';
            document.getElementById('avisoBauTexto').innerText = 'Complete pelo menos 3 lições da Unidade ' + cap + ' para desbloquear e abrir este baú de recompensa!';
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function resgatarRecompensaBau() {
            const btn = document.getElementById('btnResgatarBau');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Abrindo Baú...';

            fetch('../../back/resgatar_bau.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'unidade_numero=' + encodeURIComponent(capAtualBau)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    if (typeof opusToast === 'function') {
                        opusToast(data.mensagem || 'Não foi possível resgatar o baú.', 'error');
                    } else {
                        alert(data.mensagem || 'Não foi possível resgatar o baú.');
                    }
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> TENTAR NOVAMENTE';
                    return;
                }

                // Exibe resultados com efeito sonoro mágico
                if (window.OpusAudio) {
                    window.OpusAudio.playChest();
                }
                document.getElementById('rewardContainerPre').style.display = 'none';
                document.getElementById('rewardContainerPos').style.display = 'block';
                document.getElementById('resgateXpVal').innerText = '+' + data.xp_ganho + ' XP';
                
                if (data.vidas_ganhas > 0) {
                    document.getElementById('resgateVidaVal').innerText = '+1 Coração';
                    document.getElementById('resgateVidaLbl').innerText = 'Vida Recuperada';
                    document.getElementById('resgateVidaIcon').className = 'fa-solid fa-heart rc-icon';
                    document.getElementById('resgateVidaIcon').style.color = '#ef4444';
                } else {
                    document.getElementById('resgateVidaVal').innerText = 'XP Bônus Max';
                    document.getElementById('resgateVidaLbl').innerText = 'Vidas já cheias';
                    document.getElementById('resgateVidaIcon').className = 'fa-solid fa-star rc-icon';
                    document.getElementById('resgateVidaIcon').style.color = '#ffc800';
                }

                document.getElementById('resgateDetalheTxt').innerText = data.detalhe || data.mensagem;
                btn.style.display = 'none';
                document.getElementById('btnFecharBauPronto').style.display = 'block';

                // Atualiza o nó do baú na trilha
                const chestNode = document.getElementById('chestNode_' + capAtualBau);
                if (chestNode) {
                    chestNode.className = 'chest-node claimed';
                    chestNode.onclick = function() { avisoBauColetado(capAtualBau); };
                    chestNode.innerHTML = '<i class="fa-solid fa-box-open"></i><span class="chest-tag-done"><i class="fa-solid fa-check"></i> Coletado</span>';
                    const balloon = chestNode.parentElement.querySelector('.chest-balloon');
                    if (balloon) balloon.remove();
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

        // ── Scroll para a lição atual e Botão Flutuante de Seta ───────────
        function rolarAteLicaoAtual(tocarSom = false) {
            if (tocarSom && window.OpusAudio) window.OpusAudio.playClick();
            const licaoAtual = document.getElementById('currentLessonNode') 
                            || document.querySelector('.modulo-node.current')
                            || document.querySelector('.capitulo-container.current-chapter')
                            || document.querySelector('.modulo-node.completed');
            
            if (licaoAtual) {
                licaoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Animação de pulso para evidenciar onde o jogador está
                licaoAtual.classList.add('pulse-highlight');
                setTimeout(() => licaoAtual.classList.remove('pulse-highlight'), 2200);
            } else {
                const primeiroCap = document.querySelector('.capitulo-container');
                if (primeiroCap) {
                    primeiroCap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        // Monitora o scroll para orientar a direção da seta (cima ou baixo) e ajustar a opacidade quando visível
        function monitorarVisibilidadeLicaoAtual() {
            const btn = document.getElementById('btnScrollToLesson');
            const alvo = document.getElementById('currentLessonNode') 
                      || document.querySelector('.modulo-node.current')
                      || document.querySelector('.capitulo-container.current-chapter');
            const container = document.querySelector('.main-content');
            if (!btn || !alvo) return;

            const icone = btn.querySelector('i');

            const checar = () => {
                const rect = alvo.getBoundingClientRect();
                const containerRect = container ? container.getBoundingClientRect() : { top: 0, bottom: window.innerHeight };
                
                // Posição central do elemento alvo e do container de rolagem
                const alvoCentro = (rect.top + rect.bottom) / 2;
                const containerCentro = (containerRect.top + containerRect.bottom) / 2;
                
                // Verifica se a lição/capítulo está no campo de visão
                const visivel = (rect.top >= containerRect.top + 60 && rect.bottom <= containerRect.bottom - 60);

                if (icone) {
                    if (alvoCentro < containerCentro) {
                        // O capítulo/lição recomendado está acima: seta aponta para CIMA
                        if (!icone.classList.contains('fa-arrow-up')) {
                            icone.className = 'fa-solid fa-arrow-up';
                        }
                        btn.setAttribute('title', 'Rolar para cima até a lição atual');
                    } else {
                        // O capítulo/lição recomendado está abaixo: seta aponta para BAIXO
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
                    const nextCap = capitulo.nextElementSibling;
                    if (nextCap && nextCap.classList.contains('capitulo-container')) {
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


