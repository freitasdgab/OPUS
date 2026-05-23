<?php
$host = "localhost";
$usuario_db = "root";
$senha_db = "";
$nome_db = "opus";

// Ativa o relatório de erros do MySQLi para ajudar no desenvolvimento
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $usuario_db, $senha_db, $nome_db);
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Falha na conexão com o banco de dados: " . $e->getMessage());
}
?>