<?php
session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── BUSCA APENAS POR NOME OU EMAIL ──
if ($action === 'search_users') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 1) { 
        echo json_encode([]); 
        exit(); 
    }
    
    $stmt = $conn->prepare("SELECT id, nome, email, foto_perfil FROM usuarios WHERE id != ? AND (nome LIKE ? OR email LIKE ?) LIMIT 15");
    $qlike = "%$q%";
    $stmt->bind_param("iss", $user_id, $qlike, $qlike);
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
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'email' => $row['email'],
            'foto' => $foto,
            'status' => $status
        ];
    }
    echo json_encode($users);
    exit();
}

if ($action === 'send_request') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    if ($target_id <= 0 || $target_id === $user_id) {
        echo json_encode(['success' => false, 'error' => 'invalid_target']);
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO pedidos_conexao (remetente_id, destinatario_id, status, notificado_aceite) VALUES (?, ?, 'pendente', 0) ON DUPLICATE KEY UPDATE status = 'pendente', notificado_aceite = 0");
    $stmt->bind_param("ii", $user_id, $target_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

if ($action === 'get_conversations') {
    // Pega todos os amigos (pedidos aceitos)
    $query = "
        SELECT u.id, u.nome, u.foto_perfil, 
               (SELECT mensagem FROM chat_mensagens WHERE (remetente_id = ? AND destinatario_id = u.id) OR (remetente_id = u.id AND destinatario_id = ?) ORDER BY data_envio DESC LIMIT 1) as ultima_msg,
               (SELECT data_envio FROM chat_mensagens WHERE (remetente_id = ? AND destinatario_id = u.id) OR (remetente_id = u.id AND destinatario_id = ?) ORDER BY data_envio DESC LIMIT 1) as ultima_data,
               (SELECT COUNT(*) FROM chat_mensagens WHERE remetente_id = u.id AND destinatario_id = ? AND status_leitura != 'lido') as unread
        FROM usuarios u
        WHERE u.id IN (
            SELECT destinatario_id FROM pedidos_conexao WHERE remetente_id = ? AND status = 'aceito'
            UNION
            SELECT remetente_id FROM pedidos_conexao WHERE destinatario_id = ? AND status = 'aceito'
        )
        ORDER BY (ultima_data IS NOT NULL) DESC, ultima_data DESC, u.nome ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $convs = [];
    while ($row = $res->fetch_assoc()) {
        $amigo_id = (int)$row['id'];
        
        $convs[] = [
            'id' => $amigo_id,
            'nome' => $row['nome'],
            'foto' => $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png',
            'ultima_msg' => $row['ultima_msg'] ?: 'Nova conversa iniciada. Diga olá!',
            'ultima_data' => $row['ultima_data'],
            'unread' => (int)$row['unread']
        ];
    }
    echo json_encode($convs);
    exit();
}

if ($action === 'get_messages') {
    $target_id = (int)($_GET['target_id'] ?? 0);
    if ($target_id <= 0) {
        echo json_encode(['error' => 'invalid_target']);
        exit();
    }
    
    // Marca mensagens recebidas como lidas
    $stmt = $conn->prepare("UPDATE chat_mensagens SET status_leitura = 'lido' WHERE remetente_id = ? AND destinatario_id = ?");
    $stmt->bind_param("ii", $target_id, $user_id);
    $stmt->execute();
    
    // Busca mensagens ordenadas por data
    $stmt = $conn->prepare("
        SELECT id, remetente_id, destinatario_id, mensagem, status_leitura, data_envio 
        FROM chat_mensagens 
        WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?)
        ORDER BY data_envio ASC
    ");
    $stmt->bind_param("iiii", $user_id, $target_id, $target_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $msgs = [];
    while ($row = $res->fetch_assoc()) {
        $msgs[] = [
            'id' => (int)$row['id'],
            'remetente_id' => (int)$row['remetente_id'],
            'mensagem' => $row['mensagem'],
            'status' => $row['status_leitura'] ?? 'lido',
            'data' => $row['data_envio']
        ];
    }
    
    // Informações do amigo (sem sequências ou níveis)
    $s2 = $conn->prepare("SELECT nome, foto_perfil FROM usuarios WHERE id = ?");
    $s2->bind_param("i", $target_id);
    $s2->execute();
    $amigo = $s2->get_result()->fetch_assoc();
    $foto = !empty($amigo['foto_perfil']) ? $amigo['foto_perfil'] : '../assets/img/opi pulando feliz.png';
    
    echo json_encode([
        'messages' => $msgs, 
        'friend' => [
            'id' => $target_id, 
            'nome' => $amigo['nome'] ?? 'Amigo', 
            'foto' => $foto
        ]
    ]);
    exit();
}

if ($action === 'send_message') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    $msg = trim($_POST['mensagem'] ?? '');
    
    if ($msg !== '' && $target_id > 0) {
        $stmt = $conn->prepare("INSERT INTO chat_mensagens (remetente_id, destinatario_id, mensagem, status_leitura) VALUES (?, ?, ?, 'nao_lido')");
        $stmt->bind_param("iis", $user_id, $target_id, $msg);
        $stmt->execute();
        $novo_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'id' => $novo_id,
            'mensagem' => $msg,
            'status' => 'nao_lido',
            'data' => date('Y-m-d H:i:s')
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'empty_or_invalid']);
    }
    exit();
}

echo json_encode(['error' => 'invalid_action']);
