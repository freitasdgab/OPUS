<?php
/**
 * Migration & Seed para a Seção 2 (Variáveis Parte 1 em Java)
 * Insere 5 lições estruturadas com 6 perguntas por módulo (Lições 1 a 4)
 * e configura a Lição 5 como o Laboratório/Desafio Prático na IDE estilo Codédex.
 */

require_once __DIR__ . '/conexao.php';

echo "=== ATUALIZANDO SEÇÃO 2 (VARIÁVEIS PARTE 1) COM 6 PERGUNTAS POR MÓDULO ===\n";

// 1. Atualizar ou garantir as 5 Lições da Unidade 2
$licoes_dados = [
    1 => [
        'titulo' => 'Variáveis - O Tipo Inteiro (int)',
        'texto_explicativo' => "Na programação, variáveis são como caixas na memória do computador onde guardamos informações para usar mais tarde.\n\nO tipo int (inteiro) serve para armazenar números inteiros, ou seja, números sem casas decimais (como 10, 42, -5 e 0).\n\nPara criar uma variável inteira, informamos o tipo int, o nome da variável, o sinal de igual (=) e o valor finalizado com ponto e vírgula (;).\n\nPara exibir o valor guardado na variável, passamos seu nome diretamente em System.out.println(pontos); sem aspas!",
        'codigo_exemplo' => "int pontos = 100;\nSystem.out.println(pontos);"
    ],
    2 => [
        'titulo' => 'Variáveis - Textos e a Classe String',
        'texto_explicativo' => "Enquanto o tipo int guarda números inteiros, palavras e textos são representados em Java pelo tipo String.\n\nEm Java, a palavra String começa obrigatoriamente com 'S' maiúsculo, pois tecnicamente ela é uma classe nativa da linguagem.\n\nO conteúdo de uma String deve ficar sempre delimitado por aspas duplas (\"...\"). Por exemplo: String jogador = \"Gabriel\";\n\nAssim como os números inteiros, para exibir a variável no console basta passar seu nome sem aspas para o comando de impressão.",
        'codigo_exemplo' => "String heroi = \"Link\";\nSystem.out.println(heroi);"
    ],
    3 => [
        'titulo' => 'Variáveis - Concatenação de Textos (+)',
        'texto_explicativo' => "Na programação, concatenar significa juntar duas ou mais sequências de texto em uma só.\n\nEm Java, o símbolo + realiza a concatenação de textos quando pelo menos um dos valores for uma String!\n\nPodemos combinar um texto fixo com uma variável: System.out.println(\"Olá, \" + nome);\n\nTambém podemos juntar textos com variáveis numéricas. Preste sempre atenção nos espaços dentro das aspas para não colar as palavras!",
        'codigo_exemplo' => "String curso = \"Java\";\nint ano = 2026;\nSystem.out.println(\"Estudando \" + curso + \" em \" + ano);"
    ],
    4 => [
        'titulo' => 'Variáveis - Reatribuição e Atualização',
        'texto_explicativo' => "O nome 'variável' existe justamente porque seu valor pode variar ao longo da execução do programa!\n\nPara atualizar o valor de uma variável que já existe, NÃO devemos repetir o tipo (int ou String). Apenas usamos seu nome e o novo valor.\n\nSe você repetir o tipo (ex: int vidas = 2; novamente), o compilador acusará um erro de variável já declarada no mesmo bloco.\n\nO programa executa de cima para baixo. Portanto, a variável terá o valor da sua atribuição mais recente.",
        'codigo_exemplo' => "int moedas = 10;\nmoedas = 25;\nSystem.out.println(moedas);"
    ],
    5 => [
        'titulo' => 'Variáveis - Desafio IDE: Declarando Variáveis',
        'texto_explicativo' => "Chegou a hora de colocar o conhecimento em prática no editor de código real!\n\nNeste desafio prático no estilo Codédex, você declarará uma variável inteira e exibirá seu valor no terminal.\n\nEscreva a instrução para declarar a variável e execute o programa para ver o resultado!",
        'codigo_exemplo' => ""
    ],
];

foreach ($licoes_dados as $licao_num => $dados) {
    // Garante que a lição existe
    $chk = $conn->prepare("SELECT id FROM licoes WHERE unidade_numero = 2 AND licao_numero = ?");
    $chk->bind_param("i", $licao_num);
    $chk->execute();
    $res_chk = $chk->get_result();

    if ($res_chk->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE licoes SET titulo = ?, texto_explicativo = ?, codigo_exemplo = ? WHERE unidade_numero = 2 AND licao_numero = ?");
        $stmt->bind_param("sssi", $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo'], $licao_num);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES (2, ?, ?, ?, ?)");
        $stmt->bind_param("isss", $licao_num, $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo']);
        $stmt->execute();
    }
    echo "Lição 2.$licao_num atualizada/garantida.\n";
}

// 2. Mapear IDs das lições da Unidade 2
$res_lic_ids = $conn->query("SELECT id, licao_numero FROM licoes WHERE unidade_numero = 2 ORDER BY licao_numero ASC");
$ids_map = [];
while ($r = $res_lic_ids->fetch_assoc()) {
    $ids_map[$r['licao_numero']] = (int) $r['id'];
}

// Limpa perguntas antigas da Seção 2 para reinserir com o currículo de 6 perguntas por módulo
$conn->query("DELETE FROM perguntas WHERE licao_id IN (" . implode(',', array_values($ids_map)) . ")");
echo "Perguntas anteriores da Seção 2 limpas com sucesso.\n";

// 3. Currículo Didático de Fixação Repetitiva - 6 Questões por Módulo (Sem entregar respostas no enunciado)
$perguntas_secao_2 = [
    // =========================================================================
    // LIÇÃO 1 (Módulo 1): O Tipo Inteiro (int) (6 questões)
    // =========================================================================
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que é uma variável na programação Java?',
        'codigo_contexto' => "// Conceito fundamental de programação",
        'alt_a' => 'Um espaço na memória com nome usado para armazenar dados',
        'alt_b' => 'Um comando que desliga o terminal do computador',
        'alt_c' => 'Um arquivo que só pode ser aberto através da internet',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual tipo primitivo em Java é utilizado para armazenar números inteiros?',
        'codigo_contexto' => "// 10, 20, 30...",
        'alt_a' => 'int',
        'alt_b' => 'text',
        'alt_c' => 'number_full',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução para declarar a variável inteira:',
        'codigo_contexto' => "___ vidas = 3;",
        'alt_a' => 'int',
        'alt_b' => 'integer',
        'alt_c' => 'INT',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que o código abaixo enviará para o terminal do computador?',
        'codigo_contexto' => "int nivel = 5;\nSystem.out.println(nivel);",
        'alt_a' => '5',
        'alt_b' => '"nivel"',
        'alt_c' => 'Um erro de compilação',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual é a diferença entre System.out.println(idade); e System.out.println("idade");?',
        'codigo_contexto' => "// Com aspas vs sem aspas",
        'alt_a' => 'Sem aspas imprime o valor da variável; com aspas imprime o texto literal "idade"',
        'alt_b' => 'Não existe diferença alguma na saída do programa',
        'alt_c' => 'Sem aspas causa um erro obrigatório no compilador',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução para exibir o valor armazenado na variável moedas:',
        'codigo_contexto' => "int moedas = 50;\nSystem.out.println(___);",
        'alt_a' => 'moedas',
        'alt_b' => '"moedas"',
        'alt_c' => '50 = moedas',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 2 (Módulo 2): Textos e a Classe String (6 questões)
    // =========================================================================
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual tipo de dado em Java é utilizado para armazenar palavras e frases?',
        'codigo_contexto' => "// Ex: \"Olá, Jogador!\"",
        'alt_a' => 'String',
        'alt_b' => 'str',
        'alt_c' => 'text',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete com o tipo de dado para criar a variável de texto:',
        'codigo_contexto' => "___ cargo = \"Desenvolvedor\";",
        'alt_a' => 'String',
        'alt_b' => 'string',
        'alt_c' => 'STRING',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual declaração de variável de texto segue as regras da linguagem Java?',
        'codigo_contexto' => "// Declaração de texto",
        'alt_a' => 'String cidade = "Curitiba";',
        'alt_b' => 'String cidade = Curitiba;',
        'alt_c' => 'string cidade = "Curitiba";',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o valor da variável para armazenar o nome Opus:',
        'codigo_contexto' => "String app = ___;",
        'alt_a' => '"Opus"',
        'alt_b' => 'Opus',
        'alt_c' => '(Opus)',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que acontecerá ao tentar compilar este código Java?',
        'codigo_contexto' => "String animal = Gato;",
        'alt_a' => 'Ocorrerá um erro de compilação por falta de aspas duplas no texto',
        'alt_b' => 'O Java colocará as aspas duplas automaticamente',
        'alt_c' => 'A palavra será convertida para número',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Verdadeiro ou Falso? O nome do tipo String em Java deve iniciar obrigatoriamente com letra maiúscula.',
        'codigo_contexto' => "String nick = \"Player1\";",
        'alt_a' => 'Verdadeiro',
        'alt_b' => 'Falso',
        'alt_c' => 'Apenas em programas para web',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 3 (Módulo 3): Concatenação de Textos com + (6 questões)
    // =========================================================================
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Em Java, quando usamos o operador + envolvendo um texto, qual operação é realizada?',
        'codigo_contexto' => "\"Pontos: \" + 50",
        'alt_a' => 'Concatenação (junção de textos)',
        'alt_b' => 'Soma matemática dos caracteres',
        'alt_c' => 'Divisão de strings',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual será a saída exibida no terminal após executar este código?',
        'codigo_contexto' => "String jogo = \"Xadrez\";\nSystem.out.println(\"Partida de \" + jogo);",
        'alt_a' => 'Partida de Xadrez',
        'alt_b' => 'Partida de jogo',
        'alt_c' => '"Partida de Xadrez"',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução para juntar a saudação com o nome da variável:',
        'codigo_contexto' => "String nick = \"Alex\";\nSystem.out.println(\"Olá, \" ___ nick);",
        'alt_a' => '+',
        'alt_b' => '&',
        'alt_c' => '.',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual será o resultado exato da impressão abaixo?',
        'codigo_contexto' => "String cor = \"Azul\";\nSystem.out.println(\"Cor:\" + cor);",
        'alt_a' => 'Cor:Azul',
        'alt_b' => 'Cor: Azul',
        'alt_c' => 'Cor Azul',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete com a variável para exibir "Nível 10" no terminal:',
        'codigo_contexto' => "int level = 10;\nSystem.out.println(\"Nível \" + ___);",
        'alt_a' => 'level',
        'alt_b' => '"level"',
        'alt_c' => '10',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que será exibido no terminal ao executar este comando?',
        'codigo_contexto' => "int a = 1;\nint b = 2;\nSystem.out.println(\"Total: \" + a + b);",
        'alt_a' => 'Total: 12',
        'alt_b' => 'Total: 3',
        'alt_c' => 'Total: a + b',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 4 (Módulo 4): Reatribuição e Atualização (6 questões)
    // =========================================================================
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Para atualizar o valor de uma variável já criada anteriormente, como devemos proceder?',
        'codigo_contexto' => "// Atualização de variável",
        'alt_a' => 'Escrever apenas o nome da variável e o novo valor, sem repetir o tipo',
        'alt_b' => 'Repetir obrigatoriamente o tipo da variável antes do nome',
        'alt_c' => 'Deletar a variável da memória e recriar com outro nome',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual valor será exibido no terminal após executar este código?',
        'codigo_contexto' => "int score = 50;\nscore = 80;\nSystem.out.println(score);",
        'alt_a' => '80',
        'alt_b' => '50',
        'alt_c' => '130',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que acontecerá ao compilar o trecho de código abaixo?',
        'codigo_contexto' => "int vidas = 3;\nint vidas = 2;",
        'alt_a' => 'Ocorrerá um erro porque a variável já foi declarada no mesmo bloco',
        'alt_b' => 'O Java atualizará o valor normalmente para 2',
        'alt_c' => 'As vidas serão somadas automaticamente para 5',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução para atualizar a variável status para "Jogando":',
        'codigo_contexto' => "String status = \"Online\";\n___ = \"Jogando\";",
        'alt_a' => 'status',
        'alt_b' => 'String status',
        'alt_c' => 'status.set',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual será o valor final impresso na tela do terminal?',
        'codigo_contexto' => "int x = 10;\nx = 20;\nx = 30;\nSystem.out.println(x);",
        'alt_a' => '30',
        'alt_b' => '10',
        'alt_c' => '20',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a reatribuição da variável para atualizar o saldo para 150:',
        'codigo_contexto' => "int saldo = 100;\nsaldo = ___;",
        'alt_a' => '150',
        'alt_b' => 'int 150',
        'alt_c' => '"150"',
        'correta' => 'A'
    ],
];

// Inserir perguntas no banco
$stmt_insert = $conn->prepare("INSERT INTO perguntas (licao_id, pergunta_texto, codigo_contexto, tipo, alternativa_a, alternativa_b, alternativa_c, alternativa_correta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($perguntas_secao_2 as $p) {
    $lid = $ids_map[$p['licao']];
    $ctx = $p['codigo_contexto'] ?? null;
    $stmt_insert->bind_param("isssssss", $lid, $p['pergunta'], $ctx, $p['tipo'], $p['alt_a'], $p['alt_b'], $p['alt_c'], $p['correta']);
    $stmt_insert->execute();
}
echo "Total de " . count($perguntas_secao_2) . " perguntas cadastradas na Seção 2 (6 perguntas em cada lição de 1 a 4)!\n";

// 4. Configuração de progresso para testes (Seção 1 concluída, Seção 2 corrente)
echo "\n=== CONFIGURANDO PROGRESSO PARA TESTE DA SEÇÃO 2 ===\n";
$usuarios_teste = [1, 24];
foreach ($usuarios_teste as $uid) {
    $conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) 
                  VALUES ($uid, 1, 'completo', 5) 
                  ON DUPLICATE KEY UPDATE status = 'completo', licoes_concluidas = 5");

    $conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) 
                  VALUES ($uid, 2, 'corrente', 0) 
                  ON DUPLICATE KEY UPDATE status = 'corrente', licoes_concluidas = 0");
    echo "Usuário ID $uid configurado: Seção 1 Completa e Seção 2 Corrente!\n";
}

echo "\n=== SEÇÃO 2 (VARIÁVEIS PARTE 1) CONFIGURADA COM SUCESSO! ===\n";
