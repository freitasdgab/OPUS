/**
 * ==========================================================================
 * OPUS - PAINEL ADMINISTRATIVO (ADMIN DASHBOARD)
 * Gestão de métricas globais, ligas, gráficos e inspeção completa de perfil.
 * ==========================================================================
 */

(function() {
    'use strict';

    // ── Estado Global ────────────────────────────────────────────────────────
    let chartInstances = {};
    let currentPage = 1;
    let searchTimer = null;

    const LIGAS_INFO = {
        'bronze':   { nome: 'Bronze',   cor: '#cd7f32', corClara: '#cd7f32', bg: 'rgba(205, 127, 50, 0.15)' },
        'prata':    { nome: 'Prata',    cor: '#8e8e99', corClara: '#c0c0c0', bg: 'rgba(192, 192, 192, 0.15)' },
        'ouro':     { nome: 'Ouro',     cor: '#d4a017', corClara: '#ffd700', bg: 'rgba(255, 215, 0, 0.15)' },
        'diamante': { nome: 'Diamante', cor: '#1ec8e0', corClara: '#5be7ff', bg: 'rgba(91, 231, 255, 0.15)' },
        'mestre':   { nome: 'Mestre',   cor: '#8b2fd9', corClara: '#c47bff', bg: 'rgba(196, 123, 255, 0.15)' },
    };

    const CAPITULOS_NOMES = {
        1: 'Fundamentos de Java',
        2: 'Estruturas de Controle',
        3: 'Orientação a Objetos',
        4: 'Estruturas de Dados',
        5: 'Java Avançado & Projeto'
    };

    // ── Feedback Toast / Alerta ──────────────────────────────────────────────
    function showToast(msg, tipo = 'info') {
        if (typeof window.opusToast === 'function') {
            window.opusToast(msg, tipo);
            return;
        }

        const wrap = document.getElementById('toast-wrapper');
        if (!wrap) return;
        const toast = document.createElement('div');
        toast.className = `toast-msg ${tipo}`;
        
        let icon = 'fa-circle-info';
        if (tipo === 'success') icon = 'fa-circle-check';
        if (tipo === 'error') icon = 'fa-triangle-exclamation';
        if (tipo === 'warning') icon = 'fa-triangle-exclamation';

        toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${escapeHtml(msg)}</span>`;
        wrap.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    // ── Carregar Estatísticas Gerais da API ───────────────────────────────────
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
            document.getElementById('kpi-total-usuarios').innerText = Number(s.total_usuarios || 0).toLocaleString('pt-BR');
            document.getElementById('kpi-ofensiva-ativa').innerText = Number(s.ofensivas.com_fogo || 0).toLocaleString('pt-BR');
            document.getElementById('kpi-max-ofensiva').innerText = (s.ofensivas.max_fogo || 0);
            document.getElementById('kpi-vidas-cheias').innerText = Number(s.vidas.cheias_3 || 0).toLocaleString('pt-BR');
            document.getElementById('kpi-vidas-zeradas').innerText = (s.vidas.zeradas_0 || 0);

            // 3. Cards de Divisões
            renderizarCardsDivisoes(s.divisoes);

            // 4. Gráficos
            renderizarGraficos(s);

        } catch (err) {
            console.error("Erro ao carregar estatísticas:", err);
            showToast('Falha na conexão com o servidor ao carregar estatísticas.', 'error');
        }
    }

    function renderizarCardsDivisoes(divisoes) {
        const container = document.getElementById('ligas-grid-container');
        if (!container || !divisoes) return;
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
                    <h4 style="color: ${div.corClara};">${escapeHtml(div.nome)}</h4>
                    <div class="count">${Number(div.quantidade).toLocaleString('pt-BR')}</div>
                    <div class="pct">${div.porcentagem}% dos alunos</div>
                </div>
            `;
            container.appendChild(card);
        });
    }

    // ── Renderização dos Gráficos (Chart.js) ──────────────────────────────────
    function renderizarGraficos(data) {
        Object.values(chartInstances).forEach(inst => {
            if (inst && typeof inst.destroy === 'function') inst.destroy();
        });

        Chart.defaults.color = '#8e95a1';
        Chart.defaults.font.family = "'Poppins', sans-serif";

        // 1. GRÁFICO DE LIGAS (DONUT)
        const ctxLigas = document.getElementById('chartLigas')?.getContext('2d');
        if (ctxLigas && data.divisoes) {
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
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14, font: { weight: '600' } } }
                    },
                    cutout: '68%'
                }
            });
        }

        // 2. GRÁFICO DE CONCLUSÃO DE CAPÍTULOS (BARRAS)
        const ctxCapitulos = document.getElementById('chartCapitulos')?.getContext('2d');
        if (ctxCapitulos) {
            const caps = data.progresso_capitulos || [];
            chartInstances.capitulos = new Chart(ctxCapitulos, {
                type: 'bar',
                data: {
                    labels: caps.map(c => c.nome),
                    datasets: [{
                        label: 'Alunos que Concluíram',
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
        }

        // 3. GRÁFICO DE STATUS DE VIDAS (BARRA HORIZONTAL)
        const ctxVidas = document.getElementById('chartStatusVidas')?.getContext('2d');
        if (ctxVidas && data.vidas) {
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
    }

    // ── Gerenciamento de Usuários (Tabela) ────────────────────────────────────
    async function carregarUsuarios(page = 1) {
        currentPage = page;
        const q = document.getElementById('input-busca-email')?.value.trim() || '';
        const divisao = document.getElementById('select-divisao')?.value || '';
        const orderDir = document.getElementById('select-ordenacao-id')?.value || 'ASC';

        const tbody = document.getElementById('admin-table-body');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align:center; padding: 40px; color: var(--text-muted);">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 22px; margin-bottom: 8px; display:block; color: var(--accent-blue);"></i>
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
                tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 30px; color: #ef4444;">${escapeHtml(data.mensagem)}</td></tr>`;
                return;
            }

            renderizarTabela(data.data);
        } catch (err) {
            console.error("Erro ao carregar lista de usuários:", err);
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 30px; color: #ef4444;">Erro ao carregar dados dos usuários.</td></tr>`;
        }
    }

    function renderizarTabela(data) {
        const tbody = document.getElementById('admin-table-body');
        const lista = data.usuarios || [];

        if (lista.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align:center; padding: 40px; color: var(--text-muted);">Nenhum usuário encontrado com os filtros aplicados.</td></tr>`;
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
                <td style="font-family: 'Orbitron', sans-serif; font-weight: bold; color: var(--accent-blue); font-size: 0.85rem;">#${u.id}</td>
                <td>
                    <div class="user-avatar-cell" onclick="abrirDetalhesAluno(${u.id})" style="cursor: pointer;" title="Clique para ver o perfil completo">
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
                <td style="color: var(--text-muted); font-size: 0.82rem;">${u.criado_em}</td>
                <td style="text-align: center;">
                    <button class="btn-view-profile" onclick="abrirDetalhesAluno(${u.id})" title="Visualizar Perfil Completo">
                        <i class="fa-solid fa-user"></i>
                        <span>Ver Perfil</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Paginação
        const total = data.total || 0;
        const pagAtual = data.pagina_atual || 1;
        const totalPags = data.total_paginas || 1;
        const limite = data.limite || 15;
        const de = total > 0 ? (pagAtual - 1) * limite + 1 : 0;
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

    // ── Disparo Manual da Virada de Ligas ────────────────────────────────────
    async function dispararViradaLigas() {
        if (typeof window.opusAlerta === 'function') {
            const confirmou = await window.opusAlerta({
                tipo: 'warning',
                titulo: 'Processar Virada Semanal',
                mensagem: 'Deseja realmente processar a Virada Semanal de Ligas agora?<br><br>As subidas e descidas de divisão serão recalculadas e aplicadas imediatamente.',
                botaoTexto: 'SIM, PROCESSAR AGORA',
                cancelTexto: 'CANCELAR'
            });
            if (!confirmou) return;
        } else {
            if (!confirm("Deseja realmente processar a Virada Semanal de Ligas agora?\n\nAs subidas e descidas de divisão serão calculadas e aplicadas imediatamente.")) {
                return;
            }
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

    // ── Inspeção Completa do Perfil do Aluno (Visualizar Perfil) ─────────────
    async function abrirDetalhesAluno(userId) {
        const modal = document.getElementById('aluno-modal');
        const box = document.getElementById('aluno-modal-content');
        if (!modal || !box) return;

        box.innerHTML = `
            <div class="profile-loader-box">
                <i class="fa-solid fa-circle-notch fa-spin"></i>
                <p>Carregando perfil completo do usuário...</p>
            </div>
        `;
        modal.classList.add('active');

        try {
            const res = await fetch(`../../back/api_admin.php?action=user_details&user_id=${userId}`);
            const data = await res.json();

            if (!data.sucesso) {
                box.innerHTML = `
                    <div style="text-align:center; padding: 40px 20px;">
                        <i class="fa-solid fa-circle-exclamation" style="font-size: 3rem; color: #ef4444; margin-bottom: 14px;"></i>
                        <h3 style="color: #fff; margin-bottom: 8px;">Erro ao carregar perfil</h3>
                        <p style="color: var(--text-muted);">${escapeHtml(data.mensagem || 'Usuário não encontrado.')}</p>
                    </div>
                `;
                return;
            }

            const u = data.data.usuario;
            const soc = data.data.social || {};
            const prog = data.data.progresso || [];
            const hist = data.data.historico_ligas || [];
            const conquistasTotal = data.data.conquistas_total || 0;

            let foto = u.foto_perfil && u.foto_perfil.startsWith('data:image') ? u.foto_perfil : '../assets/img/opi pulando feliz.png';
            const divSlug = (u.divisao || 'bronze').toLowerCase();
            const divInfo = LIGAS_INFO[divSlug] || LIGAS_INFO.bronze;

            // Renderização das Unidades de Aprendizado (Capítulos 1 a 5)
            let progressoCardsHtml = '';
            for (let c = 1; c <= 5; c++) {
                const item = prog.find(p => Number(p.unidade_numero) === c) || { status: 'trancado', licoes_concluidas: 0 };
                const st = item.status || 'trancado';
                const licoes = item.licoes_concluidas || 0;
                const nomeCap = CAPITULOS_NOMES[c] || `Capítulo ${c}`;

                let badgeClass = 'status-locked';
                let badgeText = 'Trancado';
                let iconStatus = 'fa-lock';
                let pct = Math.min(100, Math.round((licoes / 5) * 100));

                if (st === 'completo') {
                    badgeClass = 'status-completed';
                    badgeText = 'Concluído';
                    iconStatus = 'fa-circle-check';
                    pct = 100;
                } else if (st === 'corrente') {
                    badgeClass = 'status-current';
                    badgeText = 'Em Andamento';
                    iconStatus = 'fa-spinner fa-spin';
                }

                progressoCardsHtml += `
                    <div class="chapter-card ${st}">
                        <div class="chapter-card-header">
                            <div class="chapter-num">Unidade ${c}</div>
                            <span class="chapter-status-badge ${badgeClass}">
                                <i class="fa-solid ${iconStatus}"></i> ${badgeText}
                            </span>
                        </div>
                        <div class="chapter-title">${nomeCap}</div>
                        <div class="chapter-progress-track">
                            <div class="chapter-progress-fill ${st}" style="width: ${pct}%;"></div>
                        </div>
                        <div class="chapter-card-footer">
                            <span>${licoes}/5 lições concluídas</span>
                            <span>${pct}%</span>
                        </div>
                    </div>
                `;
            }

            // Histórico de Ligas
            let historicoHtml = '';
            if (hist.length > 0) {
                hist.forEach(h => {
                    const resLower = (h.resultado || '').toLowerCase();
                    let resBadge = '<span class="hist-badge neutro">Permaneceu</span>';
                    if (resLower === 'subiu') resBadge = '<span class="hist-badge subiu"><i class="fa-solid fa-arrow-trend-up"></i> Promovido</span>';
                    if (resLower === 'desceu') resBadge = '<span class="hist-badge desceu"><i class="fa-solid fa-arrow-trend-down"></i> Rebaixado</span>';

                    historicoHtml += `
                        <div class="hist-row">
                            <div class="hist-week">
                                <i class="fa-solid fa-calendar-day"></i>
                                <span>Semana: <strong>${escapeHtml(h.semana_ref)}</strong></span>
                            </div>
                            <div class="hist-transition">
                                <span style="text-transform: capitalize;">${escapeHtml(h.divisao_anterior)}</span>
                                <i class="fa-solid fa-arrow-right" style="font-size: 10px; color: var(--text-dim);"></i>
                                <span style="text-transform: capitalize; font-weight: bold;">${escapeHtml(h.divisao_nova)}</span>
                            </div>
                            ${resBadge}
                        </div>
                    `;
                });
            } else {
                historicoHtml = `
                    <div class="empty-sub-box">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <p>Nenhuma virada de ligas registrada no histórico deste aluno.</p>
                    </div>
                `;
            }

            // Informação do Grupo de Batalha
            let grupoBoxHtml = '';
            if (soc.grupo) {
                grupoBoxHtml = `
                    <div class="profile-group-box">
                        <div class="group-icon-wrap">
                            <i class="fa-solid fa-people-group"></i>
                        </div>
                        <div class="group-info">
                            <div class="group-title">${escapeHtml(soc.grupo.nome)}</div>
                            <div class="group-meta">Código de Convite: <strong>${escapeHtml(soc.grupo.codigo)}</strong> • ${soc.grupo.total_membros} membros</div>
                        </div>
                    </div>
                `;
            } else {
                grupoBoxHtml = `
                    <div class="profile-group-empty">
                        <i class="fa-solid fa-user-group"></i> O aluno ainda não está participando de um Grupo de Batalha.
                    </div>
                `;
            }

            // Template completo de inspeção
            box.innerHTML = `
                <!-- 1. CABEÇALHO DO PERFIL -->
                <div class="profile-header-card">
                    <div class="profile-avatar-wrapper" style="border-color: ${divInfo.corClara};">
                        <img src="${foto}" alt="Avatar de ${escapeHtml(u.nome)}" class="profile-avatar-big">
                        <div class="profile-league-badge-mini" style="background: ${divInfo.cor};" title="Divisão: ${divInfo.nome}">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                    </div>
                    <div class="profile-user-details">
                        <div class="profile-name-row">
                            <h2 class="profile-user-name">${escapeHtml(u.nome)}</h2>
                            <span class="profile-role-badge ${u.nivel_acesso === 'admin' ? 'role-admin' : 'role-user'}">
                                <i class="fa-solid ${u.nivel_acesso === 'admin' ? 'fa-shield-cat' : 'fa-graduation-cap'}"></i>
                                ${u.nivel_acesso === 'admin' ? 'ADMINISTRADOR' : 'ALUNO'}
                            </span>
                        </div>
                        <div class="profile-email-text">
                            <i class="fa-solid fa-envelope"></i> ${escapeHtml(u.email)}
                        </div>
                        <div class="profile-tags-row">
                            <span class="profile-tag"><i class="fa-solid fa-hashtag"></i> ID: ${u.id}</span>
                            <span class="profile-tag"><i class="fa-solid fa-calendar-check"></i> Cadastrado em: ${u.criado_em}</span>
                            <span class="profile-tag"><i class="fa-solid fa-compass"></i> Dificuldade: ${escapeHtml(u.dificuldade)}</span>
                        </div>
                    </div>
                </div>

                <!-- 2. GRID DE ESTATÍSTICAS PRINCIPAIS (6 CARDS) -->
                <div class="profile-stats-grid">
                    <!-- XP TOTAL -->
                    <div class="pstat-card stat-xp">
                        <div class="pstat-icon"><i class="fa-solid fa-bolt"></i></div>
                        <div class="pstat-data">
                            <div class="pstat-val">${Number(u.xp || 0).toLocaleString('pt-BR')}</div>
                            <div class="pstat-lbl">XP Total Acumulado</div>
                        </div>
                    </div>

                    <!-- TROFÉUS -->
                    <div class="pstat-card stat-trophies">
                        <div class="pstat-icon"><i class="fa-solid fa-trophy"></i></div>
                        <div class="pstat-data">
                            <div class="pstat-val">${Number(u.trofeus || 0).toLocaleString('pt-BR')}</div>
                            <div class="pstat-lbl">Troféus Conquistados</div>
                        </div>
                    </div>

                    <!-- DIVISÃO DE LIGA -->
                    <div class="pstat-card stat-league" style="border-left: 3px solid ${divInfo.corClara};">
                        <div class="pstat-icon" style="color: ${divInfo.corClara};"><i class="fa-solid fa-shield-halved"></i></div>
                        <div class="pstat-data">
                            <div class="pstat-val" style="color: ${divInfo.corClara};">${divInfo.nome}</div>
                            <div class="pstat-lbl">${Number(u.xp_semana || 0).toLocaleString('pt-BR')} XP nesta semana</div>
                        </div>
                    </div>

                    <!-- OFENSIVA -->
                    <div class="pstat-card stat-streak">
                        <div class="pstat-icon"><i class="fa-solid fa-fire"></i></div>
                        <div class="pstat-data">
                            <div class="pstat-val">${u.dias_fogo} dias</div>
                            <div class="pstat-lbl">Sequência de Ofensiva</div>
                        </div>
                    </div>

                    <!-- VIDAS -->
                    <div class="pstat-card stat-lives">
                        <div class="pstat-icon"><i class="fa-solid fa-heart"></i></div>
                        <div class="pstat-data">
                            <div class="pstat-val">${u.vidas} / 3</div>
                            <div class="pstat-lbl">Vidas Disponíveis</div>
                        </div>
                    </div>

                    <!-- CONQUISTAS & AMIGOS -->
                    <div class="pstat-card stat-social">
                        <div class="pstat-icon"><i class="fa-solid fa-award"></i></div>
                        <div class="pstat-data">
                            <div class="pstat-val">${conquistasTotal} Conquistas</div>
                            <div class="pstat-lbl">${soc.seguidores || 0} seguidores • ${soc.seguindo || 0} seguindo</div>
                        </div>
                    </div>
                </div>

                <!-- 3. GRUPO DE BATALHA -->
                <div class="profile-section-title">
                    <i class="fa-solid fa-people-robotic"></i> Grupo de Batalha
                </div>
                ${grupoBoxHtml}

                <!-- 4. PROGRESSO NAS 5 UNIDADES -->
                <div class="profile-section-title" style="margin-top: 24px;">
                    <i class="fa-solid fa-graduation-cap"></i> Trilha de Aprendizado (Capítulos 1 a 5)
                </div>
                <div class="profile-chapters-grid">
                    ${progressoCardsHtml}
                </div>

                <!-- 5. HISTÓRICO DE LIGAS -->
                <div class="profile-section-title" style="margin-top: 24px;">
                    <i class="fa-solid fa-timeline"></i> Histórico de Viradas e Divisões
                </div>
                <div class="profile-hist-container">
                    ${historicoHtml}
                </div>

                <!-- 6. AÇÕES DO MODAL -->
                <div class="profile-modal-footer">
                    <button type="button" class="btn-profile-close" onclick="fecharModalAluno()">
                        <i class="fa-solid fa-xmark"></i> Fechar Visualização
                    </button>
                </div>
            `;

        } catch (err) {
            console.error("Erro ao carregar detalhes do aluno:", err);
            box.innerHTML = `
                <div style="text-align:center; padding: 40px 20px;">
                    <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: #ef4444; margin-bottom: 14px;"></i>
                    <h3 style="color: #fff; margin-bottom: 8px;">Erro de Comunicação</h3>
                    <p style="color: var(--text-muted);">Não foi possível carregar os detalhes do usuário.</p>
                </div>
            `;
        }
    }

    function fecharModalAluno() {
        const modal = document.getElementById('aluno-modal');
        if (modal) modal.classList.remove('active');
    }

    function abrirModalLogoutAdmin() {
        const modal = document.getElementById('logout-modal-admin');
        if (modal) modal.classList.add('active');
    }

    function fecharModalLogoutAdmin() {
        const modal = document.getElementById('logout-modal-admin');
        if (modal) modal.classList.remove('active');
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ── Exportações Globais ───────────────────────────────────────────────────
    window.abrirDetalhesAluno = abrirDetalhesAluno;
    window.fecharModalAluno = fecharModalAluno;
    window.abrirModalLogoutAdmin = abrirModalLogoutAdmin;
    window.fecharModalLogoutAdmin = fecharModalLogoutAdmin;

    // ── Inicialização de Eventos ─────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        carregarEstatisticas();
        carregarUsuarios(1);

        // Botão de Virada de Ligas
        document.getElementById('btn-virada-ligas')?.addEventListener('click', dispararViradaLigas);

        // Busca com Debounce
        document.getElementById('input-busca-email')?.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => carregarUsuarios(1), 300);
        });

        // Filtros de Divisão e Ordenação
        document.getElementById('select-divisao')?.addEventListener('change', () => carregarUsuarios(1));
        document.getElementById('select-ordenacao-id')?.addEventListener('change', () => carregarUsuarios(1));

        // Fechar modais ao clicar no fundo
        document.getElementById('aluno-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'aluno-modal') fecharModalAluno();
        });

        document.getElementById('logout-modal-admin')?.addEventListener('click', (e) => {
            if (e.target.id === 'logout-modal-admin') fecharModalLogoutAdmin();
        });

        // Fechar modais ao pressionar tecla ESC
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                fecharModalAluno();
                fecharModalLogoutAdmin();
            }
        });
    });

})();
