CREATE TABLE IF NOT EXISTS `log_acoes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario`     VARCHAR(100) NOT NULL,
  `acao`        VARCHAR(50)  NOT NULL,
  `descricao`   TEXT         NOT NULL,
  `ip`          VARCHAR(45)  DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
