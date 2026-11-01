<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../../back/config/conexao.php';
require_once '../../back/lib/jogador_status.php';
require_once '../../back/lib/mascotes_capitulos.php';
require_once '../../back/lib/missoes_logic.php';

$is_streak_preview = isset($_GET['preview_streak']);
$is_meta_preview   = isset($_GET['preview_meta']) || isset($_GET['meta_diaria']) || isset($_GET['meta_teste']);

if (!isset($_SESSION['user_id']) && !$is_streak_preview && !$is_meta_preview) {
    header("Location: dashboard.php");
    exit();
}

if (!isset($_POST['acertos']) && !$is_streak_preview && !$is_meta_preview) {
    header("Location: dashboard.php");
    exit();
}

if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = (int) ($_SESSION['user_id'] ?? 1);
$acertos = (int) ($_POST['acertos'] ?? 5);
$cap_atual = (int) ($_POST['cap'] ?? ($_GET['cap'] ?? 1));
$licao_atual = (int) ($_POST['licao'] ?? ($_GET['licao'] ?? 1));

if (($cap_atual <= 0 || $licao_atual <= 0) && !empty($_POST['licao_id'])) {
    $lid_lookup = (int) $_POST['licao_id'];
    $res_lk = $conn->query("SELECT unidade_numero, licao_numero FROM licoes WHERE id = $lid_lookup LIMIT 1");
    if ($res_lk && ($row_lk = $res_lk->fetch_assoc())) {
        $cap_atual = (int) $row_lk['unidade_numero'];
        $licao_atual = (int) $row_lk['licao_numero'];
    }
}
if ($cap_atual <= 0) $cap_atual = 1;
if ($licao_atual <= 0) $licao_atual = 1;

$attempt_token = preg_replace('/[^a-f0-9]/', '', (string) ($_POST['attempt_token'] ?? ''));

$used = $_SESSION['used_attempts'] ?? [];
$ja_processado = ($attempt_token !== '' && in_array($attempt_token, $used, true));
$perdeu_vida = false;
$vidas_restantes = 3;

if (!$ja_processado && $attempt_token !== '') {
    $used[] = $attempt_token;
    $_SESSION['used_attempts'] = array_slice($used, -40);
}

// Mascote/cor do capítulo (mesmo mapeamento usado no dashboard e na lição)
$mascote_capitulo = opus_mascote_do_capitulo($cap_atual);
$cor_tema = $mascote_capitulo['cor'];

$total_questoes = isset($_POST['total_questoes']) ? (int)$_POST['total_questoes'] : 6;
if ($total_questoes <= 0) $total_questoes = 6;

// 1. Mensagens e Imagens PNG Dinâmicas com base na taxa de acertos
$taxa_acertos = ($total_questoes > 0) ? ($acertos / $total_questoes) : 1;

if ($taxa_acertos >= 1.0) {
    $mensagem = "Perfeito! Você dominou 100% do conteúdo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['feliz'];
    $xp_ganho = 50;
} elseif ($taxa_acertos >= 0.8) {
    $mensagem = "Muito bom! Quase perfeito!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['feliz'];
    $xp_ganho = 40;
} elseif ($taxa_acertos >= 0.5) {
    $mensagem = "Bom trabalho! Você está no caminho certo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['explicando'];
    $xp_ganho = 25;
} elseif ($taxa_acertos >= 0.2) {
    $mensagem = "Foi por pouco! Que tal revisar o conteúdo?";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['triste'];
    $xp_ganho = 15;
} else {
    $mensagem = "Não desanime! A programação exige prática. Tente novamente!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['triste'];
    $xp_ganho = 10; 
}

// Ofensiva de fogo: detecta se ESTA lição foi a que acendeu o fogo de hoje
// (antes: ultima_atividade != hoje / depois: ultima_atividade == hoje).
$fogo_acendeu_agora = false;
$dias_fogo_atual = 0;
$ler_ultima_atividade = function () use ($conn, $user_id) {
    $st = $conn->prepare("SELECT dias_fogo, (ultima_atividade = CURDATE()) AS aceso_hoje, WEEKDAY(CURDATE()) AS dia_semana FROM usuarios WHERE id = ?");
    $st->bind_param("i", $user_id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc() ?: [];
    $st->close();
    return $row;
};
$fogo_antes = $ler_ultima_atividade();
$fogo_ja_aceso_hoje = ((int) ($fogo_antes['aceso_hoje'] ?? 0) === 1);

// Estado das missões diárias ANTES de processar a lição
$missoes_antes = missoes_obter_hoje($conn, $user_id);
$todas_metas_antes = missoes_todas_concluidas($missoes_antes);
$xp_antes_hoje = 0;
foreach ($missoes_antes as $m) {
    if ($m['id'] === 'xp_50') $xp_antes_hoje = (int) $m['atual'];
}

if (!$ja_processado) {
    // Atualiza XP, liga, missão, sequência de fogo, troféus e avanço de progresso
    $res_proc = opus_call($conn, "CALL sp_processar_resultado_licao(?, ?, ?, ?, ?)", "iiiii", [$user_id, $cap_atual, $licao_atual, $acertos, $total_questoes]);

    if ($res_proc && isset($res_proc['xp_ganho'])) {
        $xp_ganho = (int) $res_proc['xp_ganho'];
    }
    if ($res_proc && (int) ($res_proc['eh_repeticao'] ?? 0) === 1) {
        $mensagem .= " (Lição já concluída antes — XP reduzido.)";
    }

    // Registra os acertos de hoje no sistema de batalha entre amigos
    if ($acertos > 0) {
        $data_hoje = date('Y-m-d');
        $conn->query("CREATE TABLE IF NOT EXISTS `acertos_diarios` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `usuario_id` INT NOT NULL,
            `data_dia` DATE NOT NULL,
            `acertos` INT DEFAULT 0,
            UNIQUE KEY `uq_user_dia` (`usuario_id`, `data_dia`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $conn->query("INSERT INTO acertos_diarios (usuario_id, data_dia, acertos) VALUES ($user_id, '$data_hoje', $acertos) ON DUPLICATE KEY UPDATE acertos = acertos + $acertos");
    }

    if ($acertos === 0) {
        $vidas_restantes = opus_perder_vida($conn, $user_id);
        $perdeu_vida = true;
        $mensagem .= " Você perdeu 1 vida.";
        if ($vidas_restantes <= 0) {
            $mensagem .= " Sem vidas: cada coração volta a cada 5 horas.";
        }
    }
} else {
    $status_atual = opus_sincronizar_jogador($conn, $user_id);
    $vidas_restantes = (int) $status_atual['vidas'];
}

// 4. Descobre qual é a próxima lição para o Botão "Continuar"
$prox_licao = $licao_atual + 1;
$prox_cap_link = $cap_atual;

if ($licao_atual == 5) {
    $prox_licao = 1;
    $prox_cap_link = $cap_atual + 1;
}
$is_curso_finalizado = ($cap_atual >= 40 && $licao_atual == 5);
$liberou_bau = ($licao_atual == 3 && $acertos > 0);
$desbloqueou_secao = ($licao_atual == 5 && $acertos > 0 && !$is_curso_finalizado);

// Dados da transição de seção (ex: Seção 1 Azul #1cb0f6 -> Seção 2 Rosa #ff527b)
$mascote_prox_capitulo = opus_mascote_do_capitulo($prox_cap_link);
$cor_origem = $mascote_capitulo['cor'];
$cor_destino = $mascote_prox_capitulo['cor'];
$img_mascote_origem = "../assets/img/" . $mascote_capitulo['feliz'];
$img_mascote_destino = "../assets/img/" . $mascote_prox_capitulo['feliz'];

if ($licao_atual == 5 && $acertos > 0) {
    $mensagem = "Parabéns! Você concluiu o desafio prático no terminal e finalizou a Seção {$cap_atual}!";
    if ($res_proc && (int) ($res_proc['eh_repeticao'] ?? 0) === 1) {
        $mensagem .= " (Desafio já concluído antes — XP reduzido.)";
    }
}

$status_atual = opus_sincronizar_jogador($conn, $user_id);
$vidas_restantes = (int) $status_atual['vidas'];

// Resultado da ofensiva após processar a lição
$fogo_depois = $ler_ultima_atividade();
$dias_fogo_atual = (int) ($fogo_depois['dias_fogo'] ?? 0);
$fogo_acendeu_agora = !$ja_processado
    && !$fogo_ja_aceso_hoje
    && ((int) ($fogo_depois['aceso_hoje'] ?? 0) === 1);

if ($is_streak_preview) {
    $fogo_acendeu_agora = true;
    $dias_fogo_atual = isset($_GET['dias']) ? max(1, (int)$_GET['dias']) : 1;
}

if ($fogo_acendeu_agora && $dias_fogo_atual < 1) {
    $dias_fogo_atual = 1;
}

// Semana atual (Seg → Dom). Acende os dias cobertos pela sequência
// (hoje e os (dias_fogo - 1) dias anteriores); hoje é destacado e os
// dias futuros ficam cinzas para incentivar a continuar.
$semana_fogo = [];
if ($fogo_acendeu_agora) {
    $nomes_dias = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    $letras_dias = ['S', 'T', 'Q', 'Q', 'S', 'S', 'D'];
    // WEEKDAY() do MySQL: 0 = segunda (mesma data usada pela procedure)
    $idx_hoje = (int) ($fogo_depois['dia_semana'] ?? ((int) date('N') - 1));
    for ($i = 0; $i < 7; $i++) {
        $diff = $i - $idx_hoje; // negativo = passado
        $semana_fogo[] = [
            'nome'  => $nomes_dias[$i],
            'letra' => $letras_dias[$i],
            'hoje'  => ($diff === 0),
            'aceso' => ($diff <= 0 && -$diff < $dias_fogo_atual),
        ];
    }
}

// Resultado das missões diárias após o processamento da lição
$missoes_depois = missoes_obter_hoje($conn, $user_id);
$todas_metas_depois = missoes_todas_concluidas($missoes_depois);
$xp_depois_hoje = 0;
foreach ($missoes_depois as $m) {
    if ($m['id'] === 'xp_50') $xp_depois_hoje = (int) $m['atual'];
}

// Bateu TODAS as metas diárias exatamente nesta lição (ou no modo preview)
$meta_diaria_alcancada = (!$ja_processado && !$todas_metas_antes && $todas_metas_depois);

if ($is_meta_preview) {
    $meta_diaria_alcancada = true;
    if ($xp_depois_hoje < 50) {
        $xp_depois_hoje = 140; // réplica do print enviado pelo usuário (140 / 100 XP)
    }
}
$xp_meta_alvo = 50;

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultado - Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    <link rel="stylesheet" href="../assets/css/resultado.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/light-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>
<body style="--cor-tema: <?php echo $cor_tema; ?>;">
        <div class="app-container">
        
        <main class="main-content" style="width: 100%; margin-left: 0;">
            <?php include '../../back/includes/topbar.php'; ?>
            <div class="result-container">
                <div class="result-card" id="mainResultCard">
                    <div class="result-mascote-wrapper">
                        <img src="<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-img" id="resultMascoteImg">
                    </div>
                    
                    <?php if ($licao_atual == 5 && !$is_curso_finalizado): ?>
                        <h1 class="result-title" id="mainResultTitle">Você desbloqueou a Seção <?php echo $prox_cap_link; ?></h1>
                    <?php elseif ($licao_atual == 5 && $is_curso_finalizado): ?>
                        <h1 class="result-title" id="mainResultTitle">Curso Concluído!</h1>
                    <?php else: ?>
                        <h1 class="result-title" id="mainResultTitle"><?php echo $acertos; ?> / <?php echo $total_questoes; ?> Acertos</h1>
                    <?php endif; ?>
                    <p class="result-message"><?php echo $mensagem; ?></p>
                    
                    <div class="xp-box">
                        +<?php echo $xp_ganho; ?> XP
                    </div>

                    <?php if ($perdeu_vida): ?>
                        <p class="result-message" style="color:#ff8a8a; margin-bottom: 16px;">
                            <i class="fa-solid fa-heart-crack"></i>
                            <?php echo $vidas_restantes > 0
                                ? "Vidas restantes: {$vidas_restantes}/3. Cada coração volta a cada 5h."
                                : "Você ficou sem vidas. Aguarde 5h para recuperar cada coração."; ?>
                        </p>
                    <?php endif; ?>

                    <div class="buttons">
                        <?php if ($liberou_bau): ?>
                            <a href="dashboard.php?abrir_bau=<?php echo $cap_atual; ?>" class="btn-action btn-solid-gold">
                                <i class="fa-solid fa-arrows-rotate"></i> GIRAR ROLETA DO BAÚ
                            </a>
                            <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-solid-green">
                                Próxima Lição <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        <?php elseif ($licao_atual == 5): ?>
                            <a href="dashboard.php" class="btn-action btn-solid-dash"><i class="fa-solid fa-house"></i> Voltar ao dashboard</a>
                            <?php if ($acertos > 0 && !$is_curso_finalizado): ?>
                                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-solid-green">
                                    Ir para a lição <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn-action btn-solid-dash"><i class="fa-solid fa-house"></i> Dashboard</a>
                            <?php if ($acertos > 0): ?>
                                <?php if (!$is_curso_finalizado): ?>
                                    <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-solid-green">
                                        Próxima Lição <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="conquistas.php" class="btn-action btn-solid-green">Ver Troféu! <i class="fa-solid fa-trophy"></i></a>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($vidas_restantes > 0): ?>
                                    <a href="licao.php?cap=<?php echo $cap_atual; ?>&licao=<?php echo $licao_atual; ?>" class="btn-action btn-solid-green">
                                        Tentar Novamente <i class="fa-solid fa-rotate-right"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="dashboard.php?sem_vidas=1" class="btn-action btn-solid-dash">
                                        Sem vidas <i class="fa-solid fa-heart-crack"></i>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($liberou_bau): ?>
    <!-- POPUP ANIMADO DE DESBLOQUEIO DO BAÚ (DIRETO NA TELA SEM ROLAGEM) -->
    <div id="modalDesbloqueioBau" class="popup-bau-overlay active" role="dialog" aria-modal="true">
        <div class="popup-bau-card">
            <button type="button" class="popup-bau-close" onclick="fecharPopupBau()" title="Fechar"><i class="fa-solid fa-xmark"></i></button>
            
            <div class="popup-bau-stage">
                <div class="cartoon-chest-preview">
                    <div class="cc-lid"></div>
                    <div class="cc-body">
                        <span class="cc-clasp"></span>
                    </div>
                </div>
                <div class="popup-bau-sparkles">
                    <i class="fa-solid fa-star s1"></i>
                    <i class="fa-solid fa-star s2"></i>
                    <i class="fa-solid fa-sparkles s3"></i>
                </div>
            </div>

            <span class="popup-bau-badge"><i class="fa-solid fa-crown"></i> NOVO BAÚ DESBLOQUEADO</span>
            <h2 class="popup-bau-title">Você desbloqueou a Roleta do Baú!</h2>
            <p class="popup-bau-desc">
                Parabéns! Você concluiu a 3ª lição da <strong>Unidade <?php echo $cap_atual; ?></strong>. Gire a <strong>Roleta do Baú</strong> agora para concorrer a vários prêmios de <strong>3 XP até 25 XP (Máximo)</strong> ou <strong>Corações de Vida</strong>!
            </p>

            <div class="popup-bau-buttons">
                <a href="dashboard.php?abrir_bau=<?php echo $cap_atual; ?>" class="btn-solid btn-solid-gold">
                    <i class="fa-solid fa-arrows-rotate"></i> GIRAR ROLETA DO BAÚ
                </a>
                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-solid btn-solid-green">
                    <i class="fa-solid fa-arrow-right"></i> CONTINUAR LIÇÃO
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($meta_diaria_alcancada): ?>
    <!-- META DIÁRIA ALCANÇADA EM TELA INTEIRA (ESTILO MIMO) -->
    <div class="daily-goal-overlay" id="dailyGoalOverlay" role="dialog" aria-modal="true" aria-labelledby="dailyGoalTitle">
        <div class="daily-goal-bg-glow" aria-hidden="true"></div>
        <div class="daily-goal-confetti-container" id="dailyGoalConfetti" aria-hidden="true"></div>

        <div class="daily-goal-center-stage">
            <!-- Palco da Barra Circular (Gauge Aberto no Rodapé) -->
            <div class="daily-goal-gauge-stage">
                <svg class="daily-goal-svg" viewBox="0 0 220 220" aria-hidden="true">
                    <defs>
                        <linearGradient id="mimoGoldGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#fff176"/>
                            <stop offset="40%" stop-color="#ffd54f"/>
                            <stop offset="80%" stop-color="#ffb300"/>
                            <stop offset="100%" stop-color="#ff8f00"/>
                        </linearGradient>
                    </defs>
                    <!-- Trilho de fundo (abertura inferior de ~100°) -->
                    <circle cx="110" cy="110" r="85" class="gauge-track"
                            stroke-width="14" fill="none" stroke-linecap="round"
                            stroke-dasharray="385.7 534.1"
                            transform="rotate(140 110 110)"/>
                    <!-- Barra de progresso preenchida e iluminada em dourado -->
                    <circle cx="110" cy="110" r="85" class="gauge-fill"
                            stroke-width="14" fill="none" stroke-linecap="round"
                            stroke-dasharray="385.7 534.1"
                            transform="rotate(140 110 110)"/>
                </svg>

                <!-- Emblema de Cofre Metálico Centralizado (Estilo Mimo) -->
                <div class="daily-goal-emblem-wrap">
                    <svg class="daily-goal-emblem-svg" viewBox="0 0 100 100" aria-hidden="true">
                        <defs>
                            <linearGradient id="vaultHexGrad" x1="0.2" y1="0" x2="0.8" y2="1">
                                <stop offset="0%" stop-color="#4e5579"/>
                                <stop offset="50%" stop-color="#323753"/>
                                <stop offset="100%" stop-color="#23263d"/>
                            </linearGradient>
                            <linearGradient id="vaultMetalGrad" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#cbd5e1"/>
                                <stop offset="50%" stop-color="#94a3b8"/>
                                <stop offset="100%" stop-color="#64748b"/>
                            </linearGradient>
                            <linearGradient id="vaultGlowGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                        <!-- Hexágono base chanfrado -->
                        <polygon points="50,6 88,28 88,72 50,94 12,72 12,28"
                                 fill="url(#vaultHexGrad)" stroke="#68729e" stroke-width="3" stroke-linejoin="round"/>
                        <!-- Brilho superior chanfrado -->
                        <polygon points="50,9 85,29 85,38 50,18 15,38 15,29"
                                 fill="url(#vaultGlowGrad)"/>
                        <!-- Anel do cofre -->
                        <circle cx="50" cy="50" r="27" fill="none" stroke="url(#vaultMetalGrad)" stroke-width="5"/>
                        <circle cx="50" cy="50" r="21" fill="#1e2136" stroke="#475073" stroke-width="2"/>
                        <!-- Raios / manetes do volante do cofre -->
                        <line x1="50" y1="50" x2="50" y2="16" stroke="url(#vaultMetalGrad)" stroke-width="4.5" stroke-linecap="round"/>
                        <line x1="50" y1="50" x2="79" y2="67" stroke="url(#vaultMetalGrad)" stroke-width="4.5" stroke-linecap="round"/>
                        <line x1="50" y1="50" x2="21" y2="67" stroke="url(#vaultMetalGrad)" stroke-width="4.5" stroke-linecap="round"/>
                        <!-- Centro do volante com rebite -->
                        <circle cx="50" cy="50" r="8.5" fill="url(#vaultMetalGrad)" stroke="#475569" stroke-width="1.5"/>
                        <circle cx="50" cy="50" r="4" fill="#1e2136"/>
                        <!-- Puxadores / esferas nas pontas dos raios -->
                        <circle cx="50" cy="16" r="4" fill="url(#vaultMetalGrad)"/>
                        <circle cx="79" cy="67" r="4" fill="url(#vaultMetalGrad)"/>
                        <circle cx="21" cy="67" r="4" fill="url(#vaultMetalGrad)"/>
                    </svg>
                </div>
            </div>

            <!-- Contador de XP Abaixo da Abertura da Barra (ex: 140 / 50 XP) -->
            <div class="daily-goal-xp-counter">
                <span class="xp-cur"><?php echo $xp_depois_hoje; ?></span>
                <span class="xp-sep">/</span>
                <span class="xp-max"><?php echo $xp_meta_alvo; ?> XP</span>
            </div>

            <!-- Título e Subtítulo (idênticos à referência do Mimo) -->
            <h1 class="daily-goal-hero-title" id="dailyGoalTitle">Meta diária alcançada!</h1>
            <p class="daily-goal-hero-subtitle">Parabéns por ter alcançado sua meta diária. Continue assim!</p>

            <!-- Chips demonstrando as 3 metas diárias concluídas -->
            <div class="daily-goal-chips">
                <div class="daily-goal-chip" title="Meta de XP diário alcançada">
                    <i class="fa-solid fa-bolt" style="color: #ffc800;"></i>
                    <span>Ganhe 50 XP</span>
                    <i class="fa-solid fa-circle-check chip-check"></i>
                </div>
                <div class="daily-goal-chip" title="Meta de lições concluídas alcançada">
                    <i class="fa-solid fa-book-open" style="color: #1cb0f6;"></i>
                    <span>2 Lições</span>
                    <i class="fa-solid fa-circle-check chip-check"></i>
                </div>
                <div class="daily-goal-chip" title="Meta de lição perfeita alcançada">
                    <i class="fa-solid fa-bullseye" style="color: #ff527b;"></i>
                    <span>1 Lição 100%</span>
                    <i class="fa-solid fa-circle-check chip-check"></i>
                </div>
            </div>

            <!-- Pílula Central de Recompensa (⚡ MERECIDO 140 XP) -->
            <div class="daily-goal-reward-pill">
                <i class="fa-solid fa-bolt pill-bolt"></i>
                <span class="pill-tag">MERECIDO</span>
                <span class="pill-amount"><?php echo $xp_depois_hoje; ?> XP</span>
            </div>
        </div>

        <!-- Mascote alegre no canto inferior direito (estilo Mimo) -->
        <div class="daily-goal-mascot-wrapper">
            <img src="../assets/img/acertoutudo.png" alt="Opi Mascote Comemorando" class="daily-goal-mascot-img">
        </div>

        <!-- Barra inferior com botão Obtenha Recompensa posicionado à direita -->
        <div class="daily-goal-bottom-bar">
            <button type="button" class="daily-goal-continue-btn" id="dailyGoalContinueBtn">
                <span>Obtenha recompensa</span>
                <span class="daily-goal-kbd-hint">Ctrl+↵</span>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($fogo_acendeu_agora): ?>
    <!-- SEQUÊNCIA / OFENSIVA EM TELA INTEIRA (ESTILO MIMO) -->
    <div class="streak-overlay streak-fullscreen-view" id="streakOverlay" role="dialog" aria-modal="true" aria-labelledby="streakTitle">
        <!-- Partículas de fundo sutis -->
        <div class="streak-bg-particles" aria-hidden="true">
            <span class="streak-bg-spark spark-1"></span>
            <span class="streak-bg-spark spark-2"></span>
            <span class="streak-bg-spark spark-3"></span>
        </div>

        <div class="streak-center-stage">
            <!-- Palco da chama com badge numérico -->
            <div class="streak-flame-stage">
                <div class="streak-flame-glow"></div>
                
                <!-- Partícula decorativa gráfica em degrau (estilo ícone Mimo) -->
                <div class="streak-spark-accent" aria-hidden="true">
                    <span></span>
                    <span></span>
                </div>

                <div class="streak-flame-svg-wrap">
                    <svg class="streak-flame-svg" viewBox="0 0 160 200" aria-hidden="true">
                        <defs>
                            <linearGradient id="mimoFlameOuter" x1="0.1" y1="1" x2="0.9" y2="0">
                                <stop offset="0%" stop-color="#1d4ed8"/>
                                <stop offset="50%" stop-color="#2563eb"/>
                                <stop offset="100%" stop-color="#3b82f6"/>
                            </linearGradient>
                            <linearGradient id="mimoFlameMid" x1="0.1" y1="1" x2="0.9" y2="0">
                                <stop offset="0%" stop-color="#38bdf8"/>
                                <stop offset="70%" stop-color="#60a5fa"/>
                                <stop offset="100%" stop-color="#93c5fd"/>
                            </linearGradient>
                            <linearGradient id="mimoFlameInner" x1="0.1" y1="1" x2="0.9" y2="0">
                                <stop offset="0%" stop-color="#bae6fd"/>
                                <stop offset="100%" stop-color="#ffffff"/>
                            </linearGradient>
                        </defs>
                        <!-- Camadas da chama estilizada vetorial -->
                        <path class="flame-layer-outer" fill="url(#mimoFlameOuter)"
                              d="M80 8 C98 52 144 82 144 134 C144 174 116 196 80 196 C44 196 16 174 16 134 C16 94 40 76 52 48 C58 70 68 82 76 84 C70 56 72 30 80 8 Z"/>
                        <path class="flame-layer-mid" fill="url(#mimoFlameMid)"
                              d="M80 58 C94 84 124 106 124 142 C124 168 104 186 80 186 C56 186 36 168 36 142 C36 114 52 98 62 78 C66 94 72 102 78 104 C74 84 74 68 80 58 Z"/>
                        <path class="flame-layer-inner" fill="url(#mimoFlameInner)"
                              d="M80 102 C88 120 106 134 106 154 C106 172 94 180 80 180 C66 180 54 172 54 154 C54 136 64 126 70 116 C72 126 75 130 78 131 C76 120 76 110 80 102 Z"/>
                    </svg>
                </div>

                <!-- Badge circular com o número de dias no pé da chama -->
                <div class="streak-flame-badge">
                    <span class="streak-badge-count" id="streakCount" data-target="<?php echo $dias_fogo_atual; ?>"><?php echo max(0, $dias_fogo_atual - 1); ?></span>
                </div>
            </div>

            <!-- Chips circulares dos dias da semana (S T Q Q S S D) -->
            <div class="streak-week-chips">
                <?php foreach ($semana_fogo as $i => $dia): ?>
                    <div class="streak-chip <?php echo $dia['aceso'] ? 'is-lit' : ''; ?> <?php echo $dia['hoje'] ? 'is-today' : ''; ?>"
                         style="--d: <?php echo $i; ?>;"
                         title="<?php echo htmlspecialchars($dia['nome']); ?>">
                        <span class="streak-chip-text"><?php echo htmlspecialchars($dia['letra']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Título e subtítulo idênticos ao layout de referência -->
            <h1 class="streak-hero-title" id="streakTitle">
                <?php if ($dias_fogo_atual <= 1): ?>
                    Você iniciou uma sequência!
                <?php else: ?>
                    Você manteve sua sequência!
                <?php endif; ?>
            </h1>

            <p class="streak-hero-subtitle">
                Aprenda todos os dias para aumentar sua sequência de vitórias.
            </p>
        </div>

        <!-- Barra inferior com botão Continuar posicionado à direita -->
        <div class="streak-bottom-bar">
            <button type="button" class="streak-continue-btn" id="streakContinueBtn">
                <span>Continuar</span>
                <span class="streak-kbd-hint">Ctrl+↵</span>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($desbloqueou_secao): ?>
    <!-- ANIMAÇÃO DE DESBLOQUEIO DE SEÇÃO (TRANSIÇÃO DO AZUL PARA O ROSA) -->
    <div class="chapter-unlock-overlay" id="chapterUnlockOverlay" role="dialog" aria-modal="true" aria-labelledby="chapterUnlockTitle"
         style="--cor-origem: <?php echo htmlspecialchars($cor_origem); ?>; --cor-destino: <?php echo htmlspecialchars($cor_destino); ?>;">
        <div class="chapter-unlock-card">
            <div class="chapter-unlock-stage">
                <div class="chapter-unlock-glow"></div>
                <div class="chapter-unlock-ring"></div>
                <div class="chapter-unlock-spark-burst">
                    <span style="--angle: 0deg;"></span>
                    <span style="--angle: 45deg;"></span>
                    <span style="--angle: 90deg;"></span>
                    <span style="--angle: 135deg;"></span>
                    <span style="--angle: 180deg;"></span>
                    <span style="--angle: 225deg;"></span>
                    <span style="--angle: 270deg;"></span>
                    <span style="--angle: 315deg;"></span>
                </div>
                <div class="chapter-mascot-morph">
                    <img src="<?php echo htmlspecialchars($img_mascote_origem); ?>" alt="Seção <?php echo $cap_atual; ?>" class="chapter-mascot-img mascot-from">
                    <img src="<?php echo htmlspecialchars($img_mascote_destino); ?>" alt="Seção <?php echo $prox_cap_link; ?>" class="chapter-mascot-img mascot-to">
                </div>
                <div class="chapter-lock-badge" id="chapterLockBadge">
                    <i class="fa-solid fa-lock" id="chapterLockIcon"></i>
                </div>
            </div>

            <div class="chapter-transition-track">
                <div class="chapter-node node-origin">
                    <span class="node-dot"><i class="fa-solid fa-check"></i></span>
                    <span class="node-label">Seção <?php echo $cap_atual; ?></span>
                </div>
                <div class="chapter-bridge">
                    <div class="chapter-bridge-fill"></div>
                </div>
                <div class="chapter-node node-dest">
                    <span class="node-dot"><i class="fa-solid fa-lock" id="nodeDestIcon"></i></span>
                    <span class="node-label">Seção <?php echo $prox_cap_link; ?></span>
                </div>
            </div>

            <div class="chapter-xp-pill">+<?php echo $xp_ganho; ?> XP GANHOS</div>

            <h2 class="chapter-unlock-title" id="chapterUnlockTitle">
                Você desbloqueou a Seção <?php echo $prox_cap_link; ?>
            </h2>
            <p class="chapter-unlock-sub">
                Parabéns! Você concluiu o desafio prático no terminal e liberou uma nova seção da sua jornada em Java!
            </p>

            <div class="chapter-unlock-actions">
                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=1" class="chapter-btn-primary">
                    Ir para a lição <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="dashboard.php" class="chapter-btn-secondary">
                    <i class="fa-solid fa-house"></i> Voltar ao dashboard
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="../assets/js/opus_audio.js?v=<?= time() ?>"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Interações sonoras e reativas na tela de Resultado
        document.addEventListener('DOMContentLoaded', function() {
            const chapterOverlay = document.getElementById('chapterUnlockOverlay');
            const corDestino = <?php echo json_encode($cor_destino); ?>;
            const imgMascoteDestino = <?php echo json_encode($img_mascote_destino); ?>;

            function iniciarAnimacaoCapitulo() {
                if (!chapterOverlay) return;
                requestAnimationFrame(() => chapterOverlay.classList.add('is-open'));

                // Inicia preenchimento da ponte Azul -> Rosa
                setTimeout(() => {
                    chapterOverlay.classList.add('is-bridging');
                }, 350);

                // Momento da transição do Azul para o Rosa + abertura do cadeado
                setTimeout(() => {
                    chapterOverlay.classList.add('is-morphed');
                    const lockIcon = document.getElementById('chapterLockIcon');
                    if (lockIcon) lockIcon.className = 'fa-solid fa-lock-open';
                    const nodeDestIcon = document.getElementById('nodeDestIcon');
                    if (nodeDestIcon) nodeDestIcon.className = 'fa-solid fa-unlock-keyhole';

                    // Também transiciona o card principal ao fundo do Azul para o Rosa
                    document.body.style.transition = '--cor-tema 0.8s ease';
                    document.body.style.setProperty('--cor-tema', corDestino);
                    const mainMascote = document.getElementById('resultMascoteImg');
                    if (mainMascote && imgMascoteDestino) {
                        mainMascote.src = imgMascoteDestino;
                    }

                    try {
                        if (window.OpusAudio) {
                            if (typeof window.OpusAudio.playLessonComplete === 'function') {
                                window.OpusAudio.playLessonComplete();
                            } else if (typeof window.OpusAudio.playSuccess === 'function') {
                                window.OpusAudio.playSuccess();
                            }
                        }
                    } catch (e) {}
                }, 950);
            }

            // ── Gerenciador de sequência dos Overlays Comemorativos ──
            const dailyGoalOverlay = document.getElementById('dailyGoalOverlay');
            const streakOverlay = document.getElementById('streakOverlay');
            const chapterOverlay = document.getElementById('chapterUnlockOverlay');

            function iniciarStreakOuCapitulo() {
                if (streakOverlay) {
                    const countEl = document.getElementById('streakCount');
                    const alvo = parseInt(countEl.dataset.target, 10) || 1;
                    const inicial = Math.max(0, alvo - 1);
                    countEl.textContent = inicial;

                    // Deixa a topbar inicialmente cinza para acender junto com a chama
                    const topbarFogo = document.getElementById('topbarStatFogo');
                    const topbarFogoCount = document.getElementById('topbarFogoCount');
                    if (topbarFogo) {
                        topbarFogo.classList.remove('fogo-ativo');
                        topbarFogo.classList.add('fogo-inativo');
                    }
                    if (topbarFogoCount) {
                        topbarFogoCount.textContent = inicial;
                    }

                    requestAnimationFrame(() => {
                        streakOverlay.classList.add('is-open');
                        streakOverlay.classList.add('show-week');
                    });

                    // Chama sai do cinza para o laranja após o card abrir
                    setTimeout(() => {
                        streakOverlay.classList.add('is-lit');
                        if (topbarFogo) {
                            topbarFogo.classList.remove('fogo-inativo');
                            topbarFogo.classList.add('fogo-ativo');
                        }
                        if (topbarFogoCount) {
                            topbarFogoCount.textContent = alvo;
                        }
                        if (window.OpusAudio && window.OpusAudio.playSuccess) window.OpusAudio.playSuccess();

                        // Contador sobe do valor anterior até o novo total de dias
                        const dur = Math.min(700, Math.max(350, 120 * alvo));
                        const ini = performance.now();
                        (function tick(t) {
                            const p = Math.min(1, (t - ini) / dur);
                            countEl.textContent = Math.round(inicial + (alvo - inicial) * p);
                            if (p < 1) requestAnimationFrame(tick);
                            else countEl.classList.add('pop');
                        })(ini);
                    }, 650);

                    const fechar = () => {
                        if (window.OpusAudio) window.OpusAudio.playClick();
                        streakOverlay.classList.remove('is-open');
                        streakOverlay.classList.add('is-closing');
                        setTimeout(() => {
                            streakOverlay.remove();
                            if (chapterOverlay) iniciarAnimacaoCapitulo();
                        }, 350);
                    };

                    const continueBtn = document.getElementById('streakContinueBtn');
                    if (continueBtn) {
                        continueBtn.addEventListener('click', fechar);
                    }

                    const onStreakKey = function(e) {
                        if (!document.body.contains(streakOverlay)) {
                            document.removeEventListener('keydown', onStreakKey);
                            return;
                        }
                        if (e.key === 'Escape' || e.key === 'Enter' || (e.ctrlKey && e.key === 'Enter') || e.code === 'Space') {
                            e.preventDefault();
                            fechar();
                            document.removeEventListener('keydown', onStreakKey);
                        }
                    };
                    document.addEventListener('keydown', onStreakKey);
                } else if (chapterOverlay) {
                    // Se não houve abertura da ofensiva de fogo agora, inicia direto a transição de seção
                    iniciarAnimacaoCapitulo();
                }
            }

            // Confetes da comemoração de Meta Diária
            function launchDailyGoalConfetti() {
                const container = document.getElementById('dailyGoalConfetti');
                if (!container) return;
                const colors = ['#ffd54f', '#ffb300', '#7c3aed', '#a855f7', '#38bdf8', '#58cc02', '#ff527b'];
                for (let i = 0; i < 75; i++) {
                    const piece = document.createElement('div');
                    piece.className = 'dg-confetti-piece';
                    piece.style.left = (Math.random() * 100) + '%';
                    piece.style.top = (Math.random() * 20 - 25) + 'px';
                    piece.style.width = (Math.random() * 9 + 6) + 'px';
                    piece.style.height = (Math.random() * 9 + 6) + 'px';
                    piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                    piece.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
                    const dur = Math.random() * 1.8 + 1.8;
                    const delay = Math.random() * 0.45;
                    piece.style.animation = `dgConfettiFall ${dur}s cubic-bezier(0.25, 1, 0.5, 1) ${delay}s forwards`;
                    container.appendChild(piece);
                    setTimeout(() => piece.remove(), (dur + delay + 0.5) * 1000);
                }
            }

            // Se a Meta Diária foi alcançada nesta lição, exibe primeiro a tela da Meta
            if (dailyGoalOverlay) {
                requestAnimationFrame(() => {
                    dailyGoalOverlay.classList.add('is-open');
                });

                if (window.OpusAudio) {
                    setTimeout(() => {
                        if (typeof window.OpusAudio.playVictory === 'function') {
                            window.OpusAudio.playVictory();
                        } else if (typeof window.OpusAudio.playSuccess === 'function') {
                            window.OpusAudio.playSuccess();
                        }
                    }, 250);
                }

                launchDailyGoalConfetti();

                const fecharDailyGoal = () => {
                    if (window.OpusAudio && window.OpusAudio.playClick) window.OpusAudio.playClick();
                    dailyGoalOverlay.classList.remove('is-open');
                    dailyGoalOverlay.classList.add('is-closing');
                    setTimeout(() => {
                        dailyGoalOverlay.remove();
                        iniciarStreakOuCapitulo();
                    }, 350);
                };

                const dgBtn = document.getElementById('dailyGoalContinueBtn');
                if (dgBtn) {
                    dgBtn.addEventListener('click', fecharDailyGoal);
                }

                const onDailyGoalKey = function(e) {
                    if (!document.body.contains(dailyGoalOverlay)) {
                        document.removeEventListener('keydown', onDailyGoalKey);
                        return;
                    }
                    if (e.key === 'Escape' || e.key === 'Enter' || (e.ctrlKey && e.key === 'Enter') || e.code === 'Space') {
                        e.preventDefault();
                        fecharDailyGoal();
                        document.removeEventListener('keydown', onDailyGoalKey);
                    }
                };
                document.addEventListener('keydown', onDailyGoalKey);
            } else {
                iniciarStreakOuCapitulo();
            }

            // Feedback tátil em todos os botões de ação
            document.querySelectorAll('.btn-action, .chapter-btn-primary, .chapter-btn-secondary').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (window.OpusAudio) window.OpusAudio.playClick();
                });
            });

            // Mascote reativo com salto
            const mascote = document.getElementById('resultMascoteImg');

            if (mascote) {
                mascote.addEventListener('click', function() {
                    if (window.OpusAudio) window.OpusAudio.playClick();
                    mascote.classList.remove('mascote-jump');
                    void mascote.offsetWidth;
                    mascote.classList.add('mascote-jump');
                });
            }
            // Popup de Desbloqueio do Baú
            <?php if ($liberou_bau): ?>
            if (window.OpusAudio) {
                setTimeout(function() {
                    window.OpusAudio.playChest();
                }, 250);
            }
            <?php endif; ?>
        });

        function fecharPopupBau() {
            if (window.OpusAudio) window.OpusAudio.playClick();
            const modal = document.getElementById('modalDesbloqueioBau');
            if (modal) {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 250);
            }
        }
    </script>
</body>
</html>
