-- Migration 005: Controle do Tour Guiado de Apresentação com o Mascote Opi (estilo Duolingo)
ALTER TABLE `usuarios`
    ADD COLUMN IF NOT EXISTS `tour_visto` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `tour_perfil_visto` TINYINT(1) NOT NULL DEFAULT 0;
