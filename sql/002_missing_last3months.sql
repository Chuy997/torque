-- /var/www/html/torque/sql/002_missing_last3months.sql
-- Detecta calibraciones faltantes de LUNES, MIÉRCOLES y VIERNES en los últimos 3 meses
-- para torques ACTIVO(S), excluyendo días festivos cargados por el usuario.

SET NAMES utf8mb4;

-- 1) Tabla de festivos (llénala tú con las fechas oficiales)
CREATE TABLE IF NOT EXISTS holidays (
  dt   DATE PRIMARY KEY,
  name VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Vista de fechas esperadas (últimos 3 meses, solo LUN/MIE/VIE, sin festivos)
--    DAYOFWEEK() en MariaDB: 1=Dom, 2=Lun, 3=Mar, 4=Mié, 5=Jue, 6=Vie, 7=Sáb
DROP VIEW IF EXISTS vw_expected_dates_last3m;
CREATE VIEW vw_expected_dates_last3m AS
WITH RECURSIVE rng AS (
  SELECT (CURDATE() - INTERVAL 3 MONTH) AS d
  UNION ALL
  SELECT d + INTERVAL 1 DAY FROM rng WHERE d + INTERVAL 1 DAY <= CURDATE()
)
SELECT d AS expected_date
FROM rng
WHERE DAYOFWEEK(d) IN (2,4,6)         -- LUN(2), MIE(4), VIE(6)
  AND d NOT IN (SELECT dt FROM holidays)
OPTION MAX_RECURSION_DEPTH = 366;

-- 3) Vista de faltantes por torque ACTIVO
DROP VIEW IF EXISTS vw_missing_calibrations_last3m;
CREATE VIEW vw_missing_calibrations_last3m AS
SELECT
  t.torqueID,
  e.expected_date
FROM torques t
JOIN vw_expected_dates_last3m e
LEFT JOIN calibrations c
  ON c.torqueID = t.torqueID
 AND DATE(c.fechaCalibracion) = e.expected_date
WHERE t.status = 'activo'
  AND c.calibrationID IS NULL
ORDER BY e.expected_date ASC, t.torqueID ASC;

-- Nota:
-- - Primero inserta tus festivos recientes en 'holidays' (ejemplos):
--   INSERT INTO holidays (dt, name) VALUES ('2025-09-16','Día de la Independencia');
-- - Para ver faltantes: SELECT * FROM vw_missing_calibrations_last3m;
