<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/light-mode.css?v=<?= time() ?>">
<?php
// Barra lateral única, incluída em todas as páginas internas.
// Detecta a página atual automaticamente para marcar o link "active".
$pagina_atual = basename($_SERVER['SCRIPT_NAME']);
$is_admin = !empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin');

$itens_menu = [
    ['href' => 'dashboard.php',  'icon' => 'fa-house',         'label' => 'Aprender'],
    ['href' => 'desafios.php',   'icon' => 'fa-laptop-code',   'label' => 'Desafios'],
    ['href' => 'ranking.php',    'icon' => 'fa-ranking-star',  'label' => 'Ranking'],
    ['href' => 'ligas.php',      'icon' => 'fa-shield-halved', 'label' => 'Ligas'],
    ['href' => 'dicionario.php', 'icon' => 'fa-book-bookmark', 'label' => 'Dicionário'],
    ['href' => 'perfil.php',     'icon' => 'fa-user',          'label' => 'Perfil'],
];
?>
<aside class="sidebar">
    <div class="logo">OPUS</div>
    <nav class="menu">
        <?php foreach ($itens_menu as $item): 
            $is_active = ($pagina_atual === $item['href']) || ($item['href'] === 'desafios.php' && $pagina_atual === 'desafio_ide.php');
        ?>
            <a href="<?= $item['href'] ?>" class="nav-link<?= $is_active ? ' active' : '' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>

        <a href="#" class="nav-link nav-link-theme" id="themeToggleBtn" style="margin-top: 15px;">
            <i class="fa-solid fa-sun" id="themeIcon"></i> <span id="themeText">Modo Claro</span>
        </a>

        <a href="../../back/auth/logout.php" class="nav-link nav-link-logout" style="margin-top: 15px; color: #ff4757;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair
        </a>
    </nav>
</aside>

<script src="../assets/js/theme.js"></script>

<?php
// Inclui o assistente flutuante Opi IA em todas as páginas com a sidebar
include __DIR__ . '/chatbot.php';
?>
