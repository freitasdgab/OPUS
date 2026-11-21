-- =============================================================================
-- OPUS - SISTEMA GAMIFICADO DE ENSINO DE PROGRAMAÇÃO EM JAVA
-- BANCO DE DADOS OFICIAL - PADRÃO MYSQL / MARIADB (UTF-8 MB4)
-- =============================================================================
-- Este arquivo contém a estrutura completa e dados iniciais da aplicação OPUS,
-- incluindo tabelas relacionais, índices, chaves estrangeiras, stored procedures,
-- views analíticas, triggers de integridade e catálogo de conteúdos.
-- Compatível com: MySQL 8.0+, MySQL 5.7 e MariaDB 10.4+ (XAMPP / Laragon).
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `opus`
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `opus`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- -----------------------------------------------------------------------------
-- REMOÇÃO DE ESTRUTURAS OBSOLETAS SE EXISTIREM
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `batalhas_amigos`;
DROP TABLE IF EXISTS `recuperacao_senha`;
DROP TABLE IF EXISTS `chat_mensagens`;
DROP TABLE IF EXISTS `pedidos_conexao`;
DROP TABLE IF EXISTS `seguidores`;
DROP TABLE IF EXISTS `grupo_membros`;
DROP TABLE IF EXISTS `grupos_batalha`;
DROP TABLE IF EXISTS `ligas_historico`;
DROP TABLE IF EXISTS `ligas_usuario`;
DROP TABLE IF EXISTS `ligas_grupos`;
DROP TABLE IF EXISTS `desafios_projetos_usuario`;
DROP TABLE IF EXISTS `conquistas_usuario`;
DROP TABLE IF EXISTS `user_trofeus`;
DROP TABLE IF EXISTS `catalogo_trofeus`;
DROP TABLE IF EXISTS `bau_recompensas`;
DROP TABLE IF EXISTS `missoes_diarias_usuario`;
DROP TABLE IF EXISTS `acertos_diarios`;
DROP TABLE IF EXISTS `progresso_usuario`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `desafios_projetos`;
DROP TABLE IF EXISTS `perguntas`;
DROP TABLE IF EXISTS `licoes`;
DROP TABLE IF EXISTS `desafios`;
DROP TABLE IF EXISTS `unidades`;
DROP TABLE IF EXISTS `divisoes`;
DROP VIEW IF EXISTS `vw_ranking_geral`;
DROP VIEW IF EXISTS `vw_desempenho_aluno`;

-- =============================================================================
-- SEÇÃO 1: ESTRUTURA DAS TABELAS (DDL)
-- =============================================================================
-- Tabela: `divisoes`
CREATE TABLE `divisoes` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `tema` varchar(100) NOT NULL,
  `descricao` varchar(500) NOT NULL,
  `secao_inicio` int(11) NOT NULL,
  `secao_fim` int(11) NOT NULL,
  `icone` varchar(50) NOT NULL,
  `cor` varchar(7) NOT NULL,
  `badge` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `unidades`
CREATE TABLE `unidades` (
  `numero` int(11) NOT NULL,
  `titulo` varchar(50) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `cor` varchar(7) NOT NULL,
  `mascote` varchar(100) DEFAULT NULL,
  `guia_texto` text NOT NULL,
  `guia_codigo` text NOT NULL,
  `total_licoes` tinyint(4) NOT NULL DEFAULT 5,
  PRIMARY KEY (`numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `desafios`
CREATE TABLE `desafios` (
  `unidade_numero` int(11) NOT NULL,
  `badge` varchar(100) NOT NULL,
  `heading` varchar(200) NOT NULL,
  `intro` text NOT NULL,
  `missao` text NOT NULL,
  `alvo_visual` varchar(255) NOT NULL,
  `dica_texto` text NOT NULL,
  `dica_codigo` text NOT NULL,
  `arquivo` varchar(100) NOT NULL,
  `codigo_inicial` text NOT NULL,
  `validador_js` text NOT NULL COMMENT 'Expressão JS avaliada no front (variáveis disponíveis: stdout, normStdout)',
  `mensagem_ajuste` text NOT NULL,
  PRIMARY KEY (`unidade_numero`),
  CONSTRAINT `fk_desafios_unidade` FOREIGN KEY (`unidade_numero`) REFERENCES `unidades` (`numero`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `licoes`
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
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `perguntas`
CREATE TABLE `perguntas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `licao_id` int(11) NOT NULL,
  `pergunta_texto` text NOT NULL,
  `codigo_contexto` text DEFAULT NULL,
  `tipo` varchar(50) NOT NULL DEFAULT 'multipla_escolha',
  `alternativa_a` varchar(500) NOT NULL,
  `alternativa_b` varchar(500) NOT NULL,
  `alternativa_c` varchar(500) NOT NULL,
  `alternativa_correta` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_licao_id` (`licao_id`),
  CONSTRAINT `fk_perguntas_licao` FOREIGN KEY (`licao_id`) REFERENCES `licoes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=784 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `desafios_projetos`
CREATE TABLE `desafios_projetos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(60) NOT NULL,
  `titulo` varchar(120) NOT NULL,
  `subtitulo` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `secao_requerida` int(11) NOT NULL,
  `requisitos_titulo` varchar(120) NOT NULL,
  `requisitos_desc` text NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `dificuldade` enum('iniciante','facil','medio','desafiador') NOT NULL DEFAULT 'facil',
  `xp_recompensa` int(11) NOT NULL DEFAULT 50,
  `icone` varchar(50) NOT NULL DEFAULT 'fa-code',
  `cor` varchar(10) NOT NULL DEFAULT '#1cb0f6',
  `arquivo_nome` varchar(60) NOT NULL DEFAULT 'Main.java',
  `codigo_inicial` mediumtext NOT NULL,
  `missao_detalhada` mediumtext NOT NULL,
  `exemplo_saida` text NOT NULL,
  `dica` text NOT NULL,
  `validador_tipo` varchar(30) NOT NULL DEFAULT 'saida_contem',
  `validador_regra` text NOT NULL,
  `ordem` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_desafios_slug` (`slug`),
  KEY `idx_desafios_secao` (`secao_requerida`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `usuarios`
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `xp` int(11) NOT NULL DEFAULT 0,
  `trofeus` int(11) NOT NULL DEFAULT 0,
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
  `link_github` varchar(255) DEFAULT NULL,
  `link_instagram` varchar(255) DEFAULT NULL,
  `link_facebook` varchar(255) DEFAULT NULL,
  `link_youtube` varchar(255) DEFAULT NULL,
  `link_email` varchar(255) DEFAULT NULL,
  `link_linkedin` varchar(255) DEFAULT NULL,
  `projeto_titulo_1` varchar(255) DEFAULT NULL,
  `projeto_url_1` varchar(255) DEFAULT NULL,
  `projeto_titulo_2` varchar(255) DEFAULT NULL,
  `projeto_url_2` varchar(255) DEFAULT NULL,
  `projeto_titulo_3` varchar(255) DEFAULT NULL,
  `projeto_url_3` varchar(255) DEFAULT NULL,
  `pais` varchar(50) DEFAULT 'Brasil',
  `pais_codigo` varchar(10) DEFAULT 'br',
  `tour_visto` tinyint(1) NOT NULL DEFAULT 0,
  `tour_perfil_visto` tinyint(1) NOT NULL DEFAULT 0,
  `tour_licao1_visto` tinyint(1) NOT NULL DEFAULT 0,
  `tour_resultado1_visto` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `progresso_usuario`
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
) ENGINE=InnoDB AUTO_INCREMENT=311 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `acertos_diarios`
CREATE TABLE `acertos_diarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_dia` date NOT NULL,
  `acertos` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_dia` (`usuario_id`,`data_dia`),
  CONSTRAINT `fk_acertos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `missoes_diarias_usuario`
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `bau_recompensas`
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `catalogo_trofeus`
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

-- Tabela: `user_trofeus`
CREATE TABLE `user_trofeus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `trofeu_slug` varchar(80) NOT NULL,
  `conquistado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_trofeu` (`user_id`,`trofeu_slug`),
  KEY `idx_trofeu_slug` (`trofeu_slug`),
  CONSTRAINT `fk_user_trofeus_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `conquistas_usuario`
CREATE TABLE `conquistas_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `conquista_slug` varchar(80) NOT NULL,
  `data_desbloqueio` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_conquista` (`usuario_id`,`conquista_slug`),
  CONSTRAINT `fk_conquistas_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=239 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `desafios_projetos_usuario`
CREATE TABLE `desafios_projetos_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `desafio_id` int(11) NOT NULL,
  `status` enum('disponivel','em_progresso','concluido') NOT NULL DEFAULT 'disponivel',
  `codigo_salvo` mediumtext DEFAULT NULL,
  `concluido_em` datetime DEFAULT NULL,
  `xp_ganho` int(11) NOT NULL DEFAULT 0,
  `tentativas` int(11) NOT NULL DEFAULT 0,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_desafio` (`usuario_id`,`desafio_id`),
  KEY `idx_user_desafio_status` (`usuario_id`,`status`),
  KEY `fk_desafio_projeto` (`desafio_id`),
  CONSTRAINT `fk_desafio_projeto` FOREIGN KEY (`desafio_id`) REFERENCES `desafios_projetos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_desafio_user` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `ligas_grupos`
CREATE TABLE `ligas_grupos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `divisao` enum('bronze','prata','ouro','diamante','mestre') NOT NULL,
  `semana_ref` date NOT NULL,
  `capacidade` int(11) NOT NULL DEFAULT 30,
  `criado_em` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `divisao_semana` (`divisao`,`semana_ref`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `ligas_usuario`
CREATE TABLE `ligas_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `divisao` enum('bronze','prata','ouro','diamante','mestre') NOT NULL DEFAULT 'bronze',
  `grupo_id` int(11) DEFAULT NULL,
  `xp_semana` int(11) NOT NULL DEFAULT 0,
  `posicao_semana_anterior` int(11) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuario` (`usuario_id`),
  KEY `grupo_id` (`grupo_id`),
  CONSTRAINT `fk_ligas_usuario_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `ligas_grupos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ligas_usuario_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=202 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `ligas_historico`
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
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `grupos_batalha`
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

-- Tabela: `grupo_membros`
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

-- Tabela: `seguidores`
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

-- Tabela: `pedidos_conexao`
CREATE TABLE `pedidos_conexao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remetente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `data_criacao` datetime DEFAULT current_timestamp(),
  `notificado_aceite` tinyint(1) DEFAULT 0,
  `data_resposta` datetime DEFAULT NULL,
  `viraram_amigos` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `chat_mensagens`
CREATE TABLE `chat_mensagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remetente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `status_leitura` varchar(20) DEFAULT 'nao_lido',
  `data_envio` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela: `recuperacao_senha`
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

-- =============================================================================
-- SEÇÃO 2: DADOS INICIAIS E CATÁLOGO PEDAGÓGICO (DML)
-- =============================================================================
-- Dados da tabela: `divisoes` (3 registros)
INSERT INTO `divisoes` (`id`, `nome`, `tema`, `descricao`, `secao_inicio`, `secao_fim`, `icone`, `cor`, `badge`) VALUES
('1', 'Divisão 1', 'Fundamentos de Java', 'Aprenda os pilares essenciais: estrutura básica, variáveis, operadores, decisões, laços e métodos.', '1', '15', 'fa-seedling', '#1cb0f6', 'Fundamentos'),
('2', 'Divisão 2', 'Estruturas de Dados & Coleções', 'Avance para matrizes, HashMap, HashSet, manipulação avançada de Strings com Regex e datas modernas.', '16', '26', 'fa-layer-group', '#ce82ff', 'Intermediário'),
('3', 'Divisão 3', 'Orientação a Objetos & Avançado', 'Domine POO completo: classes, encapsulamento, herança, polimorfismo, generics, exceções e design patterns.', '27', '40', 'fa-crown', '#ffc800', 'Avançado');

-- Dados da tabela: `unidades` (40 registros)
INSERT INTO `unidades` (`numero`, `titulo`, `nome`, `descricao`, `cor`, `mascote`, `guia_texto`, `guia_codigo`, `total_licoes`) VALUES
('1', 'Seção 1', 'Introdução', 'Módulo focado em Introdução.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção, você dará seus primeiros passos na programação Java! Vamos conhecer a estrutura básica de um programa, como a classe principal e o método main funcionam, e como exibir mensagens na tela.', 'public class Main {<br>&nbsp;&nbsp;&nbsp;&nbsp;public static void main(String[] args) {<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;System.out.println(\"Olá, Mundo!\");<br>&nbsp;&nbsp;&nbsp;&nbsp;}<br>}', '5'),
('2', 'Seção 2', 'Variáveis Parte 1', 'Módulo focado em Variáveis Parte 1.', '#ff4b4b', 'vistodecimarosa.png', 'As variáveis são como caixas onde guardamos informações. Aqui aprenderemos a declarar variáveis para armazenar números inteiros (int) e texto (String), além de ver como atribuir e ler esses valores.', 'int idade = 20;<br>String nome = \"Opus\";<br><br>System.out.println(nome + \" tem \" + idade + \" anos.\");', '5'),
('3', 'Seção 3', 'Variáveis Parte 2', 'Módulo focado em Variáveis Parte 2.', '#ce82ff', 'vistodecimaroxo.png', 'Avançando no uso das variáveis! Agora você vai conhecer os tipos para armazenar números decimais (double) e valores lógicos de verdadeiro ou falso (boolean). Entender esses tipos é essencial para lidar com informações reais.', 'double altura = 1.75;<br>boolean aprovado = true;<br><br>if(aprovado) {<br>&nbsp;&nbsp;&nbsp;&nbsp;System.out.println(\"Altura: \" + altura);<br>}', '5'),
('4', 'Seção 4', 'Operadores Parte 1', 'Módulo focado em Operadores Parte 1.', '#58cc02', 'vistodecimaverde.png', 'Chegou a hora da matemática! Vamos usar os operadores aritméticos básicos para fazer contas no código: adição (+), subtração (-), multiplicação (*) e divisão (/). Você verá como o Java processa essas expressões.', 'int a = 10;<br>int b = 3;<br>int soma = a + b;<br>int resto = a % b;<br>System.out.println(\"Soma: \" + soma);', '5'),
('5', 'Seção 5', 'Operadores Parte 2', 'Módulo focado em Operadores Parte 2.', '#ffc800', 'vistodecimalaranja.png', 'Além de contas, precisamos tomar decisões. Vamos explorar os operadores relacionais (==, !=, &gt;, &lt;) e lógicos (&amp;&amp;, ||) que permitem comparar variáveis e criar regras complexas para o nosso programa.', 'int nota = 8;<br>boolean passou = (nota &gt;= 7) &amp;&amp; (nota != 0);<br><br>System.out.println(\"Passou? \" + passou);', '5'),
('6', 'Seção 6', 'Tomada de Decisão', 'Módulo focado em Tomada de Decisão.', '#1cb0f6', 'vistodecimaazul.png', 'Aprenda a controlar o fluxo de execução dos seus programas Java com if, else-if e switch, permitindo que seu código tome decisões lógicas com base nas condições fornecidas.', 'int idade = 18;<br>if (idade &gt;= 18) {<br>&nbsp;&nbsp;&nbsp;&nbsp;System.out.println(\"Maior de idade\");<br>} else {<br>&nbsp;&nbsp;&nbsp;&nbsp;System.out.println(\"Menor de idade\");<br>}', '5'),
('7', 'Seção 7', 'I/O Básico', 'Módulo focado em I/O Básico.', '#ff4b4b', 'vistodecimarosa.png', 'Explore os recursos fundamentais de entrada e saída em Java, desde a leitura de dados do teclado com a classe Scanner até a formatação elegante de mensagens com printf.', 'Scanner sc = new Scanner(System.in);<br>System.out.print(\"Digite o nome: \");<br>String nome = sc.nextLine();<br>System.out.printf(\"Olá, %s!\\n\", nome);', '5'),
('8', 'Seção 8', 'Aplicativo de Calculadora', 'Módulo focado em Aplicativo de Calculadora.', '#ce82ff', 'vistodecimaroxo.png', 'Construa lógica computacional e modular com operações matemáticas completas, explorando a classe java.lang.Math para arredondamentos, potências e raízes.', 'double raiz = Math.sqrt(64);<br>double potencia = Math.pow(2, 3);<br>System.out.println(\"Raiz: \" + raiz + \", Potência: \" + potencia);', '5'),
('9', 'Seção 9', 'Laços', 'Módulo focado em Laços.', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Laços de Repetição for e while. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Laços<br>System.out.println(\"1 2 3\");', '5'),
('10', 'Seção 10', 'Métodos e Funções', 'Módulo focado em Métodos e Funções.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará Declaração e Sobrecarga de Métodos. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Métodos e Funções<br>System.out.println(\"50\");', '5'),
('11', 'Seção 11', 'Funcionamento de Arrays', 'Módulo focado em Funcionamento de Arrays.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção você dominará Alocação e Índices de Vetores. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Funcionamento de Arrays<br>System.out.println(\"Java\");', '5'),
('12', 'Seção 12', 'Inteirando sobre Arrays', 'Módulo focado em Inteirando sobre Arrays.', '#ff4b4b', 'vistodecimarosa.png', 'Nesta seção você dominará Percorrendo Vetores com for e for-each. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Iterando sobre Arrays<br>System.out.println(\"60\");', '5'),
('13', 'Seção 13', 'Operações com String', 'Módulo focado em Operações com String.', '#ce82ff', 'vistodecimaroxo.png', 'Nesta seção você dominará Métodos toUpperCase, length e substring. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Operações com String<br>System.out.println(\"JAVA\");', '5'),
('14', 'Seção 14', 'Desafios Finais', 'Módulo focado em Desafios Finais.', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Revisão e Prática Integrada dos Módulos Anteriores. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', 'System.out.println(\"Sucesso Total\");', '5'),
('15', 'Seção 15', 'Consolidação dos Fundamentos', 'Módulo focado em Consolidação dos Fundamentos.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará Consolidação da Primeira Etapa de Java. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', 'System.out.println(\"Certificado Conquistado\");', '5'),
('16', 'Seção 16', 'Arrays Multidimensionais', 'Módulo focado em Arrays Multidimensionais.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção você dominará Matrizes e Linhas/Colunas. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Arrays Multidimensionais<br>System.out.println(\"99\");', '5'),
('17', 'Seção 17', 'HashMap Parte 1', 'Módulo focado em HashMap Parte 1.', '#ff4b4b', 'vistodecimarosa.png', 'Nesta seção você dominará Mapeamento de Chave e Valor com HashMap. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de HashMap Parte 1<br>System.out.println(\"Brasilia\");', '5'),
('18', 'Seção 18', 'HashMap Parte 2', 'Módulo focado em HashMap Parte 2.', '#ce82ff', 'vistodecimaroxo.png', 'Nesta seção você dominará Iteração e Métodos containsKey e remove. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de HashMap Parte 2<br>System.out.println(\"true\");', '5'),
('19', 'Seção 19', 'HashSet Parte 1', 'Módulo focado em HashSet Parte 1.', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Conjuntos de Elementos Únicos sem Repetição. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de HashSet Parte 1<br>System.out.println(\"2\");', '5'),
('20', 'Seção 20', 'HashSet Parte 2', 'Módulo focado em HashSet Parte 2.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará Operações de União e Interseção com Sets. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de HashSet Parte 2<br>System.out.println(\"true\");', '5'),
('21', 'Seção 21', 'Fluxo de Controle Avançado', 'Módulo focado em Fluxo de Controle Avançado.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção você dominará Controle com Break Rotulado e Continue. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Fluxo de Controle Avançado<br>System.out.println(\"1 3\");', '5'),
('22', 'Seção 22', 'Tratamento Básico de Erros', 'Módulo focado em Tratamento Básico de Erros.', '#ff4b4b', 'vistodecimarosa.png', 'Nesta seção você dominará Blocos try, catch e finally. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Tratamento Básico de Erros<br>System.out.println(\"Erro Capturado\");', '5'),
('23', 'Seção 23', 'Operações Avançadas com Strings', 'Módulo focado em Operações Avançadas com Strings.', '#ce82ff', 'vistodecimaroxo.png', 'Nesta seção você dominará StringBuilder e Regex em Strings. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Operações Avançadas com Strings<br>System.out.println(\"Opus\");', '5'),
('24', 'Seção 24', 'Projeto Contador de Palavras Únicas', 'Módulo focado em Projeto Contador de Palavras Únicas.', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Prática de Strings e Sets integrados. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Projeto Contador de Palavras Únicas<br>System.out.println(\"3\");', '5'),
('25', 'Seção 25', 'Data e Hora', 'Módulo focado em Data e Hora.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará A nova API java.time (LocalDate). Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Data e Hora<br>System.out.println(\"2026\");', '5'),
('26', 'Seção 26', 'Avaliação Intermediária', 'Módulo focado em Avaliação Intermediária.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção você dominará Avaliação Intermediária de Competências. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Certificado da Seção 1 e 2<br>System.out.println(\"Etapa Concluida\");', '5'),
('27', 'Seção 27', 'Fundamentos de POO', 'Módulo focado em Fundamentos de POO.', '#ff4b4b', 'vistodecimarosa.png', 'Nesta seção você dominará Classes, Atributos e Métodos. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Fundamentos de POO<br>System.out.println(\"Ana\");', '5'),
('28', 'Seção 28', 'Modificadores de Acesso e Encapsulamento', 'Módulo focado em Modificadores de Acesso e Encapsulamento.', '#ce82ff', 'vistodecimaroxo.png', 'Nesta seção você dominará Modificadores private, public e Getters/Setters. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Modificadores de Acesso e Encapsulamento<br>System.out.println(\"100\");', '5'),
('29', 'Seção 29', 'Prioridades de Classe e Membros Estáticos', 'Módulo focado em Prioridades de Classe e Membros Estáticos.', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Palavra-chave static e Constantes final. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Prioridades de Classe e Membros Estáticos<br>System.out.println(\"1\");', '5'),
('30', 'Seção 30', 'Herança', 'Módulo focado em Herança.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará Reuso de Classes com a palavra-chave extends. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Herança<br>System.out.println(\"Au\");', '5'),
('31', 'Seção 31', 'Polimorfismo', 'Módulo focado em Polimorfismo.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção você dominará Sobrescrita de Métodos e Polimorfismo Dinâmico. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Polimorfismo<br>System.out.println(\"Polimorfismo\");', '5'),
('32', 'Seção 32', 'Interfaces e Classes Abstratas', 'Módulo focado em Interfaces e Classes Abstratas.', '#ff4b4b', 'vistodecimarosa.png', 'Nesta seção você dominará Contratos de Software com Interface e Classes Abstract. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Interfaces e Classes Abstratas<br>System.out.println(\"Autenticado\");', '5'),
('33', 'Seção 33', 'Métodos Especiais e Classe Object', 'Módulo focado em Métodos Especiais e Classe Object.', '#ce82ff', 'vistodecimaroxo.png', 'Nesta seção você dominará Sobrescrita de toString(), equals() e hashCode(). Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Métodos Especiais e Classe Object<br>System.out.println(\"Objeto Ativo\");', '5'),
('34', 'Seção 34', 'Conceitos Avançados de OOP (Generics)', 'Módulo focado em Conceitos Avançados de OOP (Generics).', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Tipagem Genérica com Generics <T>. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Conceitos Avançados de OOP (Generics)<br>System.out.println(\"Tesouro\");', '5'),
('35', 'Seção 35', 'Tratamento de Exceção em PPO', 'Módulo focado em Tratamento de Exceção em PPO.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará Criação de Exceções Customizadas com throw e throws. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Tratamento de Exceção em POO<br>System.out.println(\"SaldoInvalido\");', '5'),
('36', 'Seção 36', 'Padrões de Projeto Parte 1', 'Módulo focado em Padrões de Projeto Parte 1.', '#1cb0f6', 'vistodecimaazul.png', 'Nesta seção você dominará Padrão Singleton e Factory Method. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Padrões de Projeto Parte 1<br>System.out.println(\"Conexao Unica\");', '5'),
('37', 'Seção 37', 'Padrões de Projeto Parte 2', 'Módulo focado em Padrões de Projeto Parte 2.', '#ff4b4b', 'vistodecimarosa.png', 'Nesta seção você dominará Padrões Comportamentais: Strategy e Observer. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Padrões de Projeto Parte 2<br>System.out.println(\"Estrategia A\");', '5'),
('38', 'Seção 38', 'Projeto Gerenciamento de Biblioteca', 'Módulo focado em Projeto Gerenciamento de Biblioteca.', '#ce82ff', 'vistodecimaroxo.png', 'Nesta seção você dominará Modelagem Completa de Sistema em POO. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Projeto Gerenciamento de Biblioteca<br>System.out.println(\"Livro Cadastrado\");', '5'),
('39', 'Seção 39', 'Desafio Final', 'Módulo focado em Desafio Final.', '#58cc02', 'vistodecimaverde.png', 'Nesta seção você dominará Desafio Integrador de Arquitetura e Lógica. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Desafio Final<br>System.out.println(\"Sistema Concluido\");', '5'),
('40', 'Seção 40', 'Conclusão', 'Módulo focado em Conclusão.', '#ffc800', 'vistodecimalaranja.png', 'Nesta seção você dominará Conclusão Geral do Curso Opus Java Master. Pratique os conceitos centrais, analise os exemplos de código e consolide seu aprendizado com repetição e desafios reais.', '// Exemplo prático de Certificado de tudo<br>System.out.println(\"Mestre em Java\");', '5');

-- Dados da tabela: `desafios` (40 registros)
INSERT INTO `desafios` (`unidade_numero`, `badge`, `heading`, `intro`, `missao`, `alvo_visual`, `dica_texto`, `dica_codigo`, `arquivo`, `codigo_inicial`, `validador_js`, `mensagem_ajuste`) VALUES
('1', 'Java / Ola Mundo', 'O Seu Primeiro Programa Java', 'Agora que você já aprendeu os conceitos fundamentais nas lições anteriores, chegou a hora de colocar o conhecimento em prática!', 'Escreva um código em Java para exibir exatamente no terminal a seguinte mensagem:', 'Ola Mundo', 'Para imprimir uma mensagem no console em Java, utilizamos a instrução:', 'System.out.println(\"Ola Mundo\");', 'HelloWorld.java', 'public class HelloWorld {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}', '(normStdout.includes(\'ola mundo\') || normStdout.includes(\'olá mundo\') || normStdout.includes(\'hello world\'))', 'Seu programa executou, mas a saída esperada é exatamente \"Ola Mundo\". Ajuste o texto no editor e tente novamente!'),
('2', 'Java / Variáveis', 'Armazenando e Exibindo Variáveis', 'Agora que você já conheceu como funcionam as variáveis e o tipo inteiro, chegou a hora de declarar sua primeira variável na prática!', 'Declare uma variável inteira chamada <strong>pontos</strong> com o valor <strong>100</strong> e exiba o seu valor no terminal:', '100', 'Para declarar uma variável inteira e imprimir seu valor, utilize:', 'int pontos = 100;\nSystem.out.println(pontos);', 'Pontuacao.java', 'public class Pontuacao {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}', '(normStdout.includes(\'100\'))', 'Seu programa executou, mas a saída esperada é o valor 100 da variável pontos. Verifique a declaração e tente novamente!'),
('3', 'Java / Variáveis Parte 2', 'Números Decimais e Lógicos', 'Agora que você domina os tipos double, boolean, char e constantes (final), vamos aplicar isso em um programa real!', 'Declare uma variável <strong>double preco = 19.90;</strong> (ou imprima o valor decimal) para exibir exatamente no terminal:', '19.9', 'Para declarar uma variável decimal e exibi-la no console, utilize:', 'double preco = 19.9;\nSystem.out.println(preco);', 'Produto.java', 'public class Produto {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}', '(normStdout.includes(\'199\') || stdout.includes(\'19.9\'))', 'Seu programa executou, mas a saída esperada no terminal deve conter o valor 19.9 (ou 19.90). Verifique sua variável double e tente novamente!'),
('4', 'Java / Operadores Parte 1', 'Calculando com Operadores Aritméticos', 'Chegou a hora de usar o computador como uma super calculadora utilizando os operadores matemáticos do Java!', 'Some dois números inteiros (ex: <strong>15 + 25</strong>) em uma variável e exiba o resultado <strong>40</strong> no terminal:', '40', 'Para somar dois valores em uma variável e imprimir o resultado, utilize:', 'int resultado = 15 + 25;\nSystem.out.println(resultado);', 'Calculadora.java', 'public class Calculadora {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}', '(normStdout.includes(\'40\'))', 'Seu programa executou, mas a saída esperada no terminal é 40. Verifique a soma e tente novamente!'),
('5', 'Java / Operadores Parte 2', 'Comparações e Lógica Booleana', 'Os operadores relacionais e lógicos permitem que nossos programas tomem decisões inteligentes retornando true ou false!', 'Avalie uma comparação verdadeira (ex: <strong>10 > 5</strong>) e exiba o resultado <strong>true</strong> no terminal:', 'true', 'Para guardar o resultado de uma comparação e exibi-lo, utilize:', 'boolean passou = 10 > 5;\nSystem.out.println(passou);', 'Verificador.java', 'public class Verificador {\n    public static void main(String[] args) {\n        // Escreva seu código aqui embaixo:\n        \n    }\n}', '(normStdout.includes(\'true\'))', 'Seu programa executou, mas a saída esperada no terminal é true. Verifique a expressão lógica e tente novamente!'),
('6', 'Java / Tomada de Decisão', 'Verificando Aprovação com if/else', 'Decisões condicionais são o coração da lógica de programação. Vamos criar uma verificação de nota para aprovação!', 'Verifique se a nota <strong>85</strong> é maior ou igual a 70. Se for, imprima exatamente: <strong>Aprovado</strong>', 'Aprovado', 'Utilize a estrutura if com System.out.println:', 'if (nota >= 70) {\n    System.out.println(\"Aprovado\");\n}', 'Aprovacao.java', 'public class Aprovacao {\n    public static void main(String[] args) {\n        int nota = 85;\n        // Escreva sua condição aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'aprovado\')', 'A saída esperada no terminal é exatamente \"Aprovado\". Verifique sua condição e tente novamente!'),
('7', 'Java / I/O Básico', 'Formatando Saída com printf', 'Apresentar dados com clareza é vital. Vamos formatar uma mensagem contendo texto e número decimal com printf!', 'Exiba uma mensagem formatada usando System.out.printf com o produto \"Mouse\" e preço 50.00. Saída esperada:', 'Mouse: R$ 50.00', 'Utilize especificadores de formato %s para texto e %.2f para decimal:', 'String prod = \"Mouse\";\ndouble valor = 50.0;\nSystem.out.printf(\"%s: R$ %.2f\", prod, valor);', 'Formatacao.java', 'public class Formatacao {\n    public static void main(String[] args) {\n        // Exiba exatamente Mouse: R$ 50.00 usando printf ou print:\n        \n    }\n}', 'normStdout.includes(\'mouse\') && normStdout.includes(\'50\')', 'Certifique-se de que a saída contenha exatamente \"Mouse: R$ 50.00\"!'),
('8', 'Java / Calculadora', 'Calculando Potência com Math.pow', 'A classe Math oferece utilitários matemáticos poderosos. Vamos calcular a potência de um número!', 'Calcule e exiba o resultado de 2 elevado a 4 (2^4 = 16) usando Math.pow ou cálculo direto. Saída esperada:', '16', 'Utilize Math.pow(base, expoente) e imprima o resultado:', 'double res = Math.pow(2, 4);\nSystem.out.println((int) res);', 'Potencia.java', 'public class Potencia {\n    public static void main(String[] args) {\n        // Calcule 2 elevado a 4 e imprima exatamente 16:\n        \n    }\n}', 'normStdout.includes(\'16\')', 'A saída esperada no terminal é 16. Verifique o cálculo de 2^4 e tente novamente!'),
('9', 'Java / Laços', 'Desafio Prático: Laços', 'Chegou a hora de comprovar seu domínio em Laços! Escreva o código Java necessário para solucionar o desafio.', 'Imprima os números de 1 a 3 em linhas separadas. Saída esperada no terminal:', '1 2 3', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"1 2 3\");', 'DesafioUnidade9.java', 'public class DesafioUnidade9 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'1\') && normStdout.includes(\'2\') && normStdout.includes(\'3\')', 'A saída esperada no console é \"1 2 3\". Verifique o código e tente novamente!'),
('10', 'Java / Métodos e Funções', 'Desafio Prático: Métodos e Funções', 'Chegou a hora de comprovar seu domínio em Métodos e Funções! Escreva o código Java necessário para solucionar o desafio.', 'Crie um método que some 20 e 30 e exiba 50. Saída esperada no terminal:', '50', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"50\");', 'DesafioUnidade10.java', 'public class DesafioUnidade10 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'50\')', 'A saída esperada no console é \"50\". Verifique o código e tente novamente!'),
('11', 'Java / Funcionamento de Arrays', 'Desafio Prático: Funcionamento de Arrays', 'Chegou a hora de comprovar seu domínio em Funcionamento de Arrays! Escreva o código Java necessário para solucionar o desafio.', 'Crie um vetor com 3 itens e exiba o primeiro item: Java Saída esperada no terminal:', 'Java', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Java\");', 'DesafioUnidade11.java', 'public class DesafioUnidade11 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'java\')', 'A saída esperada no console é \"Java\". Verifique o código e tente novamente!'),
('12', 'Java / Iterando sobre Arrays', 'Desafio Prático: Iterando sobre Arrays', 'Chegou a hora de comprovar seu domínio em Iterando sobre Arrays! Escreva o código Java necessário para solucionar o desafio.', 'Some os números do vetor {10, 20, 30} e exiba 60. Saída esperada no terminal:', '60', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"60\");', 'DesafioUnidade12.java', 'public class DesafioUnidade12 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'60\')', 'A saída esperada no console é \"60\". Verifique o código e tente novamente!'),
('13', 'Java / Operações com String', 'Desafio Prático: Operações com String', 'Chegou a hora de comprovar seu domínio em Operações com String! Escreva o código Java necessário para solucionar o desafio.', 'Converta o texto \"java\" para maiúsculas e exiba JAVA. Saída esperada no terminal:', 'JAVA', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"JAVA\");', 'DesafioUnidade13.java', 'public class DesafioUnidade13 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'java\')', 'A saída esperada no console é \"JAVA\". Verifique o código e tente novamente!'),
('14', 'Java / Desafios Finais', 'Desafio Prático: Desafios Finais', 'Chegou a hora de comprovar seu domínio em Desafios Finais! Escreva o código Java necessário para solucionar o desafio.', 'Exiba no console a mensagem: Sucesso Total Saída esperada no terminal:', 'Sucesso Total', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Sucesso Total\");', 'DesafioUnidade14.java', 'public class DesafioUnidade14 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'sucesso total\') || normStdout.includes(\'sucesso\')', 'A saída esperada no console é \"Sucesso Total\". Verifique o código e tente novamente!'),
('15', 'Java / Consolidação dos Fundamentos', 'Desafio Prático: Consolidação dos Fundamentos', 'Chegou a hora de comprovar seu domínio em Consolidação dos Fundamentos! Escreva o código Java necessário para solucionar o desafio.', 'Imprima no console: Certificado Conquistado Saída esperada no terminal:', 'Certificado Conquistado', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Certificado Conquistado\");', 'DesafioUnidade15.java', 'public class DesafioUnidade15 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'certificado\')', 'A saída esperada no console é \"Certificado Conquistado\". Verifique o código e tente novamente!'),
('16', 'Java / Arrays Multidimensionais', 'Desafio Prático: Arrays Multidimensionais', 'Chegou a hora de comprovar seu domínio em Arrays Multidimensionais! Escreva o código Java necessário para solucionar o desafio.', 'Acesse o item da linha 0 coluna 0 de uma matriz 2x2 contendo 99 e exiba-o. Saída esperada no terminal:', '99', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"99\");', 'DesafioUnidade16.java', 'public class DesafioUnidade16 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'99\')', 'A saída esperada no console é \"99\". Verifique o código e tente novamente!'),
('17', 'Java / HashMap Parte 1', 'Desafio Prático: HashMap Parte 1', 'Chegou a hora de comprovar seu domínio em HashMap Parte 1! Escreva o código Java necessário para solucionar o desafio.', 'Armazene a chave \"Brasil\" com valor \"Brasilia\" em um HashMap e exiba o valor. Saída esperada no terminal:', 'Brasilia', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Brasilia\");', 'DesafioUnidade17.java', 'public class DesafioUnidade17 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'brasilia\')', 'A saída esperada no console é \"Brasilia\". Verifique o código e tente novamente!'),
('18', 'Java / HashMap Parte 2', 'Desafio Prático: HashMap Parte 2', 'Chegou a hora de comprovar seu domínio em HashMap Parte 2! Escreva o código Java necessário para solucionar o desafio.', 'Verifique se a chave existe e imprima true. Saída esperada no terminal:', 'true', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"true\");', 'DesafioUnidade18.java', 'public class DesafioUnidade18 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'true\')', 'A saída esperada no console é \"true\". Verifique o código e tente novamente!'),
('19', 'Java / HashSet Parte 1', 'Desafio Prático: HashSet Parte 1', 'Chegou a hora de comprovar seu domínio em HashSet Parte 1! Escreva o código Java necessário para solucionar o desafio.', 'Adicione itens a um HashSet e exiba seu tamanho size() que é 2. Saída esperada no terminal:', '2', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"2\");', 'DesafioUnidade19.java', 'public class DesafioUnidade19 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'2\')', 'A saída esperada no console é \"2\". Verifique o código e tente novamente!'),
('20', 'Java / HashSet Parte 2', 'Desafio Prático: HashSet Parte 2', 'Chegou a hora de comprovar seu domínio em HashSet Parte 2! Escreva o código Java necessário para solucionar o desafio.', 'Verifique a presença de um item no set com contains() e exiba true. Saída esperada no terminal:', 'true', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"true\");', 'DesafioUnidade20.java', 'public class DesafioUnidade20 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'true\')', 'A saída esperada no console é \"true\". Verifique o código e tente novamente!'),
('21', 'Java / Fluxo de Controle Avançado', 'Desafio Prático: Fluxo de Controle Avançado', 'Chegou a hora de comprovar seu domínio em Fluxo de Controle Avançado! Escreva o código Java necessário para solucionar o desafio.', 'Pule o número 2 em uma contagem de 1 a 3 e exiba 1 e 3. Saída esperada no terminal:', '1 3', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"1 3\");', 'DesafioUnidade21.java', 'public class DesafioUnidade21 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'1\') && normStdout.includes(\'3\')', 'A saída esperada no console é \"1 3\". Verifique o código e tente novamente!'),
('22', 'Java / Tratamento Básico de Erros', 'Desafio Prático: Tratamento Básico de Erros', 'Chegou a hora de comprovar seu domínio em Tratamento Básico de Erros! Escreva o código Java necessário para solucionar o desafio.', 'Capture uma divisão por zero com try-catch e imprima: Erro Capturado Saída esperada no terminal:', 'Erro Capturado', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Erro Capturado\");', 'DesafioUnidade22.java', 'public class DesafioUnidade22 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'erro\')', 'A saída esperada no console é \"Erro Capturado\". Verifique o código e tente novamente!'),
('23', 'Java / Operações Avançadas com Strings', 'Desafio Prático: Operações Avançadas com Strings', 'Chegou a hora de comprovar seu domínio em Operações Avançadas com Strings! Escreva o código Java necessário para solucionar o desafio.', 'Crie um StringBuilder, adicione \"Opus\" e exiba. Saída esperada no terminal:', 'Opus', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Opus\");', 'DesafioUnidade23.java', 'public class DesafioUnidade23 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'opus\')', 'A saída esperada no console é \"Opus\". Verifique o código e tente novamente!'),
('24', 'Java / Projeto Contador de Palavras Únicas', 'Desafio Prático: Projeto Contador de Palavras Únicas', 'Chegou a hora de comprovar seu domínio em Projeto Contador de Palavras Únicas! Escreva o código Java necessário para solucionar o desafio.', 'Divida uma frase com split e conte 3 palavras. Saída esperada no terminal:', '3', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"3\");', 'DesafioUnidade24.java', 'public class DesafioUnidade24 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'3\')', 'A saída esperada no console é \"3\". Verifique o código e tente novamente!'),
('25', 'Java / Data e Hora', 'Desafio Prático: Data e Hora', 'Chegou a hora de comprovar seu domínio em Data e Hora! Escreva o código Java necessário para solucionar o desafio.', 'Obtenha o ano da data atual ou exiba 2026. Saída esperada no terminal:', '2026', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"2026\");', 'DesafioUnidade25.java', 'public class DesafioUnidade25 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'2026\')', 'A saída esperada no console é \"2026\". Verifique o código e tente novamente!'),
('26', 'Java / Avaliação Intermediária', 'Desafio Prático: Avaliação Intermediária', 'Chegou a hora de comprovar seu domínio em Avaliação Intermediária! Escreva o código Java necessário para solucionar o desafio.', 'Exiba no console: Etapa Concluida Saída esperada no terminal:', 'Etapa Concluida', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Etapa Concluida\");', 'DesafioUnidade26.java', 'public class DesafioUnidade26 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'etapa con\') || normStdout.includes(\'concluida\')', 'A saída esperada no console é \"Etapa Concluida\". Verifique o código e tente novamente!'),
('27', 'Java / Fundamentos de POO', 'Desafio Prático: Fundamentos de POO', 'Chegou a hora de comprovar seu domínio em Fundamentos de POO! Escreva o código Java necessário para solucionar o desafio.', 'Instancie uma classe Aluno com nome \"Ana\" e exiba o nome. Saída esperada no terminal:', 'Ana', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Ana\");', 'DesafioUnidade27.java', 'public class DesafioUnidade27 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'ana\')', 'A saída esperada no console é \"Ana\". Verifique o código e tente novamente!'),
('28', 'Java / Modificadores de Acesso e Encapsulamento', 'Desafio Prático: Modificadores de Acesso e Encapsulamento', 'Chegou a hora de comprovar seu domínio em Modificadores de Acesso e Encapsulamento! Escreva o código Java necessário para solucionar o desafio.', 'Defina um atributo privado, use setValor(100) e getValor() para exibir 100. Saída esperada no terminal:', '100', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"100\");', 'DesafioUnidade28.java', 'public class DesafioUnidade28 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'100\')', 'A saída esperada no console é \"100\". Verifique o código e tente novamente!'),
('29', 'Java / Prioridades de Classe e Membros Estáticos', 'Desafio Prático: Prioridades de Classe e Membros Estáticos', 'Chegou a hora de comprovar seu domínio em Prioridades de Classe e Membros Estáticos! Escreva o código Java necessário para solucionar o desafio.', 'Acesse uma variável estática Contador.total e exiba 1. Saída esperada no terminal:', '1', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"1\");', 'DesafioUnidade29.java', 'public class DesafioUnidade29 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'1\')', 'A saída esperada no console é \"1\". Verifique o código e tente novamente!'),
('30', 'Java / Herança', 'Desafio Prático: Herança', 'Chegou a hora de comprovar seu domínio em Herança! Escreva o código Java necessário para solucionar o desafio.', 'Crie a classe Cachorro herdando de Animal e emita o som: Au Saída esperada no terminal:', 'Au', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Au\");', 'DesafioUnidade30.java', 'public class DesafioUnidade30 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'au\')', 'A saída esperada no console é \"Au\". Verifique o código e tente novamente!'),
('31', 'Java / Polimorfismo', 'Desafio Prático: Polimorfismo', 'Chegou a hora de comprovar seu domínio em Polimorfismo! Escreva o código Java necessário para solucionar o desafio.', 'Trate uma subclasse através de sua classe pai e exiba: Polimorfismo Saída esperada no terminal:', 'Polimorfismo', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Polimorfismo\");', 'DesafioUnidade31.java', 'public class DesafioUnidade31 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'polimorfismo\')', 'A saída esperada no console é \"Polimorfismo\". Verifique o código e tente novamente!'),
('32', 'Java / Interfaces e Classes Abstratas', 'Desafio Prático: Interfaces e Classes Abstratas', 'Chegou a hora de comprovar seu domínio em Interfaces e Classes Abstratas! Escreva o código Java necessário para solucionar o desafio.', 'Implemente a interface Autenticavel e exiba: Autenticado Saída esperada no terminal:', 'Autenticado', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Autenticado\");', 'DesafioUnidade32.java', 'public class DesafioUnidade32 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'autenticado\')', 'A saída esperada no console é \"Autenticado\". Verifique o código e tente novamente!'),
('33', 'Java / Métodos Especiais e Classe Object', 'Desafio Prático: Métodos Especiais e Classe Object', 'Chegou a hora de comprovar seu domínio em Métodos Especiais e Classe Object! Escreva o código Java necessário para solucionar o desafio.', 'Sobrescreva toString() para retornar \"Objeto Ativo\" e exiba. Saída esperada no terminal:', 'Objeto Ativo', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Objeto Ativo\");', 'DesafioUnidade33.java', 'public class DesafioUnidade33 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'objeto\')', 'A saída esperada no console é \"Objeto Ativo\". Verifique o código e tente novamente!'),
('34', 'Java / Conceitos Avançados de OOP (Generics)', 'Desafio Prático: Conceitos Avançados de OOP (Generics)', 'Chegou a hora de comprovar seu domínio em Conceitos Avançados de OOP (Generics)! Escreva o código Java necessário para solucionar o desafio.', 'Crie uma classe Caixa<String> guardando \"Tesouro\" e exiba o item. Saída esperada no terminal:', 'Tesouro', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Tesouro\");', 'DesafioUnidade34.java', 'public class DesafioUnidade34 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'tesouro\')', 'A saída esperada no console é \"Tesouro\". Verifique o código e tente novamente!'),
('35', 'Java / Tratamento de Exceção em POO', 'Desafio Prático: Tratamento de Exceção em POO', 'Chegou a hora de comprovar seu domínio em Tratamento de Exceção em POO! Escreva o código Java necessário para solucionar o desafio.', 'Lance e trate uma exceção customizada exibindo: SaldoInvalido Saída esperada no terminal:', 'SaldoInvalido', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"SaldoInvalido\");', 'DesafioUnidade35.java', 'public class DesafioUnidade35 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'saldoinvalido\') || normStdout.includes(\'saldo\')', 'A saída esperada no console é \"SaldoInvalido\". Verifique o código e tente novamente!'),
('36', 'Java / Padrões de Projeto Parte 1', 'Desafio Prático: Padrões de Projeto Parte 1', 'Chegou a hora de comprovar seu domínio em Padrões de Projeto Parte 1! Escreva o código Java necessário para solucionar o desafio.', 'Garanta uma única instância com Singleton e exiba: Conexao Unica Saída esperada no terminal:', 'Conexao Unica', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Conexao Unica\");', 'DesafioUnidade36.java', 'public class DesafioUnidade36 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'conexao\')', 'A saída esperada no console é \"Conexao Unica\". Verifique o código e tente novamente!'),
('37', 'Java / Padrões de Projeto Parte 2', 'Desafio Prático: Padrões de Projeto Parte 2', 'Chegou a hora de comprovar seu domínio em Padrões de Projeto Parte 2! Escreva o código Java necessário para solucionar o desafio.', 'Aplique o padrão Strategy e exiba o resultado: Estrategia A Saída esperada no terminal:', 'Estrategia A', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Estrategia A\");', 'DesafioUnidade37.java', 'public class DesafioUnidade37 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'estrategia\')', 'A saída esperada no console é \"Estrategia A\". Verifique o código e tente novamente!'),
('38', 'Java / Projeto Gerenciamento de Biblioteca', 'Desafio Prático: Projeto Gerenciamento de Biblioteca', 'Chegou a hora de comprovar seu domínio em Projeto Gerenciamento de Biblioteca! Escreva o código Java necessário para solucionar o desafio.', 'Cadastre um livro no acervo e exiba: Livro Cadastrado Saída esperada no terminal:', 'Livro Cadastrado', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Livro Cadastrado\");', 'DesafioUnidade38.java', 'public class DesafioUnidade38 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'livro\')', 'A saída esperada no console é \"Livro Cadastrado\". Verifique o código e tente novamente!'),
('39', 'Java / Desafio Final', 'Desafio Prático: Desafio Final', 'Chegou a hora de comprovar seu domínio em Desafio Final! Escreva o código Java necessário para solucionar o desafio.', 'Execute o sistema completo e exiba a saída: Sistema Concluido Saída esperada no terminal:', 'Sistema Concluido', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Sistema Concluido\");', 'DesafioUnidade39.java', 'public class DesafioUnidade39 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'sistema\') || normStdout.includes(\'concluido\')', 'A saída esperada no console é \"Sistema Concluido\". Verifique o código e tente novamente!'),
('40', 'Java / Conclusão', 'Desafio Prático: Conclusão', 'Chegou a hora de comprovar seu domínio em Conclusão! Escreva o código Java necessário para solucionar o desafio.', 'Celebre a conclusão máxima emitindo no terminal: Mestre em Java Saída esperada no terminal:', 'Mestre em Java', 'Utilize System.out.println para imprimir exatamente a saída exigida:', 'System.out.println(\"Mestre em Java\");', 'DesafioUnidade40.java', 'public class DesafioUnidade40 {\n    public static void main(String[] args) {\n        // Escreva sua lógica aqui embaixo:\n        \n    }\n}', 'normStdout.includes(\'mestre\') || normStdout.includes(\'java\')', 'A saída esperada no console é \"Mestre em Java\". Verifique o código e tente novamente!');

-- Dados da tabela: `licoes` (200 registros)
INSERT INTO `licoes` (`id`, `unidade_numero`, `licao_numero`, `titulo`, `texto_explicativo`, `codigo_exemplo`, `criado_em`) VALUES
('1', '1', '1', 'Introdução - Primeiros Passos em Java', 'Java tem uma sintaxe clara e estruturada. Para exibir mensagens e textos na tela do computador, usamos a instrução System.out.println().\n\nO computador precisa de instruções precisas para funcionar. Em Java, o comando println envia uma linha inteira de saída para o terminal.\n\nPratique com repetição: todo texto precisa estar dentro dos parênteses da instrução!', 'System.out.println(\"Olá, Mundo!\");', '2026-10-08 16:05:50'),
('2', '1', '2', 'Introdução - A Regra das Aspas Duplas', 'Na programação Java, as aspas duplas são obrigatórias para textos. Quando colocamos caracteres entre \" \", o Java reconhece como uma String de texto.\n\nSe esquecermos as aspas em uma palavra ou frase, o compilador do Java gerará um erro e não conseguirá entender a instrução.\n\nLembre-se da regra de ouro: abra aspas duplas antes do texto e feche aspas duplas logo após!', 'System.out.println(\"Level Up\");', '2026-10-08 16:05:50'),
('3', '1', '3', 'Introdução - Maiúsculas e Minúsculas', 'Java é uma linguagem Case-Sensitive. Isso significa que letras maiúsculas e minúsculas fazem total diferença para o computador!\n\nA palavra System começa obrigatoriamente com \'S\' maiúsculo. Se você escrever system ou SYSTEM, o Java não reconhecerá a classe.\n\nJá a instrução println é toda em minúsculas. Preste sempre muita atenção nas maiúsculas ao escrever seu código!', 'System.out.println(\"Atenção às maiúsculas!\");', '2026-10-08 16:05:50'),
('4', '1', '4', 'Introdução - Parênteses e Ponto e Vírgula', 'Em Java, o conteúdo a ser exibido fica sempre dentro dos parênteses ( ).\n\nAlém disso, toda instrução em Java precisa terminar obrigatoriamente com o caractere ponto e vírgula (;).\n\nO ponto e vírgula indica ao computador onde o comando terminou. A fórmula completa é: System.out.println(\"Texto\");', 'System.out.println(\"Ponto e vírgula obrigatório!\");', '2026-10-08 16:05:50'),
('5', '1', '5', 'Introdução - Desafio IDE: Olá Mundo', 'Chegou a hora de juntar tudo o que você aprendeu com repetição e colocar as mãos no código real!\n\nNeste desafio prático no estilo Codédex, você usará o editor de código e o terminal OpenJDK para programar seu primeiro Hello World.\n\nEscreva a instrução para exibir Olá Mundo na tela e execute o programa no terminal!', '', '2026-10-08 16:05:50'),
('6', '2', '1', 'Variáveis - O Tipo Inteiro (int)', 'Na programação, variáveis são como caixas na memória do computador onde guardamos informações para usar mais tarde.\n\nO tipo int (inteiro) serve para armazenar números inteiros, ou seja, números sem casas decimais (como 10, 42, -5 e 0).\n\nPara criar uma variável inteira, informamos o tipo int, o nome da variável, o sinal de igual (=) e o valor finalizado com ponto e vírgula (;).\n\nPara exibir o valor guardado na variável, passamos seu nome diretamente em System.out.println(pontos); sem aspas!', 'int pontos = 100;\nSystem.out.println(pontos);', '2026-10-08 16:05:50'),
('7', '2', '2', 'Variáveis - Textos e a Classe String', 'Enquanto o tipo int guarda números inteiros, palavras e textos são representados em Java pelo tipo String.\n\nEm Java, a palavra String começa obrigatoriamente com \'S\' maiúsculo, pois tecnicamente ela é uma classe nativa da linguagem.\n\nO conteúdo de uma String deve ficar sempre delimitado por aspas duplas (\"...\"). Por exemplo: String jogador = \"Gabriel\";\n\nAssim como os números inteiros, para exibir a variável no console basta passar seu nome sem aspas para o comando de impressão.', 'String heroi = \"Link\";\nSystem.out.println(heroi);', '2026-10-08 16:05:50'),
('8', '2', '3', 'Variáveis - Concatenação de Textos (+)', 'Na programação, concatenar significa juntar duas ou mais sequências de texto em uma só.\n\nEm Java, o símbolo + realiza a concatenação de textos quando pelo menos um dos valores for uma String!\n\nPodemos combinar um texto fixo com uma variável: System.out.println(\"Olá, \" + nome);\n\nTambém podemos juntar textos com variáveis numéricas. Preste sempre atenção nos espaços dentro das aspas para não colar as palavras!', 'String curso = \"Java\";\nint ano = 2026;\nSystem.out.println(\"Estudando \" + curso + \" em \" + ano);', '2026-10-08 16:05:50'),
('9', '2', '4', 'Variáveis - Reatribuição e Atualização', 'O nome \'variável\' existe justamente porque seu valor pode variar ao longo da execução do programa!\n\nPara atualizar o valor de uma variável que já existe, NÃO devemos repetir o tipo (int ou String). Apenas usamos seu nome e o novo valor.\n\nSe você repetir o tipo (ex: int vidas = 2; novamente), o compilador acusará um erro de variável já declarada no mesmo bloco.\n\nO programa executa de cima para baixo. Portanto, a variável terá o valor da sua atribuição mais recente.', 'int moedas = 10;\nmoedas = 25;\nSystem.out.println(moedas);', '2026-10-08 16:05:50'),
('10', '2', '5', 'Variáveis - Desafio IDE: Declarando Variáveis', 'Chegou a hora de colocar o conhecimento em prática no editor de código real!\n\nNeste desafio prático no estilo Codédex, você declarará uma variável inteira e exibirá seu valor no terminal.\n\nEscreva a instrução para declarar a variável e execute o programa para ver o resultado!', '', '2026-10-08 16:05:50'),
('11', '3', '1', 'Variáveis Parte 2 - Números Decimais (double)', 'No mundo real, nem todos os números são inteiros. Preços de produtos, alturas, pesos e médias escolares possuem casas decimais.\n\nEm Java, o tipo primitivo padrão para guardar números com casas decimais é o double.\n\nAtenção à regra fundamental de sintaxe: em Java, usamos sempre o PONTO (.) e nunca a vírgula para separar as casas decimais (ex: double preco = 19.90;).\n\nSe usarmos vírgula em um número decimal no código, o compilador do Java acusará um erro de sintaxe!', 'double altura = 1.75;\ndouble preco = 29.99;\nSystem.out.println(preco);', '2026-10-08 16:05:50'),
('12', '3', '2', 'Variáveis Parte 2 - Lógica com boolean', 'Muitas vezes um programa precisa responder perguntas de Sim ou Não: O usuário está online? A porta está aberta? O aluno foi aprovado?\n\nPara armazenar valores lógicos em Java, utilizamos o tipo primitivo boolean.\n\nUma variável boolean aceita exclusivamente um de dois valores possíveis: true (verdadeiro) ou false (falso).\n\nEsses valores são palavras reservadas da linguagem Java: são escritos sempre em letras minúsculas e NUNCA levam aspas!', 'boolean aprovado = true;\nboolean chovendo = false;\nSystem.out.println(aprovado);', '2026-10-08 16:05:50'),
('13', '3', '3', 'Variáveis Parte 2 - Caracteres Únicos (char)', 'Enquanto o tipo String armazena palavras e frases inteiras usando aspas duplas (\"...\"), o Java possui um tipo especial para um único caractere: o char.\n\nUma variável do tipo char armazena exatamente uma única letra, símbolo ou dígito (como \'A\', \'B\', \'$\' ou \'7\').\n\nA regra de ouro do tipo char é o uso obrigatório de ASPAS SIMPLES (\' \') ao redor do caractere!\n\nLembre-se: aspas simples (\'A\') são para char (um só caractere), e aspas duplas (\"Texto\") são para String!', 'char rank = \'S\';\nchar inicial = \'G\';\nSystem.out.println(rank);', '2026-10-08 16:05:50'),
('14', '3', '4', 'Variáveis Parte 2 - Constantes com final', 'Existem valores que nunca devem mudar durante toda a execução de um programa, como o número Pi (3.14159) ou o limite máximo de jogadores.\n\nEm Java, usamos a palavra-chave final antes do tipo da variável para transformá-la em uma constante (um valor fixo e imutável).\n\nSe qualquer linha do código tentar alterar o valor de uma variável marcada com final, o compilador bloqueará a ação com um erro!\n\nPor convenção entre programadores Java, costumamos escrever o nome de constantes em LETRAS MAIÚSCULAS (ex: final int MAX_VIDAS = 3;).', 'final double PI = 3.14;\nfinal int MAX_NIVEL = 99;\nSystem.out.println(MAX_NIVEL);', '2026-10-08 16:05:50'),
('15', '3', '5', 'Variáveis Parte 2 - Desafio IDE: Tipos de Dados', 'Chegou a hora de colocar o conhecimento sobre novos tipos de variáveis em prática no editor de código real!\n\nNeste desafio prático no estilo Codédex, você declarará uma variável decimal (double) e exibirá seu valor no terminal.\n\nEscreva a instrução no editor e clique em Executar para concluir a Seção 3!', '', '2026-10-08 16:05:50'),
('16', '4', '1', 'Operadores Parte 1 - Adição (+) e Subtração (-)', 'Os computadores são calculadoras extremamente rápidas! Em Java, usamos os operadores aritméticos para realizar cálculos matemáticos.\n\nO operador + soma dois valores numéricos, enquanto o operador - realiza a subtração.\n\nPodemos fazer a conta diretamente dentro do println ou guardar o resultado dentro de uma variável: int total = 10 + 5;\n\nLembre-se: quando ambos os lados do + são números (int ou double), o Java realiza a SOMA matemática!', 'int saldo = 100 - 30;\nint bonus = saldo + 20;\nSystem.out.println(bonus);', '2026-10-08 16:05:50'),
('17', '4', '2', 'Operadores Parte 1 - Multiplicação (*) e Divisão (/)', 'Na programação Java, a multiplicação NÃO usa a letra \'x\'. Usamos sempre o símbolo asterisco (*).\n\nJá para a divisão, utilizamos a barra normal (/). Por exemplo: int metade = 20 / 2;\n\nAtenção a um detalhe importante do Java: quando dividimos dois números inteiros (int), o resultado também será inteiro (a parte decimal é descartada).\n\nPor exemplo, 7 / 2 entre inteiros resulta em 3. Se quisermos casas decimais, pelo menos um número deve ser double (ex: 7.0 / 2).', 'int dobro = 6 * 2;\nint metade = 10 / 2;\nSystem.out.println(dobro);', '2026-10-08 16:05:50'),
('18', '4', '3', 'Operadores Parte 1 - Resto da Divisão (%)', 'Existe um operador matemático muito útil na programação chamado Módulo, representado pelo símbolo de porcentagem (%).\n\nO operador % NÃO calcula porcentagem! Ele retorna o RESTO de uma divisão inteira.\n\nPor exemplo: na divisão de 10 por 3, o quociente é 3 e sobra 1. Portanto, 10 % 3 resulta em 1!\n\nQuando um número é perfeitamente divisível por outro, o resto é zero (ex: 8 % 2 resulta em 0). É assim que descobrimos se um número é par ou ímpar!', 'int sobra = 10 % 3;\nint par = 8 % 2;\nSystem.out.println(sobra);', '2026-10-08 16:05:50'),
('19', '4', '4', 'Operadores Parte 1 - Incremento (++) e Precedência', 'Somar ou diminuir 1 de uma variável é tão comum em jogos e programas que o Java possui atalhos especiais!\n\nO operador ++ aumenta o valor da variável em 1 unidade (ex: vidas++; é o mesmo que vidas = vidas + 1;).\n\nJá o operador -- diminui o valor da variável em 1 unidade.\n\nAlém disso, assim como na matemática da escola, a multiplicação (*) e a divisão (/) são calculadas ANTES da soma (+) e subtração (-), a menos que usemos parênteses ( )!', 'int nivel = 1;\nnivel++;\nint pontos = (2 + 3) * 10;\nSystem.out.println(pontos);', '2026-10-08 16:05:50'),
('20', '4', '5', 'Operadores Parte 1 - Desafio IDE: Calculadora', 'Chegou a hora de programar sua própria expressão aritmética no editor de código Java!\n\nNeste desafio prático no estilo Codédex, você somará dois números inteiros em uma variável e exibirá o resultado 40 no terminal.\n\nDigite seu código e clique em Executar!', '', '2026-10-08 16:05:50'),
('21', '5', '1', 'Operadores Parte 2 - Maior (>) e Menor (<)', 'Os operadores relacionais servem para comparar dois valores. O resultado de toda comparação em Java é sempre um boolean: true ou false!\n\nUsamos > para verificar se um valor é maior que outro, e < para verificar se é menor.\n\nTambém temos >= (maior ou igual) e <= (menor ou igual). Por exemplo: 18 >= 18 resulta em true!\n\nEssas comparações são a base para que o programa tome decisões inteligentes.', 'int idade = 20;\nboolean maiorDeIdade = idade >= 18;\nSystem.out.println(maiorDeIdade);', '2026-10-08 16:05:50'),
('22', '5', '2', 'Operadores Parte 2 - Igualdade (==) e Diferença (!=)', 'Preste muita atenção na diferença entre = e == em Java!\n\nUm único sinal de igual (=) serve para ATRIBUIR valor a uma variável (ex: int vidas = 3;).\n\nDois sinais de igual juntos (==) servem para COMPARAR se dois valores são iguais (ex: vidas == 3 resulta em true).\n\nJá o operador != verifica se dois valores são DIFERENTES (ex: 5 != 3 resulta em true, pois 5 é diferente de 3).', 'int senha = 1234;\nboolean correta = (senha == 1234);\nSystem.out.println(correta);', '2026-10-08 16:05:50'),
('23', '5', '3', 'Operadores Parte 2 - Operador Lógico E (&&)', 'Às vezes precisamos testar duas condições ao mesmo tempo. Para isso usamos os operadores lógicos!\n\nO operador && (chamado de AND ou \'E\' lógico) retorna true APENAS se TODAS as condições forem verdadeiras ao mesmo tempo.\n\nPor exemplo: para entrar em um campeonato avançado, o jogador precisa ter (nivel >= 10 && moedas >= 100).\n\nSe uma das condições for false, o resultado inteiro do && será false!', 'int nivel = 12;\nint moedas = 150;\nboolean podeEntrar = (nivel >= 10) && (moedas >= 100);\nSystem.out.println(podeEntrar);', '2026-10-08 16:05:50'),
('24', '5', '4', 'Operadores Parte 2 - OU (||) e Negação (!)', 'O operador || (duas barras verticais, chamado de OR ou \'OU\' lógico) retorna true se PELO MENOS UMA das condições for verdadeira.\n\nPor exemplo: (temIngresso || nomeNaLista) será true mesmo que apenas uma das duas coisas seja verdade!\n\nPor fim, o operador de negação ! (ponto de exclamação) inverte um valor booleano: !true vira false, e !false vira true.', 'boolean chovendo = false;\nboolean sairDeCasa = !chovendo;\nSystem.out.println(sairDeCasa);', '2026-10-08 16:05:50'),
('25', '5', '5', 'Operadores Parte 2 - Desafio IDE: Lógica Booleana', 'Chegou a hora de testar comparações e lógica booleana no editor Java!\n\nNeste desafio prático no estilo Codédex, avalie uma comparação verdadeira (como 10 > 5) e exiba o resultado true no terminal.\n\nEscreva seu código e clique em Executar!', '', '2026-10-08 16:05:50'),
('26', '6', '1', 'Estrutura Condicional Simples: if', 'O comando \'if\' é a estrutura básica para tomada de decisões em Java.\n\nEle avalia uma condição booleana. Se a expressão for verdadeira (true), o bloco de instruções dentro das chaves será executado.\n\nSe for falsa, o programa simplesmente ignora o bloco e continua a execução normalmente.', 'public class ExemploIf {\n    public static void main(String[] args) {\n        int idade = 20;\n        if (idade >= 18) {\n            System.out.println(\"Acesso permitido!\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('27', '6', '2', 'Estrutura Composta: if e else', 'Quando precisamos executar um caminho alternativo caso a condição seja falsa, usamos o \'else\'.\n\nO bloco \'else\' nunca recebe uma condição entre parênteses; ele é executado por padrão sempre que o \'if\' falhar.\n\nIsso garante que um dos dois caminhos seja obrigatoriamente seguido pelo programa.', 'public class ExemploIfElse {\n    public static void main(String[] args) {\n        int saldo = 50;\n        if (saldo >= 100) {\n            System.out.println(\"Compra aprovada!\");\n        } else {\n            System.out.println(\"Saldo insuficiente!\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('28', '6', '3', 'Múltiplas Decisões com else if', 'Quando temos três ou mais condições mutuamente exclusivas, encadeamos instruções com \'else if\'.\n\nO Java testa as condições de cima para baixo. Assim que encontra a primeira condição verdadeira, executa o bloco e encerra a checagem das demais.\n\nO \'else\' no final atua como caso genérico para quando nenhuma condição foi satisfeita.', 'public class ExemploElseIf {\n    public static void main(String[] args) {\n        int nota = 82;\n        if (nota >= 90) {\n            System.out.println(\"Conceito A\");\n        } else if (nota >= 80) {\n            System.out.println(\"Conceito B\");\n        } else {\n            System.out.println(\"Conceito C\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('29', '6', '4', 'Seleção com switch e break', 'A instrução \'switch\' avalia uma única variável contra valores específicos chamados \'case\'.\n\nÉ uma alternativa muito limpa quando testamos igualdade de inteiros, caracteres ou Strings.\n\nA palavra-chave \'break\' é essencial para encerrar o switch; sem ela, o código continua executando os blocos abaixo (\'fall-through\').', 'public class ExemploSwitch {\n    public static void main(String[] args) {\n        int dia = 2;\n        switch (dia) {\n            case 1: System.out.println(\"Domingo\"); break;\n            case 2: System.out.println(\"Segunda\"); break;\n            default: System.out.println(\"Outro dia\"); break;\n        }\n    }\n}', '2026-10-08 16:05:50'),
('30', '6', '5', 'Desafio Prático: Verificador de Decisão', 'Parabéns por dominar estruturas condicionais! Na programação real, o tempo todo precisamos validar regras de negócio, limites e autorizações.\n\nAgora você aplicará esses conceitos no desafio prático de código a seguir, compilando e testando na IDE!', 'public class DesafioDecisao {\n    public static void main(String[] args) {\n        int nota = 85;\n        if (nota >= 70) {\n            System.out.println(\"Aprovado\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('31', '7', '1', 'Introdução ao System.out: print vs println', 'Em Java, a saída padrão no terminal é controlada pelo objeto System.out.\n\nO método print() exibe a mensagem na tela sem quebrar linha ao final, permitindo encadear textos na mesma linha.\n\nJá o println() insere automaticamente uma quebra de linha após imprimir o conteúdo, posicionando o cursor na linha seguinte.', 'public class SaidaBasica {\n    public static void main(String[] args) {\n        System.out.print(\"Carregando\");\n        System.out.print(\"...\");\n        System.out.println(\" Concluído!\");\n    }\n}', '2026-10-08 16:05:50'),
('32', '7', '2', 'Lendo Dados com Scanner', 'A classe Scanner (do pacote java.util) é a maneira mais prática de capturar entradas do usuário no teclado.\n\nPara começar, instanciamos: Scanner sc = new Scanner(System.in);\n\nUsamos métodos específicos de acordo com o tipo: nextInt() para inteiros, nextDouble() para números reais e nextLine() para textos completos.', 'import java.util.Scanner;\n\npublic class Leitura {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        int pontuacao = 100;\n        System.out.println(\"Pontos lidos: \" + pontuacao);\n        sc.close();\n    }\n}', '2026-10-08 16:05:50'),
('33', '7', '3', 'Formatação Precisa com printf', 'O método printf() permite criar saídas altamente formatadas utilizando marcadores de formato (placeholders).\n\nOs principais marcadores são:\n%s para texto (String)\n%d para números inteiros (decimal integer)\n%.2f para números de ponto flutuante com 2 casas decimais.\n\nIsso evita concatenações confusas de strings com o operador +.', 'public class ExemploPrintf {\n    public static void main(String[] args) {\n        String item = \"Livro\";\n        double preco = 49.90;\n        System.out.printf(\"Item: %s | Valor: R$ %.2f\\n\", item, preco);\n    }\n}', '2026-10-08 16:05:50'),
('34', '7', '4', 'Armadilhas de Leitura: Buffer e Quebra de Linha', 'Um erro comum ao usar Scanner ocorre ao ler um número com nextInt() seguido imediatamente por um nextLine().\n\nO método nextInt() lê apenas o número, deixando a quebra de linha (Enter) pendente no buffer de entrada.\n\nQuando o nextLine() subsequente é chamado, ele consome esse Enter residual e retorna uma String vazia. A solução é adicionar um sc.nextLine() extra para limpar o buffer.', 'import java.util.Scanner;\n\npublic class LimpezaBuffer {\n    public static void main(String[] args) {\n        // Exemplo ilustrativo de consumo do buffer pendente:\n        int idade = 25;\n        String bufferPendente = \"\\n\"; // Enter pendente consumido\n        String nome = \"Opus\";\n        System.out.println(nome + \" - \" + idade);\n    }\n}', '2026-10-08 16:05:50'),
('35', '7', '5', 'Desafio Prático: Saída Formatada Profissional', 'Agora que você domina a saída com printf e manipulação de tipos no console, chegou o momento do desafio prático.\n\nFormate a mensagem exigida na tela com exatidão usando o conhecimento adquirido!', 'public class DesafioIO {\n    public static void main(String[] args) {\n        String prod = \"Mouse\";\n        double valor = 50.0;\n        System.out.printf(\"%s: R$ %.2f\\n\", prod, valor);\n    }\n}', '2026-10-08 16:05:50'),
('36', '8', '1', 'Operações Fundamentais e Precedência Matemática', 'No Java, assim como na matemática tradicional, operadores possuem regras claras de precedência.\n\nMultiplicação (*), divisão (/) e módulo (%) são calculados antes de adição (+) e subtração (-).\n\nPara alterar a ordem natural e forçar a execução prioritária de uma soma ou subtração, usamos parênteses.', 'public class Precedencia {\n    public static void main(String[] args) {\n        int res1 = 10 + 5 * 2;    // 20\n        int res2 = (10 + 5) * 2;  // 30\n        System.out.println(\"Sem parênteses: \" + res1);\n        System.out.println(\"Com parênteses: \" + res2);\n    }\n}', '2026-10-08 16:05:50'),
('37', '8', '2', 'Potenciação e Raízes com Math', 'A classe Math (do pacote padrão java.lang) traz dezenas de funções prontas.\n\nPara calcular potências: Math.pow(base, expoente) retorna um double com o resultado.\n\nPara raiz quadrada: Math.sqrt(numero) retorna a raiz positiva do número informado.', 'public class FuncoesMath {\n    public static void main(String[] args) {\n        double quad = Math.pow(5, 2); // 25.0\n        double raiz = Math.sqrt(49);   // 7.0\n        System.out.println(\"5 ao quadrado: \" + quad);\n        System.out.println(\"Raiz de 49: \" + raiz);\n    }\n}', '2026-10-08 16:05:50'),
('38', '8', '3', 'Arredondamentos e Valores Extremos: min, max e round', 'Outros métodos muito úteis da classe Math incluem:\n\nMath.max(a, b): retorna o maior entre dois números.\nMath.min(a, b): retorna o menor entre dois números.\nMath.abs(x): retorna o valor absoluto (módulo sem sinal).\nMath.round(x): arredonda o valor decimal para o inteiro mais próximo.', 'public class Arredondamento {\n    public static void main(String[] args) {\n        int maior = Math.max(15, 42); // 42\n        long arredondado = Math.round(7.6); // 8\n        System.out.println(\"Maior: \" + maior + \" | Arredondado: \" + arredondado);\n    }\n}', '2026-10-08 16:05:50'),
('39', '8', '4', 'Gerando Números Aleatórios com Math.random', 'O método Math.random() retorna um número decimal aleatório do tipo double no intervalo de 0.0 (inclusivo) até 1.0 (exclusivo).\n\nPara gerar um número inteiro entre 1 e 10, multiplicamos e fazemos o casting:\nint numero = (int)(Math.random() * 10) + 1;\n\nÉ a base para jogos, sorteios e lógicas dinâmicas.', 'public class Aleatorio {\n    public static void main(String[] args) {\n        double sorteado = Math.random();\n        int dado = (int)(sorteado * 6) + 1; // simula dado de 1 a 6\n        System.out.println(\"Resultado do dado: \" + dado);\n    }\n}', '2026-10-08 16:05:50'),
('40', '8', '5', 'Desafio Prático: Potência e Cálculos', 'Você já domina precedência matemática e os recursos da classe Math!\n\nAgora coloque em prática executando o cálculo de potência na IDE do desafio prático.', 'public class DesafioCalculadora {\n    public static void main(String[] args) {\n        double res = Math.pow(2, 4);\n        System.out.println((int) res);\n    }\n}', '2026-10-08 16:05:50'),
('41', '9', '1', 'Introdução a Laços', 'Bem-vindo ao estudo de Laços!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Laços!\");\n    }\n}', '2026-10-08 16:05:50'),
('42', '9', '2', 'Fundamentos e Mecânica de Laços', 'Avançando em Laços, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Laços\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('43', '9', '3', 'Aplicações Práticas de Laços', 'Agora que você compreende a base teórica, vamos aplicar Laços em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Laços\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 1 2 3...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('44', '9', '4', 'Boas Práticas e Prevenção de Falhas em Laços', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Laços, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"1 2 3\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('45', '9', '5', 'Desafio Prático e Consolidação: Laços', 'Parabéns por completar todas as etapas de estudo de Laços!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"1 2 3\");\n    }\n}', '2026-10-08 16:05:50'),
('46', '10', '1', 'Introdução a Métodos e Funções', 'Bem-vindo ao estudo de Métodos e Funções!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Métodos e Funções!\");\n    }\n}', '2026-10-08 16:05:50'),
('47', '10', '2', 'Fundamentos e Mecânica de Métodos e Funções', 'Avançando em Métodos e Funções, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Métodos e Funções\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('48', '10', '3', 'Aplicações Práticas de Métodos e Funções', 'Agora que você compreende a base teórica, vamos aplicar Métodos e Funções em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Métodos e Funções\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 50...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('49', '10', '4', 'Boas Práticas e Prevenção de Falhas em Métodos e Funções', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Métodos e Funções, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"50\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('50', '10', '5', 'Desafio Prático e Consolidação: Métodos e Funções', 'Parabéns por completar todas as etapas de estudo de Métodos e Funções!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"50\");\n    }\n}', '2026-10-08 16:05:50');
INSERT INTO `licoes` (`id`, `unidade_numero`, `licao_numero`, `titulo`, `texto_explicativo`, `codigo_exemplo`, `criado_em`) VALUES
('51', '11', '1', 'Introdução a Funcionamento de Arrays', 'Bem-vindo ao estudo de Funcionamento de Arrays!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Funcionamento de Arrays!\");\n    }\n}', '2026-10-08 16:05:50'),
('52', '11', '2', 'Fundamentos e Mecânica de Funcionamento de Arrays', 'Avançando em Funcionamento de Arrays, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Funcionamento de Arrays\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('53', '11', '3', 'Aplicações Práticas de Funcionamento de Arrays', 'Agora que você compreende a base teórica, vamos aplicar Funcionamento de Arrays em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Funcionamento de Arrays\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Java...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('54', '11', '4', 'Boas Práticas e Prevenção de Falhas em Funcionamento de Arrays', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Funcionamento de Arrays, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Java\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('55', '11', '5', 'Desafio Prático e Consolidação: Funcionamento de Arrays', 'Parabéns por completar todas as etapas de estudo de Funcionamento de Arrays!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Java\");\n    }\n}', '2026-10-08 16:05:50'),
('56', '12', '1', 'Introdução a Iterando sobre Arrays', 'Bem-vindo ao estudo de Iterando sobre Arrays!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Iterando sobre Arrays!\");\n    }\n}', '2026-10-08 16:05:50'),
('57', '12', '2', 'Fundamentos e Mecânica de Iterando sobre Arrays', 'Avançando em Iterando sobre Arrays, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Iterando sobre Arrays\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('58', '12', '3', 'Aplicações Práticas de Iterando sobre Arrays', 'Agora que você compreende a base teórica, vamos aplicar Iterando sobre Arrays em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Iterando sobre Arrays\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 60...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('59', '12', '4', 'Boas Práticas e Prevenção de Falhas em Iterando sobre Arrays', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Iterando sobre Arrays, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"60\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('60', '12', '5', 'Desafio Prático e Consolidação: Iterando sobre Arrays', 'Parabéns por completar todas as etapas de estudo de Iterando sobre Arrays!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"60\");\n    }\n}', '2026-10-08 16:05:50'),
('61', '13', '1', 'Introdução a Operações com String', 'Bem-vindo ao estudo de Operações com String!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Operações com String!\");\n    }\n}', '2026-10-08 16:05:50'),
('62', '13', '2', 'Fundamentos e Mecânica de Operações com String', 'Avançando em Operações com String, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Operações com String\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('63', '13', '3', 'Aplicações Práticas de Operações com String', 'Agora que você compreende a base teórica, vamos aplicar Operações com String em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Operações com String\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando JAVA...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('64', '13', '4', 'Boas Práticas e Prevenção de Falhas em Operações com String', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Operações com String, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"JAVA\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('65', '13', '5', 'Desafio Prático e Consolidação: Operações com String', 'Parabéns por completar todas as etapas de estudo de Operações com String!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"JAVA\");\n    }\n}', '2026-10-08 16:05:50'),
('66', '14', '1', 'Introdução a Desafios Finais', 'Bem-vindo ao estudo de Desafios Finais!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Desafios Finais!\");\n    }\n}', '2026-10-08 16:05:50'),
('67', '14', '2', 'Fundamentos e Mecânica de Desafios Finais', 'Avançando em Desafios Finais, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Desafios Finais\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('68', '14', '3', 'Aplicações Práticas de Desafios Finais', 'Agora que você compreende a base teórica, vamos aplicar Desafios Finais em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Desafios Finais\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Sucesso Total...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('69', '14', '4', 'Boas Práticas e Prevenção de Falhas em Desafios Finais', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Desafios Finais, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Sucesso Total\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('70', '14', '5', 'Desafio Prático e Consolidação: Desafios Finais', 'Parabéns por completar todas as etapas de estudo de Desafios Finais!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Sucesso Total\");\n    }\n}', '2026-10-08 16:05:50'),
('71', '15', '1', 'Introdução à Consolidação dos Fundamentos', 'Bem-vindo ao estudo de Consolida??o dos Fundamentos!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Certificado!\");\n    }\n}', '2026-10-08 16:05:50'),
('72', '15', '2', 'Fundamentos e Mecânica da Consolidação', 'Avançando em Consolida??o dos Fundamentos, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Certificado\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('73', '15', '3', 'Aplicações Práticas da Consolidação', 'Agora que você compreende a base teórica, vamos aplicar Consolida??o dos Fundamentos em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Certificado\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Certificado Conquistado...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('74', '15', '4', 'Boas Práticas e Prevenção de Falhas em Fundamentos', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Consolida??o dos Fundamentos, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Certificado Conquistado\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('75', '15', '5', 'Desafio Prático e Consolidação: Fundamentos', 'Parabéns por completar todas as etapas de estudo de Consolida??o dos Fundamentos!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Certificado Conquistado\");\n    }\n}', '2026-10-08 16:05:50'),
('76', '16', '1', 'Introdução a Arrays Multidimensionais', 'Bem-vindo ao estudo de Arrays Multidimensionais!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Arrays Multidimensionais!\");\n    }\n}', '2026-10-08 16:05:50'),
('77', '16', '2', 'Fundamentos e Mecânica de Arrays Multidimensionais', 'Avançando em Arrays Multidimensionais, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Arrays Multidimensionais\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('78', '16', '3', 'Aplicações Práticas de Arrays Multidimensionais', 'Agora que você compreende a base teórica, vamos aplicar Arrays Multidimensionais em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Arrays Multidimensionais\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 99...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('79', '16', '4', 'Boas Práticas e Prevenção de Falhas em Arrays Multidimensionais', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Arrays Multidimensionais, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"99\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('80', '16', '5', 'Desafio Prático e Consolidação: Arrays Multidimensionais', 'Parabéns por completar todas as etapas de estudo de Arrays Multidimensionais!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"99\");\n    }\n}', '2026-10-08 16:05:50'),
('81', '17', '1', 'Introdução a HashMap Parte 1', 'Bem-vindo ao estudo de HashMap Parte 1!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em HashMap Parte 1!\");\n    }\n}', '2026-10-08 16:05:50'),
('82', '17', '2', 'Fundamentos e Mecânica de HashMap Parte 1', 'Avançando em HashMap Parte 1, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de HashMap Parte 1\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('83', '17', '3', 'Aplicações Práticas de HashMap Parte 1', 'Agora que você compreende a base teórica, vamos aplicar HashMap Parte 1 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em HashMap Parte 1\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Brasilia...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('84', '17', '4', 'Boas Práticas e Prevenção de Falhas em HashMap Parte 1', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com HashMap Parte 1, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Brasilia\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('85', '17', '5', 'Desafio Prático e Consolidação: HashMap Parte 1', 'Parabéns por completar todas as etapas de estudo de HashMap Parte 1!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Brasilia\");\n    }\n}', '2026-10-08 16:05:50'),
('86', '18', '1', 'Introdução a HashMap Parte 2', 'Bem-vindo ao estudo de HashMap Parte 2!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em HashMap Parte 2!\");\n    }\n}', '2026-10-08 16:05:50'),
('87', '18', '2', 'Fundamentos e Mecânica de HashMap Parte 2', 'Avançando em HashMap Parte 2, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de HashMap Parte 2\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('88', '18', '3', 'Aplicações Práticas de HashMap Parte 2', 'Agora que você compreende a base teórica, vamos aplicar HashMap Parte 2 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em HashMap Parte 2\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando true...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('89', '18', '4', 'Boas Práticas e Prevenção de Falhas em HashMap Parte 2', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com HashMap Parte 2, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"true\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('90', '18', '5', 'Desafio Prático e Consolidação: HashMap Parte 2', 'Parabéns por completar todas as etapas de estudo de HashMap Parte 2!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"true\");\n    }\n}', '2026-10-08 16:05:50'),
('91', '19', '1', 'Introdução a HashSet Parte 1', 'Bem-vindo ao estudo de HashSet Parte 1!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em HashSet Parte 1!\");\n    }\n}', '2026-10-08 16:05:50'),
('92', '19', '2', 'Fundamentos e Mecânica de HashSet Parte 1', 'Avançando em HashSet Parte 1, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de HashSet Parte 1\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('93', '19', '3', 'Aplicações Práticas de HashSet Parte 1', 'Agora que você compreende a base teórica, vamos aplicar HashSet Parte 1 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em HashSet Parte 1\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 2...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('94', '19', '4', 'Boas Práticas e Prevenção de Falhas em HashSet Parte 1', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com HashSet Parte 1, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"2\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('95', '19', '5', 'Desafio Prático e Consolidação: HashSet Parte 1', 'Parabéns por completar todas as etapas de estudo de HashSet Parte 1!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"2\");\n    }\n}', '2026-10-08 16:05:50'),
('96', '20', '1', 'Introdução a HashSet Parte 2', 'Bem-vindo ao estudo de HashSet Parte 2!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em HashSet Parte 2!\");\n    }\n}', '2026-10-08 16:05:50'),
('97', '20', '2', 'Fundamentos e Mecânica de HashSet Parte 2', 'Avançando em HashSet Parte 2, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de HashSet Parte 2\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('98', '20', '3', 'Aplicações Práticas de HashSet Parte 2', 'Agora que você compreende a base teórica, vamos aplicar HashSet Parte 2 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em HashSet Parte 2\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando true...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('99', '20', '4', 'Boas Práticas e Prevenção de Falhas em HashSet Parte 2', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com HashSet Parte 2, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"true\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('100', '20', '5', 'Desafio Prático e Consolidação: HashSet Parte 2', 'Parabéns por completar todas as etapas de estudo de HashSet Parte 2!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"true\");\n    }\n}', '2026-10-08 16:05:50');
INSERT INTO `licoes` (`id`, `unidade_numero`, `licao_numero`, `titulo`, `texto_explicativo`, `codigo_exemplo`, `criado_em`) VALUES
('101', '21', '1', 'Introdução a Fluxo de Controle Avançado', 'Bem-vindo ao estudo de Fluxo de Controle Avançado!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Fluxo de Controle Avançado!\");\n    }\n}', '2026-10-08 16:05:50'),
('102', '21', '2', 'Fundamentos e Mecânica de Fluxo de Controle Avançado', 'Avançando em Fluxo de Controle Avançado, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Fluxo de Controle Avançado\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('103', '21', '3', 'Aplicações Práticas de Fluxo de Controle Avançado', 'Agora que você compreende a base teórica, vamos aplicar Fluxo de Controle Avançado em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Fluxo de Controle Avançado\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 1 3...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('104', '21', '4', 'Boas Práticas e Prevenção de Falhas em Fluxo de Controle Avançado', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Fluxo de Controle Avançado, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"1 3\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('105', '21', '5', 'Desafio Prático e Consolidação: Fluxo de Controle Avançado', 'Parabéns por completar todas as etapas de estudo de Fluxo de Controle Avançado!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"1 3\");\n    }\n}', '2026-10-08 16:05:50'),
('106', '22', '1', 'Introdução a Tratamento Básico de Erros', 'Bem-vindo ao estudo de Tratamento Básico de Erros!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Tratamento Básico de Erros!\");\n    }\n}', '2026-10-08 16:05:50'),
('107', '22', '2', 'Fundamentos e Mecânica de Tratamento Básico de Erros', 'Avançando em Tratamento Básico de Erros, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Tratamento Básico de Erros\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('108', '22', '3', 'Aplicações Práticas de Tratamento Básico de Erros', 'Agora que você compreende a base teórica, vamos aplicar Tratamento Básico de Erros em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Tratamento Básico de Erros\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Erro Capturado...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('109', '22', '4', 'Boas Práticas e Prevenção de Falhas em Tratamento Básico de Erros', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Tratamento Básico de Erros, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Erro Capturado\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('110', '22', '5', 'Desafio Prático e Consolidação: Tratamento Básico de Erros', 'Parabéns por completar todas as etapas de estudo de Tratamento Básico de Erros!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Erro Capturado\");\n    }\n}', '2026-10-08 16:05:50'),
('111', '23', '1', 'Introdução a Operações Avançadas com Strings', 'Bem-vindo ao estudo de Operações Avançadas com Strings!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Operações Avançadas com Strings!\");\n    }\n}', '2026-10-08 16:05:50'),
('112', '23', '2', 'Fundamentos e Mecânica de Operações Avançadas com Strings', 'Avançando em Operações Avançadas com Strings, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Operações Avançadas com Strings\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('113', '23', '3', 'Aplicações Práticas de Operações Avançadas com Strings', 'Agora que você compreende a base teórica, vamos aplicar Operações Avançadas com Strings em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Operações Avançadas com Strings\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Opus...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('114', '23', '4', 'Boas Práticas e Prevenção de Falhas em Operações Avançadas com Strings', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Operações Avançadas com Strings, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Opus\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('115', '23', '5', 'Desafio Prático e Consolidação: Operações Avançadas com Strings', 'Parabéns por completar todas as etapas de estudo de Operações Avançadas com Strings!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Opus\");\n    }\n}', '2026-10-08 16:05:50'),
('116', '24', '1', 'Introdução a Projeto Contador de Palavras Únicas', 'Bem-vindo ao estudo de Projeto Contador de Palavras Únicas!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Projeto Contador de Palavras Únicas!\");\n    }\n}', '2026-10-08 16:05:50'),
('117', '24', '2', 'Fundamentos e Mecânica de Projeto Contador de Palavras Únicas', 'Avançando em Projeto Contador de Palavras Únicas, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Projeto Contador de Palavras Únicas\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('118', '24', '3', 'Aplicações Práticas de Projeto Contador de Palavras Únicas', 'Agora que você compreende a base teórica, vamos aplicar Projeto Contador de Palavras Únicas em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Projeto Contador de Palavras Únicas\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 3...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('119', '24', '4', 'Boas Práticas e Prevenção de Falhas em Projeto Contador de Palavras Únicas', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Projeto Contador de Palavras Únicas, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"3\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('120', '24', '5', 'Desafio Prático e Consolidação: Projeto Contador de Palavras Únicas', 'Parabéns por completar todas as etapas de estudo de Projeto Contador de Palavras Únicas!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"3\");\n    }\n}', '2026-10-08 16:05:50'),
('121', '25', '1', 'Introdução a Data e Hora', 'Bem-vindo ao estudo de Data e Hora!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Data e Hora!\");\n    }\n}', '2026-10-08 16:05:50'),
('122', '25', '2', 'Fundamentos e Mecânica de Data e Hora', 'Avançando em Data e Hora, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Data e Hora\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('123', '25', '3', 'Aplicações Práticas de Data e Hora', 'Agora que você compreende a base teórica, vamos aplicar Data e Hora em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Data e Hora\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 2026...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('124', '25', '4', 'Boas Práticas e Prevenção de Falhas em Data e Hora', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Data e Hora, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"2026\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('125', '25', '5', 'Desafio Prático e Consolidação: Data e Hora', 'Parabéns por completar todas as etapas de estudo de Data e Hora!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"2026\");\n    }\n}', '2026-10-08 16:05:50'),
('126', '26', '1', 'Introdução à Avaliação Intermediária', 'Bem-vindo ao estudo de Certificado da Seção 1 e 2!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Certificado da Seção 1 e 2!\");\n    }\n}', '2026-10-08 16:05:50'),
('127', '26', '2', 'Fundamentos e Mecânica da Avaliação Intermediária', 'Avançando em Certificado da Seção 1 e 2, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Certificado da Seção 1 e 2\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('128', '26', '3', 'Aplicações Práticas da Avaliação Intermediária', 'Agora que você compreende a base teórica, vamos aplicar Certificado da Seção 1 e 2 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Certificado da Seção 1 e 2\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Etapa Concluida...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('129', '26', '4', 'Boas Práticas e Prevenção de Falhas Intermediárias', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Certificado da Seção 1 e 2, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Etapa Concluida\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('130', '26', '5', 'Desafio Prático e Consolidação: Avaliação Intermediária', 'Parabéns por completar todas as etapas de estudo de Certificado da Seção 1 e 2!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Etapa Concluida\");\n    }\n}', '2026-10-08 16:05:50'),
('131', '27', '1', 'Introdução a Fundamentos de POO', 'Bem-vindo ao estudo de Fundamentos de POO!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Fundamentos de POO!\");\n    }\n}', '2026-10-08 16:05:50'),
('132', '27', '2', 'Fundamentos e Mecânica de Fundamentos de POO', 'Avançando em Fundamentos de POO, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Fundamentos de POO\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('133', '27', '3', 'Aplicações Práticas de Fundamentos de POO', 'Agora que você compreende a base teórica, vamos aplicar Fundamentos de POO em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Fundamentos de POO\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Ana...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('134', '27', '4', 'Boas Práticas e Prevenção de Falhas em Fundamentos de POO', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Fundamentos de POO, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Ana\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('135', '27', '5', 'Desafio Prático e Consolidação: Fundamentos de POO', 'Parabéns por completar todas as etapas de estudo de Fundamentos de POO!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Ana\");\n    }\n}', '2026-10-08 16:05:50'),
('136', '28', '1', 'Introdução a Modificadores de Acesso e Encapsulamento', 'Bem-vindo ao estudo de Modificadores de Acesso e Encapsulamento!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Modificadores de Acesso e Encapsulamento!\");\n    }\n}', '2026-10-08 16:05:50'),
('137', '28', '2', 'Fundamentos e Mecânica de Modificadores de Acesso e Encapsulamento', 'Avançando em Modificadores de Acesso e Encapsulamento, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Modificadores de Acesso e Encapsulamento\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('138', '28', '3', 'Aplicações Práticas de Modificadores de Acesso e Encapsulamento', 'Agora que você compreende a base teórica, vamos aplicar Modificadores de Acesso e Encapsulamento em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Modificadores de Acesso e Encapsulamento\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 100...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('139', '28', '4', 'Boas Práticas e Prevenção de Falhas em Modificadores de Acesso e Encapsulamento', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Modificadores de Acesso e Encapsulamento, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"100\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('140', '28', '5', 'Desafio Prático e Consolidação: Modificadores de Acesso e Encapsulamento', 'Parabéns por completar todas as etapas de estudo de Modificadores de Acesso e Encapsulamento!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"100\");\n    }\n}', '2026-10-08 16:05:50'),
('141', '29', '1', 'Introdução a Prioridades de Classe e Membros Estáticos', 'Bem-vindo ao estudo de Prioridades de Classe e Membros Estáticos!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Prioridades de Classe e Membros Estáticos!\");\n    }\n}', '2026-10-08 16:05:50'),
('142', '29', '2', 'Fundamentos e Mecânica de Prioridades de Classe e Membros Estáticos', 'Avançando em Prioridades de Classe e Membros Estáticos, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Prioridades de Classe e Membros Estáticos\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('143', '29', '3', 'Aplicações Práticas de Prioridades de Classe e Membros Estáticos', 'Agora que você compreende a base teórica, vamos aplicar Prioridades de Classe e Membros Estáticos em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Prioridades de Classe e Membros Estáticos\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando 1...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('144', '29', '4', 'Boas Práticas e Prevenção de Falhas em Prioridades de Classe e Membros Estáticos', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Prioridades de Classe e Membros Estáticos, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"1\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('145', '29', '5', 'Desafio Prático e Consolidação: Prioridades de Classe e Membros Estáticos', 'Parabéns por completar todas as etapas de estudo de Prioridades de Classe e Membros Estáticos!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"1\");\n    }\n}', '2026-10-08 16:05:50'),
('146', '30', '1', 'Introdução a Herança', 'Bem-vindo ao estudo de Herança!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Herança!\");\n    }\n}', '2026-10-08 16:05:50'),
('147', '30', '2', 'Fundamentos e Mecânica de Herança', 'Avançando em Herança, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Herança\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('148', '30', '3', 'Aplicações Práticas de Herança', 'Agora que você compreende a base teórica, vamos aplicar Herança em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Herança\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Au...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('149', '30', '4', 'Boas Práticas e Prevenção de Falhas em Herança', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Herança, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Au\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('150', '30', '5', 'Desafio Prático e Consolidação: Herança', 'Parabéns por completar todas as etapas de estudo de Herança!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Au\");\n    }\n}', '2026-10-08 16:05:50');
INSERT INTO `licoes` (`id`, `unidade_numero`, `licao_numero`, `titulo`, `texto_explicativo`, `codigo_exemplo`, `criado_em`) VALUES
('151', '31', '1', 'Introdução a Polimorfismo', 'Bem-vindo ao estudo de Polimorfismo!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Polimorfismo!\");\n    }\n}', '2026-10-08 16:05:50'),
('152', '31', '2', 'Fundamentos e Mecânica de Polimorfismo', 'Avançando em Polimorfismo, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Polimorfismo\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('153', '31', '3', 'Aplicações Práticas de Polimorfismo', 'Agora que você compreende a base teórica, vamos aplicar Polimorfismo em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Polimorfismo\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Polimorfismo...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('154', '31', '4', 'Boas Práticas e Prevenção de Falhas em Polimorfismo', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Polimorfismo, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Polimorfismo\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('155', '31', '5', 'Desafio Prático e Consolidação: Polimorfismo', 'Parabéns por completar todas as etapas de estudo de Polimorfismo!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Polimorfismo\");\n    }\n}', '2026-10-08 16:05:50'),
('156', '32', '1', 'Introdução a Interfaces e Classes Abstratas', 'Bem-vindo ao estudo de Interfaces e Classes Abstratas!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Interfaces e Classes Abstratas!\");\n    }\n}', '2026-10-08 16:05:50'),
('157', '32', '2', 'Fundamentos e Mecânica de Interfaces e Classes Abstratas', 'Avançando em Interfaces e Classes Abstratas, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Interfaces e Classes Abstratas\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('158', '32', '3', 'Aplicações Práticas de Interfaces e Classes Abstratas', 'Agora que você compreende a base teórica, vamos aplicar Interfaces e Classes Abstratas em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Interfaces e Classes Abstratas\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Autenticado...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('159', '32', '4', 'Boas Práticas e Prevenção de Falhas em Interfaces e Classes Abstratas', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Interfaces e Classes Abstratas, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Autenticado\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('160', '32', '5', 'Desafio Prático e Consolidação: Interfaces e Classes Abstratas', 'Parabéns por completar todas as etapas de estudo de Interfaces e Classes Abstratas!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Autenticado\");\n    }\n}', '2026-10-08 16:05:50'),
('161', '33', '1', 'Introdução a Métodos Especiais e Classe Object', 'Bem-vindo ao estudo de Métodos Especiais e Classe Object!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Métodos Especiais e Classe Object!\");\n    }\n}', '2026-10-08 16:05:50'),
('162', '33', '2', 'Fundamentos e Mecânica de Métodos Especiais e Classe Object', 'Avançando em Métodos Especiais e Classe Object, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Métodos Especiais e Classe Object\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('163', '33', '3', 'Aplicações Práticas de Métodos Especiais e Classe Object', 'Agora que você compreende a base teórica, vamos aplicar Métodos Especiais e Classe Object em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Métodos Especiais e Classe Object\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Objeto Ativo...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('164', '33', '4', 'Boas Práticas e Prevenção de Falhas em Métodos Especiais e Classe Object', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Métodos Especiais e Classe Object, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Objeto Ativo\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('165', '33', '5', 'Desafio Prático e Consolidação: Métodos Especiais e Classe Object', 'Parabéns por completar todas as etapas de estudo de Métodos Especiais e Classe Object!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Objeto Ativo\");\n    }\n}', '2026-10-08 16:05:50'),
('166', '34', '1', 'Introdução a Conceitos Avançados de OOP (Generics)', 'Bem-vindo ao estudo de Conceitos Avançados de OOP (Generics)!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Conceitos Avançados de OOP (Generics)!\");\n    }\n}', '2026-10-08 16:05:50'),
('167', '34', '2', 'Fundamentos e Mecânica de Conceitos Avançados de OOP (Generics)', 'Avançando em Conceitos Avançados de OOP (Generics), examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Conceitos Avançados de OOP (Generics)\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('168', '34', '3', 'Aplicações Práticas de Conceitos Avançados de OOP (Generics)', 'Agora que você compreende a base teórica, vamos aplicar Conceitos Avançados de OOP (Generics) em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Conceitos Avançados de OOP (Generics)\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Tesouro...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('169', '34', '4', 'Boas Práticas e Prevenção de Falhas em Conceitos Avançados de OOP (Generics)', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Conceitos Avançados de OOP (Generics), evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Tesouro\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('170', '34', '5', 'Desafio Prático e Consolidação: Conceitos Avançados de OOP (Generics)', 'Parabéns por completar todas as etapas de estudo de Conceitos Avançados de OOP (Generics)!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Tesouro\");\n    }\n}', '2026-10-08 16:05:50'),
('171', '35', '1', 'Introdução a Tratamento de Exceção em POO', 'Bem-vindo ao estudo de Tratamento de Exceção em POO!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Tratamento de Exceção em POO!\");\n    }\n}', '2026-10-08 16:05:50'),
('172', '35', '2', 'Fundamentos e Mecânica de Tratamento de Exceção em POO', 'Avançando em Tratamento de Exceção em POO, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Tratamento de Exceção em POO\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('173', '35', '3', 'Aplicações Práticas de Tratamento de Exceção em POO', 'Agora que você compreende a base teórica, vamos aplicar Tratamento de Exceção em POO em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Tratamento de Exceção em POO\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando SaldoInvalido...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('174', '35', '4', 'Boas Práticas e Prevenção de Falhas em Tratamento de Exceção em POO', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Tratamento de Exceção em POO, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"SaldoInvalido\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('175', '35', '5', 'Desafio Prático e Consolidação: Tratamento de Exceção em POO', 'Parabéns por completar todas as etapas de estudo de Tratamento de Exceção em POO!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"SaldoInvalido\");\n    }\n}', '2026-10-08 16:05:50'),
('176', '36', '1', 'Introdução a Padrões de Projeto Parte 1', 'Bem-vindo ao estudo de Padrões de Projeto Parte 1!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Padrões de Projeto Parte 1!\");\n    }\n}', '2026-10-08 16:05:50'),
('177', '36', '2', 'Fundamentos e Mecânica de Padrões de Projeto Parte 1', 'Avançando em Padrões de Projeto Parte 1, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Padrões de Projeto Parte 1\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('178', '36', '3', 'Aplicações Práticas de Padrões de Projeto Parte 1', 'Agora que você compreende a base teórica, vamos aplicar Padrões de Projeto Parte 1 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Padrões de Projeto Parte 1\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Conexao Unica...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('179', '36', '4', 'Boas Práticas e Prevenção de Falhas em Padrões de Projeto Parte 1', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Padrões de Projeto Parte 1, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Conexao Unica\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('180', '36', '5', 'Desafio Prático e Consolidação: Padrões de Projeto Parte 1', 'Parabéns por completar todas as etapas de estudo de Padrões de Projeto Parte 1!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Conexao Unica\");\n    }\n}', '2026-10-08 16:05:50'),
('181', '37', '1', 'Introdução a Padrões de Projeto Parte 2', 'Bem-vindo ao estudo de Padrões de Projeto Parte 2!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Padrões de Projeto Parte 2!\");\n    }\n}', '2026-10-08 16:05:50'),
('182', '37', '2', 'Fundamentos e Mecânica de Padrões de Projeto Parte 2', 'Avançando em Padrões de Projeto Parte 2, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Padrões de Projeto Parte 2\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('183', '37', '3', 'Aplicações Práticas de Padrões de Projeto Parte 2', 'Agora que você compreende a base teórica, vamos aplicar Padrões de Projeto Parte 2 em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Padrões de Projeto Parte 2\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Estrategia A...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('184', '37', '4', 'Boas Práticas e Prevenção de Falhas em Padrões de Projeto Parte 2', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Padrões de Projeto Parte 2, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Estrategia A\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('185', '37', '5', 'Desafio Prático e Consolidação: Padrões de Projeto Parte 2', 'Parabéns por completar todas as etapas de estudo de Padrões de Projeto Parte 2!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Estrategia A\");\n    }\n}', '2026-10-08 16:05:50'),
('186', '38', '1', 'Introdução a Projeto Gerenciamento de Biblioteca', 'Bem-vindo ao estudo de Projeto Gerenciamento de Biblioteca!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Projeto Gerenciamento de Biblioteca!\");\n    }\n}', '2026-10-08 16:05:50'),
('187', '38', '2', 'Fundamentos e Mecânica de Projeto Gerenciamento de Biblioteca', 'Avançando em Projeto Gerenciamento de Biblioteca, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Projeto Gerenciamento de Biblioteca\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('188', '38', '3', 'Aplicações Práticas de Projeto Gerenciamento de Biblioteca', 'Agora que você compreende a base teórica, vamos aplicar Projeto Gerenciamento de Biblioteca em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Projeto Gerenciamento de Biblioteca\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Livro Cadastrado...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('189', '38', '4', 'Boas Práticas e Prevenção de Falhas em Projeto Gerenciamento de Biblioteca', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Projeto Gerenciamento de Biblioteca, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Livro Cadastrado\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('190', '38', '5', 'Desafio Prático e Consolidação: Projeto Gerenciamento de Biblioteca', 'Parabéns por completar todas as etapas de estudo de Projeto Gerenciamento de Biblioteca!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Livro Cadastrado\");\n    }\n}', '2026-10-08 16:05:50'),
('191', '39', '1', 'Introdução a Desafio Final', 'Bem-vindo ao estudo de Desafio Final!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Desafio Final!\");\n    }\n}', '2026-10-08 16:05:50'),
('192', '39', '2', 'Fundamentos e Mecânica de Desafio Final', 'Avançando em Desafio Final, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Desafio Final\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('193', '39', '3', 'Aplicações Práticas de Desafio Final', 'Agora que você compreende a base teórica, vamos aplicar Desafio Final em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Desafio Final\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Sistema Concluido...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('194', '39', '4', 'Boas Práticas e Prevenção de Falhas em Desafio Final', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Desafio Final, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Sistema Concluido\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('195', '39', '5', 'Desafio Prático e Consolidação: Desafio Final', 'Parabéns por completar todas as etapas de estudo de Desafio Final!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Sistema Concluido\");\n    }\n}', '2026-10-08 16:05:50'),
('196', '40', '1', 'Introdução à Conclusão', 'Bem-vindo ao estudo de Conclus?o!\n\nNesta primeira aula, compreenderemos os objetivos principais, a sintaxe necessária e onde esse conceito é aplicado na construção de sistemas corporativos.\n\nA compreensão sólida deste fundamento evitará bugs comuns e tornará seu código muito mais legível.', 'public class ExemploAula1 {\n    public static void main(String[] args) {\n        System.out.println(\"Iniciando estudos em Certificado de tudo!\");\n    }\n}', '2026-10-08 16:05:50'),
('197', '40', '2', 'Fundamentos e Mecânica da Conclusão', 'Avançando em Conclus?o, examinamos os mecanismos internos e operadores envolvidos.\n\nJava é uma linguagem com tipagem forte e checagem estática, garantindo que o compilador encontre inconsistências antes mesmo do programa rodar.\n\nPreste sempre atenção aos tipos de retorno e à visibilidade dos recursos.', 'public class ExemploAula2 {\n    public static void main(String[] args) {\n        // Operação estruturada de Certificado de tudo\n        int status = 1;\n        System.out.println(\"Status operacional: \" + status);\n    }\n}', '2026-10-08 16:05:50'),
('198', '40', '3', 'Aplicações Práticas da Conclusão', 'Agora que você compreende a base teórica, vamos aplicar Conclus?o em um cenário do dia a dia do desenvolvimento.\n\nSaber combinar condicionais, repetições e tipos de dados permite construir fluxos de negócio consistentes e à prova de falhas.\n\nObserve com atenção como os dados trafegam e são manipulados no exemplo a seguir.', 'public class ExemploAula3 {\n    public static void main(String[] args) {\n        // Aplicação prática em Certificado de tudo\n        boolean ativo = true;\n        if (ativo) {\n            System.out.println(\"Processando Mestre em Java...\");\n        }\n    }\n}', '2026-10-08 16:05:50'),
('199', '40', '4', 'Boas Práticas e Prevenção de Falhas Finais', 'Programadores profissionais se destacam por antecipar possíveis falhas e aplicar convenções de código limpo (Clean Code).\n\nAo trabalhar com Conclus?o, evite duplicação desnecessária, utilize nomes significativos para variáveis e classes e garanta tratamento adequado para casos excepcionais.\n\nIsso torna seu software robusto e preparado para ambientes de produção.', 'public class ExemploAula4 {\n    public static void main(String[] args) {\n        // Código padronizado e limpo\n        String resultado = \"Mestre em Java\";\n        System.out.println(\"Resultado verificado: \" + resultado);\n    }\n}', '2026-10-08 16:05:50'),
('200', '40', '5', 'Desafio Prático e Consolidação: Conclusão', 'Parabéns por completar todas as etapas de estudo de Conclus?o!\n\nAgora é a hora da verdade: você programará no editor integrado, compilará o código em Java e testará se a saída gerada atende exatamente ao requisito da missão.\n\nSucesso no desafio!', 'public class ExemploAula5 {\n    public static void main(String[] args) {\n        System.out.println(\"Mestre em Java\");\n    }\n}', '2026-10-08 16:05:50');

-- Dados da tabela: `perguntas` (645 registros)
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('115', '1', 'Programas de computador usam código para dar instruções às máquinas. Em Java, qual instrução é usada para exibir uma mensagem na tela?', '// Como instruir o computador a exibir uma mensagem no terminal?', 'multipla_escolha', 'System.out.println', 'delete', 'printer.paper', 'A', '2026-10-15 22:33:27'),
('116', '1', 'Ordene as linhas de código para montar um programa que exibe duas mensagens na sequência correta:', 'System.out.println(\"Iniciando...\");\nSystem.out.println(\"Nível 1 Concluído!\");', 'ordenar_codigo', 'System.out.println(\"Iniciando...\");', 'System.out.println(\"Nível 1 Concluído!\");', 'Para apagar a palavra do computador', '0,1', '2026-10-15 22:33:27'),
('117', '1', 'Complete a instrução Java para enviar uma mensagem para a tela:', '___(\"Bem-vindo\");', 'completar_codigo', 'System.out.println', 'send', 'display', 'A', '2026-10-15 22:33:27'),
('118', '1', 'O que será impresso no terminal ao executar este comando?', 'System.out.println(24);', 'predicao_saida', '24', 'Nada (falta aspas)', 'print(24)', 'A', '2026-10-15 22:33:27'),
('119', '1', 'Complete a linha de código para exibir a mensagem \"Novo email\":', 'System.out.println(___);', 'completar_codigo', '\"Novo email\"', 'Novo email', 'email.delete', 'A', '2026-10-15 22:33:27'),
('120', '1', 'Encontre o bug! Toque na linha que contém um erro de sintaxe que impede a compilação:', 'public class Principal {\n    System.out.println(\"Bem-vindo\");\n    system.out.println(\"Ao Opus\");\n}', 'spot_bug', 'system deve começar com S maiúsculo (System)', 'Falta ponto e vírgula', 'Aspas incorretas', '3', '2026-10-15 22:33:27'),
('121', '2', 'Em Java, qualquer texto ou frase que queremos imprimir na tela deve estar obrigatoriamente entre qual destes símbolos?', '// Delimitação de textos e frases no comando', 'multipla_escolha', 'Aspas duplas (\"...\")', 'Apenas parênteses sem aspas', 'Cerquilhas (###)', 'A', '2026-10-15 22:33:27'),
('122', '2', 'Complete a instrução abaixo para exibir o texto Level Up na tela:', 'System.out.println(___);', 'completar_codigo', '\"Level Up\"', 'Level Up', '\'Level Up\'', 'A', '2026-10-15 22:33:27'),
('123', '2', 'O que acontecerá ao tentar executar este código Java sem aspas duplas no texto?', 'System.out.println(Olá);', 'multipla_escolha', 'Ocorrerá um erro de compilação, pois o Java não reconhece palavras sem aspas', 'O Java exibirá Olá normalmente', 'O texto será enviado para a lixeira', 'A', '2026-10-15 22:33:27'),
('124', '2', 'Complete o código para um aplicativo de música exibir \"Tocando agora...\":', 'System.out.println(___);', 'completar_codigo', '\"Tocando agora...\"', 'Tocando agora...', 'play_music', 'A', '2026-10-15 22:33:27'),
('125', '2', 'O que será impresso no terminal após executar esta instrução?', 'System.out.println(\"100\");', 'predicao_saida', '100', 'Erro de compilação', '\"100\"', 'A', '2026-10-15 22:33:27'),
('126', '2', 'Encontre o bug! Toque na linha que esqueceu de fechar as aspas duplas:', 'public class Mensagem {\n    System.out.println(\"Linha correta\");\n    System.out.println(\"Texto sem fechar aspas);\n}', 'spot_bug', 'Falta fechar a aspa dupla antes do parêntese', 'Comando inválido', 'Nome de classe errado', '3', '2026-10-15 22:33:27'),
('127', '3', 'Java é uma linguagem Case-Sensitive. O que isso significa na prática?', '// Case-Sensitive', 'multipla_escolha', 'O Java diferencia letras maiúsculas de minúsculas', 'O Java aceita qualquer letra maiúscula ou minúscula sem distinção', 'O Java só aceita comandos em maiúsculas', 'A', '2026-10-15 22:33:27'),
('128', '3', 'Complete a instrução com o nome correto da classe:', '___.out.println(\"Opus\");', 'completar_codigo', 'System', 'system', 'SYSTEM', 'A', '2026-10-15 22:33:27'),
('129', '3', 'Complete a instrução com o método de exibição de texto:', 'System.out.___(\"Aprendendo Java\");', 'completar_codigo', 'println', 'Println', 'PRINTLN', 'A', '2026-10-15 22:33:27'),
('130', '3', 'Java é Case-Sensitive! Toque na linha em que uma palavra-chave foi escrita com letras minúsculas incorretamente:', 'public class Teste {\n    system.out.println(\"Atenção!\");\n    System.out.println(\"Correto!\");\n}', 'spot_bug', 'system em minúsculo não é reconhecido pelo compilador Java', 'Ponto e vírgula incorreto', 'Texto inválido', '2', '2026-10-15 22:33:27'),
('131', '3', 'Complete a chamada de impressão:', 'System.___.println(\"Atenção aos detalhes!\");', 'completar_codigo', 'out', 'Out', 'OUT', 'A', '2026-10-15 22:33:27'),
('132', '3', 'Conecte cada elemento Java ao seu padrão de maiúsculas/minúsculas correto:', '{\"pares\":[{\"esq\":\"System\",\"dir\":\"Primeira letra Maiúscula (Classe)\"},{\"esq\":\"println\",\"dir\":\"Tudo em minúsculas (Método)\"},{\"esq\":\"out\",\"dir\":\"Tudo em minúsculas (Objeto)\"}]}', 'pareamento', 'System : Primeira letra Maiúscula (Classe)', 'println : Tudo em minúsculas (Método)', 'out : Tudo em minúsculas (Objeto)', 'ALL_MATCHED', '2026-10-15 22:33:27'),
('133', '4', 'Toda declaração ou instrução de comando em Java deve terminar obrigatoriamente com qual símbolo de pontuação?', '// Pontuação obrigatória ao final do comando', 'multipla_escolha', 'Ponto e vírgula (;)', 'Dois pontos (:)', 'Ponto final (.)', 'A', '2026-10-15 22:33:27'),
('134', '4', 'Complete o encerramento da instrução Java:', 'System.out.println(\"Quase lá\")___', 'completar_codigo', ';', ':', '.', 'A', '2026-10-15 22:33:27'),
('135', '4', 'Complete o fechamento da instrução:', 'System.out.println(\"Vitória!\"___', 'completar_codigo', ');', ':)', '.;', 'A', '2026-10-15 22:33:27'),
('136', '4', 'O código em programas de computador é feito de declarações. Qual é o número de declarações neste código Java?', 'System.out.println(\"Harry Potter\");\nSystem.out.println(\"J. K. Rowling\");', 'multipla_escolha', '2', '1', '3', 'A', '2026-10-15 22:33:27'),
('137', '4', 'Complete com o comando completo para exibir a mensagem na tela:', '___(\"Código Limpo\");', 'completar_codigo', 'System.out.println', 'System.out.print_line', 'print', 'A', '2026-10-15 22:33:27'),
('138', '4', 'Qual destas opções de código Java está 100% correta de acordo com todas as regras de sintaxe aprendidas?', '// Qual declaração segue todas as regras de sintaxe do Java?', 'multipla_escolha', 'System.out.println(\"Parabéns!\");', 'System.out.println(\"Parabéns!\")', 'system.out.println(Parabéns!);', 'A', '2026-10-15 22:33:27'),
('163', '6', 'O que é uma variável na programação Java?', '// Conceito fundamental de programação', 'multipla_escolha', 'Um espaço na memória com nome usado para armazenar dados', 'Um comando que desliga o terminal do computador', 'Um arquivo que só pode ser aberto através da internet', 'A', '2026-10-15 22:51:44'),
('164', '6', 'Conecte cada tipo de dado em Java ao seu valor de exemplo correspondente:', '{\"pares\":[{\"esq\":\"int\",\"dir\":\"42\"},{\"esq\":\"String\",\"dir\":\"\\\"Opus\\\"\"},{\"esq\":\"boolean\",\"dir\":\"true\"}]}', 'pareamento', 'int : 42', 'String : \"Opus\"', 'boolean : true', 'ALL_MATCHED', '2026-10-15 22:51:44'),
('165', '6', 'Complete a instrução para declarar a variável inteira:', '___ vidas = 3;', 'completar_codigo', 'int', 'integer', 'INT', 'A', '2026-10-15 22:51:44'),
('166', '6', 'O que será impresso no terminal após a execução do programa?', 'int nivel = 5;\nSystem.out.println(nivel);', 'predicao_saida', '5', 'nivel', 'int 5', 'A', '2026-10-15 22:51:44'),
('167', '6', 'Encontre o bug! Toque na linha em que uma variável foi usada incorretamente com erro:', 'int idade = 20;\nint pontos = idade + 10;\nsystem.out.println(pontos);', 'spot_bug', 'system deve iniciar com letra maiúscula (System)', 'A soma de inteiros está errada', 'Variável idade não foi inicializada', '3', '2026-10-15 22:51:44'),
('168', '6', 'Ordene as linhas lógicas para criar uma variável com valor 50 e exibir no terminal:', 'int moedas = 50;\nSystem.out.println(moedas);', 'ordenar_codigo', 'int moedas = 50;', 'System.out.println(moedas);', '50 = moedas', '0,1', '2026-10-15 22:51:44'),
('169', '7', 'Qual tipo de dado em Java é utilizado para armazenar palavras e frases?', '// Ex: \"Olá, Jogador!\"', 'multipla_escolha', 'String', 'str', 'text', 'A', '2026-10-15 22:51:44'),
('170', '7', 'Complete com o tipo de dado para criar a variável de texto:', '___ cargo = \"Desenvolvedor\";', 'completar_codigo', 'String', 'string', 'STRING', 'A', '2026-10-15 22:51:44'),
('171', '7', 'Qual declaração de variável de texto segue as regras da linguagem Java?', '// Declaração de texto', 'multipla_escolha', 'String cidade = \"Curitiba\";', 'String cidade = Curitiba;', 'string cidade = \"Curitiba\";', 'A', '2026-10-15 22:51:44'),
('172', '7', 'Complete o valor da variável para armazenar o nome Opus:', 'String app = ___;', 'completar_codigo', '\"Opus\"', 'Opus', '(Opus)', 'A', '2026-10-15 22:51:44'),
('173', '7', 'O que acontecerá ao tentar compilar este código Java?', 'String animal = Gato;', 'multipla_escolha', 'Ocorrerá um erro de compilação por falta de aspas duplas no texto', 'O Java colocará as aspas duplas automaticamente', 'A palavra será convertida para número', 'A', '2026-10-15 22:51:45'),
('174', '7', 'Verdadeiro ou Falso? O nome do tipo String em Java deve iniciar obrigatoriamente com letra maiúscula.', 'String nick = \"Player1\";', 'multipla_escolha', 'Verdadeiro', 'Falso', 'Apenas em programas para web', 'A', '2026-10-15 22:51:45'),
('175', '8', 'Em Java, quando usamos o operador + envolvendo um texto, qual operação é realizada?', '\"Pontos: \" + 50', 'multipla_escolha', 'Concatenação (junção de textos)', 'Soma matemática dos caracteres', 'Divisão de strings', 'A', '2026-10-15 22:51:45'),
('176', '8', 'Qual será a saída exibida no terminal após executar este código?', 'String jogo = \"Xadrez\";\nSystem.out.println(\"Partida de \" + jogo);', 'multipla_escolha', 'Partida de Xadrez', 'Partida de jogo', '\"Partida de Xadrez\"', 'A', '2026-10-15 22:51:45'),
('177', '8', 'Complete a instrução para juntar a saudação com o nome da variável:', 'String nick = \"Alex\";\nSystem.out.println(\"Olá, \" ___ nick);', 'completar_codigo', '+', '&', '.', 'A', '2026-10-15 22:51:45'),
('178', '8', 'Qual será o resultado exato da impressão abaixo?', 'String cor = \"Azul\";\nSystem.out.println(\"Cor:\" + cor);', 'multipla_escolha', 'Cor:Azul', 'Cor: Azul', 'Cor Azul', 'A', '2026-10-15 22:51:45'),
('179', '8', 'Complete com a variável para exibir \"Nível 10\" no terminal:', 'int level = 10;\nSystem.out.println(\"Nível \" + ___);', 'completar_codigo', 'level', '\"level\"', '10', 'A', '2026-10-15 22:51:45'),
('180', '8', 'O que será exibido no terminal ao executar este comando?', 'int a = 1;\nint b = 2;\nSystem.out.println(\"Total: \" + a + b);', 'multipla_escolha', 'Total: 12', 'Total: 3', 'Total: a + b', 'A', '2026-10-15 22:51:45'),
('181', '9', 'Para atualizar o valor de uma variável já criada anteriormente, como devemos proceder?', '// Atualização de variável', 'multipla_escolha', 'Escrever apenas o nome da variável e o novo valor, sem repetir o tipo', 'Repetir obrigatoriamente o tipo da variável antes do nome', 'Deletar a variável da memória e recriar com outro nome', 'A', '2026-10-15 22:51:45'),
('182', '9', 'Qual valor será exibido no terminal após executar este código?', 'int score = 50;\nscore = 80;\nSystem.out.println(score);', 'multipla_escolha', '80', '50', '130', 'A', '2026-10-15 22:51:45'),
('183', '9', 'O que acontecerá ao compilar o trecho de código abaixo?', 'int vidas = 3;\nint vidas = 2;', 'multipla_escolha', 'Ocorrerá um erro porque a variável já foi declarada no mesmo bloco', 'O Java atualizará o valor normalmente para 2', 'As vidas serão somadas automaticamente para 5', 'A', '2026-10-15 22:51:45'),
('184', '9', 'Complete a instrução para atualizar a variável status para \"Jogando\":', 'String status = \"Online\";\n___ = \"Jogando\";', 'completar_codigo', 'status', 'String status', 'status.set', 'A', '2026-10-15 22:51:45'),
('185', '9', 'Qual será o valor final impresso na tela do terminal?', 'int x = 10;\nx = 20;\nx = 30;\nSystem.out.println(x);', 'multipla_escolha', '30', '10', '20', 'A', '2026-10-15 22:51:45'),
('186', '9', 'Complete a reatribuição da variável para atualizar o saldo para 150:', 'int saldo = 100;\nsaldo = ___;', 'completar_codigo', '150', 'int 150', '\"150\"', 'A', '2026-10-15 22:51:45'),
('187', '11', 'Qual tipo primitivo em Java é ideal para armazenar números com casas decimais, como preços e alturas?', '// Ex: 1.75, 19.90, 3.14', 'multipla_escolha', 'double', 'int', 'boolean', 'A', '2026-10-16 02:37:37'),
('188', '11', 'Qual símbolo deve ser utilizado obrigatoriamente para separar as casas decimais de um double em Java?', '// Separador decimal no código-fonte', 'multipla_escolha', 'Ponto (.)', 'Vírgula (,)', 'Dois pontos (:)', 'A', '2026-10-16 02:37:37');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('189', '11', 'Complete a declaração da variável para armazenar a altura com casas decimais:', '___ altura = 1.82;', 'completar_codigo', 'double', 'int', 'String', 'A', '2026-10-16 02:37:37'),
('190', '11', 'Qual destas declarações de variável decimal compila sem erros em Java?', '// Escolha a sintaxe válida', 'multipla_escolha', 'double nota = 9.5;', 'double nota = 9,5;', 'int nota = 9.5;', 'A', '2026-10-16 02:37:37'),
('191', '11', 'Complete com o valor decimal em formato válido do Java para representar doze e cinquenta:', 'double valor = ___;', 'completar_codigo', '12.50', '12,50', '\"12,50\"', 'A', '2026-10-16 02:37:37'),
('192', '11', 'O que será exibido no console ao executar este código?', 'double peso = 68.4;\nSystem.out.println(\"Peso: \" + peso);', 'multipla_escolha', 'Peso: 68.4', 'Peso: 68', 'Peso: peso', 'A', '2026-10-16 02:37:37'),
('193', '12', 'Quais são os dois únicos valores que uma variável do tipo boolean pode armazenar em Java?', '// Valores lógicos em Java', 'multipla_escolha', 'true e false', 'sim e nao', 'TRUE e FALSE em maiúsculas', 'A', '2026-10-16 02:37:37'),
('194', '12', 'Complete com o tipo correto para declarar uma variável lógica:', '___ conectado = true;', 'completar_codigo', 'boolean', 'bool', 'BooleanText', 'A', '2026-10-16 02:37:37'),
('195', '12', 'Por que o código abaixo causará um erro de compilação em Java?', 'boolean ativo = \"true\";', 'multipla_escolha', 'Porque valores booleanos não devem ter aspas duplas (com aspas vira String)', 'Porque a palavra boolean deve começar com B maiúsculo', 'Porque falta usar vírgula no final da linha', 'A', '2026-10-16 02:37:37'),
('196', '12', 'Complete a variável lógica para indicar que o jogo NÃO está pausado (falso):', 'boolean pausado = ___;', 'completar_codigo', 'false', '\"false\"', 'FALSE', 'A', '2026-10-16 02:37:37'),
('197', '12', 'Qual será a saída exibida no terminal após executar o trecho abaixo?', 'boolean chave = false;\nchave = true;\nSystem.out.println(chave);', 'multipla_escolha', 'true', 'false', 'chave', 'A', '2026-10-16 02:37:37'),
('198', '12', 'Complete a instrução para exibir o estado da variável vip no console:', 'boolean vip = true;\nSystem.out.println(___);', 'completar_codigo', 'vip', '\"vip\"', 'boolean', 'A', '2026-10-16 02:37:37'),
('199', '13', 'Qual tipo primitivo do Java é utilizado para guardar apenas UM único caractere entre aspas simples?', '// Ex: \'A\', \'B\', \'?\'', 'multipla_escolha', 'char', 'String', 'letter', 'A', '2026-10-16 02:37:37'),
('200', '13', 'Complete a declaração para armazenar a letra inicial usando o tipo de caractere único:', '___ letra = \'J\';', 'completar_codigo', 'char', 'String', 'int', 'A', '2026-10-16 02:37:37'),
('201', '13', 'Qual é a diferença de pontuação entre um literal char e um literal String em Java?', 'char c = \'A\';\nString s = \"A\";', 'multipla_escolha', 'char usa aspas simples (\' \') e String usa aspas duplas (\" \")', 'Ambos usam apenas aspas simples', 'char não precisa de nenhum tipo de aspas', 'A', '2026-10-16 02:37:37'),
('202', '13', 'Complete o valor da variável char para guardar o conceito S:', 'char rank = ___;', 'completar_codigo', '\'S\'', '\"S\"', 'S', 'A', '2026-10-16 02:37:37'),
('203', '13', 'O que acontecerá se tentarmos guardar uma palavra inteira dentro de um char?', 'char turma = \'Java\';', 'multipla_escolha', 'Erro de compilação, pois char comporta apenas um único caractere', 'O Java guardará apenas a primeira letra automaticamente', 'O tipo mudará sozinho para String', 'A', '2026-10-16 02:37:37'),
('204', '13', 'Qual será a saída exibida no terminal ao executar este código?', 'char grupo = \'B\';\nSystem.out.println(\"Turma \" + grupo);', 'multipla_escolha', 'Turma B', 'Turma \'B\'', 'Turma grupo', 'A', '2026-10-16 02:37:37'),
('205', '14', 'Qual palavra-chave em Java impede que o valor de uma variável seja alterado depois de criado?', '// Valor constante e imutável', 'multipla_escolha', 'final', 'const', 'fixed', 'A', '2026-10-16 02:37:37'),
('206', '14', 'Complete a declaração para tornar o limite de vidas uma constante imutável:', '___ int MAX_VIDAS = 5;', 'completar_codigo', 'final', 'static_const', 'lock', 'A', '2026-10-16 02:37:37'),
('207', '14', 'O que acontecerá ao tentar compilar o código abaixo?', 'final int LIMITE = 100;\nLIMITE = 200;', 'multipla_escolha', 'Erro de compilação, pois não é permitido reatribuir valor a uma variável final', 'A constante LIMITE será atualizada para 200', 'O valor passará a ser 300', 'A', '2026-10-16 02:37:37'),
('208', '14', 'Qual é a convenção de nomenclatura recomendada em Java para nomear constantes (final)?', 'final double TAXA_JUROS = 0.05;', 'multipla_escolha', 'Letras todas maiúsculas separadas por underline (UPPER_SNAKE_CASE)', 'Começar obrigatoriamente com números', 'Usar espaços em branco entre as palavras', 'A', '2026-10-16 02:37:37'),
('209', '14', 'Complete a constante decimal para armazenar o valor aproximado de PI:', 'final ___ PI = 3.14;', 'completar_codigo', 'double', 'int', 'boolean', 'A', '2026-10-16 02:37:37'),
('210', '14', 'Qual será o resultado impresso no terminal pelo código abaixo?', 'final int BONUS = 50;\nSystem.out.println(\"Bônus: \" + BONUS);', 'multipla_escolha', 'Bônus: 50', 'Bônus: BONUS', 'Erro, pois variáveis final não podem ser impressas', 'A', '2026-10-16 02:37:37'),
('211', '16', 'Quando usamos o operador + entre dois números inteiros em Java, o que acontece?', 'int res = 15 + 5;', 'multipla_escolha', 'Os números são somados matematicamente (resultado 20)', 'Ocorre um erro porque falta texto', 'Os números são apagados', 'A', '2026-10-16 02:46:40'),
('212', '16', 'Complete a operação para somar os dois valores:', 'int soma = 30 ___ 10;', 'completar_codigo', '+', 'plus', '&', 'A', '2026-10-16 02:46:40'),
('213', '16', 'Qual será o valor exibido no terminal pelo código abaixo?', 'int energia = 100 - 25;\nSystem.out.println(energia);', 'multipla_escolha', '75', '125', '100 - 25', 'A', '2026-10-16 02:46:40'),
('214', '16', 'Complete a expressão para descontar 10 moedas do saldo:', 'int saldo = 50;\nint novoSaldo = saldo ___ 10;', 'completar_codigo', '-', 'less', '/', 'A', '2026-10-16 02:46:40'),
('215', '16', 'O que será impresso na tela após executar estas instruções?', 'int a = 10;\nint b = 20;\nSystem.out.println(a + b);', 'multipla_escolha', '30', '1020', 'a + b', 'A', '2026-10-16 02:46:40'),
('216', '16', 'Complete com o nome da variável para exibir o resultado da soma:', 'int total = 8 + 4;\nSystem.out.println(___);', 'completar_codigo', 'total', '\"total\"', '8', 'A', '2026-10-16 02:46:40'),
('217', '17', 'Qual símbolo é utilizado em Java para realizar a operação de multiplicação?', '// Multiplicação em Java', 'multipla_escolha', 'Asterisco (*)', 'Letra x', 'Ponto (.)', 'A', '2026-10-16 02:46:40'),
('218', '17', 'Complete a instrução para multiplicar o valor por 3:', 'int triplo = 10 ___ 3;', 'completar_codigo', '*', 'x', '^', 'A', '2026-10-16 02:46:40'),
('219', '17', 'Qual símbolo representa a divisão em Java?', 'int metade = 100 / 2;', 'multipla_escolha', 'Barra (/)', 'Dois pontos (:)', 'Porcentagem (%)', 'A', '2026-10-16 02:46:40'),
('220', '17', 'Complete o código para dividir os 50 pontos igualmente por 2:', 'int parte = 50 ___ 2;', 'completar_codigo', '/', ':', '\\', 'A', '2026-10-16 02:46:40'),
('221', '17', 'Em Java, qual é o resultado da divisão inteira 7 / 2 (quando ambos são int)?', 'int r = 7 / 2;\nSystem.out.println(r);', 'multipla_escolha', '3 (pois a parte decimal é descartada na divisão entre inteiros)', '3.5', '4', 'A', '2026-10-16 02:46:41'),
('222', '17', 'Qual será o valor final exibido no console?', 'int largura = 4;\nint altura = 5;\nSystem.out.println(largura * altura);', 'multipla_escolha', '20', '9', '45', 'A', '2026-10-16 02:46:41'),
('223', '18', 'O que o operador módulo (%) calcula em Java?', 'int r = 10 % 3;', 'multipla_escolha', 'O resto de uma divisão inteira', 'A porcentagem de um número', 'A multiplicação por 100', 'A', '2026-10-16 02:46:41'),
('224', '18', 'Qual é o resultado da operação 10 % 3 em Java?', 'int resto = 10 % 3;\nSystem.out.println(resto);', 'multipla_escolha', '1 (pois 10 dividido por 3 dá 3 e sobra 1)', '3', '0', 'A', '2026-10-16 02:46:41'),
('225', '18', 'Complete com o operador que obtém o resto da divisão:', 'int resto = 15 ___ 4;', 'completar_codigo', '%', '/', '*', 'A', '2026-10-16 02:46:41'),
('226', '18', 'Qual será o resultado de 8 % 2 no terminal?', 'System.out.println(8 % 2);', 'multipla_escolha', '0 (pois 8 é par e a divisão por 2 é exata)', '4', '2', 'A', '2026-10-16 02:46:41'),
('227', '18', 'Complete com o valor do resto esperado de 7 dividido por 2:', '// 7 dividido por 2 dá 3 e sobra:\nint sobra = ___;', 'completar_codigo', '1', '3.5', '0', 'A', '2026-10-16 02:46:41'),
('228', '18', 'Por que o operador % é muito usado para saber se um número x é par?', '// Verificando paridade', 'multipla_escolha', 'Porque todo número par dividido por 2 tem resto igual a 0 (x % 2 == 0)', 'Porque ele transforma números ímpares em textos', 'Porque ele multiplica o número por 2', 'A', '2026-10-16 02:46:41'),
('229', '19', 'O que a instrução pontos++; faz com a variável pontos?', 'int pontos = 10;\npontos++;', 'multipla_escolha', 'Aumenta o valor da variável em 1 (passa a valer 11)', 'Multiplica o valor por 2', 'Zera a variável', 'A', '2026-10-16 02:46:41'),
('230', '19', 'Complete com o operador de incremento para somar 1 à variável nivel:', 'int nivel = 5;\nnivel___;', 'completar_codigo', '++', '**', '//', 'A', '2026-10-16 02:46:41'),
('231', '19', 'Qual será o valor da variável vidas após executar este código?', 'int vidas = 3;\nvidas--;\nSystem.out.println(vidas);', 'multipla_escolha', '2', '3', '4', 'A', '2026-10-16 02:46:41'),
('232', '19', 'De acordo com a ordem de precedência matemática do Java, qual é o resultado de 2 + 3 * 4?', 'System.out.println(2 + 3 * 4);', 'multipla_escolha', '14 (pois a multiplicação 3 * 4 é feita antes da soma)', '20', '9', 'A', '2026-10-16 02:46:41'),
('233', '19', 'E qual será o resultado quando usamos parênteses em (2 + 3) * 4?', 'System.out.println((2 + 3) * 4);', 'multipla_escolha', '20 (pois os parênteses são resolvidos primeiro: 5 * 4)', '14', '24', 'A', '2026-10-16 02:46:41'),
('234', '19', 'Complete com o operador de decremento para reduzir 1 unidade:', 'int tentativas = 3;\ntentativas___;', 'completar_codigo', '--', '++', '==', 'A', '2026-10-16 02:46:41'),
('235', '21', 'Qual tipo de valor é retornado por qualquer comparação relacional (como 10 > 5) em Java?', 'System.out.println(10 > 5);', 'multipla_escolha', 'Um valor boolean (true ou false)', 'Sempre o número 10', 'Um texto com aspas', 'A', '2026-10-16 02:46:41'),
('236', '21', 'Complete a comparação para verificar se a pontuação é maior que 100:', 'boolean recorde = pontos ___ 100;', 'completar_codigo', '>', '<', '=', 'A', '2026-10-16 02:46:41'),
('237', '21', 'Qual será a saída exibida no terminal pelo código abaixo?', 'int nota = 7;\nSystem.out.println(nota >= 7);', 'multipla_escolha', 'true', 'false', '7', 'A', '2026-10-16 02:46:41'),
('238', '21', 'Complete com o operador \"maior ou igual a\":', 'boolean aprovado = media ___ 6;', 'completar_codigo', '>=', '<=', '=>', 'A', '2026-10-16 02:46:41');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('239', '21', 'O que será impresso no console ao executar esta comparação?', 'System.out.println(3 > 8);', 'multipla_escolha', 'false', 'true', '3', 'A', '2026-10-16 02:46:41'),
('240', '21', 'Complete para verificar se a temperatura está abaixo (menor que) de zero:', 'boolean congelando = temp ___ 0;', 'completar_codigo', '<', '>', '==', 'A', '2026-10-16 02:46:41'),
('241', '22', 'Qual operador é usado em Java para COMPARAR se dois valores são exatamente iguais?', '// Comparação de igualdade', 'multipla_escolha', '== (dois sinais de igual)', '= (um único sinal de igual)', '=== (três sinais de igual)', 'A', '2026-10-16 02:46:41'),
('242', '22', 'Complete a expressão para verificar se o número de vidas é igual a 3:', 'boolean cheio = (vidas ___ 3);', 'completar_codigo', '==', '=', '!=', 'A', '2026-10-16 02:46:41'),
('243', '22', 'O que o operador != verifica em uma expressão Java?', 'boolean resultado = (10 != 5);', 'multipla_escolha', 'Se os dois valores são diferentes', 'Se os dois valores são iguais', 'Divide 10 por 5', 'A', '2026-10-16 02:46:41'),
('244', '22', 'Complete com o operador de \"diferente de\":', 'boolean ativo = (status ___ 0);', 'completar_codigo', '!=', '==', '<>', 'A', '2026-10-16 02:46:41'),
('245', '22', 'Qual será a saída exibida no terminal pelo código abaixo?', 'int x = 5;\nSystem.out.println(x == 5);', 'multipla_escolha', 'true', 'false', '5', 'A', '2026-10-16 02:46:41'),
('246', '22', 'E qual será a saída de System.out.println(5 != 5);?', 'System.out.println(5 != 5);', 'multipla_escolha', 'false (pois 5 é igual a 5, e não diferente)', 'true', '0', 'A', '2026-10-16 02:46:41'),
('247', '23', 'Quando o operador lógico && (E) retorna true em Java?', 'boolean ok = condicao1 && condicao2;', 'multipla_escolha', 'Apenas quando AMBAS as condições são verdadeiras (true)', 'Quando ambas são falsas', 'Quando apenas uma é verdadeira', 'A', '2026-10-16 02:46:41'),
('248', '23', 'Complete com o operador lógico \"E\" para exigir as duas condições ao mesmo tempo:', 'boolean acesso = temChave ___ temSenha;', 'completar_codigo', '&&', '||', 'and', 'A', '2026-10-16 02:46:41'),
('249', '23', 'Qual será o resultado impresso por este código?', 'boolean a = true;\nboolean b = false;\nSystem.out.println(a && b);', 'multipla_escolha', 'false', 'true', 'Erro de compilação', 'A', '2026-10-16 02:46:41'),
('250', '23', 'Qual será o resultado da expressão (10 > 5) && (8 == 8)?', 'System.out.println((10 > 5) && (8 == 8));', 'multipla_escolha', 'true (pois ambas as comparações são verdadeiras)', 'false', '18', 'A', '2026-10-16 02:46:41'),
('251', '23', 'Complete a segunda condição para que o resultado final seja true:', 'boolean passou = (nota >= 7) && (___);', 'completar_codigo', 'frequencia >= 75', 'false', '0', 'A', '2026-10-16 02:46:41'),
('252', '23', 'Se um jogador tem nivel = 5, qual é o resultado de (nivel >= 10 && nivel < 20)?', 'int nivel = 5;\nSystem.out.println(nivel >= 10 && nivel < 20);', 'multipla_escolha', 'false (pois a primeira condição nivel >= 10 é falsa)', 'true', '5', 'A', '2026-10-16 02:46:41'),
('253', '24', 'Qual operador representa o \"OU\" lógico em Java (verdadeiro se pelo menos uma condição for true)?', 'boolean entrada = vip || convidado;', 'multipla_escolha', '|| (duas barras verticais)', '&&', 'OR', 'A', '2026-10-16 02:46:41'),
('254', '24', 'Complete com o operador lógico \"OU\":', 'boolean desconto = estudante ___ idoso;', 'completar_codigo', '||', '&&', '??', 'A', '2026-10-16 02:46:41'),
('255', '24', 'Qual será a saída exibida no terminal pelo código abaixo?', 'boolean sol = true;\nboolean piscina = false;\nSystem.out.println(sol || piscina);', 'multipla_escolha', 'true (pois pelo menos uma das variáveis é true)', 'false', 'sol', 'A', '2026-10-16 02:46:41'),
('256', '24', 'O que faz o operador de negação ! antes de um valor booleano (ex: !true)?', 'System.out.println(!true);', 'multipla_escolha', 'Inverte o valor lógico (true vira false, e false vira true)', 'Repete o valor duas vezes', 'Apaga a variável da memória', 'A', '2026-10-16 02:46:41'),
('257', '24', 'Complete com o operador de negação para inverter o valor de bloqueado:', 'boolean bloqueado = false;\nboolean liberado = ___bloqueado;', 'completar_codigo', '!', '~', '-', 'A', '2026-10-16 02:46:41'),
('258', '24', 'Qual será o resultado impresso por System.out.println(!false);?', 'System.out.println(!false);', 'multipla_escolha', 'true', 'false', '!false', 'A', '2026-10-16 02:46:41'),
('259', '26', 'Quando o bloco dentro do comando if é executado?', NULL, 'multipla_escolha', 'Apenas se a condição entre parênteses for verdadeira (true)', 'Sempre que o programa rodar', 'Somente quando a condição for falsa', 'A', '2026-10-16 14:11:25'),
('260', '26', 'Qual operador verifica se dois valores são estritamente iguais?', NULL, 'multipla_escolha', '==', '=', '!=', 'A', '2026-10-16 14:11:25'),
('261', '26', 'Ordene as linhas de código para montar a estrutura condicional if corretamente:', 'int nota = 85;\nif (nota >= 70) {\n    System.out.println(\"Aprovado\");\n}', 'ordenar_codigo', 'int nota = 85;', 'if (nota >= 70) {', '    System.out.println(\"Aprovado\");', '0,1,2,3', '2026-10-16 14:11:25'),
('262', '27', 'O bloco else pode ter sua própria condição entre parênteses como else (x > 5)?', NULL, 'multipla_escolha', 'Não, else não aceita condição direta; para isso usa-se else if', 'Sim, sempre que necessário', 'Sim, apenas com números inteiros', 'A', '2026-10-16 14:11:25'),
('263', '27', 'Se saldo = 50 e a condição for (saldo >= 100), qual mensagem é impressa?', NULL, 'multipla_escolha', 'Saldo insuficiente!', 'Compra aprovada!', 'Nenhuma mensagem', 'A', '2026-10-16 14:11:25'),
('264', '27', 'Quantos blocos else associados a um único if podem existir?', NULL, 'multipla_escolha', 'No máximo um', 'Quantos o programador quiser', 'Dois', 'A', '2026-10-16 14:11:25'),
('265', '28', 'Se a primeira condição no if for verdadeira, o que acontece com os else if seguintes?', NULL, 'multipla_escolha', 'São todos ignorados', 'São testados normalmente', 'Geram aviso no console', 'A', '2026-10-16 14:11:25'),
('266', '28', 'Qual conceito será impresso se nota for 85 no exemplo?', NULL, 'multipla_escolha', 'Conceito B', 'Conceito A', 'Conceito C', 'A', '2026-10-16 14:11:25'),
('267', '28', 'O uso de else if é indicado quando:', NULL, 'multipla_escolha', 'Temos várias alternativas excludentes a serem testadas sequencialmente', 'Queremos repetir o código várias vezes', 'Queremos criar variáveis locais', 'A', '2026-10-16 14:11:25'),
('268', '29', 'O que acontece se esquecermos a palavra break dentro de um case?', NULL, 'multipla_escolha', 'O Java executa os próximos cases sem checar a condição (fall-through)', 'Dá erro de compilação imediatamente', 'O programa para a execução', 'A', '2026-10-16 14:11:25'),
('269', '29', 'Qual cláusula do switch é executada quando nenhum case corresponde?', NULL, 'multipla_escolha', 'default', 'else', 'fallback', 'A', '2026-10-16 14:11:25'),
('270', '29', 'Quais tipos de dados podem ser utilizados na expressão do switch?', NULL, 'multipla_escolha', 'int, char, String, enum e seus wrappers', 'Apenas números decimais (double)', 'Qualquer objeto sem restrição', 'A', '2026-10-16 14:11:25'),
('271', '30', 'Qual a finalidade de unir condições com o operador &&?', NULL, 'multipla_escolha', 'Exigir que todas as expressões combinadas sejam verdadeiras', 'Permitir que apenas uma delas seja verdadeira', 'Inverter o resultado lógico', 'A', '2026-10-16 14:11:25'),
('272', '30', 'Qual operador lógico representa o OU (retorna true se pelo menos um for true)?', NULL, 'multipla_escolha', '||', '&&', '!=', 'A', '2026-10-16 14:11:25'),
('273', '30', 'Qual o resultado da expressão: (10 > 5) && (3 == 4)?', NULL, 'multipla_escolha', 'false', 'true', 'erro de sintaxe', 'A', '2026-10-16 14:11:25'),
('274', '31', 'Qual é a diferença central entre System.out.print e System.out.println?', NULL, 'multipla_escolha', 'println quebra a linha ao final, enquanto print mantém o cursor na mesma linha', 'print só imprime números e println só imprime texto', 'Não existe diferença prática', 'A', '2026-10-16 14:11:25'),
('275', '31', 'Qual comando gera uma linha em branco no console?', NULL, 'multipla_escolha', 'System.out.println();', 'System.out.empty();', 'System.break();', 'A', '2026-10-16 14:11:25'),
('276', '31', 'Qual caractere especial de escape representa uma quebra de linha manual em uma String?', NULL, 'multipla_escolha', '\\n', '\\t', '\\b', 'A', '2026-10-16 14:11:25'),
('277', '32', 'Qual pacote deve ser importado para utilizar a classe Scanner?', NULL, 'multipla_escolha', 'java.util.Scanner', 'java.io.Scanner', 'java.lang.Scanner', 'A', '2026-10-16 14:11:25'),
('278', '32', 'Qual método do Scanner lê uma linha inteira incluindo espaços?', NULL, 'multipla_escolha', 'nextLine()', 'next()', 'readAll()', 'A', '2026-10-16 14:11:25'),
('279', '32', 'Qual é uma boa prática após terminar o uso do Scanner com System.in?', NULL, 'multipla_escolha', 'Chamar sc.close() para liberar o recurso', 'Reiniciar a máquina virtual', 'Apagar a variável sc', 'A', '2026-10-16 14:11:25'),
('280', '33', 'Qual marcador no printf é usado para números inteiros?', NULL, 'multipla_escolha', '%d', '%i', '%int', 'A', '2026-10-16 14:11:25'),
('281', '33', 'O que significa o formato %.2f em um printf?', NULL, 'multipla_escolha', 'Exibir o número decimal com exatamente 2 casas decimais', 'Dividir o número por 2', 'Multiplicar por 100', 'A', '2026-10-16 14:11:25'),
('282', '33', 'Qual marcador é utilizado para exibir textos e cadeias de caracteres (String)?', NULL, 'multipla_escolha', '%s', '%t', '%str', 'A', '2026-10-16 14:11:25'),
('283', '34', 'Por que um nextLine() logo após nextInt() pode retornar vazio?', NULL, 'multipla_escolha', 'Porque ele lê o Enter pendente deixado no buffer pelo nextInt()', 'Porque o Java não aceita misturar tipos na leitura', 'Porque o teclado trava na leitura de números', 'A', '2026-10-16 14:11:25'),
('284', '34', 'Como resolvemos a questão do Enter pendente no buffer do Scanner?', NULL, 'multipla_escolha', 'Chamando um nextLine() extra logo após a leitura numérica', 'Reiniciando o computador', 'Instanciando dois scanners diferentes', 'A', '2026-10-16 14:11:25'),
('285', '34', 'Qual método do Scanner lê apenas a próxima palavra ignorando espaços seguintes?', NULL, 'multipla_escolha', 'next()', 'nextLine()', 'readWord()', 'A', '2026-10-16 14:11:25'),
('286', '35', 'Qual classe padrão representa a saída padrão do sistema?', NULL, 'multipla_escolha', 'System.out', 'System.in', 'System.err', 'A', '2026-10-16 14:11:25'),
('287', '35', 'Qual classe representa a entrada padrão de dados do sistema?', NULL, 'multipla_escolha', 'System.in', 'System.out', 'System.read', 'A', '2026-10-16 14:11:25'),
('288', '35', 'O método System.out.printf aceita múltiplos argumentos dinâmicos?', NULL, 'multipla_escolha', 'Sim, aceita a máscara de formato e todos os valores a preencher', 'Não, aceita no máximo 1 argumento', 'Apenas se forem do mesmo tipo', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('289', '36', 'Qual é o resultado da expressão Java: 2 + 3 * 4?', NULL, 'multipla_escolha', '14', '20', '24', 'A', '2026-10-16 14:11:25'),
('290', '36', 'Como alteramos a ordem de avaliação em expressões aritméticas no Java?', NULL, 'multipla_escolha', 'Usando parênteses ( )', 'Usando colchetes [ ]', 'Usando chaves { }', 'A', '2026-10-16 14:11:25'),
('291', '36', 'Qual operador calcula o resto de uma divisão inteira?', NULL, 'multipla_escolha', '%', '/', '//', 'A', '2026-10-16 14:11:25'),
('292', '37', 'Qual método da classe Math calcula a raiz quadrada de um número?', NULL, 'multipla_escolha', 'Math.sqrt()', 'Math.root()', 'Math.square()', 'A', '2026-10-16 14:11:25'),
('293', '37', 'O método Math.pow(2, 3) retorna qual valor?', NULL, 'multipla_escolha', '8.0', '6.0', '5.0', 'A', '2026-10-16 14:11:25'),
('294', '37', 'É necessário importar a classe Math com comando import no início do arquivo?', NULL, 'multipla_escolha', 'Não, pois faz parte do pacote java.lang, importado automaticamente', 'Sim, import java.util.Math', 'Sim, import java.math.Math', 'A', '2026-10-16 14:11:25'),
('295', '38', 'Qual método retorna o maior valor entre dois parâmetros?', NULL, 'multipla_escolha', 'Math.max()', 'Math.top()', 'Math.greater()', 'A', '2026-10-16 14:11:25'),
('296', '38', 'O que Math.abs(-15) retorna?', NULL, 'multipla_escolha', '15', '-15', '0', 'A', '2026-10-16 14:11:25'),
('297', '38', 'Qual é o resultado de Math.round(4.3)?', NULL, 'multipla_escolha', '4', '5', '4.3', 'A', '2026-10-16 14:11:25'),
('298', '39', 'Qual é o intervalo padrão de valores retornado por Math.random()?', NULL, 'multipla_escolha', 'De 0.0 (inclusivo) até 1.0 (exclusivo)', 'De 1 até 100', 'De -1.0 até 1.0', 'A', '2026-10-16 14:11:25'),
('299', '39', 'Para simular um número inteiro de 1 a 6 a partir de Math.random(), o que somamos ao final?', NULL, 'multipla_escolha', '+ 1', '+ 6', '+ 0.5', 'A', '2026-10-16 14:11:25'),
('300', '39', 'Qual tipo primitivo é retornado diretamente por Math.random()?', NULL, 'multipla_escolha', 'double', 'int', 'float', 'A', '2026-10-16 14:11:25'),
('301', '40', 'O que acontece ao dividir um número inteiro por zero em Java (ex: 10 / 0)?', NULL, 'multipla_escolha', 'Lança uma exceção ArithmeticException em tempo de execução', 'Retorna 0 sem erro', 'O programa compila e imprime null', 'A', '2026-10-16 14:11:25'),
('302', '40', 'Como evitar a divisão por zero em uma aplicação de calculadora?', NULL, 'multipla_escolha', 'Verificando com um if se o divisor é diferente de zero antes da conta', 'Usando apenas variáveis float', 'Não é possível evitar', 'A', '2026-10-16 14:11:25'),
('303', '40', 'Qual constante da classe Math representa o valor de Pi (3.14159...)?', NULL, 'multipla_escolha', 'Math.PI', 'Math.PI_VALUE', 'Math.pi()', 'A', '2026-10-16 14:11:25'),
('304', '41', 'Qual é a importância central de estudar Laços?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('305', '41', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('306', '41', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('307', '42', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('308', '42', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('309', '42', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('310', '43', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('311', '43', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('312', '43', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('313', '44', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('314', '44', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('315', '44', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('316', '45', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('317', '45', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('318', '45', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('319', '46', 'Qual é a importância central de estudar Métodos e Funções?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('320', '46', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('321', '46', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('322', '47', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('323', '47', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('324', '47', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('325', '48', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('326', '48', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('327', '48', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('328', '49', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('329', '49', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('330', '49', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('331', '50', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('332', '50', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('333', '50', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('334', '51', 'Qual é a importância central de estudar Funcionamento de Arrays?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('335', '51', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('336', '51', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('337', '52', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('338', '52', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('339', '52', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('340', '53', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('341', '53', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('342', '53', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('343', '54', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('344', '54', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('345', '54', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('346', '55', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('347', '55', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('348', '55', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('349', '56', 'Qual é a importância central de estudar Iterando sobre Arrays?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('350', '56', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('351', '56', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('352', '57', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('353', '57', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('354', '57', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('355', '58', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('356', '58', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('357', '58', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('358', '59', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('359', '59', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('360', '59', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('361', '60', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('362', '60', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('363', '60', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('364', '61', 'Qual é a importância central de estudar Operações com String?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('365', '61', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('366', '61', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('367', '62', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('368', '62', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('369', '62', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('370', '63', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('371', '63', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('372', '63', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('373', '64', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('374', '64', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('375', '64', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('376', '65', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('377', '65', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('378', '65', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('379', '66', 'Qual é a importância central de estudar Desafios Finais?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('380', '66', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('381', '66', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('382', '67', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('383', '67', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('384', '67', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('385', '68', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('386', '68', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('387', '68', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('388', '69', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('389', '69', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('390', '69', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('391', '70', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('392', '70', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('393', '70', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('394', '71', 'Qual é a importância central de consolidar os fundamentos?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('395', '71', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('396', '71', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('397', '72', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('398', '72', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('399', '72', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('400', '73', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('401', '73', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('402', '73', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('403', '74', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('404', '74', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('405', '74', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('406', '75', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('407', '75', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('408', '75', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('409', '76', 'Qual é a importância central de estudar Arrays Multidimensionais?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('410', '76', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('411', '76', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('412', '77', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('413', '77', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('414', '77', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('415', '78', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('416', '78', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('417', '78', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('418', '79', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('419', '79', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('420', '79', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('421', '80', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('422', '80', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('423', '80', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('424', '81', 'Qual é a importância central de estudar HashMap Parte 1?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('425', '81', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('426', '81', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('427', '82', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('428', '82', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('429', '82', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('430', '83', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('431', '83', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('432', '83', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('433', '84', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('434', '84', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('435', '84', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('436', '85', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('437', '85', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('438', '85', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('439', '86', 'Qual é a importância central de estudar HashMap Parte 2?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('440', '86', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('441', '86', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('442', '87', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('443', '87', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('444', '87', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('445', '88', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('446', '88', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('447', '88', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('448', '89', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('449', '89', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('450', '89', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('451', '90', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('452', '90', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('453', '90', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('454', '91', 'Qual é a importância central de estudar HashSet Parte 1?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('455', '91', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('456', '91', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('457', '92', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('458', '92', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('459', '92', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('460', '93', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('461', '93', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('462', '93', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('463', '94', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('464', '94', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('465', '94', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('466', '95', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('467', '95', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('468', '95', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('469', '96', 'Qual é a importância central de estudar HashSet Parte 2?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('470', '96', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('471', '96', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('472', '97', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('473', '97', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('474', '97', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('475', '98', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('476', '98', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('477', '98', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('478', '99', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('479', '99', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('480', '99', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('481', '100', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('482', '100', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('483', '100', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('484', '101', 'Qual é a importância central de estudar Fluxo de Controle Avançado?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('485', '101', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('486', '101', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('487', '102', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('488', '102', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('489', '102', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('490', '103', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('491', '103', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('492', '103', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('493', '104', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('494', '104', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('495', '104', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('496', '105', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('497', '105', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('498', '105', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('499', '106', 'Qual é a importância central de estudar Tratamento Básico de Erros?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('500', '106', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('501', '106', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('502', '107', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('503', '107', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('504', '107', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('505', '108', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('506', '108', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('507', '108', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('508', '109', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('509', '109', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('510', '109', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('511', '110', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('512', '110', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('513', '110', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('514', '111', 'Qual é a importância central de estudar Operações Avançadas com Strings?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('515', '111', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('516', '111', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('517', '112', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('518', '112', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('519', '112', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('520', '113', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('521', '113', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('522', '113', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('523', '114', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('524', '114', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('525', '114', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('526', '115', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('527', '115', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('528', '115', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('529', '116', 'Qual é a importância central de estudar Projeto Contador de Palavras Únicas?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('530', '116', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('531', '116', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('532', '117', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('533', '117', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('534', '117', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('535', '118', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('536', '118', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('537', '118', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('538', '119', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('539', '119', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('540', '119', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('541', '120', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('542', '120', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('543', '120', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('544', '121', 'Qual é a importância central de estudar Data e Hora?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('545', '121', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('546', '121', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('547', '122', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('548', '122', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('549', '122', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('550', '123', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('551', '123', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('552', '123', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('553', '124', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('554', '124', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('555', '124', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('556', '125', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('557', '125', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('558', '125', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('559', '126', 'Qual é a importância central da avaliação intermediária?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('560', '126', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('561', '126', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('562', '127', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('563', '127', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('564', '127', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('565', '128', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('566', '128', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('567', '128', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('568', '129', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('569', '129', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('570', '129', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('571', '130', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('572', '130', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('573', '130', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('574', '131', 'Qual é a importância central de estudar Fundamentos de POO?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('575', '131', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('576', '131', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('577', '132', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('578', '132', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('579', '132', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('580', '133', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('581', '133', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('582', '133', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('583', '134', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('584', '134', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('585', '134', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('586', '135', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('587', '135', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('588', '135', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('589', '136', 'Qual é a importância central de estudar Modificadores de Acesso e Encapsulamento?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('590', '136', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('591', '136', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('592', '137', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('593', '137', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('594', '137', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('595', '138', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('596', '138', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('597', '138', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('598', '139', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('599', '139', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('600', '139', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('601', '140', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('602', '140', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('603', '140', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('604', '141', 'Qual é a importância central de estudar Prioridades de Classe e Membros Estáticos?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('605', '141', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('606', '141', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('607', '142', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('608', '142', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('609', '142', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('610', '143', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('611', '143', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('612', '143', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('613', '144', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('614', '144', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('615', '144', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('616', '145', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('617', '145', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('618', '145', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('619', '146', 'Qual é a importância central de estudar Herança?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('620', '146', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('621', '146', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('622', '147', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('623', '147', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('624', '147', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('625', '148', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('626', '148', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('627', '148', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('628', '149', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('629', '149', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('630', '149', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('631', '150', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('632', '150', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('633', '150', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('634', '151', 'Qual é a importância central de estudar Polimorfismo?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('635', '151', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('636', '151', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('637', '152', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('638', '152', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('639', '152', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('640', '153', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('641', '153', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('642', '153', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('643', '154', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('644', '154', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('645', '154', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('646', '155', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('647', '155', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('648', '155', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('649', '156', 'Qual é a importância central de estudar Interfaces e Classes Abstratas?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('650', '156', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('651', '156', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('652', '157', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('653', '157', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('654', '157', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('655', '158', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('656', '158', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('657', '158', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('658', '159', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('659', '159', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('660', '159', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('661', '160', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('662', '160', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('663', '160', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('664', '161', 'Qual é a importância central de estudar Métodos Especiais e Classe Object?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('665', '161', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('666', '161', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('667', '162', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('668', '162', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('669', '162', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('670', '163', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('671', '163', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('672', '163', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('673', '164', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('674', '164', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('675', '164', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('676', '165', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('677', '165', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('678', '165', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('679', '166', 'Qual é a importância central de estudar Conceitos Avançados de OOP (Generics)?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('680', '166', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('681', '166', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('682', '167', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('683', '167', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('684', '167', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('685', '168', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('686', '168', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('687', '168', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('688', '169', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('689', '169', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('690', '169', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('691', '170', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('692', '170', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('693', '170', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('694', '171', 'Qual é a importância central de estudar Tratamento de Exceção em POO?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('695', '171', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('696', '171', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('697', '172', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('698', '172', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('699', '172', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('700', '173', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('701', '173', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('702', '173', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('703', '174', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('704', '174', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('705', '174', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('706', '175', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('707', '175', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('708', '175', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('709', '176', 'Qual é a importância central de estudar Padrões de Projeto Parte 1?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('710', '176', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('711', '176', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('712', '177', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('713', '177', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('714', '177', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('715', '178', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('716', '178', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('717', '178', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('718', '179', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('719', '179', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('720', '179', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('721', '180', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('722', '180', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('723', '180', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('724', '181', 'Qual é a importância central de estudar Padrões de Projeto Parte 2?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('725', '181', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('726', '181', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('727', '182', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('728', '182', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('729', '182', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('730', '183', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('731', '183', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('732', '183', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('733', '184', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('734', '184', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('735', '184', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('736', '185', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('737', '185', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('738', '185', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25');
INSERT INTO `perguntas` (`id`, `licao_id`, `pergunta_texto`, `codigo_contexto`, `tipo`, `alternativa_a`, `alternativa_b`, `alternativa_c`, `alternativa_correta`, `criado_em`) VALUES
('739', '186', 'Qual é a importância central de estudar Projeto Gerenciamento de Biblioteca?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('740', '186', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('741', '186', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('742', '187', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('743', '187', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('744', '187', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('745', '188', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('746', '188', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('747', '188', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('748', '189', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('749', '189', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('750', '189', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('751', '190', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('752', '190', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('753', '190', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('754', '191', 'Qual é a importância central de estudar Desafio Final?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('755', '191', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('756', '191', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('757', '192', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('758', '192', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('759', '192', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('760', '193', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('761', '193', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('762', '193', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('763', '194', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('764', '194', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('765', '194', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('766', '195', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('767', '195', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('768', '195', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25'),
('769', '196', 'Qual é a importância central da conclusão do curso?', NULL, 'multipla_escolha', 'Garantir organização, boas práticas e solução eficiente de problemas no software', 'Apenas reduzir o tamanho do arquivo compilado', 'Não possui impacto na qualidade do código', 'A', '2026-10-16 14:11:25'),
('770', '196', 'Em Java, onde os métodos e instruções de execução devem estar contidos?', NULL, 'multipla_escolha', 'Dentro do escopo de uma classe', 'Fora de qualquer arquivo', 'Apenas no cabeçalho do sistema operacional', 'A', '2026-10-16 14:11:25'),
('771', '196', 'Qual é o método principal que serve de ponto de entrada para execução de um programa Java?', NULL, 'multipla_escolha', 'public static void main(String[] args)', 'start()', 'init()', 'A', '2026-10-16 14:11:25'),
('772', '197', 'O que significa dizer que Java possui checagem de tipos estática?', NULL, 'multipla_escolha', 'Que os tipos das variáveis são validados e verificados durante a compilação', 'Que as variáveis mudam de tipo automaticamente em tempo de execução', 'Que todos os dados são tratados como texto', 'A', '2026-10-16 14:11:25'),
('773', '197', 'Qual palavra-chave impede que o valor de uma variável seja alterado após inicializado?', NULL, 'multipla_escolha', 'final', 'static', 'const', 'A', '2026-10-16 14:11:25'),
('774', '197', 'Qual é a extensão dos arquivos de código-fonte em Java?', NULL, 'multipla_escolha', '.java', '.class', '.jar', 'A', '2026-10-16 14:11:25'),
('775', '198', 'Ao implementar uma regra de negócio, qual é a principal vantagem de modularizar o código?', NULL, 'multipla_escolha', 'Facilitar a leitura, manutenção e reutilização em diferentes partes do sistema', 'Aumentar propositalmente o número de linhas', 'Impedir que outros programadores leiam a lógica', 'A', '2026-10-16 14:11:25'),
('776', '198', 'Qual valor uma variável do tipo boolean pode assumir?', NULL, 'multipla_escolha', 'Apenas true ou false', 'Qualquer número positivo', 'Texto entre aspas simples', 'A', '2026-10-16 14:11:25'),
('777', '198', 'Como concatenamos dois valores em uma mensagem de texto em Java?', NULL, 'multipla_escolha', 'Utilizando o operador de adição (+)', 'Utilizando o operador (&)', 'Utilizando dois pontos (:)', 'A', '2026-10-16 14:11:25'),
('778', '199', 'De acordo com as convenções oficiais de nomenclatura em Java, como nomes de classes devem ser escritos?', NULL, 'multipla_escolha', 'PascalCase (ex: MinhaClassePrincipal)', 'camelCase iniciando com minúscula', 'snake_case com sublinhados', 'A', '2026-10-16 14:11:25'),
('779', '199', 'Por que devemos evitar duplicar blocos de código idênticos em várias partes do programa?', NULL, 'multipla_escolha', 'Porque dificulta a manutenção e correções futuras', 'Porque o compilador Java recusa arquivos com mais de 50 linhas', 'Porque consome toda a memória do computador', 'A', '2026-10-16 14:11:25'),
('780', '199', 'Qual é o papel dos comentários no código-fonte?', NULL, 'multipla_escolha', 'Explicar a intenção e decisões não óbvias aos programadores que darão manutenção', 'Serem impressos automaticamente no terminal do usuário', 'Acelerar o processamento na JVM', 'A', '2026-10-16 14:11:25'),
('781', '200', 'Qual comando finaliza a execução bem-sucedida da lição prática?', NULL, 'multipla_escolha', 'Compilar sem erros e produzir a saída esperada no console', 'Apenas abrir o terminal', 'Digitar qualquer comando sem compilar', 'A', '2026-10-16 14:11:25'),
('782', '200', 'Se o compilador acusar erro de sintaxe, o que devemos fazer?', NULL, 'multipla_escolha', 'Ler a mensagem de erro, localizar a linha indicada e corrigir a instrução', 'Ignorar o erro e continuar', 'Apagar todo o projeto', 'A', '2026-10-16 14:11:25'),
('783', '200', 'Qual das alternativas abaixo representa uma chamada válida de impressão no console?', NULL, 'multipla_escolha', 'System.out.println(\"Texto\");', 'print(\"Texto\");', 'Console.Write(\"Texto\");', 'A', '2026-10-16 14:11:25');

-- Dados da tabela: `desafios_projetos` (10 registros)
INSERT INTO `desafios_projetos` (`id`, `slug`, `titulo`, `subtitulo`, `descricao`, `secao_requerida`, `requisitos_titulo`, `requisitos_desc`, `categoria`, `dificuldade`, `xp_recompensa`, `icone`, `cor`, `arquivo_nome`, `codigo_inicial`, `missao_detalhada`, `exemplo_saida`, `dica`, `validador_tipo`, `validador_regra`, `ordem`) VALUES
('1', 'cracha-desenvolvedor', 'Cartão de Visita do Desenvolvedor', 'Construa um crachá de apresentação no terminal com seus dados de dev.', 'Coloque em prática seus primeiros comandos Java estruturando um cartão de visitas no console utilizando classes, método main e impressões bem diagramadas.', '1', 'Seção 1: Introdução a Java', 'Estrutura básica de classe Java, método main e exibição de mensagens com System.out.println.', 'Fundamentos', 'iniciante', '50', 'fa-id-card', '#1cb0f6', 'CrachaDev.java', 'public class CrachaDev {\n    public static void main(String[] args) {\n        // === MEU CRACHÁ DE DESENVOLVEDOR ===\n        // Escreva instruções System.out.println para exibir:\n        // 1. Uma borda ou divisória\n        // 2. Seu nome de desenvolvedor\n        // 3. A linguagem \"Java\"\n        // 4. Sua mensagem de status no OPUS\n        \n    }\n}', 'Sua missão é exibir um crachá no terminal contendo:\n1. Uma linha divisória;\n2. Seu nome ou apelido como desenvolvedor;\n3. A palavra \"Java\" destacando sua linguagem de estudo;\n4. Uma mensagem de status final.\n\nVocê pode personalizar as cores das bordas com caracteres como =, * ou -.', '============================\nDESENVOLVEDOR: Gabriel Freitas\nLINGUAGEM: Java\nSTATUS: Codificando no OPUS!\n============================', 'Lembre-se de fechar todas as strings com aspas duplas \"...\" e terminar cada comando System.out.println(...) com ponto e vírgula (;).', 'saida_contem', 'java', '1'),
('2', 'ficha-personagem-rpg', 'Ficha de Personagem RPG', 'Guarde atributos de um guerreiro em variáveis inteiras e texto para exibir seus atributos.', 'Crie as estatísticas de um aventureiro guardando nome, nível, pontos de vida e força em variáveis dos tipos int e String.', '2', 'Seção 2: Variáveis Parte 1', 'Declaração de variáveis int e String, atribuição de valores e concatenação com o operador +.', 'Variáveis & Tipos', 'facil', '50', 'fa-shield-halved', '#ff4b4b', 'FichaRPG.java', 'public class FichaRPG {\n    public static void main(String[] args) {\n        // 1. Declare as variáveis heroi (String), nivel (int), forca (int) e vida (int):\n        \n        // 2. Exiba a ficha concatenando os dados com System.out.println:\n        \n    }\n}', 'Crie um programa que declare 4 variáveis:\n• heroi (String) com o nome do personagem;\n• nivel (int) com o nível;\n• forca (int) com o valor de força;\n• vida (int) com o HP do personagem.\nEm seguida, concatene e imprima os valores no console!', '--- FICHA DO HERÓI ---\nNome: Arthur\nNível: 5\nForça: 18\nPontos de Vida: 100', 'Para juntar texto e números em Java, use o operador de concatenação +: System.out.println(\"Nível: \" + nivel);', 'saida_contem', 'heroi,nivel,vida', '2'),
('3', 'conversor-temperatura', 'Conversor de Temperatura & Moeda', 'Utilize tipos decimais (double) para converter Celsius em Fahrenheit e Dólar em Real.', 'Aprenda a lidar com números decimais e precisão monetária e climática utilizando variáveis do tipo double e fórmulas matemáticas.', '3', 'Seção 3: Variáveis Parte 2', 'Variáveis decimais (double), números com ponto flutuante e cálculos de conversão.', 'Variáveis & Tipos', 'facil', '60', 'fa-temperature-half', '#ce82ff', 'Conversor.java', 'public class Conversor {\n    public static void main(String[] args) {\n        // 1. Converta 25 graus Celsius para Fahrenheit: F = (C * 1.8) + 32\n        double celsius = 25.0;\n        // Calcule fahrenheit:\n        \n        // 2. Converta 15 dólares para reais com a cotação a 5.20:\n        double dolares = 15.0;\n        double cotacao = 5.20;\n        // Calcule reais:\n        \n        // 3. Imprima os resultados no console:\n        \n    }\n}', 'Implemente o conversor aplicando as duas regras de negócio:\n1. Calcule a temperatura Fahrenheit a partir de 25 graus Celsius: (celsius * 1.8) + 32;\n2. Calcule a conversão de 15 dólares para reais com a cotação a 5.20.\nImprima os dois resultados!', 'Temperatura em Fahrenheit: 77.0\nTotal em Reais: R$ 78.0', 'Lembre-se de usar ponto (.) em vez de vírgula (,) para valores decimais em Java (ex: 1.8 e 5.20).', 'saida_contem', '77,78', '3'),
('4', 'divisor-conta-restaurante', 'Divisor de Conta & Gorjeta', 'Faça o cálculo aritmético completo: some o consumo, adicione os 10% do garçom e divida igualmente.', 'Construa uma aplicação de caixa para restaurantes que calcula o total do pedido, a taxa de serviço e quanto cada cliente da mesa deve pagar.', '4', 'Seção 4: Operadores Parte 1', 'Operadores aritméticos (+, -, *, /) e cálculo percentual de gorjeta.', 'Matemática & Contas', 'facil', '65', 'fa-receipt', '#58cc02', 'ContaRestaurante.java', 'public class ContaRestaurante {\n    public static void main(String[] args) {\n        double consumo = 180.0;\n        int amigos = 4;\n\n        // 1. Calcule a taxa de serviço de 10% (consumo * 0.10):\n        \n        // 2. Calcule o total final somando consumo e taxa:\n        \n        // 3. Calcule o valor por pessoa dividindo o total pelo número de amigos:\n        \n        // 4. Exiba os valores no terminal:\n        \n    }\n}', 'Calcule o rateio de uma mesa:\n• Consumo de R$ 180.0 dividido entre 4 amigos;\n• Taxa de 10% (R$ 18.0);\n• Total com taxa: R$ 198.0;\n• Valor individual: R$ 49.50.', 'Consumo: R$ 180.0\nTaxa (10%): R$ 18.0\nTotal Final: R$ 198.0\nValor por pessoa (4 pessoas): R$ 49.5', 'Para calcular 10% de um valor, basta multiplicá-lo por 0.10: double taxa = valor * 0.10;', 'saida_contem', '198,49.5', '4'),
('5', 'calculadora-basica', 'Calculadora Aritmética & Lógica', 'Construa uma calculadora que executa as 4 operações fundamentais e realiza comparações booleanas!', 'Um dos projetos mais clássicos e importantes da programação! Aplique as operações matemáticas (+, -, *, /) e faça testes lógicos para verificar se os números são iguais, positivos ou maiores que o outro.', '5', 'Seção 5: Operadores Parte 2', 'Requer conclusão da Seção 5: operadores aritméticos, relacionais (==, !=, >, <) e lógicos (&&, ||).', 'Matemática & Lógica', 'medio', '75', 'fa-calculator', '#ffc800', 'Calculadora.java', 'public class Calculadora {\n    public static void main(String[] args) {\n        int num1 = 20;\n        int num2 = 4;\n\n        // 1. Operações Aritméticas (calcule soma, subtração, multiplicação e divisão de num1 e num2):\n        \n        // 2. Operações Relacionais e Lógicas:\n        // - Verifique se num1 e num2 são iguais (==)\n        // - Verifique se num1 é maior que num2 (>)\n        // - Verifique se ambos são positivos (> 0 && > 0)\n        \n        // 3. Exiba os resultados no console usando System.out.println:\n        \n    }\n}', 'Construa a Calculadora Básica executando os seguintes passos:\n1. Declare dois números inteiros (ex: num1 = 20 e num2 = 4);\n2. Calcule e imprima: Soma (24), Subtração (16), Multiplicação (80) e Divisão (5);\n3. Faça as verificações lógicas usando operadores relacionais e lógicos: verifique se são iguais (==), se o primeiro é maior (>) e se ambos são positivos (> 0 && > 0).\n4. Exiba cada resultado no terminal com mensagens descritivas!', '=== CALCULADORA OPUS ===\nSoma: 24\nSubtracao: 16\nMultiplicacao: 80\nDivisao: 5\nSao iguais? false\nPrimeiro e maior? true\nAmbos positivos? true', 'Em Java, a comparação de igualdade usa dois sinais de igual (==), enquanto um único sinal (=) é usado para atribuição de variáveis.', 'saida_contem', 'soma,subtracao,multiplicacao,divisao', '5'),
('6', 'radar-velocidade', 'Simulador de Radar & Multas', 'Crie um sistema com if / else-if / else para fiscalizar velocidades e aplicar avisos ou multas.', 'Simule o funcionamento de um radar eletrônico de trânsito aplicando regras condicionais: dentro do limite, advertência leve ou infração grave.', '6', 'Seção 6: Tomada de Decisão', 'Controle de fluxo condicional com if, else-if e else.', 'Controle de Fluxo', 'medio', '80', 'fa-gauge-high', '#1cb0f6', 'RadarTransito.java', 'public class RadarTransito {\n    public static void main(String[] args) {\n        int limite = 80;\n        int velocidade = 95;\n\n        // Crie a estrutura if / else-if / else para avaliar a velocidade:\n        // - Se velocidade <= limite: exiba \"Velocidade Permitida\"\n        // - Se velocidade <= limite + 20: exiba \"Infracao Media\"\n        // - Se ultrapassar: exiba \"Infracao Grave\"\n        \n    }\n}', 'Implemente o fiscalizador de velocidade:\n• Se velocidade <= limite: exiba \"Velocidade Permitida\";\n• Se velocidade <= limite + 20: exiba \"Infracao Media\";\n• Acima disso: exiba \"Infracao Grave\".', 'Velocidade aferida: 95 km/h\nLimite da via: 80 km/h\nStatus: Infracao Media! Excedeu em ate 20 km/h.', 'Lembre-se de encadear as condições na ordem lógica correta com if (...) else if (...) else {...}.', 'saida_contem', 'infracao media,velocidade', '6'),
('7', 'caixa-registradora-printf', 'Caixa Registradora & Recibo Formatado', 'Formate recibos de venda profissionais com System.out.printf e cálculos com especificadores %s e %.2f.', 'Aprenda a estruturar saídas organizadas em colunas e com duas casas decimais como em sistemas comerciais e cupons fiscais.', '7', 'Seção 7: I/O Básico', 'Formatação de texto e decimais usando System.out.printf (%s, %d, %.2f, %n).', 'Entrada & Saída', 'medio', '80', 'fa-cash-register', '#ff4b4b', 'ReciboCompra.java', 'public class ReciboCompra {\n    public static void main(String[] args) {\n        String item1 = \"Teclado Mecanico\";\n        double preco1 = 250.50;\n        String item2 = \"Mouse Gamer\";\n        double preco2 = 120.00;\n\n        // 1. Calcule o valor total somando preco1 e preco2:\n        \n        // 2. Use System.out.printf para exibir o cupom fiscal formatado com %.2f:\n        \n    }\n}', 'Gere um cupom fiscal com alinhamento tabular utilizando printf e especificadores %.2f para os preços e %s para a descrição dos itens.', '========================================\n           CUPOM FISCAL OPUS            \n========================================\nTeclado Mecanico     R$   250.50\nMouse Gamer          R$   120.00\n----------------------------------------\nTOTAL:               R$   370.50\n========================================', 'O marcador %.2f exibe um número double com duas casas decimais. %n ou \\n pula para a próxima linha.', 'saida_contem', 'cupom fiscal,370.5', '7'),
('8', 'calculadora-geometrica-math', 'Calculadora Geométrica & Math', 'Explore Math.pow, Math.sqrt e constantes para calcular áreas, hipotenusas e potências.', 'Use a biblioteca nativa java.lang.Math para calcular o Teorema de Pitágoras (hipotenusa) e a área de um círculo com Math.PI.', '8', 'Seção 8: Aplicativo de Calculadora / Math', 'Uso de métodos estáticos da classe Math (pow, sqrt, PI).', 'Matemática Avançada', 'medio', '85', 'fa-shapes', '#ce82ff', 'CalculadoraMath.java', 'public class CalculadoraMath {\n    public static void main(String[] args) {\n        double catetoA = 3.0;\n        double catetoB = 4.0;\n\n        // 1. Calcule a hipotenusa usando Math.sqrt e Math.pow (catetoA² + catetoB²):\n        \n        // 2. Calcule a raiz quadrada de 144 com Math.sqrt:\n        \n        // 3. Calcule 2 elevado à potência 8 com Math.pow:\n        \n        // 4. Exiba os três resultados no console:\n        \n    }\n}', 'Calcule e imprima:\n• Hipotenusa do triângulo retângulo com catetos 3 e 4 (resultado: 5.0);\n• Raiz quadrada de 144 (resultado: 12);\n• 2 elevado à potência 8 (resultado: 256).', '=== CALCULADORA MATH ===\nHipotenusa de 3 e 4: 5.0\nRaiz de 144: 12\n2 elevado a 8: 256', 'Math.sqrt(x) calcula a raiz quadrada de x e Math.pow(base, exp) calcula a base elevada ao expoente.', 'saida_contem', '5,12,256', '8'),
('9', 'gerador-tabuada-lacos', 'Gerador de Tabuada & Sequências', 'Utilize laços de repetição (for e while) para gerar tabuadas completas e sequências numéricas.', 'A repetição é a alma da automação na programação! Crie um programa que gera a tabuada de um número do 1 ao 10 com laço for e uma contagem regressiva.', '9', 'Seção 9: Laços', 'Estruturas de repetição for, while e variáveis acumuladoras.', 'Repetição & Laços', 'medio', '90', 'fa-repeat', '#58cc02', 'Tabuada.java', 'public class Tabuada {\n    public static void main(String[] args) {\n        int numero = 7;\n\n        // 1. Crie um laço for de 1 até 10 para exibir a tabuada do número:\n        \n        // 2. Crie uma contagem regressiva com laço while de 3 até 1, e ao final exiba \"Decolar!\":\n        \n    }\n}', 'Gere a tabuada completa do 7 de 1 a 10 usando um laço for, seguida de uma contagem regressiva 3, 2, 1 com while.', '=== TABUADA DO 7 ===\n7 x 1 = 7\n7 x 2 = 14\n...\n7 x 10 = 70\n--- CONTAGEM REGRESSIVA ---\n3...\n2...\n1...\nDecolar!', 'A estrutura do for é: for (inicialização; condição; incremento) { ... }', 'saida_contem', 'tabuada,70,decolar', '9'),
('10', 'modulo-funcoes-matematicas', 'Módulo de Funções Matemáticas', 'Organize seu código criando métodos estáticos modulares com parâmetros e retorno de valores.', 'Aprenda a modularizar seu código Java dividindo tarefas em funções e métodos com parâmetros e retornos claros.', '10', 'Seção 10: Métodos e Funções', 'Declaração de métodos estáticos com modificador public static, parâmetros de entrada e cláusula return.', 'Modularização', 'desafiador', '100', 'fa-cubes', '#ffc800', 'ModuloMatematico.java', 'public class ModuloMatematico {\n    // 1. Declare o método estático somar(int a, int b) que retorna a soma:\n    \n    // 2. Declare o método estático multiplicar(int a, int b) que retorna a multiplicação:\n    \n    // 3. Declare o método estático ehPar(int n) que retorna true se for par:\n    \n    public static void main(String[] args) {\n        // Invoque os métodos criados e imprima os resultados no console:\n        \n    }\n}', 'Crie três métodos estáticos:\n1. somar(int a, int b): retorna a + b;\n2. multiplicar(int a, int b): retorna a * b;\n3. ehPar(int n): retorna true se n for par;\nNo método main, invoque os métodos e imprima os resultados!', 'Resultado da Soma: 50\nResultado da Multiplicacao: 42\nA soma e par? true', 'Declare os métodos fora do main, mas dentro da classe. Use public static tipoRetorno nome(parametros) { return ...; }', 'saida_contem', '50,42,true', '10');

-- Dados da tabela: `catalogo_trofeus` (12 registros)
INSERT INTO `catalogo_trofeus` (`slug`, `nome`, `descricao`, `listado`, `ordem`, `icone`, `imagem`, `cor`, `raridade`, `criado_em`) VALUES
('batalha_irmaos', 'Batalha de Irmãos', 'Venceu uma Batalha de Fogo contra um amigo!', '1', '99', 'fa-handshake-angle', 'trofeu_fogo.png', '#ff0055', 'lendario', '2026-10-08 18:47:15'),
('capitulo_1', 'Fundamentos', 'Terminou o Capítulo 1.', '1', '3', 'fa-award', 'trofeu_cap1.png', '#22c55e', 'comum', '2026-09-27 02:22:37'),
('capitulo_2', 'Caminhos Lógicos', 'Dominou as Estruturas de Decisão no Cap. 2.', '1', '4', 'fa-shield-halved', 'trofeu_cap2.png', '#3b82f6', 'raro', '2026-09-27 02:22:37'),
('capitulo_3', 'Mestre da Repetição', 'Dominou os Loops no Capítulo 3.', '1', '5', 'fa-repeat', 'trofeu_cap3.png', '#a855f7', 'epico', '2026-09-27 02:22:37'),
('capitulo_4', 'Senhor dos Arrays', 'Dominou Arrays e Matrizes no Capítulo 4.', '1', '6', 'fa-database', 'trofeu_cap4.png', '#ec4899', 'epico', '2026-09-27 02:22:37'),
('capitulo_5', 'Arquiteto Java', 'Concluiu POO no Capítulo 5. Você é o mestre!', '1', '7', 'fa-crown', 'trofeu_cap5.png', '#ffd700', 'lendario', '2026-09-27 02:22:37'),
('fogo_3', 'Em Chamas', 'Alcançou 3 Dias de Fogo.', '1', '8', 'fa-fire', 'trofeu_fogo.png', '#f97316', 'raro', '2026-09-27 02:22:37'),
('perfeicao', 'Mente Brilhante', 'Acertou 3/3 em um desafio.', '1', '2', 'fa-brain', 'trofeu_cerebro.png', '#1cb0f6', 'raro', '2026-09-27 02:22:37'),
('precisao_absoluta', 'Precisão Absoluta', 'Alcançou 150 XP acumulados.', '0', '15', 'fa-bullseye', 'trofeu_alvo.png', '#1cb0f6', 'raro', '2026-09-27 02:22:37'),
('primeiro_passo', 'Primeiro Passo', 'Concluiu sua primeira lição.', '1', '1', 'fa-star', 'medalha_estrela.png', '#ffd700', 'comum', '2026-09-27 02:22:37'),
('sequencia_30', 'Mês Épico', 'Alcançou 30 Dias de Fogo.', '0', '17', 'fa-fire-flame-simple', 'trofeu_fogo30.png', '#a855f7', 'lendario', '2026-09-27 02:22:37'),
('sequencia_7', 'Semana Perfeita', 'Alcançou 7 Dias de Fogo.', '0', '16', 'fa-fire-flame-curved', 'trofeu_fogo7.png', '#ef4444', 'epico', '2026-09-27 02:22:37');

-- Dados da tabela: `usuarios` (33 registros)
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `xp`, `trofeus`, `foto_perfil`, `criado_em`, `dias_fogo`, `vidas`, `vidas_proxima_em`, `ultima_atividade`, `nivel_acesso`, `cor_fundo`, `username`, `google_id`, `foto_google`, `link_github`, `link_instagram`, `link_facebook`, `link_youtube`, `link_email`, `link_linkedin`, `projeto_titulo_1`, `projeto_url_1`, `projeto_titulo_2`, `projeto_url_2`, `projeto_titulo_3`, `projeto_url_3`, `pais`, `pais_codigo`, `tour_visto`, `tour_perfil_visto`, `tour_licao1_visto`, `tour_resultado1_visto`) VALUES
('1', 'Administrador OPUS', 'admin@opus.com', '$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi', '0', '0', NULL, '2026-11-02 17:20:15', '0', '3', NULL, NULL, 'admin', '#58cc02', 'admin_opus', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('2', 'Gab Santos', 'gabsantosbag@gmail.com', '', '705', '6', '../assets/uploads/avatars/avatar_2_1793651107_8ac84287.jpg', '2026-11-02 17:21:06', '1', '3', NULL, '2026-11-19', 'comum', '#ff9600', NULL, '106201833585413825315', 'https://lh3.googleusercontent.com/a/ACg8ocLdaUy7rcG2C8yeI1163uwixoCqwFzMxpfd_sM6epC2w34oUmA=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Reino Unido', 'gb', '1', '1', '1', '1'),
('101', 'Helena Martins', 'helena.martins@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '12400', '11', NULL, '2026-10-23 17:24:21', '0', '3', NULL, '2026-11-02', 'comum', '#a855f7', 'helena_martins', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('102', 'Sophia Castro', 'sophia.castro@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '10850', '11', NULL, '2026-10-16 17:24:21', '41', '3', NULL, '2026-11-02', 'comum', '#6366f1', 'sophia_castro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('103', 'Vinícius Prado', 'vinicius.prado@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '9300', '10', NULL, '2026-10-09 17:24:21', '0', '3', NULL, '2026-11-02', 'comum', '#ec4899', 'vinicius_prado', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Portugal', 'pt', '0', '0', '0', '0'),
('104', 'Gabriel Nakamura', 'gabriel.nakamura@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '8750', '11', NULL, '2026-10-02 17:24:21', '33', '2', '2026-11-02 17:44:21', '2026-11-02', 'comum', '#14b8a6', 'gabriel_nakamura', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Japão', 'jp', '0', '0', '0', '0'),
('105', 'Isabella Rossi', 'isabella.rossi@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '7600', '10', NULL, '2026-09-25 17:24:21', '17', '3', NULL, '2026-11-02', 'comum', '#f43f5e', 'isabella_rossi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Itália', 'it', '0', '0', '0', '0'),
('106', 'Rafael Duarte', 'rafael.duarte@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '5200', '10', NULL, '2026-09-18 17:24:21', '21', '3', NULL, '2026-11-02', 'comum', '#06b6d4', 'rafael_duarte', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('107', 'Beatriz Almeida', 'beatriz.almeida@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '4650', '10', NULL, '2026-09-11 17:24:21', '14', '3', NULL, '2026-11-02', 'comum', '#3b82f6', 'beatriz_almeida', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('108', 'Thiago Ferreira', 'thiago.ferreira@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '4100', '10', NULL, '2026-09-04 17:24:21', '9', '2', '2026-11-02 17:44:21', '2026-11-02', 'comum', '#84cc16', 'thiago_ferreira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('109', 'Carlos Mendoza', 'carlos.mendoza@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '3800', '10', NULL, '2026-08-28 17:24:21', '12', '3', NULL, '2026-11-02', 'comum', '#0ea5e9', 'carlos_mendoza', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Argentina', 'ar', '0', '0', '0', '0'),
('110', 'Emma Johnson', 'emma.johnson@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '3400', '9', NULL, '2026-08-21 17:24:21', '6', '1', '2026-11-02 17:44:21', '2026-11-02', 'comum', '#8b5cf6', 'emma_johnson', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Estados Unidos', 'us', '0', '0', '0', '0'),
('111', 'Bruno Carvalho', 'bruno.carvalho@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '2650', '10', NULL, '2026-08-14 17:24:21', '0', '3', NULL, '2026-11-02', 'comum', '#d97706', 'bruno_carvalho', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('112', 'Mariana Costa', 'mariana.costa@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '2300', '10', NULL, '2026-08-07 17:24:21', '8', '3', NULL, '2026-11-02', 'comum', '#ff9600', 'mariana_costa', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('113', 'Larissa Souza', 'larissa.souza@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '1980', '10', NULL, '2026-07-31 17:24:21', '7', '3', NULL, '2026-11-02', 'comum', '#9333ea', 'larissa_souza', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('114', 'Rodrigo Lima', 'rodrigo.lima@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '1750', '9', NULL, '2026-07-24 17:24:21', '5', '1', '2026-11-02 17:44:21', '2026-11-02', 'comum', '#2563eb', 'rodrigo_lima', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('115', 'Diego Fernández', 'diego.fernandez@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '1500', '9', NULL, '2026-07-17 17:24:21', '4', '3', NULL, '2026-11-02', 'comum', '#16a34a', 'diego_fernandez', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', 'mx', '0', '0', '0', '0'),
('116', 'Fernanda Alves', 'fernanda.alves@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '1320', '8', NULL, '2026-07-10 17:24:21', '3', '2', '2026-11-02 17:44:21', '2026-11-02', 'comum', '#e11d48', 'fernanda_alves', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('117', 'Felipe Oliveira', 'felipe.oliveira@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '980', '6', NULL, '2026-07-03 17:24:21', '6', '3', NULL, '2026-11-02', 'comum', '#00cd9c', 'felipe_oliveira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('118', 'Lucas Mendes', 'lucas.mendes@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '820', '6', NULL, '2026-06-26 17:24:21', '4', '2', '2026-11-02 17:44:21', '2026-11-02', 'comum', '#ce82ff', 'lucas_mendes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('119', 'Camila Ribeiro', 'camila.ribeiro@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '640', '5', NULL, '2026-06-19 17:24:21', '3', '3', NULL, '2026-11-02', 'comum', '#e11d48', 'camila_ribeiro', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('120', 'João Pereira', 'joao.pereira@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '520', '4', NULL, '2026-06-12 17:24:21', '2', '3', NULL, '2026-11-02', 'comum', '#f59e0b', 'joao_pereira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('121', 'Amélie Dubois', 'amelie.dubois@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '430', '3', NULL, '2026-06-05 17:24:21', '0', '3', NULL, NULL, 'comum', '#1cb0f6', 'amelie_dubois', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'França', 'fr', '0', '0', '0', '0'),
('122', 'Ana Silva', 'ana.silva@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '280', '3', NULL, '2026-05-29 17:24:21', '2', '3', NULL, '2026-11-02', 'comum', '#ff4b4b', 'ana_silva', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('123', 'Pedro Rocha', 'pedro.rocha@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '160', '2', NULL, '2026-05-22 17:24:21', '0', '3', NULL, '2026-11-02', 'comum', '#1cb0f6', 'pedro_rocha', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('124', 'Júlia Santos', 'julia.santos@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '95', '1', NULL, '2026-05-15 17:24:21', '0', '0', '2026-11-02 17:44:21', NULL, 'comum', '#ff9600', 'julia_santos', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('125', 'Matheus Oliveira', 'matheus.oliveira@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '60', '1', NULL, '2026-05-08 17:24:21', '0', '3', NULL, NULL, 'comum', '#58cc02', 'matheus_oliveira', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('126', 'Letícia Gomes', 'leticia.gomes@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '30', '1', NULL, '2026-05-01 17:24:21', '0', '2', '2026-11-02 17:44:21', NULL, 'comum', '#ce82ff', 'leticia_gomes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('127', 'Novo Aluno', 'novo.aluno@opus-teste.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', '0', '0', NULL, '2026-04-24 17:24:21', '0', '3', NULL, NULL, 'comum', '#1cb0f6', 'novo_aluno', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('128', 'Gabi Freitas', 'gabrielefr3itas@gmail.com', '', '0', '0', NULL, '2026-11-05 17:39:09', '0', '3', NULL, NULL, 'comum', '#1cb0f6', NULL, '104488639218720630991', 'https://lh3.googleusercontent.com/a/ACg8ocLFS12oG26YBx4b8bzF8ApUR4YsFynj20RfggXc6xKwADx9IxY1=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('129', 'GABRIELE DOS SANTOS DE FREITAS', '4542039519@estudante.sed.sc.gov.br', '', '50', '2', NULL, '2026-11-14 13:23:20', '1', '3', NULL, '2026-11-14', 'comum', '#1cb0f6', NULL, '115570340187171235530', 'https://lh3.googleusercontent.com/a/ACg8ocLd8dgzzuv12ZP4NefkzF180u1cfOker2ofnsGT48FE9wsvvoFF=s96-c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('130', 'Gab Santos', 'kk@opus.com', 'Opus@2026Master!', '40', '1', NULL, '2026-11-14 15:46:11', '1', '3', NULL, '2026-11-14', 'comum', '#1cb0f6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0'),
('131', 'Admin OPUS', 'adm@opus.com', '$2y$10$mYUbc9bvSPXw1Fg4oVtK1.tFsDVG9.LCnbXbsS3pJp98zVV2TxDI.', '0', '0', NULL, '2026-11-19 17:55:14', '0', '3', NULL, NULL, 'admin', '#1cb0f6', 'adm_opus', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Brasil', 'br', '0', '0', '0', '0');

-- Dados da tabela: `progresso_usuario` (304 registros)
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('1', '1', '1', 'corrente', '0', '2026-11-02 17:20:15'),
('2', '2', '1', 'completo', '5', '2026-11-14 12:30:41'),
('3', '2', '2', 'completo', '5', '2026-11-16 20:13:03'),
('4', '2', '3', 'completo', '5', '2026-11-19 13:27:22'),
('5', '2', '4', 'corrente', '0', '2026-11-19 13:27:22'),
('6', '2', '5', 'trancado', '0', '2026-11-02 17:21:06'),
('7', '101', '1', 'completo', '5', '2026-11-02 17:24:21'),
('8', '101', '2', 'completo', '5', '2026-11-02 17:24:21'),
('9', '101', '3', 'completo', '5', '2026-11-02 17:24:21'),
('10', '101', '4', 'completo', '5', '2026-11-02 17:24:21'),
('11', '101', '5', 'completo', '5', '2026-11-02 17:24:21'),
('12', '101', '6', 'completo', '5', '2026-11-02 17:24:21'),
('13', '101', '7', 'completo', '5', '2026-11-02 17:24:21'),
('14', '101', '8', 'completo', '5', '2026-11-02 17:24:21'),
('15', '101', '9', 'completo', '5', '2026-11-02 17:24:21'),
('16', '101', '10', 'completo', '5', '2026-11-02 17:24:21'),
('17', '101', '11', 'completo', '5', '2026-11-02 17:24:21'),
('18', '101', '12', 'completo', '5', '2026-11-02 17:24:21'),
('19', '101', '13', 'completo', '5', '2026-11-02 17:24:21'),
('20', '101', '14', 'completo', '5', '2026-11-02 17:24:21'),
('21', '101', '15', 'completo', '5', '2026-11-02 17:24:21'),
('22', '101', '16', 'completo', '5', '2026-11-02 17:24:21'),
('23', '101', '17', 'completo', '5', '2026-11-02 17:24:21'),
('24', '101', '18', 'completo', '5', '2026-11-02 17:24:21'),
('25', '101', '19', 'completo', '5', '2026-11-02 17:24:21'),
('26', '101', '20', 'completo', '5', '2026-11-02 17:24:21'),
('27', '101', '21', 'completo', '5', '2026-11-02 17:24:21'),
('28', '101', '22', 'completo', '5', '2026-11-02 17:24:21'),
('29', '101', '23', 'completo', '5', '2026-11-02 17:24:21'),
('30', '101', '24', 'completo', '5', '2026-11-02 17:24:21'),
('31', '101', '25', 'completo', '5', '2026-11-02 17:24:21'),
('32', '101', '26', 'completo', '5', '2026-11-02 17:24:21'),
('33', '101', '27', 'completo', '5', '2026-11-02 17:24:21'),
('34', '101', '28', 'completo', '5', '2026-11-02 17:24:21'),
('35', '101', '29', 'completo', '5', '2026-11-02 17:24:21'),
('36', '101', '30', 'completo', '5', '2026-11-02 17:24:21'),
('37', '101', '31', 'completo', '5', '2026-11-02 17:24:21'),
('38', '101', '32', 'completo', '5', '2026-11-02 17:24:21'),
('39', '101', '33', 'completo', '5', '2026-11-02 17:24:21'),
('40', '101', '34', 'completo', '5', '2026-11-02 17:24:21'),
('41', '101', '35', 'completo', '5', '2026-11-02 17:24:21'),
('42', '101', '36', 'completo', '5', '2026-11-02 17:24:21'),
('43', '101', '37', 'completo', '5', '2026-11-02 17:24:21'),
('44', '101', '38', 'completo', '5', '2026-11-02 17:24:21'),
('45', '101', '39', 'corrente', '3', '2026-11-02 17:24:21'),
('46', '102', '1', 'completo', '5', '2026-11-02 17:24:21'),
('47', '102', '2', 'completo', '5', '2026-11-02 17:24:21'),
('48', '102', '3', 'completo', '5', '2026-11-02 17:24:21'),
('49', '102', '4', 'completo', '5', '2026-11-02 17:24:21'),
('50', '102', '5', 'completo', '5', '2026-11-02 17:24:21');
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('51', '102', '6', 'completo', '5', '2026-11-02 17:24:21'),
('52', '102', '7', 'completo', '5', '2026-11-02 17:24:21'),
('53', '102', '8', 'completo', '5', '2026-11-02 17:24:21'),
('54', '102', '9', 'completo', '5', '2026-11-02 17:24:21'),
('55', '102', '10', 'completo', '5', '2026-11-02 17:24:21'),
('56', '102', '11', 'completo', '5', '2026-11-02 17:24:21'),
('57', '102', '12', 'completo', '5', '2026-11-02 17:24:21'),
('58', '102', '13', 'completo', '5', '2026-11-02 17:24:21'),
('59', '102', '14', 'completo', '5', '2026-11-02 17:24:21'),
('60', '102', '15', 'completo', '5', '2026-11-02 17:24:21'),
('61', '102', '16', 'completo', '5', '2026-11-02 17:24:21'),
('62', '102', '17', 'completo', '5', '2026-11-02 17:24:21'),
('63', '102', '18', 'completo', '5', '2026-11-02 17:24:21'),
('64', '102', '19', 'completo', '5', '2026-11-02 17:24:21'),
('65', '102', '20', 'completo', '5', '2026-11-02 17:24:21'),
('66', '102', '21', 'completo', '5', '2026-11-02 17:24:21'),
('67', '102', '22', 'completo', '5', '2026-11-02 17:24:21'),
('68', '102', '23', 'completo', '5', '2026-11-02 17:24:21'),
('69', '102', '24', 'completo', '5', '2026-11-02 17:24:21'),
('70', '102', '25', 'completo', '5', '2026-11-02 17:24:21'),
('71', '102', '26', 'completo', '5', '2026-11-02 17:24:21'),
('72', '102', '27', 'completo', '5', '2026-11-02 17:24:21'),
('73', '102', '28', 'completo', '5', '2026-11-02 17:24:21'),
('74', '102', '29', 'completo', '5', '2026-11-02 17:24:21'),
('75', '102', '30', 'completo', '5', '2026-11-02 17:24:21'),
('76', '102', '31', 'completo', '5', '2026-11-02 17:24:21'),
('77', '102', '32', 'completo', '5', '2026-11-02 17:24:21'),
('78', '102', '33', 'completo', '5', '2026-11-02 17:24:21'),
('79', '102', '34', 'corrente', '4', '2026-11-02 17:24:21'),
('80', '103', '1', 'completo', '5', '2026-11-02 17:24:21'),
('81', '103', '2', 'completo', '5', '2026-11-02 17:24:21'),
('82', '103', '3', 'completo', '5', '2026-11-02 17:24:21'),
('83', '103', '4', 'completo', '5', '2026-11-02 17:24:21'),
('84', '103', '5', 'completo', '5', '2026-11-02 17:24:21'),
('85', '103', '6', 'completo', '5', '2026-11-02 17:24:21'),
('86', '103', '7', 'completo', '5', '2026-11-02 17:24:21'),
('87', '103', '8', 'completo', '5', '2026-11-02 17:24:21'),
('88', '103', '9', 'completo', '5', '2026-11-02 17:24:21'),
('89', '103', '10', 'completo', '5', '2026-11-02 17:24:21'),
('90', '103', '11', 'completo', '5', '2026-11-02 17:24:21'),
('91', '103', '12', 'completo', '5', '2026-11-02 17:24:21'),
('92', '103', '13', 'completo', '5', '2026-11-02 17:24:21'),
('93', '103', '14', 'completo', '5', '2026-11-02 17:24:21'),
('94', '103', '15', 'completo', '5', '2026-11-02 17:24:21'),
('95', '103', '16', 'completo', '5', '2026-11-02 17:24:21'),
('96', '103', '17', 'completo', '5', '2026-11-02 17:24:21'),
('97', '103', '18', 'completo', '5', '2026-11-02 17:24:21'),
('98', '103', '19', 'completo', '5', '2026-11-02 17:24:21'),
('99', '103', '20', 'completo', '5', '2026-11-02 17:24:21'),
('100', '103', '21', 'completo', '5', '2026-11-02 17:24:21');
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('101', '103', '22', 'completo', '5', '2026-11-02 17:24:21'),
('102', '103', '23', 'completo', '5', '2026-11-02 17:24:21'),
('103', '103', '24', 'completo', '5', '2026-11-02 17:24:21'),
('104', '103', '25', 'completo', '5', '2026-11-02 17:24:21'),
('105', '103', '26', 'completo', '5', '2026-11-02 17:24:21'),
('106', '103', '27', 'completo', '5', '2026-11-02 17:24:21'),
('107', '103', '28', 'completo', '5', '2026-11-02 17:24:21'),
('108', '103', '29', 'corrente', '2', '2026-11-02 17:24:21'),
('109', '104', '1', 'completo', '5', '2026-11-02 17:24:21'),
('110', '104', '2', 'completo', '5', '2026-11-02 17:24:21'),
('111', '104', '3', 'completo', '5', '2026-11-02 17:24:21'),
('112', '104', '4', 'completo', '5', '2026-11-02 17:24:21'),
('113', '104', '5', 'completo', '5', '2026-11-02 17:24:21'),
('114', '104', '6', 'completo', '5', '2026-11-02 17:24:21'),
('115', '104', '7', 'completo', '5', '2026-11-02 17:24:21'),
('116', '104', '8', 'completo', '5', '2026-11-02 17:24:21'),
('117', '104', '9', 'completo', '5', '2026-11-02 17:24:21'),
('118', '104', '10', 'completo', '5', '2026-11-02 17:24:21'),
('119', '104', '11', 'completo', '5', '2026-11-02 17:24:21'),
('120', '104', '12', 'completo', '5', '2026-11-02 17:24:21'),
('121', '104', '13', 'completo', '5', '2026-11-02 17:24:21'),
('122', '104', '14', 'completo', '5', '2026-11-02 17:24:21'),
('123', '104', '15', 'completo', '5', '2026-11-02 17:24:21'),
('124', '104', '16', 'completo', '5', '2026-11-02 17:24:21'),
('125', '104', '17', 'completo', '5', '2026-11-02 17:24:21'),
('126', '104', '18', 'completo', '5', '2026-11-02 17:24:21'),
('127', '104', '19', 'completo', '5', '2026-11-02 17:24:21'),
('128', '104', '20', 'completo', '5', '2026-11-02 17:24:21'),
('129', '104', '21', 'completo', '5', '2026-11-02 17:24:21'),
('130', '104', '22', 'completo', '5', '2026-11-02 17:24:21'),
('131', '104', '23', 'completo', '5', '2026-11-02 17:24:21'),
('132', '104', '24', 'completo', '5', '2026-11-02 17:24:21'),
('133', '104', '25', 'completo', '5', '2026-11-02 17:24:21'),
('134', '104', '26', 'corrente', '4', '2026-11-02 17:24:21'),
('135', '105', '1', 'completo', '5', '2026-11-02 17:24:21'),
('136', '105', '2', 'completo', '5', '2026-11-02 17:24:21'),
('137', '105', '3', 'completo', '5', '2026-11-02 17:24:21'),
('138', '105', '4', 'completo', '5', '2026-11-02 17:24:21'),
('139', '105', '5', 'completo', '5', '2026-11-02 17:24:21'),
('140', '105', '6', 'completo', '5', '2026-11-02 17:24:21'),
('141', '105', '7', 'completo', '5', '2026-11-02 17:24:21'),
('142', '105', '8', 'completo', '5', '2026-11-02 17:24:21'),
('143', '105', '9', 'completo', '5', '2026-11-02 17:24:21'),
('144', '105', '10', 'completo', '5', '2026-11-02 17:24:21'),
('145', '105', '11', 'completo', '5', '2026-11-02 17:24:21'),
('146', '105', '12', 'completo', '5', '2026-11-02 17:24:21'),
('147', '105', '13', 'completo', '5', '2026-11-02 17:24:21'),
('148', '105', '14', 'completo', '5', '2026-11-02 17:24:21'),
('149', '105', '15', 'completo', '5', '2026-11-02 17:24:21'),
('150', '105', '16', 'completo', '5', '2026-11-02 17:24:21');
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('151', '105', '17', 'completo', '5', '2026-11-02 17:24:21'),
('152', '105', '18', 'completo', '5', '2026-11-02 17:24:21'),
('153', '105', '19', 'completo', '5', '2026-11-02 17:24:21'),
('154', '105', '20', 'completo', '5', '2026-11-02 17:24:21'),
('155', '105', '21', 'completo', '5', '2026-11-02 17:24:21'),
('156', '105', '22', 'completo', '5', '2026-11-02 17:24:21'),
('157', '105', '23', 'corrente', '1', '2026-11-02 17:24:21'),
('158', '106', '1', 'completo', '5', '2026-11-02 17:24:21'),
('159', '106', '2', 'completo', '5', '2026-11-02 17:24:21'),
('160', '106', '3', 'completo', '5', '2026-11-02 17:24:21'),
('161', '106', '4', 'completo', '5', '2026-11-02 17:24:21'),
('162', '106', '5', 'completo', '5', '2026-11-02 17:24:21'),
('163', '106', '6', 'completo', '5', '2026-11-02 17:24:21'),
('164', '106', '7', 'completo', '5', '2026-11-02 17:24:21'),
('165', '106', '8', 'completo', '5', '2026-11-02 17:24:21'),
('166', '106', '9', 'completo', '5', '2026-11-02 17:24:21'),
('167', '106', '10', 'completo', '5', '2026-11-02 17:24:21'),
('168', '106', '11', 'completo', '5', '2026-11-02 17:24:21'),
('169', '106', '12', 'completo', '5', '2026-11-02 17:24:21'),
('170', '106', '13', 'completo', '5', '2026-11-02 17:24:21'),
('171', '106', '14', 'completo', '5', '2026-11-02 17:24:21'),
('172', '106', '15', 'completo', '5', '2026-11-02 17:24:21'),
('173', '106', '16', 'completo', '5', '2026-11-02 17:24:21'),
('174', '106', '17', 'corrente', '2', '2026-11-02 17:24:21'),
('175', '107', '1', 'completo', '5', '2026-11-02 17:24:21'),
('176', '107', '2', 'completo', '5', '2026-11-02 17:24:21'),
('177', '107', '3', 'completo', '5', '2026-11-02 17:24:21'),
('178', '107', '4', 'completo', '5', '2026-11-02 17:24:21'),
('179', '107', '5', 'completo', '5', '2026-11-02 17:24:21'),
('180', '107', '6', 'completo', '5', '2026-11-02 17:24:21'),
('181', '107', '7', 'completo', '5', '2026-11-02 17:24:21'),
('182', '107', '8', 'completo', '5', '2026-11-02 17:24:21'),
('183', '107', '9', 'completo', '5', '2026-11-02 17:24:21'),
('184', '107', '10', 'completo', '5', '2026-11-02 17:24:21'),
('185', '107', '11', 'completo', '5', '2026-11-02 17:24:21'),
('186', '107', '12', 'completo', '5', '2026-11-02 17:24:21'),
('187', '107', '13', 'completo', '5', '2026-11-02 17:24:21'),
('188', '107', '14', 'completo', '5', '2026-11-02 17:24:21'),
('189', '107', '15', 'corrente', '3', '2026-11-02 17:24:21'),
('190', '108', '1', 'completo', '5', '2026-11-02 17:24:21'),
('191', '108', '2', 'completo', '5', '2026-11-02 17:24:21'),
('192', '108', '3', 'completo', '5', '2026-11-02 17:24:21'),
('193', '108', '4', 'completo', '5', '2026-11-02 17:24:21'),
('194', '108', '5', 'completo', '5', '2026-11-02 17:24:21'),
('195', '108', '6', 'completo', '5', '2026-11-02 17:24:21'),
('196', '108', '7', 'completo', '5', '2026-11-02 17:24:21'),
('197', '108', '8', 'completo', '5', '2026-11-02 17:24:21'),
('198', '108', '9', 'completo', '5', '2026-11-02 17:24:21'),
('199', '108', '10', 'completo', '5', '2026-11-02 17:24:21'),
('200', '108', '11', 'completo', '5', '2026-11-02 17:24:21');
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('201', '108', '12', 'completo', '5', '2026-11-02 17:24:21'),
('202', '108', '13', 'corrente', '1', '2026-11-02 17:24:21'),
('203', '109', '1', 'completo', '5', '2026-11-02 17:24:21'),
('204', '109', '2', 'completo', '5', '2026-11-02 17:24:21'),
('205', '109', '3', 'completo', '5', '2026-11-02 17:24:21'),
('206', '109', '4', 'completo', '5', '2026-11-02 17:24:21'),
('207', '109', '5', 'completo', '5', '2026-11-02 17:24:21'),
('208', '109', '6', 'completo', '5', '2026-11-02 17:24:21'),
('209', '109', '7', 'completo', '5', '2026-11-02 17:24:21'),
('210', '109', '8', 'completo', '5', '2026-11-02 17:24:21'),
('211', '109', '9', 'completo', '5', '2026-11-02 17:24:21'),
('212', '109', '10', 'completo', '5', '2026-11-02 17:24:21'),
('213', '109', '11', 'completo', '5', '2026-11-02 17:24:21'),
('214', '109', '12', 'corrente', '4', '2026-11-02 17:24:21'),
('215', '110', '1', 'completo', '5', '2026-11-02 17:24:21'),
('216', '110', '2', 'completo', '5', '2026-11-02 17:24:21'),
('217', '110', '3', 'completo', '5', '2026-11-02 17:24:21'),
('218', '110', '4', 'completo', '5', '2026-11-02 17:24:21'),
('219', '110', '5', 'completo', '5', '2026-11-02 17:24:21'),
('220', '110', '6', 'completo', '5', '2026-11-02 17:24:21'),
('221', '110', '7', 'completo', '5', '2026-11-02 17:24:21'),
('222', '110', '8', 'completo', '5', '2026-11-02 17:24:21'),
('223', '110', '9', 'completo', '5', '2026-11-02 17:24:21'),
('224', '110', '10', 'completo', '5', '2026-11-02 17:24:21'),
('225', '110', '11', 'corrente', '0', '2026-11-02 17:24:21'),
('226', '111', '1', 'completo', '5', '2026-11-02 17:24:21'),
('227', '111', '2', 'completo', '5', '2026-11-02 17:24:21'),
('228', '111', '3', 'completo', '5', '2026-11-02 17:24:21'),
('229', '111', '4', 'completo', '5', '2026-11-02 17:24:21'),
('230', '111', '5', 'completo', '5', '2026-11-02 17:24:21'),
('231', '111', '6', 'completo', '5', '2026-11-02 17:24:21'),
('232', '111', '7', 'completo', '5', '2026-11-02 17:24:21'),
('233', '111', '8', 'completo', '5', '2026-11-02 17:24:21'),
('234', '111', '9', 'corrente', '2', '2026-11-02 17:24:21'),
('235', '112', '1', 'completo', '5', '2026-11-02 17:24:21'),
('236', '112', '2', 'completo', '5', '2026-11-02 17:24:21'),
('237', '112', '3', 'completo', '5', '2026-11-02 17:24:21'),
('238', '112', '4', 'completo', '5', '2026-11-02 17:24:21'),
('239', '112', '5', 'completo', '5', '2026-11-02 17:24:21'),
('240', '112', '6', 'completo', '5', '2026-11-02 17:24:21'),
('241', '112', '7', 'completo', '5', '2026-11-02 17:24:21'),
('242', '112', '8', 'corrente', '3', '2026-11-02 17:24:21'),
('243', '113', '1', 'completo', '5', '2026-11-02 17:24:21'),
('244', '113', '2', 'completo', '5', '2026-11-02 17:24:21'),
('245', '113', '3', 'completo', '5', '2026-11-02 17:24:21'),
('246', '113', '4', 'completo', '5', '2026-11-02 17:24:21'),
('247', '113', '5', 'completo', '5', '2026-11-02 17:24:21'),
('248', '113', '6', 'completo', '5', '2026-11-02 17:24:21'),
('249', '113', '7', 'corrente', '1', '2026-11-02 17:24:21'),
('250', '114', '1', 'completo', '5', '2026-11-02 17:24:21');
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('251', '114', '2', 'completo', '5', '2026-11-02 17:24:21'),
('252', '114', '3', 'completo', '5', '2026-11-02 17:24:21'),
('253', '114', '4', 'completo', '5', '2026-11-02 17:24:21'),
('254', '114', '5', 'completo', '5', '2026-11-02 17:24:21'),
('255', '114', '6', 'corrente', '4', '2026-11-02 17:24:21'),
('256', '115', '1', 'completo', '5', '2026-11-02 17:24:21'),
('257', '115', '2', 'completo', '5', '2026-11-02 17:24:21'),
('258', '115', '3', 'completo', '5', '2026-11-02 17:24:21'),
('259', '115', '4', 'completo', '5', '2026-11-02 17:24:21'),
('260', '115', '5', 'completo', '5', '2026-11-02 17:24:21'),
('261', '115', '6', 'corrente', '0', '2026-11-02 17:24:21'),
('262', '116', '1', 'completo', '5', '2026-11-02 17:24:21'),
('263', '116', '2', 'completo', '5', '2026-11-02 17:24:21'),
('264', '116', '3', 'completo', '5', '2026-11-02 17:24:21'),
('265', '116', '4', 'completo', '5', '2026-11-02 17:24:21'),
('266', '116', '5', 'corrente', '2', '2026-11-02 17:24:21'),
('267', '117', '1', 'completo', '5', '2026-11-02 17:24:21'),
('268', '117', '2', 'completo', '5', '2026-11-02 17:24:21'),
('269', '117', '3', 'completo', '5', '2026-11-02 17:24:21'),
('270', '117', '4', 'corrente', '1', '2026-11-02 17:24:21'),
('271', '118', '1', 'completo', '5', '2026-11-02 17:24:21'),
('272', '118', '2', 'completo', '5', '2026-11-02 17:24:21'),
('273', '118', '3', 'completo', '5', '2026-11-02 17:24:21'),
('274', '118', '4', 'corrente', '0', '2026-11-02 17:24:21'),
('275', '119', '1', 'completo', '5', '2026-11-02 17:24:21'),
('276', '119', '2', 'completo', '5', '2026-11-02 17:24:21'),
('277', '119', '3', 'corrente', '3', '2026-11-02 17:24:21'),
('278', '120', '1', 'completo', '5', '2026-11-02 17:24:21'),
('279', '120', '2', 'completo', '5', '2026-11-02 17:24:21'),
('280', '120', '3', 'corrente', '1', '2026-11-02 17:24:21'),
('281', '121', '1', 'completo', '5', '2026-11-02 17:24:21'),
('282', '121', '2', 'corrente', '4', '2026-11-02 17:24:21'),
('283', '122', '1', 'completo', '5', '2026-11-02 17:24:21'),
('284', '122', '2', 'corrente', '1', '2026-11-02 17:24:21'),
('285', '123', '1', 'corrente', '3', '2026-11-02 17:24:21'),
('286', '124', '1', 'corrente', '2', '2026-11-02 17:24:21'),
('287', '125', '1', 'corrente', '1', '2026-11-02 17:24:21'),
('288', '126', '1', 'corrente', '0', '2026-11-02 17:24:21'),
('289', '127', '1', 'corrente', '0', '2026-11-02 17:24:21'),
('290', '128', '1', 'corrente', '0', '2026-11-05 17:39:09'),
('291', '128', '2', 'trancado', '0', '2026-11-05 17:39:09'),
('292', '128', '3', 'trancado', '0', '2026-11-05 17:39:09'),
('293', '128', '4', 'trancado', '0', '2026-11-05 17:39:09'),
('294', '128', '5', 'trancado', '0', '2026-11-05 17:39:09'),
('296', '129', '1', 'corrente', '1', '2026-11-14 13:24:15'),
('297', '129', '2', 'trancado', '0', '2026-11-14 13:23:21'),
('298', '129', '3', 'trancado', '0', '2026-11-14 13:23:21'),
('299', '129', '4', 'trancado', '0', '2026-11-14 13:23:21'),
('300', '129', '5', 'trancado', '0', '2026-11-14 13:23:21'),
('301', '130', '1', 'corrente', '1', '2026-11-14 15:47:31');
INSERT INTO `progresso_usuario` (`id`, `usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`, `atualizado_em`) VALUES
('302', '130', '2', 'trancado', '0', '2026-11-14 15:46:11'),
('303', '130', '3', 'trancado', '0', '2026-11-14 15:46:11'),
('304', '130', '4', 'trancado', '0', '2026-11-14 15:46:11'),
('305', '130', '5', 'trancado', '0', '2026-11-14 15:46:11');

-- Dados da tabela: `ligas_grupos` (8 registros)
INSERT INTO `ligas_grupos` (`id`, `divisao`, `semana_ref`, `capacidade`, `criado_em`) VALUES
('1', 'bronze', '2026-11-01', '30', '2026-11-02 17:21:06'),
('2', 'prata', '2026-11-01', '30', '2026-11-02 17:24:21'),
('3', 'ouro', '2026-11-01', '30', '2026-11-02 17:24:21'),
('4', 'diamante', '2026-11-01', '30', '2026-11-02 17:24:21'),
('5', 'mestre', '2026-11-01', '30', '2026-11-02 17:24:21'),
('6', 'bronze', '2026-11-08', '30', '2026-11-14 13:23:21'),
('7', 'bronze', '2026-11-15', '30', '2026-11-18 21:36:44'),
('8', 'prata', '2026-11-15', '30', '2026-11-19 19:32:43');

-- Dados da tabela: `ligas_usuario` (32 registros)
INSERT INTO `ligas_usuario` (`id`, `usuario_id`, `divisao`, `grupo_id`, `xp_semana`, `posicao_semana_anterior`, `atualizado_em`) VALUES
('1', '2', 'prata', '8', '10', '1', '2026-11-19 19:39:36'),
('3', '101', 'mestre', NULL, '0', '1', '2026-11-19 18:51:54'),
('4', '102', 'mestre', NULL, '0', '2', '2026-11-19 18:51:54'),
('5', '103', 'mestre', NULL, '0', '3', '2026-11-19 18:51:54'),
('6', '104', 'mestre', NULL, '0', '4', '2026-11-19 18:51:54'),
('7', '105', 'mestre', NULL, '0', '5', '2026-11-19 18:51:54'),
('8', '106', 'mestre', NULL, '0', '1', '2026-11-19 18:51:54'),
('9', '107', 'mestre', NULL, '0', '2', '2026-11-19 18:51:54'),
('10', '108', 'diamante', NULL, '0', '3', '2026-11-19 18:51:54'),
('11', '109', 'diamante', NULL, '0', '4', '2026-11-19 18:51:54'),
('12', '110', 'ouro', NULL, '0', '5', '2026-11-19 18:51:54'),
('13', '111', 'diamante', NULL, '0', '1', '2026-11-19 18:51:54'),
('14', '112', 'diamante', NULL, '0', '2', '2026-11-19 18:51:54'),
('15', '113', 'ouro', NULL, '0', '3', '2026-11-19 18:51:54'),
('16', '114', 'ouro', NULL, '0', '4', '2026-11-19 18:51:54'),
('17', '115', 'ouro', NULL, '0', '5', '2026-11-19 18:51:54'),
('18', '116', 'prata', NULL, '0', '6', '2026-11-19 18:51:54'),
('19', '117', 'ouro', NULL, '0', '1', '2026-11-19 18:51:54'),
('20', '118', 'ouro', NULL, '0', '2', '2026-11-19 18:51:54'),
('21', '119', 'prata', NULL, '0', '3', '2026-11-19 18:51:54'),
('22', '120', 'prata', NULL, '0', '4', '2026-11-19 18:51:54'),
('23', '121', 'bronze', NULL, '0', '5', '2026-11-19 18:51:54'),
('24', '122', 'prata', NULL, '0', '2', '2026-11-19 18:51:54'),
('25', '123', 'prata', NULL, '0', '3', '2026-11-19 18:51:54'),
('26', '124', 'prata', NULL, '0', '4', '2026-11-19 18:51:54'),
('27', '125', 'bronze', NULL, '0', '5', '2026-11-19 18:51:54'),
('28', '126', 'bronze', NULL, '0', '6', '2026-11-19 18:51:54'),
('29', '127', 'bronze', NULL, '0', '7', '2026-11-19 18:51:54'),
('50', '128', 'bronze', NULL, '0', '8', '2026-11-19 18:51:54'),
('67', '129', 'prata', NULL, '0', '1', '2026-11-19 18:51:54'),
('69', '130', 'bronze', NULL, '0', '2', '2026-11-19 18:51:54'),
('116', '1', 'prata', NULL, '0', '1', '2026-11-19 18:51:54');

-- =============================================================================
-- SEÇÃO 3: VIEWS ANALÍTICAS
-- =============================================================================
CREATE OR REPLACE VIEW `vw_ranking_geral` AS
SELECT 
    u.id,
    u.nome,
    u.username,
    u.foto_perfil,
    u.cor_fundo,
    u.xp,
    u.trofeus,
    u.dias_fogo,
    u.nivel_acesso,
    u.pais_codigo,
    COALESCE(lu.divisao, 'bronze') AS divisao,
    COALESCE(lu.xp_semana, 0) AS xp_semana,
    DENSE_RANK() OVER (ORDER BY u.xp DESC) AS posicao_geral
FROM `usuarios` u
LEFT JOIN `ligas_usuario` lu ON lu.usuario_id = u.id;

CREATE OR REPLACE VIEW `vw_desempenho_aluno` AS
SELECT 
    u.id AS usuario_id,
    u.nome,
    u.email,
    u.xp,
    u.trofeus,
    u.dias_fogo,
    u.vidas,
    COUNT(DISTINCT pu.unidade_numero) AS unidades_iniciadas,
    SUM(CASE WHEN pu.status = 'completo' THEN 1 ELSE 0 END) AS unidades_concluidas,
    COALESCE(SUM(pu.licoes_concluidas), 0) AS total_licoes_concluidas,
    COALESCE((SELECT COUNT(*) FROM desafios_projetos_usuario dpu WHERE dpu.usuario_id = u.id AND dpu.status = 'concluido'), 0) AS projetos_concluidos
FROM `usuarios` u
LEFT JOIN `progresso_usuario` pu ON pu.usuario_id = u.id
GROUP BY u.id, u.nome, u.email, u.xp, u.trofeus, u.dias_fogo, u.vidas;

-- =============================================================================
-- SEÇÃO 4: STORED PROCEDURES (LÓGICA DE NEGÓCIO E GAMIFICAÇÃO NO BANCO)
-- =============================================================================
DELIMITER ;;

DROP PROCEDURE IF EXISTS `sp_atualizar_fogo`;;
CREATE PROCEDURE `sp_atualizar_fogo`(IN p_user_id INT)
BEGIN
    DECLARE v_ultima DATE DEFAULT NULL;
    DECLARE v_fogo INT DEFAULT 0;
    DECLARE v_hoje DATE DEFAULT CURDATE();
    DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

    SELECT ultima_atividade, dias_fogo INTO v_ultima, v_fogo FROM usuarios WHERE id = p_user_id FOR UPDATE;

    IF v_ultima IS NULL THEN
        SET v_fogo = 1;
    ELSEIF v_ultima = DATE_SUB(v_hoje, INTERVAL 1 DAY) THEN
        SET v_fogo = v_fogo + 1;
    ELSEIF v_ultima < DATE_SUB(v_hoje, INTERVAL 1 DAY) THEN
        SET v_fogo = 1;
    ELSEIF v_fogo <= 0 THEN
        SET v_fogo = 1;
    END IF;

    UPDATE usuarios SET dias_fogo = v_fogo, ultima_atividade = v_hoje WHERE id = p_user_id;
    SELECT v_fogo AS dias_fogo;
END;;

DROP PROCEDURE IF EXISTS `sp_liga_registrar_xp`;;
CREATE PROCEDURE `sp_liga_registrar_xp`(IN p_user_id INT, IN p_xp INT)
BEGIN
    DECLARE v_semana DATE;
    DECLARE v_grupo_id INT DEFAULT NULL;
    DECLARE v_divisao VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'bronze';
    DECLARE v_existe INT DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

    -- semana começa no domingo (igual a liga_semana_atual() do PHP)
    SET v_semana = DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) - 1) DAY);

    SELECT COUNT(*) INTO v_existe FROM ligas_usuario WHERE usuario_id = p_user_id;
    IF v_existe > 0 THEN
        SELECT divisao, grupo_id INTO v_divisao, v_grupo_id
        FROM ligas_usuario WHERE usuario_id = p_user_id LIMIT 1;
    END IF;

    IF v_grupo_id IS NULL THEN
        SELECT g.id INTO v_grupo_id
        FROM ligas_grupos g
        WHERE g.semana_ref = v_semana AND g.divisao = v_divisao
          AND (SELECT COUNT(*) FROM ligas_usuario lu WHERE lu.grupo_id = g.id) < g.capacidade
        ORDER BY g.id LIMIT 1;

        IF v_grupo_id IS NULL THEN
            INSERT INTO ligas_grupos (divisao, semana_ref, capacidade) VALUES (v_divisao, v_semana, 30);
            SET v_grupo_id = LAST_INSERT_ID();
        END IF;
    END IF;

    INSERT INTO ligas_usuario (usuario_id, divisao, grupo_id, xp_semana)
    VALUES (p_user_id, v_divisao, v_grupo_id, GREATEST(0, p_xp))
    ON DUPLICATE KEY UPDATE grupo_id = v_grupo_id, xp_semana = GREATEST(0, xp_semana + p_xp);
END;;

DROP PROCEDURE IF EXISTS `sp_penalizar_saida_licao`;;
CREATE PROCEDURE `sp_penalizar_saida_licao`(IN p_user_id INT)
BEGIN
    CALL sp_perder_vida(p_user_id);
    SELECT xp, 0 AS xp_perdido, vidas FROM usuarios WHERE id = p_user_id;
END;;

DROP PROCEDURE IF EXISTS `sp_perder_vida`;;
CREATE PROCEDURE `sp_perder_vida`(IN p_user_id INT)
BEGIN
    DECLARE v_vidas INT DEFAULT 0;
    DECLARE v_proxima_em DATETIME DEFAULT NULL;
    DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

    SELECT vidas, vidas_proxima_em INTO v_vidas, v_proxima_em FROM usuarios WHERE id = p_user_id FOR UPDATE;

    IF v_vidas > 0 THEN
        SET v_vidas = v_vidas - 1;
        IF v_proxima_em IS NULL THEN
            SET v_proxima_em = DATE_ADD(NOW(), INTERVAL 30 MINUTE);
        END IF;
        UPDATE usuarios SET vidas = v_vidas, vidas_proxima_em = v_proxima_em WHERE id = p_user_id;
    END IF;

    SELECT v_vidas AS vidas, v_vidas AS vidas_restantes, v_proxima_em AS proxima_recarga;
END;;

DROP PROCEDURE IF EXISTS `sp_processar_resultado_licao`;;
CREATE PROCEDURE `sp_processar_resultado_licao`(
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
    DECLARE v_xp_ganho INT DEFAULT 10;
    DECLARE v_licoes_feitas INT DEFAULT NULL;
    DECLARE v_eh_repeticao INT DEFAULT 0;
    DECLARE v_eh_perfeita INT DEFAULT 0;

    IF p_acertos > 0 THEN
        IF p_acertos >= p_total AND p_total > 0 THEN
            SET v_xp_ganho = 50;
            SET v_eh_perfeita = 1;
            INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'perfeicao');
            INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'perfeicao');
        ELSEIF (p_acertos * 10 >= p_total * 8) AND p_total > 0 THEN
            SET v_xp_ganho = 40;
        ELSEIF (p_acertos * 10 >= p_total * 5) AND p_total > 0 THEN
            SET v_xp_ganho = 25;
        ELSEIF (p_acertos * 10 >= p_total * 2) AND p_total > 0 THEN
            SET v_xp_ganho = 15;
        ELSE
            SET v_xp_ganho = 10;
        END IF;

        SET v_licoes_feitas = (
            SELECT licoes_concluidas 
            FROM progresso_usuario 
            WHERE usuario_id = p_user_id AND unidade_numero = p_unidade
            LIMIT 1
        );
        
        IF v_licoes_feitas IS NULL THEN
            INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) 
            VALUES (p_user_id, p_unidade, IF(p_licao >= 5, 'completo', 'corrente'), p_licao);
            SET v_licoes_feitas = p_licao;
            SET v_avancou = 1;
        ELSEIF p_licao > v_licoes_feitas THEN
            SET v_licoes_feitas = p_licao;
            UPDATE progresso_usuario 
            SET licoes_concluidas = v_licoes_feitas,
                status = IF(v_licoes_feitas >= 5, 'completo', IF(status = 'trancado', 'corrente', status))
            WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
            SET v_avancou = 1;
        ELSE
            SET v_eh_repeticao = 1;
            SET v_xp_ganho = 10;
        END IF;

        UPDATE usuarios SET xp = xp + v_xp_ganho WHERE id = p_user_id;

        IF v_licoes_feitas >= 3 THEN
            SET v_bau_liberado = 1;
        END IF;

        IF v_licoes_feitas >= 5 OR p_licao >= 5 THEN
            SET v_capitulo_concluido = 1;
            UPDATE progresso_usuario 
            SET status = 'completo', licoes_concluidas = GREATEST(licoes_concluidas, 5)
            WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
            
            IF p_unidade < 40 THEN
                INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas)
                VALUES (p_user_id, p_unidade + 1, 'corrente', 0)
                ON DUPLICATE KEY UPDATE status = IF(status = 'trancado', 'corrente', status);
            END IF;

            IF p_unidade = 1 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_1'); END IF;
            IF p_unidade = 2 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_2'); END IF;
            IF p_unidade = 3 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_3'); END IF;
            IF p_unidade = 4 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_4'); END IF;
            IF p_unidade = 5 THEN INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'capitulo_5'); END IF;
        END IF;

        CALL sp_liga_registrar_xp(p_user_id, v_xp_ganho);
        CALL sp_registrar_missao_progresso(p_user_id, v_xp_ganho, v_eh_perfeita);
        CALL sp_atualizar_fogo(p_user_id);
        CALL sp_verificar_conquistas(p_user_id);
    END IF;

    SELECT v_avancou AS avancou, v_capitulo_concluido AS capitulo_concluido, v_bau_liberado AS bau_liberado, v_xp_ganho AS xp_ganho, v_eh_repeticao AS eh_repeticao;
END;;

DROP PROCEDURE IF EXISTS `sp_registrar_missao_progresso`;;
CREATE PROCEDURE `sp_registrar_missao_progresso`(IN p_user_id INT, IN p_xp INT, IN p_perfeita INT)
BEGIN
    DECLARE v_hoje DATE DEFAULT CURDATE();
    INSERT INTO missoes_diarias_usuario (usuario_id, data_ref, xp_ganho, licoes_concluidas, licoes_perfeitas)
    VALUES (p_user_id, v_hoje, p_xp, 1, IF(p_perfeita >= 1, 1, 0))
    ON DUPLICATE KEY UPDATE
        xp_ganho = xp_ganho + p_xp,
        licoes_concluidas = licoes_concluidas + 1,
        licoes_perfeitas = licoes_perfeitas + IF(p_perfeita >= 1, 1, 0);
END;;

DROP PROCEDURE IF EXISTS `sp_resgatar_bau`;;
CREATE PROCEDURE `sp_resgatar_bau`(
    IN p_user_id INT,
    IN p_unidade INT,
    IN p_tipo_recompensa VARCHAR(50),
    IN p_xp_ganho INT,
    IN p_vidas_ganhas INT,
    IN p_detalhe VARCHAR(255)
)
BEGIN
    DECLARE v_ja_resgatou INT DEFAULT 0;
    DECLARE v_concluidas INT DEFAULT 0;
    DECLARE v_status_cap VARCHAR(20) DEFAULT '';
    DECLARE v_vidas_atuais INT DEFAULT 3;
    DECLARE v_xp_recompensa INT DEFAULT 0;
    DECLARE v_vidas_recompensa INT DEFAULT 0;

    SELECT COUNT(*) INTO v_ja_resgatou FROM bau_recompensas WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
    SELECT COALESCE(licoes_concluidas, 0), COALESCE(status, '') INTO v_concluidas, v_status_cap
    FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_unidade LIMIT 1;

    IF v_ja_resgatou > 0 THEN
        SELECT 'ja_resgatado' AS status, 0 AS resgatado, 0 AS xp_ganho, 0 AS vidas_ganhas, 'Você já resgatou o baú desta unidade!' AS detalhe;
    ELSEIF v_concluidas < 3 AND v_status_cap <> 'completo' THEN
        SELECT 'requisito_pendente' AS status, 0 AS resgatado, 0 AS xp_ganho, 0 AS vidas_ganhas, 'Você precisa concluir pelo menos 3 lições desta unidade para abrir o baú!' AS detalhe;
    ELSE
        SELECT COALESCE(vidas, 3) INTO v_vidas_atuais FROM usuarios WHERE id = p_user_id FOR UPDATE;

        SET v_xp_recompensa = GREATEST(0, COALESCE(p_xp_ganho, 0));
        SET v_vidas_recompensa = GREATEST(0, COALESCE(p_vidas_ganhas, 0));

        IF v_vidas_recompensa > 0 AND (v_vidas_atuais + v_vidas_recompensa) > 3 THEN
            SET v_vidas_recompensa = GREATEST(0, 3 - v_vidas_atuais);
        END IF;

        INSERT INTO bau_recompensas (usuario_id, unidade_numero, tipo_recompensa, xp_ganho, vidas_ganhas)
        VALUES (p_user_id, p_unidade, COALESCE(p_tipo_recompensa, 'roleta'), v_xp_recompensa, v_vidas_recompensa);

        UPDATE usuarios SET 
            xp = xp + v_xp_recompensa,
            vidas = LEAST(3, vidas + v_vidas_recompensa),
            vidas_proxima_em = IF(LEAST(3, vidas + v_vidas_recompensa) >= 3, NULL, vidas_proxima_em)
        WHERE id = p_user_id;

        IF v_xp_recompensa > 0 THEN
            CALL sp_liga_registrar_xp(p_user_id, v_xp_recompensa);
            CALL sp_registrar_missao_progresso(p_user_id, v_xp_recompensa, 0);
        END IF;

        SELECT 'ok' AS status, 1 AS resgatado, v_xp_recompensa AS xp_ganho, v_vidas_recompensa AS vidas_ganhas, COALESCE(p_detalhe, '') AS detalhe;
    END IF;
END;;

DROP PROCEDURE IF EXISTS `sp_sincronizar_jogador`;;
CREATE PROCEDURE `sp_sincronizar_jogador`(IN p_user_id INT)
BEGIN
    DECLARE v_vidas INT;
    DECLARE v_proxima_em DATETIME;
    DECLARE v_agora DATETIME DEFAULT NOW();
    DECLARE v_tempo_recarga INT DEFAULT 1800;
    DECLARE v_vidas_ganhas INT DEFAULT 0;
    DECLARE v_segundos_passados INT;
    DECLARE v_resto_segundos INT;
    DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

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

    SELECT id, nome, email, xp, trofeus, foto_perfil, dias_fogo, vidas, vidas_proxima_em, nivel_acesso, cor_fundo, username
    FROM usuarios WHERE id = p_user_id;
END;;

DROP PROCEDURE IF EXISTS `sp_verificar_conquistas`;;
CREATE PROCEDURE `sp_verificar_conquistas`(IN p_user_id INT)
BEGIN
    DECLARE v_xp INT DEFAULT 0;
    DECLARE v_fogo INT DEFAULT 0;
    DECLARE CONTINUE HANDLER FOR NOT FOUND BEGIN END;

    SELECT xp, dias_fogo INTO v_xp, v_fogo FROM usuarios WHERE id = p_user_id;

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
END;;

DROP PROCEDURE IF EXISTS `sp_corrigir_licao`;;
CREATE PROCEDURE `sp_corrigir_licao`(
    IN p_user_id INT,
    IN p_unidade INT,
    IN p_licao INT,
    IN p_acertos INT
)
BEGIN
    CALL sp_processar_resultado_licao(p_user_id, p_unidade, p_licao, p_acertos, 3);
END;;

DROP PROCEDURE IF EXISTS `sp_processar_virada_semana`;;
CREATE PROCEDURE `sp_processar_virada_semana`()
BEGIN
    DECLARE v_semana_antiga DATE;
    DECLARE v_nova_semana DATE;
    SET v_nova_semana = DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) - 1) DAY);
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
END;;

DELIMITER ;

-- =============================================================================
-- SEÇÃO 5: TRIGGERS DE INTEGRIDADE E AUTOMAÇÃO
-- =============================================================================
DELIMITER ;;

DROP TRIGGER IF EXISTS `tg_novo_usuario_setup`;;
CREATE TRIGGER `tg_novo_usuario_setup`
AFTER INSERT ON `usuarios`
FOR EACH ROW
BEGIN
    INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`)
    VALUES (NEW.id, 1, 'corrente', 0)
    ON DUPLICATE KEY UPDATE `status` = 'corrente';

    INSERT IGNORE INTO `missoes_diarias_usuario` (`usuario_id`, `data_ref`, `xp_ganho`, `licoes_concluidas`, `licoes_perfeitas`)
    VALUES (NEW.id, CURDATE(), 0, 0, 0);
END;;

DROP TRIGGER IF EXISTS `tg_incrementar_trofeu`;;
CREATE TRIGGER `tg_incrementar_trofeu`
AFTER INSERT ON `user_trofeus`
FOR EACH ROW
BEGIN
    UPDATE `usuarios`
    SET `trofeus` = (SELECT COUNT(*) FROM `user_trofeus` WHERE `user_id` = NEW.user_id)
    WHERE `id` = NEW.user_id;
END;;

DROP TRIGGER IF EXISTS `tg_decrementar_trofeu`;;
CREATE TRIGGER `tg_decrementar_trofeu`
AFTER DELETE ON `user_trofeus`
FOR EACH ROW
BEGIN
    UPDATE `usuarios`
    SET `trofeus` = (SELECT COUNT(*) FROM `user_trofeus` WHERE `user_id` = OLD.user_id)
    WHERE `id` = OLD.user_id;
END;;

DROP TRIGGER IF EXISTS `tg_validar_pedido_conexao`;;
CREATE TRIGGER `tg_validar_pedido_conexao`
BEFORE INSERT ON `pedidos_conexao`
FOR EACH ROW
BEGIN
    IF NEW.remetente_id = NEW.destinatario_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é permitido enviar solicitação de amizade para si mesmo.';
    END IF;
END;;

DROP TRIGGER IF EXISTS `tg_validar_chat_mensagem`;;
CREATE TRIGGER `tg_validar_chat_mensagem`
BEFORE INSERT ON `chat_mensagens`
FOR EACH ROW
BEGIN
    IF NEW.remetente_id = NEW.destinatario_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é permitido enviar mensagens no chat para si próprio.';
    END IF;
END;;

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- Fim do script oficial opus.sql
