<?php
/**
 * Vidas (3 corações) e sequência diária de fogo.
 */

function opus_ensure_player_columns(mysqli $conn): void {
    static $done = false;
    if ($done) {
        return;
    }

    $colunas = [
        'vidas'             => 'TINYINT NOT NULL DEFAULT 3',
        'vidas_proxima_em'  => 'DATETIME NULL DEFAULT NULL',
        'ultima_atividade'  => 'DATE NULL DEFAULT NULL',
    ];

    foreach ($colunas as $nome => $definicao) {
        $res = $conn->query("SHOW COLUMNS FROM usuarios LIKE '" . $conn->real_escape_string($nome) . "'");
        if ($res && $res->num_rows === 0) {
            $conn->query("ALTER TABLE usuarios ADD COLUMN `$nome` $definicao");
        }
    }

    $done = true;
}

function opus_sincronizar_jogador(mysqli $conn, int $user_id): array {
    opus_ensure_player_columns($conn);

    $stmt = $conn->prepare("SELECT xp, trofeus, dias_fogo, vidas, vidas_proxima_em, ultima_atividade, nome, foto_perfil FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

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
        ];
    }

    $vidas = (int) ($u['vidas'] ?? 3);
    if ($vidas > 3) {
        $vidas = 3;
    }
    if ($vidas < 0) {
        $vidas = 0;
    }

    $proxima = $u['vidas_proxima_em'] ?? null;
    $agora = new DateTime();

    if ($vidas < 3 && !empty($proxima)) {
        $quando = new DateTime($proxima);
        $mudou = false;

        while ($vidas < 3 && $quando <= $agora) {
            $vidas++;
            $mudou = true;
            if ($vidas >= 3) {
                $quando = null;
                break;
            }
            $quando->modify('+24 hours');
        }

        if ($mudou) {
            if ($quando === null) {
                $up = $conn->prepare("UPDATE usuarios SET vidas = ?, vidas_proxima_em = NULL WHERE id = ?");
                $up->bind_param("ii", $vidas, $user_id);
            } else {
                $proxima_str = $quando->format('Y-m-d H:i:s');
                $up = $conn->prepare("UPDATE usuarios SET vidas = ?, vidas_proxima_em = ? WHERE id = ?");
                $up->bind_param("isi", $vidas, $proxima_str, $user_id);
            }
            $up->execute();
            $proxima = $quando ? $quando->format('Y-m-d H:i:s') : null;
        }
    }

    if ($vidas >= 3) {
        $proxima = null;
    }

    $hoje = date('Y-m-d');
    $ontem = date('Y-m-d', strtotime('-1 day'));
    $ultima = $u['ultima_atividade'] ?? null;
    $fogo = (int) ($u['dias_fogo'] ?? 0);

    if (!empty($ultima) && $ultima !== $hoje && $ultima !== $ontem && $fogo > 0) {
        $fogo = 0;
        $up_fogo = $conn->prepare("UPDATE usuarios SET dias_fogo = 0 WHERE id = ?");
        $up_fogo->bind_param("i", $user_id);
        $up_fogo->execute();
    }

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
        'dias_fogo' => $fogo,
        'vidas' => $vidas,
        'vidas_proxima_em' => $proxima,
        'proxima_vida_texto' => $proxima_texto,
        'nome' => $u['nome'] ?? 'Usuário',
        'foto_perfil' => $u['foto_perfil'] ?? '',
    ];
}

function opus_perder_vida(mysqli $conn, int $user_id): int {
    $status = opus_sincronizar_jogador($conn, $user_id);
    $vidas = (int) $status['vidas'];

    if ($vidas <= 0) {
        return 0;
    }

    $vidas--;
    $proxima = $status['vidas_proxima_em'];

    if ($vidas < 3 && empty($proxima)) {
        $proxima = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
        $up = $conn->prepare("UPDATE usuarios SET vidas = ?, vidas_proxima_em = ? WHERE id = ?");
        $up->bind_param("isi", $vidas, $proxima, $user_id);
    } else {
        $up = $conn->prepare("UPDATE usuarios SET vidas = ? WHERE id = ?");
        $up->bind_param("ii", $vidas, $user_id);
    }
    $up->execute();

    return $vidas;
}

function opus_atualizar_fogo(mysqli $conn, int $user_id): int {
    opus_ensure_player_columns($conn);

    $stmt = $conn->prepare("SELECT dias_fogo, ultima_atividade FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    $hoje = date('Y-m-d');
    $ontem = date('Y-m-d', strtotime('-1 day'));
    $ultima = $u['ultima_atividade'] ?? null;
    $seq = (int) ($u['dias_fogo'] ?? 0);

    if ($ultima === $hoje) {
        return $seq;
    }

    if ($ultima === $ontem) {
        $seq += 1;
    } else {
        $seq = 1;
    }

    $up = $conn->prepare("UPDATE usuarios SET dias_fogo = ?, ultima_atividade = ? WHERE id = ?");
    $up->bind_param("isi", $seq, $hoje, $user_id);
    $up->execute();

    if ($seq >= 3) {
        $ins = $conn->prepare("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (?, 'fogo_3')");
        $ins->bind_param("i", $user_id);
        $ins->execute();
        $conn->query("UPDATE usuarios SET trofeus = (SELECT COUNT(*) FROM user_trofeus WHERE user_id = $user_id) WHERE id = $user_id");
    }

    return $seq;
}
