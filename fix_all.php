<?php
$c = file_get_contents('back/api_chat.php');
$c = str_replace(
    '$has_duel = false;' . "\n" . '    while ($row',
    '$has_duel = false;' . "\n" . '    $duel_info = null;' . "\n" . '    while ($row',
    $c
);
file_put_contents('back/api_chat.php', $c);
echo "fixed all";
