<?php
// tour_opi.php - Tour Guiado Interativo com o Mascote Opi (Estilo Duolingo)
// Incluído automaticamente junto com a sidebar nas páginas internas do aluno.

$tour_is_admin = !empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin');
$is_preview_tour = isset($_GET['preview_fim_curso']) || isset($_GET['fim_curso']) || isset($_GET['preview_streak']) || isset($_GET['preview_tudo']);
if ((!isset($_SESSION['user_id']) && !$is_preview_tour) || $tour_is_admin) {
    return;
}

if (!empty($GLOBALS['OPUS_TOUR_RENDERED'])) {
    return;
}
$GLOBALS['OPUS_TOUR_RENDERED'] = true;

$tour_user_id = (int) ($_SESSION['user_id'] ?? 1);
$tour_pagina = basename($_SERVER['SCRIPT_NAME']);
$tour_nome_completo = trim((string) ($_SESSION['user_nome'] ?? 'Estudante'));
$tour_visto_db = false;
$tour_perfil_visto_db = false;
$tour_licao1_visto_db = false;
$tour_resultado1_visto_db = false;
$tour_fim_curso_visto_db = false;

try {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        require_once __DIR__ . '/../config/conexao.php';
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $colunas_tour = ['tour_visto', 'tour_perfil_visto', 'tour_licao1_visto', 'tour_resultado1_visto', 'tour_fim_curso_visto'];
        foreach ($colunas_tour as $col_t) {
            $chk = $conn->query("SHOW COLUMNS FROM usuarios LIKE '{$col_t}'");
            if ($chk && $chk->num_rows === 0) {
                @$conn->query("ALTER TABLE usuarios ADD COLUMN {$col_t} TINYINT(1) NOT NULL DEFAULT 0");
            }
        }

        $stmt_t = $conn->prepare("SELECT nome, tour_visto, tour_perfil_visto, tour_licao1_visto, tour_resultado1_visto, tour_fim_curso_visto FROM usuarios WHERE id = ?");
        if ($stmt_t) {
            $stmt_t->bind_param("i", $tour_user_id);
            $stmt_t->execute();
            $row_t = $stmt_t->get_result()->fetch_assoc();
            $stmt_t->close();
            if ($row_t) {
                if (!empty($row_t['nome'])) {
                    $tour_nome_completo = trim((string) $row_t['nome']);
                }
                $tour_visto_db = !empty($row_t['tour_visto']);
                $tour_perfil_visto_db = !empty($row_t['tour_perfil_visto']);
                $tour_licao1_visto_db = !empty($row_t['tour_licao1_visto']);
                $tour_resultado1_visto_db = !empty($row_t['tour_resultado1_visto']);
                $tour_fim_curso_visto_db = !empty($row_t['tour_fim_curso_visto']);
            }
        }
    }
} catch (Throwable $e) {
    // Fallback silencioso: tour_opi.js também usa localStorage como respaldo
}

$partes_nome = preg_split('/\s+/', $tour_nome_completo);
$tour_primeiro_nome = !empty($partes_nome[0]) ? $partes_nome[0] : 'Estudante';
?>

<link rel="stylesheet" href="../assets/css/tour_opi.css?v=<?= time() ?>">

<!-- OVERLAY DO TOUR GUIADO COM O MASCOTE OPI (ESTILO DUOLINGO) -->
<div class="opi-tour-root" id="opiTourRoot" role="dialog" aria-modal="true" aria-labelledby="opiTourTitle" aria-hidden="true">
    <!-- Camada de escurecimento (quando não há spotlight ou em telas introdutórias) -->
    <div class="opi-tour-backdrop" id="opiTourBackdrop"></div>

    <!-- Recorte luminoso (Spotlight) que acompanha cada botão apresentado -->
    <div class="opi-tour-spotlight" id="opiTourSpotlight" aria-hidden="true">
        <div class="opi-tour-spotlight-ring"></div>
        <div class="opi-tour-pointer-tag" id="opiTourPointerTag">
            <i class="fa-solid fa-hand-pointer"></i>
            <span id="opiTourPointerLabel">Aqui!</span>
        </div>
    </div>

    <!-- Palco do Mascote Opi + Balão de Fala -->
    <div class="opi-tour-stage is-centered" id="opiTourStage">
        <!-- Mascote Opi -->
        <div class="opi-tour-mascot-wrap" id="opiTourMascotWrap">
            <div class="opi-tour-mascot-aura" aria-hidden="true"></div>
            <img src="../assets/img/vistodecimaazul.png" alt="Mascote Opi apresentando o Opus" class="opi-tour-mascot-img" id="opiTourMascotImg">
            <div class="opi-tour-mascot-shadow" aria-hidden="true"></div>
        </div>

        <!-- Balão de Fala Estilo Duolingo -->
        <div class="opi-tour-bubble" id="opiTourBubble">
            <div class="opi-tour-bubble-tail" aria-hidden="true"></div>

            <!-- Barra superior do balão -->
            <div class="opi-tour-bubble-header">
                <div class="opi-tour-badge-wrap">
                    <span class="opi-tour-badge" id="opiTourBadge">TOUR OPUS</span>
                    <span class="opi-tour-counter" id="opiTourCounter">1 / 26</span>
                </div>

                <div class="opi-tour-controls-mini">
                    <button type="button" class="opi-tour-icon-btn opi-tour-close-btn" id="opiTourCloseBtn" title="Fechar apresentação (Esc)" aria-label="Fechar tour">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Título do passo -->
            <h3 class="opi-tour-title" id="opiTourTitle">Olá! Eu sou o Opi!</h3>

            <!-- Cartão de Troféu Conquistado (exibido apenas em passos de troféu) -->
            <div class="opi-tour-trophy-card" id="opiTourTrophyCard" style="display: none;">
                <div class="opi-tour-trophy-icon-wrap">
                    <div class="opi-tour-trophy-glow" aria-hidden="true"></div>
                    <img src="../assets/img/medalha.png" alt="Troféu conquistado" class="opi-tour-trophy-img" id="opiTourTrophyImg">
                </div>
                <div class="opi-tour-trophy-meta">
                    <span class="opi-tour-trophy-kicker"><i class="fa-solid fa-trophy"></i> NOVO TROFÉU</span>
                    <strong class="opi-tour-trophy-name" id="opiTourTrophyName">Troféu</strong>
                    <small class="opi-tour-trophy-desc" id="opiTourTrophyDesc"></small>
                </div>
            </div>

            <!-- Texto digitado em tempo real (Typewriter) -->
            <div class="opi-tour-speech-box" id="opiTourSpeechBox" title="Clique para acelerar a fala">
                <p class="opi-tour-text" id="opiTourText" aria-live="polite"></p>
            </div>

            <!-- Barra de progresso e pontos -->
            <div class="opi-tour-progress-row">
                <div class="opi-tour-progress-track">
                    <div class="opi-tour-progress-fill" id="opiTourProgressFill"></div>
                </div>
                <div class="opi-tour-dots" id="opiTourDots" aria-hidden="true"></div>
            </div>

            <!-- Ações / Botões 3D estilo Duolingo -->
            <div class="opi-tour-actions">
                <div class="opi-tour-actions-left">
                    <button type="button" class="opi-tour-btn opi-tour-btn-ghost" id="opiTourSkipBtn">Pular</button>
                    <button type="button" class="opi-tour-btn opi-tour-btn-secondary" id="opiTourPrevBtn" style="display: none;">
                        <i class="fa-solid fa-arrow-left"></i> Voltar
                    </button>
                </div>
                <div class="opi-tour-actions-right">
                    <button type="button" class="opi-tour-btn opi-tour-btn-accent" id="opiTourExtraBtn" style="display: none;"></button>
                    <button type="button" class="opi-tour-btn opi-tour-btn-primary" id="opiTourNextBtn">
                        <span id="opiTourNextLabel">Próximo</span> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$tour_config = [
    'userId' => $tour_user_id,
    'userName' => $tour_primeiro_nome,
    'page' => $tour_pagina,
    'tourVisto' => $tour_visto_db,
    'tourPerfilVisto' => $tour_perfil_visto_db,
    'tourLicao1Visto' => $tour_licao1_visto_db,
    'tourResultado1Visto' => $tour_resultado1_visto_db,
    'tourFimCursoVisto' => $tour_fim_curso_visto_db,
    'apiUrl' => '../../back/api/tour_status.php',
    'imgBase' => '../assets/img/',
];
if (isset($OPUS_TOUR_EXTRA_CONFIG) && is_array($OPUS_TOUR_EXTRA_CONFIG)) {
    $tour_config = array_merge($tour_config, $OPUS_TOUR_EXTRA_CONFIG);
}
?>
<script>
    window.OPUS_TOUR_CONFIG = <?= json_encode($tour_config, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../assets/js/tour_opi.js?v=<?= time() ?>"></script>
