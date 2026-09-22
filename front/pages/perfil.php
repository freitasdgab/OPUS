<?php
session_start();
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

$user_id =$_SESSION['user_id'];

$stmt_user =$conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);$stmt_user->execute();
$dados_user =$stmt_user->get_result()->fetch_assoc();

// Personalização de Avatar e Fundo (Corrigido para os nomes reais)
$foto_perfil = !empty($dados_user['foto_perfil']) ? $dados_user['foto_perfil'] : '../assets/img/opi pulando feliz.png';$cor_fundo = !empty($dados_user['cor_fundo']) ?$dados_user['cor_fundo'] : '#1cb0f6';

// Dados do Usuário
$nome_principal = !empty($dados_user['nome']) ?$dados_user['nome'] : 'Usuário';
$username = !empty($dados_user['username']) ?$dados_user['username'] : strtolower(str_replace(' ', '', $nome_principal)) .$user_id;
$email = !empty($dados_user['email']) ?$dados_user['email'] : 'usuario@email.com';

// Estatísticas
$xp_total = isset($dados_user['xp']) ?$dados_user['xp'] : 0; 
$dias_ofensiva = isset($dados_user['ofensiva']) ?$dados_user['ofensiva'] : 0;
$ligas_validas = ['Bronze', 'Prata', 'Ouro', 'Diamante'];$divisao = isset($dados_user['divisao']) && in_array($dados_user['divisao'], $ligas_validas) ?$dados_user['divisao'] : 'Bronze';

// Data formatada
$meses = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
if (!empty($dados_user['data_criacao'])) {$timestamp = strtotime($dados_user['data_criacao']);$mes_idx = (int)date('n', $timestamp);$ano = date('Y', $timestamp);$membro_desde = "Por aqui desde " . $meses[$mes_idx] . " de " . $ano;
} else {
    $membro_desde = "Por aqui desde " . $meses[(int)date('n')] . " de " . date('Y');
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
    <link rel="shortcut icon" href="../assets/img/LOGO.png">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    
    <link rel="stylesheet" href="../assets/css/perfil.css">
</head>
<body>

    <div class="app-container">
        
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../../back/topbar.php'; ?>

            <div class="profile-container">
                
                <!-- COLUNA ESQUERDA -->
                <div class="main-column">
                    
                    <div class="banner-section" style="background-color: <?php echo htmlspecialchars($cor_fundo); ?>;">
                        <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Avatar" class="avatar-img">
                        <button class="edit-banner-btn" onclick="openProfileModal()">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>

                    <div class="user-info">
                        <h1><?php echo htmlspecialchars($nome_principal); ?></h1>
                        <div class="user-details">
                            <span class="username">@<?php echo htmlspecialchars($username); ?></span>
                            <span class="email-text"><?php echo htmlspecialchars($email); ?></span>
                        </div>
                        <div class="member-since">
                            <i class="fa-regular fa-calendar-days"></i> <?php echo $membro_desde; ?>
                        </div>
                        
                        <div class="social-links">
                            <a href="#"><span>0</span> Seguindo</a>
                            <a href="#"><span>0</span> Seguidores</a>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="stats-section">
                        <h2>Estatísticas</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #ff9600;"><i class="fa-solid fa-fire"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $dias_ofensiva; ?></span>
                                    <span class="stat-card-label">Dias de ofensiva</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #ffc800;"><i class="fa-solid fa-bolt"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $xp_total; ?></span>
                                    <span class="stat-card-label">Total ganho</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #1cb0f6;"><i class="fa-solid fa-shield"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo htmlspecialchars($divisao); ?></span>
                                    <span class="stat-card-label">Liga atual</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="stats-section">
                        <h2>Conquistas</h2>
                        <!-- Container onde a API insere os troféus via JS -->
                        <div class="stats-grid" id="trophy-container">
                        </div>
                    </div>
                </div>

                <!-- COLUNA DIREITA -->
                <div class="side-column">
                    <div class="side-card">
                        <div class="side-card-title">Interagir</div>
                        <ul class="side-card-list">
                            <li onclick="alert('Recurso em desenvolvimento!')">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <i class="fa-solid fa-magnifying-glass" style="font-size: 1.2rem; color: #1cb0f6;"></i> Encontrar amigos
                                </div>
                                <i class="fa-solid fa-chevron-right"></i>
                            </li>
                            <li onclick="alert('Convite copiado!')">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <i class="fa-solid fa-share-nodes" style="font-size: 1.2rem; color: #ffc800;"></i> Convidar amigos
                                </div>
                                <i class="fa-solid fa-chevron-right"></i>
                            </li>
                        </ul>
                    </div>
                    
                    <button class="btn-outline" onclick="openLogoutModal()">Sair da Conta</button>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL DE EDIÇÃO -->
    <div class="modal-overlay" id="profileModal">
        <div class="modal-content">
            <div class="modal-title">Editar Perfil</div>
            
            <form method="POST" action="../../back/atualizar_perfil.php">
                <input type="hidden" name="action" value="atualizar_aparencia">
                <input type="hidden" name="avatar_selecionado" id="input-avatar" value="<?php echo htmlspecialchars($foto_perfil); ?>">
                <input type="hidden" name="cor_selecionada" id="input-cor" value="<?php echo htmlspecialchars($cor_fundo); ?>">

                <div class="modal-subtitle">Escolha seu avatar</div>
                <!-- Nomes dos arquivos corrigidos conforme a pasta de imagens -->
                <div class="avatar-grid">
                    <div class="avatar-option" data-src="../assets/img/rosa.png"><img src="../assets/img/rosa.png" alt="Rosa"></div>
                    <div class="avatar-option" data-src="../assets/img/roxo.png"><img src="../assets/img/roxo.png" alt="Roxo"></div>
                    <div class="avatar-option" data-src="../assets/img/laranja.png"><img src="../assets/img/laranja.png" alt="Laranja"></div>
                    <div class="avatar-option" data-src="../assets/img/verdefeliz.png"><img src="../assets/img/verdefeliz.png" alt="Verde"></div>
                    <div class="avatar-option" data-src="../assets/img/opi pulando feliz.png"><img src="../assets/img/opi pulando feliz.png" alt="Feliz"></div>
                    <div class="avatar-option" data-src="../assets/img/acertoutudo.png"><img src="../assets/img/acertoutudo.png" alt="Acertou"></div>
                </div>

                <div class="modal-subtitle">Cor de fundo</div>
                <div class="color-grid">
                    <div class="color-option" style="background-color: #1cb0f6;" data-color="#1cb0f6"></div>
                    <div class="color-option" style="background-color: #e56565;" data-color="#e56565"></div>
                    <div class="color-option" style="background-color: #78c800;" data-color="#78c800"></div>
                    <div class="color-option" style="background-color: #ce82ff;" data-color="#ce82ff"></div>
                    <div class="color-option" style="background-color: #ff9600;" data-color="#ff9600"></div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-cancel" onclick="closeProfileModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-save">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO DE SAÍDA -->
    <div class="modal-overlay" id="logoutModal">
        <div class="modal-content" style="max-width: 380px; text-align: center;">
            <div class="modal-title">Sair da Conta</div>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Tem certeza que deseja sair da sua conta?</p>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeLogoutModal()">Cancelar</button>
                <a href="../../back/logout.php" class="btn-modal btn-danger" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Sim, Sair</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Config gerada pelo PHP: unico trecho dinamico da pagina, o resto da
        // logica mora em ../assets/js/perfil.js
        window.OPUS_PERFIL = {
            avatar: "<?php echo htmlspecialchars($foto_perfil); ?>",
            cor: "<?php echo htmlspecialchars($cor_fundo); ?>"
        };
    </script>
    <script src="../assets/js/perfil.js"></script>
</body>
</html>