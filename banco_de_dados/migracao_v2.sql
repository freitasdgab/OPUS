-- ============================================================
--  OPUS — Migração v2
--  Execute este script se já tiver o banco criado (opus.sql já importado).
--  Se for importar do zero, use o opus.sql (já atualizado).
-- ============================================================

USE opus;

-- Adiciona colunas de streak (ignora se já existirem)
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS sequencia_dias   INT  NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS ultima_atividade DATE          DEFAULT NULL;

-- Garante que a coluna foto_perfil existe (pode já existir)
ALTER TABLE usuarios
    MODIFY COLUMN foto_perfil VARCHAR(255) DEFAULT NULL;
