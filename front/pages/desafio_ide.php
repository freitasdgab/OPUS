<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../../back/config/conexao.php';

// Proteção de autenticação
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

require_once __DIR__ . '/../../back/lib/jogador_status.php';
require_once __DIR__ . '/../../back/lib/desafios_logic.php';

$user_id = (int) $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);

$id_ou_slug = $_GET['slug'] ?? $_GET['id'] ?? '';
if (empty($id_ou_slug)) {
    header("Location: desafios.php");
    exit();
}

$desafio = desafios_obter_detalhe($conn, $user_id, $id_ou_slug);
if (!$desafio) {
    header("Location: desafios.php");
    exit();
}

// Se o desafio estiver bloqueado para o aluno, redireciona para a lista
if (!$desafio['desbloqueado']) {
    header("Location: desafios.php?bloqueado=1&secao=" . (int)$desafio['secao_requerida']);
    exit();
}

$codigo_inicial = $desafio['codigo_atual'];
$is_concluido = !empty($desafio['concluido']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($desafio['titulo']) ?> - Desafio Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Orbitron:wght@700;900&family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/codedex.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/opus_alerta.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/light-mode.css?v=<?= time() ?>">
    
    <!-- Ace Editor -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ext-language_tools.min.js"></script>

    <style>
        .codedex-container {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            background: #0d0d12 !important;
            z-index: 9999 !important;
            font-family: 'Nunito', sans-serif !important;
        }
        .codedex-topbar {
            height: 56px !important;
            min-height: 56px !important;
            background: #13151f !important;
            border-bottom: 1px solid #232738 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 24px !important;
        }
        .codedex-workspace {
            flex: 1 !important;
            display: grid !important;
            grid-template-columns: 44% 56% !important;
            height: calc(100vh - 56px) !important;
            overflow: hidden !important;
            background: #0d0d12 !important;
        }
        .codedex-instructions-panel {
            background: #13151f !important;
            border-right: 1px solid #232738 !important;
            padding: 28px 32px !important;
            overflow-y: auto !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 18px !important;
        }
        .codedex-ide-panel {
            display: flex !important;
            flex-direction: column !important;
            background: #0d0d12 !important;
            height: 100% !important;
            overflow: hidden !important;
        }
        .codedex-editor-area {
            height: 54% !important;
            min-height: 250px !important;
            position: relative !important;
            background: #0d0d12 !important;
        }
        #codedexAceEditor {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            width: 100% !important;
            height: 100% !important;
            font-size: 15px !important;
            font-family: 'Courier New', Courier, monospace !important;
        }
        .codedex-terminal-panel {
            flex: 1 !important;
            min-height: 180px !important;
            background: #08090c !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }

        /* Banner de Projeto Concluído */
        .banner-projeto-concluido {
            background: rgba(88, 204, 2, 0.12);
            border: 1px solid rgba(88, 204, 2, 0.35);
            border-left: 4px solid #58cc02;
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #58cc02;
        }
        .banner-projeto-concluido i {
            font-size: 1.8rem;
        }
        .banner-projeto-concluido h4 {
            margin: 0 0 4px 0;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 800;
        }
        .banner-projeto-concluido p {
            margin: 0;
            color: #cbd5e1;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        /* Boxes Específicos com Cores Vivas */
        .codedex-box-requisitos {
            background: rgba(34, 197, 94, 0.08) !important;
            border: 1px solid rgba(34, 197, 94, 0.25) !important;
            border-left: 5px solid #22c55e !important;
            border-radius: 12px;
            padding: 18px 22px;
        }
        .box-requisitos-title {
            color: #4ade80 !important;
            font-size: 1rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .codedex-box-requisitos strong {
            color: #86efac;
        }

        .codedex-box-missao {
            background: rgba(28, 176, 246, 0.08) !important;
            border: 1px solid rgba(28, 176, 246, 0.25) !important;
            border-left: 5px solid <?= htmlspecialchars($desafio['cor']) ?> !important;
            border-radius: 12px;
            padding: 18px 22px;
        }
        .box-missao-title {
            color: <?= htmlspecialchars($desafio['cor']) ?> !important;
            font-size: 1rem;
            font-weight: 800;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Box de Saída Esperada no Console */
        .codedex-box-saida {
            background: rgba(14, 165, 233, 0.08) !important;
            border: 1px solid rgba(14, 165, 233, 0.25) !important;
            border-left: 5px solid #0284c7 !important;
            border-radius: 12px;
            padding: 18px 22px;
        }
        .box-saida-title {
            color: #38bdf8 !important;
            font-size: 1rem;
            font-weight: 800;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .codedex-saida-pre {
            background: #090a0e;
            border: 1px solid #1e2232;
            border-radius: 8px;
            padding: 12px 14px;
            color: #38bdf8;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.88rem;
            margin: 0;
            overflow-x: auto;
            white-space: pre-wrap;
            line-height: 1.5;
        }

        /* Topo do Terminal Redesenhado (Sem Lixeirinha, Visual Clean) */
        .terminal-header-redesigned {
            height: 40px;
            background: #11131c;
            border-top: 1px solid #232738;
            border-bottom: 1px solid #1c1f2e;
            padding: 0 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .terminal-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .terminal-dots {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .terminal-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .dot-red { background: #ff5f56; }
        .dot-yellow { background: #ffbd2e; }
        .dot-green { background: #27c93f; }

        .terminal-title-text {
            font-size: 0.82rem;
            font-weight: 700;
            color: #94a3b8;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .terminal-status-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
            background: #1b1e2a;
            color: #8b95a8;
            border: 1px solid #2b3044;
        }
        .terminal-status-badge.running {
            background: rgba(28, 176, 246, 0.15);
            color: #38bdf8;
            border-color: rgba(28, 176, 246, 0.4);
        }
        .terminal-status-badge.success {
            background: rgba(88, 204, 2, 0.15);
            color: #58cc02;
            border-color: rgba(88, 204, 2, 0.4);
        }
        .terminal-status-badge.error {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.4);
        }

        /* Texto de Retorno do Terminal Formatado e com Alto Contraste */
        .codedex-terminal-text {
            font-family: 'Consolas', 'Fira Code', 'Courier New', monospace;
            font-size: 0.95rem;
            line-height: 1.55;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .codedex-terminal-text.success-text {
            color: #4ade80;
            font-weight: 600;
        }
        .codedex-terminal-text.error-text {
            color: #f87171;
            font-weight: 600;
        }
        .codedex-terminal-loading {
            color: #38bdf8;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.92rem;
            font-weight: 600;
        }

        /* Botões de Ação na IDE (Estilo 3D Sólido, Sem Glitter) */
        .btn-submeter-projeto {
            background: #58cc02 !important;
            color: #ffffff !important;
            border: none !important;
            border-bottom: 4px solid #46a302 !important;
            font-family: 'Nunito', sans-serif !important;
            font-size: 0.92rem !important;
            font-weight: 800 !important;
            padding: 9px 20px !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            transition: all 0.1s ease !important;
            box-shadow: none !important;
        }
        .btn-submeter-projeto:hover:not(:disabled) {
            background: #61df02 !important;
            transform: translateY(-2px) !important;
        }
        .btn-submeter-projeto:active:not(:disabled) {
            transform: translateY(2px) !important;
            border-bottom-width: 2px !important;
        }

        /* Botão quando o projeto já foi concluído (Verde Sólido, NUNCA Preto) */
        .btn-projeto-entregue {
            background: rgba(88, 204, 2, 0.15) !important;
            color: #58cc02 !important;
            border: 1.5px solid rgba(88, 204, 2, 0.4) !important;
            border-bottom: 4px solid #46a302 !important;
            cursor: default !important;
            font-weight: 800 !important;
            padding: 9px 20px !important;
            border-radius: 10px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 8px !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            opacity: 1 !important;
        }

        .btn-salvar-rascunho {
            background: transparent !important;
            border: 1px solid #333a4d !important;
            color: #94a3b8 !important;
            font-size: 0.85rem !important;
            font-weight: 700 !important;
            padding: 8px 14px !important;
            border-radius: 8px !important;
            cursor: pointer !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            transition: all 0.15s ease !important;
        }
        .btn-salvar-rascunho:hover {
            color: #ffffff !important;
            border-color: #4b5563 !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        /* Confetes Animados */
        .confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 100000;
            overflow: hidden;
        }
        .confetti-piece {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0;
        }
        @keyframes confettiFall {
            0% { transform: translateY(-30px) rotate(0deg); opacity: 1; }
            100% { transform: translateY(105vh) rotate(720deg); opacity: 0; }
        }

        /* Modais */
        .modal-conclusao-overlay {
            position: fixed;
            inset: 0;
            background: rgba(8, 9, 14, 0.85);
            backdrop-filter: blur(6px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            animation: fadeInModal 0.2s ease-out forwards;
        }
        @keyframes fadeInModal {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Animação Vibrante de Vitória */
        @keyframes modalPopIn {
            0% { transform: scale(0.7) translateY(40px); opacity: 0; }
            60% { transform: scale(1.04) translateY(-8px); opacity: 1; }
            100% { transform: scale(1) translateY(0); opacity: 1; }
        }
        .modal-anim-vitoria {
            animation: modalPopIn 0.38s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes mascotBounceCelebration {
            0%, 100% { transform: translateY(0) scale(1) rotate(0deg); }
            25% { transform: translateY(-12px) scale(1.05) rotate(-3deg); }
            75% { transform: translateY(-4px) scale(1.02) rotate(3deg); }
        }

        @keyframes sparkleSpin {
            0% { transform: scale(0) rotate(0deg); opacity: 0; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 1; }
            100% { transform: scale(0.9) rotate(360deg); opacity: 0.85; }
        }

        .mascote-vitoria-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 12px;
        }
        .mascote-vitoria-img {
            width: 135px;
            height: auto;
            animation: mascotBounceCelebration 2s ease-in-out infinite;
        }
        .vitoria-sparkle-1 {
            position: absolute;
            top: 5px;
            left: -15px;
            color: #ffc800;
            font-size: 1.4rem;
            animation: sparkleSpin 2s ease-in-out infinite alternate;
        }
        .vitoria-sparkle-2 {
            position: absolute;
            top: 15px;
            right: -15px;
            color: #38bdf8;
            font-size: 1.2rem;
            animation: sparkleSpin 2.4s ease-in-out infinite alternate-reverse;
        }

        .modal-conclusao-card {
            background: #141722;
            border: 2px solid #58cc02;
            border-radius: 24px;
            max-width: 480px;
            width: 90%;
            padding: 36px 32px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
            position: relative;
        }
        .badge-vitoria-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(88, 204, 2, 0.15);
            color: #58cc02;
            border: 1px solid rgba(88, 204, 2, 0.4);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 12px;
        }
        .modal-conclusao-title {
            font-size: 1.55rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 12px;
        }
        .modal-conclusao-desc {
            color: #cbd5e1;
            font-size: 0.94rem;
            line-height: 1.55;
            margin-bottom: 26px;
        }

        .btn-modal-voltar-desafios {
            background: #58cc02;
            color: #ffffff;
            border: none;
            border-bottom: 4px solid #46a302;
            padding: 13px 30px;
            font-size: 0.96rem;
            font-weight: 800;
            font-family: 'Poppins', sans-serif;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.15s ease;
            cursor: pointer;
        }
        .btn-modal-voltar-desafios:hover {
            background: #61df02;
            transform: translateY(-2px);
        }
        .btn-modal-voltar-desafios:active {
            transform: translateY(2px);
            border-bottom-width: 2px;
        }

        /* Card de Erro / Ajuste */
        .modal-erro-card {
            background: #141722;
            border: 2px solid #ef4444;
            border-radius: 24px;
            max-width: 480px;
            width: 90%;
            padding: 34px 30px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
            position: relative;
        }
        .modal-anim-entry {
            animation: modalPopIn 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .modal-erro-mascote img {
            width: 120px;
            height: auto;
            margin-bottom: 12px;
            animation: floatMascotErro 3s ease-in-out infinite;
        }
        @keyframes floatMascotErro {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .badge-erro-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 10px;
        }
        .modal-erro-title {
            font-size: 1.45rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 12px;
        }
        .erro-detalhes-box {
            background: #0d0f16;
            border: 1px solid #232738;
            border-radius: 12px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 0.88rem;
            line-height: 1.5;
            text-align: left;
            margin-bottom: 16px;
        }
        .modal-erro-pergunta {
            color: #cbd5e1;
            font-size: 0.92rem;
            line-height: 1.55;
            margin-bottom: 22px;
        }
        .modal-erro-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* =========================================================================
           MODO CLARO COMPLETO E LIMPO (SEM BARRAS PRETAS E COM CORES VIVAS)
           ========================================================================= */
        body.light-mode {
            background: #f8fafc !important;
            color: #0f172a !important;
        }
        body.light-mode .codedex-container {
            background: #ffffff !important;
            color: #0f172a !important;
        }
        body.light-mode .codedex-topbar {
            background: #ffffff !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        body.light-mode .codedex-topbar-title {
            color: #0f172a !important;
        }
        body.light-mode .codedex-btn-back {
            background: #f1f5f9 !important;
            color: #334155 !important;
            border-color: #cbd5e1 !important;
        }
        body.light-mode .codedex-btn-back:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }

        /* Área de Trabalho Sem Linha Preta Divisória */
        body.light-mode .codedex-workspace {
            background: #f8fafc !important;
        }
        body.light-mode .codedex-instructions-panel {
            background: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
        }
        body.light-mode .codedex-instructions-panel::-webkit-scrollbar {
            width: 6px;
        }
        body.light-mode .codedex-instructions-panel::-webkit-scrollbar-track {
            background: #ffffff !important;
        }
        body.light-mode .codedex-instructions-panel::-webkit-scrollbar-thumb {
            background: #cbd5e1 !important;
            border-radius: 4px;
        }
        body.light-mode .codedex-instructions-panel::-webkit-scrollbar-thumb:hover {
            background: #94a3b8 !important;
        }

        body.light-mode .codedex-lesson-heading {
            color: #0f172a !important;
        }
        body.light-mode .codedex-text-content {
            color: #334155 !important;
        }

        /* Requisitos Aprendidos na Trilha (Verde Vivo no Modo Claro) */
        body.light-mode .codedex-box-requisitos {
            background: #f0fdf4 !important;
            border: 1px solid #bbf7d0 !important;
            border-left: 5px solid #16a34a !important;
        }
        body.light-mode .box-requisitos-title {
            color: #15803d !important;
        }
        body.light-mode .codedex-box-requisitos p {
            color: #1f2937 !important;
        }
        body.light-mode .codedex-box-requisitos strong {
            color: #166534 !important;
        }

        /* Missão do Desafio (Azul/Tema Vivo no Modo Claro) */
        body.light-mode .codedex-box-missao {
            background: #f0f9ff !important;
            border: 1px solid #bae6fd !important;
            border-left: 5px solid #0284c7 !important;
        }
        body.light-mode .box-missao-title {
            color: #0369a1 !important;
        }
        body.light-mode .codedex-box-missao p {
            color: #1e293b !important;
        }

        /* Saída Esperada no Modo Claro (Nítido e Sem Linhas Pretas) */
        body.light-mode .codedex-box-saida {
            background: #f0f9ff !important;
            border: 1px solid #bae6fd !important;
            border-left: 5px solid #0284c7 !important;
        }
        body.light-mode .box-saida-title {
            color: #0369a1 !important;
        }
        body.light-mode .codedex-saida-pre {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #0c4a6e !important;
            font-weight: 600 !important;
        }

        /* Banner de Concluído no Modo Claro */
        body.light-mode .banner-projeto-concluido {
            background: #f0fdf4 !important;
            border: 1px solid #86efac !important;
            border-left: 4px solid #16a34a !important;
        }
        body.light-mode .banner-projeto-concluido h4 {
            color: #166534 !important;
        }
        body.light-mode .banner-projeto-concluido p {
            color: #15803d !important;
        }

        /* Dica no Modo Claro */
        body.light-mode .codedex-hint-card {
            background: #fefce8 !important;
            border: 1px solid #fef08a !important;
            border-left: 5px solid #ca8a04 !important;
        }
        body.light-mode .codedex-hint-header {
            color: #a16207 !important;
        }
        body.light-mode .codedex-hint-card p {
            color: #713f12 !important;
        }

        /* Painel da IDE no Modo Claro */
        body.light-mode .codedex-ide-panel {
            background: #ffffff !important;
        }
        body.light-mode .codedex-editor-top {
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        body.light-mode .codedex-tab-file {
            background: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #e2e8f0 !important;
            border-bottom: none !important;
            border-top: 3px solid #1cb0f6 !important;
        }
        body.light-mode .codedex-editor-area {
            background: #ffffff !important;
        }
        body.light-mode .codedex-ide-actions {
            background: #f8fafc !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }

        /* Terminal no Modo Claro Sem Barra Escura */
        body.light-mode .terminal-header-redesigned {
            background: #ffffff !important;
            border-top: 1px solid #e2e8f0 !important;
            border-bottom: 1px solid #e2e8f0 !important;
        }
        body.light-mode .terminal-title-text {
            color: #475569 !important;
        }
        body.light-mode .codedex-terminal-panel {
            background: #ffffff !important;
        }
        body.light-mode .codedex-terminal-output {
            background: #ffffff !important;
            color: #0f172a !important;
        }
        body.light-mode .codedex-terminal-output::-webkit-scrollbar-track {
            background: #ffffff !important;
        }
        body.light-mode .codedex-terminal-output::-webkit-scrollbar-thumb {
            background: #cbd5e1 !important;
            border-radius: 4px;
        }
        body.light-mode .codedex-term-empty {
            color: #64748b !important;
        }

        /* Texto de Retorno do Terminal no Modo Claro com Contraste Alto (Nunca Duas Cores Claras) */
        body.light-mode .codedex-terminal-text.success-text {
            color: #166534 !important;
            font-weight: 700 !important;
        }
        body.light-mode .codedex-terminal-text.error-text {
            color: #b91c1c !important;
            font-weight: 700 !important;
        }
        body.light-mode .codedex-terminal-loading {
            color: #0284c7 !important;
        }

        /* Badges de Status do Terminal no Modo Claro */
        body.light-mode .terminal-status-badge {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
        }
        body.light-mode .terminal-status-badge.running {
            background: #e0f2fe !important;
            color: #0369a1 !important;
            border-color: #7dd3fc !important;
        }
        body.light-mode .terminal-status-badge.success {
            background: #dcfce7 !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }
        body.light-mode .terminal-status-badge.error {
            background: #fee2e2 !important;
            color: #b91c1c !important;
            border-color: #fca5a5 !important;
        }

        /* Botão "PROJETO CONCLUÍDO" no Modo Claro (Verde Sólido, NUNCA Preto) */
        body.light-mode .btn-projeto-entregue {
            background: #dcfce7 !important;
            color: #15803d !important;
            border: 1.5px solid #86efac !important;
            border-bottom: 4px solid #4ade80 !important;
            opacity: 1 !important;
        }

        body.light-mode .codedex-btn-reset,
        body.light-mode .btn-salvar-rascunho {
            background: #f1f5f9 !important;
            color: #475569 !important;
            border-color: #cbd5e1 !important;
        }
        body.light-mode .codedex-btn-reset:hover,
        body.light-mode .btn-salvar-rascunho:hover {
            background: #e2e8f0 !important;
            color: #0f172a !important;
        }
        body.light-mode .codedex-btn-run {
            background: #e0f2fe !important;
            color: #0284c7 !important;
            border-bottom: 4px solid #7dd3fc !important;
        }

        /* Modais no Modo Claro */
        body.light-mode .modal-conclusao-card {
            background: #ffffff !important;
            border-color: #86efac !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15) !important;
        }
        body.light-mode .badge-vitoria-chip {
            background: #dcfce7 !important;
            color: #15803d !important;
            border-color: #86efac !important;
        }
        body.light-mode .modal-conclusao-title {
            color: #0f172a !important;
        }
        body.light-mode .modal-conclusao-desc {
            color: #475569 !important;
        }

        body.light-mode .modal-erro-card {
            background: #ffffff !important;
            border-color: #fca5a5 !important;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15) !important;
        }
        body.light-mode .badge-erro-chip {
            background: #fee2e2 !important;
            color: #b91c1c !important;
            border-color: #fca5a5 !important;
        }
        body.light-mode .modal-erro-title {
            color: #0f172a !important;
        }
        body.light-mode .modal-erro-pergunta {
            color: #475569 !important;
        }
        body.light-mode .erro-detalhes-box {
            background: #fef2f2 !important;
            border-color: #fecaca !important;
            color: #991b1b !important;
        }
    </style>
</head>
<body>

    <!-- CONTAINER DE CONFETES/GLITTER ANIMADOS -->
    <div class="confetti-container" id="confettiContainer"></div>

    <div class="codedex-container">
        
        <!-- BARRA SUPERIOR (HEADER) -->
        <header class="codedex-topbar">
            <div class="codedex-topbar-left">
                <a href="desafios.php" class="codedex-btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Desafios
                </a>
                <div class="codedex-topbar-title">
                    <i class="fa-solid <?= htmlspecialchars($desafio['icone']) ?>" style="color: <?= htmlspecialchars($desafio['cor']) ?>;"></i>
                    <span>Projeto: <?= htmlspecialchars($desafio['titulo']) ?></span>
                </div>
            </div>

            <div class="codedex-topbar-right" style="display: flex; align-items: center; gap: 14px;">
                <button type="button" class="codedex-btn-back" id="ideThemeToggle" title="Alternar Modo Claro / Escuro" onclick="alternarTemaIde()">
                    <i class="fa-solid fa-sun" id="ideThemeIcon"></i>
                </button>
            </div>
        </header>

        <!-- ÁREA DE TRABALHO SPLIT-SCREEN -->
        <main class="codedex-workspace">
            
            <!-- PAINEL ESQUERDO: INSTRUÇÕES, REQUISITOS E TEORIA -->
            <section class="codedex-instructions-panel">
                
                <div>
                    <span class="codedex-lesson-badge">
                        <i class="fa-brands fa-java"></i> <?= htmlspecialchars($desafio['categoria']) ?> &bull; Nível <?= htmlspecialchars(ucfirst($desafio['dificuldade'])) ?>
                    </span>
                    <h1 class="codedex-lesson-heading"><?= htmlspecialchars($desafio['titulo']) ?></h1>
                </div>

                <!-- Banner caso o projeto já esteja concluído -->
                <?php if ($is_concluido): ?>
                    <div class="banner-projeto-concluido">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <h4>Projeto Já Concluído!</h4>
                            <p>Você já entregou este projeto com sucesso. Abaixo está o seu código final salvo.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="codedex-text-content">
                    <p><?= htmlspecialchars($desafio['descricao']) ?></p>
                </div>

                <!-- Box de Requisitos Prévios Colorido e Nítido -->
                <div class="codedex-box-requisitos">
                    <h4 class="box-requisitos-title">
                        <i class="fa-solid fa-graduation-cap"></i> Requisitos Aprendidos na Trilha
                    </h4>
                    <p>
                        <strong><?= htmlspecialchars($desafio['requisitos_titulo']) ?></strong><br>
                        <?= htmlspecialchars($desafio['requisitos_desc']) ?>
                    </p>
                </div>

                <!-- Missão Detalhada Colorida -->
                <div class="codedex-box-missao">
                    <h4 class="box-missao-title">
                        <i class="fa-solid fa-bullseye"></i> Missão do Desafio
                    </h4>
                    <p><?= nl2br(htmlspecialchars($desafio['missao_detalhada'])) ?></p>
                </div>

                <!-- Exemplo de Saída Esperada no Console -->
                <?php if (!empty($desafio['exemplo_saida'])): ?>
                    <div class="codedex-box-saida">
                        <h4 class="box-saida-title">
                            <i class="fa-solid fa-terminal"></i> Saída Esperada no Console
                        </h4>
                        <pre class="codedex-saida-pre"><?= htmlspecialchars($desafio['exemplo_saida']) ?></pre>
                    </div>
                <?php endif; ?>

                <!-- Dica do Mascote Expansível -->
                <div class="codedex-hint-section">
                    <button type="button" class="codedex-btn-hint" onclick="toggleDica()">
                        <i class="fa-solid fa-lightbulb"></i> <span id="hintBtnText">Ver Dica do Mascote</span>
                    </button>
                    
                    <div class="codedex-hint-card" id="desafioHintCard" style="display: none;">
                        <div class="codedex-hint-header">
                            <i class="fa-solid fa-comment-dots"></i> Dica de Código:
                        </div>
                        <p><?= htmlspecialchars($desafio['dica']) ?></p>
                    </div>
                </div>

            </section>

            <!-- PAINEL DIREITO: EDITOR E TERMINAL -->
            <section class="codedex-ide-panel">
                
                <!-- Aba do Arquivo -->
                <div class="codedex-editor-top">
                    <div class="codedex-tab-file">
                        <i class="fa-brands fa-java"></i> <?= htmlspecialchars($desafio['arquivo_nome']) ?>
                    </div>
                </div>

                <!-- Área do Ace Editor -->
                <div class="codedex-editor-area">
                    <div id="codedexAceEditor"></div>
                </div>

                <!-- Barra de Ações (Entre Editor e Terminal) -->
                <div class="codedex-ide-actions">
                    <div style="display: flex; gap: 10px;">
                        <button type="button" class="codedex-btn-reset" onclick="resetarCodigo()" title="Restaurar esqueleto inicial">
                            <i class="fa-solid fa-rotate-left"></i> Código Inicial
                        </button>
                        <button type="button" class="btn-salvar-rascunho" onclick="salvarRascunho()" id="btnSalvarRascunho" title="Salva seu progresso atual">
                            <i class="fa-solid fa-floppy-disk"></i> <span id="salvarText">Salvar Rascunho</span>
                        </button>
                    </div>

                    <div style="display: flex; gap: 12px; align-items: center;">
                        <button type="button" class="codedex-btn-run" id="btnRun" onclick="executarCodigo()">
                            <i class="fa-solid fa-play"></i> TESTAR CÓDIGO
                        </button>

                        <?php if ($is_concluido): ?>
                            <button type="button" class="btn-submeter-projeto btn-projeto-entregue" disabled title="Você já concluiu este projeto">
                                <i class="fa-solid fa-circle-check"></i> PROJETO CONCLUÍDO
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn-submeter-projeto" id="btnSubmeter" onclick="submeterDesafio()">
                                <i class="fa-solid fa-circle-check"></i> ENTREGAR PROJETO
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Painel do Terminal Redesenhado (Sem Lixeirinha, Visual Clean) -->
                <div class="codedex-terminal-panel">
                    <div class="terminal-header-redesigned">
                        <div class="terminal-header-left">
                            <div class="terminal-dots">
                                <span class="terminal-dot dot-red"></span>
                                <span class="terminal-dot dot-yellow"></span>
                                <span class="terminal-dot dot-green"></span>
                            </div>
                            <span class="terminal-title-text">
                                <i class="fa-solid fa-terminal"></i> Console Java
                            </span>
                        </div>
                        <div>
                            <span class="terminal-status-badge" id="termStatus">Aguardando Execução</span>
                        </div>
                    </div>

                    <div class="codedex-terminal-output" id="terminalOutput">
                        <div class="codedex-term-empty">
                            Clique em <strong>TESTAR CÓDIGO</strong> para compilar e rodar sua aplicação, ou em <strong>ENTREGAR PROJETO</strong> quando finalizar para validar os requisitos!
                        </div>
                    </div>
                </div>

            </section>

        </main>
    </div>

    <!-- Modal Festivo de Vitória com Animação (Apenas 1 Botão para Voltar à Aba de Desafios) -->
    <div class="modal-conclusao-overlay" id="modalVitoria">
        <div class="modal-conclusao-card modal-anim-vitoria">
            <div class="modal-conclusao-icone-topo">
                <div class="mascote-vitoria-wrapper">
                    <img src="../assets/img/acertoutudo.png" alt="Parabéns" class="mascote-vitoria-img">
                    <div class="vitoria-sparkle-1"><i class="fa-solid fa-star"></i></div>
                    <div class="vitoria-sparkle-2"><i class="fa-solid fa-star"></i></div>
                </div>
            </div>
            <div class="badge-vitoria-chip">
                <i class="fa-solid fa-circle-check"></i> DESAFIO CONCLUÍDO COM SUCESSO!
            </div>
            <h2 class="modal-conclusao-title"><?= htmlspecialchars($desafio['titulo']) ?></h2>

            <p class="modal-conclusao-desc" id="vitoriaDesc">
                Incrível! Seu código passou em todas as verificações e atende 100% aos requisitos solicitados.
            </p>

            <div class="modal-conclusao-actions">
                <a href="desafios.php" class="btn-modal-voltar-desafios">
                    <i class="fa-solid fa-arrow-left"></i> Voltar para a Aba de Desafios
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Didático de Erro / Ajuste com Pergunta ao Aluno -->
    <div class="modal-conclusao-overlay" id="modalErroValidacao">
        <div class="modal-erro-card modal-anim-entry">
            <div class="modal-erro-mascote">
                <img src="../assets/img/acertoumetade.png" alt="Atenção">
            </div>
            <div class="badge-erro-chip">
                <i class="fa-solid fa-triangle-exclamation"></i> AINDA NÃO ATENDE AOS REQUISITOS
            </div>
            <h2 class="modal-erro-title">Falta pouco para concluir!</h2>
            
            <div class="erro-detalhes-box" id="modalErroMensagem">
                <!-- Preenchido dinamicamente via JS com o que faltou -->
            </div>

            <p class="modal-erro-pergunta">
                Deseja fazer os ajustes no editor agora ou prefere praticar mais lições na trilha antes de tentar novamente?
            </p>

            <div class="modal-erro-actions">
                <button type="button" class="btn-modal-primary" onclick="fecharModalErro()">
                    <i class="fa-solid fa-pencil"></i> Ajustar Meu Código
                </button>
                <a href="dashboard.php" class="btn-modal-secondary" style="text-decoration: none;">
                    <i class="fa-solid fa-graduation-cap"></i> Praticar na Trilha
                </a>
            </div>
        </div>
    </div>

    <script src="../assets/js/opus_audio.js"></script>
    <script src="../assets/js/opus_alerta.js?v=<?= time() ?>"></script>

    <script>
        const DESAFIO_ID = <?= (int) $desafio['id'] ?>;
        const DEFAULT_CODE = <?= json_encode($desafio['codigo_inicial']) ?>;
        const CURRENT_SAVED_CODE = <?= json_encode($codigo_inicial) ?>;
        let aceEditor = null;
        let ultimaSaida = '';
        let ultimoCompilou = false;

        document.addEventListener('DOMContentLoaded', () => {
            // Sincroniza tema inicial com o localStorage
            const savedTheme = localStorage.getItem('opus_theme') || 'dark';
            const isLight = (savedTheme === 'light');
            if (isLight) {
                document.body.classList.add('light-mode');
                document.documentElement.classList.add('light-mode');
                const icon = document.getElementById('ideThemeIcon');
                if (icon) icon.className = 'fa-solid fa-moon';
            }

            // Inicializa o Ace Editor
            if (window.ace) {
                aceEditor = ace.edit("codedexAceEditor");
                aceEditor.setTheme(isLight ? "ace/theme/chrome" : "ace/theme/dracula");
                aceEditor.session.setMode("ace/mode/java");
                aceEditor.setOptions({
                    fontSize: "15px",
                    showPrintMargin: false,
                    showGutter: true,
                    highlightActiveLine: true,
                    tabSize: 4,
                    useSoftTabs: true,
                    enableBasicAutocompletion: true,
                    enableLiveAutocompletion: true
                });
                aceEditor.setValue(CURRENT_SAVED_CODE, -1);
                aceEditor.gotoLine(4, 9, true);

                setTimeout(() => { if (aceEditor) aceEditor.resize(); }, 150);
            }
        });

        // Alterna Modo Claro / Escuro de forma garantida
        function alternarTemaIde() {
            const isLight = document.body.classList.toggle('light-mode');
            document.documentElement.classList.toggle('light-mode', isLight);
            localStorage.setItem('opus_theme', isLight ? 'light' : 'dark');
            
            const icon = document.getElementById('ideThemeIcon');
            if (icon) {
                icon.className = isLight ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            }

            if (aceEditor) {
                aceEditor.setTheme(isLight ? 'ace/theme/chrome' : 'ace/theme/dracula');
            }

            if (window.OpusAudio) window.OpusAudio.playClick();
        }

        // Confetes animados ao vencer o desafio
        function launchConfetti() {
            const container = document.getElementById('confettiContainer');
            if (!container) return;
            const colors = ['#58cc02', '#1cb0f6', '#ffc800', '#ff4b4b', '#ce82ff', '#38bdf8'];

            for (let i = 0; i < 90; i++) {
                const piece = document.createElement('div');
                piece.className = 'confetti-piece';
                piece.style.left = (Math.random() * 100) + 'vw';
                piece.style.top = '-15px';
                piece.style.width = (Math.random() * 9 + 6) + 'px';
                piece.style.height = (Math.random() * 9 + 6) + 'px';
                piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                piece.style.borderRadius = Math.random() > 0.4 ? '50%' : '3px';
                piece.style.animation = `confettiFall ${Math.random() * 2 + 1.8}s cubic-bezier(0.25, 1, 0.5, 1) ${Math.random() * 0.4}s forwards`;
                container.appendChild(piece);

                setTimeout(() => piece.remove(), 4000);
            }
        }

        // Alterna visualização da dica
        function toggleDica() {
            const card = document.getElementById('desafioHintCard');
            const txt = document.getElementById('hintBtnText');
            if (card.style.display === 'none' || !card.style.display) {
                card.style.display = 'block';
                txt.textContent = 'Ocultar Dica';
                if (window.OpusAudio) window.OpusAudio.playClick();
            } else {
                card.style.display = 'none';
                txt.textContent = 'Ver Dica do Mascote';
            }
        }

        // Restaura esqueleto original
        function resetarCodigo() {
            if (confirm("Deseja redefinir o editor para o esqueleto inicial do desafio? Seu código atual será substituído.")) {
                if (aceEditor) {
                    aceEditor.setValue(DEFAULT_CODE, -1);
                    aceEditor.focus();
                }
                if (window.OpusAudio) window.OpusAudio.playClick();
                if (window.OpusAlerta) window.OpusAlerta.info("Código redefinido para o esqueleto inicial.");
            }
        }

        // Salva rascunho
        async function salvarRascunho() {
            if (!aceEditor) return;
            const codigo = aceEditor.getValue();
            const btn = document.getElementById('btnSalvarRascunho');
            const txt = document.getElementById('salvarText');

            txt.innerText = 'Salvando...';
            btn.disabled = true;

            try {
                const res = await fetch('../../back/api/desafios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'salvar_rascunho',
                        desafio_id: DESAFIO_ID,
                        codigo: codigo
                    })
                });
                const data = await res.json();
                if (data.sucesso) {
                    txt.innerText = 'Salvo!';
                    setTimeout(() => { txt.innerText = 'Salvar Rascunho'; btn.disabled = false; }, 1600);
                    if (window.OpusAlerta) window.OpusAlerta.sucesso("Rascunho salvo com sucesso!");
                } else {
                    txt.innerText = 'Erro';
                    btn.disabled = false;
                }
            } catch (e) {
                txt.innerText = 'Erro';
                btn.disabled = false;
            }
        }

        // Executa o código e mostra o resultado no terminal com contraste de cores perfeito
        async function executarCodigo() {
            if (!aceEditor) return;
            const codigo = aceEditor.getValue().trim();
            const term = document.getElementById('terminalOutput');
            const status = document.getElementById('termStatus');
            const btnRun = document.getElementById('btnRun');

            if (!codigo) {
                if (window.OpusAlerta) window.OpusAlerta.erro("Digite algum código antes de executar!");
                return;
            }

            btnRun.disabled = true;
            btnRun.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> EXECUTANDO...';
            status.className = 'terminal-status-badge running';
            status.innerText = 'Compilando...';

            term.innerHTML = '<div class="codedex-terminal-loading"><i class="fa-solid fa-spinner fa-spin"></i> Compilando e executando programa Java...</div>';

            try {
                const res = await fetch('../../back/api/executar_codigo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        codigo: codigo,
                        stdin: ''
                    })
                });
                const data = await res.json();

                btnRun.disabled = false;
                btnRun.innerHTML = '<i class="fa-solid fa-play"></i> TESTAR CÓDIGO';

                const stdout = (data.saida || data.program_output || '').trim();
                const erro = (data.erro_compilacao || data.erro_execucao || data.erro || '').trim();

                ultimaSaida = stdout;
                ultimoCompilou = !!data.sucesso;

                if (data.sucesso) {
                    status.className = 'terminal-status-badge success';
                    status.innerText = 'Execução OK (0)';
                    term.innerHTML = `<pre class="codedex-terminal-text success-text">${escapeHtml(stdout || '[Programa finalizado sem saída de texto]')}</pre>`;
                    if (window.OpusAudio) window.OpusAudio.playClick();
                } else {
                    status.className = 'terminal-status-badge error';
                    status.innerText = 'Erro de Execução';
                    term.innerHTML = `<pre class="codedex-terminal-text error-text">${escapeHtml(erro || 'Ocorreu um erro durante a compilação ou execução do código.')}</pre>`;
                    if (window.OpusAudio) window.OpusAudio.playError();
                }
            } catch (err) {
                btnRun.disabled = false;
                btnRun.innerHTML = '<i class="fa-solid fa-play"></i> TESTAR CÓDIGO';
                status.className = 'terminal-status-badge error';
                status.innerText = 'Falha de Conexão';
                term.innerHTML = `<div class="codedex-terminal-text error-text">Erro ao comunicar com o compilador: ${escapeHtml(err.message)}</div>`;
                if (window.OpusAudio) window.OpusAudio.playError();
            }
        }

        // Submete e valida a solução do desafio
        async function submeterDesafio() {
            if (!aceEditor) return;
            const codigo = aceEditor.getValue().trim();
            const btnSubmeter = document.getElementById('btnSubmeter');
            const term = document.getElementById('terminalOutput');
            const status = document.getElementById('termStatus');

            if (!codigo) {
                if (window.OpusAlerta) window.OpusAlerta.erro("O editor está vazio!");
                return;
            }

            btnSubmeter.disabled = true;
            btnSubmeter.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> VALIDANDO...';

            // 1. Executa primeiro para obter a saída atualizada
            await executarCodigo();

            // 2. Submete para verificação de requisitos
            try {
                const res = await fetch('../../back/api/desafios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'submeter',
                        desafio_id: DESAFIO_ID,
                        codigo: codigo,
                        saida: ultimaSaida,
                        sucesso_compilacao: ultimoCompilou
                    })
                });
                const data = await res.json();

                btnSubmeter.disabled = false;
                btnSubmeter.innerHTML = '<i class="fa-solid fa-circle-check"></i> ENTREGAR PROJETO';

                if (data.sucesso) {
                    status.className = 'terminal-status-badge success';
                    status.innerText = 'Projeto Aprovado!';

                    // Dispara a chuva de glitter/confetes
                    launchConfetti();

                    // Abre modal de vitória com animação e apenas o botão de voltar
                    const modal = document.getElementById('modalVitoria');
                    const desc = document.getElementById('vitoriaDesc');
                    desc.innerText = data.mensagem || "Parabéns! Sua solução atende a todos os requisitos do projeto.";
                    modal.style.display = 'flex';

                    if (window.OpusAudio) {
                        window.OpusAudio.playVictory();
                    }
                } else {
                    status.className = 'terminal-status-badge error';
                    status.innerText = 'Requer Ajustes';

                    // Abre modal de erro animado perguntando se quer ajustar ou praticar
                    const modalErro = document.getElementById('modalErroValidacao');
                    const boxErro = document.getElementById('modalErroMensagem');
                    
                    let msgFormatada = escapeHtml(data.erro || 'A saída do programa ainda não atende aos requisitos.');
                    if (data.dica) {
                        msgFormatada += `<div style="margin-top: 8px; color: #fde047;"><strong>💡 Dica do Mascote:</strong> ${escapeHtml(data.dica)}</div>`;
                    }
                    boxErro.innerHTML = msgFormatada;
                    modalErro.style.display = 'flex';

                    if (window.OpusAudio) window.OpusAudio.playError();
                }
            } catch (e) {
                btnSubmeter.disabled = false;
                btnSubmeter.innerHTML = '<i class="fa-solid fa-circle-check"></i> ENTREGAR PROJETO';
                if (window.OpusAlerta) window.OpusAlerta.erro("Erro ao submeter desafio: " + e.message);
            }
        }

        function fecharModalErro() {
            const modal = document.getElementById('modalErroValidacao');
            if (modal) modal.style.display = 'none';
            if (aceEditor) aceEditor.focus();
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
