<?php
require 'back/conexao.php';

echo "=== LICOES ===\n";
$r = $conn->query("SELECT id, unidade_numero, licao_numero, titulo FROM licoes ORDER BY unidade_numero, licao_numero");
while($row = $r->fetch_assoc()) {
    echo $row['id'] . ' | cap:' . $row['unidade_numero'] . ' | lic:' . $row['licao_numero'] . ' | ' . $row['titulo'] . "\n";
}

echo "\n=== PERGUNTAS (por licao_id) ===\n";
$r2 = $conn->query("SELECT licao_id, COUNT(*) as cnt FROM perguntas GROUP BY licao_id ORDER BY licao_id");
while($row = $r2->fetch_assoc()) {
    echo 'licao_id:' . $row['licao_id'] . ' | perguntas:' . $row['cnt'] . "\n";
}

echo "\n=== PROGRESSO ===\n";
$r3 = $conn->query("SELECT * FROM progresso_usuario ORDER BY usuario_id, unidade_numero");
while($row = $r3->fetch_assoc()) {
    echo 'user:' . $row['usuario_id'] . ' | cap:' . $row['unidade_numero'] . ' | status:' . $row['status'] . ' | feitas:' . $row['licoes_concluidas'] . "\n";
}
?>
