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
$acertos = (int)$_POST['acertos'];
$cap_atual = (int)$_POST['cap'];
$licao_atual = (int)$_POST['licao'];
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

// 1. Mensagens e Imagens PNG Dinâmicas com base nos acertos
//    Lógica de humor do mascote: 3 acertos = feliz | 2 = animado/confiante
//    (estado "explicando") | 0 ou 1 = triste, incentivando a revisão.
if ($acertos == 3) {
    $mensagem = "Perfeito! Você dominou o conteúdo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['feliz'];
    $xp_ganho = 50;
} elseif ($acertos == 2) {
    $mensagem = "Muito bom! Você está no caminho certo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['explicando'];
    $xp_ganho = 25;
} elseif ($acertos == 1) {
    $mensagem = "Foi por pouco! Que tal revisar o conteúdo?";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['triste'];
    $xp_ganho = 10;
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
    // Atualiza XP, liga, missão, sequência de fogo, troféus e avanço de
    // progresso — tudo isso mora em sp_processar_resultado_licao
    // (back/sql/opus_procedures.sql). Se for repetição de uma lição já
    // concluída, a procedure reduz o XP pra 10 (como se tivesse zerado)
    // e devolve o valor realmente aplicado. Usa opus_call() porque essa
    // procedure chama sp_atualizar_fogo por dentro, então o resultado
    // final não é o primeiro result set.
    $res_proc = opus_call($conn, "CALL sp_processar_resultado_licao(?, ?, ?, ?, ?)", "iiiii", [$user_id, $cap_atual, $licao_atual, $acertos, 3]);

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
$is_curso_finalizado = ($cap_atual == 5 && $licao_atual == 5);
$liberou_bau = ($licao_atual == 3 && $acertos > 0);

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
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    <link rel="stylesheet" href="../assets/css/resultado.css">
    <link rel="stylesheet" href="../assets/css/light-mode.css">
    <script src="../assets/js/theme.js"></script>
</head>
<body style="--cor-tema: <?php echo $cor_tema; ?>;">
        <div class="app-container">
        
        <main class="main-content" style="width: 100%; margin-left: 0;">
            <?php include '../../back/topbar.php'; ?>
            <div class="result-container">
                <div class="result-card">
                    <div class="result-mascote-wrapper">
                        <div class="result-speech-bubble" id="resultSpeechBubble">
                            <?php 
                            if ($acertos == 3) {
                                echo "Incrível! Você dominou todo o conteúdo desta lição! 🏆";
                            } elseif ($acertos == 2) {
                                echo "Mandou muito bem! Quase gabaritou! 🚀";
                            } elseif ($acertos == 1) {
                                echo "Bom esforço! Pratique mais para dominar de vez! 💡";
                            } else {
                                echo "Não desanime! Programação é prática e persistência! 💪";
                            }
                            ?>
                        </div>
                        <img src="<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-img" id="resultMascoteImg" title="Clique no mascote para incentivo!">
                    </div>
                    
                    <h1 class="result-title"><?php echo $acertos; ?> / 3 Acertos</h1>
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
                        <?php else: ?>
                            <a href="dashboard.php" class="btn-action btn-dash"><i class="fa-solid fa-house"></i> Dashboard</a>
                        <?php endif; ?>
                        
                        <?php if ($acertos > 0): ?>
                            <?php if (!$is_curso_finalizado): ?>
                                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-next">
                                    <?php echo ($licao_atual == 5) ? 'Próximo Capítulo' : 'Próxima Lição'; ?> <i class="fa-solid fa-arrow-right"></i>
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

            <div class="streak-count" id="streakCount" data-target="<?php echo $dias_fogo_atual; ?>">0</div>
            <h2 class="streak-title" id="streakTitle">
                <?php echo $dias_fogo_atual === 1 ? 'dia de ofensiva!' : 'dias de ofensiva!'; ?>
            </h2>
            <p class="streak-sub">
                <?php if ($dias_fogo_atual <= 1): ?>
                    Você acendeu sua primeira chama! Volte amanhã para manter o fogo aceso. 🔥
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
    <script src="../assets/js/opus_audio.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Interações sonoras e reativas na tela de Resultado
        document.addEventListener('DOMContentLoaded', function() {
            // ── Ofensiva de fogo: chama acendendo + semana ──────────────
            const streakOverlay = document.getElementById('streakOverlay');
            if (streakOverlay) {
                const countEl = document.getElementById('streakCount');
                const alvo = parseInt(countEl.dataset.target, 10) || 1;

                requestAnimationFrame(() => streakOverlay.classList.add('is-open'));

                // Chama acende após o card entrar
                setTimeout(() => {
                    streakOverlay.classList.add('is-lit');
                    if (window.OpusAudio && window.OpusAudio.playSuccess) window.OpusAudio.playSuccess();
                }, 450);

                // Contador sobe até o total de dias
                setTimeout(() => {
                    const dur = Math.min(900, 120 * alvo);
                    const ini = performance.now();
                    (function tick(t) {
                        const p = Math.min(1, (t - ini) / dur);
                        countEl.textContent = Math.max(1, Math.round(alvo * p));
                        if (p < 1) requestAnimationFrame(tick);
                        else countEl.classList.add('pop');
                    })(ini);
                }, 1100);

                // Semana aparece dia a dia
                setTimeout(() => streakOverlay.classList.add('show-week'), 1500);

                const fechar = () => {
                    if (window.OpusAudio) window.OpusAudio.playClick();
                    streakOverlay.classList.remove('is-open');
                    streakOverlay.classList.add('is-closing');
                    setTimeout(() => streakOverlay.remove(), 350);
                };
                document.getElementById('streakContinueBtn').addEventListener('click', fechar);
                document.addEventListener('keydown', function onEsc(e) {
                    if (e.key === 'Escape' && document.body.contains(streakOverlay)) {
                        fechar();
                        document.removeEventListener('keydown', onEsc);
                    }
                });
            }
            // Feedback tátil em todos os botões de ação
            document.querySelectorAll('.btn-action').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    if (window.OpusAudio) window.OpusAudio.playClick();
                });
            });

            // Mascote reativo com salto e falas motivacionais
            const mascote = document.getElementById('resultMascoteImg');
            const balao = document.getElementById('resultSpeechBubble');
            const falas = [
                "Bora codar mais um pouco! ☕",
                "Você está evoluindo a cada lição! 🚀",
                "A prática constante é o segredo do Java! 💡",
                "Rumo à maestria em programação! ⭐",
                "Vamos conquistar a próxima lição juntos! 🔥"
            ];

            if (mascote) {
                mascote.addEventListener('click', function() {
                    if (window.OpusAudio) window.OpusAudio.playClick();
                    mascote.classList.remove('mascote-jump');
                    void mascote.offsetWidth;
                    mascote.classList.add('mascote-jump');

                    if (balao) {
                        balao.textContent = falas[Math.floor(Math.random() * falas.length)];
                        balao.style.animation = 'none';
                        void balao.offsetWidth;
                        balao.style.animation = 'bubblePop 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    }
                });
            }
        });
    </script>
</body>
</html>
