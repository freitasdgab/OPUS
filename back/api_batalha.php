<?php
session_start();
require_once 'conexao.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'msg' => 'Não logado']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'listar') {
    // Listar batalhas ativas do usuario
    $sql = "SELECT b.id, b.data_inicio, b.data_fim, b.vencedor_id,
            u1.nome as nome1, u1.foto_perfil as foto1, 
            u2.nome as nome2, u2.foto_perfil as foto2,
            u1.id as uid1, u2.id as uid2
            FROM batalhas_amigos b
            JOIN usuarios u1 ON b.user1_id = u1.id
            JOIN usuarios u2 ON b.user2_id = u2.id
            WHERE (b.user1_id = ? OR b.user2_id = ?) AND b.vencedor_id IS NULL";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $batalhas = [];
    while($row = $res->fetch_assoc()) {
        $oponente_nome = ($row['uid1'] == $user_id) ? $row['nome2'] : $row['nome1'];
        $oponente_foto = ($row['uid1'] == $user_id) ? $row['foto2'] : $row['foto1'];
        $batalhas[] = [
            'id' => $row['id'],
            'oponente_nome' => $oponente_nome,
            'oponente_foto' => $oponente_foto,
            'dias_restantes' => 7 // mock
        ];
    }
    echo json_encode(['success' => true, 'batalhas' => $batalhas]);
    exit();
}

if ($action === 'desafiar') {
    // Pega o primeiro amigo para mockar
    $amigo_id = $_POST['amigo_id'] ?? 0;
    if (!$amigo_id) {
        $stmt = $conn->prepare("SELECT amigo_id FROM seguidores WHERE user_id = ? LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $amigo_id = $res->fetch_assoc()['amigo_id'];
        } else {
            // Pega qualquer usuario
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id != ? LIMIT 1");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $amigo_id = $res->fetch_assoc()['id'];
            }
        }
    }
    
    if (!$amigo_id) {
        echo json_encode(['success' => false, 'msg' => 'Nenhum oponente disponível.']);
        exit();
    }
    
    $data_inicio = date('Y-m-d H:i:s');
    $data_fim = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    $xp1 = $conn->query("SELECT xp FROM usuarios WHERE id = $user_id")->fetch_assoc()['xp'] ?? 0;
    $xp2 = $conn->query("SELECT xp FROM usuarios WHERE id = $amigo_id")->fetch_assoc()['xp'] ?? 0;
    $stmt = $conn->prepare("INSERT INTO batalhas_amigos (user1_id, user2_id, data_inicio, data_fim, xp_inicio_1, xp_inicio_2) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissii", $user_id, $amigo_id, $data_inicio, $data_fim, $xp1, $xp2);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

if ($action === 'simular_vitoria') {
    $batalha_id = $_POST['batalha_id'];
    
    // Atualiza vencedor
    $stmt = $conn->prepare("UPDATE batalhas_amigos SET vencedor_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $user_id, $batalha_id);
    $stmt->execute();
    
    // Dar o troféu
    $stmt2 = $conn->prepare("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (?, 'batalha_irmaos')");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    
    // Dar XP
    $stmt3 = $conn->prepare("UPDATE usuarios SET xp = xp + 500 WHERE id = ?");
    $stmt3->bind_param("i", $user_id);
    $stmt3->execute();
    
    echo json_encode(['success' => true, 'msg' => 'Você venceu a batalha e ganhou o troféu Batalha de Irmãos!']);
    exit();
}
