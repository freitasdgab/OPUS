<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../../back/config/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador não pode jogar: redireciona para o Painel Administrativo
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
    exit();
}

require_once '../../back/lib/jogador_status.php';
require_once '../../back/lib/ligas_logic.php';
require_once '../../back/lib/missoes_logic.php';
require_once '../../back/lib/conteudo_logic.php';
require_once '../../back/includes/bau_svg.php';

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
$praticou_hoje = !empty($status_jogador['fogo_hoje']);

// 4. PROGRESSO GERAL DO CURSO
$total_licoes_feitas = 0;
foreach ($unidades as $u) {
    if (($u['status'] ?? '') === 'completo') {
        $total_licoes_feitas += 5;
    } else {
        $total_licoes_feitas += (int) ($u['licoes_concluidas'] ?? 0);
    }
}
$total_licoes_curso = 200;
$progresso_porcentagem = min(100, (int) round(($total_licoes_feitas / $total_licoes_curso) * 100));

// 5. BAÚS DE RECOMPENSA RESGATADOS PELO USUÁRIO
$baus_resgatados = [];
$res_baus = $conn->query("SELECT unidade_numero FROM bau_recompensas WHERE usuario_id = $user_id");
if ($res_baus) {
    while ($rb = $res_baus->fetch_assoc()) {
        $baus_resgatados[] = (int) $rb['unidade_numero'];
    }
}

// Conteúdo da trilha (seções e divisões) vem do banco: tabelas `unidades` e `divisoes`
$nomes_unidades = conteudo_unidades($conn);
$divisoes = conteudo_divisoes($conn);

// Identifica a seção corrente do aluno
$capitulo_corrente_usuario = 1;
foreach ($nomes_unidades as $num_cap => $info) {
    $st = $unidades[$num_cap]['status'] ?? 'trancado';
    if ($st === 'current' || $st === 'corrente') {
        $capitulo_corrente_usuario = $num_cap;
        break;
    }
}

// Determina qual divisão o usuário está cursando atualmente
$divisao_ativa_padrao = conteudo_divisao_da_secao($divisoes, $capitulo_corrente_usuario);

// Estatísticas e progresso de cada divisão
foreach ($divisoes as $d_id => &$d_info) {
    $total_secoes_div = ($d_info['secao_fim'] - $d_info['secao_inicio'] + 1);
    $concluidas_div = 0;
    for ($c = $d_info['secao_inicio']; $c <= $d_info['secao_fim']; $c++) {
        $st = $unidades[$c]['status'] ?? 'trancado';
        if ($st === 'completo') {
            $concluidas_div++;
        }
    }
    $d_info['total_secoes'] = $total_secoes_div;
    $d_info['concluidas'] = $concluidas_div;
    $d_info['porcentagem'] = round(($concluidas_div / $total_secoes_div) * 100);
    $d_info['is_completa'] = ($concluidas_div >= $total_secoes_div);
    $d_info['is_atual'] = ($divisao_ativa_padrao === $d_id);
}
unset($d_info);

$div_ativa_info = $divisoes[$divisao_ativa_padrao] ?? [
    'id' => 1,
    'nome' => 'Divisão 1',
    'tema' => 'Fundamentos de Java',
    'secao_inicio' => 1,
    'secao_fim' => 15,
    'concluidas' => 0,
    'total_secoes' => 15,
    'porcentagem' => 0,
];
$pct_div_ativa_hero = (int) ($div_ativa_info['porcentagem'] ?? 0);

$ilhas_apelidos = [
    1 => 'Ilha dos Fundamentos',
    2 => 'Ilha do Código',
    3 => 'Ilha dos Desafios',
];
$ilha_ativa_nome = $ilhas_apelidos[$divisao_ativa_padrao] ?? 'Ilha dos Fundamentos';

// Elementos minimalistas e orgânicos que remetem à ilha ao longo da trilha (alternam por seção)
function opus_render_island_motif(int $num_cap, int $divisao): string
{
    $lado = ($num_cap % 2 !== 0) ? 'motif-right' : 'motif-left';
    $variante = ($num_cap - 1) % 4;

    if ($variante === 0) {
        // Variante 1: Palmeira orgânica minimalista sobre banco de areia + brisa costeira
        return '<div class="trail-island-motif ' . $lado . ' motif-palm" aria-hidden="true">'
            . '<svg viewBox="0 0 140 160" fill="none" xmlns="http://www.w3.org/2000/svg">'
            . '<ellipse cx="72" cy="138" rx="46" ry="10" class="im-sand" />'
            . '<path d="M22 146 Q48 140 72 146 T122 146" class="im-wave" stroke-width="2" stroke-linecap="round" />'
            . '<path d="M40 154 Q66 149 98 154" class="im-wave-soft" stroke-width="1.6" stroke-linecap="round" />'
            . '<g class="im-palm-sway">'
            . '<path d="M72 135 Q67 98 78 58" class="im-trunk" stroke-width="4.2" stroke-linecap="round" />'
            . '<path d="M78 58 Q50 42 28 60 Q54 55 78 58 Z" class="im-leaf" />'
            . '<path d="M78 58 Q56 26 38 30 Q62 40 78 58 Z" class="im-leaf-soft" />'
            . '<path d="M78 58 Q90 24 114 34 Q96 42 78 58 Z" class="im-leaf" />'
            . '<path d="M78 58 Q108 46 124 68 Q102 58 78 58 Z" class="im-leaf-soft" />'
            . '<path d="M78 58 Q74 30 78 18 Q84 36 78 58 Z" class="im-leaf-soft" />'
            . '</g>'
            . '<ellipse cx="94" cy="133" rx="9" ry="5" class="im-pebble" />'
            . '<ellipse cx="52" cy="135" rx="6" ry="3.5" class="im-pebble-soft" />'
            . '</svg>'
            . '</div>';
    }

    if ($variante === 1) {
        // Variante 2: Barquinho à vela minimalista deslizando em águas calmas
        return '<div class="trail-island-motif ' . $lado . ' motif-boat" aria-hidden="true">'
            . '<svg viewBox="0 0 140 130" fill="none" xmlns="http://www.w3.org/2000/svg">'
            . '<path d="M18 32 Q25 26 32 32 Q39 26 46 32" class="im-bird" stroke-width="1.8" stroke-linecap="round" />'
            . '<path d="M98 24 Q103 20 108 24 Q113 20 118 24" class="im-bird" stroke-width="1.5" stroke-linecap="round" />'
            . '<g class="im-boat-float">'
            . '<line x1="70" y1="30" x2="70" y2="86" class="im-trunk" stroke-width="2.4" stroke-linecap="round" />'
            . '<path d="M72 34 Q99 52 96 82 L72 82 Z" class="im-sail" />'
            . '<path d="M67 42 Q47 57 49 82 L67 82 Z" class="im-sail-soft" />'
            . '<path d="M36 86 L104 86 Q96 99 70 99 Q44 99 36 86 Z" class="im-hull" />'
            . '</g>'
            . '<path d="M16 105 Q44 98 70 105 T124 105" class="im-wave" stroke-width="2.2" stroke-linecap="round" />'
            . '<path d="M32 115 Q58 110 86 115 T114 115" class="im-wave-soft" stroke-width="1.6" stroke-linecap="round" />'
            . '</svg>'
            . '</div>';
    }

    if ($variante === 2) {
        // Variante 3: Seixos costeiros, folhagem tropical orgânica e ondulação da lagoa
        return '<div class="trail-island-motif ' . $lado . ' motif-stones" aria-hidden="true">'
            . '<svg viewBox="0 0 140 130" fill="none" xmlns="http://www.w3.org/2000/svg">'
            . '<ellipse cx="70" cy="100" rx="50" ry="14" class="im-lagoon-ring" />'
            . '<g class="im-palm-sway">'
            . '<path d="M52 92 Q42 62 24 54 Q46 54 58 76 Z" class="im-leaf-soft" />'
            . '<path d="M60 90 Q58 54 46 34 Q66 48 68 82 Z" class="im-leaf" />'
            . '<path d="M66 90 Q78 58 98 46 Q82 68 72 90 Z" class="im-leaf-soft" />'
            . '</g>'
            . '<path d="M40 96 C40 82, 76 80, 82 95 C84 101, 40 103, 40 96 Z" class="im-pebble" />'
            . '<path d="M74 98 C76 88, 102 88, 104 98 C105 103, 73 103, 74 98 Z" class="im-pebble-soft" />'
            . '<circle cx="108" cy="70" r="3" class="im-dot" />'
            . '<path d="M20 114 Q52 108 82 114 T122 114" class="im-wave-soft" stroke-width="1.8" stroke-linecap="round" />'
            . '</svg>'
            . '</div>';
    }

    // Variante 4: Ilhota minimalista com farol sereno / bússola costeira
    return '<div class="trail-island-motif ' . $lado . ' motif-islet" aria-hidden="true">'
        . '<svg viewBox="0 0 140 150" fill="none" xmlns="http://www.w3.org/2000/svg">'
        . '<circle cx="70" cy="40" r="18" class="im-beacon-glow" />'
        . '<path d="M26 116 C34 98, 106 98, 114 116 C100 123, 40 123, 26 116 Z" class="im-sand" />'
        . '<path d="M63 106 L66 52 L74 52 L77 106 Z" class="im-sail" />'
        . '<rect x="64" y="44" width="12" height="8" rx="3" class="im-hull" />'
        . '<path d="M63 44 L70 35 L77 44 Z" class="im-leaf" />'
        . '<circle cx="70" cy="48" r="2.2" class="im-dot" />'
        . '<path d="M44 108 Q40 92 34 86" class="im-trunk" stroke-width="2.2" stroke-linecap="round" />'
        . '<path d="M34 86 Q24 82 18 89 Q28 88 34 86 Z" class="im-leaf-soft" />'
        . '<path d="M34 86 Q34 74 44 76 Q38 82 34 86 Z" class="im-leaf" />'
        . '<path d="M16 128 Q46 122 72 128 T124 128" class="im-wave" stroke-width="2" stroke-linecap="round" />'
        . '<path d="M34 138 Q62 133 94 138" class="im-wave-soft" stroke-width="1.5" stroke-linecap="round" />'
        . '</svg>'
        . '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Progresso - Opus</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Orbitron:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/topbar.css?v=<?= time() ?>">
    <link rel="icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    <link rel="shortcut icon" type="image/png" href="../assets/img/LOGO.png?v=2">
    
    <link rel="stylesheet" href="../assets/css/dashboard-page.css?v=<?= time() ?>">
    <link rel="stylesheet" href="../assets/css/spotify_widget.css?v=<?= time() ?>">
</head>
<body class="dashboard-organic" data-active-island="<?php echo (int) $divisao_ativa_padrao; ?>">
    <div class="app-container">
        <?php include '../../back/includes/sidebar.php'; ?>

        <main class="main-content">
            <!-- ATMOSFERA ORGÂNICA E TOPOGRAFIA DA ILHA EM TELA INTEIRA -->
            <div class="island-atmosphere-bg" aria-hidden="true">
                <svg class="island-topo-svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Curvas topográficas orgânicas de ilha (esquerda) -->
                    <path class="topo-line" d="M-80 260 C60 190, 210 210, 280 310 C340 395, 290 520, 170 570 C50 620, -60 540, -110 430 Z" />
                    <path class="topo-line soft" d="M-40 300 C70 245, 185 260, 235 335 C280 405, 240 495, 150 530 C60 565, -25 505, -60 420 Z" />
                    <path class="topo-line faint" d="M10 345 C85 310, 155 320, 185 365 C215 410, 188 465, 130 485 C72 505, 18 468, -5 415 Z" />

                    <!-- Curvas topográficas orgânicas (direita / horizonte) -->
                    <path class="topo-line" d="M1180 60 C1310 10, 1460 65, 1520 180 C1575 285, 1505 425, 1370 465 C1235 505, 1110 415, 1095 290 C1080 175, 1115 85, 1180 60 Z" />
                    <path class="topo-line soft" d="M1225 115 C1325 78, 1435 120, 1475 205 C1515 285, 1465 385, 1365 415 C1265 445, 1175 378, 1165 285 C1155 200, 1180 132, 1225 115 Z" />

                    <!-- Ondas suaves do mar no horizonte -->
                    <path class="topo-wave" d="M180 760 Q280 732 380 760 T580 760 T780 760" />
                    <path class="topo-wave soft" d="M620 825 Q730 798 840 825 T1060 825 T1280 825" />
                    <path class="topo-wave soft" d="M420 130 Q510 112 600 130 T780 130" />

                    <!-- Gaivotas minimalistas no horizonte -->
                    <path class="topo-bird" d="M470 92 Q480 83 490 92 Q500 83 510 92" />
                    <path class="topo-bird" d="M528 74 Q535 67 542 74 Q549 67 556 74" />
                    <path class="topo-bird" d="M980 154 Q989 146 998 154 Q1007 146 1016 154" />

                    <!-- Rosa dos ventos minimalista (marca d'água sutil) -->
                    <g class="topo-compass" transform="translate(1060, 680)">
                        <circle cx="0" cy="0" r="54" />
                        <circle cx="0" cy="0" r="38" stroke-dasharray="3 7" />
                        <path d="M0 -64 L7 -9 L64 0 L7 9 L0 64 L-7 9 L-64 0 L-7 -9 Z" />
                        <circle cx="0" cy="0" r="4" />
                    </g>
                </svg>
            </div>

            <?php include '../../back/includes/topbar.php'; ?>

            <section class="dashboard-grid">
                
                <!-- COLUNA DA TRILHA DE APRENDIZADO (ORGANIZADA POR CAPÍTULO / ILHA SEM PULAR SEÇÕES) -->
                <div class="curriculum-column">

                    <!-- CONTROLE DE NAVEGAÇÃO ENTRE CAPÍTULOS (3 ILHAS DO ARQUIPÉLAGO) -->
                    <div class="woofz-island-topbar" id="woofzIslandTopbar">
                        <div class="wit-main-row">
                            <button type="button" class="wit-nav-btn" id="btnCapituloAnterior" onclick="navegarCapitulo(-1)" title="Capítulo Anterior" <?php echo ($divisao_ativa_padrao <= 1) ? 'disabled' : ''; ?>>
                                <i class="fa-solid fa-chevron-left"></i>
                                <span>Anterior</span>
                            </button>

                            <button type="button" class="wit-island-selector" id="btnSeletorCapituloTopo" onclick="abrirMapaIlhas()" title="Abrir Mapa das Ilhas">
                                <span class="wit-world-pill" id="witWorldPill">
                                    <i class="fa-solid fa-compass"></i>
                                    CAPÍTULO <?php echo (int) $divisao_ativa_padrao; ?> · <?php echo htmlspecialchars(mb_strtoupper($ilha_ativa_nome, 'UTF-8')); ?> · SEÇÕES <?php echo (int) $div_ativa_info['secao_inicio']; ?>–<?php echo (int) $div_ativa_info['secao_fim']; ?>
                                </span>
                                <div class="wit-title-line">
                                    <strong id="witIslandTitle"><?php echo htmlspecialchars($div_ativa_info['tema'] ?? $div_ativa_info['nome']); ?></strong>
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </button>

                            <div class="wit-right-actions">
                                <button type="button" class="wit-map-btn" onclick="abrirMapaIlhas()" title="Ver Mapa das Ilhas">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    <span>Mapa</span>
                                </button>
                                <button type="button" class="wit-nav-btn" id="btnCapituloProximo" onclick="navegarCapitulo(1)" title="Próximo Capítulo" <?php echo ($divisao_ativa_padrao >= count($divisoes)) ? 'disabled' : ''; ?>>
                                    <span>Próximo</span>
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="wit-progress-strip">
                            <div class="wit-progress-track">
                                <div class="wit-progress-fill" id="witProgressFill" style="width: <?php echo max(6, $pct_div_ativa_hero); ?>%;"></div>
                            </div>
                            <span class="wit-progress-meta" id="witProgressMeta"><?php echo (int) $div_ativa_info['concluidas']; ?>/<?php echo (int) $div_ativa_info['total_secoes']; ?> seções</span>
                        </div>
                    </div>

                    <?php if ($progresso_porcentagem >= 100 || isset($_GET['preview_fim_curso']) || isset($_GET['curso_concluido'])): ?>
                    <a href="resultado.php?preview_fim_curso=1" class="banner-curso-concluido-trigger" id="btnAbrirSpotifyBanner" style="text-decoration: none;">
                        <div class="bc-left">
                            <img src="../assets/img/mascote_vitoria_cipo_transparente.png" alt="Opi Formatura" class="bc-mascot-thumb">
                            <div class="bc-texts">
                                <span class="bc-badge"><i class="fa-solid fa-graduation-cap"></i> FORMAÇÃO CONCLUÍDA</span>
                                <h3 class="bc-title">Parabéns! Você Concluiu o Curso OPUS!</h3>
                                <p class="bc-desc">Releia a retrospectiva com o Opi e veja o seu <strong>Card Especial Spotify Wrapped</strong> para compartilhar!</p>
                            </div>
                        </div>
                        <span class="bc-btn-open">
                            <i class="fa-brands fa-spotify"></i> Ver Card Spotify Wrapped
                        </span>
                    </a>
                    <?php endif; ?>

                    <!-- TRILHA ABERTA E ORGÂNICA DO CAPÍTULO ATIVO (SEM BORDA EXTERNA) -->
                    <div class="ocean-stage-wrapper" id="oceanStageWrapper">
                        <div class="unit-list">
                            <?php foreach ($nomes_unidades as $num_cap => $info): 
                                $status_cap_banco = $unidades[$num_cap]['status'] ?? 'trancado';
                                $licoes_feitas = $unidades[$num_cap]['licoes_concluidas'] ?? 0;
                                $cor_unidade = $info['cor'] ?? '#1cb0f6';
                                $sombras_por_cor = [
                                    '#1cb0f6' => '#148bc4',
                                    '#ff4b4b' => '#c82828',
                                    '#ff527b' => '#c42950',
                                    '#ce82ff' => '#9542d4',
                                    '#58cc02' => '#3f9900',
                                    '#ffc800' => '#c99700',
                                ];
                                $cor_sombra = $sombras_por_cor[strtolower($cor_unidade)] ?? '#148bc4';
                                
                                $posicao_mascote = ($num_cap % 2 != 0) ? 'mascote-esquerda' : 'mascote-direita';
                                $imagem_mascote = $info['mascote'] ?? 'vistodecimaazul.png';

                                // Identifica o capítulo/divisão a que esta seção pertence (1, 2 ou 3)
                                $divisao_capitulo = conteudo_divisao_da_secao($divisoes, $num_cap);
                                $is_divisao_visivel_inicial = ($divisao_capitulo === $divisao_ativa_padrao);
                                $total_mods_cap = count($info['licoes']);
                                $concluidas_cap_vis = ($status_cap_banco === 'completo') ? $total_mods_cap : min($total_mods_cap, (int)$licoes_feitas);
                                $pct_cap_vis = $total_mods_cap > 0 ? round(($concluidas_cap_vis / $total_mods_cap) * 100) : 0;
                            ?>
                                <div class="capitulo-container <?php echo ($status_cap_banco === 'current' || $status_cap_banco === 'corrente' ? 'current-chapter' : ''); ?> <?php echo $is_divisao_visivel_inicial ? 'island-active' : ''; ?>" 
                                     id="capitulo_<?php echo $num_cap; ?>" 
                                     data-capitulo="<?php echo $num_cap; ?>"
                                     data-divisao="<?php echo $divisao_capitulo; ?>"
                                     data-titulo="<?php echo htmlspecialchars($info['titulo']); ?>"
                                     data-nome="<?php echo htmlspecialchars($info['nome']); ?>"
                                     style="<?php echo (!$is_divisao_visivel_inicial) ? 'display: none;' : 'display: flex;'; ?>">

                                    <div class="capitulo-header"
                                         data-concluidas="<?php echo $concluidas_cap_vis; ?>"
                                         data-total="<?php echo $total_mods_cap; ?>"
                                         data-pct="<?php echo $pct_cap_vis; ?>"
                                         style="--unit-color: <?php echo $cor_unidade; ?>; --unit-shadow: <?php echo $cor_sombra; ?>;">
                                        <div class="capitulo-header-accent" aria-hidden="true">
                                            <span class="cha-dot"></span>
                                        </div>
                                        <div class="capitulo-header-text">
                                            <h2><?php echo htmlspecialchars($info['titulo']); ?></h2>
                                            <h3><?php echo htmlspecialchars($info['nome']); ?></h3>
                                        </div>
                                        <button class="btn-guia" 
                                                data-titulo="<?php echo htmlspecialchars($info['titulo']); ?>" 
                                                data-nome="<?php echo htmlspecialchars($info['nome']); ?>" 
                                                data-cor="<?php echo $cor_unidade; ?>" 
                                                data-texto="<?php echo htmlspecialchars($info['guia_texto']); ?>" 
                                                data-codigo="<?php echo htmlspecialchars($info['guia_codigo']); ?>" 
                                                onclick="abrirGuia(this)">
                                            <i class="fa-solid fa-book-open"></i> Guia
                                        </button>
                                    </div>
                                    
                                    <div class="trail-flex" data-capitulo="<?php echo $num_cap; ?>" data-status="<?php echo $status_cap_banco; ?>" data-cor="<?php echo $cor_unidade; ?>">
                                        <svg class="trail-svg" id="trail_svg_<?php echo $num_cap; ?>" aria-hidden="true"></svg>
                                        <?php echo opus_render_island_motif((int) $num_cap, (int) $divisao_capitulo); ?>
                                        <?php $total_mods = count($info['licoes']); for ($mod = 1; $mod <= $total_mods; $mod++): 
                                            $classe_modulo = "locked";
                                            $icone_modulo = "fa-star";
                                            $is_clicavel = false;
                                            $is_current = false;

                                            if ($mod == 5) {
                                                $icone_modulo = "fa-terminal";
                                            }

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
                                                <a href="<?php echo $url_destino; ?>" class="modulo-node current <?php echo $pos_class; ?>" id="currentLessonNode" data-capitulo="<?php echo $num_cap; ?>" data-licao="<?php echo $mod; ?>" style="--balloon-color: <?php echo $cor_unidade; ?>; --unit-color: <?php echo $cor_unidade; ?>; --node-shadow: <?php echo $cor_sombra; ?>;">
                                                    <div class="start-balloon" style="--balloon-color: <?php echo $cor_unidade; ?>;">
                                                        <?php echo $vidas_atual <= 0 ? 'SEM VIDAS' : 'COMEÇAR'; ?>
                                                    </div>
                                                    <div class="active-ring">
                                                        <div class="circle-button" style="--unit-color: <?php echo $cor_unidade; ?>; --node-shadow: <?php echo $cor_sombra; ?>;">
                                                            <i class="fa-solid <?php echo $icone_modulo; ?>"></i>
                                                        </div>
                                                    </div>
                                                    <span class="node-island-pedestal"></span>
                                                </a>
                                            <?php elseif ($is_clicavel): ?>
                                                <a href="<?php echo $url_destino; ?>" class="modulo-node completed <?php echo $pos_class; ?>" style="--balloon-color: <?php echo $cor_unidade; ?>; --unit-color: <?php echo $cor_unidade; ?>; --node-shadow: <?php echo $cor_sombra; ?>;">
                                                    <div class="circle-button" style="--unit-color: <?php echo $cor_unidade; ?>; --node-shadow: <?php echo $cor_sombra; ?>;">
                                                        <i class="fa-solid fa-check"></i>
                                                    </div>
                                                    <span class="node-island-pedestal"></span>
                                                </a>
                                            <?php else: ?>
                                                <div class="modulo-node locked <?php echo $pos_class; ?>">
                                                    <div class="circle-button">
                                                        <i class="fa-solid fa-lock"></i>
                                                    </div>
                                                    <span class="node-island-pedestal"></span>
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
                                                <div class="reward-chest-container pos-chest <?php echo $chest_state; ?>" data-chest-state="<?php echo $chest_state; ?>">
                                                    <?php if ($chest_state === 'available'): ?>
                                                        <div class="chest-balloon"><i class="fa-solid fa-arrows-rotate"></i> GIRAR ROLETA!</div>
                                                        <div class="chest-node available" id="chestNode_<?php echo $num_cap; ?>" onclick="abrirModalBau(<?php echo $num_cap; ?>, '<?php echo addslashes(htmlspecialchars($info['titulo'])); ?>')" role="button" tabindex="0" aria-label="Abrir Baú de Recompensas da Unidade <?php echo $num_cap; ?>">
                                                            <div class="chest-aura-glow"></div>
                                                            <?php echo opus_render_bau_svg('available', ['class' => 'chest-svg-trail']); ?>
                                                        </div>
                                                    <?php elseif ($chest_state === 'claimed'): ?>
                                                        <div class="chest-node claimed" id="chestNode_<?php echo $num_cap; ?>" onclick="avisoBauColetado(<?php echo $num_cap; ?>, '<?php echo addslashes(htmlspecialchars($info['titulo'])); ?>')" role="button" tabindex="0" aria-label="Baú da Unidade <?php echo $num_cap; ?> já coletado">
                                                            <?php echo opus_render_bau_svg('claimed', ['class' => 'chest-svg-trail']); ?>
                                                            <span class="chest-tag-done"><i class="fa-solid fa-check"></i> Coletado</span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="chest-node locked" id="chestNode_<?php echo $num_cap; ?>" onclick="avisoBauTrancado(<?php echo $num_cap; ?>, '<?php echo addslashes(htmlspecialchars($info['titulo'])); ?>', <?php echo (int)min(3, $licoes_feitas); ?>)" role="button" tabindex="0" aria-label="Baú da Unidade <?php echo $num_cap; ?> trancado (<?php echo (int)min(3, $licoes_feitas); ?> de 3 lições)">
                                                            <?php echo opus_render_bau_svg('locked', ['class' => 'chest-svg-trail']); ?>
                                                            <span class="chest-progress-pill"><i class="fa-solid fa-lock"></i> <?php echo (int)min(3, $licoes_feitas); ?>/3</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                        <?php endfor; ?>

                                        <img src="../assets/img/<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-lateral <?php echo $posicao_mascote; ?>">
                                    </div>
                                </div>

                                <?php if ($num_cap == 15): ?>
                                    <!-- MARCO DO FIM DO CAPÍTULO 1 -> NAVEGAÇÃO DE BARCO PARA O CAPÍTULO 2 -->
                                    <div class="division-milestone-card milestone-div-1" data-divisao="1" style="<?php echo ($divisao_ativa_padrao !== 1) ? 'display: none;' : 'display: flex;'; ?>">
                                        <div class="milestone-badge-icon" style="--ms-accent: #1cb0f6;">
                                            <i class="fa-solid fa-sailboat"></i>
                                        </div>
                                        <div class="milestone-content">
                                            <span class="milestone-tag">FIM DO CAPÍTULO 1 · ILHA DOS FUNDAMENTOS</span>
                                            <h3>Fundamentos de Java Concluídos!</h3>
                                            <p>Você percorreu todas as 15 seções desta ilha: sintaxe, variáveis, operadores, decisões, laços e métodos. Embarque rumo à próxima ilha!</p>
                                            <button type="button" class="btn-ir-proxima-divisao" style="--ms-btn: #1cb0f6;" onclick="irParaDivisao(2)">
                                                <i class="fa-solid fa-sailboat"></i> Navegar para o Capítulo 2 <i class="fa-solid fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php elseif ($num_cap == 26): ?>
                                    <!-- MARCO DO FIM DO CAPÍTULO 2 -> NAVEGAÇÃO DE BARCO PARA O CAPÍTULO 3 -->
                                    <div class="division-milestone-card milestone-div-2" data-divisao="2" style="<?php echo ($divisao_ativa_padrao !== 2) ? 'display: none;' : 'display: flex;'; ?>">
                                        <div class="milestone-badge-icon" style="--ms-accent: #ce82ff;">
                                            <i class="fa-solid fa-compass"></i>
                                        </div>
                                        <div class="milestone-content">
                                            <span class="milestone-tag" style="color: #c084fc;">FIM DO CAPÍTULO 2 · ILHA DO CÓDIGO</span>
                                            <h3>Estruturas &amp; Coleções Dominadas!</h3>
                                            <p>Todas as 11 seções intermediárias concluídas! Zarpe agora para a terceira ilha e domine Orientação a Objetos profissional.</p>
                                            <button type="button" class="btn-ir-proxima-divisao" style="--ms-btn: #a855f7;" onclick="irParaDivisao(3)">
                                                <i class="fa-solid fa-sailboat"></i> Navegar para o Capítulo 3 <i class="fa-solid fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php elseif ($num_cap == 40): ?>
                                    <!-- MARCO FINAL DO CAPÍTULO 3 -->
                                    <div class="division-milestone-card milestone-div-3" data-divisao="3" style="<?php echo ($divisao_ativa_padrao !== 3) ? 'display: none;' : 'display: flex;'; ?>">
                                        <div class="milestone-badge-icon" style="--ms-accent: #f59e0b;">
                                            <i class="fa-solid fa-crown"></i>
                                        </div>
                                        <div class="milestone-content">
                                            <span class="milestone-tag" style="color: #fbbf24;">MESTRE EM JAVA OPUS · ILHA FINAL</span>
                                            <h3>Todos os 3 Capítulos Finalizados!</h3>
                                            <p>Parabéns, desenvolvedor! Você explorou todas as ilhas, 40 seções e 200 lições do curso, dominando POO, design patterns e arquitetura Java.</p>
                                            <button type="button" class="btn-ir-proxima-divisao btn-ms-gold" style="--ms-btn: #f59e0b;" onclick="abrirMapaIlhas()">
                                                <i class="fa-solid fa-map-location-dot"></i> Explorar Mapa das Ilhas
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </div>
                    </div> <!-- Fim .ocean-stage-wrapper -->
                </div>

                <!-- COLUNA DIREITA (WIDGETS ORIGINAIS DO SITE) -->
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
                                    <?php echo !empty($status_jogador['proxima_vida_texto']) ? htmlspecialchars($status_jogador['proxima_vida_texto']) : '30min'; ?>
                                </span></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- WIDGET DE OFENSIVA / SEQUÊNCIA DIÁRIA -->
                    <div class="widget-box widget-ofensiva <?php echo $praticou_hoje ? 'fogo-ativo' : 'fogo-inativo'; ?>" id="widgetOfensiva">
                        <div class="widget-header">
                            <h3><i class="fa-solid fa-fire-flame-curved ofensiva-header-icon"></i> Ofensiva</h3>
                            <span class="ofensiva-header-badge">
                                <?php echo $dias_fogo; ?> <?= $dias_fogo === 1 ? 'dia' : 'dias'; ?>
                            </span>
                        </div>
                        <div class="ofensiva-body <?php echo $praticou_hoje ? 'fogo-ativo' : 'fogo-inativo'; ?>">
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

                        <!-- Mini Capítulos no Widget de Progresso -->
                        <div class="progresso-divisoes-mini">
                            <div class="prog-div-item <?php echo $divisoes[1]['is_atual'] ? 'atual' : ''; ?>" onclick="irParaDivisao(1)" title="Navegar para o Capítulo 1">
                                <span class="pdiv-dot" style="background: #1cb0f6;"></span>
                                <span class="pdiv-nome">Capítulo 1</span>
                                <span class="pdiv-val"><?php echo $divisoes[1]['concluidas']; ?>/15</span>
                            </div>
                            <div class="prog-div-item <?php echo $divisoes[2]['is_atual'] ? 'atual' : ''; ?>" onclick="irParaDivisao(2)" title="Navegar para o Capítulo 2">
                                <span class="pdiv-dot" style="background: #ce82ff;"></span>
                                <span class="pdiv-nome">Capítulo 2</span>
                                <span class="pdiv-val"><?php echo $divisoes[2]['concluidas']; ?>/11</span>
                            </div>
                            <div class="prog-div-item <?php echo $divisoes[3]['is_atual'] ? 'atual' : ''; ?>" onclick="irParaDivisao(3)" title="Navegar para o Capítulo 3">
                                <span class="pdiv-dot" style="background: #ffc800;"></span>
                                <span class="pdiv-nome">Capítulo 3</span>
                                <span class="pdiv-val"><?php echo $divisoes[3]['concluidas']; ?>/14</span>
                            </div>
                        </div>
                    </div>

                </div> <!-- Fim widgets-column -->

            </section>
        </main>
    </div>

    <!-- OVERLAY EM TELA INTEIRA: ANIMAÇÃO DO BARQUINHO & MAPA DAS ILHAS (3 CAPÍTULOS) -->
    <div id="islandVoyageOverlay" class="island-voyage-fullscreen" role="dialog" aria-modal="true" aria-hidden="true" aria-label="Navegação entre ilhas dos capítulos">
        <!-- Cenário oceânico estendido que se funde perfeitamente às bordas da imagem -->
        <div class="ivo-ocean-backdrop" aria-hidden="true">
            <span class="ivo-ambient-cloud c1"></span>
            <span class="ivo-ambient-cloud c2"></span>
            <span class="ivo-ambient-cloud c3"></span>
            <span class="ivo-ambient-cloud c4"></span>
            <span class="ivo-ambient-ripple w1"></span>
            <span class="ivo-ambient-ripple w2"></span>
            <span class="ivo-ambient-ripple w3"></span>
            <span class="ivo-ambient-ripple w4"></span>
        </div>

        <!-- Barra HUD Superior -->
        <div class="ivo-top-hud">
            <div class="ivo-route-badge" id="ivoRouteBadge">
                <i class="fa-solid fa-compass" id="ivoCompassIcon"></i>
                <span id="ivoRouteBadgeText">CAPÍTULO 1 ➜ CAPÍTULO 2</span>
            </div>
            <button type="button" class="ivo-action-btn" id="ivoTopActionBtn" onclick="tratarAcaoTopoMapa()">
                <span id="ivoTopActionLabel">Pular animação</span>
                <i class="fa-solid fa-forward-step" id="ivoTopActionIcon"></i>
            </button>
        </div>

        <!-- Palco Central com Câmera Dinâmica e a Imagem da Ilha -->
        <div class="ivo-camera-viewport">
            <div class="ivo-map-stage" id="ivoMapStage">
                <img src="../assets/img/mapa_ilhas.jpg" alt="Mapa das Ilhas do Curso Opus" class="ivo-map-image" draggable="false">

                <!-- Rotas Náuticas em SVG (Coordenadas verificadas sobre água em 1000x1000) -->
                <svg class="ivo-nautical-svg" id="ivoNauticalSvg" viewBox="0 0 1000 1000" aria-hidden="true">
                    <!-- Rotas náuticas sutis conectando as 3 ilhas -->
                    <path id="ivoLane12" class="ivo-sea-lane" d="M 390 390 C 500 440, 620 600, 780 520" />
                    <path id="ivoLane23" class="ivo-sea-lane" d="M 780 520 C 740 650, 650 750, 520 750" />
                    <path id="ivoLane13" class="ivo-sea-lane" d="M 390 390 C 510 470, 540 620, 520 750" />

                    <!-- Rota ativa da viagem atual (trilha pontilhada + aura dupla leve sem blur custoso) -->
                    <path id="ivoActiveRouteBg" class="ivo-active-route-bg" d="" />
                    <path id="ivoActiveRouteOuter" class="ivo-active-route-outer" d="" />
                    <path id="ivoActiveRouteGlow" class="ivo-active-route-glow" d="" />

                    <!-- Portos / Âncoras das 3 Ilhas -->
                    <g class="ivo-dock-beacon" id="ivoDock1" transform="translate(390, 390)">
                        <circle class="ivo-dock-pulse" r="18" />
                        <circle class="ivo-dock-ring" r="10" />
                        <circle class="ivo-dock-core" r="5" />
                    </g>
                    <g class="ivo-dock-beacon" id="ivoDock2" transform="translate(780, 520)">
                        <circle class="ivo-dock-pulse" r="18" />
                        <circle class="ivo-dock-ring" r="10" />
                        <circle class="ivo-dock-core" r="5" />
                    </g>
                    <g class="ivo-dock-beacon" id="ivoDock3" transform="translate(520, 750)">
                        <circle class="ivo-dock-pulse" r="18" />
                        <circle class="ivo-dock-ring" r="10" />
                        <circle class="ivo-dock-core" r="5" />
                    </g>

                    <!-- Efeito de onda celebratória ao atracar na ilha de destino -->
                    <g id="ivoArrivalBurst" class="ivo-arrival-burst" transform="translate(780, 520)">
                        <circle class="ivo-burst-ring b1" r="16" />
                        <circle class="ivo-burst-ring b2" r="16" />
                    </g>
                </svg>

                <!-- Pool pré-alocado de ondas de espuma (zero criação de DOM durante o voo -> 60fps liso) -->
                <div class="ivo-wake-pool" id="ivoWakePool" aria-hidden="true">
                    <span class="ivo-wake-dot"></span>
                    <span class="ivo-wake-dot"></span>
                    <span class="ivo-wake-dot"></span>
                    <span class="ivo-wake-dot"></span>
                    <span class="ivo-wake-dot"></span>
                    <span class="ivo-wake-dot"></span>
                </div>

                <!-- BARQUINHO HEROI OPUS EM DESTAQUE (AZUL REAL + DOURADO + AURA NA ÁGUA) -->
                <div class="ivo-voyage-boat" id="ivoVoyageBoat" aria-hidden="true">
                    <div class="ivo-boat-anchor">
                        <span class="ivo-boat-halo"></span>
                        <div class="ivo-boat-bob">
                            <div class="ivo-boat-flip" id="ivoBoatFlip">
                                <svg class="ivo-boat-svg" viewBox="0 0 140 124" width="106" height="94">
                                    <defs>
                                        <linearGradient id="opusSailGold" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#fffbeb" />
                                            <stop offset="55%" stop-color="#fde047" />
                                            <stop offset="100%" stop-color="#f59e0b" />
                                        </linearGradient>
                                        <linearGradient id="opusHullBlue" x1="0%" y1="0%" x2="0%" y2="100%">
                                            <stop offset="0%" stop-color="#2563eb" />
                                            <stop offset="100%" stop-color="#172554" />
                                        </linearGradient>
                                    </defs>

                                    <!-- Sombra e espuma vibrante na linha d'água -->
                                    <ellipse cx="70" cy="103" rx="48" ry="11" fill="rgba(7, 25, 48, 0.36)" />
                                    <path d="M 14 98 Q 36 91 58 98 T 104 98 Q 116 93 126 98" fill="none" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round" opacity="0.95" />
                                    <path d="M 24 106 Q 48 101 72 106 T 116 106" fill="none" stroke="#bae6fd" stroke-width="2.8" stroke-linecap="round" opacity="0.85" />

                                    <!-- Mastro dourado reforçado -->
                                    <rect x="64" y="14" width="6.5" height="64" rx="3" fill="#fbbf24" stroke="#0b1936" stroke-width="2.4" />
                                    <!-- Estrela / Farol no topo do mastro -->
                                    <circle cx="67.2" cy="12" r="5.5" fill="#fef08a" stroke="#0b1936" stroke-width="2" />

                                    <!-- Bandeira Coral Vibrante no Topo -->
                                    <path d="M 70 15 L 104 22 L 70 30 Z" fill="#ff4b4b" stroke="#0b1936" stroke-width="2.4" stroke-linejoin="round" />

                                    <!-- Vela Principal Dourada com Emblema OPUS <> -->
                                    <path d="M 70 28 Q 118 42 114 75 L 70 75 Z" fill="url(#opusSailGold)" stroke="#0b1936" stroke-width="3" stroke-linejoin="round" />
                                    <!-- Reflexo brilhante na vela -->
                                    <path d="M 76 36 Q 97 45 99 66" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" opacity="0.65" />
                                    <!-- Emblema de código <> azul marinho na vela -->
                                    <path d="M 85 52 L 78 58 L 85 64 M 96 52 L 103 58 L 96 64" fill="none" stroke="#1e3a8a" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" />

                                    <!-- Vela Dianteira Ciano Brilhante -->
                                    <path d="M 63 32 Q 28 48 31 75 L 63 75 Z" fill="#38bdf8" stroke="#0b1936" stroke-width="3" stroke-linejoin="round" />
                                    <path d="M 57 42 Q 41 53 41 68" fill="none" stroke="#e0f2fe" stroke-width="2.8" stroke-linecap="round" opacity="0.75" />

                                    <!-- Casco Azul Real com Borda Branca/Marinho de Alto Contraste -->
                                    <path d="M 16 76 L 124 76 L 108 98 L 32 98 Z" fill="url(#opusHullBlue)" stroke="#0b1936" stroke-width="3.2" stroke-linejoin="round" />
                                    <!-- Friso Dourado Brilhante no Convés -->
                                    <path d="M 20 79 L 120 79 L 116 85 L 24 85 Z" fill="#fbbf24" stroke="#0b1936" stroke-width="1.6" stroke-linejoin="round" />
                                    <!-- Vigias Iluminadas do Barco -->
                                    <circle cx="46" cy="91" r="4" fill="#fef08a" stroke="#0b1936" stroke-width="1.8" />
                                    <circle cx="70" cy="91" r="4" fill="#fef08a" stroke="#0b1936" stroke-width="1.8" />
                                    <circle cx="94" cy="91" r="4" fill="#fef08a" stroke="#0b1936" stroke-width="1.8" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3 MARCADORES INTERATIVOS DAS ILHAS (1 POR CAPÍTULO) -->
                <?php
                    $pins_ilhas = [
                        1 => ['left' => '25%', 'top' => '11%', 'sub' => 'Seções 1 a 15',  'cor' => '#1cb0f6', 'apelido' => 'Ilha dos Fundamentos'],
                        2 => ['left' => '73%', 'top' => '9%',  'sub' => 'Seções 16 a 26', 'cor' => '#ce82ff', 'apelido' => 'Ilha do Código'],
                        3 => ['left' => '31%', 'top' => '59%', 'sub' => 'Seções 27 a 40', 'cor' => '#ffc800', 'apelido' => 'Ilha dos Desafios'],
                    ];
                    foreach ($divisoes as $d_pin_id => $d_pin_info):
                        $cfg_pin = $pins_ilhas[$d_pin_id] ?? $pins_ilhas[1];
                ?>
                    <button type="button"
                            class="ivo-island-pin <?php echo ($d_pin_id === $divisao_ativa_padrao) ? 'is-current' : ''; ?>"
                            id="ivoPin<?php echo (int) $d_pin_id; ?>"
                            data-capitulo="<?php echo (int) $d_pin_id; ?>"
                            style="left: <?php echo $cfg_pin['left']; ?>; top: <?php echo $cfg_pin['top']; ?>; --pin-accent: <?php echo $cfg_pin['cor']; ?>;"
                            onclick="selecionarIlhaCapituloMapa(<?php echo (int) $d_pin_id; ?>)">
                        <span class="ivo-pin-badge">CAPÍTULO <?php echo (int) $d_pin_id; ?></span>
                        <strong class="ivo-pin-title"><?php echo htmlspecialchars($d_pin_info['tema']); ?></strong>
                        <span class="ivo-pin-meta"><?php echo $cfg_pin['sub']; ?> · <?php echo (int) $d_pin_info['concluidas']; ?>/<?php echo (int) $d_pin_info['total_secoes']; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cartão HUD Inferior de Status da Viagem -->
        <div class="ivo-bottom-hud">
            <div class="ivo-travel-card" id="ivoTravelCard">
                <div class="ivo-travel-kicker" id="ivoTravelKicker">
                    <i class="fa-solid fa-sailboat"></i>
                    <span>NAVEGANDO PELO ARQUIPÉLAGO OPUS</span>
                </div>
                <h2 class="ivo-travel-title" id="ivoTravelTitle">Capítulo 2 · Estruturas de Dados &amp; Coleções</h2>
                <p class="ivo-travel-sub" id="ivoTravelSub">Atravessando o oceano rumo às Seções 16 a 26...</p>
                <div class="ivo-progress-wrap" id="ivoProgressWrap">
                    <div class="ivo-progress-bar">
                        <div class="ivo-progress-fill" id="ivoProgressFill"></div>
                    </div>
                    <span class="ivo-progress-pct" id="ivoProgressPct">0%</span>
                </div>
            </div>
        </div>
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

    <!-- Modal do Baú de Recompensas: ROLETA LATERALIZADA -->
    <div id="modalBauRecompensa" class="modal-overlay">
        <div class="modal-bau-box modal-roleta-box modal-roleta-lateral">
            <canvas id="chestConfettiCanvas" class="chest-confetti-canvas"></canvas>
            <button class="close-btn-modal" id="btnCloseModalBau" onclick="fecharModalBau()" title="Fechar"><i class="fa-solid fa-xmark"></i></button>
            
            <div class="modal-roulette-header">
                <div class="modal-roulette-chest-crest" aria-hidden="true">
                    <?php echo opus_render_bau_svg('available', ['class' => 'chest-svg-roulette-crest']); ?>
                </div>
                <h2 class="modal-bau-title" id="modalBauTitle">Roleta de Recompensas!</h2>
                <div class="modal-bau-sub" id="modalBauSub"><i class="fa-solid fa-crown"></i> Unidade X · Baú da Trilha</div>
            </div>

            <p class="modal-bau-desc" id="roletaInstrucao">
                Gire a roleta lateral e descubra seu prêmio! Prêmios de <strong>3 XP até 25 XP (Máximo)</strong> ou <strong>Coração de Vida</strong>!
            </p>

            <!-- Palco da Roleta Lateralizada (Horizontal Reel) -->
            <div class="roleta-horizontal-stage" id="roletaHorizontalStage">
                <!-- Marcador Central / Agulha Indicadora -->
                <div class="roleta-center-needle" id="roletaCenterNeedle">
                    <div class="needle-arrow needle-top"></div>
                    <div class="needle-bar"></div>
                    <div class="needle-arrow needle-bottom"></div>
                </div>

                <!-- Sombras laterais para fade suave de entrada e saída -->
                <div class="roleta-fade roleta-fade-left"></div>
                <div class="roleta-fade roleta-fade-right"></div>

                <!-- Trilho deslizante onde ficam os cartões -->
                <div class="roleta-reel-viewport" id="roletaReelViewport">
                    <div class="roleta-cards-track" id="roletaCardsTrack">
                        <!-- Cartões inseridos dinamicamente pelo JS -->
                    </div>
                </div>
            </div>

            <!-- Card de Vitória / Prêmio Conquistado -->
            <div id="rewardContainerPos" style="display: none;">
                <div class="roleta-win-card" id="roletaWinCard">
                    <div class="win-sparkle-tag" id="winSparkleTag"><i class="fa-solid fa-star"></i> PRÊMIO CONQUISTADO!</div>
                    <div class="win-card-body">
                        <div class="win-icon-wrap" id="winIconWrap">
                            <i class="fa-solid fa-bolt" id="winIcon"></i>
                        </div>
                        <div class="win-card-texts">
                            <div class="win-val" id="winVal">+25 XP</div>
                            <div class="win-sub" id="winSub">Prêmio Máximo da Roleta!</div>
                        </div>
                    </div>
                    <div class="win-detalhe-pill" id="winDetalhePill">
                        <i class="fa-solid fa-circle-check"></i> <span id="winDetalheTxt">Recompensa creditada com sucesso!</span>
                    </div>
                </div>
            </div>

            <button class="btn-claim-chest btn-spin-roleta" id="btnResgatarBau" onclick="girarRoletaBau()">
                <i class="fa-solid fa-arrows-rotate"></i> GIRAR ROLETA!
            </button>
            <button class="btn-claim-chest" id="btnFecharBauPronto" onclick="fecharModalBau()" style="display: none; background: #58cc02; border-bottom: 5px solid #46a302; box-shadow: none;">
                <i class="fa-solid fa-arrow-right"></i> CONTINUAR APRENDENDO
            </button>
        </div>
    </div>

    <!-- Modal de Aviso Personalizado para Baú Coletado/Trancado com Trilha de Progresso -->
    <div id="modalAvisoBau" class="modal-overlay">
        <div class="modal-bau-box modal-aviso-box">
            <button class="close-btn-modal" onclick="fecharAvisoBau()"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-aviso-icon-wrap" id="avisoBauIconBox">
                <i class="fa-solid fa-lock" id="avisoBauIcone"></i>
            </div>
            
            <h2 class="modal-bau-title" id="avisoBauTitulo">Baú Trancado</h2>
            <div class="modal-bau-sub" id="avisoBauSub">Unidade X</div>
            
            <div id="avisoBauProgressoBox" class="aviso-bau-progress-container">
                <div class="aviso-steps-track">
                    <div class="step-line-bg">
                        <div class="step-line-fill" id="avisoStepFill"></div>
                    </div>
                    <div class="step-point" id="avisoStep1">
                        <span class="step-dot"><i class="fa-solid fa-1"></i></span>
                        <span class="step-lbl">Lição 1</span>
                    </div>
                    <div class="step-point" id="avisoStep2">
                        <span class="step-dot"><i class="fa-solid fa-2"></i></span>
                        <span class="step-lbl">Lição 2</span>
                    </div>
                    <div class="step-point" id="avisoStep3">
                        <span class="step-dot"><i class="fa-solid fa-gift"></i></span>
                        <span class="step-lbl">Baú</span>
                    </div>
                </div>
                <div class="aviso-progress-meta">
                    <span id="avisoProgressoTexto">0 de 3 lições concluídas</span>
                    <span class="aviso-percent-badge" id="avisoProgressoPorcentagem">0%</span>
                </div>
            </div>

            <p class="aviso-bau-desc" id="avisoBauTexto">
                Complete pelo menos 3 lições para desbloquear este baú.
            </p>
            
            <div class="aviso-action-buttons">
                <button class="btn-claim-chest btn-aviso-primario" id="btnAvisoAcao" onclick="fecharAvisoBau()">
                    CONTINUAR
                </button>
            </div>
        </div>
    </div>

    <!-- Templates SVG do Baú Pirata para atualizações dinâmicas via JS -->
    <template id="tplBauClaimedSvg"><?php echo opus_render_bau_svg('claimed', ['class' => 'chest-svg-trail']); ?></template>
    <template id="tplBauLockedModalSvg"><?php echo opus_render_bau_svg('locked', ['class' => 'chest-svg-modal']); ?></template>
    <template id="tplBauClaimedModalSvg"><?php echo opus_render_bau_svg('claimed', ['class' => 'chest-svg-modal']); ?></template>

    <!-- BOTÃO FLUTUANTE COM FORMATO DE SETA PARA ROLAGEM DIRETA À LIÇÃO ATUAL -->
    <button class="btn-scroll-to-lesson" id="btnScrollToLesson" onclick="rolarAteLicaoAtual(true)" title="Ir direto para a lição atual na trilha">
        <i class="fa-solid fa-arrow-right"></i>
    </button>

    <script>
        window.OPUS_DIVISOES_DADOS = <?= json_encode($divisoes, JSON_UNESCAPED_UNICODE) ?>;
        window.OPUS_DIVISAO_INICIAL = <?= json_encode($divisao_ativa_padrao) ?>;
        window.OPUS_ILHA_ATUAL_USUARIO = <?= json_encode((int) $capitulo_corrente_usuario) ?>;
        window.OPUS_TOTAL_ILHAS = <?= json_encode(count($nomes_unidades)) ?>;
    </script>
    <script src="../assets/js/script.js"></script> 
    <script src="../assets/js/opus_audio.js"></script>
    <script src="../assets/js/dashboard.js?v=<?= time() ?>"></script>
</body>
</html>
