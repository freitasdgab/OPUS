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

        function avisoBauColetado(cap) {
            alert('Você já abriu e coletou a recompensa deste baú da Unidade ' + cap + '!');
        }

        function avisoBauTrancado(cap) {
            alert('🔒 Complete pelo menos 3 lições da Unidade ' + cap + ' para desbloquear e abrir este baú de recompensa!');
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
                    alert(data.mensagem || 'Não foi possível resgatar o baú.');
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
                alert('Erro de conexão ao abrir o baú.');
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

        // ── Scroll automático para a lição atual ──────────────────────────
        function rolarParaLicaoAtual() {
            // Tenta achar o nó exato da lição atual
            const licaoAtual = document.getElementById('currentLessonNode');
            if (licaoAtual) {
                licaoAtual.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            // Fallback: se não houver lição atual, rola até o capítulo corrente
            const capAtual = document.querySelector('.capitulo-container.current-chapter');
            if (capAtual) {
                capAtual.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Aguarda o carregamento completo (incluindo imagens/fontes) para que
        // o layout esteja 100% calculado antes de rolar.
        window.addEventListener('load', function () {
            // Pequeno delay extra garante que fontes externas (Google Fonts)
            // não causem refluxo de layout após o scroll.
            setTimeout(rolarParaLicaoAtual, 150);
        });
