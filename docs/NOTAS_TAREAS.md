12/3/2026

*Ver de realizar la entrada de marcas insumos asi con multiples entradas de un mismo proveedor, ya
que ahora esta asi para una entrada unitaria:


- Si el CSV historico trae Salidas_Final y Mts. Totales, esos valores se respetan en la importacion. Si vienen vacios, Laravel los recalcula con la formula vigente.
- Ajuste_Stock puede ser positivo o negativo: positivo suma al stock final y negativo lo reduce.


## Regla de filtros numericos exactos
- Para columnas numericas (`Nro`, cantidades, metros, longitudes), si el usuario escribe un valor numerico en el filtro, la busqueda debe ser por coincidencia exacta (`=` en backend o regex `^valor$` en DataTables cliente).
- Solo usar `like %valor%` cuando el filtro sea texto real no numerico.
- Esta regla ya se aplica en `mp_salidas_iniciales`, `mp_ingresos`, `mp_egresos`, `mp_movimientos_adicionales` y `materia_prima/stock`.
