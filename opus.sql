-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: opus
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `opus`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `opus` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `opus`;

--
-- Table structure for table `acertos_diarios`
--

DROP TABLE IF EXISTS `acertos_diarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acertos_diarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_dia` date NOT NULL,
  `acertos` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_dia` (`usuario_id`,`data_dia`),
  CONSTRAINT `fk_acertos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acertos_diarios`
--

LOCK TABLES `acertos_diarios` WRITE;
/*!40000 ALTER TABLE `acertos_diarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `acertos_diarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bau_recompensas`
--

DROP TABLE IF EXISTS `bau_recompensas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bau_recompensas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `unidade_numero` int(11) NOT NULL,
  `tipo_recompensa` varchar(50) NOT NULL DEFAULT 'misto',
  `xp_ganho` int(11) NOT NULL DEFAULT 50,
  `vidas_ganhas` int(11) NOT NULL DEFAULT 0,
  `resgatado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_bau_unidade` (`usuario_id`,`unidade_numero`),
  CONSTRAINT `fk_bau_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bau_recompensas`
--

LOCK TABLES `bau_recompensas` WRITE;
/*!40000 ALTER TABLE `bau_recompensas` DISABLE KEYS */;
/*!40000 ALTER TABLE `bau_recompensas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `catalogo_trofeus`
--

DROP TABLE IF EXISTS `catalogo_trofeus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catalogo_trofeus` (
  `slug` varchar(80) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `listado` tinyint(1) NOT NULL DEFAULT 0,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `icone` varchar(60) NOT NULL DEFAULT 'fa-trophy',
  `imagem` varchar(255) DEFAULT NULL,
  `cor` varchar(30) NOT NULL DEFAULT '#ffd700',
  `raridade` enum('comum','raro','epico','lendario') NOT NULL DEFAULT 'comum',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `catalogo_trofeus`
--

LOCK TABLES `catalogo_trofeus` WRITE;
/*!40000 ALTER TABLE `catalogo_trofeus` DISABLE KEYS */;
INSERT INTO `catalogo_trofeus` VALUES ('capitulo_1','Fundamentos','Terminou o Capítulo 1.',1,3,'fa-award','trofeu_cap1.png','#22c55e','comum','2026-09-27 02:22:37'),('capitulo_2','Caminhos Lógicos','Dominou as Estruturas de Decisão no Cap. 2.',1,4,'fa-shield-halved','trofeu_cap2.png','#3b82f6','raro','2026-09-27 02:22:37'),('capitulo_3','Mestre da Repetição','Dominou os Loops no Capítulo 3.',1,5,'fa-repeat','trofeu_cap3.png','#a855f7','epico','2026-09-27 02:22:37'),('capitulo_4','Senhor dos Arrays','Dominou Arrays e Matrizes no Capítulo 4.',1,6,'fa-database','trofeu_cap4.png','#ec4899','epico','2026-09-27 02:22:37'),('capitulo_5','Arquiteto Java','Concluiu POO no Capítulo 5. Você é o mestre!',1,7,'fa-crown','trofeu_cap5.png','#ffd700','lendario','2026-09-27 02:22:37'),('fogo_3','Em Chamas','Alcançou 3 Dias de Fogo.',1,8,'fa-fire','trofeu_fogo.png','#f97316','raro','2026-09-27 02:22:37'),('perfeicao','Mente Brilhante','Acertou 3/3 em um desafio.',1,2,'fa-brain','trofeu_cerebro.png','#1cb0f6','raro','2026-09-27 02:22:37'),('precisao_absoluta','Precisão Absoluta','Alcançou 150 XP acumulados.',0,15,'fa-bullseye','trofeu_alvo.png','#1cb0f6','raro','2026-09-27 02:22:37'),('primeiro_passo','Primeiro Passo','Concluiu sua primeira lição.',1,1,'fa-star','medalha_estrela.png','#ffd700','comum','2026-09-27 02:22:37'),('sequencia_30','Mês Épico','Alcançou 30 Dias de Fogo.',0,17,'fa-fire-flame-simple','trofeu_fogo30.png','#a855f7','lendario','2026-09-27 02:22:37'),('sequencia_7','Semana Perfeita','Alcançou 7 Dias de Fogo.',0,16,'fa-fire-flame-curved','trofeu_fogo7.png','#ef4444','epico','2026-09-27 02:22:37');
/*!40000 ALTER TABLE `catalogo_trofeus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conquistas_usuario`
--

DROP TABLE IF EXISTS `conquistas_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `conquistas_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `conquista_slug` varchar(80) NOT NULL,
  `data_desbloqueio` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_conquista` (`usuario_id`,`conquista_slug`),
  CONSTRAINT `fk_conquistas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conquistas_usuario`
--

LOCK TABLES `conquistas_usuario` WRITE;
/*!40000 ALTER TABLE `conquistas_usuario` DISABLE KEYS */;
INSERT INTO `conquistas_usuario` VALUES (1,1,'perfeicao','2026-09-26 23:23:00'),(2,1,'primeiro_passo','2026-09-26 23:23:00'),(3,1,'precisao_absoluta','2026-09-26 23:23:00');
/*!40000 ALTER TABLE `conquistas_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_membros`
--

DROP TABLE IF EXISTS `grupo_membros`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo_membros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grupo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_entrada` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_grupo_user` (`grupo_id`,`usuario_id`),
  KEY `fk_grupo_membros_usuario` (`usuario_id`),
  CONSTRAINT `fk_grupo_membros_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `grupos_batalha` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_grupo_membros_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_membros`
--

LOCK TABLES `grupo_membros` WRITE;
/*!40000 ALTER TABLE `grupo_membros` DISABLE KEYS */;
/*!40000 ALTER TABLE `grupo_membros` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos_batalha`
--

DROP TABLE IF EXISTS `grupos_batalha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupos_batalha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `codigo_convite` varchar(20) NOT NULL,
  `criador_id` int(11) NOT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_convite` (`codigo_convite`),
  KEY `fk_grupos_criador` (`criador_id`),
  CONSTRAINT `fk_grupos_criador` FOREIGN KEY (`criador_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos_batalha`
--

LOCK TABLES `grupos_batalha` WRITE;
/*!40000 ALTER TABLE `grupos_batalha` DISABLE KEYS */;
/*!40000 ALTER TABLE `grupos_batalha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `licoes`
--

DROP TABLE IF EXISTS `licoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `licoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_numero` int(11) NOT NULL,
  `licao_numero` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `texto_explicativo` text NOT NULL,
  `codigo_exemplo` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_unidade_licao` (`unidade_numero`,`licao_numero`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licoes`
--

LOCK TABLES `licoes` WRITE;
/*!40000 ALTER TABLE `licoes` DISABLE KEYS */;
INSERT INTO `licoes` VALUES (1,1,1,'Olá, Mundo! — Seu primeiro programa Java','Java é uma linguagem de programação orientada a objetos, fortemente tipada e amplamente utilizada no mercado mundial.\n\nTodo programa em Java precisa estar contido dentro de uma CLASSE. O nome do arquivo deve ser exatamente igual ao nome da classe pública.\n\nO método especial \'main\' é a porta de entrada da sua aplicação. É através dele que a máquina virtual Java (JVM) inicia a execução das instruções.\n\nO comando System.out.println() imprime mensagens no console e quebra a linha automaticamente ao final.','public class OlaMundo {\n    public static void main(String[] args) {\n        System.out.println(\"Olá, Mundo!\");\n        System.out.println(\"Bem-vindo ao OPUS!\");\n    }\n}','2026-09-27 02:22:38'),(2,1,2,'Variáveis e Tipos Primitivos','Uma variável é um espaço alocado na memória do computador para armazenar temporariamente dados durante a execução do programa.\n\nJava possui tipagem estática e forte: você deve declarar o tipo exato de cada variável antes de utilizá-la.\n\nOs principais tipos primitivos em Java são:\n• int: para números inteiros (ex: 25, -10)\n• double: para números com casas decimais (ex: 3.14, 100.50)\n• boolean: valores lógicos (true ou false)\n• char: para um único caractere entre aspas simples (ex: \'A\')\n• String: para textos e cadeias de caracteres entre aspas duplas.','public class Variaveis {\n    public static void main(String[] args) {\n        int idade = 21;\n        double altura = 1.78;\n        char genero = \'M\';\n        boolean ativo = true;\n        String nome = \"Gabriel\";\n        \n        System.out.println(\"Nome: \" + nome + \" | Idade: \" + idade);\n    }\n}','2026-09-27 02:22:38'),(3,1,3,'Operadores Aritméticos e Expressões','Operadores aritméticos realizam operações matemáticas fundamentais sobre números.\n\nOs operadores básicos são:\n• + (Adição e concatenação de textos)\n• - (Subtração)\n• * (Multiplicação)\n• / (Divisão)\n• % (Módulo: resto da divisão inteira)\n\nImportante: dividir dois inteiros sempre resulta em um número inteiro truncado. Para obter casas decimais, pelo menos um dos valores deve ser double ou float.','public class Calculos {\n    public static void main(String[] args) {\n        int a = 10;\n        int b = 3;\n        \n        System.out.println(\"Soma: \" + (a + b));        // 13\n        System.out.println(\"Divisão: \" + (a / b));    // 3\n        System.out.println(\"Resto (%): \" + (a % b));  // 1\n    }\n}','2026-09-27 02:22:38'),(4,1,4,'Entrada de Dados com Scanner','Para interagir com o usuário e ler dados fornecidos via teclado pelo console, utilizamos a classe Scanner do pacote java.util.\n\nPrimeiro precisamos importar a classe com \'import java.util.Scanner;\' no início do arquivo.\n\nPrincipais métodos de leitura:\n• nextLine(): lê uma linha inteira de texto (String)\n• nextInt(): lê um número inteiro\n• nextDouble(): lê um número decimal\n\nÉ uma boa prática fechar o scanner com scanner.close() quando terminar de usar.','import java.util.Scanner;\n\npublic class Entrada {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        System.out.print(\"Digite sua idade: \");\n        int idade = sc.nextInt();\n        System.out.println(\"Você tem \" + idade + \" anos!\");\n        sc.close();\n    }\n}','2026-09-27 02:22:38'),(5,1,5,'Casting e Conversão de Tipos','Casting é o mecanismo de converter um dado de um tipo para outro em Java.\n\nExistem dois tipos principais de casting:\n• Implícito (Widening): Conversão automática sem perda de dados (ex: int para double).\n• Explícito (Narrowing): Conversão manual exigida pelo compilador com possível perda de precisão (ex: double para int com sintaxe (int) valor).\n\nPara converter strings em números usamos métodos estáticos como Integer.parseInt() e Double.parseDouble().','public class Conversao {\n    public static void main(String[] args) {\n        double valor = 9.99;\n        int inteiro = (int) valor; // casting explícito: vira 9\n        \n        String texto = \"150\";\n        int numero = Integer.parseInt(texto);\n        System.out.println(\"Resultado: \" + (numero + inteiro));\n    }\n}','2026-09-27 02:22:38'),(6,2,1,'Estrutura Condicional: if e else','As estruturas condicionais permitem que seu programa tome decisões e execute diferentes blocos de código com base em condições lógicas.\n\nO bloco \'if\' só é executado se a condição fornecida entre parênteses for avaliada como verdadeira (true).\n\nO bloco \'else\' é opcional e será executado somente quando a condição do \'if\' for falsa (false).\n\nOperadores relacionais utilizados: == (igual), != (diferente), > (maior), < (menor), >= (maior igual), <= (menor igual).','public class Decisao {\n    public static void main(String[] args) {\n        int nota = 75;\n        if (nota >= 70) {\n            System.out.println(\"Aprovado! Parabéns!\");\n        } else {\n            System.out.println(\"Estude mais e tente novamente.\");\n        }\n    }\n}','2026-09-27 02:22:38'),(7,2,2,'Múltiplas Condições: else if','Quando um problema exige avaliar mais de duas possibilidades exclusivas, encadeamos vários blocos \'else if\'.\n\nO Java avalia as condições sequencialmente, do topo para baixo. Assim que a primeira condição verdadeira for encontrada, seu bloco é executado e todas as demais condições subsequentes são ignoradas.\n\nO \'else\' final serve como alternativa padrão caso nenhuma das condições anteriores seja atendida.','public class Classificacao {\n    public static void main(String[] args) {\n        int pontuacao = 85;\n        if (pontuacao >= 90) {\n            System.out.println(\"Rank: Diamante\");\n        } else if (pontuacao >= 70) {\n            System.out.println(\"Rank: Ouro\");\n        } else {\n            System.out.println(\"Rank: Bronze\");\n        }\n    }\n}','2026-09-27 02:22:38'),(8,2,3,'Seleção Múltipla: switch-case','A instrução \'switch\' é uma maneira estruturada e limpa de testar o valor de uma única variável contra múltiplos valores constantes pré-definidos.\n\nCada opção é definida por uma cláusula \'case\'.\n\nA palavra-chave \'break\' é fundamental: ela encerra a execução do switch. Se esquecer o break, o código continuará executando os cases seguintes (\'fall-through\').\n\nO \'default\' funciona como o else genérico do switch.','public class Menu {\n    public static void main(String[] args) {\n        int opcao = 2;\n        switch (opcao) {\n            case 1: System.out.println(\"Iniciando Jogo...\"); break;\n            case 2: System.out.println(\"Carregando Perfil...\"); break;\n            default: System.out.println(\"Opção Inválida!\");\n        }\n    }\n}','2026-09-27 02:22:38'),(9,2,4,'Operadores Lógicos (E, OU, NÃO)','Operadores lógicos permitem combinar múltiplas expressões booleanas em uma única condição complexa.\n\n• && (AND / E): Retorna true apenas se AMBAS as condições forem verdadeiras.\n• || (OR / OU): Retorna true se PELO MENOS UMA condição for verdadeira.\n• ! (NOT / NÃO): Inverte o valor lógico de uma expressão (!true vira false).','public class Logica {\n    public static void main(String[] args) {\n        int idade = 20;\n        boolean temCarteira = true;\n        if (idade >= 18 && temCarteira) {\n            System.out.println(\"Autorizado a dirigir!\");\n        }\n    }\n}','2026-09-27 02:22:38'),(10,2,5,'Operador Ternário','O operador ternário \'?:\' é uma sintaxe compacta em uma única linha para representar uma condicional que retorna um valor.\n\nSintaxe:\nvariavel = (condicao) ? valor_se_verdadeiro : valor_se_falso;\n\nÉ excelente para atribuições rápidas e simples, deixando o código mais limpo.','public class Ternario {\n    public static void main(String[] args) {\n        int xp = 120;\n        String nivel = (xp >= 100) ? \"Veterano\" : \"Novato\";\n        System.out.println(\"Nível do jogador: \" + nivel);\n    }\n}','2026-09-27 02:22:38'),(11,3,1,'Laço de Repetição: for','O laço \'for\' é a estrutura de repetição ideal quando sabemos previamente a quantidade exata de repetições necessárias.\n\nO for é composto por 3 partes separadas por ponto e vírgula:\n1. Inicialização: declaração da variável contadora (int i = 0)\n2. Condição de continuidade: repete enquanto for verdadeira (i < 5)\n3. Incremento/Passo: atualização do contador a cada volta (i++).','public class LoopFor {\n    public static void main(String[] args) {\n        for (int i = 1; i <= 5; i++) {\n            System.out.println(\"Executando passo \" + i);\n        }\n    }\n}','2026-09-27 02:22:38'),(12,3,2,'Laço de Repetição: while','O laço \'while\' repete um bloco de comandos ENQUANTO uma condição for verdadeira. É usado principalmente quando não sabemos de antemão quantas vezes o código precisará repetir.\n\nA condição é checada ANTES de cada iteração. Se a condição começar falsa, o bloco interno não será executado nenhuma vez.\n\nCuidado: sempre modifique a variável de controle dentro do laço para evitar laços infinitos!','public class LoopWhile {\n    public static void main(String[] args) {\n        int contador = 1;\n        while (contador <= 3) {\n            System.out.println(\"Contagem: \" + contador);\n            contador++;\n        }\n    }\n}','2026-09-27 02:22:38'),(13,3,3,'Laço do-while e Controles de Fluxo','O laço \'do-while\' garante que o bloco de código seja executado pelo menos uma vez, porque a condição de repetição só é avaliada ao FINAL do bloco.\n\nComandos especiais de controle em laços:\n• break: interrompe e encerra o laço imediatamente.\n• continue: pula o restante da iteração atual e avança para a próxima.','public class DoWhileDemo {\n    public static void main(String[] args) {\n        int num = 10;\n        do {\n            System.out.println(\"Executou mesmo com condição falsa!\");\n        } while (num < 5);\n    }\n}','2026-09-27 02:22:38'),(14,3,4,'Laços Aninhados (Nested Loops)','Laços aninhados ocorrem quando colocamos um laço de repetição dentro de outro laço.\n\nPara cada iteração do laço externo, o laço interno executa todo o seu ciclo completo do início ao fim.\n\nSão muito utilizados para manipular tabelas, matrizes bidimensionais e processar coordenadas geométricas.','public class Aninhado {\n    public static void main(String[] args) {\n        for (int i = 1; i <= 3; i++) {\n            for (int j = 1; j <= 2; j++) {\n                System.out.println(\"i=\" + i + \", j=\" + j);\n            }\n        }\n    }\n}','2026-09-27 02:22:38'),(15,3,5,'Laços com Strings e Caracteres','Podemos percorrer cada caractere de um texto em Java combinando laços \'for\' com os métodos length() e charAt() da classe String.\n\nO método texto.length() retorna o tamanho total de caracteres.\nO método texto.charAt(i) obtém o caractere na posição de índice \'i\' (começando do índice 0).','public class PercorrerTexto {\n    public static void main(String[] args) {\n        String palavra = \"JAVA\";\n        for (int i = 0; i < palavra.length(); i++) {\n            System.out.println(\"Caractere: \" + palavra.charAt(i));\n        }\n    }\n}','2026-09-27 02:22:38'),(16,4,1,'Vetores e Arrays Unidimensionais','Um Array (vetor) é uma estrutura de dados de tamanho fixo capaz de armazenar múltiplos elementos do mesmo tipo sob o mesmo nome de variável.\n\nOs índices de um array começam sempre em 0 e vão até (tamanho - 1).\n\nPara saber o tamanho de um array em Java, utilizamos o atributo \'.length\'.','public class ArrayDemo {\n    public static void main(String[] args) {\n        int[] notas = {90, 85, 100, 75};\n        System.out.println(\"Primeira nota: \" + notas[0]);\n        System.out.println(\"Total de notas: \" + notas.length);\n    }\n}','2026-09-27 02:22:38'),(17,4,2,'Laço Enhanced For (For-Each)','O laço \'for-each\' (ou enhanced for) é uma sintaxe simplificada e moderna em Java projetada para percorrer todos os itens de um array ou coleção do início ao fim sem necessidade de controlar índices manuais.\n\nSintaxe: for (Tipo item : array) { ... }','public class ForEachDemo {\n    public static void main(String[] args) {\n        String[] linguagens = {\"Java\", \"Python\", \"C++\", \"Kotlin\"};\n        for (String lang : linguagens) {\n            System.out.println(\"Linguagem: \" + lang);\n        }\n    }\n}','2026-09-27 02:22:38'),(18,4,3,'Matrizes e Arrays Multidimensionais','Uma Matriz é um array de arrays, funcionando visualmente como uma tabela organizada em linhas e colunas.\n\nDeclaração: int[][] matriz = new int[linhas][colunas];\n\nPara acessar um valor específico, usamos a coordenada [linha][coluna], como matriz[0][1]. Para iterar sobre a matriz completa, utilizamos dois loops for aninhados.','public class MatrizDemo {\n    public static void main(String[] args) {\n        int[][] grid = {\n            {1, 2, 3},\n            {4, 5, 6}\n        };\n        System.out.println(\"Elemento linha 1, coluna 2: \" + grid[1][2]);\n    }\n}','2026-09-27 02:22:38'),(19,4,4,'Manipulação e Algoritmos com Arrays','Operações clássicas em arrays incluem: busca de elementos, soma de valores, encontrar o maior ou menor número e cálculo de médias estatísticas.\n\nA classe utilitária java.util.Arrays fornece métodos prontos como Arrays.sort() para ordenação e Arrays.toString() para impressão facilitada.','import java.util.Arrays;\n\npublic class ArrayUtils {\n    public static void main(String[] args) {\n        int[] nums = {5, 2, 8, 1};\n        Arrays.sort(nums); // ordena: [1, 2, 5, 8]\n        System.out.println(Arrays.toString(nums));\n    }\n}','2026-09-27 02:22:38'),(20,4,5,'Introdução às Coleções: ArrayList','Ao contrário dos arrays primitivos que possuem tamanho fixo e imutável, a classe ArrayList do pacote java.util representa uma lista dinâmica cujo tamanho cresce e diminui automaticamente conforme adicionamos ou removemos elementos.\n\nMétodos essenciais: add(), get(index), remove(index), size().','import java.util.ArrayList;\n\npublic class ListaDemo {\n    public static void main(String[] args) {\n        ArrayList<String> lista = new ArrayList<>();\n        lista.add(\"Espada\");\n        lista.add(\"Escudo\");\n        System.out.println(\"Tamanho: \" + lista.size());\n    }\n}','2026-09-27 02:22:38'),(21,5,1,'Introdução à Orientação a Objetos: Classes e Objetos','A Programação Orientada a Objetos (POO) é o paradigma central do Java, organizando o código em moldes chamados Classes e instâncias chamadas Objetos.\n\n• Classe: É a definição abstrata (o molde), contendo atributos (características/dados) e métodos (comportamentos/ações).\n• Objeto: É uma instância real criada na memória através da palavra-chave \'new\'.','class Guerreiro {\n    String nome;\n    int nivel;\n    void atacar() {\n        System.out.println(nome + \" atacou causando dano!\");\n    }\n}\n\npublic class Jogo {\n    public static void main(String[] args) {\n        Guerreiro g1 = new Guerreiro();\n        g1.nome = \"Arthur\";\n        g1.atacar();\n    }\n}','2026-09-27 02:22:38'),(22,5,2,'Construtores e a Palavra-chave this','Um Construtor é um método especial invocado automaticamente no momento exato em que um novo objeto é instanciado com \'new\'.\n\nRegras do construtor:\n• Possui exatamente o mesmo nome da Classe.\n• Não possui tipo de retorno (nem mesmo void).\n\nA palavra-chave \'this\' faz referência explícita aos atributos da instância atual do objeto.','class Jogador {\n    String nome;\n    int vidas;\n    \n    Jogador(String nome, int vidas) {\n        this.nome = nome;\n        this.vidas = vidas;\n    }\n}\n\npublic class Main {\n    public static void main(String[] args) {\n        Jogador j = new Jogador(\"Gabriel\", 3);\n        System.out.println(j.nome + \" tem \" + j.vidas + \" vidas.\");\n    }\n}','2026-09-27 02:22:38'),(23,5,3,'Encapsulamento, Getters e Setters','O Encapsulamento protege a integridade dos dados internos de uma classe, ocultando atributos com o modificador \'private\' e permitindo acesso controlado através de métodos públicos (getters para leitura e setters para escrita com validação).','class Conta {\n    private double saldo;\n    \n    public double getSaldo() { return this.saldo; }\n    \n    public void depositar(double valor) {\n        if (valor > 0) this.saldo += valor;\n    }\n}\n\npublic class Banco {\n    public static void main(String[] args) {\n        Conta c = new Conta();\n        c.depositar(150.0);\n        System.out.println(\"Saldo: \" + c.getSaldo());\n    }\n}','2026-09-27 02:22:38'),(24,5,4,'Herança e Sobrescrita de Métodos (@Override)','Herança permite que uma nova classe (subclasse) herde atributos e métodos de uma classe existente (superclasse) através da palavra-chave \'extends\', promovendo reuso de código.\n\nA anotação \'@Override\' indica que a subclasse está redefinindo o comportamento de um método herdado.','class Animal {\n    void emitirSom() {\n        System.out.println(\"Som genérico\");\n    }\n}\n\nclass Cachorro extends Animal {\n    @Override\n    void emitirSom() {\n        System.out.println(\"Au Au!\");\n    }\n}','2026-09-27 02:22:38'),(25,5,5,'Polimorfismo e Classes Abstratas','Polimorfismo significa \'muitas formas\': a capacidade de tratar objetos de diferentes subclasses através de uma referência da superclasse comum.\n\nClasses abstratas (definidas com a palavra-chave \'abstract\') servem como modelo base e não podem ser instanciadas diretamente com \'new\'.','abstract class Personagem {\n    abstract void especial();\n}\n\nclass Mago extends Personagem {\n    void especial() {\n        System.out.println(\"Lançou Bola de Fogo!\");\n    }\n}','2026-09-27 02:22:38');
/*!40000 ALTER TABLE `licoes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ligas_grupos`
--

DROP TABLE IF EXISTS `ligas_grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ligas_grupos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `divisao` enum('bronze','prata','ouro','diamante','mestre') NOT NULL,
  `semana_ref` date NOT NULL,
  `capacidade` int(11) NOT NULL DEFAULT 30,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `divisao_semana` (`divisao`,`semana_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ligas_grupos`
--

LOCK TABLES `ligas_grupos` WRITE;
/*!40000 ALTER TABLE `ligas_grupos` DISABLE KEYS */;
INSERT INTO `ligas_grupos` VALUES (1,'bronze','2026-09-21',30,'2026-09-26 23:22:38'),(2,'prata','2026-09-21',30,'2026-09-26 23:22:38'),(3,'ouro','2026-09-21',30,'2026-09-26 23:22:38'),(4,'diamante','2026-09-21',30,'2026-09-26 23:22:38'),(5,'mestre','2026-09-21',30,'2026-09-26 23:22:38'),(6,'bronze','2026-09-28',30,'2026-10-04 21:01:36'),(7,'prata','2026-09-28',30,'2026-10-04 21:01:36'),(8,'ouro','2026-09-28',30,'2026-10-04 21:01:36'),(9,'diamante','2026-09-28',30,'2026-10-04 21:01:36'),(10,'mestre','2026-09-28',30,'2026-10-04 21:01:36');
/*!40000 ALTER TABLE `ligas_grupos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ligas_historico`
--

DROP TABLE IF EXISTS `ligas_historico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ligas_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `divisao_anterior` varchar(20) DEFAULT NULL,
  `divisao_nova` varchar(20) DEFAULT NULL,
  `resultado` varchar(20) DEFAULT NULL,
  `posicao_final` int(11) DEFAULT NULL,
  `xp_final` int(11) DEFAULT NULL,
  `semana_ref` date DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_hist` (`usuario_id`),
  CONSTRAINT `fk_ligas_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ligas_historico`
--

LOCK TABLES `ligas_historico` WRITE;
/*!40000 ALTER TABLE `ligas_historico` DISABLE KEYS */;
/*!40000 ALTER TABLE `ligas_historico` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ligas_usuario`
--

DROP TABLE IF EXISTS `ligas_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ligas_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `divisao` enum('bronze','prata','ouro','diamante','mestre') DEFAULT 'bronze',
  `grupo_id` int(11) NOT NULL,
  `xp_semana` int(11) DEFAULT 0,
  `posicao_semana_anterior` int(11) DEFAULT NULL,
  `ultima_atualizacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuario_semana` (`usuario_id`,`grupo_id`),
  KEY `idx_divisao_grupo` (`divisao`,`grupo_id`),
  CONSTRAINT `fk_ligas_usuario_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ligas_usuario`
--

LOCK TABLES `ligas_usuario` WRITE;
/*!40000 ALTER TABLE `ligas_usuario` DISABLE KEYS */;
INSERT INTO `ligas_usuario` VALUES (1,1,'bronze',1,200,NULL,'2026-09-26 23:22:38'),(2,2,'bronze',1,100,NULL,'2026-09-26 23:22:38'),(3,3,'bronze',1,150,NULL,'2026-09-26 23:22:38'),(4,4,'bronze',1,200,NULL,'2026-09-26 23:22:38'),(5,5,'bronze',1,250,NULL,'2026-09-26 23:22:38'),(6,3,'bronze',0,45,NULL,'2026-10-04 21:01:36'),(7,7,'bronze',0,25,NULL,'2026-10-04 21:01:36'),(8,8,'bronze',0,15,NULL,'2026-10-04 21:01:36'),(9,4,'prata',0,140,NULL,'2026-10-04 21:01:36'),(10,9,'prata',0,180,NULL,'2026-10-04 21:01:36'),(11,10,'prata',0,110,NULL,'2026-10-04 21:01:36'),(12,5,'ouro',0,320,NULL,'2026-10-04 21:01:36'),(13,11,'ouro',0,410,NULL,'2026-10-04 21:01:36'),(14,12,'ouro',0,290,NULL,'2026-10-04 21:01:36'),(15,13,'ouro',0,260,NULL,'2026-10-04 21:01:36'),(16,14,'diamante',0,650,NULL,'2026-10-04 21:01:36'),(17,15,'diamante',0,780,NULL,'2026-10-04 21:01:36'),(18,16,'diamante',0,590,NULL,'2026-10-04 21:01:36'),(19,1,'mestre',0,950,NULL,'2026-10-04 21:01:36'),(20,2,'mestre',0,1200,NULL,'2026-10-04 21:01:36'),(21,17,'mestre',0,1550,NULL,'2026-10-04 21:01:36'),(22,18,'mestre',0,1180,NULL,'2026-10-04 21:01:36'),(23,19,'mestre',0,1340,NULL,'2026-10-04 21:01:36'),(24,21,'bronze',6,0,NULL,'2026-10-04 21:06:18');
/*!40000 ALTER TABLE `ligas_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `missoes_diarias_usuario`
--

DROP TABLE IF EXISTS `missoes_diarias_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `missoes_diarias_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_ref` date NOT NULL,
  `xp_ganho` int(11) NOT NULL DEFAULT 0,
  `licoes_concluidas` int(11) NOT NULL DEFAULT 0,
  `licoes_perfeitas` int(11) NOT NULL DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_data_missao` (`usuario_id`,`data_ref`),
  CONSTRAINT `fk_missoes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missoes_diarias_usuario`
--

LOCK TABLES `missoes_diarias_usuario` WRITE;
/*!40000 ALTER TABLE `missoes_diarias_usuario` DISABLE KEYS */;
INSERT INTO `missoes_diarias_usuario` VALUES (1,1,'2026-09-26',50,1,1,'2026-09-27 02:23:00','2026-09-27 02:23:00');
/*!40000 ALTER TABLE `missoes_diarias_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perguntas`
--

DROP TABLE IF EXISTS `perguntas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perguntas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licao_id` int(11) NOT NULL,
  `pergunta_texto` text NOT NULL,
  `tipo` enum('multipla_escolha','completar','digitar') NOT NULL DEFAULT 'multipla_escolha',
  `alternativa_a` varchar(500) NOT NULL,
  `alternativa_b` varchar(500) NOT NULL,
  `alternativa_c` varchar(500) NOT NULL,
  `alternativa_correta` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_licao_id` (`licao_id`),
  CONSTRAINT `fk_perguntas_licao` FOREIGN KEY (`licao_id`) REFERENCES `licoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perguntas`
--

LOCK TABLES `perguntas` WRITE;
/*!40000 ALTER TABLE `perguntas` DISABLE KEYS */;
INSERT INTO `perguntas` VALUES (1,1,'Qual comando é utilizado em Java para exibir texto na tela e quebrar a linha?','multipla_escolha','System.out.println()','console.log()','print_line()','A','2026-09-27 02:22:38'),(2,1,'Todo código executável em Java deve estar contido dentro de uma:','multipla_escolha','Classe','Função Solta','Variável Global','A','2026-09-27 02:22:38'),(3,1,'Qual é o método que serve como ponto de entrada principal de um programa Java?','multipla_escolha','main','start','run','A','2026-09-27 02:22:38'),(4,2,'Qual tipo de dado você usaria para armazenar a idade de uma pessoa (número inteiro)?','multipla_escolha','int','double','boolean','A','2026-09-27 02:22:38'),(5,2,'Qual tipo primitivo armazena números com casas decimais em Java?','multipla_escolha','double','int','char','A','2026-09-27 02:22:38'),(6,2,'Qual valor é válido para uma variável do tipo boolean?','multipla_escolha','true','\"verdadeiro\"','1.0','A','2026-09-27 02:22:38'),(7,3,'Qual operador é utilizado para obter o resto de uma divisão inteira em Java?','multipla_escolha','%','/','#','A','2026-09-27 02:22:38'),(8,3,'Qual é o resultado da expressão inteira: 10 / 3 em Java?','multipla_escolha','3','3.33','4','A','2026-09-27 02:22:38'),(9,3,'Qual operador aritmético realiza a multiplicação?','multipla_escolha','*','x','^','A','2026-09-27 02:22:38'),(10,4,'Qual classe do pacote java.util é usada para ler dados do teclado?','multipla_escolha','Scanner','Reader','InputConsole','A','2026-09-27 02:22:38'),(11,4,'Qual método do Scanner lê uma linha de texto inteira digitada?','multipla_escolha','nextLine()','readText()','nextWord()','A','2026-09-27 02:22:38'),(12,4,'O que devemos fazer após terminar de usar um objeto Scanner?','multipla_escolha','Chamar scanner.close()','Apagar a variável','Reiniciar a JVM','A','2026-09-27 02:22:38'),(13,5,'Qual é a sintaxe correta para fazer o casting explícito de um double para int?','multipla_escolha','(int) valor','int(valor)','valor.toInt()','A','2026-09-27 02:22:38'),(14,5,'Qual método converte uma String contendo números em um int?','multipla_escolha','Integer.parseInt()','String.toInt()','Convert.int()','A','2026-09-27 02:22:38'),(15,5,'A conversão de um int para double é chamada de casting:','multipla_escolha','Implícito (Widening)','Explícito (Narrowing)','Inválido','A','2026-09-27 02:22:38'),(16,6,'Qual estrutura condicional executa um bloco se a condição for verdadeira?','multipla_escolha','if','for','while','A','2026-09-27 02:22:38'),(17,6,'Qual operador relacional verifica se dois valores são estritamente iguais?','multipla_escolha','==','=','equals','A','2026-09-27 02:22:38'),(18,6,'Qual bloco opcional é executado quando o if é falso?','multipla_escolha','else','stop','default','A','2026-09-27 02:22:38'),(19,7,'Para testar múltiplas condições exclusivas sequencialmente, usamos:','multipla_escolha','else if','repeat if','then','A','2026-09-27 02:22:38'),(20,7,'Se a primeira condição de um else if for verdadeira, o que acontece com as seguintes?','multipla_escolha','São ignoradas','São todas executadas','Causa erro','A','2026-09-27 02:22:38'),(21,7,'Qual operador relacional representa \\\"maior ou igual a\\\"?','multipla_escolha','>=','=>','>','A','2026-09-27 02:22:38'),(22,8,'Qual comando encerra a execução de um switch evitando executar os próximos cases?','multipla_escolha','break','exit','stop','A','2026-09-27 02:22:38'),(23,8,'Qual cláusula do switch é acionada quando nenhum case corresponde ao valor?','multipla_escolha','default','else','other','A','2026-09-27 02:22:38'),(24,8,'Em um switch-case, cada opção a ser testada é iniciada com a palavra:','multipla_escolha','case','when','if','A','2026-09-27 02:22:38'),(25,9,'Qual operador lógico representa o \\\"E\\\" (AND) que exige que ambas sejam verdadeiras?','multipla_escolha','&&','||','!','A','2026-09-27 02:22:38'),(26,9,'Qual operador lógico representa o \\\"OU\\\" (OR)?','multipla_escolha','||','&&','!=','A','2026-09-27 02:22:38'),(27,9,'Qual é o resultado da expressão: !(true)?','multipla_escolha','false','true','null','A','2026-09-27 02:22:38'),(28,10,'Qual é a sintaxe correta do operador ternário em Java?','multipla_escolha','(condicao) ? valor1 : valor2','(condicao) : valor1 ? valor2','if ? valor1 : valor2','A','2026-09-27 02:22:38'),(29,10,'O operador ternário é utilizado principalmente para:','multipla_escolha','Atribuições condicionais em linha única','Criar loops infinitos','Declarar classes','A','2026-09-27 02:22:38'),(30,10,'Quantos operandos o operador ternário recebe?','multipla_escolha','3 operandos','2 operandos','1 operando','A','2026-09-27 02:22:38'),(31,11,'Quais são as 3 partes que compõem o laço for?','multipla_escolha','Inicialização, Condição e Incremento','Início, Meio e Fim','Entrada, Processamento e Saída','A','2026-09-27 02:22:38'),(32,11,'O que o comando i++ faz em um laço for?','multipla_escolha','Incrementa o valor de i em 1','Dobra o valor de i','Zera a variável i','A','2026-09-27 02:22:38'),(33,11,'O laço for é mais recomendado quando:','multipla_escolha','Sabemos de antemão o número de repetições','O número de voltas é imprevisível','Não há variáveis','A','2026-09-27 02:22:38'),(34,12,'O laço while repete seu bloco:','multipla_escolha','Enquanto a condição for verdadeira','Apenas uma única vez','Até a memória esgotar','A','2026-09-27 02:22:38'),(35,12,'Quando a condição do laço while é verificada?','multipla_escolha','Antes de cada iteração','Apenas no final da execução','Nunca','A','2026-09-27 02:22:38'),(36,12,'O que acontece se a variável de controle nunca for alterada dentro do while?','multipla_escolha','Cria-se um loop infinito','O programa compila mais rápido','O Java fecha sozinho','A','2026-09-27 02:22:38'),(37,13,'Qual laço garante que o bloco de código execute ao menos 1 vez?','multipla_escolha','do-while','while','for','A','2026-09-27 02:22:38'),(38,13,'Qual comando interrompe e sai imediatamente de um laço de repetição?','multipla_escolha','break','continue','skip','A','2026-09-27 02:22:38'),(39,13,'Qual comando pula o restante da iteração atual e vai direto para a próxima volta?','multipla_escolha','continue','break','return','A','2026-09-27 02:22:38'),(40,14,'O que é um laço aninhado?','multipla_escolha','Um laço colocado dentro de outro laço','Um laço sem condição','Um array de repetições','A','2026-09-27 02:22:38'),(41,14,'Se o laço externo executa 3 vezes e o interno 4 vezes, quantas voltas totais ocorrem?','multipla_escolha','12 vezes','7 vezes','4 vezes','A','2026-09-27 02:22:38'),(42,14,'Laços aninhados são muito utilizados para percorrer:','multipla_escolha','Matrizes e tabelas bidimensionais','Variáveis booleanas','Métodos vazios','A','2026-09-27 02:22:38'),(43,15,'Qual método da classe String retorna o tamanho total de caracteres?','multipla_escolha','length()','size()','count()','A','2026-09-27 02:22:38'),(44,15,'Qual método obtém o caractere em uma posição específica de índice?','multipla_escolha','charAt()','getChar()','letterAt()','A','2026-09-27 02:22:38'),(45,15,'O primeiro caractere de uma String fica localizado no índice:','multipla_escolha','0','1','-1','A','2026-09-27 02:22:38'),(46,16,'Qual é o índice do primeiro elemento de um Array em Java?','multipla_escolha','0','1','-1','A','2026-09-27 02:22:38'),(47,16,'Como acessamos o tamanho total de um array chamado numeros?','multipla_escolha','numeros.length','numeros.size()','numeros.count','A','2026-09-27 02:22:38'),(48,16,'O que acontece se tentarmos acessar um índice fora dos limites do array?','multipla_escolha','Lança ArrayIndexOutOfBoundsException','Retorna 0 automaticamente','Cria o índice novo','A','2026-09-27 02:22:38'),(49,17,'Qual é a finalidade principal do laço Enhanced For (for-each)?','multipla_escolha','Percorrer todos os elementos de uma coleção facilmente','Criar arrays vazios','Fazer cálculos matemáticos','A','2026-09-27 02:22:38'),(50,17,'No laço for (String nome : lista), o que a variável nome representa a cada volta?','multipla_escolha','O valor do elemento atual da lista','O índice numérico do elemento','O tamanho total da lista','A','2026-09-27 02:22:38'),(51,17,'Podemos usar o for-each para percorrer arrays comuns em Java?','multipla_escolha','Sim, perfeitamente','Não, só funciona com objetos','Apenas com números','A','2026-09-27 02:22:38'),(52,18,'Uma matriz em Java é fundamentalmente:','multipla_escolha','Um array de arrays (bidimensional)','Uma variável primitiva','Um método especial','A','2026-09-27 02:22:38'),(53,18,'Como declaramos uma matriz de inteiros com 3 linhas e 3 colunas?','multipla_escolha','int[][] matriz = new int[3][3];','matriz int[3,3];','int matriz = new int(3,3);','A','2026-09-27 02:22:38'),(54,18,'Quantos laços aninhados geralmente usamos para percorrer uma matriz bidimensional?','multipla_escolha','2 laços','1 laço','4 laços','A','2026-09-27 02:22:38'),(55,19,'Qual método da classe java.util.Arrays ordena os elementos de um vetor?','multipla_escolha','Arrays.sort()','Arrays.order()','Arrays.align()','A','2026-09-27 02:22:38'),(56,19,'Qual método converte um array diretamente para uma String legível?','multipla_escolha','Arrays.toString()','Arrays.print()','Arrays.convert()','A','2026-09-27 02:22:38'),(57,19,'Para calcular a média de valores em um array, devemos:','multipla_escolha','Somar todos os elementos e dividir por array.length','Multiplicar o primeiro pelo último','Subtrair o tamanho total','A','2026-09-27 02:22:38'),(58,20,'Qual a principal vantagem do ArrayList em relação ao array tradicional?','multipla_escolha','Tamanho dinâmico e flexível','Executa sem JVM','Armazena menos memória','A','2026-09-27 02:22:38'),(59,20,'Qual método adiciona um novo item ao final de um ArrayList?','multipla_escolha','add()','push()','insert()','A','2026-09-27 02:22:38'),(60,20,'Qual método retorna o total de elementos presentes no ArrayList?','multipla_escolha','size()','length','count()','A','2026-09-27 02:22:38'),(61,21,'Em POO, o que representa a Classe?','multipla_escolha','O molde que define os atributos e métodos','Uma variável primitiva','O resultado final da execução','A','2026-09-27 02:22:38'),(62,21,'Qual operador é utilizado para instanciar (criar) um novo Objeto a partir de uma classe?','multipla_escolha','new','create','make','A','2026-09-27 02:22:38'),(63,21,'Como são chamadas as características ou dados que uma classe armazena?','multipla_escolha','Atributos','Métodos','Pacotes','A','2026-09-27 02:22:38'),(64,22,'O que é um Construtor em Java?','multipla_escolha','Um método especial executado ao criar o objeto com new','Uma ferramenta de compilação','Um destrutor de memória','A','2026-09-27 02:22:38'),(65,22,'Qual é o tipo de retorno declarado em um construtor?','multipla_escolha','Nenhum tipo de retorno (nem void)','void','int','A','2026-09-27 02:22:38'),(66,22,'A palavra-chave \\\"this\\\" dentro de um método refere-se a:','multipla_escolha','A própria instância do objeto atual','A classe pai','Uma variável global','A','2026-09-27 02:22:38'),(67,23,'Qual modificador de acesso restringe o atributo para que só seja visto dentro da própria classe?','multipla_escolha','private','public','protected','A','2026-09-27 02:22:38'),(68,23,'Qual convenção de métodos usamos para ler o valor de um atributo privado?','multipla_escolha','Getter (ex: getNome())','Setter (ex: setNome())','Printer','A','2026-09-27 02:22:38'),(69,23,'O pilar da POO que protege o estado interno dos objetos é chamado de:','multipla_escolha','Encapsulamento','Herança','Polimorfismo','A','2026-09-27 02:22:38'),(70,24,'Qual palavra-chave é usada em Java para fazer uma classe herdar de outra?','multipla_escolha','extends','implements','inherits','A','2026-09-27 02:22:38'),(71,24,'Qual anotação indica que um método está sobrescrevendo o comportamento da classe pai?','multipla_escolha','@Override','@Super','@Replace','A','2026-09-27 02:22:38'),(72,24,'A classe que fornece seus membros para a subclasse é chamada de:','multipla_escolha','Superclasse (ou Classe Pai)','Subclasse','Interface vazia','A','2026-09-27 02:22:38'),(73,25,'O conceito de Polimorfismo permite:','multipla_escolha','Tratar diferentes subclasses através de um tipo comum','Criar múltiplas classes com o mesmo nome','Executar código sem compilar','A','2026-09-27 02:22:38'),(74,25,'Podemos instanciar diretamente um objeto com new a partir de uma classe abstrata?','multipla_escolha','Não, classes abstratas não podem ser instanciadas','Sim, normalmente','Apenas se tiver construtor','A','2026-09-27 02:22:38'),(75,25,'Qual palavra-chave define uma classe abstrata em Java?','multipla_escolha','abstract','virtual','template','A','2026-09-27 02:22:38');
/*!40000 ALTER TABLE `perguntas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progresso_usuario`
--

DROP TABLE IF EXISTS `progresso_usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `progresso_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `unidade_numero` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'trancado',
  `licoes_concluidas` int(11) NOT NULL DEFAULT 0,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuario_unidade` (`usuario_id`,`unidade_numero`),
  CONSTRAINT `fk_progresso_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progresso_usuario`
--

LOCK TABLES `progresso_usuario` WRITE;
/*!40000 ALTER TABLE `progresso_usuario` DISABLE KEYS */;
INSERT INTO `progresso_usuario` VALUES (1,1,1,'corrente',2,'2026-09-27 02:22:38'),(2,1,2,'trancado',0,'2026-09-27 02:22:38'),(3,1,3,'trancado',0,'2026-09-27 02:22:38'),(4,1,4,'trancado',0,'2026-09-27 02:22:38'),(5,1,5,'trancado',0,'2026-09-27 02:22:38'),(6,2,1,'corrente',0,'2026-09-27 02:22:38'),(7,2,2,'trancado',0,'2026-09-27 02:22:38'),(8,2,3,'trancado',0,'2026-09-27 02:22:38'),(9,2,4,'trancado',0,'2026-09-27 02:22:38'),(10,2,5,'trancado',0,'2026-09-27 02:22:38'),(11,3,1,'corrente',0,'2026-09-27 02:22:38'),(12,3,2,'trancado',0,'2026-09-27 02:22:38'),(13,3,3,'trancado',0,'2026-09-27 02:22:38'),(14,3,4,'trancado',0,'2026-09-27 02:22:38'),(15,3,5,'trancado',0,'2026-09-27 02:22:38'),(16,4,1,'corrente',0,'2026-09-27 02:22:38'),(17,4,2,'trancado',0,'2026-09-27 02:22:38'),(18,4,3,'trancado',0,'2026-09-27 02:22:38'),(19,4,4,'trancado',0,'2026-09-27 02:22:38'),(20,4,5,'trancado',0,'2026-09-27 02:22:38'),(21,5,1,'corrente',0,'2026-09-27 02:22:38'),(22,5,2,'trancado',0,'2026-09-27 02:22:38'),(23,5,3,'trancado',0,'2026-09-27 02:22:38'),(24,5,4,'trancado',0,'2026-09-27 02:22:38'),(25,5,5,'trancado',0,'2026-09-27 02:22:38'),(26,20,1,'corrente',0,'2026-10-05 00:02:59'),(27,20,2,'trancado',0,'2026-10-05 00:02:59'),(28,20,3,'trancado',0,'2026-10-05 00:02:59'),(29,20,4,'trancado',0,'2026-10-05 00:02:59'),(30,20,5,'trancado',0,'2026-10-05 00:02:59'),(31,21,1,'corrente',0,'2026-10-05 00:03:58'),(32,21,2,'trancado',0,'2026-10-05 00:03:58'),(33,21,3,'trancado',0,'2026-10-05 00:03:58'),(34,21,4,'trancado',0,'2026-10-05 00:03:58'),(35,21,5,'trancado',0,'2026-10-05 00:03:58');
/*!40000 ALTER TABLE `progresso_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recuperacao_senha`
--

DROP TABLE IF EXISTS `recuperacao_senha`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recuperacao_senha` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_codigo` (`email`,`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recuperacao_senha`
--

LOCK TABLES `recuperacao_senha` WRITE;
/*!40000 ALTER TABLE `recuperacao_senha` DISABLE KEYS */;
/*!40000 ALTER TABLE `recuperacao_senha` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seguidores`
--

DROP TABLE IF EXISTS `seguidores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `seguidores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seguidor_id` int(11) NOT NULL,
  `seguido_id` int(11) NOT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seguidor_seguido` (`seguidor_id`,`seguido_id`),
  KEY `idx_seguidor` (`seguidor_id`),
  KEY `idx_seguido` (`seguido_id`),
  CONSTRAINT `fk_seguidores_seguido` FOREIGN KEY (`seguido_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_seguidores_seguidor` FOREIGN KEY (`seguidor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seguidores`
--

LOCK TABLES `seguidores` WRITE;
/*!40000 ALTER TABLE `seguidores` DISABLE KEYS */;
/*!40000 ALTER TABLE `seguidores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_trofeus`
--

DROP TABLE IF EXISTS `user_trofeus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_trofeus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `trofeu_slug` varchar(80) NOT NULL,
  `conquistado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_trofeu` (`user_id`,`trofeu_slug`),
  KEY `idx_trofeu_slug` (`trofeu_slug`),
  CONSTRAINT `fk_user_trofeus_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_trofeus`
--

LOCK TABLES `user_trofeus` WRITE;
/*!40000 ALTER TABLE `user_trofeus` DISABLE KEYS */;
INSERT INTO `user_trofeus` VALUES (1,1,'perfeicao','2026-09-27 02:23:00'),(2,1,'primeiro_passo','2026-09-27 02:23:00'),(3,1,'precisao_absoluta','2026-09-27 02:23:00');
/*!40000 ALTER TABLE `user_trofeus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `xp` int(11) NOT NULL DEFAULT 0,
  `trofeus` int(11) NOT NULL DEFAULT 0,
  `dificuldade` varchar(50) DEFAULT 'Iniciante',
  `foto_perfil` longtext DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `dias_fogo` int(11) NOT NULL DEFAULT 0,
  `vidas` int(11) NOT NULL DEFAULT 3,
  `vidas_proxima_em` datetime DEFAULT NULL,
  `ultima_atividade` date DEFAULT NULL,
  `nivel_acesso` enum('comum','admin') NOT NULL DEFAULT 'comum',
  `cor_fundo` varchar(20) DEFAULT '#1cb0f6',
  `username` varchar(50) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `foto_google` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador OPUS','gabriel@opus.com','$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi',2500,12,'Intermediário',NULL,'2026-09-27 02:22:37',14,3,NULL,'2026-09-26','admin','#1cb0f6','admin_opus',NULL,NULL),(2,'Gabriel Freitas','admin@opus.com','$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi',3800,15,'Avançado',NULL,'2026-09-27 02:22:37',30,3,NULL,NULL,'admin','#58cc02','gabriel_dev',NULL,NULL),(3,'Ana Silva','ana.silva@email.com','$2y$10$8Fb33G1p8YdvgMXcIyd5COwo7K6O0MFhvffFN3HvOqlFi5jhEhsTO',180,3,'Iniciante',NULL,'2026-09-27 02:22:37',2,3,NULL,NULL,'comum','#ff4b4b','aninha_code',NULL,NULL),(4,'Lucas Mendes','lucas@email.com','$2y$10$8Fb33G1p8YdvgMXcIyd5COwo7K6O0MFhvffFN3HvOqlFi5jhEhsTO',450,2,'Iniciante',NULL,'2026-09-27 02:22:37',4,2,NULL,NULL,'comum','#ce82ff','lucas_java',NULL,NULL),(5,'Mariana Costa','mariana@email.com','$2y$10$8Fb33G1p8YdvgMXcIyd5COwo7K6O0MFhvffFN3HvOqlFi5jhEhsTO',950,4,'Intermediário',NULL,'2026-09-27 02:22:37',8,3,NULL,NULL,'comum','#ff9600','mari_dev',NULL,NULL),(7,'Pedro Rocha','pedro.rocha@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',90,1,'Iniciante',NULL,'2026-10-05 00:01:36',1,1,NULL,NULL,'comum','#1cb0f6','pedro_dev',NULL,NULL),(8,'Júlia Santos','julia.santos@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',50,0,'Iniciante',NULL,'2026-10-05 00:01:36',0,0,NULL,NULL,'comum','#ff9600','ju_santos',NULL,NULL),(9,'Felipe Oliveira','felipe.oliveira@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',530,5,'Intermediário',NULL,'2026-10-05 00:01:36',6,3,NULL,NULL,'comum','#00cd9c','felipe_oli',NULL,NULL),(10,'Camila Ribeiro','camila.ribeiro@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',390,3,'Iniciante',NULL,'2026-10-05 00:01:36',3,3,NULL,NULL,'comum','#e11d48','cami_rib',NULL,NULL),(11,'Bruno Carvalho','bruno.carvalho@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',1100,8,'Intermediário',NULL,'2026-10-05 00:01:36',12,2,NULL,NULL,'comum','#d97706','bruno_code',NULL,NULL),(12,'Larissa Souza','larissa.souza@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',880,6,'Intermediário',NULL,'2026-10-05 00:01:36',7,3,NULL,NULL,'comum','#9333ea','lari_souza',NULL,NULL),(13,'Rodrigo Lima','rodrigo.lima@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',820,6,'Intermediário',NULL,'2026-10-05 00:01:36',5,1,NULL,NULL,'comum','#2563eb','rodrigo_l',NULL,NULL),(14,'Rafael Duarte','rafael.duarte@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',2100,10,'Avançado',NULL,'2026-10-05 00:01:36',18,3,NULL,NULL,'comum','#06b6d4','rafa_duarte',NULL,NULL),(15,'Beatriz Almeida','beatriz.almeida@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',2450,11,'Avançado',NULL,'2026-10-05 00:01:36',21,3,NULL,NULL,'comum','#3b82f6','bea_almeida',NULL,NULL),(16,'Thiago Ferreira','thiago.ferreira@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',1950,9,'Intermediário',NULL,'2026-10-05 00:01:36',15,2,NULL,NULL,'comum','#84cc16','thiago_f',NULL,NULL),(17,'Helena Martins','helena.martins@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',4800,18,'Avançado',NULL,'2026-10-05 00:01:36',45,3,NULL,NULL,'comum','#a855f7','helena_m',NULL,NULL),(18,'Vinicius Prado','vinicius.prado@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',3900,14,'Avançado',NULL,'2026-10-05 00:01:36',28,3,NULL,NULL,'comum','#ec4899','vini_prado',NULL,NULL),(19,'Sophia Castro','sophia.castro@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',4350,16,'Avançado',NULL,'2026-10-05 00:01:36',35,3,NULL,NULL,'comum','#6366f1','sophia_c',NULL,NULL),(20,'Gab Santos','gabsantosbag@gmail.com','',0,0,'Iniciante',NULL,'2026-10-05 00:02:59',0,3,NULL,NULL,'comum','#1cb0f6',NULL,'106201833585413825315','https://lh3.googleusercontent.com/a/ACg8ocLdaUy7rcG2C8yeI1163uwixoCqwFzMxpfd_sM6epC2w34oUmA=s96-c'),(21,'lucas','lucas@opus.com','Admin123@',0,0,'Iniciante',NULL,'2026-10-05 00:03:58',0,3,NULL,NULL,'comum','#1cb0f6',NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'opus'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_atualizar_fogo` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_atualizar_fogo`(IN p_user_id INT)
BEGIN
    DECLARE v_ultima DATE;
    DECLARE v_fogo INT;
    DECLARE v_hoje DATE DEFAULT CURDATE();

    SELECT ultima_atividade, dias_fogo INTO v_ultima, v_fogo FROM usuarios WHERE id = p_user_id FOR UPDATE;

    IF v_ultima IS NULL THEN
        SET v_fogo = 1;
    ELSEIF v_ultima = DATE_SUB(v_hoje, INTERVAL 1 DAY) THEN
        SET v_fogo = v_fogo + 1;
    ELSEIF v_ultima < DATE_SUB(v_hoje, INTERVAL 1 DAY) THEN
        SET v_fogo = 1;
    END IF;

    UPDATE usuarios SET dias_fogo = v_fogo, ultima_atividade = v_hoje WHERE id = p_user_id;
    SELECT v_fogo AS dias_fogo;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_corrigir_licao` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_corrigir_licao`(
    IN p_user_id INT,
    IN p_unidade INT,
    IN p_licao INT,
    IN p_acertos INT
)
BEGIN
    CALL sp_processar_resultado_licao(p_user_id, p_unidade, p_licao, p_acertos, 3);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_liga_registrar_xp` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_liga_registrar_xp`(IN p_user_id INT, IN p_xp INT)
BEGIN
    DECLARE v_semana DATE;
    DECLARE v_grupo_id INT;
    DECLARE v_divisao VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'bronze';

    SET v_semana = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

    SELECT g.id, u.divisao INTO v_grupo_id, v_divisao
    FROM ligas_usuario u
    JOIN ligas_grupos g ON u.grupo_id = g.id
    WHERE u.usuario_id = p_user_id AND g.semana_ref = v_semana
    LIMIT 1;

    IF v_grupo_id IS NULL THEN
        SELECT id INTO v_grupo_id FROM ligas_grupos WHERE semana_ref = v_semana AND divisao = v_divisao LIMIT 1;
        IF v_grupo_id IS NULL THEN
            INSERT INTO ligas_grupos (divisao, semana_ref, capacidade) VALUES (v_divisao, v_semana, 30);
            SET v_grupo_id = LAST_INSERT_ID();
        END IF;
        INSERT INTO ligas_usuario (usuario_id, divisao, grupo_id, xp_semana) 
        VALUES (p_user_id, v_divisao, v_grupo_id, p_xp)
        ON DUPLICATE KEY UPDATE xp_semana = xp_semana + p_xp;
    ELSE
        UPDATE ligas_usuario SET xp_semana = xp_semana + p_xp WHERE usuario_id = p_user_id AND grupo_id = v_grupo_id;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_penalizar_saida_licao` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_penalizar_saida_licao`(IN p_user_id INT)
BEGIN
    CALL sp_perder_vida(p_user_id);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_perder_vida` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_perder_vida`(IN p_user_id INT)
BEGIN
    DECLARE v_vidas INT;
    DECLARE v_proxima_em DATETIME;
    
    SELECT vidas, vidas_proxima_em INTO v_vidas, v_proxima_em FROM usuarios WHERE id = p_user_id FOR UPDATE;
    
    IF v_vidas > 0 THEN
        SET v_vidas = v_vidas - 1;
        IF v_proxima_em IS NULL THEN
            SET v_proxima_em = DATE_ADD(NOW(), INTERVAL 30 MINUTE);
        END IF;
        UPDATE usuarios SET vidas = v_vidas, vidas_proxima_em = v_proxima_em WHERE id = p_user_id;
    END IF;
    
    SELECT v_vidas AS vidas_restantes, v_proxima_em AS proxima_recarga;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_processar_resultado_licao` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_processar_resultado_licao`(
    IN p_user_id INT,
    IN p_unidade INT,
    IN p_licao INT,
    IN p_acertos INT,
    IN p_total INT
)
BEGIN
    DECLARE v_avancou INT DEFAULT 0;
    DECLARE v_capitulo_concluido INT DEFAULT 0;
    DECLARE v_bau_liberado INT DEFAULT 0;
    DECLARE v_xp_ganho INT DEFAULT 50;
    DECLARE v_licoes_feitas INT DEFAULT 0;

    IF p_acertos = p_total AND p_total > 0 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'perfeicao');
        INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'perfeicao');

        UPDATE usuarios SET xp = xp + v_xp_ganho WHERE id = p_user_id;

        SELECT licoes_concluidas INTO v_licoes_feitas FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
        
        IF v_licoes_feitas IS NULL THEN
            INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) VALUES (p_user_id, p_unidade, 'corrente', 1);
            SET v_licoes_feitas = 1;
            SET v_avancou = 1;
        ELSEIF p_licao > v_licoes_feitas THEN
            SET v_licoes_feitas = p_licao;
            UPDATE progresso_usuario SET licoes_concluidas = v_licoes_feitas WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
            SET v_avancou = 1;
        END IF;

        IF v_licoes_feitas >= 3 THEN
            SET v_bau_liberado = 1;
        END IF;

        IF v_licoes_feitas >= 5 THEN
            SET v_capitulo_concluido = 1;
            UPDATE progresso_usuario SET status = 'completo' WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
            
            INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas)
            VALUES (p_user_id, p_unidade + 1, 'corrente', 0)
            ON DUPLICATE KEY UPDATE status = IF(status = 'trancado', 'corrente', status);

            IF p_unidade = 1 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_1'); END IF;
            IF p_unidade = 2 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_2'); END IF;
            IF p_unidade = 3 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_3'); END IF;
            IF p_unidade = 4 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_4'); END IF;
            IF p_unidade = 5 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_5'); END IF;
        END IF;

        CALL sp_liga_registrar_xp(p_user_id, v_xp_ganho);
        CALL sp_registrar_missao_progresso(p_user_id, v_xp_ganho, 1);
        CALL sp_atualizar_fogo(p_user_id);
        CALL sp_verificar_conquistas(p_user_id);
    END IF;

    SELECT v_avancou AS avancou, v_capitulo_concluido AS capitulo_concluido, v_bau_liberado AS bau_liberado, v_xp_ganho AS xp_ganho;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_processar_virada_semana` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_processar_virada_semana`()
BEGIN
    DECLARE v_semana_antiga DATE;
    DECLARE v_nova_semana DATE;
    SET v_nova_semana = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);
    SET v_semana_antiga = DATE_SUB(v_nova_semana, INTERVAL 7 DAY);

    INSERT INTO ligas_historico (usuario_id, divisao_anterior, divisao_nova, resultado, posicao_final, xp_final, semana_ref)
    SELECT 
        u.usuario_id, 
        u.divisao, 
        u.divisao, 
        'manteve', 
        1, 
        u.xp_semana, 
        v_semana_antiga
    FROM ligas_usuario u
    JOIN ligas_grupos g ON u.grupo_id = g.id
    WHERE g.semana_ref = v_semana_antiga;

    UPDATE ligas_usuario SET xp_semana = 0 WHERE grupo_id IN (SELECT id FROM ligas_grupos WHERE semana_ref = v_nova_semana);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_registrar_missao_progresso` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_missao_progresso`(IN p_user_id INT, IN p_xp INT, IN p_perfeita INT)
BEGIN
    DECLARE v_hoje DATE DEFAULT CURDATE();
    INSERT INTO missoes_diarias_usuario (usuario_id, data_ref, xp_ganho, licoes_concluidas, licoes_perfeitas)
    VALUES (p_user_id, v_hoje, p_xp, 1, IF(p_perfeita = 1, 1, 0))
    ON DUPLICATE KEY UPDATE
        xp_ganho = xp_ganho + p_xp,
        licoes_concluidas = licoes_concluidas + 1,
        licoes_perfeitas = licoes_perfeitas + IF(p_perfeita = 1, 1, 0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_resgatar_bau` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_resgatar_bau`(IN p_user_id INT, IN p_unidade INT)
BEGIN
    DECLARE v_ja_resgatou INT;
    DECLARE v_concluidas INT;
    DECLARE v_xp_recompensa INT DEFAULT 100;
    DECLARE v_vidas_recompensa INT DEFAULT 1;

    SELECT COUNT(*) INTO v_ja_resgatou FROM bau_recompensas WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
    SELECT licoes_concluidas INTO v_concluidas FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;

    IF v_ja_resgatou = 0 AND v_concluidas >= 3 THEN
        INSERT INTO bau_recompensas (usuario_id, unidade_numero, tipo_recompensa, xp_ganho, vidas_ganhas)
        VALUES (p_user_id, p_unidade, 'misto', v_xp_recompensa, v_vidas_recompensa);

        UPDATE usuarios SET 
            xp = xp + v_xp_recompensa,
            vidas = LEAST(3, vidas + v_vidas_recompensa)
        WHERE id = p_user_id;

        SELECT 1 AS resgatado, v_xp_recompensa AS xp_ganho, v_vidas_recompensa AS vidas_ganhas;
    ELSE
        SELECT 0 AS resgatado, 0 AS xp_ganho, 0 AS vidas_ganhas;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_sincronizar_jogador` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_sincronizar_jogador`(IN p_user_id INT)
BEGIN
    DECLARE v_vidas INT;
    DECLARE v_proxima_em DATETIME;
    DECLARE v_agora DATETIME DEFAULT NOW();
    DECLARE v_tempo_recarga INT DEFAULT 1800; -- 30 minutos em segundos
    DECLARE v_vidas_ganhas INT DEFAULT 0;
    DECLARE v_segundos_passados INT;
    DECLARE v_resto_segundos INT;

    SELECT vidas, vidas_proxima_em INTO v_vidas, v_proxima_em
    FROM usuarios WHERE id = p_user_id FOR UPDATE;

    IF v_vidas < 3 AND v_proxima_em IS NOT NULL AND v_agora >= v_proxima_em THEN
        SET v_segundos_passados = TIMESTAMPDIFF(SECOND, v_proxima_em, v_agora) + v_tempo_recarga;
        SET v_vidas_ganhas = FLOOR(v_segundos_passados / v_tempo_recarga);
        SET v_resto_segundos = v_segundos_passados % v_tempo_recarga;

        SET v_vidas = LEAST(3, v_vidas + v_vidas_ganhas);

        IF v_vidas >= 3 THEN
            SET v_proxima_em = NULL;
        ELSE
            SET v_proxima_em = DATE_SUB(DATE_ADD(v_agora, INTERVAL v_tempo_recarga SECOND), INTERVAL v_resto_segundos SECOND);
        END IF;

        UPDATE usuarios SET vidas = v_vidas, vidas_proxima_em = v_proxima_em WHERE id = p_user_id;
    END IF;

    SELECT id, nome, email, xp, trofeus, dificuldade, foto_perfil, dias_fogo, vidas, vidas_proxima_em, nivel_acesso, cor_fundo, username
    FROM usuarios WHERE id = p_user_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_verificar_conquistas` */;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_verificar_conquistas`(IN p_user_id INT)
BEGIN
    DECLARE v_xp INT;
    DECLARE v_fogo INT;
    DECLARE v_trofeus INT;

    SELECT xp, dias_fogo, trofeus INTO v_xp, v_fogo, v_trofeus FROM usuarios WHERE id = p_user_id;

    INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'primeiro_passo');
    INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'primeiro_passo');

    IF v_fogo >= 3 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'fogo_3');
        INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'fogo_3');
    END IF;
    IF v_fogo >= 7 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'sequencia_7');
        INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'sequencia_7');
    END IF;
    IF v_fogo >= 30 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'sequencia_30');
        INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'sequencia_30');
    END IF;
    
    IF v_xp >= 150 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'precisao_absoluta');
        INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'precisao_absoluta');
    END IF;

    UPDATE usuarios SET trofeus = (SELECT COUNT(*) FROM user_trofeus WHERE user_id = p_user_id) WHERE id = p_user_id;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-04 21:10:24
