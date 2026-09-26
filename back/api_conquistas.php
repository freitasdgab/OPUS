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
    ["slug" => "primeiro_passo", "nome" => "Primeiro Passo", "desc" => "Concluiu sua primeira lição.", "imagem" => "primeirospassos.png"],
    ["slug" => "perfeicao", "nome" => "Mente Brilhante", "desc" => "Acertou 3/3 em um desafio.", "imagem" => "mentebrilhante-3de3acertos.png"],
    ["slug" => "capitulo_1", "nome" => "Fundamentos", "desc" => "Terminou o Capítulo 1.", "imagem" => "fundamentos-capitulo1.png"],
    ["slug" => "capitulo_2", "nome" => "Caminhos Lógicos", "desc" => "Dominou as Estruturas de Decisão no Cap. 2.", "imagem" => "caminhoslogicos-capitulo2.png"],
    ["slug" => "capitulo_3", "nome" => "Mestre da Repetição", "desc" => "Dominou os Loops no Capítulo 3.", "imagem" => "mestredarepeticao.png"],
    ["slug" => "capitulo_4", "nome" => "Senhor dos Arrays", "desc" => "Dominou Arrays e Matrizes no Capítulo 4.", "imagem" => "senhor dos arrays.png"],
    ["slug" => "capitulo_5", "nome" => "Arquiteto Java", "desc" => "Concluiu POO no Capítulo 5. Você é o mestre!", "imagem" => "arquitetojava.png"],
    ["slug" => "fogo_3", "nome" => "Em Chamas", "desc" => "Alcançou 3 Dias de Fogo.", "imagem" => "alcancos3diasdefogo.png"]
];

// 2. Busca no banco de dados quais troféus ESTE usuário já ganhou
$conquistados = [];
$stmt = $conn->query("SELECT trofeu_slug FROM user_trofeus WHERE user_id = $user_id");

if ($stmt) {
    while ($row = $stmt->fetch_assoc()) {
        $conquistados[] = $row['trofeu_slug'];
    }
}

// 3. Devolve os dados prontos para o JavaScript pintar a tela
echo json_encode([
    "lista" => $todos_trofeus,
    "conquistados" => $conquistados
]);
?>