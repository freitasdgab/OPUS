<?php
session_start();
require_once '../../back/conexao.php'; // Ajuste o caminho se necessário
include '../../back/topbar.php'; // Trazendo sua barra superior dinâmica!

if (!isset($_SESSION['user_id']) || !isset($_POST['acertos'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$acertos = (int)$_POST['acertos'];
$cap_atual = (int)$_POST['cap'];
$licao_atual = (int)$_POST['licao'];

// Paleta Azul Fixa para o Opus
$cor_tema = "#1a36ca"; 

// 1. Mensagens e Imagens PNG Dinâmicas com base nos acertos
if ($acertos == 3) {
    $mensagem = "Perfeito! Você dominou o conteúdo!";
    $imagem_mascote = "../img/acertoutudo.png";
    $xp_ganho = 150;
} elseif ($acertos == 2) {
    $mensagem = "Muito bom! Você está no caminho certo!";
    $imagem_mascote = "../img/acertou_algumas.png";
    $xp_ganho = 100;
} elseif ($acertos == 1) {
    $mensagem = "Foi por pouco! Que tal revisar o conteúdo?";
    $imagem_mascote = "../img/acertou_algumas.png";
    $xp_ganho = 50;
} else {
    $mensagem = "Não desanime! A programação exige prática. Tente novamente!";
    $imagem_mascote = "../img/errou_tudo.png";
    $xp_ganho = 10; // Um incentivo por tentar
}

// 2. Atualizar o XP do Usuário
$conn->query("UPDATE usuarios SET xp = xp + $xp_ganho WHERE id = $user_id");

// ==========================================
// MÁGICA DOS TROFÉUS (Adicionado agora!)
// ==========================================

// Troféu 1: Primeiro Passo (Se ele acertou pelo menos 1, concluiu a lição)
if ($acertos > 0) {
    $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'primeiro_passo')");
}

// Troféu 2: Mente Brilhante (Se ele acertou todas as 3)
if ($acertos == 3) {
    $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'perfeicao')");
}

// Troféu 3: Fundamentos (Se ele estava na Lição 3 do Capítulo 1 e passou)
if ($cap_atual == 1 && $licao_atual == 3 && $acertos > 0) {
    $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, 'capitulo_1')");
}

// Atualiza o contador de troféus totais na tabela 'usuarios' para a barra superior mostrar o número certo
$conn->query("UPDATE usuarios SET trofeus = (SELECT COUNT(*) FROM user_trofeus WHERE user_id = $user_id) WHERE id = $user_id");

// ==========================================


// 3. Lógica para Avançar de Fase Automaticamente
$res_prog = $conn->query("SELECT * FROM progresso_usuario WHERE usuario_id = $user_id AND unidade_numero = $cap_atual");
$progresso = $res_prog->fetch_assoc();

if ($progresso && $progresso['status'] == 'corrente' && $progresso['licoes_concluidas'] == ($licao_atual - 1)) {
    
    if ($licao_atual == 3) {
        // Se era a lição 3, ele terminou o capítulo!
        $conn->query("UPDATE progresso_usuario SET status = 'completo', licoes_concluidas = 3 WHERE usuario_id = $user_id AND unidade_numero = $cap_atual");
        
        // Destravar o próximo capítulo (se existir, são 5 no total)
        if ($cap_atual < 5) {
            $prox_cap = $cap_atual + 1;
            $conn->query("UPDATE progresso_usuario SET status = 'corrente' WHERE usuario_id = $user_id AND unidade_numero = $prox_cap AND status = 'trancado'");
        }
    } else {
        // Apenas avança para a próxima lição dentro do mesmo capítulo
        $conn->query("UPDATE progresso_usuario SET licoes_concluidas = $licao_atual WHERE usuario_id = $user_id AND unidade_numero = $cap_atual");
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
            <div class="result-container">
                <div class="result-card">
                    <img src="<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-img">
                    
                    <h1 class="result-title"><?php echo $acertos; ?> / 3 Acertos</h1>
                    <p class="result-message"><?php echo $mensagem; ?></p>
                    
                    <div class="xp-box">
                        +<?php echo $xp_ganho; ?> XP
                    </div>

                    <div class="buttons">
                        <a href="dashboard.php" class="btn-action btn-dash"><i class="fa-solid fa-house"></i> Dashboard</a>
                        
                        <?php if (!$is_curso_finalizado && $acertos > 0): ?>
                            <a href="licao.php?cap=<?php echo $prox_cap_link; ?>&licao=<?php echo $prox_licao; ?>" class="btn-action btn-next">
                                Próxima Lição <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        <?php elseif ($is_curso_finalizado): ?>
                            <a href="conquistas.php" class="btn-action btn-next">Ver Troféu! <i class="fa-solid fa-trophy"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>