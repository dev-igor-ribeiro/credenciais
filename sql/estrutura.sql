-- =============================================================
-- BoraCar – Estrutura do Banco de Dados
-- =============================================================

-- -------------------------------------------------------------
-- Banco: boraca19_boracar_login
-- -------------------------------------------------------------

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- -------------------------------------------------------------
-- Banco: boraca19_credenciais
-- -------------------------------------------------------------

CREATE TABLE IF NOT EXISTS motoristas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    cnh VARCHAR(20),
    cpf VARCHAR(20),
    validade DATE,
    modelo VARCHAR(100),
    ano VARCHAR(10),
    placa VARCHAR(20),
    credencial VARCHAR(50),
    status VARCHAR(20) DEFAULT 'valido',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================================
-- Migrações (rodar apenas uma vez no banco já existente)
-- =============================================================

-- [2026-06] Amplia coluna status para suportar 'suspenso' e 'pendente'
ALTER TABLE motoristas MODIFY COLUMN status VARCHAR(20) DEFAULT 'valido';
