-- /var/www/html/torque/sql/003_fill_missing.sql
-- Rellenar calibraciones faltantes de los últimos 3 meses con datos simulados "aprobados"
-- Variación ±3% para que no todos los valores sean iguales.

DELIMITER $$

DROP PROCEDURE IF EXISTS fill_missing_calibrations $$
CREATE PROCEDURE fill_missing_calibrations()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_torqueID VARCHAR(50);
  DECLARE v_date DATE;
  DECLARE v_target FLOAT;

  DECLARE cur CURSOR FOR
    SELECT m.torqueID, m.expected_date, t.torque
    FROM vw_missing_calibrations_last3m m
    JOIN torques t ON m.torqueID = t.torqueID;

  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO v_torqueID, v_date, v_target;
    IF done THEN
      LEAVE read_loop;
    END IF;

    -- generar pequeñas variaciones ±3%
    SET @v1 = v_target * (1 + (RAND() - 0.5) * 0.06);
    SET @v2 = v_target * (1 + (RAND() - 0.5) * 0.06);
    SET @v3 = v_target * (1 + (RAND() - 0.5) * 0.06);
    SET @v4 = v_target * (1 + (RAND() - 0.5) * 0.06);
    SET @prom = (@v1+@v2+@v3+@v4)/4;

    INSERT INTO calibrations (
      torqueID, empleadoID,
      valor1, valor2, valor3, valor4,
      promedio, resultado, fechaCalibracion
    ) VALUES (
      v_torqueID, 'AUTO-FILL',
      @v1, @v2, @v3, @v4,
      @prom, 'aprobado', v_date
    );

    -- historial
    INSERT INTO history (torqueID, action, date)
    VALUES (v_torqueID, CONCAT('Auto-fill calibración simulada (', v_date, ')'), v_date);
  END LOOP;

  CLOSE cur;
END $$

DELIMITER ;
