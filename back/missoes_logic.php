<?php
/**
 * Lógica do Sistema de Missões Diárias do OPUS.
 * Cria a tabela automaticamente se não existir e gerencia o progresso diário.
 */

function missoes_garantir_tabela(mysqli $conn): void {
    static $tabela_criada = false;
    if ($tabela_criada) {
        return;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `missoes_diarias_usuario` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `usuario_id` INT(11) NOT NULL,
        `data_ref` DATE NOT NULL,
        `xp_ganho` INT(11) NOT NULL DEFAULT 0,
        `licoes_concluidas` INT(11) NOT NULL DEFAULT 0,
        `licoes_perfeitas` INT(11) NOT NULL DEFAULT 0,
        `criado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `atualizado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_user_data` (`usuario_id`, `data_ref`),
        KEY `idx_usuario` (`usuario_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $conn->query($sql);
    $tabela_criada = true;
}

/**
 * Registra o progresso de uma lição concluída nas missões do dia.
 */
function missoes_registrar_progresso(mysqli $conn, int $usuario_id, int $xp_ganho, int $acertos): void {
    if ($usuario_id <= 0) {
        return;
    }

    missoes_garantir_tabela($conn);
    $hoje = date('Y-m-d');
    $is_perfeita = ($acertos >= 3) ? 1 : 0;
    $licao_feita = ($acertos > 0) ? 1 : 0;

    $stmt = $conn->prepare("
        INSERT INTO missoes_diarias_usuario (usuario_id, data_ref, xp_ganho, licoes_concluidas, licoes_perfeitas)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            xp_ganho = xp_ganho + VALUES(xp_ganho),
            licoes_concluidas = licoes_concluidas + VALUES(licoes_concluidas),
            licoes_perfeitas = licoes_perfeitas + VALUES(licoes_perfeitas)
    ");
    $stmt->bind_param("isiii", $usuario_id, $hoje, $xp_ganho, $licao_feita, $is_perfeita);
    $stmt->execute();
}

/**
 * Retorna a lista de missões diárias com os valores e progresso real do usuário hoje.
 */
function missoes_obter_hoje(mysqli $conn, int $usuario_id): array {
    missoes_garantir_tabela($conn);
    $hoje = date('Y-m-d');

    $stmt = $conn->prepare("SELECT xp_ganho, licoes_concluidas, licoes_perfeitas FROM missoes_diarias_usuario WHERE usuario_id = ? AND data_ref = ?");
    $stmt->bind_param("is", $usuario_id, $hoje);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $xp_hoje = (int) ($row['xp_ganho'] ?? 0);
    $licoes_hoje = (int) ($row['licoes_concluidas'] ?? 0);
    $perfeitas_hoje = (int) ($row['licoes_perfeitas'] ?? 0);

    return [
        [
            'id'          => 'xp_50',
            'titulo'      => 'Ganhe 50 de XP',
            'icone'       => 'fa-bolt',
            'cor'         => '#ffc800',
            'atual'       => $xp_hoje,
            'meta'        => 50,
            'unidade'     => 'XP',
            'completo'    => ($xp_hoje >= 50),
            'porcentagem' => min(100, (int) round(($xp_hoje / 50) * 100))
        ],
        [
            'id'          => 'licoes_2',
            'titulo'      => 'Conclua 2 lições',
            'icone'       => 'fa-book-open',
            'cor'         => '#1cb0f6',
            'atual'       => $licoes_hoje,
            'meta'        => 2,
            'unidade'     => '',
            'completo'    => ($licoes_hoje >= 2),
            'porcentagem' => min(100, (int) round(($licoes_hoje / 2) * 100))
        ],
        [
            'id'          => 'perfeita_1',
            'titulo'      => 'Acerte 1 lição com 100%',
            'icone'       => 'fa-bullseye',
            'cor'         => '#ff527b',
            'atual'       => $perfeitas_hoje,
            'meta'        => 1,
            'unidade'     => '',
            'completo'    => ($perfeitas_hoje >= 1),
            'porcentagem' => min(100, (int) round(($perfeitas_hoje / 1) * 100))
        ]
    ];
}
