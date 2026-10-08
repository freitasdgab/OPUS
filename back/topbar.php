<?php
// Garante que a conexão sempre exista, independentemente de quem chamou a topbar
require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/jogador_status.php';

$nome_top = 'Usuário';
$xp_top = 0;
$trofeus_top = 0;
$liga_nome_top = 'Bronze';
$dias_fogo_top = 0;
$vidas_top = 3;
$proxima_vida_texto = '';
$avatar_top = '../assets/img/opi pulando feliz.png';

if (isset($_SESSION['user_id'])) {
    $user_id_topbar = (int) $_SESSION['user_id'];
    $dados_top = opus_sincronizar_jogador($conn, $user_id_topbar);

    $nome_top = $dados_top['nome'] ?? 'Usuário';
    $xp_top = $dados_top['xp'] ?? 0;
    $trofeus_top = $dados_top['trofeus'] ?? 0;
    $liga_nome_top = $dados_top['liga_nome'] ?? 'Bronze';
    $dias_fogo_top = $dados_top['dias_fogo'] ?? $dados_top['ofensiva'] ?? 0;
    $vidas_top = (int) ($dados_top['vidas'] ?? 3);
    $proxima_vida_texto = $dados_top['proxima_vida_texto'] ?? '';
    
    // CORREÇÃO AQUI: Pega da sessão (para atualizar na hora) ou do banco, sem bloquear caminhos normais
    $foto_banco = $_SESSION['foto_perfil'] ?? $dados_top['foto_perfil'] ?? '';

    if (!empty($foto_banco)) {
        $avatar_top = $foto_banco;
    } else {
        $avatar_top = '../assets/img/opi pulando feliz.png';
    }
}

$titulo_vidas = $proxima_vida_texto !== '' ? $proxima_vida_texto : ($vidas_top . ' de 3 vidas');
?>
<link rel="stylesheet" href="../assets/css/opus_alerta.css">
<script src="../assets/js/opus_alerta.js"></script>

<style>
/* Anula o cache do navegador e força a barra a colar perfeitamente no topo */
.main-content { padding-top: 0 !important; }
.top-bar { position: sticky !important; top: 0 !important; margin-top: 0 !important; }
@media (max-width: 768px) { .top-bar { margin-top: 0 !important; } }
</style>

<header class="top-bar">
    <!-- USUÁRIO -->
    <div class="user-info">
        <span><?php echo htmlspecialchars($nome_top); ?></span>
    </div>

    <div class="topbar-stats">
                <!-- CHAT -->
        <div class="topbar-stat stat-chat" title="Chat" onclick="window.location.href='chat.php'">
            <i class="fa-solid fa-comments"></i>
        </div>

        <!-- NOTIFICAÇÕES / MENSAGENS -->
        <div class="topbar-stat stat-notificacao" id="btn-notificacoes" title="Notificações" onclick="toggleNotificacoes()">
            <i class="fa-solid fa-bell"></i>
            <span class="badge-notif" id="badge-notif" style="display: none;">0</span>
        </div>

        <!-- FOGO (OFENSIVA / DIAS) -->
        <?php $fogo_class = ($dados_top["fogo_hoje"] ?? false) ? "fogo-ativo" : "fogo-inativo"; ?>
        <div class="topbar-stat stat-fogo <?php echo $fogo_class; ?>" title="Sequ�ncia Di�ria" <?php if(!($dados_top["fogo_hoje"] ?? false)) echo "style='color: #999;'"; ?>>
            <i class="fa-solid fa-fire" <?php if(!($dados_top["fogo_hoje"] ?? false)) echo "style='color: #999;'"; ?>></i>
            <span <?php if(!($dados_top["fogo_hoje"] ?? false)) echo "style='color: #999;'"; ?>><?php echo (int) $dias_fogo_top; ?></span>
        </div>

        <!-- XP TOTAL -->
        <div class="topbar-stat stat-xp" title="Total de XP">
            <i class="fa-solid fa-bolt"></i>
            <span><?php echo number_format((int) $xp_top, 0, ',', '.'); ?></span>
        </div>

        <!-- VIDAS -->
        <div class="topbar-stat stat-vida" title="<?php echo htmlspecialchars($titulo_vidas); ?>">
            <i class="fa-solid fa-heart"></i>
            <span><?php echo $vidas_top; ?></span>
        </div>
    </div>
</header>

<!-- DROPDOWN NOTIFICAÇÕES -->
<div class="notif-dropdown" id="notif-dropdown" style="display: none;">
    <div class="notif-header">
        <h3>Notificações</h3>
        <a href="chat.php" class="notif-link"><i class="fa-solid fa-paper-plane"></i> Abrir Chat</a>
    </div>
    <div class="notif-body" id="notif-list">
        <div class="notif-empty">Nenhuma notificação nova</div>
    </div>
</div>

<script src="../assets/js/notificacoes.js"></script>




