<?php
/**
 * Migration & Seed para a Seção 1 (Introdução ao Java)
 * Insere 5 lições focadas em repetição de sintaxe Java com 6 perguntas por módulo (Lições 1 a 4)
 * e configura a Lição 5 como o Laboratório/Desafio Prático na IDE estilo Codédex.
 * Também cria/configura o perfil de testes com as 4 primeiras lições concluídas para testar a 5 direto.
 */

require_once __DIR__ . '/conexao.php';

echo "=== ATUALIZANDO SEÇÃO 1 (INTRODUÇÃO AO JAVA) COM 6 PERGUNTAS POR MÓDULO ===\n";

// 1. Atualizar ou garantir as 5 Lições da Unidade 1
$licoes_dados = [
    1 => [
        'titulo' => 'Introdução - Primeiros Passos em Java',
        'texto_explicativo' => "Java tem uma sintaxe clara e estruturada. Para exibir mensagens e textos na tela do computador, usamos a instrução System.out.println().\n\nO computador precisa de instruções precisas para funcionar. Em Java, o comando println envia uma linha inteira de saída para o terminal.\n\nPratique com repetição: todo texto precisa estar dentro dos parênteses da instrução!",
        'codigo_exemplo' => "System.out.println(\"Olá, Mundo!\");"
    ],
    2 => [
        'titulo' => 'Introdução - A Regra das Aspas Duplas',
        'texto_explicativo' => "Na programação Java, as aspas duplas são obrigatórias para textos. Quando colocamos caracteres entre \" \", o Java reconhece como uma String de texto.\n\nSe esquecermos as aspas em uma palavra ou frase, o compilador do Java gerará um erro e não conseguirá entender a instrução.\n\nLembre-se da regra de ouro: abra aspas duplas antes do texto e feche aspas duplas logo após!",
        'codigo_exemplo' => "System.out.println(\"Level Up\");"
    ],
    3 => [
        'titulo' => 'Introdução - Maiúsculas e Minúsculas',
        'texto_explicativo' => "Java é uma linguagem Case-Sensitive. Isso significa que letras maiúsculas e minúsculas fazem total diferença para o computador!\n\nA palavra System começa obrigatoriamente com 'S' maiúsculo. Se você escrever system ou SYSTEM, o Java não reconhecerá a classe.\n\nJá a instrução println é toda em minúsculas. Preste sempre muita atenção nas maiúsculas ao escrever seu código!",
        'codigo_exemplo' => "System.out.println(\"Atenção às maiúsculas!\");"
    ],
    4 => [
        'titulo' => 'Introdução - Parênteses e Ponto e Vírgula',
        'texto_explicativo' => "Em Java, o conteúdo a ser exibido fica sempre dentro dos parênteses ( ).\n\nAlém disso, toda instrução em Java precisa terminar obrigatoriamente com o caractere ponto e vírgula (;).\n\nO ponto e vírgula indica ao computador onde o comando terminou. A fórmula completa é: System.out.println(\"Texto\");",
        'codigo_exemplo' => "System.out.println(\"Ponto e vírgula obrigatório!\");"
    ],
    5 => [
        'titulo' => 'Introdução - Desafio IDE: Olá Mundo',
        'texto_explicativo' => "Chegou a hora de juntar tudo o que você aprendeu com repetição e colocar as mãos no código real!\n\nNeste desafio prático no estilo Codédex, você usará o editor de código e o terminal OpenJDK para programar seu primeiro Hello World.\n\nEscreva a instrução para exibir Olá Mundo na tela e execute o programa no terminal!",
        'codigo_exemplo' => ""
    ],
];

foreach ($licoes_dados as $licao_num => $dados) {
    // Garante que a lição existe
    $chk = $conn->prepare("SELECT id FROM licoes WHERE unidade_numero = 1 AND licao_numero = ?");
    $chk->bind_param("i", $licao_num);
    $chk->execute();
    $res_chk = $chk->get_result();

    if ($res_chk->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE licoes SET titulo = ?, texto_explicativo = ?, codigo_exemplo = ? WHERE unidade_numero = 1 AND licao_numero = ?");
        $stmt->bind_param("sssi", $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo'], $licao_num);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES (1, ?, ?, ?, ?)");
        $stmt->bind_param("isss", $licao_num, $dados['titulo'], $dados['texto_explicativo'], $dados['codigo_exemplo']);
        $stmt->execute();
    }
    echo "Lição 1.$licao_num atualizada/garantida.\n";
}

// 2. Mapear IDs das lições da Unidade 1
$res_lic_ids = $conn->query("SELECT id, licao_numero FROM licoes WHERE unidade_numero = 1 ORDER BY licao_numero ASC");
$ids_map = [];
while ($r = $res_lic_ids->fetch_assoc()) {
    $ids_map[$r['licao_numero']] = (int) $r['id'];
}

// Limpa perguntas antigas da Seção 1 para reinserir com o currículo de 6 perguntas por módulo
$conn->query("DELETE FROM perguntas WHERE licao_id IN (" . implode(',', array_values($ids_map)) . ")");
echo "Perguntas anteriores da Seção 1 limpas com sucesso.\n";

// 3. Currículo Didático de Fixação Repetitiva - 6 Questões por Módulo
$perguntas_secao_1 = [
    // =========================================================================
    // LIÇÃO 1 (Módulo 1): Primeiros Passos com System.out.println (6 questões)
    // =========================================================================
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Programas de computador usam código para dar instruções às máquinas. Em Java, qual instrução é usada para exibir uma mensagem na tela?',
        'codigo_contexto' => "// Como instruir o computador a exibir uma mensagem no terminal?",
        'alt_a' => 'System.out.println',
        'alt_b' => 'delete',
        'alt_c' => 'printer.paper',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual é a instrução que este código está dando ao computador?',
        'codigo_contexto' => "System.out.println(\"Nível 1\");",
        'alt_a' => 'Para exibir o texto "Nível 1" na tela',
        'alt_b' => 'Para enviar o texto para a impressora',
        'alt_c' => 'Para apagar a palavra do computador',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução Java para enviar uma mensagem para a tela:',
        'codigo_contexto' => "___(\"Bem-vindo\");",
        'alt_a' => 'System.out.println',
        'alt_b' => 'send',
        'alt_c' => 'display',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que o código abaixo enviará para a tela do computador?',
        'codigo_contexto' => "System.out.println(24);",
        'alt_a' => 'O número 24',
        'alt_b' => 'Nada, pois falta texto',
        'alt_c' => 'A palavra "print"',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a linha de código para exibir a mensagem "Novo email":',
        'codigo_contexto' => "System.out.println(___);",
        'alt_a' => '"Novo email"',
        'alt_b' => 'Novo email',
        'alt_c' => 'email.delete',
        'correta' => 'A'
    ],
    [
        'licao' => 1,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Verdadeiro ou Falso? O computador executa exatamente as instruções que você escreve no código Java, sem adivinhar o que você pretendia fazer.',
        'codigo_contexto' => "// O computador segue cada instrução passo a passo",
        'alt_a' => 'Verdadeiro',
        'alt_b' => 'Falso',
        'alt_c' => 'Apenas com conexão à internet',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 2 (Módulo 2): A Regra das Aspas Duplas (6 questões)
    // =========================================================================
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Em Java, qualquer texto ou frase que queremos imprimir na tela deve estar obrigatoriamente entre qual destes símbolos?',
        'codigo_contexto' => "// Delimitação de textos e frases no comando",
        'alt_a' => 'Aspas duplas ("...")',
        'alt_b' => 'Apenas parênteses sem aspas',
        'alt_c' => 'Cerquilhas (###)',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução abaixo para exibir o texto Level Up na tela:',
        'codigo_contexto' => "System.out.println(___);",
        'alt_a' => '"Level Up"',
        'alt_b' => 'Level Up',
        'alt_c' => '\'Level Up\'',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que acontecerá ao tentar executar este código Java sem aspas duplas no texto?',
        'codigo_contexto' => "System.out.println(Olá);",
        'alt_a' => 'Ocorrerá um erro de compilação, pois o Java não reconhece palavras sem aspas',
        'alt_b' => 'O Java exibirá Olá normalmente',
        'alt_c' => 'O texto será enviado para a lixeira',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o código para um aplicativo de música exibir "Tocando agora...":',
        'codigo_contexto' => "System.out.println(___);",
        'alt_a' => '"Tocando agora..."',
        'alt_b' => 'Tocando agora...',
        'alt_c' => 'play_music',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O que o código abaixo enviará para a tela do computador?',
        'codigo_contexto' => "System.out.println(\"100\");",
        'alt_a' => 'O texto "100" (pois está entre aspas duplas)',
        'alt_b' => 'Um erro, porque números não aceitam aspas',
        'alt_c' => 'O número 100 somado a 1',
        'correta' => 'A'
    ],
    [
        'licao' => 2,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Verdadeiro ou Falso? Em Java, esquecer de fechar as aspas duplas de um texto gera um erro e impede o programa de compilar.',
        'codigo_contexto' => "System.out.println(\"Texto sem fechar aspas);",
        'alt_a' => 'Verdadeiro',
        'alt_b' => 'Falso',
        'alt_c' => 'O Java fecha as aspas sozinho',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 3 (Módulo 3): Maiúsculas e Minúsculas (Case-Sensitive) (6 questões)
    // =========================================================================
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Java é uma linguagem Case-Sensitive. O que isso significa na prática?',
        'codigo_contexto' => "// Case-Sensitive",
        'alt_a' => 'O Java diferencia letras maiúsculas de minúsculas',
        'alt_b' => 'O Java aceita qualquer letra maiúscula ou minúscula sem distinção',
        'alt_c' => 'O Java só aceita comandos em maiúsculas',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução com o nome correto da classe:',
        'codigo_contexto' => "___.out.println(\"Opus\");",
        'alt_a' => 'System',
        'alt_b' => 'system',
        'alt_c' => 'SYSTEM',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a instrução com o método de exibição de texto:',
        'codigo_contexto' => "System.out.___(\"Aprendendo Java\");",
        'alt_a' => 'println',
        'alt_b' => 'Println',
        'alt_c' => 'PRINTLN',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual destas linhas de código Java está escrita com as maiúsculas e minúsculas corretas?',
        'codigo_contexto' => "// Qual opção compila corretamente em Java?",
        'alt_a' => 'System.out.println("Código correto!");',
        'alt_b' => 'system.out.println("Código correto!");',
        'alt_c' => 'SYSTEM.OUT.PRINTLN("Código correto!");',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete a chamada de impressão:',
        'codigo_contexto' => "System.___.println(\"Atenção aos detalhes!\");",
        'alt_a' => 'out',
        'alt_b' => 'Out',
        'alt_c' => 'OUT',
        'correta' => 'A'
    ],
    [
        'licao' => 3,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Verdadeiro ou Falso? Se você escrever "system.out.println()" com \'s\' minúsculo, o compilador Java não reconhecerá o comando.',
        'codigo_contexto' => "system.out.println(\"Teste\");",
        'alt_a' => 'Verdadeiro',
        'alt_b' => 'Falso',
        'alt_c' => 'O Java converte para maiúsculo sozinho',
        'correta' => 'A'
    ],

    // =========================================================================
    // LIÇÃO 4 (Módulo 4): Parênteses e Ponto e Vírgula Obrigatório (6 questões)
    // =========================================================================
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Toda declaração ou instrução de comando em Java deve terminar obrigatoriamente com qual símbolo de pontuação?',
        'codigo_contexto' => "// Pontuação obrigatória ao final do comando",
        'alt_a' => 'Ponto e vírgula (;)',
        'alt_b' => 'Dois pontos (:)',
        'alt_c' => 'Ponto final (.)',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o encerramento da instrução Java:',
        'codigo_contexto' => "System.out.println(\"Quase lá\")___",
        'alt_a' => ';',
        'alt_b' => ':',
        'alt_c' => '.',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete o fechamento da instrução:',
        'codigo_contexto' => "System.out.println(\"Vitória!\"___",
        'alt_a' => ');',
        'alt_b' => ':)',
        'alt_c' => '.;',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'O código em programas de computador é feito de declarações. Qual é o número de declarações neste código Java?',
        'codigo_contexto' => "System.out.println(\"Harry Potter\");\nSystem.out.println(\"J. K. Rowling\");",
        'alt_a' => '2',
        'alt_b' => '1',
        'alt_c' => '3',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'completar_codigo',
        'pergunta' => 'Complete com o comando completo para exibir a mensagem na tela:',
        'codigo_contexto' => "___(\"Código Limpo\");",
        'alt_a' => 'System.out.println',
        'alt_b' => 'System.out.print_line',
        'alt_c' => 'print',
        'correta' => 'A'
    ],
    [
        'licao' => 4,
        'tipo' => 'multipla_escolha',
        'pergunta' => 'Qual destas opções de código Java está 100% correta de acordo com todas as regras de sintaxe aprendidas?',
        'codigo_contexto' => "// Qual declaração segue todas as regras de sintaxe do Java?",
        'alt_a' => 'System.out.println("Parabéns!");',
        'alt_b' => 'System.out.println("Parabéns!")',
        'alt_c' => 'system.out.println(Parabéns!);',
        'correta' => 'A'
    ],
];

// Inserir perguntas no banco
$stmt_insert = $conn->prepare("INSERT INTO perguntas (licao_id, pergunta_texto, codigo_contexto, tipo, alternativa_a, alternativa_b, alternativa_c, alternativa_correta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($perguntas_secao_1 as $p) {
    $lid = $ids_map[$p['licao']];
    $ctx = $p['codigo_contexto'] ?? null;
    $stmt_insert->bind_param("isssssss", $lid, $p['pergunta'], $ctx, $p['tipo'], $p['alt_a'], $p['alt_b'], $p['alt_c'], $p['correta']);
    $stmt_insert->execute();
}
echo "Total de " . count($perguntas_secao_1) . " perguntas cadastradas (6 perguntas em cada lição de 1 a 4)!\n";

// =========================================================================
// 4. CONFIGURAÇÃO DO PERFIL DE TESTES PARA TESTAR DIRETO A LIÇÃO 5
// =========================================================================
echo "\n=== CONFIGURANDO PERFIL DE TESTES (LIBERADO NA LIÇÃO 5) ===\n";

$email_teste = 'teste@opus.com';
$senha_pura = '123456';
$senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);
$nome_teste = 'Aluno Teste (Cap 1 - Lição 5)';

$res_user = $conn->query("SELECT id FROM usuarios WHERE LOWER(email) = '$email_teste'");
if ($res_user && $u = $res_user->fetch_assoc()) {
    $user_teste_id = (int) $u['id'];
    $conn->query("UPDATE usuarios SET nome = '$nome_teste', senha = '$senha_hash', vidas = 5, xp = 250 WHERE id = $user_teste_id");
    echo "Usuário $email_teste atualizado (ID: $user_teste_id).\n";
} else {
    $stmt_new = $conn->prepare("INSERT INTO usuarios (nome, email, senha, vidas, xp, nivel_acesso, dificuldade) VALUES (?, ?, ?, 5, 250, 'comum', 'Iniciante')");
    $stmt_new->bind_param("sss", $nome_teste, $email_teste, $senha_hash);
    $stmt_new->execute();
    $user_teste_id = $conn->insert_id;
    echo "Usuário $email_teste criado com sucesso (ID: $user_teste_id).\n";
}

// Configura o progresso da Unidade 1: 4 lições concluídas e status 'corrente'
// Isso significa que no dashboard, as lições 1 a 4 estão completadas e o botão COMEÇAR aponta diretamente para a Lição 5!
$conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) 
              VALUES ($user_teste_id, 1, 'corrente', 4) 
              ON DUPLICATE KEY UPDATE status = 'corrente', licoes_concluidas = 4");

echo "Progresso do usuário $email_teste configurado: Unidade 1 na Lição 5 (4 concluídas).\n";

// Atualiza também o usuário Gabriel (ID 1), caso queira testar diretamente com a conta principal:
$conn->query("INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) 
              VALUES (1, 1, 'corrente', 4) 
              ON DUPLICATE KEY UPDATE status = 'corrente', licoes_concluidas = 4");
echo "Progresso do usuário principal Gabriel Freitas (ID 1) também atualizado para a Lição 5 (4 concluídas).\n";

echo "\n=== MIGRAÇÃO E SEED CONCLUÍDOS COM SUCESSO! ===\n";
