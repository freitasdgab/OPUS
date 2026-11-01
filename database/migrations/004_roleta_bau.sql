-- Migration 004: Atualização da procedure do Baú de Recompensas para suporte à Roleta da Sorte
DELIMITER ;;

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

DELIMITER ;
