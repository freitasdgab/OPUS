<?php
session_start();
header('Content-Type: application/json'); // Diz ao navegador que o retorno é um JSON
require_once 'conexao.php'; // Certifique-se que o nome do seu arquivo de conexão é esse

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Busca as informações do usuário atual (para a barra superior)
    $stmt_user = $conn->prepare("SELECT nome, xp, trofeus, dificuldade FROM usuarios WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_info = $result_user->fetch_assoc();

    // Se o usuário não for encontrado (ex: deletado), retorna os dados zerados para não quebrar o layout
    if (!$user_info) {
        $user_info = ['nome' => 'Desconhecido', 'xp' => 0, 'trofeus' => 0, 'dificuldade' => 'Iniciante'];
    }

    // 2. Busca todos os usuários do banco ordenados por XP (do maior para o menor)
    $query_ranking = "SELECT id, nome, xp FROM usuarios ORDER BY xp DESC";
    $result_ranking = $conn->query($query_ranking);

    $ranking = [];
    $posicao_atual = 1;
    $minha_posicao = 0;

    // Percorre todos os usuários e define quem é quem
    while ($row = $result_ranking->fetch_assoc()) {
        $row['posicao'] = $posicao_atual;
        $row['is_me'] = ($row['id'] == $user_id);
        
        if ($row['is_me']) {
            $minha_posicao = $posicao_atual;
        }
        
        $ranking[] = $row;
        $posicao_atual++;
    }

    // 3. Separa o Array: Os 3 primeiros vão para o pódio, o resto vai para a lista
    $top3 = array_slice($ranking, 0, 3);
    $lista_resto = array_slice($ranking, 3);

    // 4. Envia tudo mastigado para o JavaScript
    echo json_encode([
        "user_info" => $user_info,
        "minha_posicao" => $minha_posicao,
        "top3" => $top3,
        "lista_resto" => $lista_resto
    ]);

} catch (Exception $e) {
    // Em caso de erro no banco, retorna o erro no formato JSON para o JS conseguir ler
    echo json_encode(["error" => true, "message" => "Erro no servidor: " . $e->getMessage()]);
}
?>