<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está devidamente logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/auth.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$capitulo = intval($_POST['capitulo'] ?? 0);
$licao = intval($_POST['licao'] ?? 0);
$respostas_usuario = $_POST['resposta'] ?? []; // Pega o array de respostas do formulário

if ($capitulo == 0 || $licao == 0) {
    echo "<script>alert('Dados da lição inválidos.'); window.location.href='../front/dashboard.php';</script>";
    exit();
}

$acertos = 0;
$total_perguntas = count($respostas_usuario);

// Loop para corrigir cada uma das perguntas enviadas
if ($total_perguntas > 0) {
    foreach ($respostas_usuario as $pergunta_id => $alternativa_escolhida) {
        $stmt = $conn->prepare("SELECT alternativa_correta FROM perguntas WHERE id = ?");
        $stmt->bind_param("i", $pergunta_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        // Compara a resposta do usuário com a resposta correta salva no banco
        if ($result && strtoupper($result['alternativa_correta']) == strtoupper($alternativa_escolhida)) {
            $acertos++;
        }
    }
}

// REGRA: Para passar de módulo no Duolingo, precisa acertar todas as 3 perguntas
if ($acertos == $total_perguntas && $total_perguntas > 0) {
    
    // 1. Busca o progresso atual do usuário para este capítulo específico
    $stmt_progresso = $conn->prepare("SELECT licoes_concluidas, status FROM progresso_usuario WHERE usuario_id = ? AND unidade_numero = ?");
    $stmt_progresso->bind_param("ii", $user_id, $capitulo);
    $stmt_progresso->execute();
    $progresso = $stmt_progresso->get_result()->fetch_assoc();

    $msg_bonus = "";

    if ($progresso) {
        $licoes_feitas_atualmente = $progresso['licoes_concluidas'];
        
        // Só incrementa o progresso se ele estiver jogando a lição inédita atual 
        // (Isso evita bugs caso ele decida jogar novamente um módulo antigo que já tinha vencido)
        if ($licao == $licoes_feitas_atualmente + 1) {
            $novas_licoes_concluidas = $licoes_feitas_atualmente + 1;
            
            // Se ele completou as 3 lições/módulos do capítulo
            if ($novas_licoes_concluidas >= 3) {
                // 1. Atualiza o status do capítulo atual para 'completo'
                $stmt_update_cap = $conn->prepare("UPDATE progresso_usuario SET licoes_concluidas = 3, status = 'completo' WHERE usuario_id = ? AND unidade_numero = ?");
                $stmt_update_cap->bind_param("ii", $user_id, $capitulo);
                $stmt_update_cap->execute();
                
                // 2. Destranca o PRÓXIMO capítulo mudando de 'trancado' para 'corrente'
                $proximo_cap = $capitulo + 1;
                $stmt_unlock_next = $conn->prepare("UPDATE progresso_usuario SET status = 'corrente' WHERE usuario_id = ? AND unidade_numero = ? AND status = 'trancado'");
                $stmt_unlock_next->bind_param("ii", $user_id, $proximo_cap);
                $stmt_unlock_next->execute();
                
                $msg_bonus = " Parabéns! Você concluiu o Capítulo " . $capitulo . " e uma nova unidade foi desbloqueada!";
            } else {
                // Se ele ainda não terminou o capítulo, apenas soma +1 nas lições concluídas daquele capítulo
                $stmt_update_licao = $conn->prepare("UPDATE progresso_usuario SET licoes_concluidas = ? WHERE usuario_id = ? AND unidade_numero = ?");
                $stmt_update_licao->bind_param("iii", $novas_licoes_concluidas, $user_id, $capitulo);
                $stmt_update_licao->execute();
            }
            
            // Recompensa Gamer: Dá +50 de XP e +1 Troféu na tabela de usuários
            $stmt_recompensa = $conn->prepare("UPDATE usuarios SET xp = xp + 50, trofeus = trofeus + 1 WHERE id = ?");
            $stmt_recompensa->bind_param("i", $user_id);
            $stmt_recompensa->execute();
        }
    }

    echo "<script>
            alert('Excelente! Você acertou todas as questões. +50 XP ganho.{$msg_bonus}');
            window.location.href='../front/dashboard.php';
          </script>";
} else {
    // Se errar qualquer questão, volta para tentar de novo
    echo "<script>
            alert('Você acertou $acertos de $total_perguntas. Revise a matéria e tente novamente!');
            window.history.back();
          </script>";
}
?>