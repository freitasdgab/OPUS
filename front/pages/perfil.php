<?php
session_start();
require_once '../../back/conexao.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Busca todos os dados do usuário para preencher as estatísticas
$stmt_user = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$dados_user = $stmt_user->get_result()->fetch_assoc();

// Define a foto padrão
$foto_perfil = !empty($dados_user['foto_perfil']) ? $dados_user['foto_perfil'] : 'assets/img/opi pulando feliz.png';

function obterCaminhoAvatar($path) {
    if (strpos($path, 'data:image') === 0) return $path;
    if (strpos($path, 'assets/') === 0) return '../' . $path;
    return '../assets/img/opi pulando feliz.png';
}

// ------------------------------------------------------------------
// DADOS DE ESTATÍSTICAS E DATA
// ------------------------------------------------------------------
$xp_total = isset($dados_user['xp']) ? $dados_user['xp'] : 515; 
$dias_ofensiva = isset($dados_user['ofensiva']) ? $dados_user['ofensiva'] : 2;

// Lógica de Ligas (Apenas as que você mencionou)
$ligas_validas = ['Bronze', 'Prata', 'Ouro', 'Diamante'];
$divisao = isset($dados_user['divisao']) && in_array($dados_user['divisao'], $ligas_validas) ? $dados_user['divisao'] : 'Bronze';

// Pega o @ de usuário (simulando caso não tenha salvo um username específico)
$nome_completo = isset($dados_user['nome']) ? $dados_user['nome'] : 'Usuário Opus';
$username = strtolower(str_replace(' ', '', $nome_completo)) . $user_id;

// Formata a data "Por aqui desde..."
$meses = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
if (!empty($dados_user['data_criacao'])) {
    $timestamp = strtotime($dados_user['data_criacao']);
    $mes_idx = (int)date('n', $timestamp);
    $ano = date('Y', $timestamp);
    $membro_desde = "Por aqui desde " . $meses[$mes_idx] . " de " . $ano;
} else {
    $membro_desde = "Por aqui desde maio de 2026";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Opus</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        /* =========================================
           ESTILO DUOLINGO DARK MODE (Fiel à Foto)
           ========================================= */
        :root {
            --bg-dark: #131f24;
            --border-color: #37464f;
            --text-main: #ffffff;
            --text-muted: #778590;
            --duo-blue: #1cb0f6;
            --duo-blue-hover: #1899d6;
            --banner-bg: #e56565; /* Vermelho/Rosa do banner */
        }

        body, html {
            background-color: var(--bg-dark);
            font-family: 'Nunito', sans-serif;
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .main-content {
            padding: 24px 40px;
            min-height: 100vh;
        }

        /* Layout em 2 colunas como na foto */
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 40px;
            max-width: 1000px;
            margin: 0 auto;
        }

        /* =========================================
           COLUNA ESQUERDA (Principal)
           ========================================= */
        .main-column {
            display: flex;
            flex-direction: column;
        }

        /* Banner */
        .banner-section {
            background-color: var(--banner-bg);
            height: 200px;
            border-radius: 16px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 24px;
        }

        .avatar-img {
            height: 160px;
            width: auto;
            object-fit: contain;
            /* Se for uma imagem vazada do Opi, fica perfeito. Se for quadrada, aplicamos borda arredondada */
            border-radius: 20px; 
        }

        /* Botão de editar no banner (Canto superior direito, como na foto) */
        .edit-banner-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: 0.2s;
            border: 2px solid transparent;
        }
        .edit-banner-btn:hover { background: rgba(255, 255, 255, 0.3); }

        /* Infos do Usuário */
        .user-info {
            margin-bottom: 30px;
        }

        .user-info h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .user-info .username {
            display: block;
            color: var(--text-muted);
            font-size: 1rem;
            margin-top: 4px;
            margin-bottom: 12px;
        }

        .user-info .member-since {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 15px;
        }

        .social-links {
            display: flex;
            gap: 20px;
        }
        .social-links a {
            color: var(--duo-blue);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.95rem;
        }
        .social-links a:hover { color: var(--duo-blue-hover); }

        /* Divisória fina igual a do Duolingo */
        .divider {
            height: 2px;
            background-color: var(--border-color);
            margin: 25px 0;
            border-radius: 2px;
        }

        /* Seção de Estatísticas */
        .stats-section h2 {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        /* Card de Estatística (Borda fina, fundo da cor do site) */
        .stat-card {
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-card-icon {
            font-size: 1.8rem;
            width: 35px;
            display: flex;
            justify-content: center;
        }

        .stat-card-content {
            display: flex;
            flex-direction: column;
        }

        .stat-card-value {
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .stat-card-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
        }


        /* =========================================
           COLUNA DIREITA (Sidebar Topo e Amigos)
           ========================================= */
        .side-column {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        /* Barra de Mini-stats do Topo (Fogo e Raiozinho) */
        .top-mini-stats {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 24px;
            font-size: 1rem;
            font-weight: 800;
            padding-top: 10px;
        }

        .mini-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-main);
        }
        
        .mini-stat.fire { color: #ff9600; }
        .mini-stat.xp { color: #1cb0f6; } /* Raiozinho Azul/Amarelo */

        /* Cards da Lateral (Borda sutil, arredondada) */
        .side-card {
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
        }

        .side-card-title {
            font-size: 1.1rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .side-card-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .side-card-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 2px solid var(--border-color);
            cursor: pointer;
            color: var(--text-main);
            font-weight: 700;
            font-size: 0.95rem;
        }
        .side-card-list li:last-child { border-bottom: none; padding-bottom: 0; }
        .side-card-list li:hover { color: var(--text-muted); }
        .side-card-list i { color: var(--text-muted); }

        /* Botões Extras / Sair (Estilo Duolingo) */
        .btn-outline {
            display: block;
            text-align: center;
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            font-weight: 800;
            text-transform: uppercase;
            text-decoration: none;
            border: 2px solid var(--border-color);
            color: var(--text-muted);
            margin-top: 10px;
            transition: 0.2s;
        }
        .btn-outline:hover {
            background-color: var(--border-color);
            color: var(--text-main);
        }

        /* Responsividade */
        @media (max-width: 900px) {
            .profile-container { grid-template-columns: 1fr; }
            .top-mini-stats { justify-content: flex-start; padding-top: 0; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            
            <?php include '../../back/topbar.php'; ?>

            <div class="profile-container">
                
                <!-- COLUNA ESQUERDA: PERFIL E ESTATÍSTICAS -->
                <div class="main-column">
                    
                    <!-- BANNER -->
                    <div class="banner-section">
                        <img src="<?php echo htmlspecialchars(obterCaminhoAvatar($foto_perfil)); ?>" alt="Avatar" class="avatar-img">
                        
                        <form id="form-foto" action="../../back/atualizar_perfil.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="atualizar_foto">
                            <label for="foto-upload" class="edit-banner-btn" title="Editar Perfil">
                                <i class="fa-solid fa-pen"></i>
                            </label>
                            <input type="file" id="foto-upload" name="foto" accept="image/*" hidden onchange="document.getElementById('form-foto').submit();">
                        </form>
                    </div>

                    <!-- INFOS DO USUÁRIO -->
                    <div class="user-info">
                        <h1><?php echo htmlspecialchars($nome_completo); ?></h1>
                        <span class="username"><?php echo htmlspecialchars($username); ?></span>
                        <div class="member-since"><?php echo $membro_desde; ?></div>
                        
                        <div class="social-links">
                            <a href="#">Segue 0</a>
                            <a href="#">Tem 0 seguidores</a>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- ESTATÍSTICAS (Apenas com dados do seu site) -->
                    <div class="stats-section">
                        <h2>Estatísticas</h2>
                        
                        <div class="stats-grid">
                            
                            <!-- Ofensiva -->
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #ff9600;">
                                    <i class="fa-solid fa-fire"></i>
                                </div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $dias_ofensiva; ?></span>
                                    <span class="stat-card-label">Dias de ofensiva</span>
                                </div>
                            </div>
                            
                            <!-- O XP agora é só o Raiozinho, sem a palavra XP -->
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #ffc800;">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $xp_total; ?></span>
                                    <span class="stat-card-label">Total ganho</span>
                                </div>
                            </div>

                            <!-- Liga / Divisão (Restrito à Bronze, Prata, Ouro, Diamante) -->
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #1cb0f6;">
                                    <i class="fa-solid fa-shield"></i>
                                </div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $divisao; ?></span>
                                    <span class="stat-card-label">Liga atual</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Divisória antes das conquistas (se houver) -->
                    <div class="divider"></div>

                    <!-- Conquistas (Mantido simples e limpo, caso você use) -->
                    <div class="stats-section">
                        <h2>Conquistas</h2>
                        <div class="stats-grid" id="trophy-container">
                            <!-- Inserido dinamicamente via JS (API de conquistas) -->
                        </div>
                    </div>

                </div>

                <!-- COLUNA DIREITA: LATERAL -->
                <div class="side-column">
                    
                    <!-- Mini stats do topo (Ofensiva e Raio/XP) -->
                    <div class="top-mini-stats">
                        <div class="mini-stat fire">
                            <i class="fa-solid fa-fire"></i> 
                            <span><?php echo $dias_ofensiva; ?></span>
                        </div>
                        <div class="mini-stat xp" style="color: #1cb0f6;">
                            <i class="fa-solid fa-bolt" style="color: #1cb0f6;"></i> 
                            <span style="color: #1cb0f6;"><?php echo $xp_total; ?></span>
                        </div>
                    </div>

                    <!-- Card de Seguir (Vazio, simulando a imagem) -->
                    <div class="side-card" style="text-align: center; padding: 40px 20px;">
                        <span style="color: var(--text-muted); font-size: 0.95rem;">
                            Aprender é mais divertido e eficaz quando a gente se junta!
                        </span>
                    </div>

                    <!-- Card de Ações (Como "Adicionar amigos") -->
                    <div class="side-card">
                        <div class="side-card-title">Interagir</div>
                        <ul class="side-card-list">
                            <li>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <i class="fa-solid fa-magnifying-glass" style="font-size: 1.2rem; color: #1cb0f6;"></i> Encontrar amigos
                                </div>
                                <i class="fa-solid fa-chevron-right"></i>
                            </li>
                            <li>
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <i class="fa-solid fa-share-nodes" style="font-size: 1.2rem; color: #ffc800;"></i> Convidar amigos
                                </div>
                                <i class="fa-solid fa-chevron-right"></i>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Botão de Sair adaptado à lateral -->
                    <a href="../../back/logout.php" class="btn-outline">
                        Sair da Conta
                    </a>

                </div>

            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script> 
    
    <script>
        // Script de conquistas seguindo o layout limpo dos stats
        fetch('../../back/api_conquistas.php')
        .then(r => r.json())
        .then(data => {
            const container = document.getElementById('trophy-container');
            
            data.lista.forEach(t => {
                const isUnlocked = data.conquistados.includes(t.slug);
                const opacity = isUnlocked ? '1' : '0.4';
                const cor = isUnlocked ? '#ffc800' : 'var(--border-color)';
                
                container.innerHTML += `
                    <div class="stat-card" style="opacity: ${opacity};">
                        <div class="stat-card-icon" style="color: ${cor};">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <div class="stat-card-content">
                            <span class="stat-card-value" style="font-size: 1rem;">${t.nome}</span>
                            <span class="stat-card-label" style="font-size: 0.8rem;">${t.desc}</span>
                        </div>
                    </div>`;
            });
        })
        .catch(error => console.error('Erro ao carregar conquistas:', error));
    </script>
</body>
</html>