<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['sucesso' => false, 'erro' => 'Sessão expirada. Faça login novamente.']);
    exit();
}

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../lib/desafios_logic.php';

$user_id = (int) $_SESSION['user_id'];

// Lê dados de JSON ou POST
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true);

$action = $_GET['action'] ?? $_POST['action'] ?? ($json_data['action'] ?? 'listar');

switch ($action) {
    case 'listar':
        try {
            $dados = desafios_listar($conn, $user_id);
            echo json_encode(['sucesso' => true, 'dados' => $dados], JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['sucesso' => false, 'erro' => 'Erro ao carregar desafios: ' . $e->getMessage()]);
        }
        break;

    case 'detalhe':
        $id_ou_slug = $_GET['id'] ?? $_GET['slug'] ?? ($json_data['id'] ?? ($json_data['slug'] ?? 0));
        if (empty($id_ou_slug)) {
            echo json_encode(['sucesso' => false, 'erro' => 'Identificador do desafio não informado.']);
            exit();
        }

        $desafio = desafios_obter_detalhe($conn, $user_id, $id_ou_slug);
        if (!$desafio) {
            echo json_encode(['sucesso' => false, 'erro' => 'Desafio não encontrado.']);
            exit();
        }

        echo json_encode(['sucesso' => true, 'desafio' => $desafio], JSON_UNESCAPED_UNICODE);
        break;

    case 'salvar_rascunho':
        $desafio_id = (int) ($_POST['desafio_id'] ?? ($json_data['desafio_id'] ?? 0));
        $codigo     = (string) ($_POST['codigo'] ?? ($json_data['codigo'] ?? ''));

        if ($desafio_id <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID de desafio inválido.']);
            exit();
        }

        $ok = desafios_salvar_rascunho($conn, $user_id, $desafio_id, $codigo);
        echo json_encode(['sucesso' => $ok], JSON_UNESCAPED_UNICODE);
        break;

    case 'submeter':
        $desafio_id = (int) ($_POST['desafio_id'] ?? ($json_data['desafio_id'] ?? 0));
        $codigo     = (string) ($_POST['codigo'] ?? ($json_data['codigo'] ?? ''));
        $saida      = (string) ($_POST['saida'] ?? ($json_data['saida'] ?? ''));
        $compilou   = !empty($_POST['sucesso_compilacao'] ?? ($json_data['sucesso_compilacao'] ?? true));

        if ($desafio_id <= 0) {
            echo json_encode(['sucesso' => false, 'erro' => 'ID de desafio inválido.']);
            exit();
        }

        if (trim($codigo) === '') {
            echo json_encode(['sucesso' => false, 'erro' => 'O código não pode estar vazio.']);
            exit();
        }

        $resultado = desafios_submeter($conn, $user_id, $desafio_id, $codigo, $saida, $compilou);
        echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['sucesso' => false, 'erro' => 'Ação desconhecida.']);
        break;
}
