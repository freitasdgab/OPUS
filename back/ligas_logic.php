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
 * Garante que o usuário tenha um registro em ligas_usuario, alocado num
 * grupo válido da semana atual, e soma XP ao total da semana. Toda essa
 * lógica agora mora em sp_liga_registrar_xp (back/sql/opus_procedures.sql).
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
 * Deve ser chamado 1x por semana (cron, segunda 00h). Toda a lógica
 * agora mora em sp_processar_virada_semana (back/sql/opus_procedures.sql).
 */
function liga_processar_virada_semana(mysqli $conn): void {
    $conn->query("CALL sp_processar_virada_semana()");
    while ($conn->more_results()) {
        $conn->next_result();
    }
}