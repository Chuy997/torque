-- /var/www/html/torque/sql/001_schema_upgrade.sql
-- Objetivo: normalizar esquema, llaves primarias AUTO_INCREMENT, FKs, índices y colación.
-- Base: torquecalibrationdb

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Asegurar motor y charset/colación consistentes
ALTER TABLE torques        ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE calibrations   ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE history        ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE users          ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- ====== TORQUES ======
-- Normalizar ENUMs (garantiza los valores esperados)
ALTER TABLE torques
  MODIFY COLUMN status ENUM('activo','fuera de uso','calibracion fallida') NOT NULL DEFAULT 'activo';

-- Índices útiles para vistas y consultas por estado
CREATE INDEX idx_torques_status ON torques (status);

-- ====== CALIBRATIONS ======
-- Creación de nueva PK auto_increment segura (no asumimos unicidad de la columna previa)
-- 1) Añadir nueva columna AI como PK
ALTER TABLE calibrations
  ADD COLUMN calibrationID_new INT NOT NULL AUTO_INCREMENT FIRST,
  ADD PRIMARY KEY (calibrationID_new);

-- 2) Eliminar la antigua columna y renombrar la nueva a calibrationID
ALTER TABLE calibrations
  DROP COLUMN calibrationID,
  CHANGE COLUMN calibrationID_new calibrationID INT NOT NULL AUTO_INCREMENT;

-- 3) Normalizar ENUM de resultado
ALTER TABLE calibrations
  MODIFY COLUMN resultado ENUM('aprobado','fuera de tolerancia') NULL;

-- 4) Índices para rendimiento (búsquedas por torque y orden por fecha)
CREATE INDEX idx_calibrations_torqueID ON calibrations (torqueID);
CREATE INDEX idx_calibrations_fecha ON calibrations (fechaCalibracion);

-- ====== HISTORY ======
-- 1) Añadir nueva PK auto_increment segura
ALTER TABLE history
  ADD COLUMN historyID_new INT NOT NULL AUTO_INCREMENT FIRST,
  ADD PRIMARY KEY (historyID_new);

-- 2) Eliminar la antigua columna y renombrar la nueva a historyID
ALTER TABLE history
  DROP COLUMN historyID,
  CHANGE COLUMN historyID_new historyID INT NOT NULL AUTO_INCREMENT;

-- 3) Índices
CREATE INDEX idx_history_torqueID ON history (torqueID);
CREATE INDEX idx_history_date ON history (`date`);

-- ====== USERS ======
-- Asegurar unicidad en username (si ya existe, no falla por estar creada)
ALTER TABLE users
  ADD UNIQUE KEY uq_users_username (username);

-- ====== FOREIGN KEYS ======
-- Nota: si ya existieran FKs con nombres distintos, puedes ignorar errores de duplicado.
-- Primero, crear índice en torques.torqueID si no es PK (debería serlo)
ALTER TABLE torques
  ADD PRIMARY KEY (torqueID);

-- Asegurar tipos compatibles (torqueID es VARCHAR(50) en todas)
ALTER TABLE calibrations
  MODIFY COLUMN torqueID VARCHAR(50) NULL;

ALTER TABLE history
  MODIFY COLUMN torqueID VARCHAR(50) NULL;

-- Agregar claves foráneas con borrado/actualización restrictivo
-- (si un torque se elimina, evitamos huérfanos)
-- Nombres explícitos para fácil mantenimiento
ALTER TABLE calibrations
  ADD CONSTRAINT fk_calibrations_torqueID
  FOREIGN KEY (torqueID) REFERENCES torques (torqueID)
  ON UPDATE RESTRICT ON DELETE RESTRICT;

ALTER TABLE history
  ADD CONSTRAINT fk_history_torqueID
  FOREIGN KEY (torqueID) REFERENCES torques (torqueID)
  ON UPDATE RESTRICT ON DELETE RESTRICT;

SET FOREIGN_KEY_CHECKS = 1;

-- FIN
