-- Completar pedido_cliente_mp con maquinas usando el CSV of_maquinas.csv
-- Fuente: C:/Users/SergioDanielRomero/Documents/SQL_SRINK_LARAVEL_11/CARGA EXCEL-DB/Listado_OF/of_maquinas.csv
--
-- Uso sugerido:
-- 1. Ejecutar este script en una sesion propia.
-- 2. Si LOAD DATA LOCAL INFILE esta deshabilitado, crear la tabla temporal y cargar el CSV con el importador de HeidiSQL.
-- 3. Revisar los SELECT de diagnostico antes y despues.

SET @frenteado_default := 0.50;
SET @ancho_cut_off_default := 1.00;
SET @sobrematerial_default := 0.50;

DROP TEMPORARY TABLE IF EXISTS tmp_of_maquinas;
CREATE TEMPORARY TABLE tmp_of_maquinas (
    Nro_OF INT NOT NULL,
    Nro_Maquina VARCHAR(50) NULL,
    Familia_Maquina VARCHAR(255) NULL,
    PRIMARY KEY (Nro_OF)
) ENGINE=InnoDB;

-- Si tu servidor permite LOCAL INFILE, esta carga deberia funcionar.
LOAD DATA LOCAL INFILE 'C:/Users/SergioDanielRomero/Documents/SQL_SRINK_LARAVEL_11/CARGA EXCEL-DB/Listado_OF/of_maquinas.csv'
INTO TABLE tmp_of_maquinas
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ';'
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
(@nro_of, @nro_maquina, @familia)
SET
    Nro_OF = NULLIF(TRIM(@nro_of), ''),
    Nro_Maquina = NULLIF(TRIM(@nro_maquina), ''),
    Familia_Maquina = NULLIF(TRIM(@familia), '');

-- Limpieza simple por si el CSV trae CRLF.
UPDATE tmp_of_maquinas
SET
    Nro_Maquina = TRIM(REPLACE(REPLACE(Nro_Maquina, '\r', ''), '\n', '')),
    Familia_Maquina = TRIM(REPLACE(REPLACE(Familia_Maquina, '\r', ''), '\n', ''));

-- ============================================================
-- DIAGNOSTICO PREVIO
-- ============================================================

SELECT
    COUNT(*) AS filas_csv,
    COUNT(DISTINCT Nro_OF) AS of_unicas_csv,
    SUM(CASE WHEN Nro_Maquina IS NULL OR Nro_Maquina = '' THEN 1 ELSE 0 END) AS filas_csv_sin_maquina,
    SUM(CASE WHEN Familia_Maquina IS NULL OR Familia_Maquina = '' THEN 1 ELSE 0 END) AS filas_csv_sin_familia
FROM tmp_of_maquinas;

SELECT
    COUNT(*) AS filas_pedido_mp_con_maquina_incompleta
FROM pedido_cliente_mp pm
WHERE pm.deleted_at IS NULL
  AND (pm.Id_Maquina IS NULL OR pm.Nro_Maquina IS NULL OR pm.Familia_Maquina IS NULL);

-- ============================================================
-- 1. COMPLETAR MAQUINA DESDE CSV + CATALOGO maquinas_produc
-- ============================================================

UPDATE pedido_cliente_mp pm
INNER JOIN pedido_cliente p
    ON p.Id_OF = pm.Id_OF
   AND p.deleted_at IS NULL
INNER JOIN tmp_of_maquinas t
    ON t.Nro_OF = p.Nro_OF
INNER JOIN maquinas_produc m
    ON m.Nro_maquina = t.Nro_Maquina
   AND (t.Familia_Maquina IS NULL OR t.Familia_Maquina = '' OR m.familia_maquina = t.Familia_Maquina)
   AND m.Status = 1
SET
    pm.Id_Maquina = m.id_maquina,
    pm.Nro_Maquina = m.Nro_maquina,
    pm.Familia_Maquina = m.familia_maquina,
    pm.Scrap_Maquina = m.scrap_maquina
WHERE pm.deleted_at IS NULL
  AND (pm.Id_Maquina IS NULL OR pm.Nro_Maquina IS NULL OR pm.Familia_Maquina IS NULL OR pm.Scrap_Maquina IS NULL);

-- ============================================================
-- 2. COMPLETAR LARGO_PIEZA DESDE PRODUCTO
-- ============================================================

UPDATE pedido_cliente_mp pm
INNER JOIN pedido_cliente p
    ON p.Id_OF = pm.Id_OF
   AND p.deleted_at IS NULL
INNER JOIN productos prod
    ON prod.Id_Producto = p.Producto_Id
SET
    pm.Largo_Pieza = CASE
        WHEN pm.Largo_Pieza IS NULL OR pm.Largo_Pieza = 0 THEN prod.Prod_Longitud_de_Pieza
        ELSE pm.Largo_Pieza
    END
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- 3. DEFAULTS DE CALCULO
-- ============================================================

UPDATE pedido_cliente_mp pm
SET
    pm.Frenteado = CASE WHEN pm.Frenteado IS NULL THEN @frenteado_default ELSE pm.Frenteado END,
    pm.Ancho_Cut_Off = CASE WHEN pm.Ancho_Cut_Off IS NULL THEN @ancho_cut_off_default ELSE pm.Ancho_Cut_Off END,
    pm.Sobrematerial_Promedio = CASE WHEN pm.Sobrematerial_Promedio IS NULL THEN @sobrematerial_default ELSE pm.Sobrematerial_Promedio END
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- 4. RECALCULO DE CAMPOS DERIVADOS
-- ============================================================

UPDATE pedido_cliente_mp pm
INNER JOIN pedido_cliente p
    ON p.Id_OF = pm.Id_OF
   AND p.deleted_at IS NULL
SET
    pm.Largo_Total_Pieza = CASE
        WHEN pm.Largo_Pieza IS NOT NULL
         AND pm.Frenteado IS NOT NULL
         AND pm.Ancho_Cut_Off IS NOT NULL
         AND pm.Sobrematerial_Promedio IS NOT NULL
        THEN ROUND(pm.Largo_Pieza + pm.Frenteado + pm.Ancho_Cut_Off + pm.Sobrematerial_Promedio, 2)
        ELSE pm.Largo_Total_Pieza
    END,
    pm.MM_Totales = CASE
        WHEN p.Cant_Fabricacion IS NOT NULL
         AND pm.Largo_Pieza IS NOT NULL
         AND pm.Frenteado IS NOT NULL
         AND pm.Ancho_Cut_Off IS NOT NULL
         AND pm.Sobrematerial_Promedio IS NOT NULL
        THEN ROUND(p.Cant_Fabricacion * (pm.Largo_Pieza + pm.Frenteado + pm.Ancho_Cut_Off + pm.Sobrematerial_Promedio), 2)
        ELSE pm.MM_Totales
    END,
    pm.Longitud_Barra_Sin_Scrap = CASE
        WHEN pm.Longitud_Un_MP IS NOT NULL
         AND pm.Longitud_Un_MP > 0
         AND pm.Scrap_Maquina IS NOT NULL
        THEN ROUND((pm.Longitud_Un_MP * 1000) - pm.Scrap_Maquina, 2)
        ELSE pm.Longitud_Barra_Sin_Scrap
    END
WHERE pm.deleted_at IS NULL;

UPDATE pedido_cliente_mp pm
SET
    pm.Cant_Barras_MP = CASE
        WHEN pm.MM_Totales IS NOT NULL
         AND pm.MM_Totales > 0
         AND pm.Longitud_Barra_Sin_Scrap IS NOT NULL
         AND pm.Longitud_Barra_Sin_Scrap > 0
        THEN CEIL(pm.MM_Totales / pm.Longitud_Barra_Sin_Scrap)
        ELSE pm.Cant_Barras_MP
    END,
    pm.Cant_Piezas_Por_Barra = CASE
        WHEN pm.Largo_Total_Pieza IS NOT NULL
         AND pm.Largo_Total_Pieza > 0
         AND pm.Longitud_Barra_Sin_Scrap IS NOT NULL
         AND pm.Longitud_Barra_Sin_Scrap > 0
        THEN FLOOR((pm.Longitud_Barra_Sin_Scrap / pm.Largo_Total_Pieza) * 100) / 100
        ELSE pm.Cant_Piezas_Por_Barra
    END
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- DIAGNOSTICO FINAL
-- ============================================================

SELECT
    COUNT(*) AS total_registros,
    SUM(CASE WHEN (pm.Id_Maquina IS NULL OR pm.Nro_Maquina IS NULL OR pm.Familia_Maquina IS NULL) THEN 1 ELSE 0 END) AS filas_con_maquina_incompleta,
    SUM(CASE WHEN pm.Largo_Pieza IS NULL OR pm.Largo_Pieza = 0 THEN 1 ELSE 0 END) AS filas_sin_largo_pieza,
    SUM(CASE WHEN pm.Largo_Total_Pieza IS NULL OR pm.Largo_Total_Pieza = 0 THEN 1 ELSE 0 END) AS filas_sin_largo_total,
    SUM(CASE WHEN pm.MM_Totales IS NULL OR pm.MM_Totales = 0 THEN 1 ELSE 0 END) AS filas_sin_mm_totales,
    SUM(CASE WHEN pm.Longitud_Barra_Sin_Scrap IS NULL OR pm.Longitud_Barra_Sin_Scrap = 0 THEN 1 ELSE 0 END) AS filas_sin_longitud_barra_sin_scrap,
    SUM(CASE WHEN pm.Cant_Piezas_Por_Barra IS NULL OR pm.Cant_Piezas_Por_Barra = 0 THEN 1 ELSE 0 END) AS filas_sin_cant_piezas_por_barra
FROM pedido_cliente_mp pm
WHERE pm.deleted_at IS NULL;

SELECT
    pm.Id_Pedido_MP,
    p.Nro_OF,
    pm.Id_Maquina,
    pm.Nro_Maquina,
    pm.Familia_Maquina,
    pm.Scrap_Maquina,
    pm.Largo_Pieza,
    pm.Longitud_Un_MP,
    pm.Largo_Total_Pieza,
    pm.MM_Totales,
    pm.Longitud_Barra_Sin_Scrap,
    pm.Cant_Barras_MP,
    pm.Cant_Piezas_Por_Barra
FROM pedido_cliente_mp pm
LEFT JOIN pedido_cliente p
    ON p.Id_OF = pm.Id_OF
WHERE pm.deleted_at IS NULL
  AND (
      pm.Id_Maquina IS NULL
      OR pm.Nro_Maquina IS NULL
      OR pm.Familia_Maquina IS NULL
      OR pm.Largo_Pieza IS NULL OR pm.Largo_Pieza = 0
      OR pm.Largo_Total_Pieza IS NULL OR pm.Largo_Total_Pieza = 0
      OR pm.MM_Totales IS NULL OR pm.MM_Totales = 0
      OR pm.Longitud_Barra_Sin_Scrap IS NULL OR pm.Longitud_Barra_Sin_Scrap = 0
      OR pm.Cant_Piezas_Por_Barra IS NULL OR pm.Cant_Piezas_Por_Barra = 0
  )
ORDER BY p.Nro_OF, pm.Id_Pedido_MP;
