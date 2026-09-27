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

if ($action === 'search_users') {
    $q = $_GET['q'] ?? '';
    if (strlen($q) < 1) { echo json_encode([]); exit(); }
    
    $stmt = $conn->prepare("SELECT id, nome, foto_perfil FROM usuarios WHERE id != ? AND (id = ? OR email LIKE ?)");
    $qid = (int)$q;
    $qlike = "%$q%";
    $stmt->bind_param("iis", $user_id, $qid, $qlike);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $users = [];
    while ($row = $res->fetch_assoc()) {
        $foto = $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png';
        
        // Verifica status da amizade
        $s2 = $conn->prepare("SELECT status FROM pedidos_conexao WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?)");
        $s2->bind_param("iiii", $user_id, $row['id'], $row['id'], $user_id);
        $s2->execute();
        $r2 = $s2->get_result()->fetch_assoc();
        $status = $r2['status'] ?? 'none';
        
        $users[] = [
            'id' => $row['id'],
            'nome' => $row['nome'],
            'foto' => $foto,
            'status' => $status
        ];
    }
    echo json_encode($users);
    exit();
}

if ($action === 'send_request') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    $stmt = $conn->prepare("INSERT IGNORE INTO pedidos_conexao (remetente_id, destinatario_id, status) VALUES (?, ?, 'pendente')");
    $stmt->bind_param("ii", $user_id, $target_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

if ($action === 'get_conversations') {
    // Pega todos os amigos (pedidos aceitos) ou pessoas com quem ja tem mensagem
    $query = "
        SELECT u.id, u.nome, u.foto_perfil, 
               (SELECT mensagem FROM chat_mensagens WHERE (remetente_id = ? AND destinatario_id = u.id) OR (remetente_id = u.id AND destinatario_id = ?) ORDER BY data_envio DESC LIMIT 1) as ultima_msg,
               (SELECT COUNT(*) FROM chat_mensagens WHERE remetente_id = u.id AND destinatario_id = ? AND status_leitura != 'lido') as unread
        FROM usuarios u
        WHERE u.id IN (
            SELECT destinatario_id FROM pedidos_conexao WHERE remetente_id = ? AND status = 'aceito'
            UNION
            SELECT remetente_id FROM pedidos_conexao WHERE destinatario_id = ? AND status = 'aceito'
        )
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
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
    echo json_encode($convs);
    exit();
}

if ($action === 'get_messages') {
    $target_id = (int)($_GET['target_id'] ?? 0);
    
    // Marca como lido
    $stmt = $conn->prepare("UPDATE chat_mensagens SET status_leitura = 'lido' WHERE remetente_id = ? AND destinatario_id = ?");
    $stmt->bind_param("ii", $target_id, $user_id);
    $stmt->execute();
    
    // Busca mensagens
    $stmt = $conn->prepare("
        SELECT id, remetente_id, mensagem, data_envio 
        FROM chat_mensagens 
        WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?)
        ORDER BY data_envio ASC
    ");
    $stmt->bind_param("iiii", $user_id, $target_id, $target_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $msgs = [];
    $has_duel = false;
    while ($row = $res->fetch_assoc()) {
        $msgs[] = [
            'id' => $row['id'],
            'remetente_id' => $row['remetente_id'],
            'mensagem' => $row['mensagem'],
            'data' => $row['data_envio']
        ];
        if (strpos($row['mensagem'], '[DUEL_INVITE]') !== false) {
            $has_duel = true;
        }
    }
    
    // Informações do amigo
    $s2 = $conn->prepare("SELECT nome, foto_perfil FROM usuarios WHERE id = ?");
    $s2->bind_param("i", $target_id);
    $s2->execute();
    $amigo = $s2->get_result()->fetch_assoc();
    $foto = $amigo['foto_perfil'] ?: '../assets/img/opi pulando feliz.png';
    
    echo json_encode([
        'messages' => $msgs, 
        'friend' => ['id' => $target_id, 'nome' => $amigo['nome'], 'foto' => $foto],
        'has_active_duel' => $has_duel // Ativa o ícone flutuante
    ]);
    exit();
}

if ($action === 'send_message') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    $msg = $_POST['mensagem'] ?? '';
    
    if (trim($msg) !== '' && $target_id > 0) {
        $stmt = $conn->prepare("INSERT INTO chat_mensagens (remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $target_id, $msg);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

echo json_encode(['error' => 'invalid_action']);
