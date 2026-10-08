<?php
$c = file_get_contents('back/api_chat.php');

$search = <<<'PHP'
    $has_duel = false;
    while ($row = $res->fetch_assoc()) {
PHP;

$replace = <<<'PHP'
    $has_duel = false;
    $duel_info = null;
    while ($row = $res->fetch_assoc()) {
PHP;

$c = str_replace($search, $replace, $c);

file_put_contents('back/api_chat.php', $c);
echo "fixed warning properly";
