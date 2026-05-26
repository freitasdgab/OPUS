<?php
session_start();
require_once 'conexao.php';

$user_id = $_SESSION['user_id'];

// Lista de troféus configurados
$todos_trofeus = [
    ['slug' => 'precisao_absoluta', 'nome' => 'Precisão Absoluta', 'desc' => 'Complete 3 lições sem erros.'],
    ['slug' => 'lenda_vacilou', 'nome' => 'A Lenda Vacilou', 'desc' => 'Quebre sua sequência perfeita.'],
    ['slug' => 'lenda_opus', 'nome' => 'Lenda do OPUS', 'desc' => 'Conclua todos os capítulos.'],
    ['slug' => 'primeiro_codigo', 'nome' => 'Primeiro Código', 'desc' => 'Domine os fundamentos.'],
    ['slug' => 'mestre_escolhas', 'nome' => 'Mestre das Escolhas', 'desc' => 'Domine estruturas de decisão.'],
    ['slug' => 'loop_infinito', 'nome' => 'Loop Infinito', 'desc' => 'Domine ciclos de repetição.'],
    ['slug' => 'arquitetura_dados', 'nome' => 'Arquitetura de Dados', 'desc' => 'Domine arrays e matrizes.']
];

// Busca o que o usuário já conquistou
$conquistados = [];
$res = $conn->query("SELECT trofeu_slug FROM user_trofeus WHERE user_id = $user_id");
while($row = $res->fetch_assoc()) { $conquistados[] = $row['trofeu_slug']; }

echo json_encode(['lista' => $todos_trofeus, 'conquistados' => $conquistados]);