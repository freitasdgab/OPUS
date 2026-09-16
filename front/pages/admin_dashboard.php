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
opus_ensure_player_columns($conn);

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

    <style>
        :root {
            --bg-dark: #0a0a0f;
            --bg-surface: #12141c;
            --bg-card: rgba(20, 24, 35, 0.85);
            --bg-card-hover: rgba(26, 32, 48, 0.95);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-highlight: rgba(255, 255, 255, 0.16);
            --accent-blue: #1cb0f6;
            --accent-green: #22c55e;
            --accent-purple: #a855f7;
            --accent-orange: #f97316;
            --accent-red: #ef4444;
            --accent-gold: #ffd700;
            --text-main: #ffffff;
            --text-muted: #8e95a1;
            --text-dim: #555e6d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-dark);
            font-family: 'Poppins', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* BARRA SUPERIOR INDEPENDENTE DO ADMIN */
        .admin-navbar {
            background: rgba(18, 20, 28, 0.95);
            border-bottom: 1px solid var(--border-subtle);
            padding: 18px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(14px);
        }

        .admin-nav-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
        }

        .admin-logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 3px;
            color: #fff;
        }

        .admin-tag {
            background: linear-gradient(135deg, #ef4444, #f97316);
            color: #fff;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.72rem;
            font-weight: 900;
            padding: 4px 10px;
            border-radius: 8px;
            letter-spacing: 1px;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.35);
        }

        .admin-nav-user {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-user-info {
            text-align: right;
        }

        .admin-user-name {
            font-weight: 700;
            font-size: 0.92rem;
            color: #fff;
        }

        .admin-user-email {
            font-size: 0.76rem;
            color: var(--text-muted);
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(239, 68, 68, 0.12);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 9px 18px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.82rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #ef4444;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
        }

        /* CONTAINER PRINCIPAL */
        .admin-container {
            flex: 1;
            padding: 35px 40px 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        /* ----------------------------------------------------
           DESTAQUE VISUAL GRANDE: SEMANA, PRÓXIMA VIRADA E BOTÃO
           ---------------------------------------------------- */
        .highlight-banner-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1.2fr;
            gap: 20px;
            margin-bottom: 35px;
        }

        @media (max-width: 992px) {
            .highlight-banner-grid {
                grid-template-columns: 1fr;
            }
        }

        .highlight-card {
            background: linear-gradient(135deg, rgba(26, 32, 48, 0.9), rgba(18, 22, 34, 0.95));
            border: 1px solid var(--border-highlight);
            border-radius: 20px;
            padding: 26px 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            display: flex;
            align-items: center;
            gap: 22px;
            position: relative;
            overflow: hidden;
        }

        .highlight-card-icon {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            flex-shrink: 0;
        }

        .icon-semana {
            background: rgba(28, 176, 246, 0.15);
            color: var(--accent-blue);
            border: 1px solid rgba(28, 176, 246, 0.35);
        }

        .icon-virada {
            background: rgba(168, 85, 247, 0.15);
            color: var(--accent-purple);
            border: 1px solid rgba(168, 85, 247, 0.35);
        }

        .highlight-content .label {
            font-size: 0.82rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .highlight-content .value-large {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.7rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.15;
            letter-spacing: 0.5px;
        }

        /* CARD ESPECIAL DO BOTÃO DE VIRADA */
        .highlight-card-action {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.25));
            border: 1px solid rgba(249, 115, 22, 0.4);
            border-radius: 20px;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            box-shadow: 0 10px 30px rgba(249, 115, 22, 0.15);
        }

        .highlight-action-text h3 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.15rem;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }

        .highlight-action-text p {
            font-size: 0.82rem;
            color: #fdba74;
        }

        .btn-virada-destaque {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: #fff;
            border: none;
            padding: 16px 26px;
            border-radius: 14px;
            font-family: 'Orbitron', sans-serif;
            font-size: 0.95rem;
            font-weight: 900;
            letter-spacing: 0.5px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.4);
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .btn-virada-destaque:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 28px rgba(249, 115, 22, 0.6);
            background: linear-gradient(135deg, #fb923c, #f97316);
        }

        .btn-virada-destaque:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* ----------------------------------------------------
           KPIS PRINCIPAIS (TOTAL USUÁRIOS, OFENSIVA, VIDAS)
           ---------------------------------------------------- */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }

        @media (max-width: 900px) {
            .kpi-row {
                grid-template-columns: 1fr;
            }
        }

        .kpi-box {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 18px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transition: all 0.25s ease;
        }

        .kpi-box:hover {
            transform: translateY(-4px);
            border-color: var(--border-highlight);
            background: var(--bg-card-hover);
        }

        .kpi-box-icon {
            width: 58px;
            height: 58px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            flex-shrink: 0;
        }

        .kpi-box-users .kpi-box-icon { background: rgba(28, 176, 246, 0.15); color: #1cb0f6; }
        .kpi-box-streak .kpi-box-icon { background: rgba(249, 115, 22, 0.15); color: #f97316; }
        .kpi-box-lives .kpi-box-icon { background: rgba(239, 68, 68, 0.15); color: #ef4444; }

        .kpi-box-info .kpi-title {
            font-size: 0.82rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .kpi-box-info .kpi-number {
            font-family: 'Orbitron', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.1;
        }

        .kpi-box-info .kpi-sub {
            font-size: 0.78rem;
            color: var(--text-dim);
            margin-top: 4px;
        }

        /* ----------------------------------------------------
           SEÇÃO DE DIVISÕES (LIGAS)
           ---------------------------------------------------- */
        .section-header {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: 0.5px;
        }

        .section-header i {
            color: var(--accent-blue);
        }

        .ligas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 35px;
        }

        .liga-card-item {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.2s ease;
        }

        .liga-card-item:hover {
            transform: translateY(-3px);
            background: var(--bg-card-hover);
        }

        .liga-icon-badge {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
        }

        .liga-card-info h4 {
            font-family: 'Orbitron', sans-serif;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .liga-card-info .count {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
        }

        .liga-card-info .pct {
            font-size: 0.74rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* ----------------------------------------------------
           SEÇÃO DE GRÁFICOS (3 GRÁFICOS MANTIDOS)
           ---------------------------------------------------- */
        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
        }

        .chart-panel {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
        }

        .chart-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .chart-panel-header h3 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-body {
            position: relative;
            flex: 1;
            min-height: 270px;
            max-height: 300px;
        }

        /* ----------------------------------------------------
           TABELA DE GERENCIAMENTO DE USUÁRIOS
           ---------------------------------------------------- */
        .table-section {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            margin-bottom: 40px;
        }

        .table-filter-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .search-container {
            position: relative;
            flex: 1;
            min-width: 260px;
            max-width: 440px;
        }

        .search-container input {
            width: 100%;
            background: rgba(10, 12, 18, 0.8);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 12px 16px 12px 42px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .search-container input:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(28, 176, 246, 0.2);
        }

        .search-container i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
        }

        .select-filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .custom-select {
            background: rgba(10, 12, 18, 0.8);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 11px 16px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            outline: none;
            cursor: pointer;
            transition: border-color 0.2s ease;
        }

        .custom-select:focus {
            border-color: var(--accent-blue);
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }

        .admin-table th {
            background: rgba(10, 12, 18, 0.95);
            padding: 16px 18px;
            color: var(--text-muted);
            font-weight: 700;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid var(--border-subtle);
            white-space: nowrap;
        }

        .admin-table td {
            padding: 16px 18px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        .admin-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .user-avatar-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar-img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.12);
            background: #1e293b;
        }

        .user-meta .name {
            font-weight: 600;
            color: #fff;
            font-size: 0.9rem;
        }

        .user-meta .email {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        /* BADGES DE DIVISÃO */
        .div-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.74rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 20px;
            font-family: 'Orbitron', sans-serif;
            text-transform: uppercase;
        }

        .badge-bronze { background: rgba(205, 127, 50, 0.15); color: #cd7f32; border: 1px solid rgba(205, 127, 50, 0.4); }
        .badge-prata { background: rgba(192, 192, 192, 0.15); color: #c0c0c0; border: 1px solid rgba(192, 192, 192, 0.4); }
        .badge-ouro { background: rgba(255, 215, 0, 0.15); color: #ffd700; border: 1px solid rgba(255, 215, 0, 0.4); }
        .badge-diamante { background: rgba(91, 231, 255, 0.15); color: #5be7ff; border: 1px solid rgba(91, 231, 255, 0.4); }
        .badge-mestre { background: rgba(196, 123, 255, 0.15); color: #c47bff; border: 1px solid rgba(196, 123, 255, 0.4); }

        .hearts-box {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #ef4444;
        }

        .hearts-box i.empty {
            color: #475569;
        }

        .action-btns {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action-sm {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-subtle);
            color: #fff;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .btn-action-sm:hover {
            background: rgba(28, 176, 246, 0.2);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
            transform: scale(1.08);
        }

        .btn-action-sm.btn-heart:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
            color: #ef4444;
        }

        /* PAGINAÇÃO */
        .page-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 0.85rem;
            color: var(--text-muted);
            flex-wrap: wrap;
            gap: 15px;
        }

        .page-nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-btn {
            min-width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(10, 12, 18, 0.8);
            border: 1px solid var(--border-subtle);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s ease;
            padding: 0 10px;
        }

        .page-btn:hover:not(:disabled) {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
        }

        .page-btn.active {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            font-weight: 700;
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* MODAL DE DETALHES */
        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-bg.active {
            display: flex;
        }

        .modal-box {
            background: #161a24;
            border: 1px solid var(--border-highlight);
            border-radius: 24px;
            width: 100%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7);
            padding: 30px;
            position: relative;
        }

        .modal-close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            border: none;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s ease;
        }

        .modal-close-btn:hover {
            background: #ef4444;
            transform: scale(1.1);
        }

        /* TOAST NOTIFICATION */
        .toast-box {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-msg {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 14px 22px;
            border-radius: 12px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.3s ease;
        }

        .toast-msg.success { border-left: 5px solid #22c55e; }
        .toast-msg.error { border-left: 5px solid #ef4444; }
        .toast-msg.info { border-left: 5px solid #1cb0f6; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
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

    <script>
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
    </script>
</body>
</html>
