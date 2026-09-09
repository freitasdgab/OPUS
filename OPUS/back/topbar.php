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
    $foto_banco = $dados_top['foto_perfil'] ?? '';

    if (strpos($foto_banco, 'data:image') === 0) {
        $avatar_top = $foto_banco;
    } else {
        $avatar_top = '../assets/img/opi pulando feliz.png';
    }
}

$titulo_vidas = $proxima_vida_texto !== '' ? $proxima_vida_texto : ($vidas_top . ' de 3 vidas');
?>

<style>
/* BARRA SUPERIOR - ESTILO FLAT DUOLINGO */
.top-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 30px;
    background: transparent;
    width: 100%;
    box-sizing: border-box;
}

.topbar-stats {
    display: flex;
    align-items: center;
    gap: 28px;
}

.topbar-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 1.15rem;
    text-decoration: none;
    user-select: none;
    transition: transform 0.1s ease;
}

.topbar-stat:hover {
    transform: scale(1.05);
}

.topbar-stat i {
    font-size: 1.4rem;
}

/* CORES SÓLIDAS DA INTERFACE */
.stat-liga { color: #1cb0f6; } /* Azul */
.stat-fogo { color: #ff9600; } /* Laranja */
.stat-xp   { color: #ffc800; } /* Amarelo */
.stat-vida { color: #ff4b4b; } /* Vermelho */

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 800;
    font-size: 1rem;
    color: #fff;
}

.user-info .avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
    .top-bar {
        flex-direction: column-reverse;
        gap: 15px;
    }
    .topbar-stats {
        gap: 18px;
    }
}
</style>

<header class="top-bar">
    <div class="topbar-stats">
        <!-- LIGA / TROFÉUS -->
        <a href="ranking.php" class="topbar-stat stat-liga" title="Sua Liga Atual">
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
        <img src="<?php echo $avatar_top; ?>" alt="Avatar" class="avatar">
    </div>
</header>