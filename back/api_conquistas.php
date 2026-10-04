<?php
session_start();
header('Content-Type: application/json');
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["erro" => "Não autenticado"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Lista de todos os troféus disponíveis no Opus
$todos_trofeus = [
    ["slug" => "primeiro_passo", "nome" => "Primeiro Passo", "desc" => "Concluiu sua primeira lição.", "imagem" => "primeirospassos.png", "max" => 1, "tipo" => "licao_u1"],
    ["slug" => "perfeicao", "nome" => "Mente Brilhante", "desc" => "Acertou 3/3 em um desafio.", "imagem" => "mentebrilhante-3de3acertos.png", "max" => 1, "tipo" => "desafio"],
    ["slug" => "capitulo_1", "nome" => "Fundamentos", "desc" => "Terminou o Capítulo 1.", "imagem" => "fundamentos-capitulo1.png", "max" => 5, "tipo" => "licao_u1"],
    ["slug" => "capitulo_2", "nome" => "Caminhos Lógicos", "desc" => "Dominou as Estruturas de Decisão no Cap. 2.", "imagem" => "caminhoslogicos-capitulo2.png", "max" => 5, "tipo" => "licao_u2"],
    ["slug" => "capitulo_3", "nome" => "Mestre da Repetição", "desc" => "Dominou os Loops no Capítulo 3.", "imagem" => "mestredarepeticao.png", "max" => 5, "tipo" => "licao_u3"],
    ["slug" => "capitulo_4", "nome" => "Senhor dos Arrays", "desc" => "Dominou Arrays e Matrizes no Capítulo 4.", "imagem" => "senhor dos arrays.png", "max" => 5, "tipo" => "licao_u4"],
    ["slug" => "capitulo_5", "nome" => "Arquiteto Java", "desc" => "Concluiu POO no Capítulo 5. Você é o mestre!", "imagem" => "arquitetojava.png", "max" => 5, "tipo" => "licao_u5"],
    ["slug" => "fogo_3", "nome" => "Em Chamas", "desc" => "Alcançou 3 Dias de Fogo.", "imagem" => "alcancos3diasdefogo.png", "max" => 3, "tipo" => "fogo"]
];

// Busca status do usuario para calcular progresso
$user_info = $conn->query("SELECT dias_fogo, xp FROM usuarios WHERE id = $user_id")->fetch_assoc();
$dias_fogo = $user_info ? (int)$user_info['dias_fogo'] : 0;
$xp = $user_info ? (int)$user_info['xp'] : 0;

$progresso_licoes = [];
$res_prog = $conn->query("SELECT unidade_numero, licoes_concluidas FROM progresso_usuario WHERE usuario_id = $user_id");
if ($res_prog) {
    while ($row = $res_prog->fetch_assoc()) {
        $progresso_licoes[(int)$row['unidade_numero']] = (int)$row['licoes_concluidas'];
    }
}

$conquistados = [];
$stmt = $conn->query("SELECT trofeu_slug as slug FROM user_trofeus WHERE user_id = $user_id");
if ($stmt) {
    while ($row = $stmt->fetch_assoc()) {
        $conquistados[] = $row['slug'];
    }
}

// Calcula o progresso (em %) para cada trofeu
foreach ($todos_trofeus as &$t) {
    $slug = $t['slug'];
    $tipo = $t['tipo'] ?? '';
    $max = $t['max'] ?? 1;
    $atual = 0;

    if (in_array($slug, $conquistados)) {
        $prog_percent = 100;
    } else {
        if ($tipo == 'fogo') {
            $atual = min($dias_fogo, $max);
        } elseif (strpos($tipo, 'licao_u') === 0) {
            $unidade = (int)str_replace('licao_u', '', $tipo);
            $atual = isset($progresso_licoes[$unidade]) ? min($progresso_licoes[$unidade], $max) : 0;
        } elseif ($tipo == 'xp') {
            $atual = min($xp, $max);
        }
        
        $prog_percent = ($max > 0) ? floor(($atual / $max) * 100) : 0;

        // Correção retroativa: se o progresso atingiu 100%, desbloqueia o troféu
        if ($prog_percent >= 100) {
            $conquistados[] = $slug;
            $conn->query("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES ($user_id, '$slug')");
            $prog_percent = 100;
        }
    }
    
    $t['progresso_percent'] = $prog_percent;
}

// 3. Devolve os dados prontos para o JavaScript pintar a tela
echo json_encode([
    "lista" => $todos_trofeus,
    "conquistados" => $conquistados
]);
?>