<?php
/**
 * Núcleo do sistema de Ligas (divisões semanais).
 * Padrão de cores segue a mesma lógica de back/lib/mascotes_capitulos.php
 */

const LIGAS_TAMANHO_GRUPO = 30;

$LIGAS_CONFIG = [
    'bronze'   => ['nome' => 'Bronze',   'cor' => '#a5682a', 'corClara' => '#cd7f32', 'proxima' => 'prata',    'anterior' => null,       'sobe' => true,  'desce' => false],
    'prata'    => ['nome' => 'Prata',    'cor' => '#8e8e99', 'corClara' => '#c0c0c0', 'proxima' => 'ouro',     'anterior' => 'bronze',   'sobe' => true,  'desce' => true],
    'ouro'     => ['nome' => 'Ouro',     'cor' => '#d4a017', 'corClara' => '#ffd700', 'proxima' => 'diamante', 'anterior' => 'prata',    'sobe' => true,  'desce' => true],
    'diamante' => ['nome' => 'Diamante', 'cor' => '#1ec8e0', 'corClara' => '#5be7ff', 'proxima' => 'mestre',   'anterior' => 'ouro',     'sobe' => true,  'desce' => true],
    'mestre'   => ['nome' => 'Mestre',   'cor' => '#8b2fd9', 'corClara' => '#c47bff', 'proxima' => null,       'anterior' => 'diamante', 'sobe' => false, 'desce' => false],
];

function liga_config(string $divisao): array {
    global $LIGAS_CONFIG;
    return $LIGAS_CONFIG[$divisao] ?? $LIGAS_CONFIG['bronze'];
}

function liga_todas_divisoes(): array {
    global $LIGAS_CONFIG;
    return array_keys($LIGAS_CONFIG);
}

/**
 * Retorna a data (Y-m-d) do domingo de início da semana atual de ligas.
 * Domingo é considerado o dia 1 do ciclo semanal.
 */
function liga_semana_atual(): string {
    $hoje = new DateTime('today');
    $w = (int)$hoje->format('w'); // 0 = Domingo, 1 = Segunda, ..., 6 = Sábado
    $inicioSemana = (clone $hoje)->modify("-{$w} days");
    return $inicioSemana->format('Y-m-d');
}

/**
 * Data/hora da próxima virada semanal (sempre no Domingo às 23:59:59).
 */
function liga_proxima_virada(): DateTime {
    $agora = new DateTime('now');
    $w = (int)$agora->format('w'); // 0 = Domingo, 1 = Segunda, ..., 6 = Sábado
    $virada = (clone $agora)->setTime(23, 59, 59);

    if ($w === 0) {
        // Se hoje já é domingo, vira hoje às 23:59:59 (ou no próximo domingo se já passou)
        if ($virada <= $agora) {
            $virada->modify('+7 days');
        }
    } else {
        $diasAteDomingo = 7 - $w;
        $virada->modify("+{$diasAteDomingo} days");
    }
    return $virada;
}

/**
 * Garante que o usuário tenha um registro em ligas_usuario, alocado num
 * grupo válido da semana atual, e soma XP ao total da semana.
 */
function liga_registrar_xp(mysqli $conn, int $usuario_id, int $xp): void {
    $stmt = $conn->prepare("CALL sp_liga_registrar_xp(?, ?)");
    $stmt->bind_param("ii", $usuario_id, $xp);
    $stmt->execute();
    $stmt->close();
    $conn->next_result();
}

/**
 * Garante a alocação (sem somar XP) e devolve a linha atual de
 * ligas_usuario do jogador, para telas que só precisam exibir o estado.
 */
function liga_garantir_usuario(mysqli $conn, int $usuario_id): array {
    liga_registrar_xp($conn, $usuario_id, 0);

    $stmt = $conn->prepare("SELECT * FROM ligas_usuario WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() ?: ['usuario_id' => $usuario_id, 'divisao' => 'bronze', 'grupo_id' => null, 'xp_semana' => 0];
}

/**
 * Calcula quantos sobem/descem em um grupo de tamanho $n.
 * Regra semanal:
 * - Os primeiros sobem (top 10 ou ~33%).
 * - Os do meio ficam (permanecem na divisão).
 * - Os de baixo caem (últimos 5 ou ~17%).
 */
function liga_calcular_zonas(int $n): array {
    if ($n <= 0) return ['sobe' => 0, 'desce' => 0];
    if ($n <= 3) {
        return ['sobe' => 1, 'desce' => 0];
    }
    if ($n <= 7) {
        return ['sobe' => 2, 'desce' => 1];
    }
    if ($n <= 15) {
        return ['sobe' => 4, 'desce' => 2];
    }
    $sobe = min(10, (int)ceil($n * 0.33));
    $desce = min(5, (int)floor($n * 0.17));
    return ['sobe' => $sobe, 'desce' => $desce];
}

/**
 * Permite ao administrador alterar manualmente a divisão de qualquer aluno a qualquer momento.
 */
function liga_alterar_divisao_usuario(mysqli $conn, int $usuario_id, string $nova_divisao): bool {
    global $LIGAS_CONFIG;
    if (!isset($LIGAS_CONFIG[$nova_divisao])) {
        return false;
    }

    $stmt = $conn->prepare("
        INSERT INTO ligas_usuario (usuario_id, divisao, grupo_id, xp_semana)
        VALUES (?, ?, NULL, 0)
        ON DUPLICATE KEY UPDATE divisao = VALUES(divisao), grupo_id = NULL
    ");
    $stmt->bind_param("is", $usuario_id, $nova_divisao);
    $ok = $stmt->execute();
    $stmt->close();

    liga_garantir_usuario($conn, $usuario_id);
    return $ok;
}

/**
 * Processa a virada semanal:
 * - Fecha grupos da semana atual.
 * - Promove os primeiros colocados (exceto quem já está em Mestre).
 * - Mantém os colocados do meio na mesma divisão.
 * - Rebaixa os últimos colocados (exceto Bronze e Mestre, que NUNCA caem).
 * - Grava o histórico detalhado em ligas_historico.
 * - Zera o xp_semana para a nova semana de referência.
 */
function liga_processar_virada_semana(mysqli $conn): array {
    global $LIGAS_CONFIG;

    $semana_ref = liga_semana_atual();

    // Busca todos os grupos que possuem membros
    $sqlGrupos = "
        SELECT DISTINCT g.id, g.divisao, g.semana_ref
        FROM ligas_grupos g
        JOIN ligas_usuario u ON u.grupo_id = g.id
    ";
    $resGrupos = $conn->query($sqlGrupos);

    $total_processados = 0;
    $total_subiram = 0;
    $total_mantiveram = 0;
    $total_desceram = 0;

    $grupos = [];
    if ($resGrupos) {
        while ($row = $resGrupos->fetch_assoc()) {
            $grupos[] = $row;
        }
    }

    foreach ($grupos as $g) {
        $grupo_id = (int) $g['id'];
        $divisao_grupo = $g['divisao'];
        $cfg = $LIGAS_CONFIG[$divisao_grupo] ?? $LIGAS_CONFIG['bronze'];

        $stmtM = $conn->prepare("
            SELECT usuario_id, xp_semana, divisao
            FROM ligas_usuario
            WHERE grupo_id = ?
            ORDER BY xp_semana DESC, usuario_id ASC
        ");
        $stmtM->bind_param("i", $grupo_id);
        $stmtM->execute();
        $membros = $stmtM->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtM->close();

        $n = count($membros);
        if ($n === 0) continue;

        $zonas = liga_calcular_zonas($n);

        foreach ($membros as $idx => $m) {
            $posicao = $idx + 1;
            $user_id = (int) $m['usuario_id'];
            $div_atual = $m['divisao'] ?: $divisao_grupo;
            $xp_final = (int) $m['xp_semana'];

            $div_nova = $div_atual;
            $resultado = 'manteve';

            // 1. Primeiros colocados: sobem se a divisão tiver próxima
            if ($posicao <= $zonas['sobe'] && !empty($cfg['sobe']) && !empty($cfg['proxima'])) {
                $div_nova = $cfg['proxima'];
                $resultado = 'subiu';
                $total_subiram++;
            }
            // 2. Últimos colocados: descem somente se a divisão permitir (Mestre e Bronze nunca descem!)
            elseif ($posicao > ($n - $zonas['desce']) && !empty($cfg['desce']) && !empty($cfg['anterior'])) {
                $div_nova = $cfg['anterior'];
                $resultado = 'desceu';
                $total_desceram++;
            }
            // 3. Meio da tabela (ou divisões sem subida/descida): mantém
            else {
                $resultado = 'manteve';
                $total_mantiveram++;
            }

            // Grava histórico
            $stmtHist = $conn->prepare("
                INSERT INTO ligas_historico 
                (usuario_id, divisao_anterior, divisao_nova, resultado, posicao_final, xp_final, semana_ref)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtHist->bind_param("isssiis", $user_id, $div_atual, $div_nova, $resultado, $posicao, $xp_final, $semana_ref);
            $stmtHist->execute();
            $stmtHist->close();

            // Atualiza usuário: zera XP semanal, guarda posição final da semana anterior e desassocia do grupo encerrado
            $stmtUp = $conn->prepare("
                UPDATE ligas_usuario 
                SET divisao = ?, xp_semana = 0, posicao_semana_anterior = ?, grupo_id = NULL
                WHERE usuario_id = ?
            ");
            $stmtUp->bind_param("sii", $div_nova, $posicao, $user_id);
            $stmtUp->execute();
            $stmtUp->close();

            $total_processados++;
        }
    }

    return [
        'processados' => $total_processados,
        'subiram'     => $total_subiram,
        'mantiveram'  => $total_mantiveram,
        'desceram'    => $total_desceram
    ];
}