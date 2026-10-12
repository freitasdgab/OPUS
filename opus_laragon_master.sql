-- ==============================================================================
-- BANCO DE DADOS COMPLETO OPUS (COMPATÍVEL COM LARAGON / MYSQL / MARIADB)
-- Gerado com todas as tabelas, dados reais e Stored Procedures
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `opus`;

SET FOREIGN_KEY_CHECKS = 0;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: opus
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acertos_diarios`
--

LOCK TABLES `acertos_diarios` WRITE;
/*!40000 ALTER TABLE `acertos_diarios` DISABLE KEYS */;
INSERT INTO `acertos_diarios` VALUES (1,20,'2026-10-08',3);
/*!40000 ALTER TABLE `acertos_diarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `batalhas_amigos`
--

DROP TABLE IF EXISTS `batalhas_amigos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batalhas_amigos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `vencedor_id` int(11) DEFAULT NULL,
  `xp_inicio_1` int(11) DEFAULT 0,
  `xp_inicio_2` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user1_id` (`user1_id`),
  KEY `user2_id` (`user2_id`),
  CONSTRAINT `batalhas_amigos_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `batalhas_amigos_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batalhas_amigos`
--

LOCK TABLES `batalhas_amigos` WRITE;
/*!40000 ALTER TABLE `batalhas_amigos` DISABLE KEYS */;
INSERT INTO `batalhas_amigos` VALUES (1,22,20,'2026-10-08 21:48:30','2026-10-15 21:48:30',22,0,0),(2,22,20,'2026-10-08 21:48:59','2026-10-15 21:48:59',NULL,0,0),(3,22,20,'2026-10-08 22:02:51','2026-10-15 22:02:51',NULL,500,50),(4,22,20,'2026-10-08 22:07:41','2026-10-15 22:07:41',NULL,500,50),(5,22,20,'2026-10-08 22:07:46','2026-10-15 22:07:46',NULL,500,50),(6,22,20,'2026-10-10 00:07:14','2026-10-17 00:07:14',NULL,500,50),(7,22,20,'2026-10-10 01:44:10','2026-10-17 01:44:10',NULL,500,50);
/*!40000 ALTER TABLE `batalhas_amigos` ENABLE KEYS */;
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
INSERT INTO `catalogo_trofeus` VALUES ('batalha_irmaos','Batalha de Irmãos','Venceu uma Batalha de Fogo contra um amigo!',1,99,'fa-handshake-angle','trofeu_fogo.png','#ff0055','lendario','2026-10-08 18:47:15'),('capitulo_1','Fundamentos','Terminou o Capítulo 1.',1,3,'fa-award','trofeu_cap1.png','#22c55e','comum','2026-09-27 02:22:37'),('capitulo_2','Caminhos Lógicos','Dominou as Estruturas de Decisão no Cap. 2.',1,4,'fa-shield-halved','trofeu_cap2.png','#3b82f6','raro','2026-09-27 02:22:37'),('capitulo_3','Mestre da Repetição','Dominou os Loops no Capítulo 3.',1,5,'fa-repeat','trofeu_cap3.png','#a855f7','epico','2026-09-27 02:22:37'),('capitulo_4','Senhor dos Arrays','Dominou Arrays e Matrizes no Capítulo 4.',1,6,'fa-database','trofeu_cap4.png','#ec4899','epico','2026-09-27 02:22:37'),('capitulo_5','Arquiteto Java','Concluiu POO no Capítulo 5. Você é o mestre!',1,7,'fa-crown','trofeu_cap5.png','#ffd700','lendario','2026-09-27 02:22:37'),('fogo_3','Em Chamas','Alcançou 3 Dias de Fogo.',1,8,'fa-fire','trofeu_fogo.png','#f97316','raro','2026-09-27 02:22:37'),('perfeicao','Mente Brilhante','Acertou 3/3 em um desafio.',1,2,'fa-brain','trofeu_cerebro.png','#1cb0f6','raro','2026-09-27 02:22:37'),('precisao_absoluta','Precisão Absoluta','Alcançou 150 XP acumulados.',0,15,'fa-bullseye','trofeu_alvo.png','#1cb0f6','raro','2026-09-27 02:22:37'),('primeiro_passo','Primeiro Passo','Concluiu sua primeira lição.',1,1,'fa-star','medalha_estrela.png','#ffd700','comum','2026-09-27 02:22:37'),('sequencia_30','Mês Épico','Alcançou 30 Dias de Fogo.',0,17,'fa-fire-flame-simple','trofeu_fogo30.png','#a855f7','lendario','2026-09-27 02:22:37'),('sequencia_7','Semana Perfeita','Alcançou 7 Dias de Fogo.',0,16,'fa-fire-flame-curved','trofeu_fogo7.png','#ef4444','epico','2026-09-27 02:22:37');
/*!40000 ALTER TABLE `catalogo_trofeus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_mensagens`
--

DROP TABLE IF EXISTS `chat_mensagens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_mensagens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remetente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `mensagem` text DEFAULT NULL,
  `status_leitura` varchar(20) DEFAULT 'nao_lido',
  `data_envio` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_mensagens`
--

LOCK TABLES `chat_mensagens` WRITE;
/*!40000 ALTER TABLE `chat_mensagens` DISABLE KEYS */;
INSERT INTO `chat_mensagens` VALUES (1,20,22,'oi','lido','2026-10-08 16:34:42'),(2,20,22,'oi','lido','2026-10-08 16:47:17'),(3,20,22,'tudo bem','lido','2026-10-08 16:47:21'),(4,20,22,'como vc  esta ?','lido','2026-10-08 16:47:29'),(5,20,22,'bora batalhar ?','lido','2026-10-08 16:47:35'),(6,20,22,'[DUEL_INVITE]','lido','2026-10-08 16:47:38'),(7,22,20,'[DUEL_INVITE]','nao_lido','2026-10-08 17:07:44'),(8,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:21'),(9,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:22'),(10,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:22'),(11,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:22'),(12,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:22'),(13,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:23'),(14,22,20,'[DUEL_INVITE]','nao_lido','2026-10-09 19:07:23'),(15,22,20,'jjnjnjjnjnjnjjnjnjnjn','nao_lido','2026-10-09 20:40:28'),(16,22,20,'oi','nao_lido','2026-10-09 20:44:00');
/*!40000 ALTER TABLE `chat_mensagens` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conquistas_usuario`
--

LOCK TABLES `conquistas_usuario` WRITE;
/*!40000 ALTER TABLE `conquistas_usuario` DISABLE KEYS */;
INSERT INTO `conquistas_usuario` VALUES (1,1,'perfeicao','2026-09-26 23:23:00'),(2,1,'primeiro_passo','2026-09-26 23:23:00'),(3,1,'precisao_absoluta','2026-09-26 23:23:00'),(4,20,'perfeicao','2026-10-08 12:47:00'),(5,20,'primeiro_passo','2026-10-08 12:47:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `licoes`
--

LOCK TABLES `licoes` WRITE;
/*!40000 ALTER TABLE `licoes` DISABLE KEYS */;
INSERT INTO `licoes` VALUES (1,1,1,'Introdução - Aula 1','Conteúdo sobre Introdução','// Código exemplo','2026-10-08 16:05:50'),(2,1,2,'Introdução - Aula 2','Conteúdo sobre Introdução','// Código exemplo','2026-10-08 16:05:50'),(3,1,3,'Introdução - Aula 3','Conteúdo sobre Introdução','// Código exemplo','2026-10-08 16:05:50'),(4,1,4,'Introdução - Aula 4','Conteúdo sobre Introdução','// Código exemplo','2026-10-08 16:05:50'),(5,1,5,'Introdução - Aula 5','Conteúdo sobre Introdução','// Código exemplo','2026-10-08 16:05:50'),(6,2,1,'Variáveis Parte 1 - Aula 1','Conteúdo sobre Variáveis Parte 1','// Código exemplo','2026-10-08 16:05:50'),(7,2,2,'Variáveis Parte 1 - Aula 2','Conteúdo sobre Variáveis Parte 1','// Código exemplo','2026-10-08 16:05:50'),(8,2,3,'Variáveis Parte 1 - Aula 3','Conteúdo sobre Variáveis Parte 1','// Código exemplo','2026-10-08 16:05:50'),(9,2,4,'Variáveis Parte 1 - Aula 4','Conteúdo sobre Variáveis Parte 1','// Código exemplo','2026-10-08 16:05:50'),(10,2,5,'Variáveis Parte 1 - Aula 5','Conteúdo sobre Variáveis Parte 1','// Código exemplo','2026-10-08 16:05:50'),(11,3,1,'Variáveis Parte 2 - Aula 1','Conteúdo sobre Variáveis Parte 2','// Código exemplo','2026-10-08 16:05:50'),(12,3,2,'Variáveis Parte 2 - Aula 2','Conteúdo sobre Variáveis Parte 2','// Código exemplo','2026-10-08 16:05:50'),(13,3,3,'Variáveis Parte 2 - Aula 3','Conteúdo sobre Variáveis Parte 2','// Código exemplo','2026-10-08 16:05:50'),(14,3,4,'Variáveis Parte 2 - Aula 4','Conteúdo sobre Variáveis Parte 2','// Código exemplo','2026-10-08 16:05:50'),(15,3,5,'Variáveis Parte 2 - Aula 5','Conteúdo sobre Variáveis Parte 2','// Código exemplo','2026-10-08 16:05:50'),(16,4,1,'Operadores Parte 1 - Aula 1','Conteúdo sobre Operadores Parte 1','// Código exemplo','2026-10-08 16:05:50'),(17,4,2,'Operadores Parte 1 - Aula 2','Conteúdo sobre Operadores Parte 1','// Código exemplo','2026-10-08 16:05:50'),(18,4,3,'Operadores Parte 1 - Aula 3','Conteúdo sobre Operadores Parte 1','// Código exemplo','2026-10-08 16:05:50'),(19,4,4,'Operadores Parte 1 - Aula 4','Conteúdo sobre Operadores Parte 1','// Código exemplo','2026-10-08 16:05:50'),(20,4,5,'Operadores Parte 1 - Aula 5','Conteúdo sobre Operadores Parte 1','// Código exemplo','2026-10-08 16:05:50'),(21,5,1,'Operadores Parte 2 - Aula 1','Conteúdo sobre Operadores Parte 2','// Código exemplo','2026-10-08 16:05:50'),(22,5,2,'Operadores Parte 2 - Aula 2','Conteúdo sobre Operadores Parte 2','// Código exemplo','2026-10-08 16:05:50'),(23,5,3,'Operadores Parte 2 - Aula 3','Conteúdo sobre Operadores Parte 2','// Código exemplo','2026-10-08 16:05:50'),(24,5,4,'Operadores Parte 2 - Aula 4','Conteúdo sobre Operadores Parte 2','// Código exemplo','2026-10-08 16:05:50'),(25,5,5,'Operadores Parte 2 - Aula 5','Conteúdo sobre Operadores Parte 2','// Código exemplo','2026-10-08 16:05:50'),(26,6,1,'Tomada de Decisão - Aula 1','Conteúdo sobre Tomada de Decisão','// Código exemplo','2026-10-08 16:05:50'),(27,6,2,'Tomada de Decisão - Aula 2','Conteúdo sobre Tomada de Decisão','// Código exemplo','2026-10-08 16:05:50'),(28,6,3,'Tomada de Decisão - Aula 3','Conteúdo sobre Tomada de Decisão','// Código exemplo','2026-10-08 16:05:50'),(29,6,4,'Tomada de Decisão - Aula 4','Conteúdo sobre Tomada de Decisão','// Código exemplo','2026-10-08 16:05:50'),(30,6,5,'Tomada de Decisão - Aula 5','Conteúdo sobre Tomada de Decisão','// Código exemplo','2026-10-08 16:05:50'),(31,7,1,'I/O Básico - Aula 1','Conteúdo sobre I/O Básico','// Código exemplo','2026-10-08 16:05:50'),(32,7,2,'I/O Básico - Aula 2','Conteúdo sobre I/O Básico','// Código exemplo','2026-10-08 16:05:50'),(33,7,3,'I/O Básico - Aula 3','Conteúdo sobre I/O Básico','// Código exemplo','2026-10-08 16:05:50'),(34,7,4,'I/O Básico - Aula 4','Conteúdo sobre I/O Básico','// Código exemplo','2026-10-08 16:05:50'),(35,7,5,'I/O Básico - Aula 5','Conteúdo sobre I/O Básico','// Código exemplo','2026-10-08 16:05:50'),(36,8,1,'Aplicativo de Calculadora - Aula 1','Conteúdo sobre Aplicativo de Calculadora','// Código exemplo','2026-10-08 16:05:50'),(37,8,2,'Aplicativo de Calculadora - Aula 2','Conteúdo sobre Aplicativo de Calculadora','// Código exemplo','2026-10-08 16:05:50'),(38,8,3,'Aplicativo de Calculadora - Aula 3','Conteúdo sobre Aplicativo de Calculadora','// Código exemplo','2026-10-08 16:05:50'),(39,8,4,'Aplicativo de Calculadora - Aula 4','Conteúdo sobre Aplicativo de Calculadora','// Código exemplo','2026-10-08 16:05:50'),(40,8,5,'Aplicativo de Calculadora - Aula 5','Conteúdo sobre Aplicativo de Calculadora','// Código exemplo','2026-10-08 16:05:50'),(41,9,1,'Laços - Aula 1','Conteúdo sobre Laços','// Código exemplo','2026-10-08 16:05:50'),(42,9,2,'Laços - Aula 2','Conteúdo sobre Laços','// Código exemplo','2026-10-08 16:05:50'),(43,9,3,'Laços - Aula 3','Conteúdo sobre Laços','// Código exemplo','2026-10-08 16:05:50'),(44,9,4,'Laços - Aula 4','Conteúdo sobre Laços','// Código exemplo','2026-10-08 16:05:50'),(45,9,5,'Laços - Aula 5','Conteúdo sobre Laços','// Código exemplo','2026-10-08 16:05:50'),(46,10,1,'Métodos e Funções - Aula 1','Conteúdo sobre Métodos e Funções','// Código exemplo','2026-10-08 16:05:50'),(47,10,2,'Métodos e Funções - Aula 2','Conteúdo sobre Métodos e Funções','// Código exemplo','2026-10-08 16:05:50'),(48,10,3,'Métodos e Funções - Aula 3','Conteúdo sobre Métodos e Funções','// Código exemplo','2026-10-08 16:05:50'),(49,10,4,'Métodos e Funções - Aula 4','Conteúdo sobre Métodos e Funções','// Código exemplo','2026-10-08 16:05:50'),(50,10,5,'Métodos e Funções - Aula 5','Conteúdo sobre Métodos e Funções','// Código exemplo','2026-10-08 16:05:50'),(51,11,1,'Funcionamento de Arrays - Aula 1','Conteúdo sobre Funcionamento de Arrays','// Código exemplo','2026-10-08 16:05:50'),(52,11,2,'Funcionamento de Arrays - Aula 2','Conteúdo sobre Funcionamento de Arrays','// Código exemplo','2026-10-08 16:05:50'),(53,11,3,'Funcionamento de Arrays - Aula 3','Conteúdo sobre Funcionamento de Arrays','// Código exemplo','2026-10-08 16:05:50'),(54,11,4,'Funcionamento de Arrays - Aula 4','Conteúdo sobre Funcionamento de Arrays','// Código exemplo','2026-10-08 16:05:50'),(55,11,5,'Funcionamento de Arrays - Aula 5','Conteúdo sobre Funcionamento de Arrays','// Código exemplo','2026-10-08 16:05:50'),(56,12,1,'Inteirando sobre Arrays - Aula 1','Conteúdo sobre Inteirando sobre Arrays','// Código exemplo','2026-10-08 16:05:50'),(57,12,2,'Inteirando sobre Arrays - Aula 2','Conteúdo sobre Inteirando sobre Arrays','// Código exemplo','2026-10-08 16:05:50'),(58,12,3,'Inteirando sobre Arrays - Aula 3','Conteúdo sobre Inteirando sobre Arrays','// Código exemplo','2026-10-08 16:05:50'),(59,12,4,'Inteirando sobre Arrays - Aula 4','Conteúdo sobre Inteirando sobre Arrays','// Código exemplo','2026-10-08 16:05:50'),(60,12,5,'Inteirando sobre Arrays - Aula 5','Conteúdo sobre Inteirando sobre Arrays','// Código exemplo','2026-10-08 16:05:50'),(61,13,1,'Operações com String - Aula 1','Conteúdo sobre Operações com String','// Código exemplo','2026-10-08 16:05:50'),(62,13,2,'Operações com String - Aula 2','Conteúdo sobre Operações com String','// Código exemplo','2026-10-08 16:05:50'),(63,13,3,'Operações com String - Aula 3','Conteúdo sobre Operações com String','// Código exemplo','2026-10-08 16:05:50'),(64,13,4,'Operações com String - Aula 4','Conteúdo sobre Operações com String','// Código exemplo','2026-10-08 16:05:50'),(65,13,5,'Operações com String - Aula 5','Conteúdo sobre Operações com String','// Código exemplo','2026-10-08 16:05:50'),(66,14,1,'Desafios Finais - Aula 1','Conteúdo sobre Desafios Finais','// Código exemplo','2026-10-08 16:05:50'),(67,14,2,'Desafios Finais - Aula 2','Conteúdo sobre Desafios Finais','// Código exemplo','2026-10-08 16:05:50'),(68,14,3,'Desafios Finais - Aula 3','Conteúdo sobre Desafios Finais','// Código exemplo','2026-10-08 16:05:50'),(69,14,4,'Desafios Finais - Aula 4','Conteúdo sobre Desafios Finais','// Código exemplo','2026-10-08 16:05:50'),(70,14,5,'Desafios Finais - Aula 5','Conteúdo sobre Desafios Finais','// Código exemplo','2026-10-08 16:05:50'),(71,15,1,'Certificado - Aula 1','Conteúdo sobre Certificado','// Código exemplo','2026-10-08 16:05:50'),(72,15,2,'Certificado - Aula 2','Conteúdo sobre Certificado','// Código exemplo','2026-10-08 16:05:50'),(73,15,3,'Certificado - Aula 3','Conteúdo sobre Certificado','// Código exemplo','2026-10-08 16:05:50'),(74,15,4,'Certificado - Aula 4','Conteúdo sobre Certificado','// Código exemplo','2026-10-08 16:05:50'),(75,15,5,'Certificado - Aula 5','Conteúdo sobre Certificado','// Código exemplo','2026-10-08 16:05:50'),(76,16,1,'Arrays Multidimensionais - Aula 1','Conteúdo sobre Arrays Multidimensionais','// Código exemplo','2026-10-08 16:05:50'),(77,16,2,'Arrays Multidimensionais - Aula 2','Conteúdo sobre Arrays Multidimensionais','// Código exemplo','2026-10-08 16:05:50'),(78,16,3,'Arrays Multidimensionais - Aula 3','Conteúdo sobre Arrays Multidimensionais','// Código exemplo','2026-10-08 16:05:50'),(79,16,4,'Arrays Multidimensionais - Aula 4','Conteúdo sobre Arrays Multidimensionais','// Código exemplo','2026-10-08 16:05:50'),(80,16,5,'Arrays Multidimensionais - Aula 5','Conteúdo sobre Arrays Multidimensionais','// Código exemplo','2026-10-08 16:05:50'),(81,17,1,'HashMap Parte 1 - Aula 1','Conteúdo sobre HashMap Parte 1','// Código exemplo','2026-10-08 16:05:50'),(82,17,2,'HashMap Parte 1 - Aula 2','Conteúdo sobre HashMap Parte 1','// Código exemplo','2026-10-08 16:05:50'),(83,17,3,'HashMap Parte 1 - Aula 3','Conteúdo sobre HashMap Parte 1','// Código exemplo','2026-10-08 16:05:50'),(84,17,4,'HashMap Parte 1 - Aula 4','Conteúdo sobre HashMap Parte 1','// Código exemplo','2026-10-08 16:05:50'),(85,17,5,'HashMap Parte 1 - Aula 5','Conteúdo sobre HashMap Parte 1','// Código exemplo','2026-10-08 16:05:50'),(86,18,1,'HashMap Parte 2 - Aula 1','Conteúdo sobre HashMap Parte 2','// Código exemplo','2026-10-08 16:05:50'),(87,18,2,'HashMap Parte 2 - Aula 2','Conteúdo sobre HashMap Parte 2','// Código exemplo','2026-10-08 16:05:50'),(88,18,3,'HashMap Parte 2 - Aula 3','Conteúdo sobre HashMap Parte 2','// Código exemplo','2026-10-08 16:05:50'),(89,18,4,'HashMap Parte 2 - Aula 4','Conteúdo sobre HashMap Parte 2','// Código exemplo','2026-10-08 16:05:50'),(90,18,5,'HashMap Parte 2 - Aula 5','Conteúdo sobre HashMap Parte 2','// Código exemplo','2026-10-08 16:05:50'),(91,19,1,'HashSet Parte 1 - Aula 1','Conteúdo sobre HashSet Parte 1','// Código exemplo','2026-10-08 16:05:50'),(92,19,2,'HashSet Parte 1 - Aula 2','Conteúdo sobre HashSet Parte 1','// Código exemplo','2026-10-08 16:05:50'),(93,19,3,'HashSet Parte 1 - Aula 3','Conteúdo sobre HashSet Parte 1','// Código exemplo','2026-10-08 16:05:50'),(94,19,4,'HashSet Parte 1 - Aula 4','Conteúdo sobre HashSet Parte 1','// Código exemplo','2026-10-08 16:05:50'),(95,19,5,'HashSet Parte 1 - Aula 5','Conteúdo sobre HashSet Parte 1','// Código exemplo','2026-10-08 16:05:50'),(96,20,1,'HashSet Parte 2 - Aula 1','Conteúdo sobre HashSet Parte 2','// Código exemplo','2026-10-08 16:05:50'),(97,20,2,'HashSet Parte 2 - Aula 2','Conteúdo sobre HashSet Parte 2','// Código exemplo','2026-10-08 16:05:50'),(98,20,3,'HashSet Parte 2 - Aula 3','Conteúdo sobre HashSet Parte 2','// Código exemplo','2026-10-08 16:05:50'),(99,20,4,'HashSet Parte 2 - Aula 4','Conteúdo sobre HashSet Parte 2','// Código exemplo','2026-10-08 16:05:50'),(100,20,5,'HashSet Parte 2 - Aula 5','Conteúdo sobre HashSet Parte 2','// Código exemplo','2026-10-08 16:05:50'),(101,21,1,'Fluxo de Controle Avançado - Aula 1','Conteúdo sobre Fluxo de Controle Avançado','// Código exemplo','2026-10-08 16:05:50'),(102,21,2,'Fluxo de Controle Avançado - Aula 2','Conteúdo sobre Fluxo de Controle Avançado','// Código exemplo','2026-10-08 16:05:50'),(103,21,3,'Fluxo de Controle Avançado - Aula 3','Conteúdo sobre Fluxo de Controle Avançado','// Código exemplo','2026-10-08 16:05:50'),(104,21,4,'Fluxo de Controle Avançado - Aula 4','Conteúdo sobre Fluxo de Controle Avançado','// Código exemplo','2026-10-08 16:05:50'),(105,21,5,'Fluxo de Controle Avançado - Aula 5','Conteúdo sobre Fluxo de Controle Avançado','// Código exemplo','2026-10-08 16:05:50'),(106,22,1,'Tratamento Básico de Erros - Aula 1','Conteúdo sobre Tratamento Básico de Erros','// Código exemplo','2026-10-08 16:05:50'),(107,22,2,'Tratamento Básico de Erros - Aula 2','Conteúdo sobre Tratamento Básico de Erros','// Código exemplo','2026-10-08 16:05:50'),(108,22,3,'Tratamento Básico de Erros - Aula 3','Conteúdo sobre Tratamento Básico de Erros','// Código exemplo','2026-10-08 16:05:50'),(109,22,4,'Tratamento Básico de Erros - Aula 4','Conteúdo sobre Tratamento Básico de Erros','// Código exemplo','2026-10-08 16:05:50'),(110,22,5,'Tratamento Básico de Erros - Aula 5','Conteúdo sobre Tratamento Básico de Erros','// Código exemplo','2026-10-08 16:05:50'),(111,23,1,'Operações Avançadas com Strings - Aula 1','Conteúdo sobre Operações Avançadas com Strings','// Código exemplo','2026-10-08 16:05:50'),(112,23,2,'Operações Avançadas com Strings - Aula 2','Conteúdo sobre Operações Avançadas com Strings','// Código exemplo','2026-10-08 16:05:50'),(113,23,3,'Operações Avançadas com Strings - Aula 3','Conteúdo sobre Operações Avançadas com Strings','// Código exemplo','2026-10-08 16:05:50'),(114,23,4,'Operações Avançadas com Strings - Aula 4','Conteúdo sobre Operações Avançadas com Strings','// Código exemplo','2026-10-08 16:05:50'),(115,23,5,'Operações Avançadas com Strings - Aula 5','Conteúdo sobre Operações Avançadas com Strings','// Código exemplo','2026-10-08 16:05:50'),(116,24,1,'Projeto Contador de Palavras Únicas - Aula 1','Conteúdo sobre Projeto Contador de Palavras Únicas','// Código exemplo','2026-10-08 16:05:50'),(117,24,2,'Projeto Contador de Palavras Únicas - Aula 2','Conteúdo sobre Projeto Contador de Palavras Únicas','// Código exemplo','2026-10-08 16:05:50'),(118,24,3,'Projeto Contador de Palavras Únicas - Aula 3','Conteúdo sobre Projeto Contador de Palavras Únicas','// Código exemplo','2026-10-08 16:05:50'),(119,24,4,'Projeto Contador de Palavras Únicas - Aula 4','Conteúdo sobre Projeto Contador de Palavras Únicas','// Código exemplo','2026-10-08 16:05:50'),(120,24,5,'Projeto Contador de Palavras Únicas - Aula 5','Conteúdo sobre Projeto Contador de Palavras Únicas','// Código exemplo','2026-10-08 16:05:50'),(121,25,1,'Data e Hora - Aula 1','Conteúdo sobre Data e Hora','// Código exemplo','2026-10-08 16:05:50'),(122,25,2,'Data e Hora - Aula 2','Conteúdo sobre Data e Hora','// Código exemplo','2026-10-08 16:05:50'),(123,25,3,'Data e Hora - Aula 3','Conteúdo sobre Data e Hora','// Código exemplo','2026-10-08 16:05:50'),(124,25,4,'Data e Hora - Aula 4','Conteúdo sobre Data e Hora','// Código exemplo','2026-10-08 16:05:50'),(125,25,5,'Data e Hora - Aula 5','Conteúdo sobre Data e Hora','// Código exemplo','2026-10-08 16:05:50'),(126,26,1,'Certificado da Seção 1 e 2 - Aula 1','Conteúdo sobre Certificado da Seção 1 e 2','// Código exemplo','2026-10-08 16:05:50'),(127,26,2,'Certificado da Seção 1 e 2 - Aula 2','Conteúdo sobre Certificado da Seção 1 e 2','// Código exemplo','2026-10-08 16:05:50'),(128,26,3,'Certificado da Seção 1 e 2 - Aula 3','Conteúdo sobre Certificado da Seção 1 e 2','// Código exemplo','2026-10-08 16:05:50'),(129,26,4,'Certificado da Seção 1 e 2 - Aula 4','Conteúdo sobre Certificado da Seção 1 e 2','// Código exemplo','2026-10-08 16:05:50'),(130,26,5,'Certificado da Seção 1 e 2 - Aula 5','Conteúdo sobre Certificado da Seção 1 e 2','// Código exemplo','2026-10-08 16:05:50'),(131,27,1,'Fundamentos de POO - Aula 1','Conteúdo sobre Fundamentos de POO','// Código exemplo','2026-10-08 16:05:50'),(132,27,2,'Fundamentos de POO - Aula 2','Conteúdo sobre Fundamentos de POO','// Código exemplo','2026-10-08 16:05:50'),(133,27,3,'Fundamentos de POO - Aula 3','Conteúdo sobre Fundamentos de POO','// Código exemplo','2026-10-08 16:05:50'),(134,27,4,'Fundamentos de POO - Aula 4','Conteúdo sobre Fundamentos de POO','// Código exemplo','2026-10-08 16:05:50'),(135,27,5,'Fundamentos de POO - Aula 5','Conteúdo sobre Fundamentos de POO','// Código exemplo','2026-10-08 16:05:50'),(136,28,1,'Modificadores de Acesso e Encapsulamento - Aula 1','Conteúdo sobre Modificadores de Acesso e Encapsulamento','// Código exemplo','2026-10-08 16:05:50'),(137,28,2,'Modificadores de Acesso e Encapsulamento - Aula 2','Conteúdo sobre Modificadores de Acesso e Encapsulamento','// Código exemplo','2026-10-08 16:05:50'),(138,28,3,'Modificadores de Acesso e Encapsulamento - Aula 3','Conteúdo sobre Modificadores de Acesso e Encapsulamento','// Código exemplo','2026-10-08 16:05:50'),(139,28,4,'Modificadores de Acesso e Encapsulamento - Aula 4','Conteúdo sobre Modificadores de Acesso e Encapsulamento','// Código exemplo','2026-10-08 16:05:50'),(140,28,5,'Modificadores de Acesso e Encapsulamento - Aula 5','Conteúdo sobre Modificadores de Acesso e Encapsulamento','// Código exemplo','2026-10-08 16:05:50'),(141,29,1,'Prioridades de Classe e Membros Estáticos - Aula 1','Conteúdo sobre Prioridades de Classe e Membros Estáticos','// Código exemplo','2026-10-08 16:05:50'),(142,29,2,'Prioridades de Classe e Membros Estáticos - Aula 2','Conteúdo sobre Prioridades de Classe e Membros Estáticos','// Código exemplo','2026-10-08 16:05:50'),(143,29,3,'Prioridades de Classe e Membros Estáticos - Aula 3','Conteúdo sobre Prioridades de Classe e Membros Estáticos','// Código exemplo','2026-10-08 16:05:50'),(144,29,4,'Prioridades de Classe e Membros Estáticos - Aula 4','Conteúdo sobre Prioridades de Classe e Membros Estáticos','// Código exemplo','2026-10-08 16:05:50'),(145,29,5,'Prioridades de Classe e Membros Estáticos - Aula 5','Conteúdo sobre Prioridades de Classe e Membros Estáticos','// Código exemplo','2026-10-08 16:05:50'),(146,30,1,'Herança - Aula 1','Conteúdo sobre Herança','// Código exemplo','2026-10-08 16:05:50'),(147,30,2,'Herança - Aula 2','Conteúdo sobre Herança','// Código exemplo','2026-10-08 16:05:50'),(148,30,3,'Herança - Aula 3','Conteúdo sobre Herança','// Código exemplo','2026-10-08 16:05:50'),(149,30,4,'Herança - Aula 4','Conteúdo sobre Herança','// Código exemplo','2026-10-08 16:05:50'),(150,30,5,'Herança - Aula 5','Conteúdo sobre Herança','// Código exemplo','2026-10-08 16:05:50'),(151,31,1,'Polimorfismo - Aula 1','Conteúdo sobre Polimorfismo','// Código exemplo','2026-10-08 16:05:50'),(152,31,2,'Polimorfismo - Aula 2','Conteúdo sobre Polimorfismo','// Código exemplo','2026-10-08 16:05:50'),(153,31,3,'Polimorfismo - Aula 3','Conteúdo sobre Polimorfismo','// Código exemplo','2026-10-08 16:05:50'),(154,31,4,'Polimorfismo - Aula 4','Conteúdo sobre Polimorfismo','// Código exemplo','2026-10-08 16:05:50'),(155,31,5,'Polimorfismo - Aula 5','Conteúdo sobre Polimorfismo','// Código exemplo','2026-10-08 16:05:50'),(156,32,1,'Interfaces e Classes Abstratas - Aula 1','Conteúdo sobre Interfaces e Classes Abstratas','// Código exemplo','2026-10-08 16:05:50'),(157,32,2,'Interfaces e Classes Abstratas - Aula 2','Conteúdo sobre Interfaces e Classes Abstratas','// Código exemplo','2026-10-08 16:05:50'),(158,32,3,'Interfaces e Classes Abstratas - Aula 3','Conteúdo sobre Interfaces e Classes Abstratas','// Código exemplo','2026-10-08 16:05:50'),(159,32,4,'Interfaces e Classes Abstratas - Aula 4','Conteúdo sobre Interfaces e Classes Abstratas','// Código exemplo','2026-10-08 16:05:50'),(160,32,5,'Interfaces e Classes Abstratas - Aula 5','Conteúdo sobre Interfaces e Classes Abstratas','// Código exemplo','2026-10-08 16:05:50'),(161,33,1,'Métodos Especiais e Classe Object - Aula 1','Conteúdo sobre Métodos Especiais e Classe Object','// Código exemplo','2026-10-08 16:05:50'),(162,33,2,'Métodos Especiais e Classe Object - Aula 2','Conteúdo sobre Métodos Especiais e Classe Object','// Código exemplo','2026-10-08 16:05:50'),(163,33,3,'Métodos Especiais e Classe Object - Aula 3','Conteúdo sobre Métodos Especiais e Classe Object','// Código exemplo','2026-10-08 16:05:50'),(164,33,4,'Métodos Especiais e Classe Object - Aula 4','Conteúdo sobre Métodos Especiais e Classe Object','// Código exemplo','2026-10-08 16:05:50'),(165,33,5,'Métodos Especiais e Classe Object - Aula 5','Conteúdo sobre Métodos Especiais e Classe Object','// Código exemplo','2026-10-08 16:05:50'),(166,34,1,'Conceitos Avançados de OOP (Generics) - Aula 1','Conteúdo sobre Conceitos Avançados de OOP (Generics)','// Código exemplo','2026-10-08 16:05:50'),(167,34,2,'Conceitos Avançados de OOP (Generics) - Aula 2','Conteúdo sobre Conceitos Avançados de OOP (Generics)','// Código exemplo','2026-10-08 16:05:50'),(168,34,3,'Conceitos Avançados de OOP (Generics) - Aula 3','Conteúdo sobre Conceitos Avançados de OOP (Generics)','// Código exemplo','2026-10-08 16:05:50'),(169,34,4,'Conceitos Avançados de OOP (Generics) - Aula 4','Conteúdo sobre Conceitos Avançados de OOP (Generics)','// Código exemplo','2026-10-08 16:05:50'),(170,34,5,'Conceitos Avançados de OOP (Generics) - Aula 5','Conteúdo sobre Conceitos Avançados de OOP (Generics)','// Código exemplo','2026-10-08 16:05:50'),(171,35,1,'Tratamento de Exceção em PPO - Aula 1','Conteúdo sobre Tratamento de Exceção em PPO','// Código exemplo','2026-10-08 16:05:50'),(172,35,2,'Tratamento de Exceção em PPO - Aula 2','Conteúdo sobre Tratamento de Exceção em PPO','// Código exemplo','2026-10-08 16:05:50'),(173,35,3,'Tratamento de Exceção em PPO - Aula 3','Conteúdo sobre Tratamento de Exceção em PPO','// Código exemplo','2026-10-08 16:05:50'),(174,35,4,'Tratamento de Exceção em PPO - Aula 4','Conteúdo sobre Tratamento de Exceção em PPO','// Código exemplo','2026-10-08 16:05:50'),(175,35,5,'Tratamento de Exceção em PPO - Aula 5','Conteúdo sobre Tratamento de Exceção em PPO','// Código exemplo','2026-10-08 16:05:50'),(176,36,1,'Padrões de Projeto Parte 1 - Aula 1','Conteúdo sobre Padrões de Projeto Parte 1','// Código exemplo','2026-10-08 16:05:50'),(177,36,2,'Padrões de Projeto Parte 1 - Aula 2','Conteúdo sobre Padrões de Projeto Parte 1','// Código exemplo','2026-10-08 16:05:50'),(178,36,3,'Padrões de Projeto Parte 1 - Aula 3','Conteúdo sobre Padrões de Projeto Parte 1','// Código exemplo','2026-10-08 16:05:50'),(179,36,4,'Padrões de Projeto Parte 1 - Aula 4','Conteúdo sobre Padrões de Projeto Parte 1','// Código exemplo','2026-10-08 16:05:50'),(180,36,5,'Padrões de Projeto Parte 1 - Aula 5','Conteúdo sobre Padrões de Projeto Parte 1','// Código exemplo','2026-10-08 16:05:50'),(181,37,1,'Padrões de Projeto Parte 2 - Aula 1','Conteúdo sobre Padrões de Projeto Parte 2','// Código exemplo','2026-10-08 16:05:50'),(182,37,2,'Padrões de Projeto Parte 2 - Aula 2','Conteúdo sobre Padrões de Projeto Parte 2','// Código exemplo','2026-10-08 16:05:50'),(183,37,3,'Padrões de Projeto Parte 2 - Aula 3','Conteúdo sobre Padrões de Projeto Parte 2','// Código exemplo','2026-10-08 16:05:50'),(184,37,4,'Padrões de Projeto Parte 2 - Aula 4','Conteúdo sobre Padrões de Projeto Parte 2','// Código exemplo','2026-10-08 16:05:50'),(185,37,5,'Padrões de Projeto Parte 2 - Aula 5','Conteúdo sobre Padrões de Projeto Parte 2','// Código exemplo','2026-10-08 16:05:50'),(186,38,1,'Projeto Gerenciamento de Biblioteca - Aula 1','Conteúdo sobre Projeto Gerenciamento de Biblioteca','// Código exemplo','2026-10-08 16:05:50'),(187,38,2,'Projeto Gerenciamento de Biblioteca - Aula 2','Conteúdo sobre Projeto Gerenciamento de Biblioteca','// Código exemplo','2026-10-08 16:05:50'),(188,38,3,'Projeto Gerenciamento de Biblioteca - Aula 3','Conteúdo sobre Projeto Gerenciamento de Biblioteca','// Código exemplo','2026-10-08 16:05:50'),(189,38,4,'Projeto Gerenciamento de Biblioteca - Aula 4','Conteúdo sobre Projeto Gerenciamento de Biblioteca','// Código exemplo','2026-10-08 16:05:50'),(190,38,5,'Projeto Gerenciamento de Biblioteca - Aula 5','Conteúdo sobre Projeto Gerenciamento de Biblioteca','// Código exemplo','2026-10-08 16:05:50'),(191,39,1,'Desafio Final - Aula 1','Conteúdo sobre Desafio Final','// Código exemplo','2026-10-08 16:05:50'),(192,39,2,'Desafio Final - Aula 2','Conteúdo sobre Desafio Final','// Código exemplo','2026-10-08 16:05:50'),(193,39,3,'Desafio Final - Aula 3','Conteúdo sobre Desafio Final','// Código exemplo','2026-10-08 16:05:50'),(194,39,4,'Desafio Final - Aula 4','Conteúdo sobre Desafio Final','// Código exemplo','2026-10-08 16:05:50'),(195,39,5,'Desafio Final - Aula 5','Conteúdo sobre Desafio Final','// Código exemplo','2026-10-08 16:05:50'),(196,40,1,'Certificado de tudo - Aula 1','Conteúdo sobre Certificado de tudo','// Código exemplo','2026-10-08 16:05:50'),(197,40,2,'Certificado de tudo - Aula 2','Conteúdo sobre Certificado de tudo','// Código exemplo','2026-10-08 16:05:50'),(198,40,3,'Certificado de tudo - Aula 3','Conteúdo sobre Certificado de tudo','// Código exemplo','2026-10-08 16:05:50'),(199,40,4,'Certificado de tudo - Aula 4','Conteúdo sobre Certificado de tudo','// Código exemplo','2026-10-08 16:05:50'),(200,40,5,'Certificado de tudo - Aula 5','Conteúdo sobre Certificado de tudo','// Código exemplo','2026-10-08 16:05:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ligas_grupos`
--

LOCK TABLES `ligas_grupos` WRITE;
/*!40000 ALTER TABLE `ligas_grupos` DISABLE KEYS */;
INSERT INTO `ligas_grupos` VALUES (1,'bronze','2026-09-21',30,'2026-09-26 23:22:38'),(2,'prata','2026-09-21',30,'2026-09-26 23:22:38'),(3,'ouro','2026-09-21',30,'2026-09-26 23:22:38'),(4,'diamante','2026-09-21',30,'2026-09-26 23:22:38'),(5,'mestre','2026-09-21',30,'2026-09-26 23:22:38'),(6,'bronze','2026-09-28',30,'2026-10-04 21:01:36'),(7,'prata','2026-09-28',30,'2026-10-04 21:01:36'),(8,'ouro','2026-09-28',30,'2026-10-04 21:01:36'),(9,'diamante','2026-09-28',30,'2026-10-04 21:01:36'),(10,'mestre','2026-09-28',30,'2026-10-04 21:01:36'),(11,'bronze','2026-09-28',30,'2026-10-04 21:24:43'),(12,'prata','2026-09-28',30,'2026-10-04 21:24:43'),(13,'ouro','2026-09-28',30,'2026-10-04 21:24:43'),(14,'diamante','2026-09-28',30,'2026-10-04 21:24:43'),(15,'mestre','2026-09-28',30,'2026-10-04 21:24:43'),(16,'bronze','2026-10-05',30,'2026-10-06 07:56:11'),(17,'bronze','2026-10-01',30,'2026-10-06 21:07:08');
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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ligas_usuario`
--

LOCK TABLES `ligas_usuario` WRITE;
/*!40000 ALTER TABLE `ligas_usuario` DISABLE KEYS */;
INSERT INTO `ligas_usuario` VALUES (1,1,'bronze',1,200,NULL,'2026-09-26 23:22:38'),(2,2,'bronze',1,100,NULL,'2026-09-26 23:22:38'),(3,3,'bronze',1,150,NULL,'2026-09-26 23:22:38'),(4,4,'bronze',1,200,NULL,'2026-09-26 23:22:38'),(5,5,'bronze',1,250,NULL,'2026-09-26 23:22:38'),(6,3,'bronze',0,45,NULL,'2026-10-04 21:01:36'),(7,7,'bronze',0,25,NULL,'2026-10-04 21:01:36'),(8,8,'bronze',0,15,NULL,'2026-10-04 21:01:36'),(9,4,'prata',0,140,NULL,'2026-10-04 21:01:36'),(10,9,'prata',0,180,NULL,'2026-10-04 21:01:36'),(11,10,'prata',0,110,NULL,'2026-10-04 21:01:36'),(12,5,'ouro',0,320,NULL,'2026-10-04 21:01:36'),(13,11,'ouro',0,410,NULL,'2026-10-04 21:01:36'),(14,12,'ouro',0,290,NULL,'2026-10-04 21:01:36'),(15,13,'ouro',0,260,NULL,'2026-10-04 21:01:36'),(16,14,'diamante',0,650,NULL,'2026-10-04 21:01:36'),(17,15,'diamante',0,780,NULL,'2026-10-04 21:01:36'),(18,16,'diamante',0,590,NULL,'2026-10-04 21:01:36'),(19,1,'mestre',0,950,NULL,'2026-10-04 21:01:36'),(20,2,'mestre',0,1200,NULL,'2026-10-04 21:01:36'),(21,17,'mestre',0,1550,NULL,'2026-10-04 21:01:36'),(22,18,'mestre',0,1180,NULL,'2026-10-04 21:01:36'),(23,19,'mestre',0,1340,NULL,'2026-10-04 21:01:36'),(24,21,'bronze',6,0,NULL,'2026-10-04 21:06:18'),(39,20,'bronze',16,0,NULL,'2026-10-06 07:56:11'),(40,20,'bronze',17,50,NULL,'2026-10-06 21:07:08'),(41,22,'bronze',17,0,NULL,'2026-10-08 16:23:23');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `missoes_diarias_usuario`
--

LOCK TABLES `missoes_diarias_usuario` WRITE;
/*!40000 ALTER TABLE `missoes_diarias_usuario` DISABLE KEYS */;
INSERT INTO `missoes_diarias_usuario` VALUES (1,1,'2026-09-26',50,1,1,'2026-09-27 02:23:00','2026-09-27 02:23:00'),(2,20,'2026-10-08',50,1,1,'2026-10-08 15:47:00','2026-10-08 15:47:00');
/*!40000 ALTER TABLE `missoes_diarias_usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos_conexao`
--

DROP TABLE IF EXISTS `pedidos_conexao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos_conexao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remetente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `data_criacao` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos_conexao`
--

LOCK TABLES `pedidos_conexao` WRITE;
/*!40000 ALTER TABLE `pedidos_conexao` DISABLE KEYS */;
INSERT INTO `pedidos_conexao` VALUES (1,22,20,'aceito','2026-10-08 16:34:14');
/*!40000 ALTER TABLE `pedidos_conexao` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perguntas`
--

LOCK TABLES `perguntas` WRITE;
/*!40000 ALTER TABLE `perguntas` DISABLE KEYS */;
INSERT INTO `perguntas` VALUES (1,1,'Pergunta 1 da aula 1 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(2,1,'Pergunta 2 da aula 1 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(3,2,'Pergunta 1 da aula 2 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(4,2,'Pergunta 2 da aula 2 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(5,3,'Pergunta 1 da aula 3 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(6,3,'Pergunta 2 da aula 3 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(7,4,'Pergunta 1 da aula 4 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(8,4,'Pergunta 2 da aula 4 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(9,5,'Pergunta 1 da aula 5 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(10,5,'Pergunta 2 da aula 5 sobre Introdução','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(11,6,'Pergunta 1 da aula 1 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(12,6,'Pergunta 2 da aula 1 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(13,7,'Pergunta 1 da aula 2 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(14,7,'Pergunta 2 da aula 2 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(15,8,'Pergunta 1 da aula 3 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(16,8,'Pergunta 2 da aula 3 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(17,9,'Pergunta 1 da aula 4 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(18,9,'Pergunta 2 da aula 4 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(19,10,'Pergunta 1 da aula 5 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(20,10,'Pergunta 2 da aula 5 sobre Variáveis Parte 1','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(21,11,'Pergunta 1 da aula 1 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(22,11,'Pergunta 2 da aula 1 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(23,12,'Pergunta 1 da aula 2 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(24,12,'Pergunta 2 da aula 2 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(25,13,'Pergunta 1 da aula 3 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(26,13,'Pergunta 2 da aula 3 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(27,14,'Pergunta 1 da aula 4 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(28,14,'Pergunta 2 da aula 4 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50'),(29,15,'Pergunta 1 da aula 5 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','A','2026-10-08 16:05:50'),(30,15,'Pergunta 2 da aula 5 sobre Variáveis Parte 2','multipla_escolha','Alternativa 1','Alternativa 2','Alternativa 3','B','2026-10-08 16:05:50');
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progresso_usuario`
--

LOCK TABLES `progresso_usuario` WRITE;
/*!40000 ALTER TABLE `progresso_usuario` DISABLE KEYS */;
INSERT INTO `progresso_usuario` VALUES (1,1,1,'corrente',2,'2026-09-27 02:22:38'),(2,1,2,'trancado',0,'2026-09-27 02:22:38'),(3,1,3,'trancado',0,'2026-09-27 02:22:38'),(4,1,4,'trancado',0,'2026-09-27 02:22:38'),(5,1,5,'trancado',0,'2026-09-27 02:22:38'),(6,2,1,'corrente',0,'2026-09-27 02:22:38'),(7,2,2,'trancado',0,'2026-09-27 02:22:38'),(8,2,3,'trancado',0,'2026-09-27 02:22:38'),(9,2,4,'trancado',0,'2026-09-27 02:22:38'),(10,2,5,'trancado',0,'2026-09-27 02:22:38'),(11,3,1,'corrente',0,'2026-09-27 02:22:38'),(12,3,2,'trancado',0,'2026-09-27 02:22:38'),(13,3,3,'trancado',0,'2026-09-27 02:22:38'),(14,3,4,'trancado',0,'2026-09-27 02:22:38'),(15,3,5,'trancado',0,'2026-09-27 02:22:38'),(16,4,1,'corrente',0,'2026-09-27 02:22:38'),(17,4,2,'trancado',0,'2026-09-27 02:22:38'),(18,4,3,'trancado',0,'2026-09-27 02:22:38'),(19,4,4,'trancado',0,'2026-09-27 02:22:38'),(20,4,5,'trancado',0,'2026-09-27 02:22:38'),(21,5,1,'corrente',0,'2026-09-27 02:22:38'),(22,5,2,'trancado',0,'2026-09-27 02:22:38'),(23,5,3,'trancado',0,'2026-09-27 02:22:38'),(24,5,4,'trancado',0,'2026-09-27 02:22:38'),(25,5,5,'trancado',0,'2026-09-27 02:22:38'),(26,20,1,'corrente',1,'2026-10-08 15:47:00'),(27,20,2,'trancado',0,'2026-10-05 00:02:59'),(28,20,3,'trancado',0,'2026-10-05 00:02:59'),(29,20,4,'trancado',0,'2026-10-05 00:02:59'),(30,20,5,'trancado',0,'2026-10-05 00:02:59'),(31,21,1,'corrente',0,'2026-10-05 00:03:58'),(32,21,2,'trancado',0,'2026-10-05 00:03:58'),(33,21,3,'trancado',0,'2026-10-05 00:03:58'),(34,21,4,'trancado',0,'2026-10-05 00:03:58'),(35,21,5,'trancado',0,'2026-10-05 00:03:58'),(36,22,1,'corrente',0,'2026-10-08 19:23:23'),(37,22,2,'trancado',0,'2026-10-08 19:23:23'),(38,22,3,'trancado',0,'2026-10-08 19:23:23'),(39,22,4,'trancado',0,'2026-10-08 19:23:23'),(40,22,5,'trancado',0,'2026-10-08 19:23:23');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_trofeus`
--

LOCK TABLES `user_trofeus` WRITE;
/*!40000 ALTER TABLE `user_trofeus` DISABLE KEYS */;
INSERT INTO `user_trofeus` VALUES (1,1,'perfeicao','2026-09-27 02:23:00'),(2,1,'primeiro_passo','2026-09-27 02:23:00'),(3,1,'precisao_absoluta','2026-09-27 02:23:00'),(4,20,'perfeicao','2026-10-08 15:47:00'),(5,20,'primeiro_passo','2026-10-08 15:47:00'),(6,22,'batalha_irmaos','2026-10-08 19:48:38');
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador OPUS','gabriel@opus.com','$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi',2500,12,'Intermediário',NULL,'2026-09-27 02:22:37',0,3,NULL,'2026-09-26','admin','#1cb0f6','admin_opus',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'Gabriel Freitas','admin@opus.com','$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi',3800,15,'Avançado',NULL,'2026-09-27 02:22:37',30,3,NULL,NULL,'admin','#58cc02','gabriel_dev',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(3,'Ana Silva','ana.silva@email.com','$2y$10$8Fb33G1p8YdvgMXcIyd5COwo7K6O0MFhvffFN3HvOqlFi5jhEhsTO',180,3,'Iniciante',NULL,'2026-09-27 02:22:37',2,3,NULL,NULL,'comum','#ff4b4b','aninha_code',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'Lucas Mendes','lucas@email.com','$2y$10$8Fb33G1p8YdvgMXcIyd5COwo7K6O0MFhvffFN3HvOqlFi5jhEhsTO',450,2,'Iniciante',NULL,'2026-09-27 02:22:37',4,2,NULL,NULL,'comum','#ce82ff','lucas_java',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'Mariana Costa','mariana@email.com','$2y$10$8Fb33G1p8YdvgMXcIyd5COwo7K6O0MFhvffFN3HvOqlFi5jhEhsTO',950,4,'Intermediário',NULL,'2026-09-27 02:22:37',8,3,NULL,NULL,'comum','#ff9600','mari_dev',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,'Pedro Rocha','pedro.rocha@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',90,1,'Iniciante',NULL,'2026-10-05 00:01:36',1,1,NULL,NULL,'comum','#1cb0f6','pedro_dev',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,'Júlia Santos','julia.santos@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',50,0,'Iniciante',NULL,'2026-10-05 00:01:36',0,0,NULL,NULL,'comum','#ff9600','ju_santos',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,'Felipe Oliveira','felipe.oliveira@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',530,5,'Intermediário',NULL,'2026-10-05 00:01:36',6,3,NULL,NULL,'comum','#00cd9c','felipe_oli',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,'Camila Ribeiro','camila.ribeiro@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',390,3,'Iniciante',NULL,'2026-10-05 00:01:36',3,3,NULL,NULL,'comum','#e11d48','cami_rib',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,'Bruno Carvalho','bruno.carvalho@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',1100,8,'Intermediário',NULL,'2026-10-05 00:01:36',12,2,NULL,NULL,'comum','#d97706','bruno_code',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,'Larissa Souza','larissa.souza@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',880,6,'Intermediário',NULL,'2026-10-05 00:01:36',7,3,NULL,NULL,'comum','#9333ea','lari_souza',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(13,'Rodrigo Lima','rodrigo.lima@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',820,6,'Intermediário',NULL,'2026-10-05 00:01:36',5,1,NULL,NULL,'comum','#2563eb','rodrigo_l',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(14,'Rafael Duarte','rafael.duarte@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',2100,10,'Avançado',NULL,'2026-10-05 00:01:36',18,3,NULL,NULL,'comum','#06b6d4','rafa_duarte',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(15,'Beatriz Almeida','beatriz.almeida@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',2450,11,'Avançado',NULL,'2026-10-05 00:01:36',21,3,NULL,NULL,'comum','#3b82f6','bea_almeida',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(16,'Thiago Ferreira','thiago.ferreira@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',1950,9,'Intermediário',NULL,'2026-10-05 00:01:36',15,2,NULL,NULL,'comum','#84cc16','thiago_f',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(17,'Helena Martins','helena.martins@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',4800,18,'Avançado',NULL,'2026-10-05 00:01:36',45,3,NULL,NULL,'comum','#a855f7','helena_m',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(18,'Vinicius Prado','vinicius.prado@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',3900,14,'Avançado',NULL,'2026-10-05 00:01:36',28,3,NULL,NULL,'comum','#ec4899','vini_prado',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(19,'Sophia Castro','sophia.castro@email.com','$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a',4350,16,'Avançado',NULL,'2026-10-05 00:01:36',35,3,NULL,NULL,'comum','#6366f1','sophia_c',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(20,'Gab Santos','gabsantosbag@gmail.com','',50,2,'Iniciante',NULL,'2026-10-05 00:02:59',1,3,NULL,'2026-10-08','comum','#1cb0f6',NULL,'106201833585413825315','https://lh3.googleusercontent.com/a/ACg8ocLdaUy7rcG2C8yeI1163uwixoCqwFzMxpfd_sM6epC2w34oUmA=s96-c','https://github.com/freitasdgab','https://www.instagram.com/freitasdgabi/','','','gabsantosbag@gmail.com',NULL,'opus ','https://github.com/freitasdgab/OPUS','','','',''),(21,'lucas','lucas@opus.com','Admin123@',0,0,'Iniciante',NULL,'2026-10-05 00:03:58',0,3,NULL,NULL,'comum','#1cb0f6',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(22,'Gabi Freitas','gabrielefr3itas@gmail.com','',500,0,'Iniciante',NULL,'2026-10-08 19:23:23',0,3,NULL,NULL,'comum','#1cb0f6',NULL,'104488639218720630991','https://lh3.googleusercontent.com/a/ACg8ocLFS12oG26YBx4b8bzF8ApUR4YsFynj20RfggXc6xKwADx9IxY1=s96-c',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-10 21:55:19


-- ==============================================================================
-- STORED PROCEDURES & TRIGGERS DO OPUS
-- ==============================================================================

-- Dumping routines for database 'opus'
--
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
ALTER DATABASE `opus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
/*!50003 SET @saved_sql_mode = IFNULL(@@sql_mode, "") */ ;
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
/*!50003 SET sql_mode              = IFNULL(@saved_sql_mode, @@sql_mode) */ ;
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


SET FOREIGN_KEY_CHECKS = 1;
