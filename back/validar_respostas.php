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

    // Busca progresso atual
    $stmt_prog = $conn->prepare("SELECT licoes_concluidas, status FROM progresso_usuario WHERE usuario_id = ? AND unidade_numero = ?");
    $stmt_prog->bind_param("ii", $user_id, $capitulo);
    $stmt_prog->execute();
    $progresso = $stmt_prog->get_result()->fetch_assoc();

    $msg_bonus          = "";
    $capitulo_concluido = false;

    if ($progresso) {
        $licoes_feitas = $progresso['licoes_concluidas'];

        // Só avança se for a lição inédita atual
        if ($licao == $licoes_feitas + 1) {
            $novas_licoes = $licoes_feitas + 1;

            if ($novas_licoes >= 3) {
                // Capítulo completo
                $stmt_up = $conn->prepare("UPDATE progresso_usuario SET licoes_concluidas = 3, status = 'completo' WHERE usuario_id = ? AND unidade_numero = ?");
                $stmt_up->bind_param("ii", $user_id, $capitulo);
                $stmt_up->execute();

                // Destrava próximo capítulo
                $proximo = $capitulo + 1;
                $stmt_unlock = $conn->prepare("UPDATE progresso_usuario SET status = 'corrente' WHERE usuario_id = ? AND unidade_numero = ? AND status = 'trancado'");
                $stmt_unlock->bind_param("ii", $user_id, $proximo);
                $stmt_unlock->execute();

                $msg_bonus          = " 🎉 Capítulo $capitulo concluído! Próxima unidade desbloqueada!";
                $capitulo_concluido = true;
            } else {
                // Incrementa lição
                $stmt_inc = $conn->prepare("UPDATE progresso_usuario SET licoes_concluidas = ? WHERE usuario_id = ? AND unidade_numero = ?");
                $stmt_inc->bind_param("iii", $novas_licoes, $user_id, $capitulo);
                $stmt_inc->execute();
            }

            // +50 XP, +1 troféu
            $stmt_xp = $conn->prepare("UPDATE usuarios SET xp = xp + 50, trofeus = trofeus + 1 WHERE id = ?");
            $stmt_xp->bind_param("i", $user_id);
            $stmt_xp->execute();

            // Atualiza streak
            atualizar_streak($conn, $user_id);

            // Verifica conquistas
            $nova_conquista = verificar_conquistas($conn, $user_id, $capitulo, $capitulo_concluido);
            if ($nova_conquista) {
                $msg_bonus .= " 🏆 Nova conquista: $nova_conquista!";
            }
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

// ============================================================
// FUNÇÃO: Atualiza streak diário
// ============================================================
function atualizar_streak(mysqli $conn, int $user_id): void {
    $stmt = $conn->prepare("SELECT sequencia_dias, ultima_atividade FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    $hoje   = date('Y-m-d');
    $ontem  = date('Y-m-d', strtotime('-1 day'));
    $ultima = $u['ultima_atividade'];
    $seq    = (int) $u['sequencia_dias'];

    if ($ultima === $hoje) {
        return; // Já jogou hoje, não altera
    } elseif ($ultima === $ontem) {
        $seq += 1; // Continua a sequência
    } else {
        $seq = 1;  // Reinicia (perdeu um dia ou nunca jogou)
    }

    $stmt_up = $conn->prepare("UPDATE usuarios SET sequencia_dias = ?, ultima_atividade = ? WHERE id = ?");
    $stmt_up->bind_param("isi", $seq, $hoje, $user_id);
    $stmt_up->execute();
}

// ============================================================
// FUNÇÃO: Verifica e desbloqueia conquistas automaticamente
// ============================================================
function verificar_conquistas(mysqli $conn, int $user_id, int $capitulo, bool $capitulo_concluido): string {
    // Busca dados atuais do usuário
    $stmt = $conn->prepare("SELECT xp, sequencia_dias FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    // Busca conquistas já desbloqueadas
    $res_conq = $conn->query("SELECT trofeu_slug FROM user_trofeus WHERE user_id = $user_id");
    $conquistados = [];
    while ($r = $res_conq->fetch_assoc()) {
        $conquistados[] = $r['trofeu_slug'];
    }

    // Conta capítulos completos
    $res_caps = $conn->query("SELECT COUNT(*) as total FROM progresso_usuario WHERE usuario_id = $user_id AND status = 'completo'");
    $caps_completos = (int)($res_caps->fetch_assoc()['total'] ?? 0);

    // Mapa de conquistas: slug → condição
    $regras = [
        'primeiro_codigo'    => ($capitulo == 1 && $capitulo_concluido),
        'mestre_escolhas'    => ($capitulo == 2 && $capitulo_concluido),
        'loop_infinito'      => ($capitulo == 3 && $capitulo_concluido),
        'arquitetura_dados'  => ($capitulo == 4 && $capitulo_concluido),
        'poo_master'         => ($capitulo == 5 && $capitulo_concluido),
        'lenda_opus'         => ($caps_completos >= 5),
        'precisao_absoluta'  => ($u['xp'] >= 150),
        'sequencia_7'        => ($u['sequencia_dias'] >= 7),
        'sequencia_30'       => ($u['sequencia_dias'] >= 30),
    ];

    $nomes = [
        'primeiro_codigo'   => 'Primeiro Código',
        'mestre_escolhas'   => 'Mestre das Escolhas',
        'loop_infinito'     => 'Loop Infinito',
        'arquitetura_dados' => 'Arquitetura de Dados',
        'poo_master'        => 'Mestre da POO',
        'lenda_opus'        => 'Lenda do OPUS',
        'precisao_absoluta' => 'Precisão Absoluta',
        'sequencia_7'       => 'Semana Perfeita',
        'sequencia_30'      => 'Mês Épico',
    ];

    $nova_nome = '';

    foreach ($regras as $slug => $condicao) {
        if ($condicao && !in_array($slug, $conquistados)) {
            $stmt_ins = $conn->prepare("INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (?, ?)");
            $stmt_ins->bind_param("is", $user_id, $slug);
            $stmt_ins->execute();
            if ($nova_nome === '') {
                $nova_nome = $nomes[$slug] ?? $slug;
            }
        }
    }

    return $nova_nome;
}
?>