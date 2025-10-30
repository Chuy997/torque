-- /var/www/html/torque/sql/002d_switch_to_mondays.sql
-- Objetivo: considerar SOLO LUNES como días esperados de calibración
--           (elimina Miércoles y Viernes de los pendientes).

SET NAMES utf8mb4;

-- Asegurar tabla de festivos (por si no existe)
CREATE TABLE IF NOT EXISTS holidays (
  dt   DATE PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Vista de fechas esperadas de los ÚLTIMOS ~3 MESES (93 días) — SOLO LUNES
DROP VIEW IF EXISTS vw_expected_dates_last3m;
CREATE VIEW vw_expected_dates_last3m AS
WITH RECURSIVE rng(d, n) AS (
  SELECT CURDATE() - INTERVAL 93 DAY, 1
  UNION ALL
  SELECT d + INTERVAL 1 DAY, n + 1 FROM rng WHERE n < 93
)
SELECT d AS expected_date
FROM rng
WHERE DAYOFWEEK(d) = 2       -- LUNES (MariaDB: 1=Dom, 2=Lun, 3=Mar, 4=Mié, 5=Jue, 6=Vie, 7=Sáb)
  AND d NOT IN (SELECT dt FROM holidays);

-- Vista de CALIBRACIONES FALTANTES para torques ACTIVOS (solo lunes ahora)
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
