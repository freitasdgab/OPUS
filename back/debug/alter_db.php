<?php
require_once __DIR__ . '/../conexao.php';
try {
    $conn->query("CREATE TABLE IF NOT EXISTS `pedidos_conexao` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `remetente_id` INT NOT NULL,
        `destinatario_id` INT NOT NULL,
        `status` ENUM('pendente', 'aceito', 'recusado') DEFAULT 'pendente',
        `data_criacao` DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `uq_rem_dest` (`remetente_id`, `destinatario_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $conn->query("CREATE TABLE IF NOT EXISTS `chat_mensagens` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `remetente_id` INT NOT NULL,
        `destinatario_id` INT NOT NULL,
        `mensagem` TEXT NOT NULL,
        `status_leitura` ENUM('enviado', 'entregue', 'lido') DEFAULT 'enviado',
        `data_envio` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo 'DB Altered with Social Features';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
