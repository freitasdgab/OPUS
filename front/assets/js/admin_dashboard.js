// admin_dashboard.js - logica do painel administrativo
        // ------------------------------------------------------------------
        // ESTADO GLOBAL
        // ------------------------------------------------------------------
        let chartInstances = {};
        let currentPage = 1;
        let searchTimer = null;

        const LIGAS_INFO = {
            'bronze':   { nome: 'Bronze',   cor: '#cd7f32', corClara: '#cd7f32' },
            'prata':    { nome: 'Prata',    cor: '#8e8e99', corClara: '#c0c0c0' },
            'ouro':     { nome: 'Ouro',     cor: '#d4a017', corClara: '#ffd700' },
            'diamante': { nome: 'Diamante', cor: '#1ec8e0', corClara: '#5be7ff' },
            'mestre':   { nome: 'Mestre',   cor: '#8b2fd9', corClara: '#c47bff' },
        };

        // ------------------------------------------------------------------
        // FEEDBACK TOAST
        // ------------------------------------------------------------------
        function showToast(msg, tipo = 'info') {
            const wrap = document.getElementById('toast-wrapper');
            const toast = document.createElement('div');
            toast.className = `toast-msg ${tipo}`;
            
            let icon = 'fa-info-circle';
            if (tipo === 'success') icon = 'fa-circle-check';
            if (tipo === 'error') icon = 'fa-circle-exclamation';

            toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${msg}</span>`;
            wrap.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // ------------------------------------------------------------------
        // CARREGAR ESTATÍSTICAS DA API
        // ------------------------------------------------------------------
        async function carregarEstatisticas() {
            try {
                const res = await fetch('../../back/api_admin.php?action=stats');
                const data = await res.json();

                if (!data.sucesso) {
                    showToast(data.mensagem || 'Erro ao obter estatísticas.', 'error');
                    return;
                }

                const s = data.data;

                // 1. Destaques Ampliados (Semana e Próxima Virada)
                if (s.ligas_info) {
                    document.getElementById('destaque-semana').innerText = s.ligas_info.semana_atual || '--/--/----';
                    document.getElementById('destaque-proxima-virada').innerText = s.ligas_info.proxima_virada || '--/--/----';
                }

                // 2. KPIs
                document.getElementById('kpi-total-usuarios').innerText = Number(s.total_usuarios).toLocaleString('pt-BR');
                document.getElementById('kpi-ofensiva-ativa').innerText = Number(s.ofensivas.com_fogo).toLocaleString('pt-BR');
                document.getElementById('kpi-max-ofensiva').innerText = s.ofensivas.max_fogo;
                document.getElementById('kpi-vidas-cheias').innerText = Number(s.vidas.cheias_3).toLocaleString('pt-BR');
                document.getElementById('kpi-vidas-zeradas').innerText = s.vidas.zeradas_0;

                // 3. Cards de Divisões
                renderizarCardsDivisoes(s.divisoes);

                // 4. Gráficos
                renderizarGraficos(s);

            } catch (err) {
                console.error("Erro ao carregar estatísticas:", err);
                showToast('Falha na conexão com o servidor.', 'error');
            }
        }

        function renderizarCardsDivisoes(divisoes) {
            const container = document.getElementById('ligas-grid-container');
            container.innerHTML = '';

            Object.keys(divisoes).forEach(slug => {
                const div = divisoes[slug];
                const card = document.createElement('div');
                card.className = 'liga-card-item';
                card.style.borderLeft = `4px solid ${div.corClara}`;

                card.innerHTML = `
                    <div class="liga-icon-badge" style="background: ${div.cor};">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="liga-card-info">
                        <h4 style="color: ${div.corClara};">${div.nome}</h4>
                        <div class="count">${Number(div.quantidade).toLocaleString('pt-BR')}</div>
                        <div class="pct">${div.porcentagem}% dos alunos</div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        // ------------------------------------------------------------------
        // GRÁFICOS (CHART.JS)
        // ------------------------------------------------------------------
        function renderizarGraficos(data) {
            Object.values(chartInstances).forEach(inst => {
                if (inst && typeof inst.destroy === 'function') inst.destroy();
            });

            Chart.defaults.color = '#8e95a1';
            Chart.defaults.font.family = "'Poppins', sans-serif";

            // 1. GRÁFICO DE LIGAS (DONUT)
            const ctxLigas = document.getElementById('chartLigas').getContext('2d');
            const divKeys = Object.keys(data.divisoes);
            chartInstances.ligas = new Chart(ctxLigas, {
                type: 'doughnut',
                data: {
                    labels: divKeys.map(k => data.divisoes[k].nome),
                    datasets: [{
                        data: divKeys.map(k => data.divisoes[k].quantidade),
                        backgroundColor: divKeys.map(k => data.divisoes[k].corClara),
                        borderWidth: 2,
                        borderColor: '#12141c',
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { weight: '600' } } }
                    },
                    cutout: '65%'
                }
            });

            // 2. GRÁFICO DE CONCLUSÃO DE CAPÍTULOS (BARRAS)
            const ctxCapitulos = document.getElementById('chartCapitulos').getContext('2d');
            const caps = data.progresso_capitulos || [];
            chartInstances.capitulos = new Chart(ctxCapitulos, {
                type: 'bar',
                data: {
                    labels: caps.map(c => c.nome),
                    datasets: [{
                        label: 'Alunos Concluíram',
                        data: caps.map(c => c.concluidos),
                        backgroundColor: 'rgba(34, 197, 94, 0.75)',
                        borderColor: '#22c55e',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // 3. GRÁFICO DE STATUS DE VIDAS (BARRA HORIZONTAL)
            const ctxVidas = document.getElementById('chartStatusVidas').getContext('2d');
            chartInstances.vidas = new Chart(ctxVidas, {
                type: 'bar',
                data: {
                    labels: ['3 Vidas (Cheias)', '2 Vidas', '1 Vida', '0 Vidas (Zeradas)'],
                    datasets: [{
                        data: [
                            data.vidas.cheias_3 || 0,
                            data.vidas.medias_2 || 0,
                            data.vidas.baixas_1 || 0,
                            data.vidas.zeradas_0 || 0
                        ],
                        backgroundColor: ['#22c55e', '#eab308', '#f97316', '#ef4444'],
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        // ------------------------------------------------------------------
        // GERENCIAR USUÁRIOS (TABELA COM BUSCA POR EMAIL E ID ASC/DESC)
        // ------------------------------------------------------------------
        async function carregarUsuarios(page = 1) {
            currentPage = page;
            const q = document.getElementById('input-busca-email').value.trim();
            const divisao = document.getElementById('select-divisao').value;
            const orderDir = document.getElementById('select-ordenacao-id').value;

            const tbody = document.getElementById('admin-table-body');
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align:center; padding: 40px; color: var(--text-muted);">
                        <i class="fa-solid fa-spinner fa-spin" style="font-size: 22px; margin-bottom: 8px; display:block;"></i>
                        Carregando usuários...
                    </td>
                </tr>
            `;

            try {
                const params = new URLSearchParams({
                    action: 'users',
                    page: page,
                    limit: 15,
                    q: q,
                    divisao: divisao,
                    order_dir: orderDir
                });

                const res = await fetch(`../../back/api_admin.php?${params.toString()}`);
                const data = await res.json();

                if (!data.sucesso) {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 30px; color: #ef4444;">${data.mensagem}</td></tr>`;
                    return;
                }

                renderizarTabela(data.data);
            } catch (err) {
                console.error("Erro ao carregar lista de usuários:", err);
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 30px; color: #ef4444;">Erro ao carregar dados.</td></tr>`;
            }
        }

        function renderizarTabela(data) {
            const tbody = document.getElementById('admin-table-body');
            const lista = data.usuarios || [];

            if (lista.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 40px; color: var(--text-muted);">Nenhum usuário encontrado.</td></tr>`;
                document.getElementById('page-info-text').innerText = 'Mostrando 0 de 0 usuários';
                document.getElementById('page-nav-btns').innerHTML = '';
                return;
            }

            tbody.innerHTML = '';
            lista.forEach(u => {
                const tr = document.createElement('tr');
                
                let fotoUrl = u.foto_perfil && u.foto_perfil.startsWith('data:image') ? u.foto_perfil : '../assets/img/opi pulando feliz.png';
                const divSlug = (u.divisao || 'bronze').toLowerCase();
                const divNome = LIGAS_INFO[divSlug] ? LIGAS_INFO[divSlug].nome : 'Bronze';

                let coracoes = '';
                for (let i = 1; i <= 3; i++) {
                    coracoes += `<i class="fa-solid fa-heart ${i <= u.vidas ? '' : 'empty'}"></i>`;
                }

                tr.innerHTML = `
                    <td style="font-family: 'Orbitron', sans-serif; font-weight: bold; color: var(--text-muted); font-size: 0.8rem;">#${u.id}</td>
                    <td>
                        <div class="user-avatar-cell">
                            <img src="${fotoUrl}" alt="Avatar" class="user-avatar-img">
                            <div class="user-meta">
                                <div class="name">${escapeHtml(u.nome)}</div>
                                <div class="email">${escapeHtml(u.email)}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="div-badge badge-${divSlug}">
                            <i class="fa-solid fa-shield"></i> ${divNome}
                        </span>
                    </td>
                    <td>
                        <div class="hearts-box" title="${u.vidas} de 3 vidas">
                            ${coracoes}
                        </div>
                    </td>
                    <td style="font-family: 'Orbitron', sans-serif; font-weight: bold; color: #f97316;">
                        <i class="fa-solid fa-fire"></i> ${u.dias_fogo}d
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">${u.criado_em}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn-action-sm" onclick="abrirDetalhesAluno(${u.id})" title="Ver Detalhes do Aluno">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="btn-action-sm btn-heart" onclick="restaurarVidas(${u.id}, '${escapeHtml(u.nome)}')" title="Restaurar 3 Vidas">
                                <i class="fa-solid fa-heart-circle-plus"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Paginação
            const total = data.total || 0;
            const pagAtual = data.pagina_atual || 1;
            const totalPags = data.total_paginas || 1;
            const limite = data.limite || 15;
            const de = (pagAtual - 1) * limite + 1;
            const ate = Math.min(total, pagAtual * limite);

            document.getElementById('page-info-text').innerText = `Mostrando ${de} - ${ate} de ${total} usuários`;

            const navContainer = document.getElementById('page-nav-btns');
            navContainer.innerHTML = '';

            const btnPrev = document.createElement('button');
            btnPrev.className = 'page-btn';
            btnPrev.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
            btnPrev.disabled = (pagAtual <= 1);
            btnPrev.onclick = () => carregarUsuarios(pagAtual - 1);
            navContainer.appendChild(btnPrev);

            for (let p = 1; p <= totalPags; p++) {
                if (p === 1 || p === totalPags || (p >= pagAtual - 1 && p <= pagAtual + 1)) {
                    const btnP = document.createElement('button');
                    btnP.className = `page-btn ${p === pagAtual ? 'active' : ''}`;
                    btnP.innerText = p;
                    btnP.onclick = () => carregarUsuarios(p);
                    navContainer.appendChild(btnP);
                } else if (p === pagAtual - 2 || p === pagAtual + 2) {
                    const span = document.createElement('span');
                    span.style.padding = '0 4px';
                    span.style.color = '#555e6d';
                    span.innerText = '...';
                    navContainer.appendChild(span);
                }
            }

            const btnNext = document.createElement('button');
            btnNext.className = 'page-btn';
            btnNext.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
            btnNext.disabled = (pagAtual >= totalPags);
            btnNext.onclick = () => carregarUsuarios(pagAtual + 1);
            navContainer.appendChild(btnNext);
        }

        // ------------------------------------------------------------------
        // AÇÕES (VIRADA DE LIGAS E RESTAURAR VIDAS)
        // ------------------------------------------------------------------
        async function dispararViradaLigas() {
            if (!confirm("Deseja realmente processar a Virada Semanal de Ligas agora?\n\nAs subidas e descidas de divisão serão calculadas e aplicadas imediatamente.")) {
                return;
            }

            const btn = document.getElementById('btn-virada-ligas');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processando...';

            try {
                const formData = new FormData();
                formData.append('action', 'trigger_league_turnover');

                const res = await fetch('../../back/api_admin.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.sucesso) {
                    showToast(data.mensagem, 'success');
                    carregarEstatisticas();
                    carregarUsuarios(currentPage);
                } else {
                    showToast(data.mensagem || 'Erro ao processar virada.', 'error');
                }
            } catch (err) {
                console.error("Erro:", err);
                showToast('Erro ao executar virada de ligas.', 'error');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-bolt"></i> Atualizar Virada de Liga';
            }
        }

        async function restaurarVidas(userId, userName) {
            try {
                const formData = new FormData();
                formData.append('action', 'update_lives');
                formData.append('user_id', userId);
                formData.append('vidas', 3);

                const res = await fetch('../../back/api_admin.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (data.sucesso) {
                    showToast(`3 Vidas restauradas para "${userName}" com sucesso!`, 'success');
                    carregarEstatisticas();
                    carregarUsuarios(currentPage);
                } else {
                    showToast(data.mensagem || 'Erro ao restaurar vidas.', 'error');
                }
            } catch (err) {
                console.error("Erro:", err);
                showToast('Erro ao comunicar com o servidor.', 'error');
            }
        }

        // ------------------------------------------------------------------
        // MODAL DE DETALHES DO ALUNO
        // ------------------------------------------------------------------
        async function abrirDetalhesAluno(userId) {
            const modal = document.getElementById('aluno-modal');
            const box = document.getElementById('aluno-modal-content');

            box.innerHTML = `
                <div style="text-align:center; padding: 40px; color: var(--text-muted);">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 26px; margin-bottom: 10px; display:block;"></i>
                    Carregando detalhes do aluno...
                </div>
            `;
            modal.classList.add('active');

            try {
                const res = await fetch(`../../back/api_admin.php?action=user_details&user_id=${userId}`);
                const data = await res.json();

                if (!data.sucesso) {
                    box.innerHTML = `<p style="color: #ef4444;">${data.mensagem}</p>`;
                    return;
                }

                const u = data.data.usuario;
                const prog = data.data.progresso || [];
                const hist = data.data.historico_ligas || [];

                let foto = u.foto_perfil && u.foto_perfil.startsWith('data:image') ? u.foto_perfil : '../assets/img/opi pulando feliz.png';
                const divSlug = (u.divisao || 'bronze').toLowerCase();
                const divNome = LIGAS_INFO[divSlug] ? LIGAS_INFO[divSlug].nome : 'Bronze';

                let progressoHtml = '';
                if (prog.length > 0) {
                    prog.forEach(p => {
                        const statusColor = p.status === 'completo' ? '#22c55e' : (p.status === 'corrente' ? '#1cb0f6' : '#555e6d');
                        const statusLabel = p.status === 'completo' ? 'Concluído' : (p.status === 'corrente' ? 'Em Andamento' : 'Trancado');
                        progressoHtml += `
                            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.03); padding: 11px 14px; border-radius: 10px; margin-bottom: 6px;">
                                <span><strong>Capítulo ${p.unidade_numero}</strong> (${p.licoes_concluidas} lições concluídas)</span>
                                <span style="color: ${statusColor}; font-weight: bold; font-size: 0.8rem; text-transform: uppercase;">${statusLabel}</span>
                            </div>
                        `;
                    });
                } else {
                    progressoHtml = '<p style="color: var(--text-muted); font-size: 0.85rem;">Nenhum progresso registrado.</p>';
                }

                let historicoHtml = '';
                if (hist.length > 0) {
                    hist.forEach(h => {
                        historicoHtml += `
                            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(255,255,255,0.02); padding: 8px 12px; border-radius: 8px; margin-bottom: 5px; font-size: 0.82rem;">
                                <span>Semana: <strong>${h.semana_ref}</strong> (${h.divisao_anterior} → ${h.divisao_nova})</span>
                                <span style="font-weight: bold; color: ${h.resultado === 'subiu' ? '#22c55e' : (h.resultado === 'desceu' ? '#ef4444' : '#c0c0c0')}; text-transform: uppercase;">${h.resultado}</span>
                            </div>
                        `;
                    });
                } else {
                    historicoHtml = '<p style="color: var(--text-muted); font-size: 0.85rem;">Sem histórico de viradas ainda.</p>';
                }

                box.innerHTML = `
                    <div style="display:flex; align-items:center; gap: 16px; margin-bottom: 22px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 18px;">
                        <img src="${foto}" style="width: 65px; height: 65px; border-radius: 50%; border: 3px solid var(--accent-blue); object-fit: cover;">
                        <div>
                            <h2 style="margin:0; font-size: 1.35rem; font-family:'Orbitron', sans-serif;">${escapeHtml(u.nome)}</h2>
                            <p style="margin:2px 0 0 0; color: var(--text-muted); font-size: 0.85rem;">${escapeHtml(u.email)} • ID #${u.id}</p>
                            <div style="margin-top: 6px;">
                                <span class="div-badge badge-${divSlug}"><i class="fa-solid fa-shield"></i> Liga ${divNome}</span>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 22px;">
                        <div style="background:rgba(255,255,255,0.03); padding:12px; border-radius:12px; text-align:center;">
                            <div style="color:var(--text-muted); font-size:0.75rem;">Status de Vidas</div>
                            <div style="font-family:'Orbitron',sans-serif; font-size:1.15rem; font-weight:bold; color:#ef4444;">${u.vidas} / 3</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.03); padding:12px; border-radius:12px; text-align:center;">
                            <div style="color:var(--text-muted); font-size:0.75rem;">Dias de Ofensiva</div>
                            <div style="font-family:'Orbitron',sans-serif; font-size:1.15rem; font-weight:bold; color:#f97316;">${u.dias_fogo} dias</div>
                        </div>
                    </div>

                    <h4 style="font-family:'Orbitron',sans-serif; margin-bottom:10px; font-size:0.92rem;"><i class="fa-solid fa-book-open" style="color:var(--accent-blue)"></i> Conclusão de Capítulos</h4>
                    <div style="margin-bottom: 20px;">${progressoHtml}</div>

                    <h4 style="font-family:'Orbitron',sans-serif; margin-bottom:10px; font-size:0.92rem;"><i class="fa-solid fa-clock-rotate-left" style="color:#c47bff"></i> Histórico de Ligas</h4>
                    <div>${historicoHtml}</div>
                `;
            } catch (err) {
                console.error("Erro ao carregar detalhes:", err);
                box.innerHTML = `<p style="color: #ef4444;">Erro ao carregar detalhes do aluno.</p>`;
            }
        }

        function fecharModalAluno() {
            document.getElementById('aluno-modal').classList.remove('active');
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        // ------------------------------------------------------------------
        // INICIALIZAÇÃO
        // ------------------------------------------------------------------
        document.addEventListener('DOMContentLoaded', () => {
            carregarEstatisticas();
            carregarUsuarios(1);

            // Botão de Virada de Ligas
            document.getElementById('btn-virada-ligas').addEventListener('click', dispararViradaLigas);

            // Busca por E-mail com debounce
            document.getElementById('input-busca-email').addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => carregarUsuarios(1), 350);
            });

            // Filtro de Divisão
            document.getElementById('select-divisao').addEventListener('change', () => carregarUsuarios(1));

            // Ordenação por ID (ASC / DESC)
            document.getElementById('select-ordenacao-id').addEventListener('change', () => carregarUsuarios(1));

            // Fechar modal ao clicar fora
            document.getElementById('aluno-modal').addEventListener('click', (e) => {
                if (e.target.id === 'aluno-modal') fecharModalAluno();
            });
        });
