<?php
session_start();
require_once '../../back/conexao.php';
require_once '../../back/jogador_status.php';
require_once '../../back/ligas_logic.php';

// ----------------------------------------------------
// VERIFICAÇÃO DE SEGURANÇA E ACESSO DE ADMINISTRADOR
// ----------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Valida permissão de administrador diretamente no banco de dados
$stmt_admin = $conn->prepare("SELECT nome, email, nivel_acesso FROM usuarios WHERE id = ?");
$stmt_admin->bind_param("i", $user_id);
$stmt_admin->execute();
$current_user = $stmt_admin->get_result()->fetch_assoc();

if (!$current_user || ($current_user['nivel_acesso'] ?? '') !== 'admin') {
    echo "<!DOCTYPE html>
    <html lang='pt-BR'>
    <head>
        <meta charset='UTF-8'>
        <title>Acesso Negado - OPUS</title>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
        <style>
            body { background: #0d0d0f; color: #fff; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
            .box { background: #161a24; padding: 40px; border-radius: 20px; border: 1px solid #ff4b4b; max-width: 480px; box-shadow: 0 10px 30px rgba(255,75,75,0.2); }
            i { font-size: 48px; color: #ff4b4b; margin-bottom: 20px; }
            h1 { font-size: 22px; margin-bottom: 10px; }
            p { color: #8e95a1; margin-bottom: 25px; font-size: 14px; line-height: 1.5; }
            a { background: #1cb0f6; color: #fff; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='box'>
            <i class='fa-solid fa-lock'></i>
            <h1>Ambiente Restrito</h1>
            <p>Este painel administrativo é isolado e requer privilégios de Administrador.</p>
            <a href='auth.html'><i class='fa-solid fa-arrow-left'></i> Fazer Login como Admin</a>
        </div>
    </body>
    </html>";
    exit();
}

$nome_admin = htmlspecialchars($current_user['nome'] ?? 'Administrador');
$email_admin = htmlspecialchars($current_user['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Administrador - OPUS</title>
    <link rel="shortcut icon" href="../assets/img/logo.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600;700;800;900&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
    <!-- BARRA SUPERIOR EXCLUSIVA DO ADMINISTRADOR -->
    <header class="admin-navbar">
        <div class="admin-nav-brand">
            <div class="admin-logo">OPUS</div>
            <span class="admin-tag">ADMIN</span>
        </div>

        <div class="admin-nav-user">
            <div class="admin-user-info">
                <div class="admin-user-name"><?= $nome_admin ?></div>
                <div class="admin-user-email"><?= $email_admin ?></div>
            </div>
            <a href="../../back/logout.php" class="btn-logout" title="Encerrar Sessão">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair
            </a>
        </div>
    </header>

    <!-- ÁREA PRINCIPAL DO PAINEL ADMIN -->
    <main class="admin-container">

        <!-- DESTAQUE VISUAL AMPLIADO: SEMANA DE LIGAS & PRÓXIMA VIRADA & BOTÃO -->
        <section class="highlight-banner-grid">
            <!-- 1. SEMANA DE LIGAS (TAMANHO MAIOR) -->
            <div class="highlight-card">
                <div class="highlight-card-icon icon-semana">
                    <i class="fa-solid fa-calendar-week"></i>
                </div>
                <div class="highlight-content">
                    <div class="label">Semana de Ligas Atual</div>
                    <div class="value-large" id="destaque-semana">--/--/----</div>
                </div>
            </div>

            <!-- 2. PRÓXIMA VIRADA (TAMANHO MAIOR) -->
            <div class="highlight-card">
                <div class="highlight-card-icon icon-virada">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="highlight-content">
                    <div class="label">Próxima Virada de Ligas</div>
                    <div class="value-large" id="destaque-proxima-virada" style="color: #c47bff;">--/--/---- --:--</div>
                </div>
            </div>

            <!-- 3. BOTÃO DE ATUALIZAR VIRADA DE LIGA -->
            <div class="highlight-card-action">
                <div class="highlight-action-text">
                    <h3>Virada Semanal</h3>
                    <p>Processar subidas e rebaixamentos</p>
                </div>
                <button id="btn-virada-ligas" class="btn-virada-destaque" title="Processar virada semanal de ligas agora">
                    <i class="fa-solid fa-bolt"></i> Atualizar Virada de Liga
                </button>
            </div>
        </section>

        <!-- KPIS PRINCIPAIS (TOTAL USUÁRIOS, DIAS DE OFENSIVA, STATUS DE VIDA) -->
        <section class="kpi-row">
            <!-- TOTAL DE USUÁRIOS -->
            <div class="kpi-box kpi-box-users">
                <div class="kpi-box-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="kpi-box-info">
                    <div class="kpi-title">Total de Usuários</div>
                    <div class="kpi-number" id="kpi-total-usuarios">--</div>
                    <div class="kpi-sub">Cadastrados no ecossistema</div>
                </div>
            </div>

            <!-- DIAS DE OFENSIVA -->
            <div class="kpi-box kpi-box-streak">
                <div class="kpi-box-icon">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div class="kpi-box-info">
                    <div class="kpi-title">Dias de Ofensiva</div>
                    <div class="kpi-number" id="kpi-ofensiva-ativa">--</div>
                    <div class="kpi-sub">Recorde: <strong id="kpi-max-ofensiva" style="color:#f97316;">--</strong> dias seguidos</div>
                </div>
            </div>

            <!-- STATUS DE VIDA -->
            <div class="kpi-box kpi-box-lives">
                <div class="kpi-box-icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div class="kpi-box-info">
                    <div class="kpi-title">Status de Vida</div>
                    <div class="kpi-number" id="kpi-vidas-cheias">--</div>
                    <div class="kpi-sub">Com 3 vidas cheias (<strong id="kpi-vidas-zeradas" style="color:#ef4444;">--</strong> zerados)</div>
                </div>
            </div>
        </section>

        <!-- SEÇÃO DE DIVISÕES / LIGAS -->
        <div class="section-header">
            <i class="fa-solid fa-shield-halved"></i>
            Distribuição de Usuários por Divisão
        </div>

        <div class="ligas-grid" id="ligas-grid-container">
            <!-- Preenchido via JavaScript -->
        </div>

        <!-- GRÁFICOS INTERATIVOS -->
        <section class="charts-row">
            <!-- GRÁFICO 1: PROPORÇÃO DA DISTRIBUIÇÃO DE LIGAS -->
            <div class="chart-panel">
                <div class="chart-panel-header">
                    <h3><i class="fa-solid fa-chart-pie" style="color: #1cb0f6;"></i> Proporção de Ligas</h3>
                </div>
                <div class="chart-body">
                    <canvas id="chartLigas"></canvas>
                </div>
            </div>

            <!-- GRÁFICO 2: DADOS DE CONCLUSÃO DE CAPÍTULOS -->
            <div class="chart-panel">
                <div class="chart-panel-header">
                    <h3><i class="fa-solid fa-bars-progress" style="color: #22c55e;"></i> Conclusão de Capítulos</h3>
                </div>
                <div class="chart-body">
                    <canvas id="chartCapitulos"></canvas>
                </div>
            </div>

            <!-- GRÁFICO 3: STATUS DE VIDA DOS JOGADORES -->
            <div class="chart-panel">
                <div class="chart-panel-header">
                    <h3><i class="fa-solid fa-heart" style="color: #ef4444;"></i> Status de Vidas</h3>
                </div>
                <div class="chart-body">
                    <canvas id="chartStatusVidas"></canvas>
                </div>
            </div>
        </section>

        <!-- SEÇÃO DE GERENCIAR USUÁRIOS -->
        <section class="table-section">
            <div class="table-filter-bar">
                <div class="section-header" style="margin-bottom: 0;">
                    <i class="fa-solid fa-users-gear"></i>
                    Gerenciar Usuários
                </div>

                <div class="select-filter-group">
                    <!-- BUSCA POR E-MAIL -->
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="input-busca-email" placeholder="Buscar por e-mail ou nome...">
                    </div>

                    <!-- DIVISÃO POR LIGAS -->
                    <select id="select-divisao" class="custom-select">
                        <option value="">Todas as Divisões</option>
                        <option value="bronze">Liga Bronze</option>
                        <option value="prata">Liga Prata</option>
                        <option value="ouro">Liga Ouro</option>
                        <option value="diamante">Liga Diamante</option>
                        <option value="mestre">Liga Mestre</option>
                    </select>

                    <!-- ORDENAÇÃO POR ID CRESCENTE E DECRESCENTE -->
                    <select id="select-ordenacao-id" class="custom-select">
                        <option value="ASC">ID Crescente (1 → 99)</option>
                        <option value="DESC">ID Decrescente (99 → 1)</option>
                    </select>
                </div>
            </div>

            <!-- TABELA -->
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Aluno</th>
                            <th>Divisão de Liga</th>
                            <th>Status de Vidas</th>
                            <th>Ofensiva</th>
                            <th>Data de Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="admin-table-body">
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 40px; color: var(--text-muted);">
                                <i class="fa-solid fa-spinner fa-spin" style="font-size: 22px; margin-bottom: 8px; display:block;"></i>
                                Carregando usuários...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINAÇÃO -->
            <div class="page-bar">
                <div id="page-info-text">Mostrando 0 de 0 usuários</div>
                <div class="page-nav" id="page-nav-btns">
                    <!-- Gerado via JS -->
                </div>
            </div>
        </section>

    </main>

    <!-- MODAL DE DETALHES DO ALUNO -->
    <div class="modal-bg" id="aluno-modal">
        <div class="modal-box">
            <button class="modal-close-btn" onclick="fecharModalAluno()"><i class="fa-solid fa-xmark"></i></button>
            <div id="aluno-modal-content">
                <!-- Conteúdo preenchido dinamicamente -->
            </div>
        </div>
    </div>

    <!-- TOAST CONTAINER -->
    <div class="toast-box" id="toast-wrapper"></div>

    <script src="../assets/js/admin_dashboard.js"></script>
</body>
</html>
