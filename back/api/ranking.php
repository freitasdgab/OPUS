<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Busca as informações do usuário logado
$stmt = $conn->prepare("SELECT nome, xp, trofeus, foto_perfil, pais, pais_codigo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_info = $stmt->get_result()->fetch_assoc();

// 2. Busca TODOS os usuários ordenados pelo XP
$query = "SELECT id, nome, xp, foto_perfil, pais, pais_codigo FROM usuarios ORDER BY xp DESC, id ASC";
$result = $conn->query($query);

$ranking = [];
$minha_posicao = 0;
$pos = 1;

while ($row = $result->fetch_assoc()) {
    $is_me = ($row['id'] == $user_id);
    
    if ($is_me) {
        $minha_posicao = $pos;
    }
    
    $ranking[] = [
        "id" => (int)$row['id'],
        "posicao" => $pos,
        "nome" => $row['nome'],
        "xp" => $row['xp'],
        "foto_perfil" => $row['foto_perfil'],
        "pais" => !empty($row['pais']) ? $row['pais'] : 'Brasil',
        "pais_codigo" => !empty($row['pais_codigo']) ? strtolower($row['pais_codigo']) : 'br',
        "is_me" => $is_me
    ];
    
    $pos++;
}

// 3. Separa quem é Top 3 e quem é o resto da lista
$top3 = array_slice($ranking, 0, 3);
$lista_resto = array_slice($ranking, 3);

// 4. Envia tudo para o ranking.php desenhar a tela
echo json_encode([
    "user_info" => $user_info,
    "minha_posicao" => $minha_posicao,
    "top3" => $top3,
    "lista_resto" => $lista_resto
]);
?>