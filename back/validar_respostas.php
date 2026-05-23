<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $licao_id = intval($_POST['licao_id']);
    $respostas_usuario = $_POST['resposta'] ?? []; // Array contendo [pergunta_id => 'A/B/C']

    $total_perguntas = count($respostas_usuario);
    $acertos = 0;

    if ($total_perguntas < 3) {
        echo "<script>alert('Por favor, responda a todas as 3 perguntas!'); window.history.back();</script>";
        exit();
    }

    // Loop para verificar cada resposta no banco de dados
    foreach ($respostas_usuario as $pergunta_id => $alternativa_escolhida) {
        $stmt = $conn->prepare("SELECT alternativa_correta FROM perguntas WHERE id = ? AND licao_id = ?");
        $stmt->bind_param("ii", $pergunta_id, $licao_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res && $res['alternativa_correta'] === $alternativa_escolhida) {
            $acertos++;
        }
    }

    // Regra: Passa se acertar todas as 3 perguntas da lição
    if ($acertos === 3) {
        // 1. Dá uma recompensa de +100 XP e +1 Troféu para o usuário
        $conn->query("UPDATE usuarios SET xp = xp + 100, trofeus = trofeus + 1 WHERE id = $user_id");

        // 2. Lógica para desbloquear a próxima Unidade/Capítulo no banco
        // Aqui simula que ao terminar a lição do Cap 1, desbloqueia o Cap 2
        $conn->query("UPDATE progresso_usuario SET status = 'completo', licoes_concluidas = 15 WHERE usuario_id = $user_id AND unidade_numero = 1");
        $conn->query("UPDATE progresso_usuario SET status = 'corrente' WHERE usuario_id = $user_id AND unidade_numero = 2");

        echo "<script>
            alert('Parabéns! Você acertou todas as perguntas! Ganhou 100 XP e liberou o próximo capítulo.');
            window.location.href = '../front/dashboard.php';
        </script>";
        exit();
    } else {
        // Se errou alguma, avisa e pede para ler de novo
        echo "<script>
            alert('Você acertou $acertos de 3 perguntas. Revise o código explicativo da esquerda e tente novamente!');
            window.history.back();
        </script>";
        exit();
    }
}
?>