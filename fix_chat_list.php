<?php
$c = file_get_contents('back/api_chat.php');

$search = <<<'PHP'
    $convs = [];
    while ($row = $res->fetch_assoc()) {
        $convs[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'foto' => $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png',
            'ultima_msg' => $row['ultima_msg'] ?: 'Nova conexão! Diga olá.',
            'unread' => $row['unread']
        ];
    }
PHP;

$replace = <<<'PHP'
    $convs = [];
    while ($row = $res->fetch_assoc()) {
        // Verificar se tem batalha ativa com esse amigo
        $amigo_id = $row['id'];
        $b_stmt = $conn->prepare("SELECT id FROM batalhas_amigos WHERE ((user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)) AND vencedor_id IS NULL");
        $b_stmt->bind_param("iiii", $user_id, $amigo_id, $amigo_id, $user_id);
        $b_stmt->execute();
        $is_battling = $b_stmt->get_result()->num_rows > 0;
        
        $convs[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'foto' => $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png',
            'ultima_msg' => $row['ultima_msg'] ?: 'Nova conexão! Diga olá.',
            'unread' => $row['unread'],
            'is_battling' => $is_battling
        ];
    }
PHP;

// Need to handle exact spacing. Better to use regex for the whole block or just str_replace.
// Let's do a simple regex since 'Nova conexão! Diga olá.' might be encoded differently.
$c = preg_replace('/\$convs = \[\];.*?while \(\$row = \$res->fetch_assoc\(\)\) \{.*?\$convs\[\] = \[.*?\];\s*\}/s', $replace, $c);

file_put_contents('back/api_chat.php', $c);
echo "fixed api_chat convs";
