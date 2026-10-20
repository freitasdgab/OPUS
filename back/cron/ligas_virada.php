<?php
// Rode via cron (Linux) ou Agendador de Tarefas (Windows), 1x por mês (dia 1):
// php /caminho/para/back/cron/ligas_virada.php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/ligas_logic.php';

liga_processar_virada_semana($conn);
echo "Virada de semana das ligas processada em " . date('Y-m-d H:i:s') . "\n";