<?php
/**
 * Migration & Seed para as Seções 4 (Operadores Parte 1) e 5 (Operadores Parte 2)
 * Insere 5 lições estruturadas com 6 perguntas por módulo (Lições 1 a 4)
 * e configura a Lição 5 como o Laboratório/Desafio Prático na IDE estilo Codédex.
 */

require_once __DIR__ . '/conexao.php';

echo "=== ATUALIZANDO SEÇÕES 4 E 5 COM 6 PERGUNTAS POR MÓDULO ===\n";

// =========================================================================
// SEÇÃO 4: OPERADORES PARTE 1 (ARITMÉTICOS)
// =========================================================================
$licoes_s4 = [
    1 => [
        'titulo' => 'Operadores Parte 1 - Adição (+) e Subtração (-)',
        'texto_explicativo' => "Os computadores são calculadoras extremamente rápidas! Em Java, usamos os operadores aritméticos para realizar cálculos matemáticos.\n\nO operador + soma dois valores numéricos, enquanto o operador - realiza a subtração.\n\nPodemos fazer a conta diretamente dentro do println ou guardar o resultado dentro de uma variável: int total = 10 + 5;\n\nLembre-se: quando ambos os lados do + são números (int ou double), o Java realiza a SOMA matemática!",
        'codigo_exemplo' => "int saldo = 100 - 30;\nint bonus = saldo + 20;\nSystem.out.println(bonus);"
    ],
    2 => [
        'titulo' => 'Operadores Parte 1 - Multiplicação (*) e Divisão (/)',
        'texto_explicativo' => "Na programação Java, a multiplicação NÃO usa a letra 'x'. Usamos sempre o símbolo asterisco (*).\n\nJá para a divisão, utilizamos a barra normal (/). Por exemplo: int metade = 20 / 2;\n\nAtenção a um detalhe importante do Java: quando dividimos dois números inteiros (int), o resultado também será inteiro (a parte decimal é descartada).\n\nPor exemplo, 7 / 2 entre inteiros resulta em 3. Se quisermos casas decimais, pelo menos um número deve ser double (ex: 7.0 / 2).",
        'codigo_exemplo' => "int dobro = 6 * 2;\nint metade = 10 / 2;\nSystem.out.println(dobro);"
    ],
    3 => [
        'titulo' => 'Operadores Parte 1 - Resto da Divisão (%)',
        'texto_explicativo' => "Existe um operador matemático muito útil na programação chamado Módulo, representado pelo símbolo de porcentagem (%).\n\nO operador % NÃO calcula porcentagem! Ele retorna o RESTO de uma divisão inteira.\n\nPor exemplo: na divisão de 10 por 3, o quociente é 3 e sobra 1. Portanto, 10 % 3 resulta em 1!\n\nQuando um número é perfeitamente divisível por outro, o resto é zero (ex: 8 % 2 resulta em 0). É assim que descobrimos se um número é par ou ímpar!",
        'codigo_exemplo' => "int sobra = 10 % 3;\nint par = 8 % 2;\nSystem.out.println(sobra);"
    ],
    4 => [
        'titulo' => 'Operadores Parte 1 - Incremento (++) e Precedência',
        'texto_explicativo' => "Somar ou diminuir 1 de uma variável é tão comum em jogos e programas que o Java possui atalhos especiais!\n\nO operador ++ aumenta o valor da variável em 1 unidade (ex: vidas++; é o mesmo que vidas = vidas + 1;).\n\nJá o operador -- diminui o valor da variável em 1 unidade.\n\nAlém disso, assim como na matemática da escola, a multiplicação (*) e a divisão (/) são calculadas ANTES da soma (+) e subtração (-), a menos que usemos parênteses ( )!",
        'codigo_exemplo' => "int nivel = 1;\nnivel++;\nint pontos = (2 + 3) * 10;\nSystem.out.println(pontos);"
    ],
    5 => [
        'titulo' => 'Operadores Parte 1 - Desafio IDE: Calculadora',
        'texto_explicativo' => "Chegou a hora de programar sua própria expressão aritmética no editor de código Java!\n\nNeste desafio prático no estilo Codédex, você somará dois números inteiros em uma variável e exibirá o resultado 40 no terminal.\n\nDigite seu código e clique em Executar!",
        'codigo_exemplo' => ""
    ],
];

foreach ($licoes_s4 as $licao_num => $dados) {
    $chk = $conn->prepare("SELECT id FROM licoes WHERE unidade_numero = 4 AND licao_numero = ?");
    $chk->bind_param("i", $licao_num);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE licoes SET titulo = ?, texto_explicativo = ?, codigo_exemplo = ? WHERE unidade_numero = 4 AND licao_numero = ?");
        $stmt->bind_param("sssi", $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo'], $licao_num);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES (4, ?, ?, ?, ?)");
        $stmt->bind_param("isss", $licao_num, $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo']);
        $stmt->execute();
    }
}

$res_s4 = $conn->query("SELECT id, licao_numero FROM licoes WHERE unidade_numero = 4 ORDER BY licao_numero ASC");
$map_s4 = [];
while ($r = $res_s4->fetch_assoc()) $map_s4[$r['licao_numero']] = (int)$r['id'];
$conn->query("DELETE FROM perguntas WHERE licao_id IN (" . implode(',', array_values($map_s4)) . ")");

$perguntas_s4 = [
    // Lição 1
    ['licao'=>1, 'tipo'=>'multipla_escolha', 'pergunta'=>'Quando usamos o operador + entre dois números inteiros em Java, o que acontece?', 'codigo_contexto'=>'int res = 15 + 5;', 'alt_a'=>'Os números são somados matematicamente (resultado 20)', 'alt_b'=>'Ocorre um erro porque falta texto', 'alt_c'=>'Os números são apagados', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete a operação para somar os dois valores:', 'codigo_contexto'=>'int soma = 30 ___ 10;', 'alt_a'=>'+', 'alt_b'=>'plus', 'alt_c'=>'&', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o valor exibido no terminal pelo código abaixo?', 'codigo_contexto'=>"int energia = 100 - 25;\nSystem.out.println(energia);", 'alt_a'=>'75', 'alt_b'=>'125', 'alt_c'=>'100 - 25', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete a expressão para descontar 10 moedas do saldo:', 'codigo_contexto'=>"int saldo = 50;\nint novoSaldo = saldo ___ 10;", 'alt_a'=>'-', 'alt_b'=>'less', 'alt_c'=>'/', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'multipla_escolha', 'pergunta'=>'O que será impresso na tela após executar estas instruções?', 'codigo_contexto'=>"int a = 10;\nint b = 20;\nSystem.out.println(a + b);", 'alt_a'=>'30', 'alt_b'=>'1020', 'alt_c'=>'a + b', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o nome da variável para exibir o resultado da soma:', 'codigo_contexto'=>"int total = 8 + 4;\nSystem.out.println(___);", 'alt_a'=>'total', 'alt_b'=>'"total"', 'alt_c'=>'8', 'correta'=>'A'],

    // Lição 2
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual símbolo é utilizado em Java para realizar a operação de multiplicação?', 'codigo_contexto'=>'// Multiplicação em Java', 'alt_a'=>'Asterisco (*)', 'alt_b'=>'Letra x', 'alt_c'=>'Ponto (.)', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete a instrução para multiplicar o valor por 3:', 'codigo_contexto'=>'int triplo = 10 ___ 3;', 'alt_a'=>'*', 'alt_b'=>'x', 'alt_c'=>'^', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual símbolo representa a divisão em Java?', 'codigo_contexto'=>'int metade = 100 / 2;', 'alt_a'=>'Barra (/)', 'alt_b'=>'Dois pontos (:)', 'alt_c'=>'Porcentagem (%)', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete o código para dividir os 50 pontos igualmente por 2:', 'codigo_contexto'=>'int parte = 50 ___ 2;', 'alt_a'=>'/', 'alt_b'=>':', 'alt_c'=>'\\', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'Em Java, qual é o resultado da divisão inteira 7 / 2 (quando ambos são int)?', 'codigo_contexto'=>"int r = 7 / 2;\nSystem.out.println(r);", 'alt_a'=>'3 (pois a parte decimal é descartada na divisão entre inteiros)', 'alt_b'=>'3.5', 'alt_c'=>'4', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o valor final exibido no console?', 'codigo_contexto'=>"int largura = 4;\nint altura = 5;\nSystem.out.println(largura * altura);", 'alt_a'=>'20', 'alt_b'=>'9', 'alt_c'=>'45', 'correta'=>'A'],

    // Lição 3
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'O que o operador módulo (%) calcula em Java?', 'codigo_contexto'=>'int r = 10 % 3;', 'alt_a'=>'O resto de uma divisão inteira', 'alt_b'=>'A porcentagem de um número', 'alt_c'=>'A multiplicação por 100', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual é o resultado da operação 10 % 3 em Java?', 'codigo_contexto'=>"int resto = 10 % 3;\nSystem.out.println(resto);", 'alt_a'=>'1 (pois 10 dividido por 3 dá 3 e sobra 1)', 'alt_b'=>'3', 'alt_c'=>'0', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador que obtém o resto da divisão:', 'codigo_contexto'=>'int resto = 15 ___ 4;', 'alt_a'=>'%', 'alt_b'=>'/', 'alt_c'=>'*', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o resultado de 8 % 2 no terminal?', 'codigo_contexto'=>'System.out.println(8 % 2);', 'alt_a'=>'0 (pois 8 é par e a divisão por 2 é exata)', 'alt_b'=>'4', 'alt_c'=>'2', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o valor do resto esperado de 7 dividido por 2:', 'codigo_contexto'=>"// 7 dividido por 2 dá 3 e sobra:\nint sobra = ___;", 'alt_a'=>'1', 'alt_b'=>'3.5', 'alt_c'=>'0', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Por que o operador % é muito usado para saber se um número x é par?', 'codigo_contexto'=>'// Verificando paridade', 'alt_a'=>'Porque todo número par dividido por 2 tem resto igual a 0 (x % 2 == 0)', 'alt_b'=>'Porque ele transforma números ímpares em textos', 'alt_c'=>'Porque ele multiplica o número por 2', 'correta'=>'A'],

    // Lição 4
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'O que a instrução pontos++; faz com a variável pontos?', 'codigo_contexto'=>"int pontos = 10;\npontos++;", 'alt_a'=>'Aumenta o valor da variável em 1 (passa a valer 11)', 'alt_b'=>'Multiplica o valor por 2', 'alt_c'=>'Zera a variável', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador de incremento para somar 1 à variável nivel:', 'codigo_contexto'=>"int nivel = 5;\nnivel___;", 'alt_a'=>'++', 'alt_b'=>'**', 'alt_c'=>'//', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o valor da variável vidas após executar este código?', 'codigo_contexto'=>"int vidas = 3;\nvidas--;\nSystem.out.println(vidas);", 'alt_a'=>'2', 'alt_b'=>'3', 'alt_c'=>'4', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'De acordo com a ordem de precedência matemática do Java, qual é o resultado de 2 + 3 * 4?', 'codigo_contexto'=>'System.out.println(2 + 3 * 4);', 'alt_a'=>'14 (pois a multiplicação 3 * 4 é feita antes da soma)', 'alt_b'=>'20', 'alt_c'=>'9', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'E qual será o resultado quando usamos parênteses em (2 + 3) * 4?', 'codigo_contexto'=>'System.out.println((2 + 3) * 4);', 'alt_a'=>'20 (pois os parênteses são resolvidos primeiro: 5 * 4)', 'alt_b'=>'14', 'alt_c'=>'24', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador de decremento para reduzir 1 unidade:', 'codigo_contexto'=>"int tentativas = 3;\ntentativas___;", 'alt_a'=>'--', 'alt_b'=>'++', 'alt_c'=>'==', 'correta'=>'A'],
];

$stmt_ins = $conn->prepare("INSERT INTO perguntas (licao_id, pergunta_texto, codigo_contexto, tipo, alternativa_a, alternativa_b, alternativa_c, alternativa_correta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($perguntas_s4 as $p) {
    $lid = $map_s4[$p['licao']];
    $stmt_ins->bind_param("isssssss", $lid, $p['pergunta'], $p['codigo_contexto'], $p['tipo'], $p['alt_a'], $p['alt_b'], $p['alt_c'], $p['correta']);
    $stmt_ins->execute();
}
echo "Seção 4 populada com " . count($perguntas_s4) . " perguntas!\n";

// =========================================================================
// SEÇÃO 5: OPERADORES PARTE 2 (RELACIONAIS E LÓGICOS)
// =========================================================================
$licoes_s5 = [
    1 => [
        'titulo' => 'Operadores Parte 2 - Maior (>) e Menor (<)',
        'texto_explicativo' => "Os operadores relacionais servem para comparar dois valores. O resultado de toda comparação em Java é sempre um boolean: true ou false!\n\nUsamos > para verificar se um valor é maior que outro, e < para verificar se é menor.\n\nTambém temos >= (maior ou igual) e <= (menor ou igual). Por exemplo: 18 >= 18 resulta em true!\n\nEssas comparações são a base para que o programa tome decisões inteligentes.",
        'codigo_exemplo' => "int idade = 20;\nboolean maiorDeIdade = idade >= 18;\nSystem.out.println(maiorDeIdade);"
    ],
    2 => [
        'titulo' => 'Operadores Parte 2 - Igualdade (==) e Diferença (!=)',
        'texto_explicativo' => "Preste muita atenção na diferença entre = e == em Java!\n\nUm único sinal de igual (=) serve para ATRIBUIR valor a uma variável (ex: int vidas = 3;).\n\nDois sinais de igual juntos (==) servem para COMPARAR se dois valores são iguais (ex: vidas == 3 resulta em true).\n\nJá o operador != verifica se dois valores são DIFERENTES (ex: 5 != 3 resulta em true, pois 5 é diferente de 3).",
        'codigo_exemplo' => "int senha = 1234;\nboolean correta = (senha == 1234);\nSystem.out.println(correta);"
    ],
    3 => [
        'titulo' => 'Operadores Parte 2 - Operador Lógico E (&&)',
        'texto_explicativo' => "Às vezes precisamos testar duas condições ao mesmo tempo. Para isso usamos os operadores lógicos!\n\nO operador && (chamado de AND ou 'E' lógico) retorna true APENAS se TODAS as condições forem verdadeiras ao mesmo tempo.\n\nPor exemplo: para entrar em um campeonato avançado, o jogador precisa ter (nivel >= 10 && moedas >= 100).\n\nSe uma das condições for false, o resultado inteiro do && será false!",
        'codigo_exemplo' => "int nivel = 12;\nint moedas = 150;\nboolean podeEntrar = (nivel >= 10) && (moedas >= 100);\nSystem.out.println(podeEntrar);"
    ],
    4 => [
        'titulo' => 'Operadores Parte 2 - OU (||) e Negação (!)',
        'texto_explicativo' => "O operador || (duas barras verticais, chamado de OR ou 'OU' lógico) retorna true se PELO MENOS UMA das condições for verdadeira.\n\nPor exemplo: (temIngresso || nomeNaLista) será true mesmo que apenas uma das duas coisas seja verdade!\n\nPor fim, o operador de negação ! (ponto de exclamação) inverte um valor booleano: !true vira false, e !false vira true.",
        'codigo_exemplo' => "boolean chovendo = false;\nboolean sairDeCasa = !chovendo;\nSystem.out.println(sairDeCasa);"
    ],
    5 => [
        'titulo' => 'Operadores Parte 2 - Desafio IDE: Lógica Booleana',
        'texto_explicativo' => "Chegou a hora de testar comparações e lógica booleana no editor Java!\n\nNeste desafio prático no estilo Codédex, avalie uma comparação verdadeira (como 10 > 5) e exiba o resultado true no terminal.\n\nEscreva seu código e clique em Executar!",
        'codigo_exemplo' => ""
    ],
];

foreach ($licoes_s5 as $licao_num => $dados) {
    $chk = $conn->prepare("SELECT id FROM licoes WHERE unidade_numero = 5 AND licao_numero = ?");
    $chk->bind_param("i", $licao_num);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE licoes SET titulo = ?, texto_explicativo = ?, codigo_exemplo = ? WHERE unidade_numero = 5 AND licao_numero = ?");
        $stmt->bind_param("sssi", $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo'], $licao_num);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES (5, ?, ?, ?, ?)");
        $stmt->bind_param("isss", $licao_num, $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo']);
        $stmt->execute();
    }
}

$res_s5 = $conn->query("SELECT id, licao_numero FROM licoes WHERE unidade_numero = 5 ORDER BY licao_numero ASC");
$map_s5 = [];
while ($r = $res_s5->fetch_assoc()) $map_s5[$r['licao_numero']] = (int)$r['id'];
$conn->query("DELETE FROM perguntas WHERE licao_id IN (" . implode(',', array_values($map_s5)) . ")");

$perguntas_s5 = [
    // Lição 1
    ['licao'=>1, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual tipo de valor é retornado por qualquer comparação relacional (como 10 > 5) em Java?', 'codigo_contexto'=>'System.out.println(10 > 5);', 'alt_a'=>'Um valor boolean (true ou false)', 'alt_b'=>'Sempre o número 10', 'alt_c'=>'Um texto com aspas', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete a comparação para verificar se a pontuação é maior que 100:', 'codigo_contexto'=>'boolean recorde = pontos ___ 100;', 'alt_a'=>'>', 'alt_b'=>'<', 'alt_c'=>'=', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será a saída exibida no terminal pelo código abaixo?', 'codigo_contexto'=>"int nota = 7;\nSystem.out.println(nota >= 7);", 'alt_a'=>'true', 'alt_b'=>'false', 'alt_c'=>'7', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador "maior ou igual a":', 'codigo_contexto'=>'boolean aprovado = media ___ 6;', 'alt_a'=>'>=', 'alt_b'=>'<=', 'alt_c'=>'=>', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'multipla_escolha', 'pergunta'=>'O que será impresso no console ao executar esta comparação?', 'codigo_contexto'=>'System.out.println(3 > 8);', 'alt_a'=>'false', 'alt_b'=>'true', 'alt_c'=>'3', 'correta'=>'A'],
    ['licao'=>1, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete para verificar se a temperatura está abaixo (menor que) de zero:', 'codigo_contexto'=>'boolean congelando = temp ___ 0;', 'alt_a'=>'<', 'alt_b'=>'>', 'alt_c'=>'==', 'correta'=>'A'],

    // Lição 2
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual operador é usado em Java para COMPARAR se dois valores são exatamente iguais?', 'codigo_contexto'=>'// Comparação de igualdade', 'alt_a'=>'== (dois sinais de igual)', 'alt_b'=>'= (um único sinal de igual)', 'alt_c'=>'=== (três sinais de igual)', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete a expressão para verificar se o número de vidas é igual a 3:', 'codigo_contexto'=>'boolean cheio = (vidas ___ 3);', 'alt_a'=>'==', 'alt_b'=>'=', 'alt_c'=>'!=', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'O que o operador != verifica em uma expressão Java?', 'codigo_contexto'=>'boolean resultado = (10 != 5);', 'alt_a'=>'Se os dois valores são diferentes', 'alt_b'=>'Se os dois valores são iguais', 'alt_c'=>'Divide 10 por 5', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador de "diferente de":', 'codigo_contexto'=>'boolean ativo = (status ___ 0);', 'alt_a'=>'!=', 'alt_b'=>'==', 'alt_c'=>'<>', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será a saída exibida no terminal pelo código abaixo?', 'codigo_contexto'=>"int x = 5;\nSystem.out.println(x == 5);", 'alt_a'=>'true', 'alt_b'=>'false', 'alt_c'=>'5', 'correta'=>'A'],
    ['licao'=>2, 'tipo'=>'multipla_escolha', 'pergunta'=>'E qual será a saída de System.out.println(5 != 5);?', 'codigo_contexto'=>'System.out.println(5 != 5);', 'alt_a'=>'false (pois 5 é igual a 5, e não diferente)', 'alt_b'=>'true', 'alt_c'=>'0', 'correta'=>'A'],

    // Lição 3
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Quando o operador lógico && (E) retorna true em Java?', 'codigo_contexto'=>'boolean ok = condicao1 && condicao2;', 'alt_a'=>'Apenas quando AMBAS as condições são verdadeiras (true)', 'alt_b'=>'Quando ambas são falsas', 'alt_c'=>'Quando apenas uma é verdadeira', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador lógico "E" para exigir as duas condições ao mesmo tempo:', 'codigo_contexto'=>'boolean acesso = temChave ___ temSenha;', 'alt_a'=>'&&', 'alt_b'=>'||', 'alt_c'=>'and', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o resultado impresso por este código?', 'codigo_contexto'=>"boolean a = true;\nboolean b = false;\nSystem.out.println(a && b);", 'alt_a'=>'false', 'alt_b'=>'true', 'alt_c'=>'Erro de compilação', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o resultado da expressão (10 > 5) && (8 == 8)?', 'codigo_contexto'=>'System.out.println((10 > 5) && (8 == 8));', 'alt_a'=>'true (pois ambas as comparações são verdadeiras)', 'alt_b'=>'false', 'alt_c'=>'18', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete a segunda condição para que o resultado final seja true:', 'codigo_contexto'=>'boolean passou = (nota >= 7) && (___);', 'alt_a'=>'frequencia >= 75', 'alt_b'=>'false', 'alt_c'=>'0', 'correta'=>'A'],
    ['licao'=>3, 'tipo'=>'multipla_escolha', 'pergunta'=>'Se um jogador tem nivel = 5, qual é o resultado de (nivel >= 10 && nivel < 20)?', 'codigo_contexto'=>"int nivel = 5;\nSystem.out.println(nivel >= 10 && nivel < 20);", 'alt_a'=>'false (pois a primeira condição nivel >= 10 é falsa)', 'alt_b'=>'true', 'alt_c'=>'5', 'correta'=>'A'],

    // Lição 4
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual operador representa o "OU" lógico em Java (verdadeiro se pelo menos uma condição for true)?', 'codigo_contexto'=>'boolean entrada = vip || convidado;', 'alt_a'=>'|| (duas barras verticais)', 'alt_b'=>'&&', 'alt_c'=>'OR', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador lógico "OU":', 'codigo_contexto'=>'boolean desconto = estudante ___ idoso;', 'alt_a'=>'||', 'alt_b'=>'&&', 'alt_c'=>'??', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será a saída exibida no terminal pelo código abaixo?', 'codigo_contexto'=>"boolean sol = true;\nboolean piscina = false;\nSystem.out.println(sol || piscina);", 'alt_a'=>'true (pois pelo menos uma das variáveis é true)', 'alt_b'=>'false', 'alt_c'=>'sol', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'O que faz o operador de negação ! antes de um valor booleano (ex: !true)?', 'codigo_contexto'=>'System.out.println(!true);', 'alt_a'=>'Inverte o valor lógico (true vira false, e false vira true)', 'alt_b'=>'Repete o valor duas vezes', 'alt_c'=>'Apaga a variável da memória', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'completar_codigo', 'pergunta'=>'Complete com o operador de negação para inverter o valor de bloqueado:', 'codigo_contexto'=>"boolean bloqueado = false;\nboolean liberado = ___bloqueado;", 'alt_a'=>'!', 'alt_b'=>'~', 'alt_c'=>'-', 'correta'=>'A'],
    ['licao'=>4, 'tipo'=>'multipla_escolha', 'pergunta'=>'Qual será o resultado impresso por System.out.println(!false);?', 'codigo_contexto'=>'System.out.println(!false);', 'alt_a'=>'true', 'alt_b'=>'false', 'alt_c'=>'!false', 'correta'=>'A'],
];

foreach ($perguntas_s5 as $p) {
    $lid = $map_s5[$p['licao']];
    $stmt_ins->bind_param("isssssss", $lid, $p['pergunta'], $p['codigo_contexto'], $p['tipo'], $p['alt_a'], $p['alt_b'], $p['alt_c'], $p['correta']);
    $stmt_ins->execute();
}
echo "Seção 5 populada com " . count($perguntas_s5) . " perguntas!\n";
echo "=== SEÇÕES 4 E 5 CONFIGURADAS COM SUCESSO! ===\n";
