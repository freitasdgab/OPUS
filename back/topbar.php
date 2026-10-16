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
    
    $foto_banco = $_SESSION['foto_perfil'] ?? $dados_top['foto_perfil'] ?? '';

    if (!empty($foto_banco)) {
        $avatar_top = $foto_banco;
    } else {
        $avatar_top = '../assets/img/opi pulando feliz.png';
    }
}

$titulo_vidas = $proxima_vida_texto !== '' ? $proxima_vida_texto : ($vidas_top . ' de 3 vidas');
?>
<link rel="stylesheet" href="../assets/css/opus_alerta.css?v=<?= time() ?>">
<script src="../assets/js/opus_alerta.js?v=<?= time() ?>"></script>

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
        <a href="chat.php" class="topbar-stat stat-chat" id="btn-chat" title="Chat">
            <i class="fa-solid fa-comments"></i>
            <span class="badge-notif badge-chat-msg" id="badge-chat-msg" style="display: none;">0</span>
        </a>

        <!-- NOTIFICAÇÕES -->
        <div class="topbar-stat stat-notificacao" id="btn-notificacoes" title="Notificações" onclick="handleNotifClick(event)">
            <i class="fa-solid fa-bell"></i>
            <span class="badge-notif" id="badge-notif" style="display: none;">0</span>
        </div>

        <!-- FOGO (OFENSIVA / DIAS) -->
        <?php $fogo_ativo_top = !empty($dados_top["fogo_hoje"]); ?>
        <?php $fogo_class = $fogo_ativo_top ? "fogo-ativo" : "fogo-inativo"; ?>
        <div class="topbar-stat stat-fogo <?php echo $fogo_class; ?>" id="topbarStatFogo" title="<?php echo $fogo_ativo_top ? 'Ofensiva acesa hoje!' : 'Faça uma lição hoje para acender a ofensiva!'; ?>">
            <i class="fa-solid fa-fire"></i>
            <span id="topbarFogoCount"><?php echo (int) $dias_fogo_top; ?></span>
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

<script>
(function() {
    function apagarFogoMeiaNoite() {
        var topFogo = document.getElementById('topbarStatFogo');
        if (topFogo) {
            topFogo.classList.remove('fogo-ativo');
            topFogo.classList.add('fogo-inativo');
            topFogo.setAttribute('title', 'Faça uma lição hoje para acender a ofensiva!');
        }
        var wCard = document.querySelector('.widget-ofensiva');
        if (wCard) {
            wCard.classList.remove('fogo-ativo');
            wCard.classList.add('fogo-inativo');
        }
        var wBody = document.querySelector('.ofensiva-body');
        if (wBody) {
            wBody.classList.remove('fogo-ativo');
            wBody.classList.add('fogo-inativo');
        }
        var wStatus = document.querySelector('.ofensiva-status');
        if (wStatus) {
            wStatus.className = 'ofensiva-status ofensiva-pendente';
            wStatus.innerHTML = '<i class="fa-solid fa-hourglass-half"></i> Pratique hoje para manter sua sequência!';
        }
    }

    var agora = new Date();
    var meiaNoite = new Date(agora.getFullYear(), agora.getMonth(), agora.getDate() + 1, 0, 0, 0, 500);
    var msAteMeiaNoite = meiaNoite.getTime() - agora.getTime();
    if (msAteMeiaNoite > 0 && msAteMeiaNoite <= 86400000) {
        setTimeout(apagarFogoMeiaNoite, msAteMeiaNoite);
    }
})();
</script>
<script src="../assets/js/notificacoes.js?v=<?= time() ?>"></script>