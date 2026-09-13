<?php
require_once '../../back/conexao.php';

echo "=== LICOES ===\n";
$r = $conn->query("SELECT id, unidade_numero, licao_numero, titulo, LENGTH(texto_explicativo) as len_texto, LENGTH(codigo_exemplo) as len_codigo FROM licoes ORDER BY unidade_numero, licao_numero");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ' | cap:' . $row['unidade_numero'] . ' | lic:' . $row['licao_numero'] . ' | ' . $row['titulo'] . ' | texto:' . $row['len_texto'] . ' | cod:' . $row['len_codigo'] . "\n";
}

echo "\n=== PERGUNTAS (count per licao) ===\n";
$r2 = $conn->query("SELECT licao_id, COUNT(*) as cnt FROM perguntas GROUP BY licao_id ORDER BY licao_id");
while($row = $r2->fetch_assoc()) {
    echo 'licao_id:' . $row['licao_id'] . ' | perguntas:' . $row['cnt'] . "\n";
}

echo "\n=== TEXTO EXPLICATIVO licao cap1 lic1 (primeiros 200 chars) ===\n";
$r3 = $conn->query("SELECT texto_explicativo FROM licoes WHERE unidade_numero = 1 AND licao_numero = 1");
$row = $r3->fetch_assoc();
echo substr($row['texto_explicativo'], 0, 200) . "\n";

echo "\n=== TEXTO EXPLICATIVO licao cap1 lic2 (primeiros 200 chars) ===\n";
$r4 = $conn->query("SELECT texto_explicativo FROM licoes WHERE unidade_numero = 1 AND licao_numero = 2");
$row = $r4->fetch_assoc();
if ($row) {
    echo substr($row['texto_explicativo'], 0, 200) . "\n";
} else {
    echo "NAO ENCONTRADO\n";
}
?>
