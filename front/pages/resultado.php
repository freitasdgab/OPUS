<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../../back/conexao.php';
require_once '../../back/jogador_status.php';
require_once '../../back/mascotes_capitulos.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['acertos'])) {
    header("Location: dashboard.php");
    exit();
}

if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$acertos = (int) ($_POST['acertos'] ?? 0);
$cap_atual = (int) ($_POST['cap'] ?? 0);
$licao_atual = (int) ($_POST['licao'] ?? 0);

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

if ($taxa_acertos >= 0.8) {
    $mensagem = "Perfeito! Você dominou o conteúdo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['feliz'];
    $xp_ganho = 50;
} elseif ($taxa_acertos >= 0.5) {
    $mensagem = "Muito bom! Você está no caminho certo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['explicando'];
    $xp_ganho = 35;
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

// Semana atual (Seg → Dom). Acende os dias cobertos pela sequência
// (hoje e os (dias_fogo - 1) dias anteriores); hoje é destacado e os
// dias futuros ficam cinzas para incentivar a continuar.
$semana_fogo = [];
if ($fogo_acendeu_agora) {
    $nomes_dias = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
    // WEEKDAY() do MySQL: 0 = segunda (mesma data usada pela procedure)
    $idx_hoje = (int) ($fogo_depois['dia_semana'] ?? ((int) date('N') - 1));
    for ($i = 0; $i < 7; $i++) {
        $diff = $i - $idx_hoje; // negativo = passado
        $semana_fogo[] = [
            'nome'  => $nomes_dias[$i],
            'hoje'  => ($diff === 0),
            'aceso' => ($diff <= 0 && -$diff < $dias_fogo_atual),
        ];
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Resultado - Opus</title>
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png">
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
            <?php include '../../back/topbar.php'; ?>
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

                    <?php if ($liberou_bau): ?>
                        <div class="bau-unlocked-banner">
                            <div class="bau-banner-icon">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div class="bau-banner-text">
                                <h3>Baú de Recompensas Liberado!</h3>
                                <p>Você concluiu a 3ª lição e liberou o Baú de Recompensas na Dashboard. Resgate vidas extras e XP!</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($perdeu_vida): ?>
                        <p class="result-message" style="color:#ff8a8a;">
                            <i class="fa-solid fa-heart-crack"></i>
                            <?php echo $vidas_restantes > 0
                                ? "Vidas restantes: {$vidas_restantes}/3. Cada coração volta a cada 5h."
                                : "Você ficou sem vidas. Aguarde 5h para recuperar cada coração."; ?>
                        </p>
                    <?php endif; ?>

                    <div class="buttons">
                        <?php if ($liberou_bau): ?>
                            <a href="dashboard.php" class="btn-action btn-dash" style="background: linear-gradient(135deg, #ffd700, #ff9600); color: #1a1a24; border: none; font-weight: 900; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);">
                                <i class="fa-solid fa-gift"></i> Abrir Baú na Dashboard
                            </a>
                        <?php elseif ($licao_atual == 5): ?>
                            <a href="dashboard.php" class="btn-action btn-dash"><i class="fa-solid fa-house"></i> Voltar ao dashboard</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn-action btn-dash"><i class="fa-solid fa-house"></i> Dashboard</a>
                        <?php endif; ?>
                        
                        <?php if ($acertos > 0): ?>
                            <?php if (!$is_curso_finalizado): ?>
                                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-next">
                                    <?php echo ($licao_atual == 5) ? 'Ir para a lição' : 'Próxima Lição'; ?> <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <a href="conquistas.php" class="btn-action btn-next">Ver Troféu! <i class="fa-solid fa-trophy"></i></a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($vidas_restantes > 0): ?>
                            <a href="licao.php?cap=<?php echo $cap_atual; ?>&licao=<?php echo $licao_atual; ?>" class="btn-action btn-next">
                                Tentar Novamente <i class="fa-solid fa-rotate-right"></i>
                            </a>
                            <?php else: ?>
                            <a href="dashboard.php?sem_vidas=1" class="btn-action btn-next">
                                Sem vidas <i class="fa-solid fa-heart-crack"></i>
                            </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($fogo_acendeu_agora): ?>
    <!-- OFENSIVA DE FOGO: chama acendendo + dias da semana -->
    <div class="streak-overlay" id="streakOverlay" role="dialog" aria-modal="true" aria-labelledby="streakTitle">
        <div class="streak-card">
            <div class="streak-flame-stage">
                <div class="streak-glow"></div>
                <div class="streak-spark"></div>
                <svg class="streak-flame" viewBox="0 0 120 160" aria-hidden="true">
                    <defs>
                        <linearGradient id="flameOuter" x1="0" y1="1" x2="0" y2="0">
                            <stop offset="0%" stop-color="#ff4b00"/>
                            <stop offset="60%" stop-color="#ff9600"/>
                            <stop offset="100%" stop-color="#ffc800"/>
                        </linearGradient>
                        <linearGradient id="flameInner" x1="0" y1="1" x2="0" y2="0">
                            <stop offset="0%" stop-color="#ffc800"/>
                            <stop offset="100%" stop-color="#fff6c2"/>
                        </linearGradient>
                    </defs>
                    <path class="flame-outer" fill="url(#flameOuter)"
                          d="M60 6 C72 38 104 58 104 100 C104 132 84 154 60 154 C36 154 16 132 16 100 C16 74 32 60 40 40 C44 58 52 66 58 68 C54 46 54 26 60 6 Z"/>
                    <path class="flame-inner" fill="url(#flameInner)"
                          d="M60 70 C68 88 84 98 84 118 C84 136 74 148 60 148 C46 148 36 136 36 118 C36 104 44 96 48 86 C50 96 54 100 58 101 C56 90 56 80 60 70 Z"/>
                </svg>
            </div>

            <div class="streak-count" id="streakCount" data-target="<?php echo $dias_fogo_atual; ?>"><?php echo max(0, $dias_fogo_atual - 1); ?></div>
            <h2 class="streak-title" id="streakTitle">
                <?php echo $dias_fogo_atual === 1 ? 'dia de ofensiva!' : 'dias de ofensiva!'; ?>
            </h2>
            <p class="streak-sub">
                <?php if ($dias_fogo_atual <= 1): ?>
                    Você acendeu sua primeira chama! Volte amanhã para manter o fogo aceso.
                <?php else: ?>
                    Seu fogo continua aceso! Pratique todos os dias para não perder a sequência.
                <?php endif; ?>
            </p>

            <div class="streak-week">
                <?php foreach ($semana_fogo as $i => $dia): ?>
                    <div class="streak-day <?php echo $dia['aceso'] ? 'is-lit' : ''; ?> <?php echo $dia['hoje'] ? 'is-today' : ''; ?>"
                         style="--d: <?php echo $i; ?>;">
                        <span class="streak-day-name"><?php echo $dia['nome']; ?></span>
                        <span class="streak-day-dot">
                            <i class="fa-solid fa-fire"></i>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="streak-btn" id="streakContinueBtn">CONTINUAR</button>
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

            // ── Ofensiva de fogo: chama saindo do cinza para o laranja + semana ──
            const streakOverlay = document.getElementById('streakOverlay');
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
                document.getElementById('streakContinueBtn').addEventListener('click', fechar);
                document.addEventListener('keydown', function onEsc(e) {
                    if (e.key === 'Escape' && document.body.contains(streakOverlay)) {
                        fechar();
                        document.removeEventListener('keydown', onEsc);
                    }
                });
            } else if (chapterOverlay) {
                // Se não houve abertura da ofensiva de fogo agora, inicia direto a transição de seção
                iniciarAnimacaoCapitulo();
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
        });
    </script>
</body>
</html>
