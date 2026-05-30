<?php
// Garante que a conexão sempre exista, independentemente de quem chamou a topbar
require_once __DIR__ . '/conexao.php';

if (isset($_SESSION['user_id'])) {
    $user_id_topbar = $_SESSION['user_id'];
    
    // Busca os dados do usuário, INCLUINDO a foto_perfil
    $stmt_top = $conn->prepare("SELECT nome, xp, trofeus, dias_fogo, dificuldade, foto_perfil FROM usuarios WHERE id = ?");
    $stmt_top->bind_param("i", $user_id_topbar);
    $stmt_top->execute();
    $dados_top = $stmt_top->get_result()->fetch_assoc();

    $nome_top = $dados_top['nome'] ?? 'Usuário';
    $xp_top = $dados_top['xp'] ?? 0;
    $trofeus_top = $dados_top['trofeus'] ?? 0;
    $dias_fogo_top = $dados_top['dias_fogo'] ?? 0;
    $dificuldade_top = $dados_top['dificuldade'] ?? 'Iniciante';
    $foto_banco = $dados_top['foto_perfil'] ?? '';

    // Lógica inteligente para resolver a imagem na NavBar
    if (strpos($foto_banco, 'data:image') === 0) {
        // Se for Base64 (foto enviada pelo usuário), usa o texto direto
        $avatar_top = $foto_banco;
    } else {
        // Se estiver vazio, usa o mascote padrão
        $avatar_top = '../assets/img/opi pulando feliz.png'; 
    }
}
?>
<header class="top-bar">
    <div class="stats">
        <div class="stat-box difficulty"><i class="fa-solid fa-fire"></i> <?php echo htmlspecialchars($dificuldade_top); ?> <small>Dificuldade</small></div>
        <div class="stat-box trophies"><i class="fa-solid fa-trophy"></i> <?php echo $trofeus_top; ?> <small>Troféus</small></div>
        <div class="stat-box xp"><i class="fa-solid fa-star"></i> <?php echo number_format($xp_top, 0, ',', '.'); ?> <small>Total XP</small></div>
        <div class="stat-box fire"><i class="fa-solid fa-fire-flame-curved"></i> <?php echo $dias_fogo_top; ?> <small>Dias</small></div>
    </div>
    <div class="user-info">
        <span><?php echo htmlspecialchars($nome_top); ?></span>
        
        <img src="<?php echo $avatar_top; ?>" alt="Avatar" class="avatar" style="object-fit: cover;">
    </div>
</header>