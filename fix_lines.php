<?php
$lines = file('back/api_chat.php');
array_splice($lines, 109, 0, "    \$duel_info = null;\n");
file_put_contents('back/api_chat.php', implode("", $lines));
