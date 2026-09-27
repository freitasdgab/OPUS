<?php
$c = file_get_contents('front/assets/css/licao.css');
$c = str_replace("\0", '', $c); // Remove null bytes
$lines = explode("\n", $c);
$clean = [];
foreach($lines as $l) {
    if (trim($l) !== '' && !preg_match('/[^\x20-\x7E]/', $l)) {
        $clean[] = $l;
    }
}
$c = implode("\n", $clean);
file_put_contents('front/assets/css/licao.css', $c);
echo "fixed";
