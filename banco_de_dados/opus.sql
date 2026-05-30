-- ============================================================
--  OPUS - Plataforma de Aprendizado de Programação
--  Script de criação completa do banco de dados
--  Banco: opus
-- ============================================================

CREATE DATABASE IF NOT EXISTS opus
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE opus;

-- ============================================================
-- TABELA 1: usuarios
-- Armazena os dados de cada aluno cadastrado na plataforma.
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id                INT          NOT NULL AUTO_INCREMENT,
    nome              VARCHAR(100) NOT NULL,
    email             VARCHAR(150) NOT NULL,
    senha             VARCHAR(255) NOT NULL,
    xp                INT          NOT NULL DEFAULT 0,
    trofeus           INT          NOT NULL DEFAULT 0,
    dificuldade       VARCHAR(30)  NOT NULL DEFAULT 'Iniciante',
    foto_perfil       VARCHAR(255)          DEFAULT NULL,
    sequencia_dias    INT          NOT NULL DEFAULT 0,
    ultima_atividade  DATE                  DEFAULT NULL,
    criado_em         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA 2: progresso_usuario
-- Controla em qual capítulo o aluno está e quantas lições
-- de cada capítulo ele já concluiu.
-- Status possíveis: 'trancado' | 'corrente' | 'completo'
-- ============================================================
CREATE TABLE IF NOT EXISTS progresso_usuario (
    id                INT         NOT NULL AUTO_INCREMENT,
    usuario_id        INT         NOT NULL,
    unidade_numero    INT         NOT NULL,
    status            VARCHAR(20) NOT NULL DEFAULT 'trancado',
    licoes_concluidas INT         NOT NULL DEFAULT 0,
    atualizado_em     TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_usuario_unidade (usuario_id, unidade_numero),
    CONSTRAINT fk_progresso_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA 3: licoes
-- Conteúdo teórico de cada lição (texto + código de exemplo).
-- Cada unidade tem 3 lições (licao_numero 1, 2, 3).
-- ============================================================
CREATE TABLE IF NOT EXISTS licoes (
    id                INT          NOT NULL AUTO_INCREMENT,
    unidade_numero    INT          NOT NULL,
    licao_numero      INT          NOT NULL,
    titulo            VARCHAR(200) NOT NULL,
    texto_explicativo TEXT         NOT NULL,
    codigo_exemplo    TEXT                  DEFAULT NULL,
    criado_em         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_unidade_licao (unidade_numero, licao_numero)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA 4: perguntas
-- Questões de múltipla escolha (A, B, C) vinculadas a uma lição.
-- Cada lição deve ter exatamente 3 perguntas.
-- ============================================================
CREATE TABLE IF NOT EXISTS perguntas (
    id                  INT          NOT NULL AUTO_INCREMENT,
    licao_id            INT          NOT NULL,
    pergunta_texto      TEXT         NOT NULL,
    alternativa_a       VARCHAR(500) NOT NULL,
    alternativa_b       VARCHAR(500) NOT NULL,
    alternativa_c       VARCHAR(500) NOT NULL,
    alternativa_correta CHAR(1)      NOT NULL COMMENT 'A, B ou C',
    PRIMARY KEY (id),
    CONSTRAINT fk_pergunta_licao
        FOREIGN KEY (licao_id) REFERENCES licoes(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA 5: user_trofeus
-- Registra quais conquistas (troféus) cada usuário desbloqueou.
-- ============================================================
CREATE TABLE IF NOT EXISTS user_trofeus (
    id           INT         NOT NULL AUTO_INCREMENT,
    user_id      INT         NOT NULL,
    trofeu_slug  VARCHAR(80) NOT NULL,
    conquistado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_user_trofeu (user_id, trofeu_slug),
    CONSTRAINT fk_trofeu_usuario
        FOREIGN KEY (user_id) REFERENCES usuarios(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
--  DADOS INICIAIS — LIÇÕES E PERGUNTAS DE JAVA
-- ============================================================

-- ============================================================
--  CAPÍTULO 1: Fundamentos e Sintaxe Básica
-- ============================================================

-- Lição 1.1
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(1, 1, 'Olá, Mundo! — Seu primeiro programa Java',
'Java é uma linguagem de programação orientada a objetos, fortemente tipada e amplamente utilizada no mercado.\n\nTodo programa Java começa dentro de uma CLASSE. Uma classe é como um molde que define o que o programa pode fazer.\n\nO método "main" é o ponto de entrada: é por ele que a execução sempre começa.\n\nO comando System.out.println() exibe texto na tela e pula uma linha ao final.',
'public class OlaMundo {\n    public static void main(String[] args) {\n        System.out.println("Olá, Mundo!");\n        System.out.println("Bem-vindo ao OPUS!");\n    }\n}');

-- Perguntas da Lição 1.1
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é o ponto de entrada (ponto de início) de todo programa Java?',
       'O método start()',
       'O método main()',
       'A classe Main',
       'B' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual comando é usado para exibir texto no console em Java?',
       'console.log()',
       'print()',
       'System.out.println()',
       'C' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Em Java, o código principal fica dentro de uma...',
       'Função',
       'Classe',
       'Variável',
       'B' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 1;

-- Lição 1.2
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(1, 2, 'Variáveis e Tipos de Dados',
'Uma variável é um espaço na memória do computador onde guardamos informações.\n\nEm Java, toda variável precisa ter um TIPO definido antes de ser usada. Os tipos mais comuns são:\n\n• int — números inteiros (ex: 10, -5, 0)\n• double — números com casa decimal (ex: 3.14, -2.5)\n• String — texto (ex: "Olá")\n• boolean — verdadeiro (true) ou falso (false)\n• char — um único caractere (ex: ''A'')\n\nPara criar uma variável, escrevemos: tipo nomeDaVariavel = valor;',
'public class Variaveis {\n    public static void main(String[] args) {\n        int idade = 20;\n        double altura = 1.75;\n        String nome = "Ana";\n        boolean aprovado = true;\n        char nota = \'A\';\n\n        System.out.println("Nome: " + nome);\n        System.out.println("Idade: " + idade);\n        System.out.println("Altura: " + altura);\n        System.out.println("Aprovado: " + aprovado);\n    }\n}');

-- Perguntas da Lição 1.2
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual tipo de dado você usaria para guardar o número 3.14 em Java?',
       'int',
       'String',
       'double',
       'C' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Como declaramos corretamente uma variável de texto em Java?',
       'text nome = "Maria";',
       'String nome = "Maria";',
       'var nome = "Maria";',
       'B' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual tipo de dado armazena apenas os valores true ou false?',
       'bit',
       'char',
       'boolean',
       'C' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 2;

-- Lição 1.3
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(1, 3, 'Operadores Aritméticos',
'Operadores aritméticos são usados para fazer cálculos matemáticos em Java.\n\nOs principais são:\n• + → Adição\n• - → Subtração\n• * → Multiplicação\n• / → Divisão\n• % → Módulo (resto da divisão)\n\nDica importante: quando você divide dois números inteiros em Java, o resultado também é inteiro. Para obter casas decimais, use double.',
'public class Operadores {\n    public static void main(String[] args) {\n        int a = 10;\n        int b = 3;\n\n        System.out.println("Soma: " + (a + b));        // 13\n        System.out.println("Subtração: " + (a - b));   // 7\n        System.out.println("Multiplicação: " + (a * b)); // 30\n        System.out.println("Divisão: " + (a / b));     // 3\n        System.out.println("Módulo: " + (a % b));      // 1\n    }\n}');

-- Perguntas da Lição 1.3
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual operador retorna o RESTO de uma divisão em Java?',
       '/',
       '%',
       '#',
       'B' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é o resultado de 10 / 3 em Java quando ambos são do tipo int?',
       '3.33',
       '4',
       '3',
       'C' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é o resultado de 10 % 4 em Java?',
       '2',
       '2.5',
       '0',
       'A' FROM licoes WHERE unidade_numero = 1 AND licao_numero = 3;


-- ============================================================
--  CAPÍTULO 2: Estruturas de Decisão
-- ============================================================

-- Lição 2.1
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(2, 1, 'if e else — Tomando Decisões',
'Estruturas de decisão permitem que o programa faça escolhas com base em condições.\n\nO if (se) executa um bloco de código somente se a condição for verdadeira.\nO else (senão) executa um bloco alternativo caso a condição seja falsa.\n\nOperadores de comparação usados nas condições:\n• == → igual a\n• != → diferente de\n• > → maior que\n• < → menor que\n• >= → maior ou igual\n• <= → menor ou igual',
'public class Decisao {\n    public static void main(String[] args) {\n        int nota = 75;\n\n        if (nota >= 70) {\n            System.out.println("Aprovado!");\n        } else {\n            System.out.println("Reprovado. Tente novamente.");\n        }\n    }\n}');

-- Perguntas da Lição 2.1
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que o bloco "else" executa?',
       'O código principal sempre',
       'O código quando a condição do if é FALSA',
       'O código quando a condição do if é VERDADEIRA',
       'B' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual operador verifica se dois valores são IGUAIS em Java?',
       '=',
       '!=',
       '==',
       'C' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Dado int x = 10; qual é a saída de: if (x > 5) { System.out.println("Maior"); } else { System.out.println("Menor"); }',
       'Menor',
       'Maior',
       'Nenhuma saída',
       'B' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 1;

-- Lição 2.2
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(2, 2, 'else if — Múltiplas Condições',
'Quando precisamos verificar mais de duas possibilidades, usamos o "else if" para encadear condições.\n\nO Java verifica cada condição de cima para baixo. Assim que uma for verdadeira, ele executa o bloco correspondente e pula o restante.\n\nSempre é boa prática terminar com um "else" para capturar todos os casos que não foram atendidos.',
'public class MultiplaCondicao {\n    public static void main(String[] args) {\n        int nota = 85;\n\n        if (nota >= 90) {\n            System.out.println("Conceito: A");\n        } else if (nota >= 80) {\n            System.out.println("Conceito: B");\n        } else if (nota >= 70) {\n            System.out.println("Conceito: C");\n        } else {\n            System.out.println("Conceito: D — Reprovado");\n        }\n    }\n}');

-- Perguntas da Lição 2.2
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Dado nota = 85, qual a saída do código de exemplo desta lição?',
       'Conceito: A',
       'Conceito: B',
       'Conceito: C',
       'B' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Ao encadear if/else if, o Java verifica as condições em qual ordem?',
       'De baixo para cima',
       'Aleatoriamente',
       'De cima para baixo, parando na primeira verdadeira',
       'C' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é a finalidade do bloco "else" ao final de uma cadeia de if/else if?',
       'Repetir o primeiro if',
       'Capturar todos os casos não atendidos pelas condições anteriores',
       'Encerrar o programa',
       'B' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 2;

-- Lição 2.3
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(2, 3, 'switch — Seleção por Casos',
'O switch é uma alternativa elegante ao if/else if quando precisamos comparar uma variável com vários valores fixos e específicos.\n\nComo funciona:\n1. O switch avalia o valor da variável.\n2. Compara com cada "case" (caso).\n3. Executa o bloco do caso correspondente.\n4. O comando "break" encerra o switch e evita que os outros casos sejam executados.\n5. O "default" é executado quando nenhum caso corresponde (equivale ao else).',
'public class Switch {\n    public static void main(String[] args) {\n        int diaDaSemana = 3;\n        String nomeDia;\n\n        switch (diaDaSemana) {\n            case 1: nomeDia = "Segunda-feira"; break;\n            case 2: nomeDia = "Terça-feira";   break;\n            case 3: nomeDia = "Quarta-feira";  break;\n            case 4: nomeDia = "Quinta-feira";  break;\n            case 5: nomeDia = "Sexta-feira";   break;\n            default: nomeDia = "Fim de semana";\n        }\n        System.out.println("Hoje é: " + nomeDia);\n    }\n}');

-- Perguntas da Lição 2.3
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que acontece se você esquecer o "break" dentro de um case no switch?',
       'O programa para de funcionar',
       'A execução "cai" para o próximo case e continua rodando',
       'O Java lança um erro de compilação',
       'B' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual bloco do switch é executado quando NENHUM case corresponde ao valor?',
       'else',
       'case 0',
       'default',
       'C' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Dado diaDaSemana = 3 no código de exemplo, qual será a saída?',
       'Hoje é: Terça-feira',
       'Hoje é: Quarta-feira',
       'Hoje é: Quinta-feira',
       'B' FROM licoes WHERE unidade_numero = 2 AND licao_numero = 3;


-- ============================================================
--  CAPÍTULO 3: Estruturas de Repetição
-- ============================================================

-- Lição 3.1
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(3, 1, 'Loop for — Repetindo com Controle',
'O loop "for" é utilizado quando sabemos EXATAMENTE quantas vezes queremos repetir um bloco de código.\n\nEle possui três partes separadas por ponto e vírgula:\n1. Inicialização: cria e define o valor inicial do contador (int i = 0)\n2. Condição: o loop continua enquanto esta for verdadeira (i < 5)\n3. Incremento: atualiza o contador a cada repetição (i++)\n\nO símbolo i++ é equivalente a i = i + 1.',
'public class LoopFor {\n    public static void main(String[] args) {\n        // Conta de 1 até 5\n        for (int i = 1; i <= 5; i++) {\n            System.out.println("Contagem: " + i);\n        }\n\n        // Soma os números de 1 a 10\n        int soma = 0;\n        for (int j = 1; j <= 10; j++) {\n            soma += j;\n        }\n        System.out.println("Soma de 1 a 10: " + soma); // 55\n    }\n}');

-- Perguntas da Lição 3.1
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Quantas vezes o loop "for (int i = 0; i < 5; i++)" executa seu bloco?',
       '4 vezes',
       '6 vezes',
       '5 vezes',
       'C' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que o operador i++ faz?',
       'Divide i por 2',
       'Incrementa i em 1',
       'Reinicia i para 0',
       'B' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual das três partes do for define até quando o loop deve rodar?',
       'A inicialização',
       'O incremento',
       'A condição',
       'C' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 1;

-- Lição 3.2
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(3, 2, 'Loop while — Repetindo com Condição',
'O loop "while" (enquanto) é usado quando NÃO sabemos ao certo quantas vezes precisamos repetir, mas sabemos qual condição deve ser verdadeira para continuar.\n\nEle verifica a condição ANTES de executar o bloco. Se a condição for falsa desde o início, o bloco nunca é executado.\n\nATENÇÃO: Sempre garanta que a condição eventualmente se torne falsa, caso contrário você criará um "loop infinito" que trava o programa!',
'public class LoopWhile {\n    public static void main(String[] args) {\n        int senha = 1234;\n        int tentativa = 0;\n        int contador = 0;\n\n        while (tentativa != senha) {\n            contador++;\n            // Simulando tentativas\n            if (contador == 1) tentativa = 5678;\n            if (contador == 2) tentativa = 9999;\n            if (contador == 3) tentativa = 1234; // Acerto!\n        }\n        System.out.println("Senha correta após " + contador + " tentativa(s)!");\n    }\n}');

-- Perguntas da Lição 3.2
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Quando o loop while verifica sua condição?',
       'Após executar o bloco',
       'Antes de executar o bloco',
       'Nunca, ele sempre executa pelo menos uma vez',
       'B' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que é um "loop infinito"?',
       'Um loop que executa exatamente infinitas vezes e termina',
       'Um loop cuja condição nunca se torna falsa, travando o programa',
       'Um loop com mais de 1000 iterações',
       'B' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Em qual situação o while é mais adequado que o for?',
       'Quando sabemos exatamente quantas repetições são necessárias',
       'Quando queremos percorrer um array',
       'Quando não sabemos quantas repetições serão necessárias',
       'C' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 2;

-- Lição 3.3
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(3, 3, 'do-while e Comandos break/continue',
'O do-while é semelhante ao while, mas garante que o bloco seja executado PELO MENOS UMA VEZ, pois a condição é verificada somente ao final.\n\nAlém dos loops em si, temos dois comandos especiais:\n\n• break — interrompe o loop imediatamente, saindo dele.\n• continue — pula o restante do bloco atual e vai para a próxima iteração.',
'public class DoWhileEControle {\n    public static void main(String[] args) {\n        // do-while sempre executa ao menos uma vez\n        int x = 10;\n        do {\n            System.out.println("Executou! x = " + x);\n            x++;\n        } while (x < 5); // condição já era falsa, mas executou 1x\n\n        // break — para no número 5\n        for (int i = 1; i <= 10; i++) {\n            if (i == 5) break;\n            System.out.println("break - i: " + i);\n        }\n\n        // continue — pula o número 5\n        for (int i = 1; i <= 10; i++) {\n            if (i == 5) continue;\n            System.out.println("continue - i: " + i);\n        }\n    }\n}');

-- Perguntas da Lição 3.3
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é a principal diferença entre while e do-while?',
       'O do-while é mais rápido que o while',
       'O do-while garante que o bloco execute pelo menos uma vez',
       'O while não usa condição',
       'B' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que o comando "continue" faz dentro de um loop?',
       'Encerra o loop completamente',
       'Encerra o programa',
       'Pula a iteração atual e vai para a próxima',
       'C' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que o comando "break" faz dentro de um loop?',
       'Encerra o loop imediatamente',
       'Pausa o loop por 1 segundo',
       'Pula para a próxima iteração',
       'A' FROM licoes WHERE unidade_numero = 3 AND licao_numero = 3;


-- ============================================================
--  CAPÍTULO 4: Arrays e Matrizes
-- ============================================================

-- Lição 4.1
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(4, 1, 'Arrays — Listas de Valores',
'Um array (ou vetor) é uma estrutura de dados que armazena vários valores do MESMO tipo em uma única variável.\n\nCada posição do array tem um índice que começa em 0 (zero). Portanto, um array com 5 elementos vai do índice 0 ao índice 4.\n\nSintaxe para criar um array:\n  tipo[] nomeDoArray = new tipo[tamanho];\n\nOu com valores já definidos:\n  tipo[] nomeDoArray = {valor1, valor2, valor3};',
'public class Arrays {\n    public static void main(String[] args) {\n        // Criando um array de 5 inteiros\n        int[] notas = {85, 92, 78, 95, 60};\n\n        // Acessando pelo índice\n        System.out.println("Primeira nota: " + notas[0]); // 85\n        System.out.println("Última nota: " + notas[4]);   // 60\n\n        // Tamanho do array\n        System.out.println("Total de notas: " + notas.length); // 5\n\n        // Percorrendo com for\n        for (int i = 0; i < notas.length; i++) {\n            System.out.println("Nota " + (i+1) + ": " + notas[i]);\n        }\n    }\n}');

-- Perguntas da Lição 4.1
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Em Java, qual é o índice do PRIMEIRO elemento de um array?',
       '1',
       '0',
       '-1',
       'B' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Dado int[] nums = {10, 20, 30, 40, 50}; qual é o valor de nums[3]?',
       '30',
       '50',
       '40',
       'C' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Como obtemos o número de elementos de um array chamado "notas" em Java?',
       'notas.size()',
       'notas.length',
       'length(notas)',
       'B' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 1;

-- Lição 4.2
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(4, 2, 'for-each — Percorrendo Arrays Facilmente',
'O for-each (ou "enhanced for") é uma forma simplificada de percorrer todos os elementos de um array sem precisar gerenciar o índice manualmente.\n\nSintaxe:\nfor (tipo variavel : array) { ... }\n\nEm cada repetição, a variável recebe automaticamente o valor do próximo elemento.\n\nUSO: Ideal quando você quer apenas LER os valores do array sem precisar modificá-los ou saber o índice.',
'public class ForEach {\n    public static void main(String[] args) {\n        String[] frutas = {"Maçã", "Banana", "Laranja", "Uva"};\n\n        // for-each — percorre cada fruta\n        for (String fruta : frutas) {\n            System.out.println("Fruta: " + fruta);\n        }\n\n        // Somando com for-each\n        int[] numeros = {5, 10, 15, 20};\n        int soma = 0;\n        for (int n : numeros) {\n            soma += n;\n        }\n        System.out.println("Soma total: " + soma); // 50\n    }\n}');

-- Perguntas da Lição 4.2
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é a principal vantagem do for-each sobre o for tradicional ao percorrer arrays?',
       'É mais rápido em tempo de execução',
       'Não requer gerenciar índices manualmente, tornando o código mais simples',
       'Permite modificar os elementos do array com mais facilidade',
       'B' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é a sintaxe correta do for-each em Java para um array de Strings chamado "nomes"?',
       'for (nomes : String nome) { }',
       'foreach (String nome in nomes) { }',
       'for (String nome : nomes) { }',
       'C' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Dado int[] n = {5, 10, 15, 20}; qual é a soma calculada pelo for-each do exemplo?',
       '45',
       '50',
       '40',
       'B' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 2;

-- Lição 4.3
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(4, 3, 'Matrizes — Arrays Bidimensionais',
'Uma matriz (array bidimensional) é como uma tabela com linhas e colunas. É um array de arrays!\n\nDeclaração: tipo[][] nomeMatriz = new tipo[linhas][colunas];\n\nPara acessar um elemento: matriz[linha][coluna]\n\nLembre-se: os índices começam em 0. Então a primeira linha é [0] e a primeira coluna também é [0].\n\nPara percorrer uma matriz completa, usamos dois loops for aninhados (um dentro do outro).',
'public class Matriz {\n    public static void main(String[] args) {\n        // Matriz 3x3\n        int[][] tabela = {\n            {1, 2, 3},\n            {4, 5, 6},\n            {7, 8, 9}\n        };\n\n        // Acessando elementos\n        System.out.println("Centro: " + tabela[1][1]); // 5\n\n        // Percorrendo com for duplo\n        for (int i = 0; i < tabela.length; i++) {\n            for (int j = 0; j < tabela[i].length; j++) {\n                System.out.print(tabela[i][j] + " ");\n            }\n            System.out.println(); // Quebra de linha por linha\n        }\n    }\n}');

-- Perguntas da Lição 4.3
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Como declaramos uma matriz (array 2D) de inteiros com 3 linhas e 4 colunas em Java?',
       'int[3][4] matriz = new int;',
       'int[][] matriz = new int[3][4];',
       'int matriz = new int[3, 4];',
       'B' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Dado o código de exemplo desta lição, qual é o valor de tabela[2][0]?',
       '7',
       '4',
       '1',
       'A' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Para percorrer todos os elementos de uma matriz, geralmente utilizamos...',
       'Um loop for simples',
       'O comando switch',
       'Dois loops for aninhados (um dentro do outro)',
       'C' FROM licoes WHERE unidade_numero = 4 AND licao_numero = 3;


-- ============================================================
--  CAPÍTULO 5: Introdução à POO (Programação Orientada a Objetos)
-- ============================================================

-- Lição 5.1
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(5, 1, 'Classes e Objetos — O Molde e a Realidade',
'A Programação Orientada a Objetos (POO) é o coração do Java. Nela, modelamos o mundo real em código.\n\nCONCEITOS:\n• Classe → É o MOLDE ou "planta baixa". Define os atributos (características) e métodos (comportamentos).\n• Objeto → É uma INSTÂNCIA da classe, ou seja, uma cópia concreta criada a partir do molde.\n\nPor exemplo: "Carro" é uma classe. O seu carro específico (vermelho, 2023) é um objeto desta classe.\n\nUsar "new" cria um novo objeto a partir de uma classe.',
'// Definindo a classe (o molde)\nclass Cachorro {\n    // Atributos (características)\n    String nome;\n    String raca;\n    int idade;\n\n    // Método (comportamento)\n    void latir() {\n        System.out.println(nome + " diz: Au Au!");\n    }\n}\n\npublic class POO {\n    public static void main(String[] args) {\n        // Criando objetos (instâncias)\n        Cachorro dog1 = new Cachorro();\n        dog1.nome = "Rex";\n        dog1.raca = "Labrador";\n        dog1.idade = 3;\n        dog1.latir(); // Rex diz: Au Au!\n\n        Cachorro dog2 = new Cachorro();\n        dog2.nome = "Bolinha";\n        dog2.latir(); // Bolinha diz: Au Au!\n    }\n}');

-- Perguntas da Lição 5.1
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Em POO, o que é um "objeto"?',
       'O mesmo que uma variável simples',
       'Uma instância concreta criada a partir de uma classe',
       'Um tipo de loop especial do Java',
       'B' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual palavra-chave é usada em Java para criar um novo objeto?',
       'create',
       'object',
       'new',
       'C' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 1;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Na classe Cachorro do exemplo, "nome", "raca" e "idade" são chamados de...',
       'Métodos',
       'Atributos',
       'Construtores',
       'B' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 1;

-- Lição 5.2
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(5, 2, 'Construtores — Inicializando Objetos',
'Um construtor é um método especial dentro de uma classe que é chamado AUTOMATICAMENTE quando um objeto é criado com "new".\n\nCaracterísticas do construtor:\n• Tem o MESMO NOME da classe\n• Não tem tipo de retorno (nem void)\n• É usado para inicializar os atributos do objeto\n\nQuando definimos um construtor com parâmetros, podemos já passar os valores no momento da criação do objeto, tornando o código mais limpo e organizado.',
'class Pessoa {\n    String nome;\n    int idade;\n\n    // Construtor com parâmetros\n    Pessoa(String nome, int idade) {\n        this.nome = nome;   // "this" refere-se ao objeto atual\n        this.idade = idade;\n    }\n\n    void apresentar() {\n        System.out.println("Olá! Sou " + nome + " e tenho " + idade + " anos.");\n    }\n}\n\npublic class Construtores {\n    public static void main(String[] args) {\n        // Criando objetos já com valores\n        Pessoa p1 = new Pessoa("Carlos", 25);\n        Pessoa p2 = new Pessoa("Julia", 30);\n\n        p1.apresentar(); // Olá! Sou Carlos e tenho 25 anos.\n        p2.apresentar(); // Olá! Sou Julia e tenho 30 anos.\n    }\n}');

-- Perguntas da Lição 5.2
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é o nome correto do construtor de uma classe chamada "Veiculo"?',
       'construtor()',
       'init()',
       'Veiculo()',
       'C' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que a palavra-chave "this" representa dentro de um construtor?',
       'O próximo objeto a ser criado',
       'A própria classe em si',
       'A instância atual do objeto',
       'C' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 2;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Quando é chamado o construtor de uma classe?',
       'Toda vez que um método é executado',
       'Automaticamente quando um objeto é criado com "new"',
       'Apenas quando chamamos explicitamente construtor()',
       'B' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 2;

-- Lição 5.3
INSERT INTO licoes (unidade_numero, licao_numero, titulo, texto_explicativo, codigo_exemplo) VALUES
(5, 3, 'Encapsulamento — Protegendo os Dados',
'Encapsulamento é um dos 4 pilares da POO. Consiste em PROTEGER os atributos de uma classe, tornando-os privados (private), e expô-los de forma controlada por meio de métodos públicos chamados Getters e Setters.\n\n• private → o atributo só pode ser acessado de DENTRO da própria classe\n• public → o método pode ser acessado de QUALQUER lugar\n• getter → método que RETORNA o valor do atributo\n• setter → método que DEFINE/MODIFICA o valor do atributo\n\nIsso evita que dados críticos sejam alterados de forma incorreta.',
'class ContaBancaria {\n    private String titular;\n    private double saldo; // Protegido!\n\n    ContaBancaria(String titular, double saldoInicial) {\n        this.titular = titular;\n        this.saldo = saldoInicial;\n    }\n\n    // Getter - apenas lê o saldo\n    public double getSaldo() {\n        return saldo;\n    }\n\n    // Setter com validação\n    public void depositar(double valor) {\n        if (valor > 0) {\n            saldo += valor;\n            System.out.println("Depósito de R$" + valor + " realizado!");\n        }\n    }\n}\n\npublic class Encapsulamento {\n    public static void main(String[] args) {\n        ContaBancaria conta = new ContaBancaria("Maria", 500.0);\n        conta.depositar(250.0);\n        System.out.println("Saldo: R$" + conta.getSaldo()); // 750.0\n        // conta.saldo = -9999; // ERRO! saldo é private\n    }\n}');

-- Perguntas da Lição 5.3
INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'O que o modificador "private" faz em um atributo?',
       'Torna o atributo acessível de qualquer classe',
       'Impede que o atributo seja acessado diretamente fora da sua classe',
       'Impede que o atributo seja alterado mesmo dentro da sua classe',
       'B' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual é a função de um método "getter"?',
       'Modificar o valor de um atributo privado',
       'Criar um novo objeto',
       'Retornar o valor de um atributo privado',
       'C' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 3;

INSERT INTO perguntas (licao_id, pergunta_texto, alternativa_a, alternativa_b, alternativa_c, alternativa_correta)
SELECT id, 'Qual dos 4 pilares da POO consiste em usar private + getters/setters?',
       'Herança',
       'Encapsulamento',
       'Polimorfismo',
       'B' FROM licoes WHERE unidade_numero = 5 AND licao_numero = 3;


-- ============================================================
--  USUÁRIOS DE TESTE
--  Senhas em texto puro (sistema não usa password_hash)
--  admin@gmail.com / 123456  ← conta administrador
-- ============================================================

INSERT INTO usuarios (nome, email, senha, xp, trofeus, dificuldade) VALUES
('Admin',           'admin@gmail.com',   '123456',        2500, 5, 'Avançado'),
('João Silva',      'joao@gmail.com',    'Joao@1234',     800,  2, 'Intermediário'),
('Maria Oliveira',  'maria@gmail.com',   'Maria@1234',    1200, 3, 'Intermediário'),
('Pedro Santos',    'pedro@gmail.com',   'Pedro@1234',    350,  1, 'Iniciante'),
('Ana Costa',       'ana@gmail.com',     'Ana@12345',     0,    0, 'Iniciante');

-- Progresso inicial de cada usuário (unidade 1 = corrente, demais = trancado)
INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas)
SELECT u.id, n.num,
       CASE WHEN n.num = 1 THEN 'corrente' ELSE 'trancado' END,
       CASE
           WHEN u.email = 'admin@gmail.com'  AND n.num = 1 THEN 3
           WHEN u.email = 'admin@gmail.com'  AND n.num = 2 THEN 3
           WHEN u.email = 'admin@gmail.com'  AND n.num = 3 THEN 3
           WHEN u.email = 'admin@gmail.com'  AND n.num = 4 THEN 3
           WHEN u.email = 'admin@gmail.com'  AND n.num = 5 THEN 1
           WHEN u.email = 'joao@gmail.com'   AND n.num = 1 THEN 3
           WHEN u.email = 'joao@gmail.com'   AND n.num = 2 THEN 2
           WHEN u.email = 'maria@gmail.com'  AND n.num = 1 THEN 3
           WHEN u.email = 'maria@gmail.com'  AND n.num = 2 THEN 3
           WHEN u.email = 'maria@gmail.com'  AND n.num = 3 THEN 1
           WHEN u.email = 'pedro@gmail.com'  AND n.num = 1 THEN 1
           ELSE 0
       END
FROM usuarios u
CROSS JOIN (SELECT 1 AS num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) n
WHERE u.email IN ('admin@gmail.com','joao@gmail.com','maria@gmail.com','pedro@gmail.com','ana@gmail.com');

-- Atualiza status das unidades do admin para 'completo' onde cabível
UPDATE progresso_usuario pu
JOIN   usuarios u ON u.id = pu.usuario_id
SET    pu.status = CASE
           WHEN pu.unidade_numero < 5 THEN 'completo'
           ELSE 'corrente'
       END
WHERE  u.email = 'admin@gmail.com';

-- Atualiza status das unidades do João
UPDATE progresso_usuario pu
JOIN   usuarios u ON u.id = pu.usuario_id
SET    pu.status = CASE
           WHEN pu.unidade_numero = 1 THEN 'completo'
           WHEN pu.unidade_numero = 2 THEN 'corrente'
           ELSE 'trancado'
       END
WHERE  u.email = 'joao@gmail.com';

-- Atualiza status das unidades da Maria
UPDATE progresso_usuario pu
JOIN   usuarios u ON u.id = pu.usuario_id
SET    pu.status = CASE
           WHEN pu.unidade_numero <= 2 THEN 'completo'
           WHEN pu.unidade_numero = 3  THEN 'corrente'
           ELSE 'trancado'
       END
WHERE  u.email = 'maria@gmail.com';

