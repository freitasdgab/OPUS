<?php
session_start();
require_once __DIR__ . '/../conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_notificacoes') {
    // Busca solicitações pendentes para seguir
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
            'foto' => $foto,
            'mensagem' => $row['nome'] . ' enviou uma solicitação para te seguir'
        ];
    }
    
    // Busca pedidos enviados por mim que foram aceitos recentemente
    $stmt = $conn->prepare("
        SELECT p.id, p.destinatario_id, p.viraram_amigos, u.nome, u.foto_perfil 
        FROM pedidos_conexao p
        JOIN usuarios u ON p.destinatario_id = u.id
        WHERE p.remetente_id = ? AND p.status = 'aceito' AND (p.notificado_aceite IS NULL OR p.notificado_aceite = 0)
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $foto = $row['foto_perfil'] ?: '../assets/img/opi pulando feliz.png';
        $isAmigos = ((int)$row['viraram_amigos'] === 1);
        $notificacoes[] = [
            'type' => $isAmigos ? 'friend_accepted' : 'follow_accepted',
            'id' => $row['id'],
            'amigo_id' => $row['destinatario_id'],
            'nome' => $row['nome'],
            'foto' => $foto,
            'viraram_amigos' => $isAmigos ? 1 : 0,
            'mensagem' => $isAmigos
                ? $row['nome'] . ' aceitou seu pedido e seguiu de volta! Vocês agora são amigos.'
                : $row['nome'] . ' aceitou sua solicitação para seguir.'
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
            'qtd' => $row['qtd'],
            'mensagem' => $row['nome'] . ' enviou ' . $row['qtd'] . ' nova(s) mensagem(ns)'
        ];
    }
    
    echo json_encode(['notificacoes' => $notificacoes, 'count' => count($notificacoes)]);
    exit();
}

if ($action === 'respond_request') {
    $req_id = (int)($_POST['id'] ?? 0);
    $resp = $_POST['response'] ?? ''; // 'aceito' ou 'recusado'
    $seguir_de_volta = isset($_POST['seguir_de_volta']) ? (int)$_POST['seguir_de_volta'] : 1;
    
    if ($resp === 'recusado') {
        $stmt = $conn->prepare("UPDATE pedidos_conexao SET status = 'recusado', notificado_aceite = 1, data_resposta = NOW() WHERE id = ? AND destinatario_id = ?");
        $stmt->bind_param("ii", $req_id, $user_id);
        $stmt->execute();
        echo json_encode(['success' => true, 'action' => 'recusado']);
        exit();
    }
    
    if ($resp === 'aceito') {
        $stmt_rem = $conn->prepare("SELECT remetente_id FROM pedidos_conexao WHERE id = ? AND destinatario_id = ?");
        $stmt_rem->bind_param("ii", $req_id, $user_id);
        $stmt_rem->execute();
        $r_rem = $stmt_rem->get_result()->fetch_assoc();
        
        if ($r_rem) {
            $rem_id = (int)$r_rem['remetente_id'];
            
            // Remetente passa a seguir o destinatário (quem aceitou)
            $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($rem_id, $user_id)");
            
            $viraram_amigos = ($seguir_de_volta === 1) ? 1 : 0;
            if ($viraram_amigos === 1) {
                // Destinatário segue o remetente de volta -> viram amigos mútuos
                $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($user_id, $rem_id)");
            }
            
            $stmt_up = $conn->prepare("UPDATE pedidos_conexao SET status = 'aceito', viraram_amigos = ?, notificado_aceite = 0, data_resposta = NOW() WHERE id = ? AND destinatario_id = ?");
            $stmt_up->bind_param("iii", $viraram_amigos, $req_id, $user_id);
            $stmt_up->execute();
            
            echo json_encode(['success' => true, 'viraram_amigos' => ($viraram_amigos === 1), 'remetente_id' => $rem_id]);
            exit();
        }
    }
    
    echo json_encode(['success' => false]);
    exit();
}

if ($action === 'seguir_de_volta') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    if ($target_id > 0 && $target_id !== $user_id) {
        // Eu passo a seguir o alvo de volta
        $conn->query("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES ($user_id, $target_id)");
        
        // Atualiza conexão existente para amigos mútuos
        $stmt_up = $conn->prepare("UPDATE pedidos_conexao SET status = 'aceito', viraram_amigos = 1, notificado_aceite = 0, data_resposta = NOW() WHERE (remetente_id = ? AND destinatario_id = ?) OR (remetente_id = ? AND destinatario_id = ?)");
        $stmt_up->bind_param("iiii", $user_id, $target_id, $target_id, $user_id);
        $stmt_up->execute();
        
        echo json_encode(['success' => true, 'amigos' => true]);
        exit();
    }
    echo json_encode(['success' => false, 'mensagem' => 'ID inválido']);
    exit();
}

if ($action === 'solicitar_seguir') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    if ($target_id <= 0 || $target_id === $user_id) {
        echo json_encode(['success' => false, 'mensagem' => 'ID inválido']);
        exit();
    }
    
    // Verifica se já segue
    $check_seg = $conn->query("SELECT id FROM seguidores WHERE seguidor_id = $user_id AND seguido_id = $target_id");
    if ($check_seg && $check_seg->num_rows > 0) {
        echo json_encode(['success' => true, 'status' => 'ja_segue']);
        exit();
    }
    
    // Insere ou atualiza solicitação pendente
    $stmt_ped = $conn->prepare("INSERT INTO pedidos_conexao (remetente_id, destinatario_id, status, notificado_aceite) VALUES (?, ?, 'pendente', 0) ON DUPLICATE KEY UPDATE status = 'pendente', notificado_aceite = 0");
    $stmt_ped->bind_param("ii", $user_id, $target_id);
    $stmt_ped->execute();
    
    echo json_encode(['success' => true, 'status' => 'pendente']);
    exit();
}

if ($action === 'cancelar_solicitacao') {
    $target_id = (int)($_POST['target_id'] ?? 0);
    if ($target_id > 0) {
        $stmt_del = $conn->prepare("DELETE FROM pedidos_conexao WHERE remetente_id = ? AND destinatario_id = ? AND status = 'pendente'");
        $stmt_del->bind_param("ii", $user_id, $target_id);
        $stmt_del->execute();
        echo json_encode(['success' => true]);
        exit();
    }
    echo json_encode(['success' => false, 'mensagem' => 'ID inválido']);
    exit();
}

if ($action === 'dismiss_accepted') {
    $req_id = (int)($_POST['id'] ?? 0);
    $stmt = $conn->prepare("UPDATE pedidos_conexao SET notificado_aceite = 1 WHERE id = ? AND remetente_id = ?");
    $stmt->bind_param("ii", $req_id, $user_id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['error' => 'invalid_action']);
