-- =========================================================================
-- MIGRATION 002: NOVOS TIPOS DE EXERCÍCIOS INTERATIVOS (MICROLEARNING)
-- Formatos:
--   1. ordenar_codigo (Problemas de Parsons)
--   2. completar_codigo (Banco de Tokens / Blocos Clicáveis / Tap-to-Fill)
--   3. spot_bug (Caçador de Bugs)
--   4. predicao_saida (Predição de Saída no Terminal)
--   5. pareamento (Pareamento de Conceitos estilo Duolingo)
-- =========================================================================

-- Lição 1: Primeiros Passos em Java (unidade 1, lição 1)
-- 118: Predição de Saída
UPDATE perguntas 
SET tipo = 'predicao_saida',
    pergunta_texto = 'O que será impresso no terminal ao executar este comando?',
    codigo_contexto = 'System.out.println(24);',
    alternativa_a = '24',
    alternativa_b = 'Nada (falta aspas)',
    alternativa_c = 'print(24)',
    alternativa_correta = 'A'
WHERE id = 118;

-- 116: Problemas de Parsons (Ordenação de Código)
UPDATE perguntas 
SET tipo = 'ordenar_codigo',
    pergunta_texto = 'Ordene as linhas de código para montar um programa que exibe duas mensagens na sequência correta:',
    codigo_contexto = 'System.out.println("Iniciando...");\nSystem.out.println("Nível 1 Concluído!");',
    alternativa_a = 'System.out.println("Iniciando...");',
    alternativa_b = 'System.out.println("Nível 1 Concluído!");',
    alternativa_correta = '0,1'
WHERE id = 116;

-- 120: Caçador de Bugs (Spot the Bug)
UPDATE perguntas 
SET tipo = 'spot_bug',
    pergunta_texto = 'Encontre o bug! Toque na linha que contém um erro de sintaxe que impede a compilação:',
    codigo_contexto = 'public class Principal {\n    System.out.println("Bem-vindo");\n    system.out.println("Ao Opus");\n}',
    alternativa_a = 'system deve começar com S maiúsculo (System)',
    alternativa_b = 'Falta ponto e vírgula',
    alternativa_c = 'Aspas incorretas',
    alternativa_correta = '3'
WHERE id = 120;

-- Lição 2: A Regra das Aspas Duplas (unidade 1, lição 2)
-- 125: Predição de Saída
UPDATE perguntas 
SET tipo = 'predicao_saida',
    pergunta_texto = 'O que será impresso no terminal após executar esta instrução?',
    codigo_contexto = 'System.out.println("100");',
    alternativa_a = '100',
    alternativa_b = 'Erro de compilação',
    alternativa_c = '"100"',
    alternativa_correta = 'A'
WHERE id = 125;

-- 126: Caçador de Bugs
UPDATE perguntas 
SET tipo = 'spot_bug',
    pergunta_texto = 'Encontre o bug! Toque na linha que esqueceu de fechar as aspas duplas:',
    codigo_contexto = 'public class Mensagem {\n    System.out.println("Linha correta");\n    System.out.println("Texto sem fechar aspas);\n}',
    alternativa_a = 'Falta fechar a aspa dupla antes do parêntese',
    alternativa_b = 'Comando inválido',
    alternativa_c = 'Nome de classe errado',
    alternativa_correta = '3'
WHERE id = 126;

-- Lição 3: Maiúsculas e Minúsculas (unidade 1, lição 3)
-- 130: Caçador de Bugs
UPDATE perguntas 
SET tipo = 'spot_bug',
    pergunta_texto = 'Java é Case-Sensitive! Toque na linha em que uma palavra-chave foi escrita com letras minúsculas incorretamente:',
    codigo_contexto = 'public class Teste {\n    system.out.println("Atenção!");\n    System.out.println("Correto!");\n}',
    alternativa_a = 'system em minúsculo não é reconhecido pelo compilador Java',
    alternativa_b = 'Ponto e vírgula incorreto',
    alternativa_c = 'Texto inválido',
    alternativa_correta = '2'
WHERE id = 130;

-- 132: Pareamento de Conceitos (Pair Matching)
UPDATE perguntas 
SET tipo = 'pareamento',
    pergunta_texto = 'Conecte cada elemento Java ao seu padrão de maiúsculas/minúsculas correto:',
    codigo_contexto = '{"pares":[{"esq":"System","dir":"Primeira letra Maiúscula (Classe)"},{"esq":"println","dir":"Tudo em minúsculas (Método)"},{"esq":"out","dir":"Tudo em minúsculas (Objeto)"}]}',
    alternativa_a = 'System : Primeira letra Maiúscula (Classe)',
    alternativa_b = 'println : Tudo em minúsculas (Método)',
    alternativa_c = 'out : Tudo em minúsculas (Objeto)',
    alternativa_correta = 'ALL_MATCHED'
WHERE id = 132;

-- Lição 6: Variáveis - O Tipo Inteiro (int) (unidade 2, lição 1)
-- 164: Pareamento de Tipos de Dados
UPDATE perguntas 
SET tipo = 'pareamento',
    pergunta_texto = 'Conecte cada tipo de dado em Java ao seu valor de exemplo correspondente:',
    codigo_contexto = '{"pares":[{"esq":"int","dir":"42"},{"esq":"String","dir":"\\"Opus\\""},{"esq":"boolean","dir":"true"}]}',
    alternativa_a = 'int : 42',
    alternativa_b = 'String : "Opus"',
    alternativa_c = 'boolean : true',
    alternativa_correta = 'ALL_MATCHED'
WHERE id = 164;

-- 166: Predição de Saída
UPDATE perguntas 
SET tipo = 'predicao_saida',
    pergunta_texto = 'O que será impresso no terminal após a execução do programa?',
    codigo_contexto = 'int nivel = 5;\nSystem.out.println(nivel);',
    alternativa_a = '5',
    alternativa_b = 'nivel',
    alternativa_c = 'int 5',
    alternativa_correta = 'A'
WHERE id = 166;

-- 167: Caçador de Bugs
UPDATE perguntas 
SET tipo = 'spot_bug',
    pergunta_texto = 'Encontre o bug! Toque na linha em que uma variável foi usada incorretamente com erro:',
    codigo_contexto = 'int idade = 20;\nint pontos = idade + 10;\nsystem.out.println(pontos);',
    alternativa_a = 'system deve iniciar com letra maiúscula (System)',
    alternativa_b = 'A soma de inteiros está errada',
    alternativa_c = 'Variável idade não foi inicializada',
    alternativa_correta = '3'
WHERE id = 167;

-- 168: Problemas de Parsons (Ordenação de Declaração de Variável)
UPDATE perguntas 
SET tipo = 'ordenar_codigo',
    pergunta_texto = 'Ordene as linhas lógicas para criar uma variável com valor 50 e exibir no terminal:',
    codigo_contexto = 'int moedas = 50;\nSystem.out.println(moedas);',
    alternativa_a = 'int moedas = 50;',
    alternativa_b = 'System.out.println(moedas);',
    alternativa_correta = '0,1'
WHERE id = 168;

-- Lição 26: Estrutura Condicional Simples: if (unidade 6, lição 1)
-- 261: Problemas de Parsons (Montar estrutura if completa)
UPDATE perguntas 
SET tipo = 'ordenar_codigo',
    pergunta_texto = 'Ordene as linhas de código para montar a estrutura condicional if corretamente:',
    codigo_contexto = 'int nota = 85;\nif (nota >= 70) {\n    System.out.println("Aprovado");\n}',
    alternativa_a = 'int nota = 85;',
    alternativa_b = 'if (nota >= 70) {',
    alternativa_c = '    System.out.println("Aprovado");',
    alternativa_correta = '0,1,2,3'
WHERE id = 261;
