<?php
// Barra lateral única, incluída em todas as páginas internas.
// Detecta a página atual automaticamente para marcar o link "active".
$pagina_atual = basename($_SERVER['SCRIPT_NAME']);

$itens_menu = [
    ['href' => 'dashboard.php',  'icon' => 'fa-house',         'label' => 'Aprender'],
    ['href' => 'conquistas.php', 'icon' => 'fa-award',         'label' => 'Missões'],
    ['href' => 'ranking.php',    'icon' => 'fa-ranking-star',  'label' => 'Ranking'],
    ['href' => 'ligas.php',      'icon' => 'fa-shield-halved', 'label' => 'Ligas'],
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
    </nav>
</aside>
