<?php
$host       = "localhost";
$usuario_db = "root";
$senha_db   = "";          // Padrão XAMPP — ajuste se necessário
$nome_db    = "opus";

// Ativa o relatório de erros do MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $usuario_db, $senha_db, $nome_db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // Mensagem amigável + dica de diagnóstico
    $codigo  = $e->getCode();
    $detalhe = $e->getMessage();

    $dica = "";
    if ($codigo === 1049) {
        $dica = " — O banco de dados '<strong>$nome_db</strong>' não existe. Importe o arquivo <code>banco_de_dados/opus.sql</code> no phpMyAdmin.";
    } elseif ($codigo === 1045) {
        $dica = " — Usuário ou senha do MySQL incorretos. Verifique as variáveis em <code>back/conexao.php</code>.";
    } elseif ($codigo === 2002) {
        $dica = " — Não foi possível conectar ao servidor MySQL. Verifique se o <strong>Apache</strong> e o <strong>MySQL</strong> estão rodando no XAMPP.";
    }

    die("<style>body{font-family:sans-serif;padding:30px;background:#1e1e2e;color:#cdd6f4;}
         strong{color:#f38ba8;} code{background:#313244;padding:2px 6px;border-radius:4px;}</style>
         <h2 style='color:#f38ba8'>❌ Erro de Conexão com o Banco de Dados</h2>
         <p><strong>Código $codigo:</strong> $detalhe$dica</p>
         <p style='margin-top:20px;opacity:.7;font-size:.9em'>Verifique se o XAMPP está ativo (Apache + MySQL) e se o banco <code>opus</code> foi importado.</p>");
}

/**
 * Executa "CALL nome_procedure(...)" e devolve o ÚLTIMO result set (não o
 * primeiro). Necessário porque várias stored procedures do OPUS chamam
 * outras procedures por dentro que também terminam com um SELECT — sem
 * isso, o PHP pegaria a resposta de uma chamada interna em vez da final.
 * Uso: opus_call($conn, "CALL sp_algo(?, ?)", "ii", [$a, $b]);
 */
function opus_call(mysqli $conn, string $sql, string $types = '', array $params = []): ?array {
    $stmt = $conn->prepare($sql);
    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();

    $ultima_linha = null;
    do {
        $result = $stmt->get_result();
        if ($result) {
            $linha = $result->fetch_assoc();
            if ($linha !== null) {
                $ultima_linha = $linha;
            }
        }
    } while ($stmt->more_results() && $stmt->next_result());

    $stmt->close();
    return $ultima_linha;
}
?>
