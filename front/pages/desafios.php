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

// Administrador é redirecionado para o painel admin
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

require_once __DIR__ . '/../../back/lib/jogador_status.php';
require_once __DIR__ . '/../../back/lib/desafios_logic.php';

$user_id = (int) $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);

// Carrega os desafios e o status do usuário
$dados_desafios = desafios_listar($conn, $user_id);
$lista_desafios = $dados_desafios['desafios'];
$progresso_trilha = $dados_desafios['progresso_trilha'];
$secao_atual_usuario = max(1, $progresso_trilha['max_secao_alcancada']);

// Calcula taxa de conclusão dos projetos
$taxa_conclusao = ($dados_desafios['total'] > 0) ? round(($dados_desafios['concluidos_count'] / $dados_desafios['total']) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafios & Projetos Práticos - Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Orbitron:wght@500;700;900&family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/desafios.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/opus_alerta.css?v=<?= time() ?>">
</head>
<body>

    <div class="app-container">
        
        <!-- Sidebar lateral padrão do Opus com a nova aba Desafios -->
        <?php include __DIR__ . '/../../back/includes/sidebar.php'; ?>

        <main class="main-content">
            
            <!-- Topbar padrão com vidas, troféus, ofensiva de fogo e perfil -->
            <?php include __DIR__ . '/../../back/includes/topbar.php'; ?>

            <div class="desafios-container">
                
                <!-- Hero Promocional dos Projetos -->
                <section class="desafios-hero">
                    <div class="desafios-hero-content">
                        <div class="desafios-hero-badge">
                            <i class="fa-solid fa-laptop-code"></i> Laboratório de Código
                        </div>
                        <h1 class="desafios-hero-title">
                            Projetos Práticos & <span>Desafios Java</span>
                        </h1>
                        <p class="desafios-hero-desc">
                            Coloque suas habilidades em prática construindo pequenos projetos reais! 
                            Conforme você conclui as seções na trilha de aprendizado, novos desafios como 
                            <strong>calculadoras</strong>, <strong>conversores</strong> e <strong>sistemas modulares</strong> são desbloqueados.
                        </p>
                    </div>
                    <div class="desafios-hero-mascote">
                        <img src="../assets/img/vistodecimaazul.png" alt="Mascote Opi">
                    </div>
                </section>

                <!-- Cards de Estatísticas Rápidas -->
                <div class="desafios-stats-grid">
                    <div class="desafio-stat-card">
                        <div class="desafio-stat-icon" style="background: rgba(28, 176, 246, 0.12); color: #1cb0f6;">
                            <i class="fa-solid fa-diagram-project"></i>
                        </div>
                        <div class="desafio-stat-info">
                            <span class="desafio-stat-val"><?= $dados_desafios['total'] ?></span>
                            <span class="desafio-stat-label">Total de Projetos</span>
                        </div>
                    </div>

                    <div class="desafio-stat-card">
                        <div class="desafio-stat-icon" style="background: rgba(88, 204, 2, 0.12); color: #58cc02;">
                            <i class="fa-solid fa-lock-open"></i>
                        </div>
                        <div class="desafio-stat-info">
                            <span class="desafio-stat-val"><?= $dados_desafios['desbloqueados_count'] ?></span>
                            <span class="desafio-stat-label">Desbloqueados</span>
                        </div>
                    </div>

                    <div class="desafio-stat-card">
                        <div class="desafio-stat-icon" style="background: rgba(206, 130, 255, 0.12); color: #ce82ff;">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="desafio-stat-info">
                            <span class="desafio-stat-val"><?= $dados_desafios['concluidos_count'] ?> / <?= $dados_desafios['total'] ?></span>
                            <span class="desafio-stat-label">Concluídos</span>
                        </div>
                    </div>

                    <div class="desafio-stat-card">
                        <div class="desafio-stat-icon" style="background: rgba(255, 150, 0, 0.12); color: #ff9600;">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="desafio-stat-info">
                            <span class="desafio-stat-val"><?= $taxa_conclusao ?>%</span>
                            <span class="desafio-stat-label">Taxa de Conclusão</span>
                        </div>
                    </div>
                </div>

                <!-- Barra de Filtros -->
                <div class="desafios-filter-bar">
                    <div class="desafios-tabs">
                        <button class="desafios-tab-btn active" data-filtro="todos">
                            <i class="fa-solid fa-layer-group"></i> Todos
                            <span class="badge-count"><?= $dados_desafios['total'] ?></span>
                        </button>
                        <button class="desafios-tab-btn" data-filtro="disponivel">
                            <i class="fa-solid fa-unlock"></i> Disponíveis
                            <span class="badge-count"><?= $dados_desafios['desbloqueados_count'] ?></span>
                        </button>
                        <button class="desafios-tab-btn" data-filtro="concluido">
                            <i class="fa-solid fa-check-double"></i> Concluídos
                            <span class="badge-count"><?= $dados_desafios['concluidos_count'] ?></span>
                        </button>
                        <button class="desafios-tab-btn" data-filtro="bloqueado">
                            <i class="fa-solid fa-lock"></i> Bloqueados
                            <span class="badge-count"><?= $dados_desafios['bloqueados_count'] ?></span>
                        </button>
                    </div>
                </div>

                <!-- Grid dos Desafios -->
                <div class="desafios-grid" id="desafiosGrid">
                    <?php foreach ($lista_desafios as $desafio): 
                        $status = $desafio['status'];
                        $is_concluido = ($status === 'concluido');
                        $is_bloqueado = ($status === 'bloqueado');
                        $card_class = $is_concluido ? 'card-concluido' : ($is_bloqueado ? 'card-bloqueado' : 'card-disponivel');
                    ?>
                        <article class="desafio-card <?= $card_class ?>" data-status="<?= $status ?>" data-secao="<?= $desafio['secao_requerida'] ?>">
                            
                            <!-- Topo do Card -->
                            <div class="desafio-card-header">
                                <?php if ($is_bloqueado): ?>
                                    <div class="desafio-icon-wrapper desafio-icon-locked" title="Desafio Bloqueado - Requer Seção <?= $desafio['secao_requerida'] ?>">
                                        <i class="fa-solid fa-lock"></i>
                                    </div>
                                    <div class="desafio-badges-group">
                                        <span class="badge-dificuldade badge-locked">
                                            <i class="fa-solid fa-lock"></i> <?= htmlspecialchars($desafio['dificuldade']) ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="desafio-icon-wrapper" style="background: <?= $desafio['cor'] ?>22; color: <?= $desafio['cor'] ?>; border: 1px solid <?= $desafio['cor'] ?>44;">
                                        <i class="fa-solid <?= htmlspecialchars($desafio['icone']) ?>"></i>
                                    </div>
                                    <div class="desafio-badges-group">
                                        <span class="badge-dificuldade <?= $desafio['dificuldade'] ?>">
                                            <?= htmlspecialchars($desafio['dificuldade']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Corpo do Card -->
                            <div class="desafio-card-body">
                                <span class="desafio-card-categoria"><?= htmlspecialchars($desafio['categoria']) ?></span>
                                <h3 class="desafio-card-titulo"><?= htmlspecialchars($desafio['titulo']) ?></h3>
                                <p class="desafio-card-desc"><?= htmlspecialchars($desafio['subtitulo']) ?></p>

                                <!-- Box de Requisitos de Aprendizado -->
                                <div class="desafio-requisito-box <?= $is_concluido ? 'completed' : ($is_bloqueado ? 'locked' : 'unlocked') ?>">
                                    <div class="desafio-requisito-status">
                                        <?php if ($is_concluido): ?>
                                            <i class="fa-solid fa-circle-check"></i> Projeto Concluído!
                                        <?php elseif (!$is_bloqueado): ?>
                                            <i class="fa-solid fa-lock-open"></i> Desbloqueado (Requisitos atendidos)
                                        <?php else: ?>
                                            <i class="fa-solid fa-lock" style="font-size: 1.15rem;"></i> Bloqueado: Requer <?= htmlspecialchars($desafio['requisitos_titulo']) ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="desafio-requisito-desc">
                                        <?= htmlspecialchars($desafio['requisitos_desc']) ?>
                                    </div>

                                    <?php if ($is_bloqueado): ?>
                                        <div class="desafio-progresso-bar-mini" title="Seção atual: <?= $secao_atual_usuario ?> de <?= $desafio['secao_requerida'] ?>">
                                            <div class="desafio-progresso-bar-fill" style="width: <?= $desafio['progresso_porcentagem'] ?>%;"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Rodapé com Ação -->
                            <div class="desafio-card-footer">
                                <?php if ($is_concluido): ?>
                                    <a href="desafio_ide.php?slug=<?= urlencode($desafio['slug']) ?>" class="btn-desafio-action btn-desafio-concluido">
                                        <i class="fa-solid fa-circle-check"></i> Ver Código Concluído
                                    </a>
                                <?php elseif (!$is_bloqueado): ?>
                                    <a href="desafio_ide.php?slug=<?= urlencode($desafio['slug']) ?>" class="btn-desafio-action btn-desafio-iniciar">
                                        <i class="fa-solid fa-code"></i> Iniciar Desafio
                                    </a>
                                <?php else: ?>
                                    <button type="button" class="btn-desafio-action btn-desafio-bloqueado" 
                                            onclick="abrirModalBloqueado('<?= htmlspecialchars(addslashes($desafio['titulo'])) ?>', '<?= htmlspecialchars(addslashes($desafio['requisitos_titulo'])) ?>', <?= (int)$desafio['secao_requerida'] ?>, <?= (int)$secao_atual_usuario ?>)">
                                        <i class="fa-solid fa-lock"></i> Requer Seção <?= $desafio['secao_requerida'] ?>
                                    </button>
                                <?php endif; ?>
                            </div>

                        </article>
                    <?php endforeach; ?>
                </div>

            </div>
        </main>
    </div>

    <!-- Modal Informativo para Desafio Bloqueado -->
    <div class="desafio-modal-overlay" id="modalBloqueado">
        <div class="desafio-modal-box">
            <div class="desafio-modal-icon">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h3 class="desafio-modal-title" id="modalTitulo">Desafio Bloqueado</h3>
            <p class="desafio-modal-desc" id="modalDesc">
                Para destravar este projeto, você precisa completar a seção correspondente na trilha de aprendizado.
            </p>
            <div class="desafio-modal-actions">
                <a href="dashboard.php" class="btn-modal-primary">
                    <i class="fa-solid fa-graduation-cap"></i> Ir para a Trilha
                </a>
                <button type="button" class="btn-modal-secondary" onclick="fecharModalBloqueado()">
                    Entendi
                </button>
            </div>
        </div>
    </div>

    <script src="../assets/js/opus_audio.js"></script>
    <script src="../assets/js/opus_alerta.js?v=<?= time() ?>"></script>
    <script src="../assets/js/desafios.js?v=<?= time() ?>"></script>
</body>
</html>
