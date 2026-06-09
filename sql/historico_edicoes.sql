-- Executar no banco boraca19_credenciais

CREATE TABLE IF NOT EXISTS `historico_edicoes` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `motorista_id`   INT UNSIGNED NOT NULL,
  `usuario`        VARCHAR(100) DEFAULT NULL,
  `campo`          VARCHAR(50)  NOT NULL,
  `valor_anterior` TEXT         DEFAULT NULL,
  `valor_novo`     TEXT         DEFAULT NULL,
  `editado_em`     DATETIME     DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_motorista` (`motorista_id`),
  KEY `idx_editado_em` (`editado_em`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
