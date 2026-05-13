-- Reset operativo de fechas_of sin perder la relacion 1:1 con pedido_cliente.
-- Uso sugerido antes de reimportar un CSV actualizado.

UPDATE fechas_of
SET
    Nro_Programa_H1 = NULL,
    Nro_Programa_H2 = NULL,
    Inicio_PAP = '9999-12-31',
    Hora_Inicio_PAP = '00:00:00',
    Fin_PAP = '9999-12-31',
    Hora_Fin_PAP = '00:00:00',
    Inicio_OF = '9999-12-31',
    Finalizacion_OF = '9999-12-31',
    Tiempo_Pieza = 0.00,
    Tiempo_Seg = 0,
    updated_at = NOW();

-- Si faltara alguna OF historica en fechas_of, este insert la repone.
INSERT INTO fechas_of (
    Id_OF,
    Nro_OF_fechas,
    Inicio_PAP,
    Hora_Inicio_PAP,
    Fin_PAP,
    Hora_Fin_PAP,
    Inicio_OF,
    Finalizacion_OF,
    Tiempo_Pieza,
    Tiempo_Seg,
    reg_Status,
    created_at,
    updated_at,
    created_by,
    updated_by
)
SELECT
    p.Id_OF,
    p.Nro_OF,
    '9999-12-31',
    '00:00:00',
    '9999-12-31',
    '00:00:00',
    '9999-12-31',
    '9999-12-31',
    0.00,
    0,
    b'1',
    NOW(),
    NOW(),
    p.created_by,
    COALESCE(p.updated_by, p.created_by)
FROM pedido_cliente p
LEFT JOIN fechas_of f
    ON f.Id_OF = p.Id_OF
WHERE f.Id_OF IS NULL;
