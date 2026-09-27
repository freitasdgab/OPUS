DELIMITER $$
DROP PROCEDURE IF EXISTS `sp_processar_resultado_licao`$$
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
    DECLARE v_xp_ganho INT DEFAULT 0;
    DECLARE v_licoes_feitas INT DEFAULT 0;
    DECLARE v_eh_repeticao INT DEFAULT 0;

    IF p_acertos = p_total AND p_total > 0 THEN
        SET v_xp_ganho = 50;
    ELSEIF p_acertos >= (p_total - 1) AND p_total > 0 THEN
        SET v_xp_ganho = 25;
    ELSE
        SET v_xp_ganho = 10;
    END IF;

    IF p_acertos = p_total AND p_total > 0 THEN
        INSERT IGNORE INTO user_trofeus (user_id, trofeu_slug) VALUES (p_user_id, 'perfeicao');
        INSERT IGNORE INTO conquistas_usuario (usuario_id, conquista_slug) VALUES (p_user_id, 'perfeicao');

        SELECT licoes_concluidas INTO v_licoes_feitas FROM progresso_usuario WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
        
        IF v_licoes_feitas IS NULL THEN
            INSERT INTO progresso_usuario (usuario_id, unidade_numero, status, licoes_concluidas) VALUES (p_user_id, p_unidade, 'corrente', 1);
            SET v_licoes_feitas = 1;
            SET v_avancou = 1;
        ELSEIF p_licao > v_licoes_feitas THEN
            SET v_licoes_feitas = p_licao;
            UPDATE progresso_usuario SET licoes_concluidas = v_licoes_feitas WHERE usuario_id = p_user_id AND unidade_numero = p_unidade;
            SET v_avancou = 1;
        ELSE
            -- Se for repetição de uma lição já feita com 100%
            SET v_eh_repeticao = 1;
            SET v_xp_ganho = 10; 
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
    END IF;

    IF v_xp_ganho > 0 THEN
        UPDATE usuarios SET xp = xp + v_xp_ganho WHERE id = p_user_id;
        CALL sp_liga_registrar_xp(p_user_id, v_xp_ganho);
        CALL sp_registrar_missao_progresso(p_user_id, v_xp_ganho, 1);
        CALL sp_atualizar_fogo(p_user_id);
        CALL sp_verificar_conquistas(p_user_id);
    END IF;

    SELECT v_avancou AS avancou, v_capitulo_concluido AS capitulo_concluido, v_bau_liberado AS bau_liberado, v_xp_ganho AS xp_ganho, v_eh_repeticao AS eh_repeticao;
END$$
DELIMITER ;
