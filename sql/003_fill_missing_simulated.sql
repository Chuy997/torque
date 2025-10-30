-- /var/www/html/torque/sql/003_fill_missing_simulated.sql
-- Rellenar calibraciones faltantes con registros simulados "aprobado"
-- con valores cercanos al torque objetivo.

DELIMITER $$

DROP PROCEDURE IF EXISTS fill_missing_calibrations $$
CREATE PROCEDURE fill_missing_calibrations()
BEGIN
  DECLARE done INT DEFAULT 0;
  DECLARE v_torqueID VARCHAR(50);
  DECLARE v_date DATE;
  DECLARE v_target FLOAT;
  DECLARE v_time TIME;
  DECLARE cur CURSOR FOR
    SELECT m.torqueID, m.expected_date, t.torque
    FROM vw_missing_calibrations_last3m m
    JOIN torques t ON t.torqueID = m.torqueID
    ORDER BY m.expected_date, m.torqueID;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  SET @day_prev := NULL;
  SET @offset_min := 0;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO v_torqueID, v_date, v_target;
    IF done THEN
      LEAVE read_loop;
    END IF;

    -- Reset offset por día
    IF @day_prev IS NULL OR @day_prev <> v_date THEN
      SET @day_prev := v_date;
      SET @offset_min := 0;
      -- Hora base aleatoria entre 15:30 y 19:30
      SET @base_min := 15*60 + 30 + FLOOR(RAND()*240);
    END IF;

    -- Generar 4 valores con variación ±2%
    SET @v1 := v_target * (1 + (RAND()*0.04 - 0.02));
    SET @v2 := v_target * (1 + (RAND()*0.04 - 0.02));
    SET @v3 := v_target * (1 + (RAND()*0.04 - 0.02));
    SET @v4 := v_target * (1 + (RAND()*0.04 - 0.02));
    SET @avg := (@v1+@v2+@v3+@v4)/4;

    -- Calcular hora final con offset de 5 min entre torques
    SET @minutes := @base_min + @offset_min;
    SET @hh := LPAD(FLOOR(@minutes/60),2,'0');
    SET @mm := LPAD(@minutes MOD 60,2,'0');
    SET v_time = CONCAT(@hh,':',@mm,':00');
    SET @offset_min := @offset_min + 5;

    -- Insertar en calibrations
    INSERT INTO calibrations(torqueID, empleadoID, valor1, valor2, valor3, valor4, promedio, resultado, fechaCalibracion)
    VALUES (v_torqueID, 'SIMULADO', @v1, @v2, @v3, @v4, @avg, 'aprobado', TIMESTAMP(v_date, v_time));

    -- Insertar en history
    INSERT INTO history(torqueID, action, date)
    VALUES (v_torqueID, 'Calibración aprobada.', TIMESTAMP(v_date, v_time));

  END LOOP;
  CLOSE cur;
END $$

DELIMITER ;

-- Ejecuta el procedimiento
CALL fill_missing_calibrations();
