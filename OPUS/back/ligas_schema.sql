CREATE TABLE `ligas_grupos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `divisao` enum('bronze','prata','ouro','diamante','mestre') NOT NULL,
  `semana_ref` date NOT NULL,
  `capacidade` int(11) NOT NULL DEFAULT 30,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `divisao_semana` (`divisao`,`semana_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  CONSTRAINT `fk_ligas_usuario_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ligas_usuario_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `ligas_grupos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ligas_historico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `divisao_anterior` varchar(20) NOT NULL,
  `divisao_nova` varchar(20) NOT NULL,
  `resultado` enum('subiu','manteve','desceu') NOT NULL,
  `posicao_final` int(11) NOT NULL,
  `xp_final` int(11) NOT NULL,
  `semana_ref` date NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_ligas_historico_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;