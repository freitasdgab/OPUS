<?php
$c = file_get_contents('back/api_chat.php');
$c = str_replace("\$has_duel = false;\n    while", "\$has_duel = false;\n    \$duel_info = null;\n    while", $c);
file_put_contents('back/api_chat.php', $c);
echo "Warning fixed";
