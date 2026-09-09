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
    // 2. Atualizar o XP do Usuário
    $stmt_xp = $conn->prepare("UPDATE usuarios SET xp = xp + ? WHERE id = ?");
    $stmt_xp->bind_param("ii", $xp_ganho, $user_id);
    $stmt_xp->execute();

    opus_atualizar_fogo($conn, $user_id);

    if ($acertos === 0) {
        $vidas_restantes = opus_perder_vida($conn, $user_id);
        $perdeu_vida = true;
        $mensagem .= " Você perdeu 1 vida.";
        if ($vidas_restantes <= 0) {
            $mensagem .= " Sem vidas: a próxima volta em 24 horas.";
        }
    }
} else {
    $status_atual = opus_sincronizar_jogador($conn, $user_id);
    $vidas_restantes = (int) $status_atual['vidas'];
}

if (!$ja_processado) {
    if ($acertos > 0) {
        $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'primeiro_passo')");
    }

    if ($acertos == 3) {
        $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'perfeicao')");
    }

    if ($licao_atual == 3 && $acertos > 0) {
        if ($cap_atual == 1) {
            $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'capitulo_1')");
        } elseif ($cap_atual == 2) {
            $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'capitulo_2')");
        } elseif ($cap_atual == 3) {
            $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'capitulo_3')");
        } elseif ($cap_atual == 4) {
            $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'capitulo_4')");
        } elseif ($cap_atual == 5) {
            $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'capitulo_5')");
        }
    }

    $conn->query("UPDATE usuarios SET trofeus = (SELECT COUNT(*) FROM user_trofeus WHERE user_id = $user_id) WHERE id = $user_id");

    if ($acertos > 0) {
        $res_prog = $conn->query("SELECT * FROM progresso_usuario WHERE usuario_id = $user_id AND unidade_numero = $cap_atual");
        $progresso = $res_prog->fetch_assoc();

        if ($progresso && $progresso['status'] == 'corrente' && $progresso['licoes_concluidas'] == ($licao_atual - 1)) {
            if ($licao_atual == 3) {
                $conn->query("UPDATE progresso_usuario SET status = 'completo', licoes_concluidas = 3 WHERE usuario_id = $user_id AND unidade_numero = $cap_atual");

                if ($cap_atual < 5) {
                    $prox_cap = $cap_atual + 1;
                    $conn->query("UPDATE progresso_usuario SET status = 'corrente' WHERE usuario_id = $user_id AND unidade_numero = $prox_cap AND status = 'trancado'");
                }
            } else {
                $conn->query("UPDATE progresso_usuario SET licoes_concluidas = $licao_atual WHERE usuario_id = $user_id AND unidade_numero = $cap_atual");
            }
        }
    }
}

// 4. Descobre qual é a próxima lição para o Botão "Continuar"
$prox_licao = $licao_atual + 1;
$prox_cap_link = $cap_atual;

if ($licao_atual == 3) {
    $prox_licao = 1;
    $prox_cap_link = $cap_atual + 1;
}
$is_curso_finalizado = ($cap_atual == 5 && $licao_atual == 3);

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
    <style>
        .result-container {
            display: flex; justify-content: center; align-items: center; min-height: 80vh;
        }
        .result-card {
            background: rgba(20, 20, 28, 0.9); border: 2px solid <?php echo $cor_tema; ?>; 
            border-radius: 20px; padding: 40px; text-align: center; max-width: 500px;
            box-shadow: 0 0 30px rgba(0,0,0,0.5), inset 0 0 20px rgba(26, 54, 202, 0.2);
        }
        .mascote-img {
            width: 220px; /* Tamanho ligeiramente maior para o PNG dar mais impacto */
            height: auto;
            margin: 0 auto 20px auto;
            display: block;
            background: transparent;
            border: none;
            /* Sombra projetada que acompanha o recorte do PNG */
            filter: drop-shadow(0px 15px 15px rgba(0, 0, 0, 0.6));
        }
        .result-title {
            font-family: 'Orbitron', sans-serif; font-size: 2rem; color: #fff; margin-bottom: 10px;
        }
        .result-message {
            font-family: 'Poppins', sans-serif; color: #a0a0b0; font-size: 1.1rem; margin-bottom: 30px;
        }
        .xp-box {
            background: rgba(26, 54, 202, 0.1); padding: 15px; border-radius: 10px; margin-bottom: 30px;
            font-family: 'Orbitron'; font-size: 1.5rem; color: #a7b7e6; border: 1px solid <?php echo $cor_tema; ?>;
        }
        .buttons { display: flex; gap: 15px; justify-content: center; }
        .btn-action {
            padding: 12px 25px; font-family: 'Orbitron'; font-weight: bold; border-radius: 8px;
            text-decoration: none; border: none; cursor: pointer; transition: 0.3s;
        }
        .btn-next { background: <?php echo $cor_tema; ?>; color: #fff; }
        .btn-next:hover { transform: scale(1.05); filter: brightness(1.2); box-shadow: 0 0 15px <?php echo $cor_tema; ?>; }
        .btn-dash { background: #2a2a35; color: #fff; border: 1px solid #404050; }
        .btn-dash:hover { background: #404050; }
    </style>
</head>
<body>
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
                    <?php if ($perdeu_vida): ?>
                        <p class="result-message" style="color:#ff8a8a;">
                            <i class="fa-solid fa-heart-crack"></i>
                            <?php echo $vidas_restantes > 0
                                ? "Vidas restantes: {$vidas_restantes}/3. A próxima vida volta em 24h."
                                : "Você ficou sem vidas. Aguarde 24h para recuperar 1 coração."; ?>
                        </p>
                    <?php endif; ?>

                    <div class="buttons">
                        <a href="dashboard.php" class="btn-action btn-dash"><i class="fa-solid fa-house"></i> Dashboard</a>
                        
                        <?php if ($acertos > 0): ?>
                            <?php if (!$is_curso_finalizado): ?>
                                <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-next">
                                    Próxima Lição <i class="fa-solid fa-arrow-right"></i>
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