DELIMITER $$

CREATE TRIGGER `tg_novo_usuario_setup`
AFTER INSERT ON `usuarios`
FOR EACH ROW
BEGIN
    INSERT INTO `progresso_usuario` (`usuario_id`, `unidade_numero`, `status`, `licoes_concluidas`)
    VALUES (NEW.id, 1, 'corrente', 0)
    ON DUPLICATE KEY UPDATE `status` = 'corrente';

    INSERT IGNORE INTO `missoes_diarias_usuario` (`usuario_id`, `data_ref`, `xp_ganho`, `licoes_concluidas`, `licoes_perfeitas`)
    VALUES (NEW.id, CURDATE(), 0, 0, 0);
END$$

CREATE TRIGGER `tg_incrementar_trofeu`
AFTER INSERT ON `user_trofeus`
FOR EACH ROW
BEGIN
    UPDATE `usuarios`
    SET `trofeus` = (SELECT COUNT(*) FROM `user_trofeus` WHERE `user_id` = NEW.user_id)
    WHERE `id` = NEW.user_id;
END$$

CREATE TRIGGER `tg_decrementar_trofeu`
AFTER DELETE ON `user_trofeus`
FOR EACH ROW
BEGIN
    UPDATE `usuarios`
    SET `trofeus` = (SELECT COUNT(*) FROM `user_trofeus` WHERE `user_id` = OLD.user_id)
    WHERE `id` = OLD.user_id;
END$$

CREATE TRIGGER `tg_validar_pedido_conexao`
BEFORE INSERT ON `pedidos_conexao`
FOR EACH ROW
BEGIN
    IF NEW.remetente_id = NEW.destinatario_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é permitido enviar solicitação de amizade para si mesmo.';
    END IF;
END$$

CREATE TRIGGER `tg_validar_chat_mensagem`
BEFORE INSERT ON `chat_mensagens`
FOR EACH ROW
BEGIN
    IF NEW.remetente_id = NEW.destinatario_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Não é permitido enviar mensagens no chat para si próprio.';
    END IF;
END$$

DELIMITER ;
