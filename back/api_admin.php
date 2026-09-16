<?php
/**
 * API Administrativa do Sistema OPUS (Ambiente Independente)
 * Fornece dados consolidados, métricas específicas, gestão de usuários e virada de ligas.
 * Apenas usuários autenticados com nivel_acesso = 'admin' podem executar ações.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/jogador_status.php';
require_once __DIR__ . '/ligas_logic.php';

// Garante colunas no banco
opus_ensure_player_columns($conn);

// ----------------------------------------------------
// 1. VERIFICAÇÃO DE AUTENTICAÇÃO E PERMISSÃO DE ADMIN
// ----------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Sessão expirada ou usuário não autenticado. Faça login como administrador.'
    ]);
    exit();
}

$user_id_logado = (int) $_SESSION['user_id'];

// Valida se o usuário logado é realmente administrador no banco
$stmt_check = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = ?");
$stmt_check->bind_param("i", $user_id_logado);
$stmt_check->execute();
$res_check = $stmt_check->get_result()->fetch_assoc();

if (!$res_check || ($res_check['nivel_acesso'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Acesso negado. Apenas administradores do OPUS têm permissão para acessar esta API.'
    ]);
    exit();
}

// ----------------------------------------------------
// 2. ROTEAMENTO DE AÇÕES
// ----------------------------------------------------
$action = trim($_GET['action'] ?? $_POST['action'] ?? 'stats');

try {
    switch ($action) {
        // ------------------------------------------------
        // ESTATÍSTICAS ESPECÍFICAS DO PAINEL ADMIN
        // ------------------------------------------------
        case 'stats':
            // 1. Total de Usuários
            $total_users = (int) ($conn->query("SELECT COUNT(*) AS total FROM usuarios")->fetch_assoc()['total'] ?? 0);

            // 2. Dias de Ofensiva
            $com_ofensiva = (int) ($conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE dias_fogo > 0")->fetch_assoc()['total'] ?? 0);
            $max_ofensiva = (int) ($conn->query("SELECT MAX(dias_fogo) AS max_fogo FROM usuarios")->fetch_assoc()['max_fogo'] ?? 0);

            // 3. Status de Vidas dos Jogadores
            $vidas_3 = (int) ($conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE vidas >= 3")->fetch_assoc()['total'] ?? 0);
            $vidas_2 = (int) ($conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE vidas = 2")->fetch_assoc()['total'] ?? 0);
            $vidas_1 = (int) ($conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE vidas = 1")->fetch_assoc()['total'] ?? 0);
            $vidas_0 = (int) ($conn->query("SELECT COUNT(*) AS total FROM usuarios WHERE vidas <= 0")->fetch_assoc()['total'] ?? 0);

            // 4. Usuários por Divisão / Ligas
            global $LIGAS_CONFIG;
            $divisoes_contagem = [
                'bronze'   => 0,
                'prata'    => 0,
                'ouro'     => 0,
                'diamante' => 0,
                'mestre'   => 0,
            ];

            $res_divisoes = $conn->query("SELECT divisao, COUNT(*) AS total FROM ligas_usuario GROUP BY divisao");
            $alocados_em_ligas = 0;
            if ($res_divisoes) {
                while ($row = $res_divisoes->fetch_assoc()) {
                    $div = strtolower($row['divisao']);
                    if (isset($divisoes_contagem[$div])) {
                        $divisoes_contagem[$div] = (int) $row['total'];
                        $alocados_em_ligas += (int) $row['total'];
                    }
                }
            }

            // Usuários não alocados iniciam na liga Bronze
            $nao_alocados = max(0, $total_users - $alocados_em_ligas);
            $divisoes_contagem['bronze'] += $nao_alocados;

            $divisoes_detalhadas = [];
            foreach ($LIGAS_CONFIG as $slug => $cfg) {
                $qtd = $divisoes_contagem[$slug] ?? 0;
                $pct = $total_users > 0 ? round(($qtd / $total_users) * 100, 1) : 0;
                $divisoes_detalhadas[$slug] = [
                    'slug'        => $slug,
                    'nome'        => $cfg['nome'],
                    'cor'         => $cfg['cor'],
                    'corClara'    => $cfg['corClara'],
                    'quantidade'  => $qtd,
                    'porcentagem' => $pct,
                ];
            }

            // 5. Dados de Conclusão de Capítulos (1 a 5)
            $progresso_capitulos = [];
            for ($cap = 1; $cap <= 5; $cap++) {
                $stmt_prog = $conn->query("SELECT COUNT(*) AS concluidos FROM progresso_usuario WHERE unidade_numero = $cap AND status = 'completo'");
                $concluidos = (int) ($stmt_prog->fetch_assoc()['concluidos'] ?? 0);
                $pct_cap = $total_users > 0 ? round(($concluidos / $total_users) * 100, 1) : 0;
                $progresso_capitulos[] = [
                    'capitulo'    => $cap,
                    'nome'        => "Capítulo $cap",
                    'concluidos'  => $concluidos,
                    'porcentagem' => $pct_cap,
                ];
            }

            // 6. Informações de Virada de Ligas
            $semana_atual_dt = new DateTime(liga_semana_atual());
            $semana_atual_fmt = $semana_atual_dt->format('d/m/Y');
            $proxima_virada_fmt = liga_proxima_virada()->format('d/m/Y \à\s H:i');

            echo json_encode([
                'sucesso' => true,
                'data' => [
                    'total_usuarios'      => $total_users,
                    'ofensivas' => [
                        'com_fogo' => $com_ofensiva,
                        'max_fogo' => $max_ofensiva,
                    ],
                    'vidas' => [
                        'cheias_3'  => $vidas_3,
                        'medias_2'  => $vidas_2,
                        'baixas_1'  => $vidas_1,
                        'zeradas_0' => $vidas_0,
                    ],
                    'divisoes'            => $divisoes_detalhadas,
                    'progresso_capitulos' => $progresso_capitulos,
                    'ligas_info' => [
                        'semana_atual'   => $semana_atual_fmt,
                        'proxima_virada' => $proxima_virada_fmt,
                    ],
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        // ------------------------------------------------
        // LISTAGEM DE USUÁRIOS (BUSCA POR EMAIL, DIVISÃO E ID)
        // ------------------------------------------------
        case 'users':
            $q = trim($_GET['q'] ?? '');
            $filtro_divisao = trim($_GET['divisao'] ?? '');
            $order_dir = strtoupper(trim($_GET['order_dir'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

            $page = max(1, (int) ($_GET['page'] ?? 1));
            $limit = min(100, max(5, (int) ($_GET['limit'] ?? 15)));
            $offset = ($page - 1) * $limit;

            $where = ["1=1"];
            $params = [];
            $types = "";

            // Busca por e-mail (ou nome)
            if ($q !== '') {
                $where[] = "(LOWER(u.email) LIKE ? OR LOWER(u.nome) LIKE ? OR u.id = ?)";
                $q_like = "%" . strtolower($q) . "%";
                $q_id = is_numeric($q) ? (int)$q : 0;
                $params[] = $q_like;
                $params[] = $q_like;
                $params[] = $q_id;
                $types .= "ssi";
            }

            // Filtro por divisão de ligas (bronze, prata, ouro, diamante, mestre)
            if ($filtro_divisao !== '' && in_array($filtro_divisao, ['bronze', 'prata', 'ouro', 'diamante', 'mestre'])) {
                if ($filtro_divisao === 'bronze') {
                    $where[] = "(l.divisao = 'bronze' OR l.divisao IS NULL)";
                } else {
                    $where[] = "l.divisao = ?";
                    $params[] = $filtro_divisao;
                    $types .= "s";
                }
            }

            $where_sql = implode(" AND ", $where);

            // Contagem total para paginação
            $stmt_count = $conn->prepare("
                SELECT COUNT(*) AS total
                FROM usuarios u
                LEFT JOIN ligas_usuario l ON l.usuario_id = u.id
                WHERE $where_sql
            ");
            if (!empty($params)) {
                $stmt_count->bind_param($types, ...$params);
            }
            $stmt_count->execute();
            $total_registros = (int) ($stmt_count->get_result()->fetch_assoc()['total'] ?? 0);
            $total_paginas = ceil($total_registros / $limit);

            // Query principal ordenada por ID crescente ou decrescente
            $sql = "
                SELECT u.id, u.nome, u.email, u.foto_perfil,
                       u.dias_fogo, u.vidas, u.vidas_proxima_em, u.ultima_atividade,
                       u.criado_em,
                       COALESCE(l.divisao, 'bronze') AS divisao
                FROM usuarios u
                LEFT JOIN ligas_usuario l ON l.usuario_id = u.id
                WHERE $where_sql
                ORDER BY u.id $order_dir
                LIMIT ?, ?
            ";

            $params_limit = $params;
            $params_limit[] = $offset;
            $params_limit[] = $limit;
            $types_limit = $types . "ii";

            $stmt_users = $conn->prepare($sql);
            $stmt_users->bind_param($types_limit, ...$params_limit);
            $stmt_users->execute();
            $res_users = $stmt_users->get_result();

            $usuarios_lista = [];
            while ($row = $res_users->fetch_assoc()) {
                $usuarios_lista[] = [
                    'id'               => (int) $row['id'],
                    'nome'             => $row['nome'],
                    'email'            => $row['email'],
                    'vidas'            => (int) $row['vidas'],
                    'dias_fogo'        => (int) ($row['dias_fogo'] ?? 0),
                    'divisao'          => $row['divisao'] ?: 'bronze',
                    'foto_perfil'      => $row['foto_perfil'],
                    'criado_em'        => $row['criado_em'] ? date('d/m/Y H:i', strtotime($row['criado_em'])) : '-',
                ];
            }

            echo json_encode([
                'sucesso' => true,
                'data'    => [
                    'total'         => $total_registros,
                    'pagina_atual'  => $page,
                    'total_paginas' => $total_paginas,
                    'limite'        => $limit,
                    'usuarios'      => $usuarios_lista,
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        // ------------------------------------------------
        // DETALHES DE UM ALUNO (PROGRESSO DE CAPÍTULOS E LIGAS)
        // ------------------------------------------------
        case 'user_details':
            $target_id = (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0);
            if ($target_id <= 0) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID de usuário inválido.']);
                exit();
            }

            $stmt_u = $conn->prepare("
                SELECT u.id, u.nome, u.email, u.foto_perfil, u.vidas, u.dias_fogo, u.criado_em,
                       COALESCE(l.divisao, 'bronze') AS divisao
                FROM usuarios u
                LEFT JOIN ligas_usuario l ON l.usuario_id = u.id
                WHERE u.id = ?
            ");
            $stmt_u->bind_param("i", $target_id);
            $stmt_u->execute();
            $user_data = $stmt_u->get_result()->fetch_assoc();

            if (!$user_data) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado.']);
                exit();
            }

            // Progresso nos 5 Capítulos
            $stmt_prog = $conn->prepare("SELECT unidade_numero, status, licoes_concluidas, atualizado_em FROM progresso_usuario WHERE usuario_id = ? ORDER BY unidade_numero ASC");
            $stmt_prog->bind_param("i", $target_id);
            $stmt_prog->execute();
            $progresso = $stmt_prog->get_result()->fetch_all(MYSQLI_ASSOC);

            // Histórico de ligas
            $stmt_hist = $conn->prepare("SELECT divisao_anterior, divisao_nova, resultado, posicao_final, semana_ref, criado_em FROM ligas_historico WHERE usuario_id = ? ORDER BY semana_ref DESC LIMIT 10");
            $stmt_hist->bind_param("i", $target_id);
            $stmt_hist->execute();
            $historico_ligas = $stmt_hist->get_result()->fetch_all(MYSQLI_ASSOC);

            echo json_encode([
                'sucesso' => true,
                'data' => [
                    'usuario'         => [
                        'id'               => (int) $user_data['id'],
                        'nome'             => $user_data['nome'],
                        'email'            => $user_data['email'],
                        'vidas'            => (int) $user_data['vidas'],
                        'dias_fogo'        => (int) $user_data['dias_fogo'],
                        'divisao'          => $user_data['divisao'],
                        'foto_perfil'      => $user_data['foto_perfil'],
                        'criado_em'        => $user_data['criado_em'] ? date('d/m/Y H:i', strtotime($user_data['criado_em'])) : '-',
                    ],
                    'progresso'       => $progresso,
                    'historico_ligas' => $historico_ligas,
                ]
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;

        // ------------------------------------------------
        // RESTAURAR VIDAS DO ALUNO
        // ------------------------------------------------
        case 'update_lives':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido. Use POST.']);
                exit();
            }

            $target_id = (int) ($_POST['user_id'] ?? 0);
            $qtd_vidas = min(3, max(0, (int) ($_POST['vidas'] ?? 3)));

            if ($target_id <= 0) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'ID de usuário inválido.']);
                exit();
            }

            if ($qtd_vidas >= 3) {
                $stmt_up = $conn->prepare("UPDATE usuarios SET vidas = 3, vidas_proxima_em = NULL WHERE id = ?");
                $stmt_up->bind_param("i", $target_id);
            } else {
                $proxima = (new DateTime('+5 hours'))->format('Y-m-d H:i:s');
                $stmt_up = $conn->prepare("UPDATE usuarios SET vidas = ?, vidas_proxima_em = ? WHERE id = ?");
                $stmt_up->bind_param("isi", $qtd_vidas, $proxima, $target_id);
            }
            $stmt_up->execute();

            echo json_encode([
                'sucesso'   => true,
                'mensagem'  => "Vidas do usuário atualizadas para $qtd_vidas.",
                'vidas'     => $qtd_vidas,
            ]);
            break;

        // ------------------------------------------------
        // VIRADA SEMANAL DE LIGAS (DISPARO MANUAL PELO ADMIN)
        // ------------------------------------------------
        case 'trigger_league_turnover':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Método inválido. Use POST.']);
                exit();
            }

            // Executa a lógica de virada semanal
            liga_processar_virada_semana($conn);

            echo json_encode([
                'sucesso'   => true,
                'mensagem'  => 'Virada semanal de ligas processada com sucesso! Os grupos foram atualizados e o histórico gravado.',
                'executado_em' => date('d/m/Y H:i:s'),
            ]);
            break;

        default:
            echo json_encode(['sucesso' => false, 'mensagem' => "Ação '$action' desconhecida."]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno no servidor: ' . $e->getMessage()
    ]);
}
?>
