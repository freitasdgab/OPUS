-- Migration 006: Controle do Tutor Guiado Opi na 1ª Lição e no Final da 1ª Lição
ALTER TABLE `usuarios`
    ADD COLUMN IF NOT EXISTS `tour_licao1_visto` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `tour_resultado1_visto` TINYINT(1) NOT NULL DEFAULT 0;
