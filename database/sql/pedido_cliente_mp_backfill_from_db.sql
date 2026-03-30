-- Backfill seguro para completar pedido_cliente_mp usando datos ya existentes
-- en la base actual. Este script:
-- 1. Completa datos del ingreso MP por JOIN con mp_ingreso/mp_materia_prima/mp_diametro
-- 2. Completa datos de maquina por JOIN con maquinas_produc cuando exista Id_Maquina
-- 3. Completa Largo_Pieza desde pedido_cliente/productos
-- 4. Asigna defaults de calculo cuando falten
-- 5. Recalcula campos derivados
-- 6. Lista filas que siguen incompletas para revision manual
--
-- Recomendacion:
-- - Ejecutar primero los SELECT de diagnostico
-- - Hacer backup antes de correr los UPDATE en produccion

SET @frenteado_default := 0.50;
SET @ancho_cut_off_default := 1.00;
SET @sobrematerial_default := 0.50;

-- ============================================================
-- DIAGNOSTICO INICIAL
-- ============================================================

SELECT
    COUNT(*) AS total_registros,
    SUM(CASE WHEN (pm.Id_Maquina IS NULL OR pm.Nro_Maquina IS NULL OR pm.Familia_Maquina IS NULL) THEN 1 ELSE 0 END) AS filas_con_maquina_incompleta,
    SUM(CASE WHEN (pm.Codigo_MP IS NULL OR pm.Codigo_MP = '') THEN 1 ELSE 0 END) AS filas_sin_codigo_mp,
    SUM(CASE WHEN (pm.Materia_Prima IS NULL OR pm.Materia_Prima = '') THEN 1 ELSE 0 END) AS filas_sin_materia_prima,
    SUM(CASE WHEN (pm.Diametro_MP IS NULL OR pm.Diametro_MP = '') THEN 1 ELSE 0 END) AS filas_sin_diametro,
    SUM(CASE WHEN pm.Nro_Ingreso_MP IS NULL THEN 1 ELSE 0 END) AS filas_sin_nro_ingreso,
    SUM(CASE WHEN pm.Largo_Pieza IS NULL THEN 1 ELSE 0 END) AS filas_sin_largo_pieza,
    SUM(CASE WHEN pm.Largo_Total_Pieza IS NULL THEN 1 ELSE 0 END) AS filas_sin_largo_total,
    SUM(CASE WHEN pm.MM_Totales IS NULL THEN 1 ELSE 0 END) AS filas_sin_mm_totales,
    SUM(CASE WHEN pm.Longitud_Barra_Sin_Scrap IS NULL THEN 1 ELSE 0 END) AS filas_sin_longitud_barra_sin_scrap,
    SUM(CASE WHEN pm.Cant_Piezas_Por_Barra IS NULL THEN 1 ELSE 0 END) AS filas_sin_cant_piezas_por_barra
FROM pedido_cliente_mp pm
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- 1. COMPLETAR DATOS DEL INGRESO MP
-- ============================================================

UPDATE pedido_cliente_mp pm
INNER JOIN mp_ingreso i
    ON i.Nro_Ingreso_MP = pm.Nro_Ingreso_MP
   AND i.deleted_at IS NULL
   AND i.reg_Status = 1
LEFT JOIN mp_materia_prima mp
    ON mp.Id_Materia_Prima = i.Id_Materia_Prima
LEFT JOIN mp_diametro d
    ON d.Id_Diametro = i.Id_Diametro_MP
SET
    pm.Codigo_MP = CASE
        WHEN pm.Codigo_MP IS NULL OR pm.Codigo_MP = '' THEN i.Codigo_MP
        ELSE pm.Codigo_MP
    END,
    pm.Materia_Prima = CASE
        WHEN pm.Materia_Prima IS NULL OR pm.Materia_Prima = '' THEN mp.Nombre_Materia
        ELSE pm.Materia_Prima
    END,
    pm.Diametro_MP = CASE
        WHEN pm.Diametro_MP IS NULL OR pm.Diametro_MP = '' THEN d.Valor_Diametro
        ELSE pm.Diametro_MP
    END,
    pm.Nro_Certificado_MP = CASE
        WHEN pm.Nro_Certificado_MP IS NULL OR pm.Nro_Certificado_MP = '' THEN i.Nro_Certificado_MP
        ELSE pm.Nro_Certificado_MP
    END,
    pm.Longitud_Un_MP = CASE
        WHEN pm.Longitud_Un_MP IS NULL OR pm.Longitud_Un_MP = 0 THEN i.Longitud_Unidad_MP
        ELSE pm.Longitud_Un_MP
    END
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- 2. COMPLETAR DATOS DE MAQUINA CUANDO YA EXISTA Id_Maquina
-- ============================================================

UPDATE pedido_cliente_mp pm
INNER JOIN maquinas_produc m
    ON m.id_maquina = pm.Id_Maquina
   AND m.Status = 1
SET
    pm.Nro_Maquina = CASE
        WHEN pm.Nro_Maquina IS NULL OR pm.Nro_Maquina = '' THEN m.Nro_maquina
        ELSE pm.Nro_Maquina
    END,
    pm.Familia_Maquina = CASE
        WHEN pm.Familia_Maquina IS NULL OR pm.Familia_Maquina = '' THEN m.familia_maquina
        ELSE pm.Familia_Maquina
    END,
    pm.Scrap_Maquina = CASE
        WHEN pm.Scrap_Maquina IS NULL OR pm.Scrap_Maquina = 0 THEN m.scrap_maquina
        ELSE pm.Scrap_Maquina
    END
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- 3. COMPLETAR LARGO_PIEZA DESDE PEDIDO/PRODUCTO
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
-- 4. ASIGNAR DEFAULTS DE CALCULO CUANDO FALTEN
-- ============================================================

UPDATE pedido_cliente_mp pm
SET
    pm.Frenteado = CASE
        WHEN pm.Frenteado IS NULL THEN @frenteado_default
        ELSE pm.Frenteado
    END,
    pm.Ancho_Cut_Off = CASE
        WHEN pm.Ancho_Cut_Off IS NULL THEN @ancho_cut_off_default
        ELSE pm.Ancho_Cut_Off
    END,
    pm.Sobrematerial_Promedio = CASE
        WHEN pm.Sobrematerial_Promedio IS NULL THEN @sobrematerial_default
        ELSE pm.Sobrematerial_Promedio
    END
WHERE pm.deleted_at IS NULL;

-- ============================================================
-- 5. RECALCULAR CAMPOS DERIVADOS
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
    SUM(CASE WHEN (pm.Codigo_MP IS NULL OR pm.Codigo_MP = '') THEN 1 ELSE 0 END) AS filas_sin_codigo_mp,
    SUM(CASE WHEN (pm.Materia_Prima IS NULL OR pm.Materia_Prima = '') THEN 1 ELSE 0 END) AS filas_sin_materia_prima,
    SUM(CASE WHEN (pm.Diametro_MP IS NULL OR pm.Diametro_MP = '') THEN 1 ELSE 0 END) AS filas_sin_diametro,
    SUM(CASE WHEN pm.Nro_Certificado_MP IS NULL OR pm.Nro_Certificado_MP = '' THEN 1 ELSE 0 END) AS filas_sin_certificado,
    SUM(CASE WHEN pm.Longitud_Un_MP IS NULL OR pm.Longitud_Un_MP = 0 THEN 1 ELSE 0 END) AS filas_sin_longitud_unidad,
    SUM(CASE WHEN pm.Largo_Pieza IS NULL OR pm.Largo_Pieza = 0 THEN 1 ELSE 0 END) AS filas_sin_largo_pieza,
    SUM(CASE WHEN pm.Largo_Total_Pieza IS NULL OR pm.Largo_Total_Pieza = 0 THEN 1 ELSE 0 END) AS filas_sin_largo_total,
    SUM(CASE WHEN pm.MM_Totales IS NULL OR pm.MM_Totales = 0 THEN 1 ELSE 0 END) AS filas_sin_mm_totales,
    SUM(CASE WHEN pm.Longitud_Barra_Sin_Scrap IS NULL OR pm.Longitud_Barra_Sin_Scrap = 0 THEN 1 ELSE 0 END) AS filas_sin_longitud_barra_sin_scrap,
    SUM(CASE WHEN pm.Cant_Piezas_Por_Barra IS NULL OR pm.Cant_Piezas_Por_Barra = 0 THEN 1 ELSE 0 END) AS filas_sin_cant_piezas_por_barra
FROM pedido_cliente_mp pm
WHERE pm.deleted_at IS NULL;

-- Filas que aun requieren fuente externa o revision manual
SELECT
    pm.Id_Pedido_MP,
    pm.Id_OF,
    p.Nro_OF,
    pm.Id_Maquina,
    pm.Nro_Maquina,
    pm.Familia_Maquina,
    pm.Scrap_Maquina,
    pm.Nro_Ingreso_MP,
    pm.Codigo_MP,
    pm.Materia_Prima,
    pm.Diametro_MP,
    pm.Nro_Certificado_MP,
    pm.Longitud_Un_MP,
    pm.Largo_Pieza,
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
      OR pm.Codigo_MP IS NULL OR pm.Codigo_MP = ''
      OR pm.Materia_Prima IS NULL OR pm.Materia_Prima = ''
      OR pm.Diametro_MP IS NULL OR pm.Diametro_MP = ''
      OR pm.Nro_Certificado_MP IS NULL OR pm.Nro_Certificado_MP = ''
      OR pm.Longitud_Un_MP IS NULL OR pm.Longitud_Un_MP = 0
      OR pm.Largo_Pieza IS NULL OR pm.Largo_Pieza = 0
      OR pm.Largo_Total_Pieza IS NULL OR pm.Largo_Total_Pieza = 0
      OR pm.MM_Totales IS NULL OR pm.MM_Totales = 0
      OR pm.Longitud_Barra_Sin_Scrap IS NULL OR pm.Longitud_Barra_Sin_Scrap = 0
      OR pm.Cant_Piezas_Por_Barra IS NULL OR pm.Cant_Piezas_Por_Barra = 0
  )
ORDER BY p.Nro_OF, pm.Id_Pedido_MP;
