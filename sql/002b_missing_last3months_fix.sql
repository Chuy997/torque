-- /var/www/html/torque/sql/002b_missing_last3months_fix.sql
-- MariaDB 10.11: usa cte_max_recursion_depth y quita OPTION MAX_RECURSION_DEPTH.

SET NAMES utf8mb4;
SET SESSION cte_max_recursion_depth = 400;

-- 1) Tabla de festivos (si no existe)
CREATE TABLE IF NOT EXISTS holidays (
  dt   DATE PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Fechas esperadas últimos 3 meses (Lun/Mié/Vie), excluyendo festivos
DROP VIEW IF EXISTS vw_expected_dates_last3m;
CREATE VIEW vw_expected_dates_last3m AS
WITH RECURSIVE rng(d) AS (
  SELECT CURDATE() - INTERVAL 3 MONTH
  UNION ALL
  SELECT d + INTERVAL 1 DAY FROM rng WHERE d + INTERVAL 1 DAY <= CURDATE()
)
SELECT d AS expected_date
FROM rng
WHERE DAYOFWEEK(d) IN (2,4,6)      -- LUN=2, MIE=4, VIE=6
  AND d NOT IN (SELECT dt FROM holidays);

-- 3) Faltantes por torque ACTIVO
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
