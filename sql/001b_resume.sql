-- /var/www/html/torque/sql/001b_resume.sql
-- Objetivo: reanudar migración después del error "Multiple primary key defined"
-- Saltamos la parte de agregar PRIMARY KEY en 'torques' (ya existe) y
-- sólo aseguramos tipos y FKs.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Asegurar tipos compatibles (por si la sentencia previa no corrió)
ALTER TABLE calibrations
  MODIFY COLUMN torqueID VARCHAR(50) NULL;

ALTER TABLE history
  MODIFY COLUMN torqueID VARCHAR(50) NULL;

-- Limpiar FKs si quedaron a medias (ignora si no existen)
ALTER TABLE calibrations DROP FOREIGN KEY IF EXISTS fk_calibrations_torqueID;
ALTER TABLE history      DROP FOREIGN KEY IF EXISTS fk_history_torqueID;

-- (Opcional) Re-asegurar índices (IF NOT EXISTS evita error si ya están)
CREATE INDEX IF NOT EXISTS idx_calibrations_torqueID ON calibrations (torqueID);
CREATE INDEX IF NOT EXISTS idx_calibrations_fecha    ON calibrations (fechaCalibracion);
CREATE INDEX IF NOT EXISTS idx_history_torqueID      ON history (torqueID);
CREATE INDEX IF NOT EXISTS idx_history_date          ON history (`date`);

-- Agregar claves foráneas finales
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
