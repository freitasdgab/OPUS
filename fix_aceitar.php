<?php
$c = file_get_contents('front/assets/js/chat.js');
$search = <<<'JS'
        if(d.success) {
            alert('Batalha iniciada! Verifique seu Perfil.');
            window.location.href = 'perfil.php';
        } else {
JS;
$replace = <<<'JS'
        if(d.success) {
            alert('Batalha iniciada! O XP já começou a contar!');
            atualizarChatWindow();
        } else {
JS;
$c = str_replace($search, $replace, $c);
file_put_contents('front/assets/js/chat.js', $c);
echo "fixed aceitarBatalha";
