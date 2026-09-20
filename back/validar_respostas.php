<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/pages/auth.html");
    exit();
}

$user_id  = $_SESSION['user_id'];
$capitulo = intval($_POST['capitulo'] ?? 0);
$licao    = intval($_POST['licao']    ?? 0);
$respostas_usuario = $_POST['resposta'] ?? [];

if ($capitulo == 0 || $licao == 0) {
    echo "<script>alert('Dados da lição inválidos.'); window.location.href='../front/pages/dashboard.php';</script>";
    exit();
}

// ============================================================
// 1. CORRIGIR RESPOSTAS
// Comparar a alternativa escolhida com a correta é uma checagem
// simples por pergunta — fica no PHP. Tudo o que acontece DEPOIS de
// acertar tudo (avançar progresso, XP, troféu, liga, missão, streak,
// conquistas) mora em sp_corrigir_licao (back/sql/opus_procedures.sql).
// ============================================================
$acertos         = 0;
$total_perguntas = count($respostas_usuario);

if ($total_perguntas > 0) {
    foreach ($respostas_usuario as $pergunta_id => $alternativa_escolhida) {
        $stmt = $conn->prepare("SELECT alternativa_correta FROM perguntas WHERE id = ?");
        $stmt->bind_param("i", $pergunta_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && strtoupper($result['alternativa_correta']) == strtoupper($alternativa_escolhida)) {
            $acertos++;
        }
    }
}

// ============================================================
// 2. SE ACERTOU TUDO
// ============================================================
if ($acertos == $total_perguntas && $total_perguntas > 0) {

    // sp_corrigir_licao chama sp_atualizar_fogo por dentro (que também
    // termina com um SELECT), então usa opus_call() pra garantir que
    // pegamos o result set final, não o de uma chamada interna.
    $resultado = opus_call($conn, "CALL sp_corrigir_licao(?, ?, ?, ?)", "iiii", [$user_id, $capitulo, $licao, $acertos]);

    $msg_bonus = "";
    if ($resultado && (int) $resultado['avancou'] === 1) {
        if ((int) $resultado['capitulo_concluido'] === 1) {
            $msg_bonus = " 🎉 Capítulo $capitulo concluído! Próxima unidade desbloqueada!";
        } elseif ((int) $resultado['bau_liberado'] === 1) {
            $msg_bonus = " 🎁 Baú de Recompensas liberado na Dashboard!";
        }

        if (!empty($resultado['nova_conquista'])) {
            $msg_bonus .= " 🏆 Nova conquista: {$resultado['nova_conquista']}!";
        }
    }

    echo "<script>
            alert('✅ Excelente! Você acertou todas as questões. +50 XP ganho.$msg_bonus');
            window.location.href='../front/pages/dashboard.php';
          </script>";

} else {
    echo "<script>
            alert('❌ Você acertou $acertos de $total_perguntas. Revise e tente novamente!');
            window.history.back();
          </script>";
}
