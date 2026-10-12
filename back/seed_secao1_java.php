<?php
/**
 * Migration & Seed para a Seção 1 (Introdução ao Java)
 * Insere 5 lições focadas em repetição de sintaxe Java e os tipos de exercícios:
 * - multipla_escolha
 * - digitar_codigo (na lição 5 / última bolinha)
 * - completar_codigo
 */

require_once __DIR__ . '/conexao.php';

echo "=== ATUALIZANDO CONTEÚDO E PERGUNTAS DA SEÇÃO 1 (JAVA) ===\n";

// 1. Atualizar ou garantir as 5 Lições da Unidade 1
$licoes_dados = [
    1 => [
        'titulo' => 'Introdução - Primeiros Passos em Java',
        'texto_explicativo' => "Java tem uma sintaxe clara e estruturada. Para exibir mensagens e textos na tela, usamos a instrução System.out.println().\n\nEm Java, todo texto literal deve ser escrito obrigatoriamente entre aspas duplas, por exemplo \"Olá Mundo\".\n\nAlém disso, lembre-se: Java diferencia letras maiúsculas de minúsculas e todo comando termina com ponto e vírgula (;).",
        'codigo_exemplo' => "System.out.println(\"Olá Mundo\");"
    ],
    2 => [
        'titulo' => 'Introdução - A Regra das Aspas Duplas',
        'texto_explicativo' => "Na programação Java, as aspas duplas são fundamentais. Quando colocamos texto dentro de \" \", dizemos que é uma String de texto.\n\nSe esquecermos as aspas ou usarmos aspas simples para frases, o compilador do Java gerará um erro de sintaxe.\n\nPratique sempre a repetição: abra aspas, digite o texto e feche aspas antes de fechar os parênteses!",
        'codigo_exemplo' => "System.out.println(\"Bem-vindo ao Java\");"
    ],
    3 => [
        'titulo' => 'Introdução - Maiúsculas e Minúsculas',
        'texto_explicativo' => "Java é uma linguagem Case-Sensitive. Isso significa que letras maiúsculas e minúsculas fazem total diferença!\n\nA palavra System começa com 'S' maiúsculo. Se você escrever system ou SYSTEM, o Java não reconhecerá a classe.\n\nJá a palavra println é toda escrita em letras minúsculas. Repita essas regrinhas para fixar bem na memória!",
        'codigo_exemplo' => "System.out.println(\"Atenção às maiúsculas!\");"
    ],
    4 => [
        'titulo' => 'Introdução - O Ponto e Vírgula Obrigatório',
        'texto_explicativo' => "Em Java, quase todas as declarações de instrução precisam terminar com o caractere ponto e vírgula (;).\n\nO ponto e vírgula indica ao computador onde a instrução terminou. Esquecer o ponto e vírgula é o erro mais comum de quem está começando!\n\nLembre-se sempre da fórmula: Comando + ( \"Texto\" ) + ;",
        'codigo_exemplo' => "System.out.println(\"Nunca esqueça o ponto e vírgula!\");"
    ],
    5 => [
        'titulo' => 'Introdução - O Playground e Terminal Java',
        'texto_explicativo' => "Chegou a hora de praticar tudo o que você aprendeu com repetição!\n\nNa última lição desta seção, você vai colocar a mão no código e digitar comandos diretamente no terminal.\n\nJunte as regras de ouro: S maiúsculo em System, println minúsculo, aspas duplas no texto e ponto e vírgula no final!",
        'codigo_exemplo' => "System.out.println(\"Level Up\");"
    ],
];

foreach ($licoes_dados as $licao_num => $dados) {
    $stmt = $conn->prepare("UPDATE licoes SET titulo = ?, texto_explicativo = ?, codigo_exemplo = ? WHERE unidade_numero = 1 AND licao_numero = ?");
    $stmt->bind_param("sssi", $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo'], $licao_num);
    $stmt->execute();
    echo "Lição 1.$licao_num atualizada.\n";
}

// 2. Limpar perguntas antigas da Seção 1 para recriar as novas com repetições pedagógicas
$res_lic_ids = $conn->query("SELECT id, licao_numero FROM licoes WHERE unidade_numero = 1");
$ids_map = [];
while ($r = $res_lic_ids->fetch_assoc()) {
    $ids_map[$r['licao_numero']] = (int) $r['id'];
}

$conn->query("DELETE FROM perguntas WHERE licao_id IN (" . implode(',', array_values($ids_map)) . ")");
echo "Perguntas anteriores da Seção 1 limpas para inserção do novo currículo.\n";

// 3. Perguntas pedagógicas repetitivas estilo Duolingo / Mimo adaptadas para JAVA
// Lições 1 a 4: exercícios de repetição (completar e múltipla escolha)
// Lição 5: inclui exercício de DIGITAR código no terminal (requisito explícito da última bolinha)

$perguntas_secao_1 = [
    // --- LIÇÃO 1 (Primeira bolinha): Primeiros passos com System.out.println ---
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual é a instrução que este código está dando ao computador?',
        'codigo_contexto' => "System.out.println(\"Olá Mundo\");",
        'alt_a' => 'Para exibir o texto "Olá Mundo" na tela',
        'alt_b' => 'Para enviar o texto para a impressora',
        'alt_c' => 'Para deletar o texto do computador',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução Java para enviar uma mensagem para a tela:',
        'codigo_contexto' => "___(\"Welcome\");",
        'alt_a' => 'System.out.println',
        'alt_b' => 'print',
        'alt_c' => 'console.log',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que este código Java exibirá na tela ao ser executado?',
        'codigo_contexto' => "System.out.println(24);",
        'alt_a' => 'O número 24',
        'alt_b' => 'O texto "vinte e quatro"',
        'alt_c' => 'Nada, dará erro por não ter aspas',
        'correta' => 'A'
    ],

    // --- LIÇÃO 2 (Segunda bolinha): Repetição - Regra de Aspas Duplas ---
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Observe o código Java abaixo. Qual regra está sendo aplicada no texto "Novo email"?',
        'codigo_contexto' => "System.out.println(\"Novo email\");",
        'alt_a' => 'Todo texto literal deve estar entre aspas duplas ("...")',
        'alt_b' => 'Textos podem ser escritos sem nenhuma aspa',
        'alt_c' => 'Textos devem obrigatoriamente usar colchetes',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o código com as aspas duplas corretas para exibir a mensagem na tela:',
        'codigo_contexto' => "System.out.println(___);",
        'alt_a' => '"Novo email"',
        'alt_b' => 'Novo email',
        'alt_c' => '\'Novo email\'',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que acontecerá ao executar este código sem aspas duplas?',
        'codigo_contexto' => "System.out.println(Olá);",
        'alt_a' => 'Ocorrerá um erro de compilação',
        'alt_b' => 'Vai imprimir Olá normalmente',
        'alt_c' => 'Vai imprimir uma tela em branco',
        'correta' => 'A'
    ],

    // --- LIÇÃO 3 (Terceira bolinha): Repetição - Regra de Maiúsculas e Minúsculas (Case Sensitive) ---
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Java diferencia maiúsculas de minúsculas. Observe como o comando é escrito:',
        'codigo_contexto' => "System.out.println(\"Executando...\");",
        'alt_a' => 'System tem o \'S\' maiúsculo e println é minúsculo',
        'alt_b' => 'Tudo deve ser escrito em maiúsculas',
        'alt_c' => 'Tudo deve ser escrito em minúsculas',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução com o nome correto e sensível a maiúsculas da classe:',
        'codigo_contexto' => "___.out.println(\"Executando...\");",
        'alt_a' => 'System',
        'alt_b' => 'system',
        'alt_c' => 'SYSTEM',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual destas duas linhas será aceita pelo compilador Java?',
        'codigo_contexto' => "// Linha 1: system.out.println(\"Oi\");\n// Linha 2: System.out.println(\"Oi\");",
        'alt_a' => 'Apenas a Linha 2, pois começa com \'S\' maiúsculo',
        'alt_b' => 'Apenas a Linha 1',
        'alt_c' => 'As duas linhas são idênticas para o Java',
        'correta' => 'A'
    ],

    // --- LIÇÃO 4 (Quarta bolinha): Repetição - Ponto e Vírgula Obrigatório (;) ---
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Toda instrução em Java termina com qual caractere de pontuação?',
        'codigo_contexto' => "System.out.println(\"Tocando agora...\");",
        'alt_a' => 'Ponto e vírgula (;)',
        'alt_b' => 'Dois pontos (:)',
        'alt_c' => 'Ponto final (.)',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o final da instrução Java com a pontuação obrigatória:',
        'codigo_contexto' => "System.out.println(\"Tocando agora...\")___",
        'alt_a' => ';',
        'alt_b' => ':',
        'alt_c' => '.',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual destas opções de código Java está 100% correta de acordo com as regras de sintaxe?',
        'codigo_contexto' => "System.out.println(\"Java é incrível!\");",
        'alt_a' => 'System.out.println("Java é incrível!");',
        'alt_b' => 'system.out.println("Java é incrível!")',
        'alt_c' => 'System.out.println(Java é incrível!);',
        'correta' => 'A'
    ],

    // --- LIÇÃO 5 (Última bolinha): Repetição + Exercício de DIGITAR CÓDIGO no terminal (Playground) ---
    [
        'licao' => 5,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual é a instrução que este código dá ao computador?',
        'codigo_contexto' => "System.out.println(24);",
        'alt_a' => 'Para exibir o número 24 na tela',
        'alt_b' => 'Para enviar o número 24 para a impressora',
        'alt_c' => 'Para deletar o número 24 da memória',
        'correta' => 'A'
    ],
    [
        'licao' => 5,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o código para exibir a mensagem "Bem-vindo ao Playground":',
        'codigo_contexto' => "___(\"Bem-vindo ao Playground\");",
        'alt_a' => 'System.out.println',
        'alt_b' => 'print',
        'alt_c' => 'echo',
        'correta' => 'A'
    ],
    [
        'licao' => 5,
        'tipo' => 'digitar_codigo',
        'pergunta' => 'O Playground de Código: Digite o código Java completo para exibir a mensagem "Welcome to the Playground" na tela:',
        'codigo_contexto' => "System.out.println(\"Welcome to the Playground\");",
        'alt_a' => 'System.out.println("Welcome to the Playground");',
        'alt_b' => 'System.out.println("Welcome to the Playground")',
        'alt_c' => 'print("Welcome to the Playground");',
        'correta' => 'System.out.println("Welcome to the Playground");'
    ],
];

$stmt_insert = $conn->prepare("INSERT INTO perguntas (licao_id, pergunta_texto, codigo_contexto, tipo, alternativa_a, alternativa_b, alternativa_c, alternativa_correta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($perguntas_secao_1 as $p) {
    $lid = $ids_map[$p['licao']];
    $ctx = $p['codigo_contexto'] ?? null;
    $stmt_insert->bind_param("isssssss", $lid, $p['pergunta'], $ctx, $p['tipo'], $p['alt_a'], $p['alt_b'], $p['alt_c'], $p['correta']);
    $stmt_insert->execute();
}

echo "Perguntas da Seção 1 inseridas com sucesso (" . count($perguntas_secao_1) . " questões criadas)!\n";
echo "=== SEED DA SEÇÃO 1 CONCLUÍDO COM SUCESSO ===\n";
