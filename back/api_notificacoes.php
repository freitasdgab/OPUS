<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_notificacoes') {
    // Busca pedidos de amizade pendentes
    $stmt = $conn->prepare("
        SELECT p.id, p.remetente_id, u.nome, u.foto_perfil 
        FROM pedidos_conexao p
        JOIN usuarios u ON p.remetente_id = u.id
        WHERE p.destinatario_id = ? AND p.status = 'pendente'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $notificacoes = [];
    while ($row = $res->fetch_assoc()) {
        $foto = $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png';
        $notificacoes[] = [
            'type' => 'friend_request',
            'id' => $row['id'],
            'remetente_id' => $row['remetente_id'],
            'nome' => $row['nome'],
            'foto' => $foto
        ];
    }
    
    // Conta mensagens nao lidas
    $stmt = $conn->prepare("
        SELECT c.remetente_id, u.nome, u.foto_perfil, COUNT(*) as qtd
        FROM chat_mensagens c
        JOIN usuarios u ON c.remetente_id = u.id
        WHERE c.destinatario_id = ? AND c.status_leitura != 'lido'
        GROUP BY c.remetente_id
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $foto = $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png';
        $notificacoes[] = [
            'type' => 'unread_messages',
            'remetente_id' => $row['remetente_id'],
            'nome' => $row['nome'],
            'foto' => $foto,
            'qtd' => $row['qtd']
        ];
    }
    
    echo json_encode(['notificacoes' => $notificacoes, 'count' => count($notificacoes)]);
    exit();
}

if ($action === 'respond_request') {
    $req_id = (int)($_POST['id'] ?? 0);
    $resp = $_POST['response'] ?? ''; // 'aceito' ou 'recusado'
    
    if ($resp === 'aceito' || $resp === 'recusado') {
        $stmt = $conn->prepare("UPDATE pedidos_conexao SET status = ? WHERE id = ? AND destinatario_id = ?");
        $stmt->bind_param("sii", $resp, $req_id, $user_id);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

echo json_encode(['error' => 'invalid_action']);
