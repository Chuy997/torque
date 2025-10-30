-- /var/www/html/torque/sql/002c_missing_last3months_fix.sql
-- MariaDB 10.11: generar fechas últimos 3 meses (máx 93 días), Lun/Mié/Vie, sin festivos

SET NAMES utf8mb4;

-- 1) Tabla de festivos
CREATE TABLE IF NOT EXISTS holidays (
  dt   DATE PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Vista de fechas esperadas (últimos 93 días desde hoy, solo Lun/Mié/Vie, excluye festivos)
DROP VIEW IF EXISTS vw_expected_dates_last3m;
CREATE VIEW vw_expected_dates_last3m AS
WITH RECURSIVE rng(d, n) AS (
  SELECT CURDATE() - INTERVAL 93 DAY, 1
  UNION ALL
  SELECT d + INTERVAL 1 DAY, n + 1 FROM rng WHERE n < 93
)
SELECT d AS expected_date
FROM rng
WHERE DAYOFWEEK(d) IN (2,4,6)   -- Lunes=2, Miércoles=4, Viernes=6
  AND d NOT IN (SELECT dt FROM holidays);

-- 3) Vista de calibraciones faltantes
DROP VIEW IF EXISTS vw_missing_calibrations_last3m;
CREATE VIEW vw_missing_calibrations_last3m AS
SELECT
  t.torqueID,
  e.expected_date
FROM torques t
CROSS JOIN vw_expected_dates_last3m e
LEFT JOIN calibrations c
  ON c.torqueID = t.torqueID
 AND DATE(c.fechaCalibracion) = e.expected_date
WHERE t.status = 'activo'
  AND c.calibrationID IS NULL
ORDER BY e.expected_date ASC, t.torqueID ASC;

