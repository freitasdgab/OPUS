<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../conexao.php';
require_once __DIR__ . '/../jogador_status.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autenticado']);
    exit();
}

$meu_id = (int) $_SESSION['user_id'];
$target_id = (int) ($_GET['user_id'] ?? $_POST['user_id'] ?? 0);

if ($target_id <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário inválido']);
    exit();
}

// Sincroniza status para valores atualizados de vidas/fogo
$status_target = opus_sincronizar_jogador($conn, $target_id);

$stmt = $conn->prepare("
    SELECT u.id, u.nome, u.email, u.foto_perfil, u.cor_fundo, u.xp, u.trofeus, u.dias_fogo, u.ultima_atividade,
           u.pais, u.pais_codigo, u.criado_em,
           u.link_github, u.link_instagram, u.link_facebook, u.link_youtube, u.link_linkedin, u.link_email,
           u.projeto_titulo_1, u.projeto_url_1, u.projeto_titulo_2, u.projeto_url_2, u.projeto_titulo_3, u.projeto_url_3,
           COALESCE(l.divisao, 'Bronze') AS divisao,
           COALESCE(l.xp_semana, 0) AS xp_semana
    FROM usuarios u
    LEFT JOIN ligas_usuario l ON l.usuario_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $target_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado']);
    exit();
}

// Data formatada (membro desde)
$meses = ['', 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
if (!empty($user['criado_em'])) {
    $ts = strtotime($user['criado_em']);
    $mes_idx = (int)date('n', $ts);
    $ano = date('Y', $ts);
    $membro_desde = "Por aqui desde " . $meses[$mes_idx] . " de " . $ano;
} else {
    $membro_desde = "Por aqui desde " . $meses[(int)date('n')] . " de " . date('Y');
}

// Verifica se eu sigo este usuário
$check_eu_sigo = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $meu_id AND seguido_id = $target_id");
$eu_sigo = ($check_eu_sigo && $check_eu_sigo->num_rows > 0);

// Verifica se o usuário me segue
$check_ele_me_segue = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $target_id AND seguido_id = $meu_id");
$ele_me_segue = ($check_ele_me_segue && $check_ele_me_segue->num_rows > 0);

// Status da conexão / pedido
$stmt_ped = $conn->prepare("SELECT id, remetente_id, destinatario_id, status, viraram_amigos FROM pedidos_conexao WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?) ORDER BY id DESC LIMIT 1");
$stmt_ped->bind_param("iiii", $meu_id, $target_id, $target_id, $meu_id);
$stmt_ped->execute();
$r_ped = $stmt_ped->get_result()->fetch_assoc();

$status_amizade = 'nenhum';
$pedido_id = 0;

if ($eu_sigo && $ele_me_segue) {
    // Ambos se seguem -> amigos mútuos!
    $status_amizade = 'amigos';
} elseif ($r_ped && $r_ped['status'] === 'pendente') {
    $pedido_id = (int)$r_ped['id'];
    $status_amizade = ((int)$r_ped['remetente_id'] === $meu_id) ? 'pendente_enviado' : 'pendente_recebido';
} elseif ($ele_me_segue && !$eu_sigo) {
    // Ele me segue, mas eu ainda não o sigo de volta -> opção "Quer ser amigo? Seguir de volta"
    $status_amizade = 'ele_me_segue';
    if ($r_ped) $pedido_id = (int)$r_ped['id'];
} elseif ($eu_sigo && !$ele_me_segue) {
    // Eu sigo ele, mas ele não me segue de volta
    $status_amizade = 'eu_sigo';
} else {
    $status_amizade = 'nenhum';
}

// Contagem de seguidores e seguindo
$res_seguidores = $conn->query("SELECT COUNT(*) AS total FROM seguidores WHERE seguido_id = $target_id");
$seguidores_count = $res_seguidores ? (int)$res_seguidores->fetch_assoc()['total'] : 0;

$res_seguindo = $conn->query("SELECT COUNT(*) AS total FROM seguidores WHERE seguidor_id = $target_id");
$seguindo_count = $res_seguindo ? (int)$res_seguindo->fetch_assoc()['total'] : 0;

// Lista de conquistas disponíveis
$todos_trofeus = [
    ["slug" => "primeiro_passo", "nome" => "Primeiro Passo", "desc" => "Concluiu sua primeira lição.", "imagem" => "primeirospassos.png"],
    ["slug" => "perfeicao", "nome" => "Mente Brilhante", "desc" => "Acertou 3/3 em um desafio.", "imagem" => "mentebrilhante-3de3acertos.png"],
    ["slug" => "capitulo_1", "nome" => "Fundamentos", "desc" => "Terminou o Capítulo 1.", "imagem" => "fundamentos-capitulo1.png"],
    ["slug" => "capitulo_2", "nome" => "Caminhos Lógicos", "desc" => "Dominou as Estruturas de Decisão no Cap. 2.", "imagem" => "caminhoslogicos-capitulo2.png"],
    ["slug" => "capitulo_3", "nome" => "Mestre da Repetição", "desc" => "Dominou os Loops no Capítulo 3.", "imagem" => "mestredarepeticao.png"],
    ["slug" => "capitulo_4", "nome" => "Senhor dos Arrays", "desc" => "Dominou Arrays e Matrizes no Capítulo 4.", "imagem" => "senhor dos arrays.png"],
    ["slug" => "capitulo_5", "nome" => "Arquiteto Java", "desc" => "Concluiu POO no Capítulo 5.", "imagem" => "arquitetojava.png"],
    ["slug" => "fogo_3", "nome" => "Em Chamas", "desc" => "Alcançou 3 Dias de Fogo.", "imagem" => "alcancos3diasdefogo.png"]
];

$conquistas_desbloqueadas = [];
$res_trofeus = $conn->query("SELECT trofeu_slug FROM user_trofeus WHERE user_id = $target_id");
if ($res_trofeus) {
    while ($r = $res_trofeus->fetch_assoc()) {
        $slug = $r['trofeu_slug'];
        foreach ($todos_trofeus as $t) {
            if ($t['slug'] === $slug) {
                $conquistas_desbloqueadas[] = $t;
                break;
            }
        }
    }
}

// Projetos Git
$projetos = [];
for ($i = 1; $i <= 3; $i++) {
    if (!empty($user["projeto_titulo_$i"])) {
        $projetos[] = [
            'titulo' => $user["projeto_titulo_$i"],
            'url'    => $user["projeto_url_$i"] ?? '#'
        ];
    }
}

// Redes Sociais
$redes = [];
if (!empty($user['link_github']))    $redes['github'] = $user['link_github'];
if (!empty($user['link_instagram'])) $redes['instagram'] = $user['link_instagram'];
if (!empty($user['link_facebook']))  $redes['facebook'] = $user['link_facebook'];
if (!empty($user['link_youtube']))   $redes['youtube'] = $user['link_youtube'];
if (!empty($user['link_linkedin']))  $redes['linkedin'] = $user['link_linkedin'];
if (!empty($user['link_email']))     $redes['email'] = $user['link_email'];

echo json_encode([
    'sucesso' => true,
    'dados' => [
        'id'            => (int)$user['id'],
        'nome'          => $user['nome'],
        'username'      => strtolower(str_replace(' ', '', $user['nome'])) . $user['id'],
        'foto_perfil'   => $user['foto_perfil'],
        'cor_fundo'     => $user['cor_fundo'] ?: '#1cb0f6',
        'pais'          => !empty($user['pais']) ? $user['pais'] : 'Brasil',
        'pais_codigo'   => !empty($user['pais_codigo']) ? strtolower($user['pais_codigo']) : 'br',
        'membro_desde'  => $membro_desde,
        'divisao'       => ucfirst(strtolower($user['divisao'])),
        'xp'            => (int)$user['xp'],
        'xp_semana'     => (int)$user['xp_semana'],
        'trofeus'       => count($conquistas_desbloqueadas),
        'dias_fogo'     => (int)$user['dias_fogo'],
        'fogo_hoje'     => !empty($status_target['fogo_hoje']),
        'is_me'         => ($target_id === $meu_id),
        'is_seguindo'   => $eu_sigo,
        'ele_me_segue'  => $ele_me_segue,
        'sao_amigos'    => ($eu_sigo && $ele_me_segue),
        'status_amizade'=> $status_amizade,
        'pedido_id'     => $pedido_id,
        'seguidores'    => $seguidores_count,
        'seguindo'      => $seguindo_count,
        'redes'         => $redes,
        'projetos'      => $projetos,
        'conquistas'    => $conquistas_desbloqueadas
    ]
], JSON_UNESCAPED_UNICODE);
