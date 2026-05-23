<?php
session_start();
require_once '../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Busca os dados do usuário com correção de sintaxe
$stmt_user = $conn->prepare("SELECT nome, xp, trofeus, dificuldade FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$dados_user = $stmt_user->get_result()->fetch_assoc();

// 2. Busca o status das unidades (Removida a query duplicada)
$unidades = [];
$result_progresso = $conn->query("SELECT unidade_numero, status, licoes_concluidas FROM progresso_usuario WHERE usuario_id = $user_id ORDER BY unidade_numero ASC");

if ($result_progresso) {
    while ($row = $result_progresso->fetch_assoc()) {
        $unidades[$row['unidade_numero']] = $row;
    }
}

// Configuração estática dos 8 Capítulos
$nomes_unidades = [
    1 => ["titulo" => "Unidade 1: Fundamentos e Sintaxe Básica", "total" => 5],
    2 => ["titulo" => "Unidade 2: Estruturas de Decisão", "total" => 5],
    3 => ["titulo" => "Unidade 3: Estruturas de Repetição (Loops)", "total" => 5],
    4 => ["titulo" => "Unidade 4: Estruturas de Dados e Métodos", "total" => 5],
    5 => ["titulo" => "Unidade 5: Introdução à POO", "total" => 5],
    6 => ["titulo" => "Unidade 6: Erros e Manipulação de Arquivos", "total" => 5],
    7 => ["titulo" => "Unidade 7: Avançando na POO (Pilares)", "total" => 5],
    8 => ["titulo" => "Unidade 8: Coleções Modernas (Collections)", "total" => 5]
];

// Cálculo de progresso total
$concluidas_res = $conn->query("SELECT COUNT(*) as total FROM progresso_usuario WHERE usuario_id = $user_id AND status = 'completo'");
$concluidas = $concluidas_res ? $concluidas_res->fetch_assoc()['total'] : 0;
$porcentagem_total = ($concluidas / 8) * 100; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Progresso - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .unit-link { text-decoration: none; color: inherit; display: block; }
        .unit-item.completed, .unit-item.current { cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
        .unit-item.completed:hover, .unit-item.current:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(26, 54, 202, 0.2); }
        .unit-item.locked { cursor: not-allowed; opacity: 0.6; }
        .unit-list { max-height: 550px; overflow-y: auto; padding-right: 8px; }
        .unit-list::-webkit-scrollbar { width: 6px; }
        .unit-list::-webkit-scrollbar-thumb { background: rgba(26, 54, 202, 0.4); border-radius: 4px; }
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
            <header class="top-bar">
                <div class="stats">
                    <div class="stat-box difficulty"><i class="fa-solid fa-fire"></i> <?php echo $dados_user['dificuldade'] ?? 'Iniciante'; ?> <small>Dificuldade</small></div>
                    <div class="stat-box trophies"><i class="fa-solid fa-trophy"></i> <?php echo $dados_user['trofeus'] ?? 0; ?> <small>Troféus</small></div>
                    <div class="stat-box xp"><i class="fa-solid fa-star"></i> <?php echo number_format($dados_user['xp'] ?? 0); ?> <small>Total XP</small></div>
                </div>
                <div class="user-info">
                    <span><?php echo $dados_user['nome'] ?? 'Usuário'; ?></span>
                    <img src="opi.png" alt="Avatar" class="avatar">
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
                        <?php foreach ($nomes_unidades as $num => $info): 
                            $status_banco = $unidades[$num]['status'] ?? 'trancado';
                            $licoes_feitas = $unidades[$num]['licoes_concluidas'] ?? 0;
                            
                            $classe_css = "locked";
                            $icone = "fa-lock";
                            $texto_status = "Trancado";
                            $url_destino = "licao.php?cap=" . $num . "&licao=1";

                            if ($status_banco === 'completo') {
                                $classe_css = "completed";
                                $icone = "fa-check";
                                $texto_status = "Concluído";
                            } elseif ($status_banco === 'corrente') {
                                $classe_css = "current";
                                $icone = "fa-rocket";
                                $texto_status = "Em Progresso";
                            }
                        ?>
                            <?php if ($status_banco !== 'trancado'): ?>
                                <a href="<?php echo $url_destino; ?>" class="unit-link">
                            <?php endif; ?>

                            <div class="unit-item <?php echo $classe_css; ?>">
                                <div class="unit-icon"><i class="fa-solid <?php echo $icone; ?>"></i></div>
                                <div class="unit-text">
                                    <h3><?php echo $info['titulo']; ?></h3>
                                    <p><?php echo ($status_banco !== 'trancado') ? "$licoes_feitas / " . $info['total'] . " Lições • $texto_status" : "Trancado"; ?></p>
                                </div>
                            </div>

                            <?php if ($status_banco !== 'trancado'): ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="script.js"></script> 
</body>
</html>