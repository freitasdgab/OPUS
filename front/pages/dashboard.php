<?php
session_start();
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Busca apenas as configurações de exibição da tela do dashboard (o restante vai para o topbar.php)
$stmt_user = $conn->prepare("SELECT dificuldade FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$dados_user = $stmt_user->get_result()->fetch_assoc();

// 2. Busca o status das unidades para montar o grid de capítulos
$unidades = [];
$result_progresso = $conn->query("SELECT unidade_numero, status, licoes_concluidas FROM progresso_usuario WHERE usuario_id = $user_id ORDER BY unidade_numero ASC");

if ($result_progresso) {
    while ($row = $result_progresso->fetch_assoc()) {
        $unidades[$row['unidade_numero']] = $row;
    }
}

// Configuração estática: 5 Capítulos
$nomes_unidades = [
    1 => ["titulo" => "Capítulo 1: Fundamentos e Sintaxe Básica"],
    2 => ["titulo" => "Capítulo 2: Estruturas de Decisão"],
    3 => ["titulo" => "Capítulo 3: Estruturas de Repetição"],
    4 => ["titulo" => "Capítulo 4: Arrays e Matrizes"],
    5 => ["titulo" => "Capítulo 5: Introdução à POO"]
];

// Cálculo de progresso total
$concluidas_res = $conn->query("SELECT COUNT(*) as total FROM progresso_usuario WHERE usuario_id = $user_id AND status = 'completo' AND unidade_numero <= 5");
$concluidas = $concluidas_res ? $concluidas_res->fetch_assoc()['total'] : 0;
$porcentagem_total = ($concluidas / 5) * 100; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Progresso - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    
    <style>
        /* FORÇAR O CONTAINER A USAR 100% DA TELA E IGNORAR O DASHBOARD.CSS ANTIGO */
        .dashboard-grid, .curriculum-column {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
        }

        /* DISTRIBUIÇÃO DOS CAPÍTULOS NA TELA (GRID LADO A LADO) */
        .unit-list { 
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 25px; 
            padding: 10px;
            margin-top: 30px;
            width: 100%;
        }

        /* Container do Capítulo */
        .capitulo-container {
            background: rgba(20, 20, 28, 0.7);
            border: 1px solid rgba(26, 54, 202, 0.2);
            border-radius: 12px;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .capitulo-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(26, 54, 202, 0.3);
        }

        .capitulo-header h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 4px solid #1a36ca;
            padding-left: 10px;
            min-height: 45px;
        }

        /* Alinhamento dos Módulos em Trilha */
        .trail-flex {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            width: 100%;
        }

        /* Estilo base da Bolinha do Módulo */
        .modulo-node {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: #fff;
            transition: transform 0.2s;
        }
        
        .circle-button {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }

        .modulo-node span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            margin-top: 10px;
            font-weight: 600;
            color: #a0a0b0;
        }

        /* ESTADOS DO MÓDULO */
        .modulo-node.completed .circle-button {
            background: linear-gradient(135deg, #10b981, #059669);
            border: 3px solid #34d399;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }
        .modulo-node.completed span { color: #34d399; }
        .modulo-node.completed:hover { transform: scale(1.1); }

        .modulo-node.current .circle-button {
            background: linear-gradient(135deg, #1a36ca, #0a1b80);
            border: 3px solid #4d66f5;
            box-shadow: 0 0 20px rgba(26, 54, 202, 0.6);
            animation: pulse 2s infinite;
        }
        .modulo-node.current span { color: #4d66f5; }
        .modulo-node.current:hover { transform: scale(1.1); }

        .modulo-node.locked {
            cursor: not-allowed;
            opacity: 0.4;
        }
        .modulo-node.locked .circle-button {
            background: #2a2a35;
            border: 3px solid #404050;
            color: #707080;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(26, 54, 202, 0.7); }
            70% { box-shadow: 0 0 20px 10px rgba(26, 54, 202, 0); }
            100% { box-shadow: 0 0 0 0 rgba(26, 54, 202, 0); }
        }
    </style>
</head>
<body>
    <canvas id="bg-canvas"></canvas>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">OPUS</div>
            <nav class="menu">
                <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> Progresso</a>
                <a href="conquistas.php" class="nav-link"><i class="fa-solid fa-award"></i> Conquistas</a>
                <a href="ranking.php" class="nav-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>
                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

        <main class="main-content">
            
            <?php include '../../back/topbar.php'; ?>

            <section class="dashboard-grid">
                <div class="curriculum-column">
                    <div class="path-header" style="text-align: center; margin-bottom: 20px;">
                        <h1 style="font-family: 'Orbitron'; color: #fff; font-size: 2rem;">Meu Caminho em Java</h1>
                        <div class="progress-wrapper" style="max-width: 600px; margin: 15px auto;">
                            <div class="bar-bg" style="background: rgba(255,255,255,0.1); height: 12px; border-radius: 10px; overflow: hidden;">
                                <div class="bar-fill" style="background: <?php echo $porcentagem_total == 100 ? 'linear-gradient(90deg, #ffd700, #ffa500)' : '#4d66f5'; ?>; height: 100%; width: <?php echo $porcentagem_total; ?>%; transition: width 1s ease; box-shadow: <?php echo $porcentagem_total == 100 ? '0 0 15px rgba(255, 215, 0, 0.6)' : 'none'; ?>;"></div>
                            </div>
                            <?php if ($porcentagem_total == 100): ?>
                                <span style="color: #ffd700; font-size: 1rem; margin-top: 8px; display: block; font-weight: 700; font-family: 'Orbitron', sans-serif; text-shadow: 0 0 10px rgba(255,215,0,0.3);"><i class="fa-solid fa-trophy"></i> CAMINHO CONCLUÍDO! 100% 🏆</span>
                            <?php else: ?>
                                <span style="color: #a0a0b0; font-size: 0.9rem; margin-top: 8px; display: block;"><?php echo round($porcentagem_total); ?>% Concluído</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="unit-list">
                        <?php foreach ($nomes_unidades as $num_cap => $info): 
                            $status_cap_banco = $unidades[$num_cap]['status'] ?? 'trancado';
                            $licoes_feitas = $unidades[$num_cap]['licoes_concluidas'] ?? 0;
                        ?>
                            <div class="capitulo-container">
                                <div class="capitulo-header">
                                    <h2><?php echo $info['titulo']; ?></h2>
                                </div>
                                
                                <div class="trail-flex">
                                    <?php for ($mod = 1; $mod <= 3; $mod++): 
                                        
                                        $classe_modulo = "locked";
                                        $icone_modulo = "fa-lock";
                                        $is_clicavel = false;

                                        if ($status_cap_banco === 'completo') {
                                            $classe_modulo = "completed";
                                            $icone_modulo = "fa-check";
                                            $is_clicavel = true;
                                        } elseif ($status_cap_banco === 'corrente') {
                                            if ($mod <= $licoes_feitas) {
                                                $classe_modulo = "completed";
                                                $icone_modulo = "fa-check";
                                                $is_clicavel = true;
                                            } elseif ($mod == $licoes_feitas + 1) {
                                                $classe_modulo = "current";
                                                $icone_modulo = "fa-play";
                                                $is_clicavel = true;
                                            } else {
                                                $classe_modulo = "locked";
                                                $icone_modulo = "fa-lock";
                                                $is_clicavel = false;
                                            }
                                        }
                                        
                                        $url_destino = "licao.php?cap=" . $num_cap . "&licao=" . $mod;
                                    ?>
                                        
                                        <?php if ($is_clicavel): ?>
                                            <a href="<?php echo $url_destino; ?>" class="modulo-node <?php echo $classe_modulo; ?>">
                                                <div class="circle-button">
                                                    <i class="fa-solid <?php echo $icone_modulo; ?>"></i>
                                                </div>
                                                <span>Módulo <?php echo $mod; ?></span>
                                            </a>
                                        <?php else: ?>
                                            <div class="modulo-node <?php echo $classe_modulo; ?>">
                                                <div class="circle-button">
                                                    <i class="fa-solid <?php echo $icone_modulo; ?>"></i>
                                                </div>
                                                <span>Módulo <?php echo $mod; ?></span>
                                            </div>
                                        <?php endif; ?>

                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../assets/js/script.js"></script> 
</body>
</html>