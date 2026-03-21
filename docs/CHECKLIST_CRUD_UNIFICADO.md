# CHECKLIST CRUD UNIFICADO

Usar este checklist antes de dar por terminado cualquier CRUD simple nuevo.

---

## 1. Base del modulo

- existe modelo con tabla y PK correctas
- usa `SoftDeletes` si la tabla tiene `deleted_at`
- tiene `fillable` correctos
- contempla `created_by`, `updated_by`, `deleted_by` si existen

---

## 2. Rutas

- existe `modulo.data`
- existe `modulo.resumen`
- existe `modulo.deleted`
- existe `modulo.restore`
- `Route::resource()` esta declarado despues de las rutas auxiliares

---

## 3. Controlador

- `index()` implementado
- `getData()` implementado
- `resumen()` implementado
- `show()` implementado
- `create()` implementado
- `store()` implementado
- `edit()` implementado
- `update()` implementado
- `destroy()` implementado
- `showDeleted()` implementado
- `restore()` implementado

---

## 4. Index

- usa `x-header-card`
- usa cards resumen
- tabla dentro de `card mt-3`
- DataTable con dos filas en `thead`
- fila 1 encabezados
- fila 2 filtros
- inputs con `filtro-texto`
- selects con `filtro-select`
- botonera con `Ver`, `Editar`, `Eliminar`

---

## 5. CSS del index

- carga `shared/cards.css`
- carga `shared/datatables.css`
- carga `shared/filters.css`
- carga `shared/buttons.css`
- carga `shared/summary-boxes.css`
- carga `modulo_reutilizable/modulo_index.css`
- el CSS especifico del modulo es minimo
- no repite estilos shared

---

## 6. Show

- existe `show.blade.php`
- usa `show-header`
- usa `partials.navigation`
- usa `shared/show-details.css`
- muestra estado en badge
- muestra auditoria si existe
- tiene botones `Volver` y `Editar`

---

## 7. Create y Edit

- siguen el mismo lenguaje visual del sistema
- labels claros
- botones consistentes
- validaciones correctas
- manejo de auditoria correcto

---

## 8. Deleted

- existe vista `deleted`
- lista registros eliminados
- permite restaurar
- mantiene misma linea visual del modulo

---

## 9. DataTable

- `searching: false`
- filtros conectados
- `table.ajax.reload(null, false)` en filtros y delete
- idioma en español
- acciones no ordenables ni buscables

---

## 10. Verificacion final

- index se ve igual al patron visual del sistema
- show se ve igual al patron visual del sistema
- las cards resumen cargan
- la botonera se ve completa
- el borde azul/rojo de filtros funciona
- no quedaron CSS legacy duplicando estilos base

---

## 11. Referencia de calidad

Si hay dudas, comparar con:

- `proveedores`
- `marcas_insumos`
- `materia_prima/materias_base`
- `materia_prima/diametro`
