<?php
session_start();
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

require_once '../../back/jogador_status.php';

$user_id = $_SESSION['user_id'];
$status_jogador = opus_sincronizar_jogador($conn, $user_id);
$vidas_atual = (int) $status_jogador['vidas'];
$sem_vidas = ($vidas_atual <= 0) || (isset($_GET['sem_vidas']));

// Busca progresso
$unidades = [];
$result_progresso = $conn->query("SELECT unidade_numero, status, licoes_concluidas FROM progresso_usuario WHERE usuario_id = $user_id ORDER BY unidade_numero ASC");

if ($result_progresso) {
    while ($row = $result_progresso->fetch_assoc()) {
        $unidades[$row['unidade_numero']] = $row;
    }
}

// Configuração dos Capítulos com Guias Personalizados e Mascote por Cor
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

$concluidas_res = $conn->query("SELECT COUNT(*) as total FROM progresso_usuario WHERE usuario_id = $user_id AND status = 'completo' AND unidade_numero <= 5");
$concluidas = $concluidas_res ? $concluidas_res->fetch_assoc()['total'] : 0;
$porcentagem_total = ($concluidas / 5) * 100; 
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
            content: none !important;
            background: none !important;
            border: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        .dashboard-grid, .curriculum-column {
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            background: transparent !important;
        }

        .unit-list { 
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 50px; 
            padding: 10px;
            margin-top: 20px;
            width: 100%;
        }

        .capitulo-container {
            width: 100%;
            max-width: 650px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        /* HEADER DO CAPÍTULO */
        .capitulo-header {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px 30px;
            border-radius: 20px;
            color: #fff;
            box-shadow: 0 6px 0px rgba(0,0,0,0.15);
            margin-bottom: 30px;
            position: relative;
            z-index: 10;
        }

        .capitulo-header-text h2 { font-size: 1.6rem; font-weight: 800; margin: 0; letter-spacing: 0.5px; }
        .capitulo-header-text h3 { font-size: 1.1rem; font-weight: 600; margin: 0; opacity: 0.9; }

        /* BOTÃO GUIA */
        .btn-guia {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-bottom: 4px solid rgba(255, 255, 255, 0.4);
            color: #fff;
            padding: 10px 24px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1rem;
            text-transform: uppercase;
            text-decoration: none;
            transition: all 0.1s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        .btn-guia:active { transform: translateY(2px); border-bottom: 2px solid rgba(255, 255, 255, 0.4); }

        /* TRILHA DE MÓDULOS SEM LINHAS */
        .trail-flex {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            position: relative;
            padding: 20px 0;
            background: none !important;
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
            text-decoration: none;
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 35px;
            animation: floatNode 3.5s ease-in-out infinite;
        }

        .circle-button {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            position: relative;
            transition: transform 0.1s ease;
            color: #fff;
        }

        .modulo-node.locked .circle-button { 
            background: #3a3a45; 
            color: #6a6a75; 
            box-shadow: 0 6px 0 #2a2a35; 
        }

        .modulo-node.completed:active .circle-button { 
            transform: translateY(4px); 
        }

        .modulo-node.current .circle-button { color: #fff; }
        .modulo-node.current:active .circle-button { transform: translateY(4px); }
        
        .active-ring {
            width: 105px; height: 105px;
            border-radius: 50%;
            background: #2a2a35;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .start-balloon {
            position: absolute;
            top: -65px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 800;
            text-transform: uppercase;
            animation: bounce 2s ease-in-out infinite;
            white-space: nowrap;
            z-index: 10;
        }
        .start-balloon::after {
            content: ''; position: absolute;
            bottom: -8px; left: 50%;
            transform: translateX(-50%);
            border-width: 8px 8px 0; border-style: solid;
        }
        @keyframes bounce { 0%, 100% { transform: translate(-50%, 0); } 50% { transform: translate(-50%, -8px); } }

        .reward-chest-container {
            position: relative;
            z-index: 1;
            margin: 10px 0 45px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: floatNode 4s ease-in-out infinite;
            animation-delay: 1s;
        }

        .chest-node {
            width: 80px; height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            position: relative;
            transition: all 0.3s;
        }

        .chest-node.locked { background: #3a3a45; color: #5a5a65; box-shadow: 0 6px 0 #2a2a35; }
        .chest-node.completed {
            background: linear-gradient(145deg, #ffd700, #ffaa00);
            color: #fff;
            box-shadow: 0 6px 0 #cc8800;
        }

        .logic-hologram {
            position: absolute;
            top: -25px;
            font-size: 20px;
            color: #58cc02;
            opacity: 0;
            transition: all 0.3s;
        }
        .chest-node.completed .logic-hologram {
            opacity: 1;
            animation: logicFloat 2s infinite alternate;
        }

        @keyframes logicFloat {
            0% { transform: translateY(0) scale(1); text-shadow: 0 0 5px #58cc02; }
            100% { transform: translateY(-10px) scale(1.2); text-shadow: 0 0 15px #58cc02; }
        }

        /* MASCOTE LATERAL DA TRILHA */
        .mascote-lateral {
            position: absolute;
            width: 140px;
            z-index: 2;
            pointer-events: none;
            animation: floatMascote 4s ease-in-out infinite;
            top: 35%;
        }

        .mascote-esquerda { left: 10px; }
        .mascote-direita { right: 10px; }

        @keyframes floatMascote {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        @media (max-width: 768px) {
            .mascote-lateral { display: none; }
        }

        /* MODAL GUIA */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            display: none; align-items: center; justify-content: center;
            z-index: 9999; opacity: 0; transition: opacity 0.3s ease;
        }
        .modal-overlay.active { display: flex; opacity: 1; }
        
        .modal-box {
            background: #1e1e24;
            width: 90%; max-width: 500px;
            border-radius: 20px;
            overflow: hidden;
            transform: scale(0.9);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
        }
        .modal-overlay.active .modal-box { transform: scale(1); }

        .modal-header { padding: 25px; color: #fff; position: relative; }
        .modal-header h2 { margin: 0; font-size: 1.8rem; font-weight: 900; }
        .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; opacity: 0.9; }
        
        .close-btn-modal {
            position: absolute; top: 20px; right: 20px;
            background: rgba(0,0,0,0.2); border: none; color: #fff;
            width: 35px; height: 35px; border-radius: 50%;
            font-size: 1.2rem; cursor: pointer; transition: 0.2s;
        }
        .close-btn-modal:hover { background: rgba(0,0,0,0.4); transform: scale(1.1); }

        .modal-body { padding: 30px; color: #d0d0d5; font-size: 1.1rem; line-height: 1.6; }
        .code-box {
            background: #111115; border-left: 4px solid #1cb0f6;
            padding: 15px; border-radius: 8px; font-family: monospace;
            margin-top: 15px; color: #a5d6a7;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="logo">OPUS</div>
            <nav class="menu">
                <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Aprender</a>
                <a href="conquistas.php" class="nav-link"><i class="fa-solid fa-award"></i> Missões</a>
                <a href="ranking.php" class="nav-link"><i class="fa-solid fa-shield-halved"></i> Ligas</a>
                <a href="perfil.php" class="nav-link"><i class="fa-solid fa-user"></i> Perfil</a>
            </nav>
        </aside>

        <main class="main-content">
            <?php include '../../back/topbar.php'; ?>

            <?php if ($vidas_atual <= 0): ?>
                <div class="lives-empty-banner" style="background:rgba(239,68,68,0.12);border:1px solid #ef4444;color:#fecaca;padding:12px 16px;border-radius:12px;margin-bottom:24px;font-weight:600;">
                    <i class="fa-solid fa-heart-crack"></i>
                    Você está sem vidas. Aguarde 24 horas para recuperar 1 coração
                    <?php if (!empty($status_jogador['proxima_vida_texto'])): ?>
                        (<?php echo htmlspecialchars($status_jogador['proxima_vida_texto']); ?>).
                    <?php else: ?>
                        e voltar às lições.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <section class="dashboard-grid">
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
                                            $reward_class = ($licoes_feitas >= 3 || $status_cap_banco === 'completo') ? 'completed' : 'locked';
                                        ?>
                                            <div class="reward-chest-container <?php echo $pos_class; ?>" style="margin-left: 0; margin-right: 80px;">
                                                <div class="chest-node <?php echo $reward_class; ?>">
                                                    <i class="fa-solid fa-box-open"></i>
                                                    <i class="fa-solid fa-code logic-hologram"></i>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                    <?php endfor; ?>

                                    <img src="../assets/img/<?php echo $imagem_mascote; ?>" alt="Mascote Opus" class="mascote-lateral <?php echo $posicao_mascote; ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>

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

    <script src="../assets/js/script.js"></script> 
    
    <script>
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
            const modal = document.getElementById('modalGuia');
            if (event.target == modal) {
                fecharGuia();
            }
        }
    </script>
</body>
</html>