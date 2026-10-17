<?php
/**
 * Migration & Seed para a Seção 3 (Variáveis Parte 2 em Java)
 * Insere 5 lições estruturadas com 6 perguntas por módulo (Lições 1 a 4)
 * e configura a Lição 5 como o Laboratório/Desafio Prático na IDE estilo Codédex.
 */

require_once __DIR__ . '/conexao.php';

echo "=== ATUALIZANDO SEÇÃO 3 (VARIÁVEIS PARTE 2) COM 6 PERGUNTAS POR MÓDULO ===\n";

$licoes_dados = [
    1 => [
        'titulo' => 'Variáveis Parte 2 - Números Decimais (double)',
        'texto_explicativo' => "No mundo real, nem todos os números são inteiros. Preços de produtos, alturas, pesos e médias escolares possuem casas decimais.\n\nEm Java, o tipo primitivo padrão para guardar números com casas decimais é o double.\n\nAtenção à regra fundamental de sintaxe: em Java, usamos sempre o PONTO (.) e nunca a vírgula para separar as casas decimais (ex: double preco = 19.90;).\n\nSe usarmos vírgula em um número decimal no código, o compilador do Java acusará um erro de sintaxe!",
        'codigo_exemplo' => "double altura = 1.75;\ndouble preco = 29.99;\nSystem.out.println(preco);"
    ],
    2 => [
        'titulo' => 'Variáveis Parte 2 - Lógica com boolean',
        'texto_explicativo' => "Muitas vezes um programa precisa responder perguntas de Sim ou Não: O usuário está online? A porta está aberta? O aluno foi aprovado?\n\nPara armazenar valores lógicos em Java, utilizamos o tipo primitivo boolean.\n\nUma variável boolean aceita exclusivamente um de dois valores possíveis: true (verdadeiro) ou false (falso).\n\nEsses valores são palavras reservadas da linguagem Java: são escritos sempre em letras minúsculas e NUNCA levam aspas!",
        'codigo_exemplo' => "boolean aprovado = true;\nboolean chovendo = false;\nSystem.out.println(aprovado);"
    ],
    3 => [
        'titulo' => 'Variáveis Parte 2 - Caracteres Únicos (char)',
        'texto_explicativo' => "Enquanto o tipo String armazena palavras e frases inteiras usando aspas duplas (\"...\"), o Java possui um tipo especial para um único caractere: o char.\n\nUma variável do tipo char armazena exatamente uma única letra, símbolo ou dígito (como 'A', 'B', '$' ou '7').\n\nA regra de ouro do tipo char é o uso obrigatório de ASPAS SIMPLES (' ') ao redor do caractere!\n\nLembre-se: aspas simples ('A') são para char (um só caractere), e aspas duplas (\"Texto\") são para String!",
        'codigo_exemplo' => "char rank = 'S';\nchar inicial = 'G';\nSystem.out.println(rank);"
    ],
    4 => [
        'titulo' => 'Variáveis Parte 2 - Constantes com final',
        'texto_explicativo' => "Existem valores que nunca devem mudar durante toda a execução de um programa, como o número Pi (3.14159) ou o limite máximo de jogadores.\n\nEm Java, usamos a palavra-chave final antes do tipo da variável para transformá-la em uma constante (um valor fixo e imutável).\n\nSe qualquer linha do código tentar alterar o valor de uma variável marcada com final, o compilador bloqueará a ação com um erro!\n\nPor convenção entre programadores Java, costumamos escrever o nome de constantes em LETRAS MAIÚSCULAS (ex: final int MAX_VIDAS = 3;).",
        'codigo_exemplo' => "final double PI = 3.14;\nfinal int MAX_NIVEL = 99;\nSystem.out.println(MAX_NIVEL);"
    ],
    5 => [
        'titulo' => 'Variáveis Parte 2 - Desafio IDE: Tipos de Dados',
        'texto_explicativo' => "Chegou a hora de colocar o conhecimento sobre novos tipos de variáveis em prática no editor de código real!\n\nNeste desafio prático no estilo Codédex, você declarará uma variável decimal (double) e exibirá seu valor no terminal.\n\nEscreva a instrução no editor e clique em Executar para concluir a Seção 3!",
        'codigo_exemplo' => ""
    ],
];

foreach ($licoes_dados as $licao_num => $dados) {
    $chk = $conn->prepare("SELECT id FROM licoes WHERE unidade_numero = 3 AND licao_numero = ?");
    $chk->bind_param("i", $licao_num);
    $chk->execute();
    $res_chk = $chk->get_result();

    if ($res_chk->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE licoes SET titulo = ?, texto_explicativo = ?, codigo_exemplo = ? WHERE unidade_numero = 3 AND licao_numero = ?");
        $stmt->bind_param("sssi", $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo'], $licao_num);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES (3, ?, ?, ?, ?)");
        $stmt->bind_param("isss", $licao_num, $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo']);
        $stmt->execute();
    }
    echo "Lição 3.$licao_num atualizada/garantida.\n";
}

$res_lic_ids = $conn->query("SELECT id, licao_numero FROM licoes WHERE unidade_numero = 3 ORDER BY licao_numero ASC");
$ids_map = [];
while ($r = $res_lic_ids->fetch_assoc()) {
    $ids_map[$r['licao_numero']] = (int) $r['id'];
}

$conn->query("DELETE FROM perguntas WHERE licao_id IN (" . implode(',', array_values($ids_map)) . ")");
echo "Perguntas anteriores da Seção 3 limpas com sucesso.\n";

$perguntas_secao_3 = [
    // =========================================================================
    // LIÇÃO 1 (Módulo 1): Números Decimais (double) (6 questões)
    // =========================================================================
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual tipo primitivo em Java é ideal para armazenar números com casas decimais, como preços e alturas?',
        'codigo_contexto' => "// Ex: 1.75, 19.90, 3.14",
        'alt_a' => 'double',
        'alt_b' => 'int',
        'alt_c' => 'boolean',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual símbolo deve ser utilizado obrigatoriamente para separar as casas decimais de um double em Java?',
        'codigo_contexto' => "// Separador decimal no código-fonte",
        'alt_a' => 'Ponto (.)',
        'alt_b' => 'Vírgula (,)',
        'alt_c' => 'Dois pontos (:)',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a declaração da variável para armazenar a altura com casas decimais:',
        'codigo_contexto' => "___ altura = 1.82;",
        'alt_a' => 'double',
        'alt_b' => 'int',
        'alt_c' => 'String',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual destas declarações de variável decimal compila sem erros em Java?',
        'codigo_contexto' => "// Escolha a sintaxe válida",
        'alt_a' => 'double nota = 9.5;',
        'alt_b' => 'double nota = 9,5;',
        'alt_c' => 'int nota = 9.5;',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete com o valor decimal em formato válido do Java para representar doze e cinquenta:',
        'codigo_contexto' => "double valor = ___;",
        'alt_a' => '12.50',
        'alt_b' => '12,50',
        'alt_c' => '"12,50"',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que será exibido no console ao executar este código?',
        'codigo_contexto' => "double peso = 68.4;\nSystem.out.println(\"Peso: \" + peso);",
        'alt_a' => 'Peso: 68.4',
        'alt_b' => 'Peso: 68',
        'alt_c' => 'Peso: peso',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 2 (Módulo 2): Lógica com boolean (6 questões)
    // =========================================================================
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Quais são os dois únicos valores que uma variável do tipo boolean pode armazenar em Java?',
        'codigo_contexto' => "// Valores lógicos em Java",
        'alt_a' => 'true e false',
        'alt_b' => 'sim e nao',
        'alt_c' => 'TRUE e FALSE em maiúsculas',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete com o tipo correto para declarar uma variável lógica:',
        'codigo_contexto' => "___ conectado = true;",
        'alt_a' => 'boolean',
        'alt_b' => 'bool',
        'alt_c' => 'BooleanText',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Por que o código abaixo causará um erro de compilação em Java?',
        'codigo_contexto' => "boolean ativo = \"true\";",
        'alt_a' => 'Porque valores booleanos não devem ter aspas duplas (com aspas vira String)',
        'alt_b' => 'Porque a palavra boolean deve começar com B maiúsculo',
        'alt_c' => 'Porque falta usar vírgula no final da linha',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a variável lógica para indicar que o jogo NÃO está pausado (falso):',
        'codigo_contexto' => "boolean pausado = ___;",
        'alt_a' => 'false',
        'alt_b' => '"false"',
        'alt_c' => 'FALSE',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual será a saída exibida no terminal após executar o trecho abaixo?',
        'codigo_contexto' => "boolean chave = false;\nchave = true;\nSystem.out.println(chave);",
        'alt_a' => 'true',
        'alt_b' => 'false',
        'alt_c' => 'chave',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução para exibir o estado da variável vip no console:',
        'codigo_contexto' => "boolean vip = true;\nSystem.out.println(___);",
        'alt_a' => 'vip',
        'alt_b' => '"vip"',
        'alt_c' => 'boolean',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 3 (Módulo 3): Caracteres Únicos (char) (6 questões)
    // =========================================================================
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual tipo primitivo do Java é utilizado para guardar apenas UM único caractere entre aspas simples?',
        'codigo_contexto' => "// Ex: 'A', 'B', '?'",
        'alt_a' => 'char',
        'alt_b' => 'String',
        'alt_c' => 'letter',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a declaração para armazenar a letra inicial usando o tipo de caractere único:',
        'codigo_contexto' => "___ letra = 'J';",
        'alt_a' => 'char',
        'alt_b' => 'String',
        'alt_c' => 'int',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual é a diferença de pontuação entre um literal char e um literal String em Java?',
        'codigo_contexto' => "char c = 'A';\nString s = \"A\";",
        'alt_a' => 'char usa aspas simples (\' \') e String usa aspas duplas (" ")',
        'alt_b' => 'Ambos usam apenas aspas simples',
        'alt_c' => 'char não precisa de nenhum tipo de aspas',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o valor da variável char para guardar o conceito S:',
        'codigo_contexto' => "char rank = ___;",
        'alt_a' => "'S'",
        'alt_b' => '"S"',
        'alt_c' => 'S',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que acontecerá se tentarmos guardar uma palavra inteira dentro de um char?',
        'codigo_contexto' => "char turma = 'Java';",
        'alt_a' => 'Erro de compilação, pois char comporta apenas um único caractere',
        'alt_b' => 'O Java guardará apenas a primeira letra automaticamente',
        'alt_c' => 'O tipo mudará sozinho para String',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual será a saída exibida no terminal ao executar este código?',
        'codigo_contexto' => "char grupo = 'B';\nSystem.out.println(\"Turma \" + grupo);",
        'alt_a' => 'Turma B',
        'alt_b' => 'Turma \'B\'',
        'alt_c' => 'Turma grupo',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 4 (Módulo 4): Constantes com final (6 questões)
    // =========================================================================
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual palavra-chave em Java impede que o valor de uma variável seja alterado depois de criado?',
        'codigo_contexto' => "// Valor constante e imutável",
        'alt_a' => 'final',
        'alt_b' => 'const',
        'alt_c' => 'fixed',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a declaração para tornar o limite de vidas uma constante imutável:',
        'codigo_contexto' => "___ int MAX_VIDAS = 5;",
        'alt_a' => 'final',
        'alt_b' => 'static_const',
        'alt_c' => 'lock',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que acontecerá ao tentar compilar o código abaixo?',
        'codigo_contexto' => "final int LIMITE = 100;\nLIMITE = 200;",
        'alt_a' => 'Erro de compilação, pois não é permitido reatribuir valor a uma variável final',
        'alt_b' => 'A constante LIMITE será atualizada para 200',
        'alt_c' => 'O valor passará a ser 300',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual é a convenção de nomenclatura recomendada em Java para nomear constantes (final)?',
        'codigo_contexto' => "final double TAXA_JUROS = 0.05;",
        'alt_a' => 'Letras todas maiúsculas separadas por underline (UPPER_SNAKE_CASE)',
        'alt_b' => 'Começar obrigatoriamente com números',
        'alt_c' => 'Usar espaços em branco entre as palavras',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a constante decimal para armazenar o valor aproximado de PI:',
        'codigo_contexto' => "final ___ PI = 3.14;",
        'alt_a' => 'double',
        'alt_b' => 'int',
        'alt_c' => 'boolean',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual será o resultado impresso no terminal pelo código abaixo?',
        'codigo_contexto' => "final int BONUS = 50;\nSystem.out.println(\"Bônus: \" + BONUS);",
        'alt_a' => 'Bônus: 50',
        'alt_b' => 'Bônus: BONUS',
        'alt_c' => 'Erro, pois variáveis final não podem ser impressas',
        'correta' => 'A'
    ],
];

$stmt_insert = $conn->prepare("INSERT INTO perguntas (licao_id, pergunta_texto, codigo_contexto, tipo, alternativa_a, alternativa_b, alternativa_c, alternativa_correta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($perguntas_secao_3 as $p) {
    $lid = $ids_map[$p['licao']];
    $ctx = $p['codigo_contexto'] ?? null;
    $stmt_insert->bind_param("isssssss", $lid, $p['pergunta'], $ctx, $p['tipo'], $p['alt_a'], $p['alt_b'], $p['alt_c'], $p['correta']);
    $stmt_insert->execute();
}
echo "Total de " . count($perguntas_secao_3) . " perguntas cadastradas na Seção 3 (6 perguntas em cada lição de 1 a 4)!\n";
echo "=== SEÇÃO 3 (VARIÁVEIS PARTE 2) CONFIGURADA COM SUCESSO! ===\n";
