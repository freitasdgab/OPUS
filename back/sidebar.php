<?php
// Barra lateral única, incluída em todas as páginas internas.
// Detecta a página atual automaticamente para marcar o link "active".
$pagina_atual = basename($_SERVER['SCRIPT_NAME']);
$is_admin = !empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin');

$itens_menu = [
    ['href' => 'dashboard.php',  'icon' => 'fa-house',         'label' => 'Aprender'],
    ['href' => 'ranking.php',    'icon' => 'fa-ranking-star',  'label' => 'Ranking'],
    ['href' => 'ligas.php',      'icon' => 'fa-shield-halved', 'label' => 'Ligas'],
    ['href' => 'dicionario.php', 'icon' => 'fa-book-bookmark', 'label' => 'Dicionário'],
    ['href' => 'perfil.php',     'icon' => 'fa-user',          'label' => 'Perfil'],
];
?>
<aside class="sidebar">
    <div class="logo">OPUS</div>
    <nav class="menu">
        <?php foreach ($itens_menu as $item): ?>
            <a href="<?= $item['href'] ?>" class="nav-link<?= $pagina_atual === $item['href'] ? ' active' : '' ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
            </a>
        <?php endforeach; ?>

        <a href="../../back/logout.php" class="nav-link nav-link-logout" style="margin-top: 25px; color: #ff4757;" onclick="return confirm('Deseja realmente sair da conta?')">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Sair
        </a>
    </nav>
</aside>

<?php
// Inclui o assistente flutuante Opi IA em todas as páginas com a sidebar
include __DIR__ . '/chatbot.php';
?>