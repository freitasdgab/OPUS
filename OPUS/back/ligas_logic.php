<?php
/**
 * Núcleo do sistema de Ligas (divisões semanais).
 * Padrão de cores segue a mesma lógica de back/mascotes_capitulos.php
 */

const LIGAS_TAMANHO_GRUPO = 30;

$LIGAS_CONFIG = [
    'bronze'   => ['nome' => 'Bronze',   'cor' => '#a5682a', 'corClara' => '#cd7f32', 'proxima' => 'prata',    'anterior' => null,       'sobe' => true,  'desce' => false],
    'prata'    => ['nome' => 'Prata',    'cor' => '#8e8e99', 'corClara' => '#c0c0c0', 'proxima' => 'ouro',     'anterior' => 'bronze',   'sobe' => true,  'desce' => true],
    'ouro'     => ['nome' => 'Ouro',     'cor' => '#d4a017', 'corClara' => '#ffd700', 'proxima' => 'diamante', 'anterior' => 'prata',    'sobe' => true,  'desce' => true],
    'diamante' => ['nome' => 'Diamante', 'cor' => '#1ec8e0', 'corClara' => '#5be7ff', 'proxima' => 'mestre',   'anterior' => 'ouro',     'sobe' => true,  'desce' => true],
    'mestre'   => ['nome' => 'Mestre',   'cor' => '#8b2fd9', 'corClara' => '#c47bff', 'proxima' => null,       'anterior' => 'diamante', 'sobe' => false, 'desce' => true],
];

function liga_config(string $divisao): array {
    global $LIGAS_CONFIG;
    return $LIGAS_CONFIG[$divisao] ?? $LIGAS_CONFIG['bronze'];
}

function liga_todas_divisoes(): array {
    global $LIGAS_CONFIG;
    return array_keys($LIGAS_CONFIG);
}

/** Retorna a data (Y-m-d) da segunda-feira da semana atual. */
function liga_semana_atual(): string {
    $hoje = new DateTime('today');
    $diaSemana = (int)$hoje->format('N'); // 1 = segunda
    $hoje->modify('-' . ($diaSemana - 1) . ' days');
    return $hoje->format('Y-m-d');
}

/** Data/hora da próxima virada (segunda 00:00). */
function liga_proxima_virada(): DateTime {
    $prox = new DateTime(liga_semana_atual());
    $prox->modify('+7 days');
    return $prox;
}

/**
 * Garante que o usuário tenha um registro em ligas_usuario e esteja
 * alocado em um grupo válido para a semana atual. Cria grupo novo
 * quando o último grupo da divisão já está cheio.
 */
function liga_garantir_usuario(mysqli $conn, int $usuario_id): array {
    $semana = liga_semana_atual();

    $stmt = $conn->prepare("SELECT * FROM ligas_usuario WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $liga = $stmt->get_result()->fetch_assoc();

    if (!$liga) {
        $stmt = $conn->prepare("INSERT INTO ligas_usuario (usuario_id, divisao, xp_semana) VALUES (?, 'bronze', 0)");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $liga = ['usuario_id' => $usuario_id, 'divisao' => 'bronze', 'grupo_id' => null, 'xp_semana' => 0];
    }

    // Verifica se o grupo atual já é da semana corrente
    $grupoValido = false;
    if (!empty($liga['grupo_id'])) {
        $stmt = $conn->prepare("SELECT semana_ref FROM ligas_grupos WHERE id = ?");
        $stmt->bind_param("i", $liga['grupo_id']);
        $stmt->execute();
        $g = $stmt->get_result()->fetch_assoc();
        if ($g && $g['semana_ref'] === $semana) {
            $grupoValido = true;
        }
    }

    if (!$grupoValido) {
        $grupoId = liga_obter_ou_criar_grupo($conn, $liga['divisao'], $semana);
        $stmt = $conn->prepare("UPDATE ligas_usuario SET grupo_id = ? WHERE usuario_id = ?");
        $stmt->bind_param("ii", $grupoId, $usuario_id);
        $stmt->execute();
        $liga['grupo_id'] = $grupoId;
    }

    return $liga;
}

/** Acha um grupo com vaga na divisão/semana, ou cria um novo. */
function liga_obter_ou_criar_grupo(mysqli $conn, string $divisao, string $semana): int {
    $stmt = $conn->prepare("
        SELECT g.id, COUNT(u.id) AS total
        FROM ligas_grupos g
        LEFT JOIN ligas_usuario u ON u.grupo_id = g.id
        WHERE g.divisao = ? AND g.semana_ref = ?
        GROUP BY g.id
        HAVING total < ?
        ORDER BY g.id ASC
        LIMIT 1
    ");
    $capacidade = LIGAS_TAMANHO_GRUPO;
    $stmt->bind_param("ssi", $divisao, $semana, $capacidade);
    $stmt->execute();
    $grupo = $stmt->get_result()->fetch_assoc();

    if ($grupo) {
        return (int)$grupo['id'];
    }

    $stmt = $conn->prepare("INSERT INTO ligas_grupos (divisao, semana_ref, capacidade) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $divisao, $semana, $capacidade);
    $stmt->execute();
    return (int)$conn->insert_id;
}

/**
 * Soma XP ganho pelo usuário ao total da semana da liga.
 * Chame isso sempre que o usuário ganhar XP em progresso_usuario/usuarios.
 */
function liga_registrar_xp(mysqli $conn, int $usuario_id, int $xp): void {
    liga_garantir_usuario($conn, $usuario_id);
    $stmt = $conn->prepare("UPDATE ligas_usuario SET xp_semana = xp_semana + ? WHERE usuario_id = ?");
    $stmt->bind_param("ii", $xp, $usuario_id);
    $stmt->execute();
}

/**
 * Calcula quantos sobem/descem em um grupo de tamanho $n,
 * proporcional ao padrão 7/16/7 de um grupo de 30.
 */
function liga_calcular_zonas(int $n): array {
    $sobe = (int)round($n * 7 / 30);
    $desce = (int)round($n * 7 / 30);
    $sobe = max(1, min($sobe, $n));
    $desce = max(1, min($desce, $n));
    if ($sobe + $desce > $n) {
        $desce = max(0, $n - $sobe);
    }
    return ['sobe' => $sobe, 'desce' => $desce];
}

/**
 * Processa a virada semanal: fecha os grupos da semana anterior,
 * aplica subida/descida/permanência, grava histórico e zera XP.
 * Deve ser chamado 1x por semana (cron, segunda 00h).
 */
function liga_processar_virada_semana(mysqli $conn): void {
    $semanaAtual = liga_semana_atual();
    $semanaAnterior = (new DateTime($semanaAtual))->modify('-7 days')->format('Y-m-d');

    $stmt = $conn->prepare("SELECT id, divisao FROM ligas_grupos WHERE semana_ref = ?");
    $stmt->bind_param("s", $semanaAnterior);
    $stmt->execute();
    $grupos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($grupos)) {
        return; // nada a processar (primeira semana do sistema)
    }

    foreach ($grupos as $grupo) {
        $stmt = $conn->prepare("
            SELECT lu.usuario_id, lu.xp_semana
            FROM ligas_usuario lu
            WHERE lu.grupo_id = ?
            ORDER BY lu.xp_semana DESC, lu.usuario_id ASC
        ");
        $stmt->bind_param("i", $grupo['id']);
        $stmt->execute();
        $membros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $n = count($membros);
        if ($n === 0) continue;

        $zonas = liga_calcular_zonas($n);
        $cfg = liga_config($grupo['divisao']);

        foreach ($membros as $posIdx => $membro) {
            $posicao = $posIdx + 1;
            $novaDivisao = $grupo['divisao'];
            $resultado = 'manteve';

            if ($posicao <= $zonas['sobe'] && $cfg['sobe']) {
                $novaDivisao = $cfg['proxima'];
                $resultado = 'subiu';
            } elseif ($posicao > ($n - $zonas['desce']) && $cfg['desce']) {
                $novaDivisao = $cfg['anterior'];
                $resultado = 'desceu';
            }

            $stmt2 = $conn->prepare("
                UPDATE ligas_usuario
                SET divisao = ?, xp_semana = 0, grupo_id = NULL, posicao_semana_anterior = ?
                WHERE usuario_id = ?
            ");
            $stmt2->bind_param("sii", $novaDivisao, $posicao, $membro['usuario_id']);
            $stmt2->execute();

            $stmt3 = $conn->prepare("
                INSERT INTO ligas_historico
                    (usuario_id, divisao_anterior, divisao_nova, resultado, posicao_final, xp_final, semana_ref)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt3->bind_param("issssii", $membro['usuario_id'], $grupo['divisao'], $novaDivisao, $resultado, $posicao, $membro['xp_semana'], $semanaAnterior);
            $stmt3->execute();
        }
    }

    // Novos grupos da semana atual serão criados sob demanda por liga_garantir_usuario()
}