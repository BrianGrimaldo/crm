-- ============================================================
-- CRM Multi-Tenant :: Agregar credenciales SMTP por usuario
-- Permite que cada vendedor envíe correos desde su propio email
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `users`
  ADD COLUMN `smtp_host`       VARCHAR(255)  DEFAULT NULL COMMENT 'Servidor SMTP del usuario' AFTER `avatar_url`,
  ADD COLUMN `smtp_port`       SMALLINT      DEFAULT 587  COMMENT 'Puerto SMTP' AFTER `smtp_host`,
  ADD COLUMN `smtp_email`      VARCHAR(255)  DEFAULT NULL COMMENT 'Email SMTP para envío' AFTER `smtp_port`,
  ADD COLUMN `smtp_password`   VARCHAR(500)  DEFAULT NULL COMMENT 'Contraseña o App Password SMTP (encriptada)' AFTER `smtp_email`,
  ADD COLUMN `smtp_encryption` ENUM('tls','ssl','none') DEFAULT 'tls' COMMENT 'Tipo de encriptación' AFTER `smtp_password`,
  ADD COLUMN `smtp_from_name`  VARCHAR(200)  DEFAULT NULL COMMENT 'Nombre mostrado al enviar' AFTER `smtp_encryption`;
