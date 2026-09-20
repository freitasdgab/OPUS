<?php
session_start();
require_once '../../back/conexao.php';
require_once '../../back/jogador_status.php';
require_once '../../back/mascotes_capitulos.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['acertos'])) {
    header("Location: dashboard.php");
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
    $xp_ganho = 150;
} elseif ($acertos == 2) {
    $mensagem = "Muito bom! Você está no caminho certo!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['explicando'];
    $xp_ganho = 100;
} elseif ($acertos == 1) {
    $mensagem = "Foi por pouco! Que tal revisar o conteúdo?";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['triste'];
    $xp_ganho = 50;
} else {
    $mensagem = "Não desanime! A programação exige prática. Tente novamente!";
    $imagem_mascote = "../assets/img/" . $mascote_capitulo['triste'];
    $xp_ganho = 10; // Um incentivo por tentar
}

if (!$ja_processado) {
    // Atualiza XP, liga, missão, sequência de fogo, troféus e avanço de
    // progresso — tudo isso mora em sp_processar_resultado_licao
    // (back/sql/opus_procedures.sql). Se for repetição de uma lição já
    // concluída, a procedure reduz o XP pra 10 (como se tivesse zerado)
    // e devolve o valor realmente aplicado. Usa opus_call() porque essa
    // procedure chama sp_atualizar_fogo por dentro, então o resultado
    // final não é o primeiro result set.
    $res_proc = opus_call($conn, "CALL sp_processar_resultado_licao(?, ?, ?, ?, ?)", "iiiii", [$user_id, $cap_atual, $licao_atual, $acertos, $xp_ganho]);

    if ($res_proc && isset($res_proc['xp_aplicado'])) {
        $xp_ganho = (int) $res_proc['xp_aplicado'];
    }
    if ($res_proc && (int) ($res_proc['eh_repeticao'] ?? 0) === 1) {
        $mensagem .= " (Lição já concluída antes — XP reduzido.)";
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
</head>
<body style="--cor-tema: <?php echo $cor_tema; ?>;">
    <canvas id="bg-canvas"></canvas>
    <div class="app-container">
        
        <main class="main-content" style="width: 100%; margin-left: 0;">
            <?php include '../../back/topbar.php'; ?>
            <div class="result-container">
                <div class="result-card">
                    <img src="<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-img">
                    
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
    <script src="../assets/js/script.js"></script>
</body>
</html>