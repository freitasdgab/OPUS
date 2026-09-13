<?php
session_start();
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

require_once '../../back/jogador_status.php';
require_once '../../back/ligas_logic.php';
require_once '../../back/missoes_logic.php';

$user_id = (int) $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);
$vidas_atual = (int) $status_jogador['vidas'];
$sem_vidas = ($vidas_atual <= 0);
$dias_fogo = (int) ($status_jogador['dias_fogo'] ?? 0);
$total_trofeus = (int) ($status_jogador['trofeus'] ?? 0);
$xp_total = (int) ($status_jogador['xp'] ?? 0);

// Busca progresso das unidades
$unidades = [];
$result_progresso = $conn->query("SELECT unidade_numero, status, licoes_concluidas FROM progresso_usuario WHERE usuario_id = $user_id ORDER BY unidade_numero ASC");

if ($result_progresso) {
    while ($row = $result_progresso->fetch_assoc()) {
        $unidades[$row['unidade_numero']] = $row;
    }
}

// 1. DADOS REAIS DE LIGA DO USUÁRIO
$minha_liga = liga_garantir_usuario($conn, $user_id);
$divisao_usuario = $minha_liga['divisao'] ?? 'bronze';
$cfg_liga = liga_config($divisao_usuario);
$posicao_usuario = 1;
$total_grupo = 1;
$minha_zona = 'neutro';
$xp_semana_usuario = (int) ($minha_liga['xp_semana'] ?? 0);

if (!empty($minha_liga['grupo_id'])) {
    $stmt_membros = $conn->prepare("
        SELECT usuario_id, xp_semana 
        FROM ligas_usuario 
        WHERE grupo_id = ? 
        ORDER BY xp_semana DESC, usuario_id ASC
    ");
    $stmt_membros->bind_param("i", $minha_liga['grupo_id']);
    $stmt_membros->execute();
    $membros_grupo = $stmt_membros->get_result()->fetch_all(MYSQLI_ASSOC);

    $total_grupo = count($membros_grupo);
    if ($total_grupo > 0) {
        $zonas = liga_calcular_zonas($total_grupo);

        foreach ($membros_grupo as $idx => $m) {
            if ((int) $m['usuario_id'] === $user_id) {
                $posicao_usuario = $idx + 1;
                break;
            }
        }

        if ($posicao_usuario <= $zonas['sobe'] && $cfg_liga['sobe']) {
            $minha_zona = 'sobe';
        } elseif ($posicao_usuario > ($total_grupo - $zonas['desce']) && $cfg_liga['desce']) {
            $minha_zona = 'desce';
        } else {
            $minha_zona = 'neutro';
        }
    }
}

// 2. DADOS REAIS DE MISSÕES DIÁRIAS DO USUÁRIO
$missoes_hoje = missoes_obter_hoje($conn, $user_id);

// 3. DADOS DE ATIVIDADE DE HOJE (OFENSIVA)
$hoje_data = date('Y-m-d');
$stmt_ult = $conn->prepare("SELECT ultima_atividade FROM usuarios WHERE id = ?");
$stmt_ult->bind_param("i", $user_id);
$stmt_ult->execute();
$row_ult = $stmt_ult->get_result()->fetch_assoc();
$praticou_hoje = (!empty($row_ult['ultima_atividade']) && $row_ult['ultima_atividade'] === $hoje_data);

// 4. PROGRESSO GERAL DO CURSO
$total_licoes_feitas = 0;
foreach ($unidades as $u) {
    if (($u['status'] ?? '') === 'completo') {
        $total_licoes_feitas += 5;
    } else {
        $total_licoes_feitas += (int) ($u['licoes_concluidas'] ?? 0);
    }
}
$total_licoes_curso = 25;
$progresso_porcentagem = min(100, (int) round(($total_licoes_feitas / $total_licoes_curso) * 100));

// 5. BAÚS DE RECOMPENSA RESGATADOS PELO USUÁRIO
$baus_resgatados = [];
$conn->query("CREATE TABLE IF NOT EXISTS `bau_recompensas` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `usuario_id` INT(11) NOT NULL,
    `unidade_numero` INT(11) NOT NULL,
    `tipo_recompensa` VARCHAR(50) NOT NULL DEFAULT 'misto',
    `xp_ganho` INT(11) NOT NULL DEFAULT 50,
    `vidas_ganhas` INT(11) NOT NULL DEFAULT 0,
    `resgatado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_user_unidade` (`usuario_id`, `unidade_numero`),
    KEY `idx_usuario` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

$res_baus = $conn->query("SELECT unidade_numero FROM bau_recompensas WHERE usuario_id = $user_id");
if ($res_baus) {
    while ($rb = $res_baus->fetch_assoc()) {
        $baus_resgatados[] = (int) $rb['unidade_numero'];
    }
}

// Configuração dos Capítulos
$nomes_unidades = [
    1 => [
        "titulo" => "Unidade 1",
        "nome" => "Fundamentos e Sintaxe Básica",
        "descricao" => "Aprenda os conceitos básicos da linguagem, variáveis e tipos de dados.",
        "cor" => "#1cb0f6",
        "mascote" => "vistodecimaazul.png",
        "guia_texto" => "Nesta unidade você aprende como declarar variáveis, compreender tipos de dados (String, Int, Boolean) e exibir informações na tela com echo.",
        "guia_codigo" => '$nome = "Opus";<br>$idade = 20;<br>echo "Bem-vindo ao " . $nome;'
    ],
    2 => [
        "titulo" => "Unidade 2",
        "nome" => "Estruturas de Decisão",
        "descricao" => "Domine o fluxo do código usando if, else e switch para tomar decisões.",
        "cor" => "#ff527b",
        "mascote" => "vistodecimarosa.png",
        "guia_texto" => "Aprenda a controlar o fluxo do seu sistema utilizando condicionais. O código executará diferentes blocos com base em condições verdadeiras ou falsas.",
        "guia_codigo" => '$nota = 8;<br>if ($nota >= 7) {<br>&nbsp;&nbsp;&nbsp;&nbsp;echo "Aprovado!";<br>} else {<br>&nbsp;&nbsp;&nbsp;&nbsp;echo "Revisar conteúdo";<br>}'
    ],
    3 => [
        "titulo" => "Unidade 3",
        "nome" => "Estruturas de Repetição",
        "descricao" => "Automatize tarefas repetitivas com laços for, while e do-while.",
        "cor" => "#ce82ff",
        "mascote" => "vistodecimaroxo.png",
        "guia_texto" => "Evite repetição manual de código. Utilize laços de repetição para executar uma mesma instrução múltiplas vezes até atingir um critério de parada.",
        "guia_codigo" => 'for ($i = 1; $i <= 3; $i++) {<br>&nbsp;&nbsp;&nbsp;&nbsp;echo "Executando lição " . $i . "&lt;br&gt;";<br>}'
    ],
    4 => [
        "titulo" => "Unidade 4",
        "nome" => "Arrays e Matrizes",
        "descricao" => "Armazene e manipule coleções de dados de forma eficiente.",
        "cor" => "#58cc02",
        "mascote" => "vistodecimaverde.png",
        "guia_texto" => "Arrays permitem guardar múltiplos valores em uma única variável. Você pode acessar os dados por índices numéricos ou chaves personalizadas.",
        "guia_codigo" => '$linguagens = ["PHP", "JavaScript", "SQL"];<br>echo $linguagens[0]; // Imprime: PHP'
    ],
    5 => [
        "titulo" => "Unidade 5",
        "nome" => "Introdução à POO",
        "descricao" => "Crie programas modelando o mundo real com classes e objetos.",
        "cor" => "#ffc800",
        "mascote" => "vistodecimalaranja.png",
        "guia_texto" => "A Programação Orientada a Objetos (POO) estrutura o código em Classes e Objetos, facilitando o reuso e a organização do projeto.",
        "guia_codigo" => 'class Usuario {<br>&nbsp;&nbsp;&nbsp;&nbsp;public $nome = "Aluno";<br>}<br>$user = new Usuario();<br>echo $user->nome;'
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Progresso - Opus</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    
    <style>
        /* BASE & LAYOUT */
        body { font-family: 'Nunito', sans-serif; overflow-x: hidden; }
        
        .dashboard-grid::before, .dashboard-grid::after,
        .curriculum-column::before, .curriculum-column::after,
        .unit-list::before, .unit-list::after,
        .capitulo-container::before, .capitulo-container::after,
        .trail-flex::before, .trail-flex::after,
        .modulo-node::before, .modulo-node::after,
        .main-content::before, .main-content::after {
            display: none !important;
        }

        /* GRID PRINCIPAL: 100% DA TELA E ESPALHADA PELOS CANTOS */
        @media (min-width: 1024px) {
            .dashboard-grid {
                display: flex !important;
                flex-direction: row !important;
                justify-content: space-between !important; 
                align-items: flex-start !important;
                gap: 40px !important;
                max-width: 100% !important; 
                width: 100% !important; 
                margin: 20px 0 !important;
                padding: 0 40px; 
                box-sizing: border-box;
            }
            .curriculum-column {
                flex: 1 !important; 
                display: flex;
                flex-direction: column;
                align-items: center; 
            }
            .widgets-column {
                width: 360px !important; 
                flex: none !important;
                /* CÓDIGO PARA DEIXAR A COLUNA FIXA */
                position: sticky;
                top: 20px; /* Distância do topo da tela. Aumente se tiver uma barra superior cobrindo. */
                height: max-content; /* Garante que ela só ocupe o espaço necessário e não desça junto */
            }
        }

        /* COLUNA DIREITA E PADRONIZAÇÃO DE WIDGETS */
        .widgets-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        @media (max-width: 1023px) {
            .dashboard-grid { padding: 0 16px; margin-top: 20px; justify-content: center !important; }
            .curriculum-column { width: 100%; flex: auto !important; }
            .widgets-column { width: 100%; max-width: 600px; margin: 0 auto; flex: auto !important; position: static; }
        }

        .widget-box {
            background: #1e1e24;
            border: 2px solid #3a3a45;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 6px 0 #2a2a35;
            color: #fff;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .widget-box:hover {
            border-color: #4a4a58;
        }

        .widget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .widget-header h3 { font-size: 1.15rem; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 8px; }
        .widget-header a {
            color: #1cb0f6; text-decoration: none; font-weight: 700;
            font-size: 0.88rem; text-transform: uppercase; display: flex; align-items: center; gap: 4px;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .widget-header a:hover { color: #58cc02; transform: translateX(2px); }

        /* WIDGET DE VIDAS ZERADAS */
        .widget-vidas {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.08);
            box-shadow: 0 6px 0 rgba(239, 68, 68, 0.3);
        }
        .vidas-body { display: flex; align-items: center; gap: 18px; }
        .vidas-icon { font-size: 40px; color: #ef4444; flex-shrink: 0; animation: pulse 2s infinite; }
        .vidas-info h4 { margin: 0 0 4px 0; font-size: 1.1rem; font-weight: 800; color: #fecaca; }
        .vidas-info p { margin: 0; font-size: 0.9rem; color: #a5a5ac; font-weight: 600; }
        .vidas-info span { color: #ef4444; font-weight: 800; }

        /* WIDGET DE OFENSIVA / SEQUÊNCIA */
        .widget-ofensiva {
            background: linear-gradient(145deg, #221d28 0%, #1a1722 100%);
            border-color: #523528;
            box-shadow: 0 6px 0 #3a2218;
        }
        .ofensiva-body { display: flex; align-items: center; gap: 16px; }
        .ofensiva-icon-box {
            width: 54px; height: 54px; border-radius: 14px;
            display: flex; justify-content: center; align-items: center;
            font-size: 26px; background: rgba(255, 150, 0, 0.15);
            color: #ff9600; border: 1px solid rgba(255, 150, 0, 0.3);
            flex-shrink: 0; box-shadow: 0 0 15px rgba(255, 150, 0, 0.15);
        }
        .ofensiva-info { flex: 1; min-width: 0; }
        .ofensiva-info h4 { margin: 0 0 4px 0; font-size: 1.15rem; font-weight: 800; color: #fff; }
        .ofensiva-status { margin: 0; font-size: 0.85rem; font-weight: 700; line-height: 1.3; }
        .ofensiva-ativo { color: #58cc02; }
        .ofensiva-pendente { color: #ffc800; }

        /* WIDGET DE LIGAS DINÂMICO */
        .ranking-body { display: flex; align-items: center; gap: 16px; }
        .ranking-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; justify-content: center; align-items: center;
            font-size: 26px; color: #fff; flex-shrink: 0;
        }
        .ranking-info { flex: 1; min-width: 0; }
        .ranking-info h4 { margin: 0 0 3px 0; font-size: 1.1rem; font-weight: 800; color: #fff; }
        .ranking-info p { margin: 0 0 6px 0; font-size: 0.9rem; color: #a5a5ac; font-weight: 600; }
        .ranking-info strong { color: #ffc800; font-size: 1.05rem; font-weight: 900; }

        .badge-zona {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.72rem; font-weight: 800; padding: 3px 9px;
            border-radius: 20px; text-transform: uppercase; letter-spacing: 0.4px;
        }
        .badge-sobe { background: rgba(88, 204, 2, 0.15); color: #58cc02; border: 1px solid rgba(88, 204, 2, 0.3); }
        .badge-desce { background: rgba(255, 75, 75, 0.15); color: #ff4b4b; border: 1px solid rgba(255, 75, 75, 0.3); }
        .badge-neutro { background: rgba(142, 142, 161, 0.15); color: #a0a0b0; border: 1px solid rgba(142, 142, 161, 0.3); }

        .widget-footer-info {
            margin-top: 14px; padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex; justify-content: space-between; align-items: center;
            font-size: 0.85rem; color: #a0a0b0; font-weight: 700;
        }

        /* WIDGET DE MISSÕES DIÁRIAS DINÂMICAS */
        .missao-item { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
        .missao-item:last-child { margin-bottom: 0; }
        .missao-icon { font-size: 24px; width: 34px; text-align: center; flex-shrink: 0; }
        .missao-info { flex: 1; min-width: 0; }
        .missao-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; }
        .missao-info h4 { margin: 0; font-size: 0.92rem; font-weight: 700; color: #e5e5e5; }
        
        .badge-concluido {
            font-size: 0.72rem; font-weight: 800; color: #58cc02;
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(88, 204, 2, 0.15); padding: 2px 8px; border-radius: 12px;
        }

        .progress-bar-bg {
            background: #2a2a35; height: 16px; border-radius: 8px;
            width: 100%; position: relative; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .progress-bar-fill {
            height: 100%; border-radius: 8px;
            transition: width 0.5s ease;
        }
        .progress-text {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            display: flex; justify-content: center; align-items: center;
            font-size: 0.72rem; font-weight: 800; color: #fff;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }

        /* WIDGET DE PROGRESSO GERAL */
        .progresso-stats-row {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px;
        }
        .progresso-stat-card {
            background: #25252e; border: 1px solid #333340; border-radius: 12px;
            padding: 10px 12px; text-align: center;
        }
        .progresso-stat-card .val {
            font-size: 1.15rem; font-weight: 900; font-family: 'Orbitron', sans-serif;
            color: #1cb0f6; margin-bottom: 2px;
        }
        .progresso-stat-card .lbl {
            font-size: 0.75rem; color: #8e95a1; font-weight: 700; text-transform: uppercase;
        }

        /* AJUSTES DA TRILHA DE APRENDIZADO */
        .unit-list { 
            display: flex; flex-direction: column; align-items: center;
            gap: 40px; width: 100%; max-width: 800px;
        }

        .capitulo-container {
            width: 100%;
            display: flex; flex-direction: column; align-items: center;
            position: relative;
        }

        .capitulo-header {
            width: 100%; display: flex; justify-content: space-between; align-items: center;
            padding: 24px 30px; border-radius: 20px; color: #fff;
            box-shadow: 0 6px 0px rgba(0,0,0,0.15); margin-bottom: 30px;
            position: relative; z-index: 10;
        }
        .capitulo-header-text h2 { font-size: 1.6rem; font-weight: 800; margin: 0; }
        .capitulo-header-text h3 { font-size: 1.1rem; font-weight: 600; margin: 0; opacity: 0.9; }

        .btn-guia {
            background: transparent; border: 2px solid rgba(255, 255, 255, 0.4);
            border-bottom: 4px solid rgba(255, 255, 255, 0.4); color: #fff;
            padding: 10px 24px; border-radius: 16px; font-weight: 800; font-size: 1rem;
            text-transform: uppercase; cursor: pointer; transition: 0.1s; display: flex; gap: 10px; align-items: center;
        }
        .btn-guia:active { transform: translateY(2px); border-bottom: 2px solid rgba(255, 255, 255, 0.4); }

        .trail-flex {
            display: flex; flex-direction: column; align-items: center;
            width: 100%; position: relative; padding: 20px 0;
        }

        @keyframes floatNode {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        .pos-0 { margin-left: 0px; animation-delay: 0s !important; }
        .pos-1 { margin-left: 120px; animation-delay: 0.4s !important; }
        .pos-2 { margin-left: 160px; animation-delay: 0.8s !important; }
        .pos-3 { margin-right: 120px; animation-delay: 1.2s !important; }
        .pos-4 { margin-left: 0px; animation-delay: 1.6s !important; }

        .modulo-node {
            text-decoration: none; position: relative; z-index: 1; display: flex;
            justify-content: center; align-items: center; margin-bottom: 35px;
            animation: floatNode 3.5s ease-in-out infinite;
        }

        .circle-button {
            width: 75px; height: 75px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 28px; position: relative; transition: 0.1s; color: #fff;
        }
        .modulo-node.locked .circle-button { background: #3a3a45; color: #6a6a75; box-shadow: 0 6px 0 #2a2a35; }
        .modulo-node.completed:active .circle-button, .modulo-node.current:active .circle-button { transform: translateY(4px); }
        
        .active-ring {
            width: 105px; height: 105px; border-radius: 50%; background: #2a2a35;
            display: flex; align-items: center; justify-content: center;
        }

        .start-balloon {
            position: absolute; top: -65px; left: 50%; transform: translateX(-50%);
            color: #fff; padding: 12px 20px; border-radius: 12px; font-weight: 800;
            text-transform: uppercase; animation: bounce 2s ease-in-out infinite;
            white-space: nowrap; z-index: 10;
        }
        .start-balloon::after {
            content: ''; position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%);
            border-width: 8px 8px 0; border-style: solid;
        }
        @keyframes bounce { 0%, 100% { transform: translate(-50%, 0); } 50% { transform: translate(-50%, -8px); } }

        /* BAÚ DE RECOMPENSAS NO MEIO DA TRILHA */
        .reward-chest-container {
            margin: 15px 0 45px 0; display: flex; flex-direction: column; align-items: center;
            position: relative; z-index: 2; animation: floatNode 4s ease-in-out infinite;
        }
        .chest-node {
            width: 84px; height: 74px; border-radius: 18px; display: flex; align-items: center;
            justify-content: center; font-size: 32px; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative; user-select: none;
        }
        .chest-node.locked {
            background: #2a2a35; color: #5a5a68; box-shadow: 0 6px 0 #1e1e28; cursor: pointer;
        }
        .chest-node.locked:hover {
            transform: scale(1.03); color: #707080;
        }
        .chest-node.available {
            background: linear-gradient(145deg, #ffd700, #ff9600);
            color: #fff; box-shadow: 0 8px 0 #cc7000, 0 0 25px rgba(255, 215, 0, 0.6);
            cursor: pointer; animation: chestPulse 1.8s infinite;
        }
        .chest-node.available:hover {
            transform: translateY(-6px) scale(1.08);
            box-shadow: 0 12px 0 #cc7000, 0 0 35px rgba(255, 215, 0, 0.9);
        }
        .chest-node.available:active {
            transform: translateY(4px); box-shadow: 0 2px 0 #cc7000;
        }
        .chest-node.claimed {
            background: #202430; color: #707888; border: 2px solid rgba(88, 204, 2, 0.3);
            box-shadow: 0 6px 0 #151820; cursor: pointer;
        }
        .chest-node.claimed:hover {
            transform: scale(1.03); border-color: rgba(88, 204, 2, 0.6);
        }
        .chest-tag-done {
            position: absolute; bottom: -12px; font-size: 0.65rem; font-weight: 900;
            background: #58cc02; color: #fff; padding: 2px 8px; border-radius: 10px;
            text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(0,0,0,0.4);
            white-space: nowrap;
        }
        .chest-balloon {
            position: absolute; top: -40px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(90deg, #ffd700, #ff9600); color: #1e1e24;
            padding: 6px 14px; border-radius: 12px; font-weight: 900; font-size: 0.75rem;
            text-transform: uppercase; animation: bounce 1.8s ease-in-out infinite;
            white-space: nowrap; z-index: 10; box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .chest-balloon::after {
            content: ''; position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);
            border-width: 6px 6px 0; border-style: solid; border-color: #ff9600 transparent transparent;
        }
        @keyframes chestPulse {
            0%, 100% { filter: drop-shadow(0 0 6px rgba(255, 215, 0, 0.5)); transform: scale(1); }
            50% { filter: drop-shadow(0 0 22px rgba(255, 215, 0, 0.95)); transform: scale(1.04); }
        }

        .logic-hologram { position: absolute; top: -25px; font-size: 20px; color: #58cc02; opacity: 0; }
        .chest-node.available .logic-hologram { opacity: 1; animation: logicFloat 2s infinite alternate; }
        
        @keyframes logicFloat {
            0% { transform: translateY(0) scale(1); text-shadow: 0 0 5px #ffd700; color: #ffd700; }
            100% { transform: translateY(-10px) scale(1.2); text-shadow: 0 0 15px #ffd700; color: #ffd700; }
        }

        .mascote-lateral { position: absolute; width: 140px; z-index: 2; pointer-events: none; animation: floatMascote 4s ease-in-out infinite; top: 35%; }
        .mascote-esquerda { left: -40px; } 
        .mascote-direita { right: -40px; }
        @keyframes floatMascote { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-12px); } }
        @media (max-width: 1024px) { .mascote-lateral { display: none; } }

        /* MODAIS */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.75);
            backdrop-filter: blur(8px); display: none; align-items: center; justify-content: center; z-index: 9999; opacity: 0; transition: 0.3s;
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-box { background: #1e1e24; width: 90%; max-width: 500px; border-radius: 20px; overflow: hidden; transform: scale(0.9); transition: 0.3s; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .modal-overlay.active .modal-box { transform: scale(1); }
        .modal-header { padding: 25px; color: #fff; position: relative; }
        .modal-header h2 { margin: 0; font-size: 1.8rem; font-weight: 900; }
        .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; opacity: 0.9; }
        .close-btn-modal { position: absolute; top: 20px; right: 20px; background: rgba(0,0,0,0.2); border: none; color: #fff; width: 35px; height: 35px; border-radius: 50%; font-size: 1.2rem; cursor: pointer; transition: 0.2s; }
        .close-btn-modal:hover { background: rgba(0,0,0,0.4); transform: scale(1.1); }
        .modal-body { padding: 30px; color: #d0d0d5; font-size: 1.1rem; line-height: 1.6; }
        .code-box { background: #111115; border-left: 4px solid #1cb0f6; padding: 15px; border-radius: 8px; font-family: monospace; margin-top: 15px; color: #a5d6a7; }

        /* MODAL DO BAÚ DE RECOMPENSAS */
        .modal-bau-box {
            background: #181824; width: 90%; max-width: 480px; border-radius: 24px;
            overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.85);
            border: 2px solid #3a3a4c; text-align: center; padding: 35px 28px;
            position: relative; transform: scale(0.85); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.active .modal-bau-box { transform: scale(1); }
        .modal-bau-icon-wrapper {
            width: 110px; height: 110px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.25) 0%, rgba(255, 150, 0, 0.05) 70%);
            border: 3px solid #ffd700; display: flex; justify-content: center; align-items: center;
            font-size: 50px; color: #ffd700; margin: 0 auto 20px auto;
            box-shadow: 0 0 35px rgba(255, 215, 0, 0.45);
            animation: pulse 2s infinite;
        }
        .modal-bau-title { font-family: 'Orbitron', sans-serif; font-size: 1.6rem; font-weight: 900; color: #fff; margin-bottom: 6px; }
        .modal-bau-sub { font-size: 0.95rem; color: #a5a5ac; font-weight: 600; margin-bottom: 25px; }

        .reward-cards-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 25px;
        }
        .reward-card {
            background: #222230; border: 1px solid #38384a; border-radius: 16px;
            padding: 16px 12px; display: flex; flex-direction: column; align-items: center; gap: 6px;
            transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        .reward-card:hover { transform: translateY(-3px); border-color: #ffd700; }
        .reward-card .rc-icon { font-size: 30px; margin-bottom: 2px; }
        .reward-card .rc-val { font-size: 1.2rem; font-weight: 900; color: #fff; font-family: 'Orbitron', sans-serif; }
        .reward-card .rc-lbl { font-size: 0.75rem; color: #8e95a1; font-weight: 700; text-transform: uppercase; }

        .btn-claim-chest {
            background: #58cc02; border: none; border-bottom: 5px solid #45a300;
            color: #fff; width: 100%; padding: 14px 20px; border-radius: 16px;
            font-size: 1.05rem; font-weight: 900; text-transform: uppercase;
            letter-spacing: 0.8px; cursor: pointer; transition: 0.1s;
            box-shadow: 0 4px 15px rgba(88, 204, 2, 0.4);
        }
        .btn-claim-chest:hover { filter: brightness(1.1); transform: translateY(-2px); }
        .btn-claim-chest:active { transform: translateY(4px); border-bottom: 2px solid #45a300; }
        .btn-claim-chest:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .code-box { background: #111115; border-left: 4px solid #1cb0f6; padding: 15px; border-radius: 8px; font-family: monospace; margin-top: 15px; color: #a5d6a7; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include '../../back/sidebar.php'; ?>

        <main class="main-content">
            <?php include '../../back/topbar.php'; ?>

            <section class="dashboard-grid">
                
                <!-- COLUNA DA TRILHA DE APRENDIZADO -->
                <div class="curriculum-column">
                    <div class="unit-list">
                        <?php foreach ($nomes_unidades as $num_cap => $info): 
                            $status_cap_banco = $unidades[$num_cap]['status'] ?? 'trancado';
                            $licoes_feitas = $unidades[$num_cap]['licoes_concluidas'] ?? 0;
                            $cor_unidade = $info['cor'];
                            $cor_sombra = "rgba(0, 0, 0, 0.25)"; 
                            
                            $posicao_mascote = ($num_cap % 2 != 0) ? 'mascote-esquerda' : 'mascote-direita';
                            $imagem_mascote = $info['mascote'] ?? 'vistodecimaazul.png';
                        ?>
                            <div class="capitulo-container">
                                <div class="capitulo-header" style="background-color: <?php echo $cor_unidade; ?>;">
                                    <div class="capitulo-header-text">
                                        <h2><?php echo $info['titulo']; ?></h2>
                                        <h3><?php echo $info['nome']; ?></h3>
                                    </div>
                                    <button class="btn-guia" 
                                            data-titulo="<?php echo htmlspecialchars($info['titulo']); ?>" 
                                            data-nome="<?php echo htmlspecialchars($info['nome']); ?>" 
                                            data-cor="<?php echo $cor_unidade; ?>" 
                                            data-texto="<?php echo htmlspecialchars($info['guia_texto']); ?>" 
                                            data-codigo="<?php echo htmlspecialchars($info['guia_codigo']); ?>" 
                                            onclick="abrirGuia(this)">
                                        <i class="fa-solid fa-book"></i> Guia
                                    </button>
                                </div>
                                
                                <div class="trail-flex">
                                    <?php for ($mod = 1; $mod <= 5; $mod++): 
                                        $classe_modulo = "locked";
                                        $icone_modulo = "fa-star";
                                        $is_clicavel = false;
                                        $is_current = false;

                                        if ($status_cap_banco === 'completo') {
                                            $classe_modulo = "completed";
                                            $is_clicavel = true;
                                        } elseif ($status_cap_banco === 'corrente') {
                                            if ($mod <= $licoes_feitas) {
                                                $classe_modulo = "completed";
                                                $is_clicavel = true;
                                            } elseif ($mod == $licoes_feitas + 1) {
                                                $classe_modulo = "current";
                                                $is_clicavel = true;
                                                $is_current = true;
                                            }
                                        }
                                        
                                        $url_destino = "licao.php?cap=" . $num_cap . "&licao=" . $mod;
                                        $pos_class = "pos-" . (($mod - 1) % 5);
                                        if ($vidas_atual <= 0) {
                                            $url_destino = "dashboard.php?sem_vidas=1";
                                        }
                                    ?>
                                        
                                        <?php if ($is_current): ?>
                                            <a href="<?php echo $url_destino; ?>" class="modulo-node current <?php echo $pos_class; ?>">
                                                <div class="start-balloon" style="background-color: <?php echo $cor_unidade; ?>;">
                                                    <?php echo $vidas_atual <= 0 ? 'SEM VIDAS' : 'COMEÇAR'; ?>
                                                    <style>.start-balloon::after { border-top-color: <?php echo $cor_unidade; ?> !important; }</style>
                                                </div>
                                                <div class="active-ring">
                                                    <div class="circle-button" style="background-color: <?php echo $cor_unidade; ?>; box-shadow: 0 6px 0 <?php echo $cor_sombra; ?>;">
                                                        <i class="fa-solid <?php echo $icone_modulo; ?>"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        <?php elseif ($is_clicavel): ?>
                                            <a href="<?php echo $url_destino; ?>" class="modulo-node completed <?php echo $pos_class; ?>">
                                                <div class="circle-button" style="background-color: <?php echo $cor_unidade; ?>; box-shadow: 0 6px 0 <?php echo $cor_sombra; ?>;">
                                                    <i class="fa-solid fa-check"></i>
                                                </div>
                                            </a>
                                        <?php else: ?>
                                            <div class="modulo-node locked <?php echo $pos_class; ?>">
                                                <div class="circle-button">
                                                    <i class="fa-solid fa-lock"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php 
                                        if ($mod == 3): 
                                            $is_elegivel_bau = ($licoes_feitas >= 3 || $status_cap_banco === 'completo');
                                            $is_resgatado_bau = in_array($num_cap, $baus_resgatados, true);

                                            if ($is_resgatado_bau) {
                                                $chest_state = "claimed";
                                            } elseif ($is_elegivel_bau) {
                                                $chest_state = "available";
                                            } else {
                                                $chest_state = "locked";
                                            }
                                        ?>
                                            <div class="reward-chest-container <?php echo $pos_class; ?>" style="margin-left: 0; margin-right: 80px;">
                                                <?php if ($chest_state === 'available'): ?>
                                                    <div class="chest-balloon"><i class="fa-solid fa-gift"></i> ABRIR BAÚ!</div>
                                                    <div class="chest-node available" id="chestNode_<?php echo $num_cap; ?>" onclick="abrirModalBau(<?php echo $num_cap; ?>, '<?php echo htmlspecialchars($info['titulo']); ?>')">
                                                        <i class="fa-solid fa-gift"></i>
                                                        <i class="fa-solid fa-sparkles logic-hologram"></i>
                                                    </div>
                                                <?php elseif ($chest_state === 'claimed'): ?>
                                                    <div class="chest-node claimed" id="chestNode_<?php echo $num_cap; ?>" onclick="avisoBauColetado(<?php echo $num_cap; ?>)">
                                                        <i class="fa-solid fa-box-open"></i>
                                                        <span class="chest-tag-done"><i class="fa-solid fa-check"></i> Coletado</span>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="chest-node locked" id="chestNode_<?php echo $num_cap; ?>" onclick="avisoBauTrancado(<?php echo $num_cap; ?>)">
                                                        <i class="fa-solid fa-lock"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                    <?php endfor; ?>

                                    <img src="../assets/img/<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-lateral <?php echo $posicao_mascote; ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLUNA DIREITA (WIDGETS) -->
                <div class="widgets-column">
                    
                    <?php if ($vidas_atual <= 0): ?>
                    <!-- WIDGET DE VIDAS ZERADAS -->
                    <div class="widget-box widget-vidas">
                        <div class="widget-header">
                            <h3 style="color: #fecaca;"><i class="fa-solid fa-heart-crack"></i> Vidas Esgotadas</h3>
                        </div>
                        <div class="vidas-body">
                            <div class="vidas-icon">
                                <i class="fa-solid fa-heart-crack"></i>
                            </div>
                            <div class="vidas-info">
                                <h4>Você ficou sem vidas!</h4>
                                <p>Próxima vida em: <span>
                                    <?php echo !empty($status_jogador['proxima_vida_texto']) ? htmlspecialchars($status_jogador['proxima_vida_texto']) : '05:00'; ?>
                                </span></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- WIDGET DE OFENSIVA / SEQUÊNCIA DIÁRIA -->
                    <div class="widget-box widget-ofensiva">
                        <div class="widget-header">
                            <h3><i class="fa-solid fa-fire-flame-curved" style="color: #ff9600;"></i> Ofensiva</h3>
                            <span style="font-size: 0.85rem; color: #ff9600; font-weight: 800;">
                                <?php echo $dias_fogo; ?> <?= $dias_fogo === 1 ? 'dia' : 'dias'; ?>
                            </span>
                        </div>
                        <div class="ofensiva-body">
                            <div class="ofensiva-icon-box">
                                <i class="fa-solid fa-fire"></i>
                            </div>
                            <div class="ofensiva-info">
                                <h4><?php echo $dias_fogo; ?> <?= $dias_fogo === 1 ? 'Dia de Fogo' : 'Dias de Fogo'; ?></h4>
                                <?php if ($praticou_hoje): ?>
                                    <p class="ofensiva-status ofensiva-ativo">
                                        <i class="fa-solid fa-circle-check"></i> Praticou hoje! Chama protegida.
                                    </p>
                                <?php else: ?>
                                    <p class="ofensiva-status ofensiva-pendente">
                                        <i class="fa-solid fa-hourglass-half"></i> Pratique hoje para manter sua sequência!
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- WIDGET DE LIGAS COM DADOS REAIS -->
                    <div class="widget-box">
                        <div class="widget-header">
                            <h3><i class="fa-solid fa-shield-halved" style="color: <?php echo $cfg_liga['corClara']; ?>;"></i> Ligas</h3>
                            <a href="ligas.php">Ver Placar <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i></a>
                        </div>
                        <div class="ranking-body">
                            <div class="ranking-icon" style="background: linear-gradient(135deg, <?php echo $cfg_liga['corClara']; ?>, <?php echo $cfg_liga['cor']; ?>); box-shadow: 0 4px 14px <?php echo $cfg_liga['cor']; ?>50;">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div class="ranking-info">
                                <h4>Divisão <?php echo htmlspecialchars($cfg_liga['nome']); ?></h4>
                                <p>Sua posição: <strong>#<?php echo $posicao_usuario; ?></strong> de <?php echo $total_grupo; ?> alunos</p>
                                
                                <?php if ($minha_zona === 'sobe'): ?>
                                    <span class="badge-zona badge-sobe"><i class="fa-solid fa-arrow-up"></i> Zona de Subida</span>
                                <?php elseif ($minha_zona === 'desce'): ?>
                                    <span class="badge-zona badge-desce"><i class="fa-solid fa-arrow-down"></i> Zona de Rebaixamento</span>
                                <?php else: ?>
                                    <span class="badge-zona badge-neutro"><i class="fa-solid fa-shield"></i> Zona Segura</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="widget-footer-info">
                            <span><i class="fa-solid fa-bolt" style="color: #ffc800;"></i> <strong><?php echo number_format($xp_semana_usuario, 0, ',', '.'); ?> XP</strong> nesta semana</span>
                            <span style="color: #8e95a1; font-size: 0.8rem;">Divisão <?php echo htmlspecialchars($cfg_liga['nome']); ?></span>
                        </div>
                    </div>

                    <!-- WIDGET DE MISSÕES DIÁRIAS COM DADOS REAIS -->
                    <div class="widget-box">
                        <div class="widget-header">
                            <h3><i class="fa-solid fa-award" style="color: #ffc800;"></i> Missões Diárias</h3>
                            <a href="conquistas.php">Ver Tudo <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i></a>
                        </div>
                        
                        <?php foreach ($missoes_hoje as $m): ?>
                        <div class="missao-item">
                            <div class="missao-icon" style="color: <?php echo $m['cor']; ?>;">
                                <i class="fa-solid <?php echo $m['icone']; ?>"></i>
                            </div>
                            <div class="missao-info">
                                <div class="missao-header-row">
                                    <h4><?php echo htmlspecialchars($m['titulo']); ?></h4>
                                    <?php if ($m['completo']): ?>
                                        <span class="badge-concluido"><i class="fa-solid fa-circle-check"></i> Feito</span>
                                    <?php endif; ?>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill" style="width: <?php echo $m['porcentagem']; ?>%; background: <?php echo $m['cor']; ?>;"></div>
                                    <div class="progress-text">
                                        <?php echo min($m['atual'], $m['meta']); ?> / <?php echo $m['meta']; ?><?php echo !empty($m['unidade']) ? ' ' . $m['unidade'] : ''; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div> <!-- Fim Widget de Missões -->

                    <!-- WIDGET DE PROGRESSO GERAL DO ALUNO -->
                    <div class="widget-box">
                        <div class="widget-header">
                            <h3><i class="fa-solid fa-bars-progress" style="color: #1cb0f6;"></i> Meu Progresso</h3>
                            <a href="perfil.php">Perfil <i class="fa-solid fa-chevron-right" style="font-size: 0.75rem;"></i></a>
                        </div>
                        
                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; font-weight: 700; margin-bottom: 6px; color: #a5a5ac;">
                                <span>Trilha do Curso</span>
                                <span style="color: #1cb0f6; font-weight: 800;"><?php echo $progresso_porcentagem; ?>%</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?php echo $progresso_porcentagem; ?>%; background: #1cb0f6;"></div>
                                <div class="progress-text"><?php echo $total_licoes_feitas; ?> / <?php echo $total_licoes_curso; ?> Lições</div>
                            </div>
                        </div>

                        <div class="progresso-stats-row">
                            <div class="progresso-stat-card">
                                <div class="val" style="color: #ffc800;"><?php echo number_format($xp_total, 0, ',', '.'); ?></div>
                                <div class="lbl">XP Total</div>
                            </div>
                            <div class="progresso-stat-card">
                                <div class="val" style="color: #eab308;"><?php echo $total_trofeus; ?> / 8</div>
                                <div class="lbl">Troféus</div>
                            </div>
                        </div>
                    </div>

                </div> <!-- Fim widgets-column -->

            </section>
        </main>
    </div>

    <!-- Modal do Guia -->
    <div id="modalGuia" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header" id="modalHeaderBg">
                <button class="close-btn-modal" onclick="fecharGuia()"><i class="fa-solid fa-xmark"></i></button>
                <h2 id="modalUnidadeTitle">Unidade X</h2>
                <h3 id="modalUnidadeSub">Nome da Unidade</h3>
            </div>
            <div class="modal-body">
                <p id="modalGuiaTexto">Resumo da unidade...</p>
                <p><strong>Exemplo de código:</strong></p>
                <div class="code-box" id="modalCodeBox">
                    // Exemplo
                </div>
            </div>
        </div>
    </div>

    <!-- Modal do Baú de Recompensas -->
    <div id="modalBauRecompensa" class="modal-overlay">
        <div class="modal-bau-box">
            <button class="close-btn-modal" onclick="fecharModalBau()"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-bau-icon-wrapper">
                <i class="fa-solid fa-gift" id="modalBauIconePrincipal"></i>
            </div>
            <h2 class="modal-bau-title" id="modalBauTitle">Baú de Recompensas!</h2>
            <p class="modal-bau-sub" id="modalBauSub">Unidade X · Recompensa de Meio de Trilha</p>

            <div id="rewardContainerPre">
                <p style="color: #d0d0d8; font-size: 1rem; line-height: 1.5; margin-bottom: 25px;">
                    Você atingiu a metade desta unidade! Abra este baú misterioso para resgatar <strong>XP Bônus</strong> e <strong>Corações de Vida</strong> para continuar sua jornada.
                </p>
            </div>

            <div id="rewardContainerPos" style="display: none;">
                <div class="reward-cards-grid">
                    <div class="reward-card">
                        <i class="fa-solid fa-bolt rc-icon" style="color: #ffc800;"></i>
                        <div class="rc-val" id="resgateXpVal">+50 XP</div>
                        <div class="rc-lbl">Bônus de XP</div>
                    </div>
                    <div class="reward-card" id="resgateVidaCard">
                        <i class="fa-solid fa-heart rc-icon" id="resgateVidaIcon" style="color: #ef4444;"></i>
                        <div class="rc-val" id="resgateVidaVal">+1 Coração</div>
                        <div class="rc-lbl" id="resgateVidaLbl">Vida Bônus</div>
                    </div>
                </div>
                <p id="resgateDetalheTxt" style="color: #58cc02; font-weight: 700; font-size: 0.95rem; margin-bottom: 20px;"></p>
            </div>

            <button class="btn-claim-chest" id="btnResgatarBau" onclick="resgatarRecompensaBau()">
                <i class="fa-solid fa-box-open"></i> ABRIR BAÚ DE RECOMPENSA
            </button>
            <button class="btn-claim-chest" id="btnFecharBauPronto" onclick="fecharModalBau()" style="display: none; background: #1cb0f6; border-bottom-color: #148bc4;">
                <i class="fa-solid fa-check"></i> CONTINUAR APRENDENDO
            </button>
        </div>
    </div>

    <script src="../assets/js/script.js"></script> 
    
    <script>
        let capAtualBau = 0;

        function abrirModalBau(cap, titulo) {
            capAtualBau = cap;
            document.getElementById('modalBauTitle').innerText = 'Baú de Recompensas!';
            document.getElementById('modalBauSub').innerText = titulo + ' · Recompensa de Meio de Trilha';
            document.getElementById('rewardContainerPre').style.display = 'block';
            document.getElementById('rewardContainerPos').style.display = 'none';
            document.getElementById('btnResgatarBau').style.display = 'block';
            document.getElementById('btnResgatarBau').innerHTML = '<i class="fa-solid fa-box-open"></i> ABRIR BAÚ DE RECOMPENSA';
            document.getElementById('btnResgatarBau').disabled = false;
            document.getElementById('btnFecharBauPronto').style.display = 'none';
            
            const modal = document.getElementById('modalBauRecompensa');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function fecharModalBau() {
            const modal = document.getElementById('modalBauRecompensa');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function avisoBauColetado(cap) {
            alert('Você já abriu e coletou a recompensa deste baú da Unidade ' + cap + '!');
        }

        function avisoBauTrancado(cap) {
            alert('🔒 Complete pelo menos 3 lições da Unidade ' + cap + ' para desbloquear e abrir este baú de recompensa!');
        }

        function resgatarRecompensaBau() {
            const btn = document.getElementById('btnResgatarBau');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Abrindo Baú...';

            fetch('../../back/resgatar_bau.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'unidade_numero=' + encodeURIComponent(capAtualBau)
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert(data.mensagem || 'Não foi possível resgatar o baú.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> TENTAR NOVAMENTE';
                    return;
                }

                // Exibe resultados
                document.getElementById('rewardContainerPre').style.display = 'none';
                document.getElementById('rewardContainerPos').style.display = 'block';
                document.getElementById('resgateXpVal').innerText = '+' + data.xp_ganho + ' XP';
                
                if (data.vidas_ganhas > 0) {
                    document.getElementById('resgateVidaVal').innerText = '+1 Coração';
                    document.getElementById('resgateVidaLbl').innerText = 'Vida Recuperada';
                    document.getElementById('resgateVidaIcon').className = 'fa-solid fa-heart rc-icon';
                    document.getElementById('resgateVidaIcon').style.color = '#ef4444';
                } else {
                    document.getElementById('resgateVidaVal').innerText = 'XP Bônus Max';
                    document.getElementById('resgateVidaLbl').innerText = 'Vidas já cheias';
                    document.getElementById('resgateVidaIcon').className = 'fa-solid fa-star rc-icon';
                    document.getElementById('resgateVidaIcon').style.color = '#ffc800';
                }

                document.getElementById('resgateDetalheTxt').innerText = data.detalhe || data.mensagem;
                btn.style.display = 'none';
                document.getElementById('btnFecharBauPronto').style.display = 'block';

                // Atualiza o nó do baú na trilha
                const chestNode = document.getElementById('chestNode_' + capAtualBau);
                if (chestNode) {
                    chestNode.className = 'chest-node claimed';
                    chestNode.onclick = function() { avisoBauColetado(capAtualBau); };
                    chestNode.innerHTML = '<i class="fa-solid fa-box-open"></i><span class="chest-tag-done"><i class="fa-solid fa-check"></i> Coletado</span>';
                    const balloon = chestNode.parentElement.querySelector('.chest-balloon');
                    if (balloon) balloon.remove();
                }

                // Atualiza Topbar Vidas e XP em tempo real
                const topbarXp = document.querySelector('.topbar-stat.stat-xp span');
                if (topbarXp && data.xp_total) {
                    topbarXp.innerText = Number(data.xp_total).toLocaleString('pt-BR');
                }
                const topbarVida = document.querySelector('.topbar-stat.stat-vida span');
                if (topbarVida && typeof data.vidas_atual !== 'undefined') {
                    topbarVida.innerText = data.vidas_atual;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erro de conexão ao abrir o baú.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-box-open"></i> ABRIR BAÚ DE RECOMPENSA';
            });
        }

        function abrirGuia(btn) {
            const titulo = btn.getAttribute('data-titulo');
            const nome = btn.getAttribute('data-nome');
            const cor = btn.getAttribute('data-cor');
            const texto = btn.getAttribute('data-texto');
            const codigo = btn.getAttribute('data-codigo');

            document.getElementById('modalUnidadeTitle').innerText = titulo;
            document.getElementById('modalUnidadeSub').innerText = nome;
            document.getElementById('modalHeaderBg').style.backgroundColor = cor;
            document.getElementById('modalCodeBox').style.borderLeftColor = cor;
            
            document.getElementById('modalGuiaTexto').innerText = texto;
            document.getElementById('modalCodeBox').innerHTML = codigo;
            
            const modal = document.getElementById('modalGuia');
            modal.style.display = 'flex';
            
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        function fecharGuia() {
            const modal = document.getElementById('modalGuia');
            modal.classList.remove('active');
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        window.onclick = function(event) {
            const modalG = document.getElementById('modalGuia');
            if (event.target == modalG) {
                fecharGuia();
            }
            const modalB = document.getElementById('modalBauRecompensa');
            if (event.target == modalB) {
                fecharModalBau();
            }
        }
    </script>
</body>
</html>