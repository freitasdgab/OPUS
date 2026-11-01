<?php
/**
 * Lógica do Sistema de Missões Diárias do OPUS.
 * Gerencia o progresso diário e verificação de metas alcançadas.
 */

/**
 * Registra o progresso de uma lição concluída nas missões do dia.
 */
function missoes_registrar_progresso(mysqli $conn, int $usuario_id, int $xp_ganho, int $acertos, int $total_questoes = 0): void {
    if ($usuario_id <= 0) {
        return;
    }

    $eh_perfeita = ($total_questoes > 0) ? ($acertos >= $total_questoes ? 1 : 0) : ($acertos >= 3 ? 1 : 0);

    try {
        $stmt = $conn->prepare("CALL sp_registrar_missao_progresso(?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iii", $usuario_id, $xp_ganho, $eh_perfeita);
            $stmt->execute();
            $stmt->close();
            while ($conn->more_results() && $conn->next_result()) {;}
            return;
        }
    } catch (Throwable $e) {
        // Fallback direto via INSERT ... ON DUPLICATE KEY UPDATE se a procedure falhar
    }

    $hoje = date('Y-m-d');
    $concluida = ($acertos > 0) ? 1 : 0;
    $stmt = $conn->prepare("
        INSERT INTO missoes_diarias_usuario (usuario_id, data_ref, xp_ganho, licoes_concluidas, licoes_perfeitas)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            xp_ganho = xp_ganho + VALUES(xp_ganho),
            licoes_concluidas = licoes_concluidas + VALUES(licoes_concluidas),
            licoes_perfeitas = licoes_perfeitas + VALUES(licoes_perfeitas)
    ");
    if ($stmt) {
        $stmt->bind_param("isiii", $usuario_id, $hoje, $xp_ganho, $concluida, $eh_perfeita);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Retorna a lista de missões diárias com os valores e progresso real do usuário hoje.
 */
function missoes_obter_hoje(mysqli $conn, int $usuario_id): array {
    $hoje = date('Y-m-d');

    $stmt = $conn->prepare("SELECT xp_ganho, licoes_concluidas, licoes_perfeitas FROM missoes_diarias_usuario WHERE usuario_id = ? AND data_ref = ?");
    if ($stmt) {
        $stmt->bind_param("is", $usuario_id, $hoje);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $row = null;
    }

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

/**
 * Verifica se todas as metas diárias foram batidas hoje.
 */
function missoes_todas_concluidas(array $missoes): bool {
    if (empty($missoes)) return false;
    foreach ($missoes as $m) {
        if (empty($m['completo'])) {
            return false;
        }
    }
    return true;
}
