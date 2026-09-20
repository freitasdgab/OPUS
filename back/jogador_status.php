<?php
/**
 * Vidas (3 corações) e sequência diária de fogo.
 *
 * A lógica de regeneração de vidas e de sequência (streak) agora mora no
 * banco (sp_sincronizar_jogador, sp_perder_vida, sp_atualizar_fogo em
 * back/sql/opus_procedures.sql). Este arquivo só chama as procedures
 * (via opus_call, de back/conexao.php) e formata o texto de exibição
 * ("Próxima vida em Xh Ymin").
 */

function opus_sincronizar_jogador(mysqli $conn, int $user_id): array {
    $u = opus_call($conn, "CALL sp_sincronizar_jogador(?)", "i", [$user_id]);

    if (!$u) {
        return [
            'xp' => 0,
            'trofeus' => 0,
            'dias_fogo' => 0,
            'vidas' => 3,
            'vidas_proxima_em' => null,
            'proxima_vida_texto' => '',
            'nome' => 'Usuário',
            'foto_perfil' => '',
            'nivel_acesso' => 'comum',
            'is_admin' => false,
        ];
    }

    $nivel_acesso = $u['nivel_acesso'] ?? 'comum';
    $is_admin = ($nivel_acesso === 'admin');

    // Sincroniza variável de sessão caso exista sessão ativa
    if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $user_id) {
        $_SESSION['user_nivel_acesso'] = $nivel_acesso;
        $_SESSION['is_admin'] = $is_admin;
    }

    $vidas = (int) ($u['vidas'] ?? 3);
    $proxima = $u['vidas_proxima_em'] ?? null;

    $proxima_texto = '';
    if ($vidas < 3 && !empty($proxima)) {
        $ts = strtotime($proxima);
        $resto = max(0, $ts - time());
        $horas = (int) floor($resto / 3600);
        $mins = (int) floor(($resto % 3600) / 60);
        if ($horas > 0) {
            $proxima_texto = "Próxima vida em {$horas}h {$mins}min";
        } else {
            $proxima_texto = "Próxima vida em {$mins}min";
        }
    }

    return [
        'xp' => (int) ($u['xp'] ?? 0),
        'trofeus' => (int) ($u['trofeus'] ?? 0),
        'dias_fogo' => (int) ($u['dias_fogo'] ?? 0),
        'vidas' => $vidas,
        'vidas_proxima_em' => $proxima,
        'proxima_vida_texto' => $proxima_texto,
        'nome' => $u['nome'] ?? 'Usuário',
        'foto_perfil' => $u['foto_perfil'] ?? '',
        'nivel_acesso' => $nivel_acesso,
        'is_admin' => $is_admin,
    ];
}

function opus_perder_vida(mysqli $conn, int $user_id): int {
    if ($user_id <= 0) {
        return 0;
    }

    $row = opus_call($conn, "CALL sp_perder_vida(?)", "i", [$user_id]);
    return (int) ($row['vidas'] ?? 0);
}

function opus_atualizar_fogo(mysqli $conn, int $user_id): int {
    $row = opus_call($conn, "CALL sp_atualizar_fogo(?)", "i", [$user_id]);
    return (int) ($row['dias_fogo'] ?? 0);
}
