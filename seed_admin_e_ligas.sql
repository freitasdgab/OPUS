-- ==============================================================================
-- OPUS GAMIFICAÇÃO - SCRIPT DE POVOAMENTO (SEED)
-- Criação de Administrador e Usuários de Teste em Diferentes Ligas
-- Compatível com MariaDB / MySQL (XAMPP / phpMyAdmin)
-- ==============================================================================

USE `opus`;

-- Garante coluna atualizado_em em progresso_usuario caso não exista
ALTER TABLE `progresso_usuario` ADD COLUMN IF NOT EXISTS `atualizado_em` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ------------------------------------------------------------------------------
-- 1. CRIAÇÃO OU ATUALIZAÇÃO DO USUÁRIO ADMINISTRADOR
-- Credenciais:
--   E-mail: admin@opus.com
--   Senha:  admin123
-- ------------------------------------------------------------------------------
INSERT INTO `usuarios` (
    `id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`
) VALUES (
    1,
    'Administrador OPUS',
    'admin@opus.com',
    '$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi', -- Hash de 'admin123'
    'admin',
    2500,
    12,
    14,
    3,
    'Avançado',
    '#58cc02',
    'admin_opus'
) ON DUPLICATE KEY UPDATE
    `nome` = VALUES(`nome`),
    `senha` = VALUES(`senha`),
    `nivel_acesso` = 'admin',
    `xp` = VALUES(`xp`),
    `trofeus` = VALUES(`trofeus`),
    `dias_fogo` = VALUES(`dias_fogo`),
    `vidas` = 3,
    `username` = VALUES(`username`);

-- Administrador Secundário / Desenvolvedor
INSERT INTO `usuarios` (
    `id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`
) VALUES (
    2,
    'Gabriel Freitas',
    'gabriel@opus.com',
    '$2y$10$42ShEHGq1zO9srfpO0BvZ.Bq/3caA5Brpc/in5.S3H9juaMNyc7Qi', -- Hash de 'admin123'
    'admin',
    3800,
    15,
    30,
    3,
    'Avançado',
    '#1cb0f6',
    'gabriel_dev'
) ON DUPLICATE KEY UPDATE
    `nome` = VALUES(`nome`),
    `senha` = VALUES(`senha`),
    `nivel_acesso` = 'admin',
    `xp` = VALUES(`xp`),
    `trofeus` = VALUES(`trofeus`),
    `dias_fogo` = VALUES(`dias_fogo`),
    `vidas` = 3,
    `username` = VALUES(`username`);

-- ------------------------------------------------------------------------------
-- 2. CRIAÇÃO DE USUÁRIOS DE TESTE EM DIFERENTES LIGAS
-- Senha padrão para todos os alunos de teste: 123456
-- Hash: $2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a
-- ------------------------------------------------------------------------------

-- BRONZE
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`) VALUES
(3, 'Ana Silva', 'ana.silva@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 180, 2, 2, 3, 'Iniciante', '#ff4b4b', 'ana_silva'),
(7, 'Pedro Rocha', 'pedro.rocha@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 90, 1, 1, 1, 'Iniciante', '#1cb0f6', 'pedro_dev'),
(8, 'Júlia Santos', 'julia.santos@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 50, 0, 0, 0, 'Iniciante', '#ff9600', 'ju_santos')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `xp` = VALUES(`xp`), `vidas` = VALUES(`vidas`), `dias_fogo` = VALUES(`dias_fogo`);

-- PRATA
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`) VALUES
(4, 'Lucas Mendes', 'lucas@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 450, 4, 4, 2, 'Iniciante', '#ce82ff', 'lucas_mendes'),
(9, 'Felipe Oliveira', 'felipe.oliveira@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 530, 5, 6, 3, 'Intermediário', '#00cd9c', 'felipe_oli'),
(10, 'Camila Ribeiro', 'camila.ribeiro@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 390, 3, 3, 3, 'Iniciante', '#e11d48', 'cami_rib')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `xp` = VALUES(`xp`), `vidas` = VALUES(`vidas`), `dias_fogo` = VALUES(`dias_fogo`);

-- OURO
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`) VALUES
(5, 'Mariana Costa', 'mariana@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 950, 7, 8, 3, 'Intermediário', '#ff9600', 'mari_costa'),
(11, 'Bruno Carvalho', 'bruno.carvalho@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 1100, 8, 12, 2, 'Intermediário', '#d97706', 'bruno_code'),
(12, 'Larissa Souza', 'larissa.souza@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 880, 6, 7, 3, 'Intermediário', '#9333ea', 'lari_souza'),
(13, 'Rodrigo Lima', 'rodrigo.lima@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 820, 6, 5, 1, 'Intermediário', '#2563eb', 'rodrigo_l')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `xp` = VALUES(`xp`), `vidas` = VALUES(`vidas`), `dias_fogo` = VALUES(`dias_fogo`);

-- DIAMANTE
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`) VALUES
(14, 'Rafael Duarte', 'rafael.duarte@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 2100, 10, 18, 3, 'Avançado', '#06b6d4', 'rafa_duarte'),
(15, 'Beatriz Almeida', 'beatriz.almeida@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 2450, 11, 21, 3, 'Avançado', '#3b82f6', 'bea_almeida'),
(16, 'Thiago Ferreira', 'thiago.ferreira@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 1950, 9, 15, 2, 'Intermediário', '#84cc16', 'thiago_f')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `xp` = VALUES(`xp`), `vidas` = VALUES(`vidas`), `dias_fogo` = VALUES(`dias_fogo`);

-- MESTRE
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `nivel_acesso`, `xp`, `trofeus`, `dias_fogo`, `vidas`, `dificuldade`, `cor_fundo`, `username`) VALUES
(17, 'Helena Martins', 'helena.martins@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 4800, 18, 45, 3, 'Avançado', '#a855f7', 'helena_m'),
(18, 'Vinicius Prado', 'vinicius.prado@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 3900, 14, 28, 3, 'Avançado', '#ec4899', 'vini_prado'),
(19, 'Sophia Castro', 'sophia.castro@email.com', '$2y$10$RzAV2gjzmmm/hCnm2iBuNOsrSpaIoq0MwyGWQKIGJWH6y9LmyQj0a', 'comum', 4350, 16, 35, 3, 'Avançado', '#6366f1', 'sophia_c')
ON DUPLICATE KEY UPDATE `nome` = VALUES(`nome`), `xp` = VALUES(`xp`), `vidas` = VALUES(`vidas`), `dias_fogo` = VALUES(`dias_fogo`);

-- ------------------------------------------------------------------------------
-- 3. GRUPOS DE LIGA (SEMANA ATUAL)
-- ------------------------------------------------------------------------------
SET @semana_ref = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

INSERT IGNORE INTO `ligas_grupos` (`divisao`, `semana_ref`, `capacidade`) VALUES
('bronze', @semana_ref, 30),
('prata', @semana_ref, 30),
('ouro', @semana_ref, 30),
('diamante', @semana_ref, 30),
('mestre', @semana_ref, 30);

-- ------------------------------------------------------------------------------
-- 4. ALOCAÇÃO DOS USUÁRIOS NAS LIGAS
-- ------------------------------------------------------------------------------
-- Bronze (Usuários 3, 7, 8)
INSERT INTO `ligas_usuario` (`usuario_id`, `divisao`, `xp_semana`) VALUES
(3, 'bronze', 45),
(7, 'bronze', 25),
(8, 'bronze', 15)
ON DUPLICATE KEY UPDATE `divisao` = VALUES(`divisao`), `xp_semana` = VALUES(`xp_semana`);

-- Prata (Usuários 4, 9, 10)
INSERT INTO `ligas_usuario` (`usuario_id`, `divisao`, `xp_semana`) VALUES
(4, 'prata', 140),
(9, 'prata', 180),
(10, 'prata', 110)
ON DUPLICATE KEY UPDATE `divisao` = VALUES(`divisao`), `xp_semana` = VALUES(`xp_semana`);

-- Ouro (Usuários 5, 11, 12, 13)
INSERT INTO `ligas_usuario` (`usuario_id`, `divisao`, `xp_semana`) VALUES
(5, 'ouro', 320),
(11, 'ouro', 410),
(12, 'ouro', 290),
(13, 'ouro', 260)
ON DUPLICATE KEY UPDATE `divisao` = VALUES(`divisao`), `xp_semana` = VALUES(`xp_semana`);

-- Diamante (Usuários 14, 15, 16)
INSERT INTO `ligas_usuario` (`usuario_id`, `divisao`, `xp_semana`) VALUES
(14, 'diamante', 650),
(15, 'diamante', 780),
(16, 'diamante', 590)
ON DUPLICATE KEY UPDATE `divisao` = VALUES(`divisao`), `xp_semana` = VALUES(`xp_semana`);

-- Mestre (Admins 1, 2 e Usuários 17, 18, 19)
INSERT INTO `ligas_usuario` (`usuario_id`, `divisao`, `xp_semana`) VALUES
(1, 'mestre', 950),
(2, 'mestre', 1200),
(17, 'mestre', 1550),
(18, 'mestre', 1180),
(19, 'mestre', 1340)
ON DUPLICATE KEY UPDATE `divisao` = VALUES(`divisao`), `xp_semana` = VALUES(`xp_semana`);

-- Associa grupo_id correspondente
UPDATE `ligas_usuario` lu
JOIN `ligas_grupos` lg ON lg.divisao = lu.divisao AND lg.semana_ref = @semana_ref
SET lu.grupo_id = lg.id;

-- ------------------------------------------------------------------------------
-- 5. PROGRESSO DOS CAPÍTULOS (ALIMENTA GRÁFICO DO PAINEL ADMIN)
-- ------------------------------------------------------------------------------
-- Usuários Bronze: Capítulo 1 corrente
INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`) VALUES
(3, 1, 'corrente', 1), (3, 2, 'trancado', 0), (3, 3, 'trancado', 0), (3, 4, 'trancado', 0), (3, 5, 'trancado', 0),
(7, 1, 'corrente', 1), (7, 2, 'trancado', 0), (7, 3, 'trancado', 0), (7, 4, 'trancado', 0), (7, 5, 'trancado', 0),
(8, 1, 'corrente', 0), (8, 2, 'trancado', 0), (8, 3, 'trancado', 0), (8, 4, 'trancado', 0), (8, 5, 'trancado', 0)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `licoes_concluidas` = VALUES(`licoes_concluidas`);

-- Usuários Prata: Capítulo 1 completo, Capítulo 2 corrente
INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`) VALUES
(4, 1, 'completo', 3), (4, 2, 'corrente', 1), (4, 3, 'trancado', 0), (4, 4, 'trancado', 0), (4, 5, 'trancado', 0),
(9, 1, 'completo', 3), (9, 2, 'corrente', 2), (9, 3, 'trancado', 0), (9, 4, 'trancado', 0), (9, 5, 'trancado', 0),
(10, 1, 'completo', 3), (10, 2, 'corrente', 1), (10, 3, 'trancado', 0), (10, 4, 'trancado', 0), (10, 5, 'trancado', 0)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `licoes_concluidas` = VALUES(`licoes_concluidas`);

-- Usuários Ouro: Capítulos 1 e 2 completos, Capítulo 3 corrente
INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`) VALUES
(5, 1, 'completo', 3), (5, 2, 'completo', 3), (5, 3, 'corrente', 1), (5, 4, 'trancado', 0), (5, 5, 'trancado', 0),
(11, 1, 'completo', 3), (11, 2, 'completo', 3), (11, 3, 'corrente', 2), (11, 4, 'trancado', 0), (11, 5, 'trancado', 0),
(12, 1, 'completo', 3), (12, 2, 'completo', 3), (12, 3, 'corrente', 1), (12, 4, 'trancado', 0), (12, 5, 'trancado', 0),
(13, 1, 'completo', 3), (13, 2, 'completo', 3), (13, 3, 'corrente', 1), (13, 4, 'trancado', 0), (13, 5, 'trancado', 0)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `licoes_concluidas` = VALUES(`licoes_concluidas`);

-- Usuários Diamante: Capítulos 1, 2 e 3 completos, Capítulo 4 corrente
INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`) VALUES
(14, 1, 'completo', 3), (14, 2, 'completo', 3), (14, 3, 'completo', 3), (14, 4, 'corrente', 1), (14, 5, 'trancado', 0),
(15, 1, 'completo', 3), (15, 2, 'completo', 3), (15, 3, 'completo', 3), (15, 4, 'corrente', 2), (15, 5, 'trancado', 0),
(16, 1, 'completo', 3), (16, 2, 'completo', 3), (16, 3, 'completo', 3), (16, 4, 'corrente', 1), (16, 5, 'trancado', 0)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `licoes_concluidas` = VALUES(`licoes_concluidas`);

-- Usuários Mestre: Capítulos 1, 2, 3 e 4 completos (e 5 completo para Admins/Sophia)
INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`) VALUES
(1, 1, 'completo', 3), (1, 2, 'completo', 3), (1, 3, 'completo', 3), (1, 4, 'completo', 3), (1, 5, 'corrente', 1),
(2, 1, 'completo', 3), (2, 2, 'completo', 3), (2, 3, 'completo', 3), (2, 4, 'completo', 3), (2, 5, 'completo', 3),
(17, 1, 'completo', 3), (17, 2, 'completo', 3), (17, 3, 'completo', 3), (17, 4, 'completo', 3), (17, 5, 'corrente', 1),
(18, 1, 'completo', 3), (18, 2, 'completo', 3), (18, 3, 'completo', 3), (18, 4, 'completo', 3), (18, 5, 'corrente', 1),
(19, 1, 'completo', 3), (19, 2, 'completo', 3), (19, 3, 'completo', 3), (19, 4, 'completo', 3), (19, 5, 'completo', 3)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`), `licoes_concluidas` = VALUES(`licoes_concluidas`);
