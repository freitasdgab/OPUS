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
$is_secao_preview  = isset($_GET['preview_secao']) || isset($_GET['secao']);
$is_tudo_preview   = isset($_GET['preview_tudo']);
$is_fim_curso_preview = isset($_GET['preview_fim_curso']) || isset($_GET['fim_curso']) || isset($_GET['preview_final']) || isset($_GET['preview_curso']);

$is_qualquer_preview = ($is_streak_preview || $is_meta_preview || $is_secao_preview || $is_tudo_preview || $is_fim_curso_preview);

if (!isset($_SESSION['user_id']) && !$is_qualquer_preview) {
    header("Location: dashboard.php");
    exit();
}
if (!isset($_SESSION['user_id']) && $is_qualquer_preview) {
    $_SESSION['user_id'] = 1;
}

if (!isset($_POST['acertos']) && !$is_qualquer_preview) {
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

if ($is_fim_curso_preview) {
    $cap_atual = 40;
    $licao_atual = 5;
    $acertos = 5;
    $total_questoes = 5;
}

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

// Tempo real gasto na licao (em segundos), enviado pelo JS
$tempo_licao_seg = isset($_POST['tempo_licao']) ? max(0, (int)$_POST['tempo_licao']) : 0;

// 1. Mensagens e Imagens PNG Dinâmicas com base na taxa de acertos
$taxa_acertos = ($total_questoes > 0) ? ($acertos / $total_questoes) : 1;
$gabaritou = ($acertos >= $total_questoes);
$errou_todas = ($acertos === 0);

// ==============================================================================
// MASCOTE OFICIAL DO OPUS (XÍCARA DE CAFÉ OPI)
// Substitui o macaquinho pela xícara do OPUS. Quando você criar sua nova imagem,
// basta trocar o nome do arquivo abaixo:
// ==============================================================================
$imagem_xicara_vitoria = "../assets/img/opi pulando feliz.png";

if ($gabaritou) {
    $titulo_duo = "Incrível!";
    $subtitulo_duo = "Você acertou todas as questões!";
    $mensagem = "Perfeito! Você dominou 100% do conteúdo!";
    $xp_ganho = 50;
} elseif ($errou_todas) {
    $titulo_duo = "Não desanime!";
    $subtitulo_duo = "Errar faz parte! Revise o código e tente de novo.";
    $mensagem = "Não desanime! A programação exige prática. Tente novamente!";
    $xp_ganho = 10;
} elseif ($taxa_acertos >= 0.7) {
    $titulo_duo = "Bom trabalho!";
    $subtitulo_duo = "Lição concluída com sucesso!";
    $mensagem = "Muito bom! Quase perfeito!";
    $xp_ganho = 40;
} else {
    $titulo_duo = "Bom esforço!";
    $subtitulo_duo = "Você está no caminho certo!";
    $mensagem = "Bom trabalho! Continue praticando para fixar os conceitos.";
    $xp_ganho = 25;
}
$imagem_mascote = $imagem_xicara_vitoria;

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
$is_curso_finalizado = ($cap_atual >= 40 && $licao_atual == 5) || $is_fim_curso_preview;
$is_curso_concluido = ($is_curso_finalizado && $acertos > 0) || $is_fim_curso_preview;
$liberou_bau = ($licao_atual == 3 && $acertos > 0 && !$is_curso_concluido);
$desbloqueou_secao = ($licao_atual == 5 && $acertos > 0 && !$is_curso_finalizado);

// Se concluiu a última seção do Capítulo 1 (Seção 15) ou Capítulo 2 (Seção 26), aciona a viagem de barco ao retornar ao dashboard
$dashboard_retorno_url = "dashboard.php?fogo_aceso=1";
if ($is_curso_concluido) {
    $dashboard_retorno_url = "dashboard.php?curso_concluido=1";
    $titulo_duo = "CURSO CONCLUÍDO!";
    $subtitulo_duo = "Você completou todas as 40 seções do OPUS!";
    $mensagem = "Parabéns, Desenvolvedor! Você dominou o curso completo de Java & Lógica do OPUS!";
} elseif ($desbloqueou_secao && $cap_atual === 15) {
    $dashboard_retorno_url = "dashboard.php?de_capitulo=1&viajar_capitulo=2";
} elseif ($desbloqueou_secao && $cap_atual === 26) {
    $dashboard_retorno_url = "dashboard.php?de_capitulo=2&viajar_capitulo=3";
}

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

// ==============================================================================
// DADOS DO WIDGET SPOTIFY WRAPPED (ESTATÍSTICAS DA JORNADA)
// ==============================================================================
$sp_nome_aluno = trim((string) ($_SESSION['user_nome'] ?? ($status_atual['nome'] ?? 'Estudante Opus')));
if (empty($sp_nome_aluno)) $sp_nome_aluno = 'Estudante Opus';

$sp_username = trim((string) ($_SESSION['user_username'] ?? ''));
$sp_avatar_url = '../assets/img/opi pulando feliz.png';

$stmt_un = $conn->prepare("SELECT username, foto_perfil, foto_google FROM usuarios WHERE id = ?");
if ($stmt_un) {
    $stmt_un->bind_param("i", $user_id);
    $stmt_un->execute();
    $row_un = $stmt_un->get_result()->fetch_assoc();
    $stmt_un->close();
    if ($row_un) {
        if (!empty($row_un['username'])) $sp_username = $row_un['username'];
        if (!empty($row_un['foto_perfil'])) $sp_avatar_url = $row_un['foto_perfil'];
        elseif (!empty($row_un['foto_google'])) $sp_avatar_url = $row_un['foto_google'];
    }
}
if (empty($sp_username)) $sp_username = 'aluno.opus';
if ($sp_username[0] !== '@') $sp_username = '@' . $sp_username;

$sp_xp_total = (int) ($status_atual['xp'] ?? 0);
if ($is_fim_curso_preview && $sp_xp_total < 500) {
    $sp_xp_total = 14500;
}

$sp_dias_fogo = max(1, (int) ($status_atual['dias_fogo'] ?? 1));
if ($is_fim_curso_preview && $sp_dias_fogo <= 1) {
    $sp_dias_fogo = 21;
}

$sp_trofeus = (int) ($status_atual['trofeus'] ?? 0);
if ($is_fim_curso_preview && $sp_trofeus < 3) {
    $sp_trofeus = 15;
}

$sp_licoes_total = "200 / 200";

// Resultado da ofensiva após processar a lição
$fogo_depois = $ler_ultima_atividade();
$dias_fogo_atual = (int) ($fogo_depois['dias_fogo'] ?? 0);
$fogo_acendeu_agora = !$ja_processado
    && !$fogo_ja_aceso_hoje
    && ((int) ($fogo_depois['aceso_hoje'] ?? 0) === 1);

if ($is_streak_preview || $is_tudo_preview) {
    $fogo_acendeu_agora = true;
    $dias_fogo_atual = isset($_GET['dias']) ? max(1, (int)$_GET['dias']) : max(1, $dias_fogo_atual);
}

// A animação de sequência de fogo celebra o fogo aceso ou mantido na lição (com acertos > 0 ou em previews)
// Mostra a tela de streak APENAS quando o fogo foi acendido nesta licao (primeira atividade do dia)
// ou em modo preview. Se a sequencia ja estava ativa, nao mostra de novo.
$mostrar_streak = ($fogo_acendeu_agora || $is_streak_preview || $is_tudo_preview);

if ($mostrar_streak && $dias_fogo_atual < 1) {
    $dias_fogo_atual = 1;
}

// Semana atual (Seg → Dom). Acende os dias cobertos pela sequência
// (hoje e os (dias_fogo - 1) dias anteriores); hoje é destacado e os
// dias futuros ficam cinzas para incentivar a continuar.
$semana_fogo = [];
if ($mostrar_streak) {
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
            'aceso' => ($diff <= 0 && -$diff < $dias_fogo_atual) || ($diff === 0),
        ];
    }
}

// Animação de desbloquear nova seção
$mostrar_secao = ($desbloqueou_secao || $is_secao_preview || $is_tudo_preview);

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
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/resultado.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/spotify_widget.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/light-mode.css?v=<?= time() ?>">
    <script src="../assets/js/theme.js"></script>
</head>
<body style="--cor-tema: <?php echo $cor_tema; ?>;">
        <div class="app-container">
        
        <main class="main-content" style="width: 100%; margin-left: 0;">
            <?php include '../../back/includes/topbar.php'; ?>

            <div class="result-container">
                <div class="duo-lesson-screen-wrapper" id="duoLessonScreen">
                    
                    <!-- Mascote Opi (Xícara Oficial do OPUS) com Animação Cartoon Alegre -->
                    <div class="opi-vitoria-stage">
                        <div class="opi-vitoria-wrapper" id="opiVitoriaWrapper" title="Opi, a xícara mascote do OPUS!">
                            <img src="<?php echo htmlspecialchars($imagem_xicara_vitoria); ?>" alt="Opi Xícara OPUS" class="opi-vitoria-img" id="opiVitoriaImg">
                        </div>
                    </div>

                    <!-- Título e Subtítulo Duolingo Dinâmicos (Acertou todas / Errou todas / Bom trabalho) -->
                    <div class="duo-complete-titles">
                        <h1 class="duo-title-main"><?php echo htmlspecialchars($titulo_duo); ?></h1>
                        <h2 class="duo-title-sub"><?php echo htmlspecialchars($subtitulo_duo); ?></h2>
                    </div>

                    <!-- 3 Cards de Estatísticas com Cores Vivas (Verde, Azul e Laranja) -->
                    <div class="duo-stats-grid">
                        <!-- Card 1: Comandos Aprendidos Hoje (Verde) -->
                        <div class="duo-card-stat stat-words">
                            <div class="duo-card-icon">
                                <span class="ab-board-icon"><i class="fa-solid fa-code" style="color: #fff; font-size: 0.85rem;"></i></span>
                            </div>
                            <div class="duo-card-title">
                                Comandos<br>Aprendidos Hoje
                            </div>
                            <div class="duo-card-diff">
                                +<?php echo $errou_todas ? '0' : '2'; ?> comandos
                            </div>
                            <div class="duo-card-pill">
                                <?php echo 40 + ($errou_todas ? 0 : 2); ?>
                            </div>
                        </div>

                        <!-- Card 2: Acertos na Lição (Azul) -->
                        <div class="duo-card-stat stat-sentences">
                            <div class="duo-card-icon">
                                <span class="speech-chat-icon"><i class="fa-solid fa-bullseye"></i></span>
                            </div>
                            <div class="duo-card-title">
                                Acertos<br>na Lição
                            </div>
                            <div class="duo-card-diff">
                                <?php echo $gabaritou ? 'Acertou todas!' : ($errou_todas ? 'Errou todas' : '+' . $xp_ganho . ' XP'); ?>
                            </div>
                            <div class="duo-card-pill">
                                <?php echo $acertos; ?> / <?php echo $total_questoes; ?>
                            </div>
                        </div>

                        <!-- Card 3: Tempo Praticando (Laranja / Ouro) -->
                        <div class="duo-card-stat stat-time">
                            <div class="duo-card-icon">
                                <span class="stopwatch-icon"><i class="fa-solid fa-stopwatch"></i></span>
                            </div>
                            <div class="duo-card-title">
                                Tempo<br>Praticando
                            </div>
                            <div class="duo-card-diff">
                                <?php
                                    $mins = intdiv($tempo_licao_seg, 60);
                                    $segs = $tempo_licao_seg % 60;
                                    echo sprintf('+%d:%02d', $mins, $segs);
                                ?>
                            </div>
                            <div class="duo-card-pill">
                                <?php echo sprintf('%d:%02d', $mins, $segs); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($perdeu_vida): ?>
                        <p class="result-message" style="color:#ff8a8a; margin-bottom: 20px; text-align: center; font-weight: 700;">
                            <i class="fa-solid fa-heart-crack"></i>
                            <?php echo $vidas_restantes > 0
                                ? "Vidas restantes: {$vidas_restantes}/3. Cada coração volta a cada 5h."
                                : "Você ficou sem vidas. Aguarde 5h para recuperar cada coração."; ?>
                        </p>
                    <?php endif; ?>

                    <!-- Botão Continuar Marcante Duolingo -->
                    <div class="duo-action-container" style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                        <?php if ($is_curso_concluido): ?>
                        <button type="button" class="btn-duo-next" id="btnAbrirSpotifyCardGeral" style="background: linear-gradient(135deg, #1db954, #15883e); box-shadow: 0 8px 24px rgba(29, 185, 84, 0.45); max-width: 440px; width: 100%;">
                            <i class="fa-brands fa-spotify" style="font-size: 1.3rem;"></i>
                            <span>Abrir Card Spotify Wrapped</span>
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn-duo-next" id="btnDuoContinue" style="<?php echo $is_curso_concluido ? 'max-width: 440px; width: 100%;' : ''; ?>">
                            Continuar <i class="fa-solid fa-arrow-right"></i>
                        </button>
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

    <?php if ($mostrar_streak): ?>
    <!-- SEQUÊNCIA / OFENSIVA EM TELA INTEIRA (ESTILO MIMO / DUOLINGO) -->
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
                            <linearGradient id="cartoonFlameOuter" x1="0.1" y1="1" x2="0.9" y2="0">
                                <stop offset="0%" stop-color="#ff3700"/>
                                <stop offset="45%" stop-color="#ff7300"/>
                                <stop offset="100%" stop-color="#ff9600"/>
                            </linearGradient>
                            <linearGradient id="cartoonFlameMid" x1="0.1" y1="1" x2="0.9" y2="0">
                                <stop offset="0%" stop-color="#ff9900"/>
                                <stop offset="55%" stop-color="#ffb800"/>
                                <stop offset="100%" stop-color="#ffd500"/>
                            </linearGradient>
                            <linearGradient id="cartoonFlameInner" x1="0.1" y1="1" x2="0.9" y2="0">
                                <stop offset="0%" stop-color="#fff275"/>
                                <stop offset="100%" stop-color="#ffffff"/>
                            </linearGradient>
                        </defs>
                        <!-- Camadas da chama estilizada vetorial estilo Cartoon -->
                        <path class="flame-layer-outer" fill="url(#cartoonFlameOuter)"
                              d="M80 8 C98 52 144 82 144 134 C144 174 116 196 80 196 C44 196 16 174 16 134 C16 94 40 76 52 48 C58 70 68 82 76 84 C70 56 72 30 80 8 Z"/>
                        <path class="flame-layer-mid" fill="url(#cartoonFlameMid)"
                              d="M80 58 C94 84 124 106 124 142 C124 168 104 186 80 186 C56 186 36 168 36 142 C36 114 52 98 62 78 C66 94 72 102 78 104 C74 84 74 68 80 58 Z"/>
                        <path class="flame-layer-inner" fill="url(#cartoonFlameInner)"
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

    <?php if ($mostrar_secao): ?>
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
                Parabéns! Você concluiu os desafios e liberou uma nova seção da sua jornada em Java!
            </p>

            <div class="chapter-unlock-actions">
                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=1" class="chapter-btn-primary">
                    Ir para a lição <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="<?php echo htmlspecialchars($dashboard_retorno_url); ?>" class="chapter-btn-secondary">
                    <i class="fa-solid fa-house"></i> Voltar ao dashboard
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- =========================================================================
         MODAL DO WIDGET SPOTIFY WRAPPED (OPUS FORMATURA)
         ========================================================================= -->
    <div class="spotify-modal-overlay" id="modalSpotifyWrapped" role="dialog" aria-modal="true" aria-labelledby="spTrackTitle">
        <div class="spotify-modal-container">
            <button type="button" class="spotify-modal-close-btn" id="spBtnCloseModal" title="Fechar (Esc)" aria-label="Fechar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <!-- Card Exportável do Spotify Wrapped -->
            <div class="spotify-card" id="spotifyCardExport">
                
                <!-- Topo: Marca e Selo de Conclusão -->
                <div class="sp-header">
                    <div class="sp-brand">
                        <img src="../assets/img/LOGO.png" alt="OPUS" class="sp-brand-logo">
                        <div class="sp-brand-text">
                            <span class="sp-brand-title">OPUS <span class="sp-dot">•</span> WRAPPED</span>
                            <span class="sp-brand-sub">Edição 2026 · Formatura</span>
                        </div>
                    </div>
                    <div class="sp-badge-concluido">
                        <i class="fa-solid fa-graduation-cap"></i> 100% Concluído
                    </div>
                </div>

                <!-- Capa do Álbum / Disco de Vinil com Avatar do Aluno -->
                <div class="sp-cover-stage">
                    <div class="sp-vinyl-record" aria-hidden="true"></div>
                    
                    <div class="sp-avatar-wrap">
                        <img src="<?php echo htmlspecialchars($sp_avatar_url); ?>" alt="<?php echo htmlspecialchars($sp_nome_aluno); ?>" class="sp-avatar-img" id="spAvatarImg" crossorigin="anonymous">
                    </div>

                    <!-- Mascote Opi Comemorando -->
                    <img src="../assets/img/mascote_vitoria_cipo_transparente.png" alt="Opi Vencedor" class="sp-opi-mascot-float" id="spOpiImg">

                    <!-- Equalizador Live -->
                    <div class="sp-equalizer-live" aria-hidden="true">
                        <span class="sp-eq-bar"></span>
                        <span class="sp-eq-bar"></span>
                        <span class="sp-eq-bar"></span>
                        <span class="sp-eq-bar"></span>
                    </div>

                    <!-- Selo de Artista Verificado -->
                    <div class="sp-verified-pill">
                        <i class="fa-solid fa-circle-check"></i> Aluno Verificado
                    </div>
                </div>

                <!-- Informações da Faixa / Aluno -->
                <div class="sp-track-row">
                    <div class="sp-track-info">
                        <h3 class="sp-track-title" id="spTrackTitle">Graduado em Java & Lógica</h3>
                        <p class="sp-track-artist">
                            <span class="sp-artist-name" id="spStudentName"><?php echo htmlspecialchars($sp_nome_aluno); ?></span>
                            <span id="spStudentUser" style="color: var(--sp-green);"><?php echo htmlspecialchars($sp_username); ?></span>
                        </p>
                        <span class="sp-track-album">Formação OPUS · 40 Seções Concluídas</span>
                    </div>
                    <button type="button" class="sp-heart-btn" title="Favorito" aria-label="Favorito">
                        <i class="fa-solid fa-heart"></i>
                    </button>
                </div>

                <!-- Barra de Progresso do Player -->
                <div class="sp-player-progress">
                    <div class="sp-progress-track">
                        <div class="sp-progress-fill">
                            <span class="sp-progress-thumb"></span>
                        </div>
                    </div>
                    <div class="sp-progress-times">
                        <span>40:00</span>
                        <span>40:00 (100%)</span>
                    </div>
                </div>

                <!-- Controles do Player -->
                <div class="sp-controls-row">
                    <button type="button" class="sp-control-btn is-active" title="Ordem aleatória"><i class="fa-solid fa-shuffle"></i></button>
                    <button type="button" class="sp-control-btn" title="Anterior"><i class="fa-solid fa-backward-step"></i></button>
                    <button type="button" class="sp-play-btn" id="spBtnPlay" title="Tocar / Pausar Música Comemorativa" aria-label="Tocar"><i class="fa-solid fa-play"></i></button>
                    <button type="button" class="sp-control-btn" title="Próximo"><i class="fa-solid fa-forward-step"></i></button>
                    <button type="button" class="sp-control-btn is-active" title="Repetir"><i class="fa-solid fa-repeat"></i></button>
                </div>

                <!-- Grade de Estatísticas da Jornada -->
                <div class="sp-stats-grid">
                    <div class="sp-stat-pill">
                        <div class="sp-stat-icon-wrap xp"><i class="fa-solid fa-bolt"></i></div>
                        <div class="sp-stat-content">
                            <span class="sp-stat-val" id="spValXp"><?php echo number_format($sp_xp_total, 0, ',', '.'); ?> XP</span>
                            <span class="sp-stat-lbl">XP Total</span>
                        </div>
                    </div>
                    <div class="sp-stat-pill">
                        <div class="sp-stat-icon-wrap streak"><i class="fa-solid fa-fire"></i></div>
                        <div class="sp-stat-content">
                            <span class="sp-stat-val" id="spValStreak"><?php echo $sp_dias_fogo; ?> Dias</span>
                            <span class="sp-stat-lbl">Ofensiva</span>
                        </div>
                    </div>
                    <div class="sp-stat-pill">
                        <div class="sp-stat-icon-wrap trophy"><i class="fa-solid fa-trophy"></i></div>
                        <div class="sp-stat-content">
                            <span class="sp-stat-val" id="spValTrophy"><?php echo $sp_trofeus; ?> Troféus</span>
                            <span class="sp-stat-lbl">Conquistas</span>
                        </div>
                    </div>
                    <div class="sp-stat-pill">
                        <div class="sp-stat-icon-wrap lessons"><i class="fa-solid fa-laptop-code"></i></div>
                        <div class="sp-stat-content">
                            <span class="sp-stat-val" id="spValLessons"><?php echo $sp_licoes_total; ?></span>
                            <span class="sp-stat-lbl">Lições Feitas</span>
                        </div>
                    </div>
                </div>

                <!-- Código de Barras Sonoro Spotify -->
                <div class="sp-code-bar">
                    <div class="sp-code-logo">
                        <img src="../assets/img/LOGO.png" alt="OPUS">
                    </div>
                    <div class="sp-code-bars-wrap" aria-hidden="true">
                        <span class="sp-code-line" style="height: 10px;"></span>
                        <span class="sp-code-line" style="height: 22px;"></span>
                        <span class="sp-code-line" style="height: 30px;"></span>
                        <span class="sp-code-line" style="height: 14px;"></span>
                        <span class="sp-code-line" style="height: 28px;"></span>
                        <span class="sp-code-line" style="height: 18px;"></span>
                        <span class="sp-code-line" style="height: 24px;"></span>
                        <span class="sp-code-line" style="height: 30px;"></span>
                        <span class="sp-code-line" style="height: 12px;"></span>
                        <span class="sp-code-line" style="height: 20px;"></span>
                        <span class="sp-code-line" style="height: 28px;"></span>
                        <span class="sp-code-line" style="height: 16px;"></span>
                        <span class="sp-code-line" style="height: 26px;"></span>
                        <span class="sp-code-line" style="height: 12px;"></span>
                        <span class="sp-code-line" style="height: 22px;"></span>
                        <span class="sp-code-line" style="height: 30px;"></span>
                        <span class="sp-code-line" style="height: 14px;"></span>
                        <span class="sp-code-line" style="height: 20px;"></span>
                    </div>
                    <span class="sp-code-label">OPUS.DEV/FORMATURA</span>
                </div>
            </div>

            <!-- Botões de Ação Embaixo do Card -->
            <div class="sp-actions-wrap">
                <div class="sp-action-btn-main-row">
                    <button type="button" class="sp-btn sp-btn-download" id="spBtnDownload">
                        <i class="fa-solid fa-download"></i> Baixar Card (PNG)
                    </button>
                    <button type="button" class="sp-btn sp-btn-share" id="spBtnShare">
                        <i class="fa-solid fa-share-nodes"></i> Compartilhar
                    </button>
                </div>
                <div class="sp-action-btn-sub-row">
                    <button type="button" class="sp-btn sp-btn-tour" onclick="fecharSpotifyWrappedModal(); window.OpusTour && window.OpusTour.startCursoFinalizadoTutor();">
                        <i class="fa-solid fa-book-open"></i> Rever Retrospectiva do Opi
                    </button>
                    <a href="<?php echo htmlspecialchars($dashboard_retorno_url); ?>" class="sp-btn sp-btn-dashboard">
                        <i class="fa-solid fa-house"></i> Ir ao Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/opus_audio.js?v=<?= time() ?>"></script>
    <script src="../assets/js/spotify_widget.js?v=<?= time() ?>"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Interações sonoras e fluxo encadeado na tela de Resultado
        document.addEventListener('DOMContentLoaded', function() {
            const duoContinueBtn   = document.getElementById('btnDuoContinue');
            const streakOverlay    = document.getElementById('streakOverlay');
            const chapterOverlay   = document.getElementById('chapterUnlockOverlay');
            const opiVitoriaWrapper = document.getElementById('opiVitoriaWrapper');

            const nextLessonUrl  = "licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>";
            const nextSectionUrl = "licao.php?cap=<?php echo $prox_cap_link; ?>&licao=1";
            const dashboardUrl   = "<?php echo $dashboard_retorno_url; ?>";
            const abrirBauUrl    = "dashboard.php?abrir_bau=<?php echo $cap_atual; ?>";
            const liberouBau     = <?php echo $liberou_bau ? 'true' : 'false'; ?>;

            // Áudio alegre de conclusão da lição
            try {
                if (window.OpusAudio) {
                    setTimeout(() => {
                        if (typeof window.OpusAudio.playLessonComplete === 'function') {
                            window.OpusAudio.playLessonComplete();
                        } else if (typeof window.OpusAudio.playSuccess === 'function') {
                            window.OpusAudio.playSuccess();
                        }
                    }, 280);
                }
            } catch (e) {}

            // Interação cartoon ao clicar na xícara Opi
            if (opiVitoriaWrapper) {
                opiVitoriaWrapper.addEventListener('click', function() {
                    if (window.OpusAudio && window.OpusAudio.playClick) window.OpusAudio.playClick();
                    opiVitoriaWrapper.style.animation = 'none';
                    void opiVitoriaWrapper.offsetWidth;
                    opiVitoriaWrapper.style.animation = 'opiHopCartoon 1s ease-in-out 2';
                    setTimeout(() => {
                        opiVitoriaWrapper.style.animation = 'opiHopCartoon 3s ease-in-out infinite';
                    }, 2000);
                });
            }

            // 1. Função que executa a animação de Sequência de Fogo (Ofensiva)
            window.iniciarAnimacaoStreak = function() {
                if (!streakOverlay) {
                    if (chapterOverlay) {
                        iniciarAnimacaoCapitulo();
                    } else if (liberouBau) {
                        window.location.href = abrirBauUrl;
                    } else {
                        window.location.href = nextLessonUrl;
                    }
                    return;
                }

                const countEl = document.getElementById('streakCount');
                const alvo = parseInt(countEl ? countEl.dataset.target : 1, 10) || 1;
                const inicial = Math.max(0, alvo - 1);
                if (countEl) countEl.textContent = inicial;

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

                streakOverlay.classList.remove('is-closing');
                requestAnimationFrame(() => {
                    streakOverlay.classList.add('is-open');
                    streakOverlay.classList.add('show-week');
                });

                // Chama acende do cinza para o laranja/ouro cartoon após o card abrir
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

                    // Contador sobe até o novo total de dias
                    if (countEl) {
                        const dur = Math.min(750, Math.max(350, 120 * alvo));
                        const ini = performance.now();
                        (function tick(t) {
                            const p = Math.min(1, (t - ini) / dur);
                            countEl.textContent = Math.round(inicial + (alvo - inicial) * p);
                            if (p < 1) requestAnimationFrame(tick);
                            else countEl.classList.add('pop');
                        })(ini);
                    }
                }, 600);

                const fecharStreak = () => {
                    if (window.OpusAudio && window.OpusAudio.playClick) window.OpusAudio.playClick();
                    streakOverlay.classList.remove('is-open');
                    streakOverlay.classList.add('is-closing');

                    setTimeout(() => {
                        streakOverlay.style.display = 'none';
                        <?php if ($is_curso_concluido): ?>
                        if (typeof window.abrirSpotifyWrappedModal === 'function') {
                            window.abrirSpotifyWrappedModal();
                            return;
                        }
                        <?php endif; ?>
                        if (chapterOverlay) {
                            iniciarAnimacaoCapitulo();
                        } else if (liberouBau) {
                            window.location.href = abrirBauUrl;
                        } else {
                            window.location.href = nextLessonUrl;
                        }
                    }, 350);
                };

                const continueBtn = document.getElementById('streakContinueBtn');
                if (continueBtn) {
                    continueBtn.onclick = fecharStreak;
                }

                const onStreakKey = function(e) {
                    if (streakOverlay.style.display === 'none' || !streakOverlay.classList.contains('is-open')) {
                        document.removeEventListener('keydown', onStreakKey);
                        return;
                    }
                    if (e.key === 'Escape' || e.key === 'Enter' || (e.ctrlKey && e.key === 'Enter') || e.code === 'Space') {
                        e.preventDefault();
                        fecharStreak();
                        document.removeEventListener('keydown', onStreakKey);
                    }
                };
                document.addEventListener('keydown', onStreakKey);
            };

            // 2. Função que executa a animação de Passar para a Próxima Seção
            window.iniciarAnimacaoCapitulo = function() {
                if (!chapterOverlay) {
                    window.location.href = nextSectionUrl;
                    return;
                }

                chapterOverlay.style.display = 'flex';
                requestAnimationFrame(() => chapterOverlay.classList.add('is-open'));

                // Inicia preenchimento da ponte
                setTimeout(() => {
                    chapterOverlay.classList.add('is-bridging');
                }, 350);

                // Momento da transição + abertura do cadeado
                setTimeout(() => {
                    chapterOverlay.classList.add('is-morphed');
                    const lockIcon = document.getElementById('chapterLockIcon');
                    if (lockIcon) lockIcon.className = 'fa-solid fa-lock-open';
                    const nodeDestIcon = document.getElementById('nodeDestIcon');
                    if (nodeDestIcon) nodeDestIcon.className = 'fa-solid fa-unlock-keyhole';

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
            };

            // Ação do Botão Principal "Continuar" da Tela de Final de Lição
            if (duoContinueBtn) {
                duoContinueBtn.addEventListener('click', function() {
                    if (window.OpusAudio && window.OpusAudio.playClick) window.OpusAudio.playClick();
                    
                    <?php if ($is_curso_concluido): ?>
                    if (typeof window.abrirSpotifyWrappedModal === 'function') {
                        window.abrirSpotifyWrappedModal();
                        return;
                    }
                    <?php endif; ?>

                    if (streakOverlay) {
                        iniciarAnimacaoStreak();
                    } else if (chapterOverlay) {
                        iniciarAnimacaoCapitulo();
                    } else if (liberouBau) {
                        window.location.href = abrirBauUrl;
                    } else {
                        window.location.href = nextLessonUrl;
                    }
                });
            }

            // Atalho de teclado: Enter avança
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || (e.ctrlKey && e.key === 'Enter') || e.code === 'Space') {
                    if ((!streakOverlay || !streakOverlay.classList.contains('is-open')) &&
                        (!chapterOverlay || !chapterOverlay.classList.contains('is-open'))) {
                        e.preventDefault();
                        if (duoContinueBtn) duoContinueBtn.click();
                    }
                }
            });

            // Popup do Baú se aplicável
            <?php if ($liberou_bau): ?>
            if (window.OpusAudio && window.OpusAudio.playChest) {
                setTimeout(() => window.OpusAudio.playChest(), 250);
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

    <?php
    $OPUS_TOUR_EXTRA_CONFIG = [
        'cap' => (int) $cap_atual,
        'licao' => (int) $licao_atual,
        'isPrimeiraLicao' => ((int) $cap_atual === 1 && (int) $licao_atual === 1),
        'isCursoFinalizado' => (bool) $is_curso_concluido,
        'isFimCursoPreview' => (bool) $is_fim_curso_preview,
        'acertos' => (int) $acertos,
        'totalQuestoes' => (int) $total_questoes,
        'xpGanho' => (int) $xp_ganho,
        'errouTodas' => (bool) $errou_todas,
        'gabaritou' => (bool) $gabaritou,
        'hasStreak' => (bool) $mostrar_streak,
        'proxCap' => (int) $prox_cap_link,
        'proxLicao' => (int) $prox_licao,
        'proxLicaoTitulo' => (string) ($prox_licao_titulo ?? ''),
        'proxLicaoUrl' => "licao.php?cap={$prox_cap_link}&licao={$prox_licao}",
    ];
    include '../../back/includes/tour_opi.php';
    ?>
</body>
</html>
