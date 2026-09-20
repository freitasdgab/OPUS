-- =====================================================================
-- OPUS — Migração: lógica de negócio movida do PHP para o banco
-- =====================================================================
-- Este arquivo:
--   1. Corrige um bug de schema (coluna `resultado` faltando em
--      ligas_historico, usada pelo cron semanal de ligas).
--   2. Cria a tabela `catalogo_trofeus`, unificando os nomes/descrições
--      de conquistas que antes viviam duplicados e desalinhados em
--      back/api_conquistas.php e back/validar_respostas.php.
--   3. Cria as stored procedures que agora concentram a lógica de
--      negócio (vidas, sequência de fogo, ligas, missões, baú de
--      recompensa, correção de lição e conquistas).
--
-- Rode este arquivo inteiro no phpMyAdmin (aba SQL) depois de já ter
-- o banco `opus` importado.
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1. Corrige ligas_historico (faltava a coluna usada pelo cron semanal)
-- ---------------------------------------------------------------------
ALTER TABLE `ligas_historico`
  ADD COLUMN `resultado` VARCHAR(20) DEFAULT NULL AFTER `divisao_nova`;

-- ---------------------------------------------------------------------
-- 2. Catálogo único de troféus/conquistas
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `catalogo_trofeus` (
  `slug` VARCHAR(80) NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `descricao` VARCHAR(255) DEFAULT NULL,
  `listado` TINYINT(1) NOT NULL DEFAULT 0,
  `ordem` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- listado=1: os 8 que já apareciam na tela "Ver Troféus" (perfil.php),
-- na mesma ordem/nome/descrição de antes (back/api_conquistas.php).
-- listado=0: os 9 que já eram concedidos por back/validar_respostas.php
-- mas não tinham entrada na tela de troféus (isso já era assim antes;
-- não mudei esse comportamento, só tirei o texto do PHP).
INSERT INTO `catalogo_trofeus` (`slug`, `nome`, `descricao`, `listado`, `ordem`) VALUES
('primeiro_passo',    'Primeiro Passo',        'Concluiu sua primeira lição.', 1, 1),
('perfeicao',         'Mente Brilhante',       'Acertou 3/3 em um desafio.', 1, 2),
('capitulo_1',        'Fundamentos',           'Terminou o Capítulo 1.', 1, 3),
('capitulo_2',        'Caminhos Lógicos',      'Dominou as Estruturas de Decisão no Cap. 2.', 1, 4),
('capitulo_3',        'Mestre da Repetição',   'Dominou os Loops no Capítulo 3.', 1, 5),
('capitulo_4',        'Senhor dos Arrays',     'Dominou Arrays e Matrizes no Capítulo 4.', 1, 6),
('capitulo_5',        'Arquiteto Java',        'Concluiu POO no Capítulo 5. Você é o mestre!', 1, 7),
('fogo_3',            'Em Chamas',             'Alcançou 3 Dias de Fogo.', 1, 8),
('primeiro_codigo',   'Primeiro Código',       'Concluiu o Capítulo 1.', 0, 9),
('mestre_escolhas',   'Mestre das Escolhas',   'Concluiu o Capítulo 2.', 0, 10),
('loop_infinito',     'Loop Infinito',         'Concluiu o Capítulo 3.', 0, 11),
('arquitetura_dados', 'Arquitetura de Dados',  'Concluiu o Capítulo 4.', 0, 12),
('poo_master',        'Mestre da POO',         'Concluiu o Capítulo 5.', 0, 13),
('lenda_opus',        'Lenda do OPUS',         'Concluiu os 5 capítulos.', 0, 14),
('precisao_absoluta', 'Precisão Absoluta',     'Alcançou 150 XP.', 0, 15),
('sequencia_7',       'Semana Perfeita',       'Alcançou 7 Dias de Fogo.', 0, 16),
('sequencia_30',      'Mês Épico',             'Alcançou 30 Dias de Fogo.', 0, 17)
ON DUPLICATE KEY UPDATE nome = VALUES(nome), descricao = VALUES(descricao), listado = VALUES(listado), ordem = VALUES(ordem);

-- ---------------------------------------------------------------------
-- 3. Garante o admin padrão (antes era feito a cada request via PHP)
-- ---------------------------------------------------------------------
UPDATE `usuarios` SET `nivel_acesso` = 'admin'
  WHERE (`id` = 1 OR LOWER(`email`) = 'admin@gmail.com') AND `nivel_acesso` = 'comum';

DELIMITER $$

-- =====================================================================
-- sp_sincronizar_jogador
-- Regenera vidas (progressivo, +1 a cada 5h) e zera a sequência de fogo
-- se o usuário ficou mais de 1 dia sem atividade. Substitui a lógica
-- que vivia em back/jogador_status.php (opus_sincronizar_jogador).
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_sincronizar_jogador $$
CREATE PROCEDURE sp_sincronizar_jogador(IN p_user_id INT)
proc_sync: BEGIN
    DECLARE v_vidas TINYINT;
    DECLARE v_proxima DATETIME;
    DECLARE v_ultima_atividade DATE;
    DECLARE v_dias_fogo INT;
    DECLARE v_agora DATETIME DEFAULT NOW();

    SELECT vidas, vidas_proxima_em, ultima_atividade, dias_fogo
      INTO v_vidas, v_proxima, v_ultima_atividade, v_dias_fogo
      FROM usuarios WHERE id = p_user_id
      FOR UPDATE;

    IF v_vidas IS NULL THEN
        LEAVE proc_sync;
    END IF;

    IF v_vidas > 3 THEN SET v_vidas = 3; END IF;
    IF v_vidas < 0 THEN SET v_vidas = 0; END IF;

    -- Regenera vidas progressivamente (+1 a cada 5h), podendo pular
    -- várias vidas de uma vez se o usuário ficou muito tempo offline
    IF v_vidas < 3 THEN
        IF v_proxima IS NOT NULL THEN
            WHILE v_vidas < 3 AND v_proxima <= v_agora DO
                SET v_vidas = v_vidas + 1;
                IF v_vidas >= 3 THEN
                    SET v_proxima = NULL;
                ELSE
                    SET v_proxima = DATE_ADD(v_proxima, INTERVAL 5 HOUR);
                END IF;
            END WHILE;
        ELSE
            SET v_proxima = DATE_ADD(v_agora, INTERVAL 5 HOUR);
        END IF;

        UPDATE usuarios SET vidas = v_vidas, vidas_proxima_em = v_proxima WHERE id = p_user_id;
    END IF;

    IF v_vidas >= 3 THEN
        SET v_proxima = NULL;
    END IF;

    -- Zera a sequência de fogo se pulou mais de 1 dia sem atividade
    IF v_ultima_atividade IS NOT NULL
       AND v_ultima_atividade <> CURDATE()
       AND v_ultima_atividade <> DATE_SUB(CURDATE(), INTERVAL 1 DAY)
       AND v_dias_fogo > 0 THEN
        SET v_dias_fogo = 0;
        UPDATE usuarios SET dias_fogo = 0 WHERE id = p_user_id;
    END IF;

    SELECT xp, trofeus, v_dias_fogo AS dias_fogo, v_vidas AS vidas, v_proxima AS vidas_proxima_em,
           nome, foto_perfil, nivel_acesso
      FROM usuarios WHERE id = p_user_id;
END $$

-- =====================================================================
-- sp_perder_vida
-- Sincroniza (regenera) e então desconta 1 vida. Retorna o total atual.
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_perder_vida $$
CREATE PROCEDURE sp_perder_vida(IN p_user_id INT)
proc_perder: BEGIN
    DECLARE v_vidas TINYINT;
    DECLARE v_proxima DATETIME;

    CALL sp_sincronizar_jogador(p_user_id);

    SELECT vidas, vidas_proxima_em INTO v_vidas, v_proxima FROM usuarios WHERE id = p_user_id;

    IF v_vidas IS NULL OR v_vidas <= 0 THEN
        SELECT COALESCE(v_vidas, 0) AS vidas;
        LEAVE proc_perder;
    END IF;

    SET v_vidas = v_vidas - 1;

    IF v_vidas < 3 AND v_proxima IS NULL THEN
        SET v_proxima = DATE_ADD(NOW(), INTERVAL 5 HOUR);
        UPDATE usuarios SET vidas = v_vidas, vidas_proxima_em = v_proxima WHERE id = p_user_id;
    ELSE
        UPDATE usuarios SET vidas = v_vidas WHERE id = p_user_id;
    END IF;

    SELECT v_vidas AS vidas;
END $$

-- =====================================================================
-- sp_atualizar_fogo
-- Incrementa a sequência diária (dias_fogo) e concede o troféu de 3
-- dias. Substitui back/jogador_status.php (opus_atualizar_fogo).
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_atualizar_fogo $$
CREATE PROCEDURE sp_atualizar_fogo(IN p_user_id INT)
proc_fogo: BEGIN
    DECLARE v_seq INT;
    DECLARE v_ultima DATE;
    DECLARE v_hoje DATE DEFAULT CURDATE();
    DECLARE v_ontem DATE DEFAULT DATE_SUB(CURDATE(), INTERVAL 1 DAY);

    SELECT dias_fogo, ultima_atividade INTO v_seq, v_ultima FROM usuarios WHERE id = p_user_id;

    IF v_ultima = v_hoje THEN
        SELECT v_seq AS dias_fogo;
        LEAVE proc_fogo;
    END IF;

    IF v_ultima = v_ontem THEN
        SET v_seq = v_seq + 1;
    ELSE
        SET v_seq = 1;
    END IF;

    UPDATE usuarios SET dias_fogo = v_seq, ultima_atividade = v_hoje WHERE id = p_user_id;

    IF v_seq >= 3 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'fogo_3');
        UPDATE usuarios SET trofeus = (SELECT COUNT(*) FROM user_trofeus WHERE user_id = p_user_id) WHERE id = p_user_id;
    END IF;

    SELECT v_seq AS dias_fogo;
END $$

-- =====================================================================
-- sp_registrar_missao_progresso
-- Substitui back/missoes_logic.php (missoes_registrar_progresso).
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_registrar_missao_progresso $$
CREATE PROCEDURE sp_registrar_missao_progresso(IN p_usuario_id INT, IN p_xp_ganho INT, IN p_acertos INT)
BEGIN
    DECLARE v_perfeita TINYINT DEFAULT IF(p_acertos >= 3, 1, 0);
    DECLARE v_feita TINYINT DEFAULT IF(p_acertos > 0, 1, 0);

    INSERT INTO missoes_diarias_usuario (usuario_id, data_ref, xp_ganho, licoes_concluidas, licoes_perfeitas)
    VALUES (p_usuario_id, CURDATE(), p_xp_ganho, v_feita, v_perfeita)
    ON DUPLICATE KEY UPDATE
        xp_ganho = xp_ganho + VALUES(xp_ganho),
        licoes_concluidas = licoes_concluidas + VALUES(licoes_concluidas),
        licoes_perfeitas = licoes_perfeitas + VALUES(licoes_perfeitas);
END $$

-- =====================================================================
-- sp_liga_registrar_xp
-- Garante que o usuário tem um registro de liga válido para a semana
-- atual (criando grupo se precisar) e soma XP ao total da semana.
-- Substitui back/ligas_logic.php (liga_garantir_usuario +
-- liga_obter_ou_criar_grupo + liga_registrar_xp).
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_liga_registrar_xp $$
CREATE PROCEDURE sp_liga_registrar_xp(IN p_usuario_id INT, IN p_xp INT)
BEGIN
    DECLARE v_semana DATE;
    DECLARE v_divisao VARCHAR(20) DEFAULT 'bronze';
    DECLARE v_grupo_id INT DEFAULT NULL;
    DECLARE v_grupo_semana DATE;
    DECLARE v_capacidade INT DEFAULT 30;
    DECLARE v_existe TINYINT DEFAULT 0;

    -- Segunda-feira da semana atual
    SET v_semana = DATE_SUB(CURDATE(), INTERVAL (WEEKDAY(CURDATE())) DAY);

    SELECT 1, divisao, grupo_id INTO v_existe, v_divisao, v_grupo_id
      FROM ligas_usuario WHERE usuario_id = p_usuario_id;

    IF v_grupo_id IS NOT NULL THEN
        SELECT semana_ref INTO v_grupo_semana FROM ligas_grupos WHERE id = v_grupo_id;
    END IF;

    IF v_grupo_id IS NULL OR v_grupo_semana IS NULL OR v_grupo_semana <> v_semana THEN
        SELECT g.id INTO v_grupo_id
          FROM ligas_grupos g
          LEFT JOIN ligas_usuario u ON u.grupo_id = g.id
          WHERE g.divisao = v_divisao AND g.semana_ref = v_semana
          GROUP BY g.id
          HAVING COUNT(u.id) < v_capacidade
          ORDER BY g.id ASC
          LIMIT 1;

        IF v_grupo_id IS NULL THEN
            INSERT INTO ligas_grupos (divisao, semana_ref, capacidade) VALUES (v_divisao, v_semana, v_capacidade);
            SET v_grupo_id = LAST_INSERT_ID();
        END IF;
    END IF;

    -- Usuário novo: insere já com o grupo certo (grupo_id é NOT NULL na
    -- tabela, então não dá pra inserir a linha antes de saber o grupo —
    -- esse era um bug já presente no código PHP original)
    IF v_existe = 0 THEN
        INSERT INTO ligas_usuario (usuario_id, divisao, grupo_id, xp_semana) VALUES (p_usuario_id, v_divisao, v_grupo_id, p_xp);
    ELSE
        UPDATE ligas_usuario SET grupo_id = v_grupo_id, xp_semana = xp_semana + p_xp WHERE usuario_id = p_usuario_id;
    END IF;
END $$

-- =====================================================================
-- sp_verificar_conquistas (uso interno)
-- Confere as 9 regras de conquista por XP/capítulo/sequência que antes
-- viviam em back/validar_respostas.php (verificar_conquistas) e concede
-- as que forem novas. Retorna o NOME da primeira conquista nova (ou
-- string vazia), igual ao comportamento original.
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_verificar_conquistas $$
CREATE PROCEDURE sp_verificar_conquistas(
    IN p_user_id INT,
    IN p_capitulo INT,
    IN p_capitulo_concluido TINYINT,
    OUT p_nova_conquista VARCHAR(100)
)
BEGIN
    DECLARE v_xp INT;
    DECLARE v_dias_fogo INT;
    DECLARE v_caps_completos INT;
    DECLARE v_slug VARCHAR(80);

    SET p_nova_conquista = '';

    SELECT xp, dias_fogo INTO v_xp, v_dias_fogo FROM usuarios WHERE id = p_user_id;
    SELECT COUNT(*) INTO v_caps_completos FROM progresso_usuario WHERE usuario_id = p_user_id AND status = 'completo';

    -- primeiro_codigo (capítulo 1 concluído)
    IF p_capitulo = 1 AND p_capitulo_concluido = 1
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'primeiro_codigo') THEN
        SET v_slug = 'primeiro_codigo';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- mestre_escolhas (capítulo 2 concluído)
    IF p_capitulo = 2 AND p_capitulo_concluido = 1
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'mestre_escolhas') THEN
        SET v_slug = 'mestre_escolhas';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- loop_infinito (capítulo 3 concluído)
    IF p_capitulo = 3 AND p_capitulo_concluido = 1
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'loop_infinito') THEN
        SET v_slug = 'loop_infinito';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- arquitetura_dados (capítulo 4 concluído)
    IF p_capitulo = 4 AND p_capitulo_concluido = 1
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'arquitetura_dados') THEN
        SET v_slug = 'arquitetura_dados';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- poo_master (capítulo 5 concluído)
    IF p_capitulo = 5 AND p_capitulo_concluido = 1
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'poo_master') THEN
        SET v_slug = 'poo_master';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- lenda_opus (todos os 5 capítulos completos)
    IF v_caps_completos >= 5
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'lenda_opus') THEN
        SET v_slug = 'lenda_opus';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- precisao_absoluta (xp >= 150)
    IF v_xp >= 150
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'precisao_absoluta') THEN
        SET v_slug = 'precisao_absoluta';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- sequencia_7 (dias_fogo >= 7)
    IF v_dias_fogo >= 7
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'sequencia_7') THEN
        SET v_slug = 'sequencia_7';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;

    -- sequencia_30 (dias_fogo >= 30)
    IF v_dias_fogo >= 30
       AND NOT EXISTS (SELECT 1 FROM user_trofeus WHERE user_id = p_user_id AND trofeu_slug = 'sequencia_30') THEN
        SET v_slug = 'sequencia_30';
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug);
        IF p_nova_conquista = '' THEN
            SELECT nome INTO p_nova_conquista FROM catalogo_trofeus WHERE slug = v_slug;
        END IF;
    END IF;
END $$

-- =====================================================================
-- sp_corrigir_licao
-- Núcleo do fluxo de correção de lição: avança progresso, conclui/
-- destrava capítulo, dá XP/troféu, registra liga e missão, atualiza a
-- sequência de fogo e confere conquistas. Substitui a maior parte de
-- back/validar_respostas.php. A correção das respostas em si (comparar
-- cada alternativa escolhida com a correta) continua no PHP, que só
-- chama esta procedure quando o usuário acerta tudo.
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_corrigir_licao $$
CREATE PROCEDURE sp_corrigir_licao(
    IN p_user_id INT,
    IN p_capitulo INT,
    IN p_licao INT,
    IN p_acertos INT
)
proc_corrigir: BEGIN
    DECLARE v_licoes_feitas INT;
    DECLARE v_novas_licoes INT;
    DECLARE v_capitulo_concluido TINYINT DEFAULT 0;
    DECLARE v_bau_liberado TINYINT DEFAULT 0;
    DECLARE v_proximo INT;
    DECLARE v_nova_conquista VARCHAR(100) DEFAULT '';
    DECLARE v_progresso_existe TINYINT DEFAULT 0;

    SELECT 1, licoes_concluidas INTO v_progresso_existe, v_licoes_feitas
      FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_capitulo;

    IF v_progresso_existe = 0 OR p_licao <> v_licoes_feitas + 1 THEN
        -- Lição repetida ou fora de ordem: não avança nada (mesmo
        -- comportamento do código original)
        SELECT 0 AS avancou, 0 AS capitulo_concluido, 0 AS bau_liberado, '' AS nova_conquista;
        LEAVE proc_corrigir;
    END IF;

    SET v_novas_licoes = v_licoes_feitas + 1;

    IF v_novas_licoes >= 5 THEN
        UPDATE progresso_usuario SET licoes_concluidas = 5, status = 'completo'
          WHERE usuario_id = p_user_id AND unidade_numero = p_capitulo;

        SET v_proximo = p_capitulo + 1;
        UPDATE progresso_usuario SET status = 'corrente'
          WHERE usuario_id = p_user_id AND unidade_numero = v_proximo AND status = 'trancado';

        SET v_capitulo_concluido = 1;
    ELSE
        UPDATE progresso_usuario SET licoes_concluidas = v_novas_licoes
          WHERE usuario_id = p_user_id AND unidade_numero = p_capitulo;

        IF v_novas_licoes = 3 THEN
            SET v_bau_liberado = 1;
        END IF;
    END IF;

    UPDATE usuarios SET xp = xp + 50, trofeus = trofeus + 1 WHERE id = p_user_id;

    CALL sp_liga_registrar_xp(p_user_id, 50);
    CALL sp_registrar_missao_progresso(p_user_id, 50, p_acertos);
    CALL sp_atualizar_fogo(p_user_id);
    CALL sp_verificar_conquistas(p_user_id, p_capitulo, v_capitulo_concluido, v_nova_conquista);

    SELECT 1 AS avancou, v_capitulo_concluido AS capitulo_concluido,
           v_bau_liberado AS bau_liberado, v_nova_conquista AS nova_conquista;
END $$

-- =====================================================================
-- sp_resgatar_bau
-- Resgate do baú de recompensa de meio de trilha (libera com 3 lições
-- feitas na unidade). Substitui back/resgatar_bau.php.
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_resgatar_bau $$
CREATE PROCEDURE sp_resgatar_bau(IN p_user_id INT, IN p_unidade_numero INT)
proc_bau: BEGIN
    DECLARE v_ja_resgatado INT DEFAULT 0;
    DECLARE v_licoes_feitas INT DEFAULT 0;
    DECLARE v_status_unidade VARCHAR(20) DEFAULT 'trancado';
    DECLARE v_vidas_atual TINYINT;
    DECLARE v_xp_base INT DEFAULT 50;
    DECLARE v_vidas_ganhas INT DEFAULT 0;
    DECLARE v_detalhe VARCHAR(255) DEFAULT '';

    SELECT COUNT(*) INTO v_ja_resgatado FROM bau_recompensas
      WHERE usuario_id = p_user_id AND unidade_numero = p_unidade_numero;

    IF v_ja_resgatado > 0 THEN
        SELECT 'ja_resgatado' AS status, 0 AS xp_ganho, 0 AS vidas_ganhas, '' AS detalhe;
        LEAVE proc_bau;
    END IF;

    SELECT licoes_concluidas, status INTO v_licoes_feitas, v_status_unidade
      FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_unidade_numero;

    IF v_licoes_feitas IS NULL THEN SET v_licoes_feitas = 0; END IF;
    IF v_status_unidade IS NULL THEN SET v_status_unidade = 'trancado'; END IF;

    IF v_licoes_feitas < 3 AND v_status_unidade <> 'completo' THEN
        SELECT 'requisito_pendente' AS status, 0 AS xp_ganho, 0 AS vidas_ganhas, '' AS detalhe;
        LEAVE proc_bau;
    END IF;

    CALL sp_sincronizar_jogador(p_user_id);
    SELECT vidas INTO v_vidas_atual FROM usuarios WHERE id = p_user_id;

    IF v_vidas_atual < 3 THEN
        SET v_vidas_ganhas = 1;
        SET v_vidas_atual = v_vidas_atual + 1;
        SET v_detalhe = '+1 Coração Bônus recuperado!';

        IF v_vidas_atual >= 3 THEN
            UPDATE usuarios SET vidas = 3, vidas_proxima_em = NULL, xp = xp + v_xp_base WHERE id = p_user_id;
        ELSE
            UPDATE usuarios SET vidas = v_vidas_atual, xp = xp + v_xp_base WHERE id = p_user_id;
        END IF;
    ELSE
        SET v_xp_base = 100;
        SET v_detalhe = 'Vidas cheias! +50 XP bônus adicional concedido!';
        UPDATE usuarios SET xp = xp + v_xp_base WHERE id = p_user_id;
    END IF;

    CALL sp_liga_registrar_xp(p_user_id, v_xp_base);
    CALL sp_registrar_missao_progresso(p_user_id, v_xp_base, 0);

    INSERT INTO bau_recompensas (usuario_id, unidade_numero, tipo_recompensa, xp_ganho, vidas_ganhas)
      VALUES (p_user_id, p_unidade_numero, 'recompensa_meio_trilha', v_xp_base, v_vidas_ganhas);

    SELECT 'ok' AS status, v_xp_base AS xp_ganho, v_vidas_ganhas AS vidas_ganhas, v_detalhe AS detalhe;
END $$

-- =====================================================================
-- sp_processar_virada_semana
-- Fecha a semana anterior das ligas: sobe/desce/mantém cada usuário
-- conforme a posição no grupo e grava o histórico. Substitui
-- back/ligas_logic.php (liga_processar_virada_semana). Chamada 1x por
-- semana pelo back/cron_ligas_virada.php.
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_processar_virada_semana $$
CREATE PROCEDURE sp_processar_virada_semana()
BEGIN
    DECLARE v_done INT DEFAULT 0;
    DECLARE v_grupo_id INT;
    DECLARE v_divisao VARCHAR(20);
    DECLARE v_semana_anterior DATE;
    DECLARE v_n INT;
    DECLARE v_sobe INT;
    DECLARE v_desce INT;
    DECLARE v_proxima VARCHAR(20);
    DECLARE v_anterior VARCHAR(20);
    DECLARE v_pode_subir TINYINT;
    DECLARE v_pode_descer TINYINT;

    DECLARE cur_grupos CURSOR FOR
        SELECT id, divisao FROM ligas_grupos WHERE semana_ref = v_semana_anterior;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;

    SET v_semana_anterior = DATE_SUB(DATE_SUB(CURDATE(), INTERVAL (WEEKDAY(CURDATE())) DAY), INTERVAL 7 DAY);

    DROP TEMPORARY TABLE IF EXISTS tmp_membros;
    CREATE TEMPORARY TABLE tmp_membros (
        posicao INT,
        usuario_id INT,
        xp_semana INT
    );

    OPEN cur_grupos;
    grupo_loop: LOOP
        FETCH cur_grupos INTO v_grupo_id, v_divisao;
        IF v_done = 1 THEN LEAVE grupo_loop; END IF;

        CASE v_divisao
            WHEN 'bronze'   THEN SET v_proxima = 'prata',    v_anterior = NULL,       v_pode_subir = 1, v_pode_descer = 0;
            WHEN 'prata'    THEN SET v_proxima = 'ouro',     v_anterior = 'bronze',   v_pode_subir = 1, v_pode_descer = 1;
            WHEN 'ouro'     THEN SET v_proxima = 'diamante', v_anterior = 'prata',    v_pode_subir = 1, v_pode_descer = 1;
            WHEN 'diamante' THEN SET v_proxima = 'mestre',   v_anterior = 'ouro',     v_pode_subir = 1, v_pode_descer = 1;
            WHEN 'mestre'   THEN SET v_proxima = NULL,       v_anterior = 'diamante', v_pode_subir = 0, v_pode_descer = 1;
        END CASE;

        DELETE FROM tmp_membros;
        SET @rownum := 0;
        INSERT INTO tmp_membros (posicao, usuario_id, xp_semana)
          SELECT @rownum := @rownum + 1, usuario_id, xp_semana
          FROM ligas_usuario
          WHERE grupo_id = v_grupo_id
          ORDER BY xp_semana DESC, usuario_id ASC;

        SELECT COUNT(*) INTO v_n FROM tmp_membros;

        IF v_n > 0 THEN
            SET v_sobe = ROUND(v_n * 7 / 30);
            SET v_sobe = GREATEST(1, LEAST(v_sobe, v_n));
            SET v_desce = ROUND(v_n * 7 / 30);
            SET v_desce = GREATEST(1, LEAST(v_desce, v_n));
            IF v_sobe + v_desce > v_n THEN
                SET v_desce = GREATEST(0, v_n - v_sobe);
            END IF;

            -- Não zera grupo_id (a coluna é NOT NULL): fica apontando pro
            -- grupo da semana que fechou, e o sp_liga_registrar_xp já
            -- detecta que o grupo é de outra semana e realoca sozinho na
            -- próxima vez que o usuário ganhar XP.
            UPDATE ligas_usuario lu
              JOIN tmp_membros tm ON tm.usuario_id = lu.usuario_id
              SET lu.divisao = IF(tm.posicao <= v_sobe AND v_pode_subir = 1, v_proxima,
                               IF(tm.posicao > (v_n - v_desce) AND v_pode_descer = 1, v_anterior, v_divisao)),
                  lu.xp_semana = 0,
                  lu.posicao_semana_anterior = tm.posicao;

            INSERT INTO ligas_historico (usuario_id, divisao_anterior, divisao_nova, resultado, posicao_final, xp_final, semana_ref)
              SELECT tm.usuario_id, v_divisao,
                     IF(tm.posicao <= v_sobe AND v_pode_subir = 1, v_proxima,
                        IF(tm.posicao > (v_n - v_desce) AND v_pode_descer = 1, v_anterior, v_divisao)),
                     IF(tm.posicao <= v_sobe AND v_pode_subir = 1, 'subiu',
                        IF(tm.posicao > (v_n - v_desce) AND v_pode_descer = 1, 'desceu', 'manteve')),
                     tm.posicao, tm.xp_semana, v_semana_anterior
              FROM tmp_membros tm;
        END IF;
    END LOOP;
    CLOSE cur_grupos;

    DROP TEMPORARY TABLE IF EXISTS tmp_membros;
END $$

-- =====================================================================
-- sp_processar_resultado_licao
-- Fluxo REAL de correção de lição usado por front/pages/resultado.php
-- (chamado pelo formulário de front/pages/licao.php). Dá XP proporcional
-- aos acertos, registra liga/missão/streak, concede troféus e avança o
-- progresso do capítulo. A proteção contra reprocessamento (attempt_token
-- de sessão) e o desconto de vida continuam no PHP, que só chama esta
-- procedure quando ainda não processou a tentativa.
-- =====================================================================
DROP PROCEDURE IF EXISTS sp_processar_resultado_licao $$
CREATE PROCEDURE sp_processar_resultado_licao(
    IN p_user_id INT,
    IN p_capitulo INT,
    IN p_licao INT,
    IN p_acertos INT,
    IN p_xp_ganho INT
)
BEGIN
    DECLARE v_licoes_feitas INT;
    DECLARE v_status VARCHAR(20);
    DECLARE v_proximo INT;
    DECLARE v_slug_capitulo VARCHAR(20);

    UPDATE usuarios SET xp = xp + p_xp_ganho WHERE id = p_user_id;

    CALL sp_liga_registrar_xp(p_user_id, p_xp_ganho);
    CALL sp_registrar_missao_progresso(p_user_id, p_xp_ganho, p_acertos);
    CALL sp_atualizar_fogo(p_user_id);

    IF p_acertos > 0 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'primeiro_passo');
    END IF;

    IF p_acertos = 3 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'perfeicao');
    END IF;

    IF p_licao = 5 AND p_acertos > 0 THEN
        SET v_slug_capitulo = CONCAT('capitulo_', p_capitulo);
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, v_slug_capitulo);
    END IF;

    UPDATE usuarios SET trofeus = (SELECT COUNT(*) FROM user_trofeus WHERE user_id = p_user_id) WHERE id = p_user_id;

    IF p_acertos > 0 THEN
        SELECT licoes_concluidas, status INTO v_licoes_feitas, v_status
          FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_capitulo;

        IF v_status = 'corrente' AND v_licoes_feitas = p_licao - 1 THEN
            IF p_licao = 5 THEN
                UPDATE progresso_usuario SET status = 'completo', licoes_concluidas = 5
                  WHERE usuario_id = p_user_id AND unidade_numero = p_capitulo;

                IF p_capitulo < 5 THEN
                    SET v_proximo = p_capitulo + 1;
                    UPDATE progresso_usuario SET status = 'corrente'
                      WHERE usuario_id = p_user_id AND unidade_numero = v_proximo AND status = 'trancado';
                END IF;
            ELSE
                UPDATE progresso_usuario SET licoes_concluidas = p_licao
                  WHERE usuario_id = p_user_id AND unidade_numero = p_capitulo;
            END IF;
        END IF;
    END IF;
END $$

DELIMITER ;
