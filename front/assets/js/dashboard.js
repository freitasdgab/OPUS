// dashboard.js - logica da pagina Dashboard
        let capAtualBau = 0;

        function abrirModalBau(cap, titulo) {
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
            const modal = document.getElementById('modalBauRecompensa');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function fecharAvisoBau() {
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

                // Exibe resultados
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
        function rolarAteLicaoAtual() {
            const licaoAtual = document.getElementById('currentLessonNode') 
                            || document.querySelector('.modulo-node.current')
                            || document.querySelector('.capitulo-container.current-chapter')
                            || document.querySelector('.modulo-node.completed');
            
            if (licaoAtual) {
                licaoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Animação de pulso para evidenciar onde o jogador está
                licaoAtual.classList.add('pulse-highlight');
                setTimeout(() => licaoAtual.classList.remove('pulse-highlight'), 2200);

                if (typeof opusToast === 'function') {
                    opusToast('Lição atual localizada na trilha!', 'info', 2000);
                }
            } else {
                const primeiroCap = document.querySelector('.capitulo-container');
                if (primeiroCap) {
                    primeiroCap.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        // Monitora o scroll para diminuir a opacidade do botão quando o usuário já estiver olhando para a lição
        function monitorarVisibilidadeLicaoAtual() {
            const btn = document.getElementById('btnScrollToLesson');
            const alvo = document.getElementById('currentLessonNode') || document.querySelector('.modulo-node.current');
            const container = document.querySelector('.main-content');
            if (!btn || !alvo || !container) return;

            const checar = () => {
                const rect = alvo.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const visivel = (rect.top >= containerRect.top + 60 && rect.bottom <= containerRect.bottom - 60);
                
                if (visivel) {
                    btn.style.opacity = '0.35';
                    btn.style.transform = 'scale(0.92)';
                } else {
                    btn.style.opacity = '1';
                    btn.style.transform = 'scale(1)';
                }
            };

            container.addEventListener('scroll', checar, { passive: true });
            checar();
        }

        // Aguarda o carregamento completo para calcular posições
        window.addEventListener('load', function () {
            setTimeout(rolarAteLicaoAtual, 150);
            setTimeout(monitorarVisibilidadeLicaoAtual, 300);
        });

