<link rel="shortcut icon" href="../assets/img/logo.png">

<?php
session_start();
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Busca os dados do usuário
$stmt_user = $conn->prepare("SELECT nome, xp, trofeus, dificuldade FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$dados_user = $stmt_user->get_result()->fetch_assoc();

// 2. Busca o status das unidades
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

// Cálculo de progresso total (quantos capítulos estão 100% completos)
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
    
    <style>
        .unit-list { max-height: 600px; overflow-y: auto; padding-right: 8px; }
        .unit-list::-webkit-scrollbar { width: 6px; }
        .unit-list::-webkit-scrollbar-thumb { background: rgba(26, 54, 202, 0.4); border-radius: 4px; }

        /* Container do Capítulo */
        .capitulo-container {
            background: rgba(20, 20, 28, 0.7);
            border: 1px solid rgba(26, 54, 202, 0.2);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .capitulo-header h2 {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.1rem;
            color: #fff;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 4px solid #1a36ca;
            padding-left: 10px;
        }

        /* Alinhamento dos Módulos em Trilha (Estilo Duolingo) */
        .trail-flex {
            display: flex;
            justify-content: center;
            gap: 40px;
            align-items: center;
            padding: 10px 0;
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
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            position: relative;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            transition: all 0.3s ease;
        }

        .modulo-node span {
            font-family: 'Poppins', sans-serif;
            font-size: 0.85rem;
            margin-top: 8px;
            font-weight: 600;
            color: #a0a0b0;
        }

        /* ESTADOS DO MÓDULO (Completo, Atual e Trancado) */
        
        /* 1. Concluído (Verde ou Azul Brilhante) */
        .modulo-node.completed .circle-button {
            background: linear-gradient(135deg, #10b981, #059669);
            border: 3px solid #34d399;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }
        .modulo-node.completed span { color: #34d399; }
        .modulo-node.completed:hover { transform: scale(1.08); }

        /* 2. Módulo Atual / Em progresso (Tema Azul da sua ID Visual) */
        .modulo-node.current .circle-button {
            background: linear-gradient(135deg, #1a36ca, #0a1b80);
            border: 3px solid #4d66f5;
            box-shadow: 0 0 20px rgba(26, 54, 202, 0.6);
            animation: pulse 2s infinite;
        }
        .modulo-node.current span { color: #4d66f5; }
        .modulo-node.current:hover { transform: scale(1.08); }

        /* 3. Trancado (Cinza opaco) */
        .modulo-node.locked {
            cursor: not-allowed;
            opacity: 0.5;
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
<a href="ranking.html" class="nav-link"><i class="fa-solid fa-ranking-star"></i> Ranking</a>                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="stats">
                    <div class="stat-box difficulty"><i class="fa-solid fa-fire"></i> <?php echo $dados_user['dificuldade'] ?? 'Iniciante'; ?> <small>Dificuldade</small></div>
                    <div class="stat-box trophies"><i class="fa-solid fa-trophy"></i> <?php echo $dados_user['trofeus'] ?? 0; ?> <small>Troféus</small></div>
                    <div class="stat-box xp"><i class="fa-solid fa-star"></i> <?php echo number_format($dados_user['xp'] ?? 0); ?> <small>Total XP</small></div>
                </div>
                <div class="user-info">
                    <span><?php echo htmlspecialchars($dados_user['nome'] ?? 'Usuário'); ?></span>
                    <img src="../assets/img/logo.png" alt="Avatar" class="avatar">
                </div>
            </header>

            <section class="dashboard-grid">
                <div class="curriculum-column">
                    <div class="path-header">
                        <h1>Meu Caminho em Java</h1>
                        <div class="progress-wrapper">
                            <div class="bar-bg"><div class="bar-fill" style="width: <?php echo $porcentagem_total; ?>%;"></div></div>
                            <span><?php echo round($porcentagem_total); ?>% Concluído</span>
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
                                        
                                        // 1. Define a regra de bloqueio inteligente das bolinhas
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