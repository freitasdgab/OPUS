<?php
session_start();
require_once '../../back/config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador não possui perfil de jogador
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

$user_id =$_SESSION['user_id'];

$stmt_user =$conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt_user->bind_param("i", $user_id);$stmt_user->execute();
$dados_user =$stmt_user->get_result()->fetch_assoc();

// Personalização de Avatar e Fundo (Sem foto padrão: ou tem ou não tem)
$foto_perfil = !empty($dados_user['foto_perfil']) ? trim($dados_user['foto_perfil']) : '';
$cor_fundo = !empty($dados_user['cor_fundo']) ? $dados_user['cor_fundo'] : '#1cb0f6';
$pais_usuario = !empty($dados_user['pais']) ? $dados_user['pais'] : 'Brasil';
$pais_codigo_usuario = !empty($dados_user['pais_codigo']) ? strtolower($dados_user['pais_codigo']) : 'br';

// Dados do Usuário
$nome_principal = !empty($dados_user['nome']) ?$dados_user['nome'] : 'Usuário';
$username = !empty($dados_user['username']) ?$dados_user['username'] : strtolower(str_replace(' ', '', $nome_principal)) .$user_id;
$email = !empty($dados_user['email']) ?$dados_user['email'] : 'usuario@email.com';

// Estatísticas
$xp_total = isset($dados_user['xp']) ?$dados_user['xp'] : 0; 
$dias_ofensiva = isset($dados_user['dias_fogo']) ? (int)$dados_user['dias_fogo'] : 0;
$ultima_atividade = !empty($dados_user['ultima_atividade']) ? $dados_user['ultima_atividade'] : '';
$fogo_hoje = ($ultima_atividade === date('Y-m-d') && $dias_ofensiva > 0);
$fogo_cor = $fogo_hoje ? '#ff9600' : '#8b949e';
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
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    
    <link rel="stylesheet" href="../assets/css/perfil.css?v=<?= time() ?>">
</head>
<body>

    <div class="app-container">
        
        <?php include '../../back/includes/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../../back/includes/topbar.php'; ?>

            <div class="profile-container">
                
                <!-- COLUNA ESQUERDA -->
                <div class="main-column">
                    
                    <div class="banner-section" style="background-color: <?php echo htmlspecialchars($cor_fundo); ?>;">
                        <?php if (!empty($foto_perfil)): ?>
                            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Avatar" class="avatar-img">
                        <?php else: ?>
                            <div class="avatar-placeholder" style="display:flex; align-items:center; justify-content:center; width:120px; height:120px; border-radius:50%; background:rgba(0,0,0,0.25); color:#fff; font-size:3.5rem; z-index:1;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <button class="edit-banner-btn" onclick="openProfileModal()">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                    </div>

                    <div class="user-info">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <h1><?php echo htmlspecialchars($nome_principal); ?></h1>
                            <i class="fa-brands fa-java" style="font-size: 2rem; color: #5382a1;"></i>
    <?php if(!empty($dados_user['link_github'])): ?>
        <a href="<?php echo htmlspecialchars($dados_user['link_github']); ?>" target="_blank" style="color: #fff; font-size: 1.5rem; transition: 0.2s; margin-left: 10px;" onmouseover="this.style.color='#1cb0f6'" onmouseout="this.style.color='#fff'" title="GitHub"><i class="fa-brands fa-github"></i></a>
    <?php endif; ?>
    <?php if(!empty($dados_user['link_instagram'])): ?>
        <a href="<?php echo htmlspecialchars($dados_user['link_instagram']); ?>" target="_blank" style="color: #fff; font-size: 1.5rem; transition: 0.2s; margin-left: 10px;" onmouseover="this.style.color='#e1306c'" onmouseout="this.style.color='#fff'" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
    <?php endif; ?>
    <?php if(!empty($dados_user['link_facebook'])): ?>
        <a href="<?php echo htmlspecialchars($dados_user['link_facebook']); ?>" target="_blank" style="color: #fff; font-size: 1.5rem; transition: 0.2s; margin-left: 10px;" onmouseover="this.style.color='#1877f2'" onmouseout="this.style.color='#fff'" title="Facebook"><i class="fa-brands fa-facebook"></i></a>
    <?php endif; ?>
    <?php if(!empty($dados_user['link_youtube'])): ?>
        <a href="<?php echo htmlspecialchars($dados_user['link_youtube']); ?>" target="_blank" style="color: #fff; font-size: 1.5rem; transition: 0.2s; margin-left: 10px;" onmouseover="this.style.color='#ff0000'" onmouseout="this.style.color='#fff'" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
    <?php endif; ?>
        <?php if(!empty($dados_user['link_linkedin'])): ?>
        <a href="<?php echo htmlspecialchars($dados_user['link_linkedin']); ?>" target="_blank" style="color: #fff; font-size: 1.5rem; transition: 0.2s; margin-left: 10px;" onmouseover="this.style.color='#0077b5'" onmouseout="this.style.color='#fff'" title="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
    <?php endif; ?>
    <?php if(!empty($dados_user['link_email'])): ?>
        <a href="mailto:<?php echo htmlspecialchars($dados_user['link_email']); ?>" style="color: #fff; font-size: 1.5rem; transition: 0.2s; margin-left: 10px;" onmouseover="this.style.color='#fbbc05'" onmouseout="this.style.color='#fff'" title="Email Público"><i class="fa-solid fa-envelope"></i></a>
    <?php endif; ?>
                        </div>
                        <div class="user-details" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span class="email-text"><?php echo htmlspecialchars($email); ?></span>
                            <div onclick="openProfileModal()" style="display: inline-flex; align-items: center; gap: 6px; background: #191924; border: 1px solid #2e2e42; border-radius: 20px; padding: 3px 10px; font-weight: 700; font-size: 0.85rem; color: #fff; cursor: pointer; transition: 0.2s;" onmouseover="this.style.borderColor='#1cb0f6'" onmouseout="this.style.borderColor='#2e2e42'" title="Alterar país de origem">
                                <img src="https://flagcdn.com/24x18/<?php echo htmlspecialchars($pais_codigo_usuario); ?>.png" srcset="https://flagcdn.com/48x36/<?php echo htmlspecialchars($pais_codigo_usuario); ?>.png 2x" width="18" height="13" alt="<?php echo htmlspecialchars($pais_usuario); ?>" style="border-radius: 2px;">
                                <span><?php echo htmlspecialchars($pais_usuario); ?></span>
                                <i class="fa-solid fa-pen" style="font-size: 0.7rem; color: #7a7a9d; margin-left: 2px;"></i>
                            </div>
                        </div>
                        <div class="member-since">
                            <?php echo strtolower($membro_desde); ?>
                        </div>
                        
                        <!-- ÁREA DE NOTIFICAÇÕES (Caixa de Aprovação) -->
                        <div id="notificacoesAreaPerfil" style="margin: 20px 0; display: none; background: #191924; border: 1px solid #2e2e42; border-left: 4px solid #1cb0f6; border-radius: 12px; padding: 14px; text-align: left;">
                            <h5 style="margin:0 0 12px 0; color:#fff; font-size:0.9rem;"><i class="fa-solid fa-bell" style="color:#1cb0f6; margin-right:6px;"></i> Pedidos de Conexão</h5>
                            <div id="listaPedidosPerfil" style="display:flex; flex-direction:column; gap:10px;"></div>
                        </div>

                            <button style="width: 100%; margin-bottom: 10px; padding: 12px; border-radius: 8px; font-weight: bold; font-family: 'Nunito', sans-serif; cursor: pointer; text-transform: uppercase; background-color: #1cb0f6; color: white; border: none;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'" onclick="document.getElementById('socialEditModal').style.display='flex'">Adicionar Redes Sociais</button>
<div class="social-links">
                            <a href="#" onclick="openSocialModal('seguindo')">seguindo <span id="lblSeguindoCount">0</span></a>
                            <a href="#" onclick="openSocialModal('seguidores')">seguidores <span id="lblSeguidoresCount">0</span></a>
                            <a href="#" onclick="openSocialModal('amigos')">amigos <span id="lblAmigosCount">0</span></a>
                        </div>
                    </div>

                    <div class="stats-section">
                        <h2>Estatisticas</h2>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: <?php echo $fogo_cor; ?>;"><i class="fa-solid fa-fire"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $dias_ofensiva; ?></span>
                                    <span class="stat-card-label">dias<br>seguidos</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #ffc800;"><i class="fa-solid fa-bolt"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value"><?php echo $xp_total; ?></span>
                                    <span class="stat-card-label">total de xp</span>
                                </div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-card-icon" style="color: #ffc800;"><i class="fa-solid fa-shield"></i></div>
                                <div class="stat-card-content">
                                    <span class="stat-card-value" style="font-size: 1rem;"><?php echo strtolower(htmlspecialchars($divisao)); ?></span>
                                    <span class="stat-card-label">divisão</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- COLUNA DIREITA -->
                <div class="side-column">
                    <div class="side-card interact-card">
                        <div onclick="copiarConvitePerfil(<?php echo $user_id; ?>)" class="interact-item">
                            <img src="../assets/img/azulcarta.png" alt="Convidar">
                            <span>convidar amigos</span>
                        </div>
                        <div onclick="window.location.href='chat.php'" class="interact-item">
                            <img src="../assets/img/azulpesquisar.png" alt="Encontrar">
                            <span>encontrar amigos</span>
                        </div>
                    </div>
                    
                    <div class="friends-list-container">
                        <div class="friends-list-title">lista de amigos</div>
                        <div class="side-card friends-card">
                            <div id="listaAmigosPerfil" class="friends-list">
                                <div style="color:var(--text-muted); font-size:0.9rem; padding: 15px;">Carregando amigos...</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEÇÃO CONQUISTAS (LARGURA TOTAL) -->
                <div class="stats-section profile-full-width">
                    <h2>Conquistas</h2>
                    <!-- Container onde a API insere os troféus via JS -->
                    <div class="stats-grid-trophies" id="trophy-container">
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL DE EDIÇÃO -->
    <div class="modal-overlay" id="profileModal">
        <div class="modal-content">
            <div class="modal-title">Editar Perfil</div>
            
            <form method="POST" action="../../back/api/atualizar_perfil.php">
                <input type="hidden" name="action" value="atualizar_aparencia">
                <input type="hidden" name="avatar_selecionado" id="input-avatar" value="<?php echo htmlspecialchars($foto_perfil); ?>">
                <input type="hidden" name="cor_selecionada" id="input-cor" value="<?php echo htmlspecialchars($cor_fundo); ?>">

                <div class="modal-subtitle">Escolha seu avatar</div>
                
                <div class="avatar-grid">
                    <div class="avatar-option" data-src="" title="Sem foto" style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; color:#94a3b8; font-weight:800; font-size:0.8rem; min-height:70px;">
                        <i class="fa-solid fa-user-slash" style="font-size: 1.8rem;"></i>
                        <span>Sem foto</span>
                    </div>
                    <div class="avatar-option" data-src="../assets/img/rosa.png" title="Skatista"><img src="../assets/img/rosa.png" alt="Skatista"></div>
                    <div class="avatar-option" data-src="../assets/img/roxo.png" title="Gamer"><img src="../assets/img/roxo.png" alt="Gamer"></div>
                    <div class="avatar-option" data-src="../assets/img/laranja.png" title="Rockstar"><img src="../assets/img/laranja.png" alt="Rockstar"></div>
                    <div class="avatar-option" data-src="../assets/img/verdefeliz.png" title="Atleta"><img src="../assets/img/verdefeliz.png" alt="Atleta"></div>
                    <div class="avatar-option" data-src="../assets/img/azulpesquisar.png" title="Detetive"><img src="../assets/img/azulpesquisar.png" alt="Detetive"></div>
                    <div class="avatar-option" data-src="../assets/img/acertoutudo.png" title="Campeão"><img src="../assets/img/acertoutudo.png" alt="Campeão"></div>
                </div>

                <div class="modal-subtitle">Cor de fundo</div>
                <div class="color-grid">
                    <div class="color-option" style="background-color: #1cb0f6;" data-color="#1cb0f6"></div>
                    <div class="color-option" style="background-color: #e56565;" data-color="#e56565"></div>
                    <div class="color-option" style="background-color: #78c800;" data-color="#78c800"></div>
                    <div class="color-option" style="background-color: #ce82ff;" data-color="#ce82ff"></div>
                    <div class="color-option" style="background-color: #ff9600;" data-color="#ff9600"></div>
                </div>

                <div class="modal-subtitle">País de Origem</div>
                <div style="margin-bottom: 20px;">
                    <select name="pais_codigo" id="select-pais" style="width: 100%; padding: 12px; border-radius: 10px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif; font-size: 1rem; font-weight: 700; cursor: pointer; outline: none;">
                        <option value="br" <?php echo ($pais_codigo_usuario === 'br') ? 'selected' : ''; ?>>🇧🇷 Brasil</option>
                        <option value="de" <?php echo ($pais_codigo_usuario === 'de') ? 'selected' : ''; ?>>🇩🇪 Alemanha</option>
                        <option value="us" <?php echo ($pais_codigo_usuario === 'us') ? 'selected' : ''; ?>>🇺🇸 Estados Unidos</option>
                        <option value="pt" <?php echo ($pais_codigo_usuario === 'pt') ? 'selected' : ''; ?>>🇵🇹 Portugal</option>
                        <option value="ar" <?php echo ($pais_codigo_usuario === 'ar') ? 'selected' : ''; ?>>🇦🇷 Argentina</option>
                        <option value="ca" <?php echo ($pais_codigo_usuario === 'ca') ? 'selected' : ''; ?>>🇨🇦 Canadá</option>
                        <option value="es" <?php echo ($pais_codigo_usuario === 'es') ? 'selected' : ''; ?>>🇪🇸 Espanha</option>
                        <option value="fr" <?php echo ($pais_codigo_usuario === 'fr') ? 'selected' : ''; ?>>🇫🇷 França</option>
                        <option value="it" <?php echo ($pais_codigo_usuario === 'it') ? 'selected' : ''; ?>>🇮🇹 Itália</option>
                        <option value="jp" <?php echo ($pais_codigo_usuario === 'jp') ? 'selected' : ''; ?>>🇯🇵 Japão</option>
                        <option value="gb" <?php echo ($pais_codigo_usuario === 'gb') ? 'selected' : ''; ?>>🇬🇧 Reino Unido</option>
                        <option value="mx" <?php echo ($pais_codigo_usuario === 'mx') ? 'selected' : ''; ?>>🇲🇽 México</option>
                        <option value="cl" <?php echo ($pais_codigo_usuario === 'cl') ? 'selected' : ''; ?>>🇨🇱 Chile</option>
                        <option value="co" <?php echo ($pais_codigo_usuario === 'co') ? 'selected' : ''; ?>>🇨🇴 Colômbia</option>
                        <option value="uy" <?php echo ($pais_codigo_usuario === 'uy') ? 'selected' : ''; ?>>🇺🇾 Uruguai</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-cancel" onclick="closeProfileModal()">Cancelar</button>
                    <button type="submit" class="btn-modal btn-save">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SOCIAL (Amigos, Seguidores, Seguindo) -->
    <div class="modal-overlay" id="socialModal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-title" id="socialModalTitle">Amigos</div>
            <div id="socialModalList" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; display: flex; flex-direction: column; gap: 10px;">
                <div style="color:var(--text-muted); text-align:center;">Carregando...</div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-modal btn-cancel" onclick="closeSocialModal()" style="width: 100%;">Voltar</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE EDITAR REDES SOCIAIS -->
    <div class="modal-overlay" id="socialEditModal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-title">Adicionar Redes Sociais</div>
            <form method="POST" action="../../back/api/atualizar_perfil.php">
                <input type="hidden" name="action" value="update_social">
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    <input type="url" name="link_github" placeholder="Link do GitHub (https://github.com/...)" value="<?php echo htmlspecialchars($dados_user['link_github'] ?? ''); ?>" style="padding: 10px; border-radius: 8px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif;">
                    <input type="url" name="link_instagram" placeholder="Link do Instagram (https://instagram.com/...)" value="<?php echo htmlspecialchars($dados_user['link_instagram'] ?? ''); ?>" style="padding: 10px; border-radius: 8px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif;">
                    <input type="url" name="link_facebook" placeholder="Link do Facebook (https://facebook.com/...)" value="<?php echo htmlspecialchars($dados_user['link_facebook'] ?? ''); ?>" style="padding: 10px; border-radius: 8px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif;">
                    <input type="url" name="link_youtube" placeholder="Link do YouTube (https://youtube.com/...)" value="<?php echo htmlspecialchars($dados_user['link_youtube'] ?? ''); ?>" style="padding: 10px; border-radius: 8px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif;">
                    <input type="url" name="link_linkedin" placeholder="Link do LinkedIn (https://linkedin.com/...)" value="<?php echo htmlspecialchars($dados_user['link_linkedin'] ?? ''); ?>" style="padding: 10px; border-radius: 8px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif;">
                    <input type="email" name="link_email" placeholder="E-mail Público" value="<?php echo htmlspecialchars($dados_user['link_email'] ?? ''); ?>" style="padding: 10px; border-radius: 8px; border: 2px solid #2e2e42; background: #161a24; color: #fff; font-family: 'Nunito', sans-serif;">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-modal btn-cancel" onclick="document.getElementById('socialEditModal').style.display='none'">Cancelar</button>
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
                <a href="../../back/auth/logout.php" class="btn-modal btn-danger" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Sim, Sair</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/script.js?v=<?= time() ?>"></script>
    <script>
        // Config gerada pelo PHP: unico trecho dinamico da pagina, o resto da
        // logica mora em ../assets/js/perfil.js
        window.OPUS_PERFIL = {
            avatar: "<?php echo htmlspecialchars($foto_perfil); ?>",
            cor: "<?php echo htmlspecialchars($cor_fundo); ?>"
        };
    </script>
    <script src="../assets/js/perfil.js?v=<?= time() ?>"></script>
</body>
</html>
