<?php
require_once 'conexao.php';
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
?>
