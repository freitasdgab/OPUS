<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../../back/conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.html");
    exit();
}

// Administrador não pode jogar: redireciona para o Painel Administrativo
if (!empty($_SESSION['is_admin']) || (isset($_SESSION['user_nivel_acesso']) && $_SESSION['user_nivel_acesso'] === 'admin')) {
    header("Location: admin_dashboard.php");
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
        'titulo' => 'Seção 1',
        'nome' => 'Introdução',
        'descricao' => 'Módulo focado em Introdução.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Nesta seção, você dará seus primeiros passos na programação Java! Vamos conhecer a estrutura básica de um programa, como a classe principal e o método main funcionam, e como exibir mensagens na tela.',
        'guia_codigo' => 'public class Main {<br>&nbsp;&nbsp;&nbsp;&nbsp;public static void main(String[] args) {<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;System.out.println(\"Olá, Mundo!\");<br>&nbsp;&nbsp;&nbsp;&nbsp;}<br>}',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    2 => [
        'titulo' => 'Seção 2',
        'nome' => 'Variáveis Parte 1',
        'descricao' => 'Módulo focado em Variáveis Parte 1.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'As variáveis são como caixas onde guardamos informações. Aqui aprenderemos a declarar variáveis para armazenar números inteiros (int) e texto (String), além de ver como atribuir e ler esses valores.',
        'guia_codigo' => 'int idade = 20;<br>String nome = \"Opus\";<br><br>System.out.println(nome + \" tem \" + idade + \" anos.\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    3 => [
        'titulo' => 'Seção 3',
        'nome' => 'Variáveis Parte 2',
        'descricao' => 'Módulo focado em Variáveis Parte 2.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Avançando no uso das variáveis! Agora você vai conhecer os tipos para armazenar números decimais (double) e valores lógicos de verdadeiro ou falso (boolean). Entender esses tipos é essencial para lidar com informações reais.',
        'guia_codigo' => 'double altura = 1.75;<br>boolean aprovado = true;<br><br>if(aprovado) {<br>&nbsp;&nbsp;&nbsp;&nbsp;System.out.println(\"Altura: \" + altura);<br>}',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    4 => [
        'titulo' => 'Seção 4',
        'nome' => 'Operadores Parte 1',
        'descricao' => 'Módulo focado em Operadores Parte 1.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Chegou a hora da matemática! Vamos usar os operadores aritméticos básicos para fazer contas no código: adição (+), subtração (-), multiplicação (*) e divisão (/). Você verá como o Java processa essas expressões.',
        'guia_codigo' => 'int a = 10;<br>int b = 3;<br>int soma = a + b;<br>int resto = a % b;<br>System.out.println(\"Soma: \" + soma);',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    5 => [
        'titulo' => 'Seção 5',
        'nome' => 'Operadores Parte 2',
        'descricao' => 'Módulo focado em Operadores Parte 2.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Além de contas, precisamos tomar decisões. Vamos explorar os operadores relacionais (==, !=, &gt;, &lt;) e lógicos (&amp;&amp;, ||) que permitem comparar variáveis e criar regras complexas para o nosso programa.',
        'guia_codigo' => 'int nota = 8;<br>boolean passou = (nota &gt;= 7) &amp;&amp; (nota != 0);<br><br>System.out.println(\"Passou? \" + passou);',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    6 => [
        'titulo' => 'Seção 6',
        'nome' => 'Tomada de Decisão',
        'descricao' => 'Módulo focado em Tomada de Decisão.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    7 => [
        'titulo' => 'Seção 7',
        'nome' => 'I/O Básico',
        'descricao' => 'Módulo focado em I/O Básico.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    8 => [
        'titulo' => 'Seção 8',
        'nome' => 'Aplicativo de Calculadora',
        'descricao' => 'Módulo focado em Aplicativo de Calculadora.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    9 => [
        'titulo' => 'Seção 9',
        'nome' => 'Laços',
        'descricao' => 'Módulo focado em Laços.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    10 => [
        'titulo' => 'Seção 10',
        'nome' => 'Métodos e Funções',
        'descricao' => 'Módulo focado em Métodos e Funções.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    11 => [
        'titulo' => 'Seção 11',
        'nome' => 'Funcionamento de Arrays',
        'descricao' => 'Módulo focado em Funcionamento de Arrays.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    12 => [
        'titulo' => 'Seção 12',
        'nome' => 'Inteirando sobre Arrays',
        'descricao' => 'Módulo focado em Inteirando sobre Arrays.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    13 => [
        'titulo' => 'Seção 13',
        'nome' => 'Operações com String',
        'descricao' => 'Módulo focado em Operações com String.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    14 => [
        'titulo' => 'Seção 14',
        'nome' => 'Desafios Finais',
        'descricao' => 'Módulo focado em Desafios Finais.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    15 => [
        'titulo' => 'Seção 15',
        'nome' => 'Certificado',
        'descricao' => 'Módulo focado em Certificado.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    16 => [
        'titulo' => 'Seção 16',
        'nome' => 'Arrays Multidimensionais',
        'descricao' => 'Módulo focado em Arrays Multidimensionais.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    17 => [
        'titulo' => 'Seção 17',
        'nome' => 'HashMap Parte 1',
        'descricao' => 'Módulo focado em HashMap Parte 1.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    18 => [
        'titulo' => 'Seção 18',
        'nome' => 'HashMap Parte 2',
        'descricao' => 'Módulo focado em HashMap Parte 2.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    19 => [
        'titulo' => 'Seção 19',
        'nome' => 'HashSet Parte 1',
        'descricao' => 'Módulo focado em HashSet Parte 1.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    20 => [
        'titulo' => 'Seção 20',
        'nome' => 'HashSet Parte 2',
        'descricao' => 'Módulo focado em HashSet Parte 2.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    21 => [
        'titulo' => 'Seção 21',
        'nome' => 'Fluxo de Controle Avançado',
        'descricao' => 'Módulo focado em Fluxo de Controle Avançado.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    22 => [
        'titulo' => 'Seção 22',
        'nome' => 'Tratamento Básico de Erros',
        'descricao' => 'Módulo focado em Tratamento Básico de Erros.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    23 => [
        'titulo' => 'Seção 23',
        'nome' => 'Operações Avançadas com Strings',
        'descricao' => 'Módulo focado em Operações Avançadas com Strings.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    24 => [
        'titulo' => 'Seção 24',
        'nome' => 'Projeto Contador de Palavras Únicas',
        'descricao' => 'Módulo focado em Projeto Contador de Palavras Únicas.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    25 => [
        'titulo' => 'Seção 25',
        'nome' => 'Data e Hora',
        'descricao' => 'Módulo focado em Data e Hora.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    26 => [
        'titulo' => 'Seção 26',
        'nome' => 'Certificado da Seção 1 e 2',
        'descricao' => 'Módulo focado em Certificado da Seção 1 e 2.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    27 => [
        'titulo' => 'Seção 27',
        'nome' => 'Fundamentos de POO',
        'descricao' => 'Módulo focado em Fundamentos de POO.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    28 => [
        'titulo' => 'Seção 28',
        'nome' => 'Modificadores de Acesso e Encapsulamento',
        'descricao' => 'Módulo focado em Modificadores de Acesso e Encapsulamento.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    29 => [
        'titulo' => 'Seção 29',
        'nome' => 'Prioridades de Classe e Membros Estáticos',
        'descricao' => 'Módulo focado em Prioridades de Classe e Membros Estáticos.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    30 => [
        'titulo' => 'Seção 30',
        'nome' => 'Herança',
        'descricao' => 'Módulo focado em Herança.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    31 => [
        'titulo' => 'Seção 31',
        'nome' => 'Polimorfismo',
        'descricao' => 'Módulo focado em Polimorfismo.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    32 => [
        'titulo' => 'Seção 32',
        'nome' => 'Interfaces e Classes Abstratas',
        'descricao' => 'Módulo focado em Interfaces e Classes Abstratas.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    33 => [
        'titulo' => 'Seção 33',
        'nome' => 'Métodos Especiais e Classe Object',
        'descricao' => 'Módulo focado em Métodos Especiais e Classe Object.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    34 => [
        'titulo' => 'Seção 34',
        'nome' => 'Conceitos Avançados de OOP (Generics)',
        'descricao' => 'Módulo focado em Conceitos Avançados de OOP (Generics).',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    35 => [
        'titulo' => 'Seção 35',
        'nome' => 'Tratamento de Exceção em PPO',
        'descricao' => 'Módulo focado em Tratamento de Exceção em PPO.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    36 => [
        'titulo' => 'Seção 36',
        'nome' => 'Padrões de Projeto Parte 1',
        'descricao' => 'Módulo focado em Padrões de Projeto Parte 1.',
        'cor' => '#1cb0f6',
        'mascote' => 'vistodecimaazul.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    37 => [
        'titulo' => 'Seção 37',
        'nome' => 'Padrões de Projeto Parte 2',
        'descricao' => 'Módulo focado em Padrões de Projeto Parte 2.',
        'cor' => '#ff4b4b',
        'mascote' => 'vistodecimarosa.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    38 => [
        'titulo' => 'Seção 38',
        'nome' => 'Projeto Gerenciamento de Biblioteca',
        'descricao' => 'Módulo focado em Projeto Gerenciamento de Biblioteca.',
        'cor' => '#ce82ff',
        'mascote' => 'vistodecimaroxo.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    39 => [
        'titulo' => 'Seção 39',
        'nome' => 'Desafio Final',
        'descricao' => 'Módulo focado em Desafio Final.',
        'cor' => '#58cc02',
        'mascote' => 'vistodecimaverde.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
    40 => [
        'titulo' => 'Seção 40',
        'nome' => 'Certificado de tudo',
        'descricao' => 'Módulo focado em Certificado de tudo.',
        'cor' => '#ffc800',
        'mascote' => 'vistodecimalaranja.png',
        'guia_texto' => 'Acesse as lições desta seção para testar e expandir seu conhecimento de programação!',
        'guia_codigo' => '// Bons estudos!<br>System.out.println(\"Opus Learning\");',
        'licoes' => [1, 2, 3, 4, 5]
    ],
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
    <link rel="stylesheet" href="../assets/css/dashboard.css?v=2">
    <link rel="stylesheet" href="../assets/css/topbar.css">
    <link rel="shortcut icon" href="../assets/img/logo.png">
    
    <link rel="stylesheet" href="../assets/css/dashboard-page.css">
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
                            <div class="capitulo-container <?php echo ($status_cap_banco === 'corrente' ? 'current-chapter' : ''); ?>" id="capitulo_<?php echo $num_cap; ?>" data-capitulo="<?php echo $num_cap; ?>">
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
                                    <?php $total_mods = count($info['licoes']); for ($mod = 1; $mod <= $total_mods; $mod++): 
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
                                            <a href="<?php echo $url_destino; ?>" class="modulo-node current <?php echo $pos_class; ?>" id="currentLessonNode" data-capitulo="<?php echo $num_cap; ?>" data-licao="<?php echo $mod; ?>">
                                                <div class="start-balloon" style="background-color: <?php echo $cor_unidade; ?>; --balloon-color: <?php echo $cor_unidade; ?>;">
                                                    <?php echo $vidas_atual <= 0 ? 'SEM VIDAS' : 'COMEÇAR'; ?>
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
                            <h3><i class="fa-solid fa-fire-flame-curved" <?php if(!$praticou_hoje) echo 'style="color: #999;"'; else echo 'style="color: #ff9600;"'; ?>></i> Ofensiva</h3>
                            <span style="font-size: 0.85rem; color: <?php echo $praticou_hoje ? '#ff9600' : '#999'; ?>; font-weight: 800;">
                                <?php echo $dias_fogo; ?> <?= $dias_fogo === 1 ? 'dia' : 'dias'; ?>
                            </span>
                        </div>
                        <div class="ofensiva-body <?php echo $praticou_hoje ? '' : 'fogo-inativo'; ?>">
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
                            <span><i class="fa-solid fa-bolt" style="color: #ffc800;"></i> <strong><?php echo number_format($xp_semana_usuario, 0, ',', '.'); ?> XP</strong> neste mês</span>
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

    <!-- Modal de Aviso Personalizado para Baú Coletado/Trancado -->
    <div id="modalAvisoBau" class="modal-overlay">
        <div class="modal-bau-box" style="max-width: 380px; padding: 30px 24px;">
            <button class="close-btn-modal" onclick="fecharAvisoBau()"><i class="fa-solid fa-xmark"></i></button>
            <div class="modal-bau-icon-wrapper" id="avisoBauIconBox" style="width: 80px; height: 80px; font-size: 36px; margin-bottom: 18px;">
                <i class="fa-solid fa-lock" id="avisoBauIcone"></i>
            </div>
            <h2 class="modal-bau-title" id="avisoBauTitulo" style="font-size: 1.35rem;">Baú Trancado</h2>
            <p style="color: #b0b0c2; font-size: 0.92rem; line-height: 1.5; margin: 12px 0 22px 0;" id="avisoBauTexto">
                Complete mais lições para desbloquear este baú.
            </p>
            <button class="btn-claim-chest" onclick="fecharAvisoBau()" style="background: #1cb0f6; border-bottom-color: #148bc4; padding: 14px;">
                ENTENDIDO
            </button>
        </div>
    </div>

    <!-- BOTÃO FLUTUANTE COM FORMATO DE SETA PARA ROLAGEM DIRETA À LIÇÃO ATUAL -->
    <button class="btn-scroll-to-lesson" id="btnScrollToLesson" onclick="rolarAteLicaoAtual()" title="Ir direto para a lição atual na trilha">
        <i class="fa-solid fa-arrow-right"></i>
    </button>

    <script src="../assets/js/script.js"></script> 
    
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>

