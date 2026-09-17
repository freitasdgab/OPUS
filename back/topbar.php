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


<header class="top-bar">
    <div class="topbar-stats">
        <!-- LIGA / DIVISÃO -->
        <a href="ligas.php" class="topbar-stat stat-liga" title="Sua Divisão / Liga Atual">
            <i class="fa-solid fa-shield-halved"></i>
            <span><?php echo htmlspecialchars($liga_nome_top); ?></span>
        </a>

        <!-- FOGO (OFENSIVA / DIAS) -->
        <div class="topbar-stat stat-fogo" title="Sequência Diária">
            <i class="fa-solid fa-fire"></i>
            <span><?php echo (int) $dias_fogo_top; ?></span>
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

    <!-- USUÁRIO -->
    <div class="user-info">
        <span><?php echo htmlspecialchars($nome_top); ?></span>
        <img src="<?php echo htmlspecialchars($avatar_top); ?>" alt="Avatar" class="avatar">
    </div>
</header>