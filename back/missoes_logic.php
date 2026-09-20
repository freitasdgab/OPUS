<?php
/**
 * Lógica do Sistema de Missões Diárias do OPUS.
 * Cria a tabela automaticamente se não existir e gerencia o progresso diário.
 */

/**
 * Registra o progresso de uma lição concluída nas missões do dia.
 * Lógica em sp_registrar_missao_progresso (back/sql/opus_procedures.sql).
 * A tabela missoes_diarias_usuario já faz parte do schema (opus.sql),
 * não precisa mais ser criada em tempo de execução.
 */
function missoes_registrar_progresso(mysqli $conn, int $usuario_id, int $xp_ganho, int $acertos): void {
    if ($usuario_id <= 0) {
        return;
    }

    $stmt = $conn->prepare("CALL sp_registrar_missao_progresso(?, ?, ?)");
    $stmt->bind_param("iii", $usuario_id, $xp_ganho, $acertos);
    $stmt->execute();
    $stmt->close();
    $conn->next_result();
}

/**
 * Retorna a lista de missões diárias com os valores e progresso real do usuário hoje.
 */
function missoes_obter_hoje(mysqli $conn, int $usuario_id): array {
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
