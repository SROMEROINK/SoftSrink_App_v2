## C:\laragon\www\SoftSrink_App_v2\docs\CHECKLIST_NUEVO_MODULO.md# CHECKLIST NUEVO MODULO

## Objetivo

Este checklist se utiliza cada vez que se crea un nuevo módulo dentro de `SoftSrink_App_v2`.

Sirve para:
- no olvidar archivos importantes
- mantener el mismo patrón del proyecto
- validar que el CRUD quede completo
- unificar criterios entre módulos simples y multifila

---

# 1. Datos básicos del módulo

## Nombre del módulo
- [ ] Definido

## Tipo de módulo
- [ ] CRUD simple
- [ ] CRUD multifila

## Nombre de tabla en base de datos
- [ ] Definido

## Clave primaria
- [ ] Definida

## SoftDeletes
- [ ] Requerido
- [ ] No requerido

## Usa `reg_Status`
- [ ] Sí
- [ ] No

## Usa auditoría
- [ ] `created_by`
- [ ] `updated_by`
- [ ] `deleted_by`

---

# 2. Base de datos

## Tabla
- [ ] Tabla creada
- [ ] Nombre correcto
- [ ] Clave primaria correcta
- [ ] `deleted_at` existe si usa SoftDeletes
- [ ] `created_at` existe
- [ ] `updated_at` existe

## Campos de auditoría
- [ ] `created_by`
- [ ] `updated_by`
- [ ] `deleted_by`

## Relaciones
- [ ] Foreign keys revisadas
- [ ] Nombres de columnas validados
- [ ] Relaciones consistentes con modelos

## Restricciones
- [ ] Unique revisado
- [ ] Campos nullable revisados
- [ ] Defaults revisados

---

# 3. Modelo

Archivo:
```text
app/Models/NombreModelo.php

Notas! 08/03/2026:

Validaciones del modelo

 namespace correcto

 use SoftDeletes si corresponde

 $table correcto

 $primaryKey correcto

 $incrementing revisado si aplica

 $keyType revisado

 public $timestamps = true si corresponde

 $fillable completo

 $casts definidos si hace falta

Relaciones

 belongsTo() necesarios

 hasMany() necesarios

 creator() si aplica

 updater() si aplica

 deleter() si aplica

4. Controlador

Archivo:

app/Http/Controllers/NombreController.php
Imports

 Request

 Auth

 Log

 DB

 Rule

 modelo correcto

 CheckForChanges si hay edit individual

Métodos mínimos

 index()

 getData()

 create()

 store()

 show()

 edit()

 update()

 destroy()

Métodos extra si aplica

 resumen()

 getUniqueFilters()

 showDeleted()

 restore()

 métodos AJAX auxiliares

 correlativo / último número

 carga dependiente por select

 funciones de detalle técnico

Validación

 reglas store() correctas

 reglas update() correctas

 whereNull('deleted_at') si corresponde

 ignore($id, 'PK') en update si corresponde

Lógica de guardado

 usa transacción si corresponde

 guarda created_by

 guarda updated_by

 guarda deleted_by

 devuelve JSON si se usa AJAX

 devuelve redirect si corresponde

5. Rutas

Archivo:

routes/web.php
Rutas mínimas

 Route::resource(...)

 ruta data

 ruta filters si aplica

 ruta resumen si aplica

 ruta deleted

 ruta restore

Revisión

 nombres de rutas correctos

 controller correcto

 no hay duplicados

 orden correcto dentro del grupo middleware

 permisos/middleware revisados

6. Vista index

Archivo:

resources/views/modulo/index.blade.php
Estructura

 @extends('adminlte::page')

 @section('title')

 @section('content_header')

 usa x-header-card si corresponde

 @section('content')

 DataTable creado

 filtros creados

 columna acciones creada

Cards resumen

 total

 activos

 eliminados

DataTable

 ajax configurado

 columnas correctas

 botones ver/editar/eliminar

 filtros conectados

 clearFilters funcionando

 searching: false si se usan filtros por columna

JS

 carga resumen

 eliminación con SweetAlert

 recarga de tabla tras eliminar

 carga de filtros únicos si aplica

7. Vista create

Archivo:

resources/views/modulo/create.blade.php
Estructura

 formulario creado

 @csrf

 action() correcto

 campos correctos

 botón guardar

 botón volver/cancelar

AJAX

 data-ajax="true"

 data-redirect-url

 usa form-ajax-submit.js

Validación visual

 required donde corresponde

 selects cargados correctamente

 placeholders correctos

 defaults correctos

Si es multifila

 tabla dinámica creada

 tbody dinámico

 botón agregar fila

 botón eliminar fila

 primera fila autogenerada

 arrays campo[] correctos

 correlativo funciona

 cálculos por fila funcionan

8. Vista edit

Archivo:

resources/views/modulo/edit.blade.php
Estructura

 action() correcto

 @csrf

 @method('PUT')

 campos cargan valor actual

 botón actualizar

 botón cancelar

Script global

 data-edit-check="true"

 data-exclude-fields="_token,_method"

 data-redirect-url

 data-success-message

 usa form-edit-check.js

Validación

 update devuelve JSON correcto

 detecta sin cambios

 muestra alerta de éxito

 redirige bien al index

9. Vista show

Archivo:

resources/views/modulo/show.blade.php
Revisión

 muestra datos principales

 muestra relaciones si corresponde

 muestra estado

 muestra created_at

 muestra updated_at

 botón volver

10. Vista deleted

Archivo:

resources/views/modulo/deleted.blade.php
Revisión

 usa onlyTrashed()

 lista registros eliminados

 botón restaurar

 confirmación con SweetAlert

 restore funciona

 vuelve al index correctamente

11. CSS

Ubicación:

public/vendor/adminlte/dist/css/
Archivos creados

 modulo_index.css

 modulo_create.css

 modulo_edit.css

 modulo_show.css

 modulo_deleted.css

Revisión

 estilos cargan correctamente

 nombre del archivo coincide con la vista

 no rompe otros módulos

 tabla se ve bien

 formularios se ven bien

 botones se ven bien

 responsive aceptable

12. Scripts globales
form-ajax-submit.js

 create funciona con AJAX

 errores 422 se muestran bien

 errores 400/500 se muestran bien

 redirect funciona

 duplicados por fila se muestran si aplica

form-edit-check.js

 detecta cambios

 detecta "sin cambios"

 submit por AJAX funciona

 mensaje de éxito funciona

 redirect funciona

13. Soft delete
Revisión

 use SoftDeletes en modelo

 destroy() hace soft delete

 deleted_by se guarda

 index() no muestra eliminados

 showDeleted() sí los muestra

 restore() funciona

Regla de negocio

 puede eliminarse libremente

 no puede eliminarse si tiene uso posterior

 validación de bloqueo de eliminación implementada si corresponde

14. Auditoría
Crear

 guarda created_by

Actualizar

 guarda updated_by

Eliminar

 guarda deleted_by

Revisado en base

 valores se guardan correctamente

 timestamps correctos

15. Validaciones funcionales finales
Create

 guarda correctamente

 muestra éxito

 redirige al index

Edit

 actualiza correctamente

 detecta sin cambios

 muestra éxito

 redirige al index

Delete

 elimina correctamente

 no rompe DataTable

 recarga la tabla

 resumen se mantiene coherente

Restore

 restaura correctamente

 vuelve al index

 vuelve a aparecer en activos

16. Filtros y DataTables
Filtros

 texto

 select

 fechas

 numéricos

Data

 getUniqueFilters() funciona

 getData() respeta filtros

 recordsTotal correcto

 recordsFiltered correcto

UI

 clearFilters limpia todo

 paginación correcta

 orden correcto

 scroll correcto si aplica

17. Si el módulo es multifila
Create

 arrays correctos

 store() recorre filas

 transacción activa

 primera fila se genera bien

 agregar fila funciona

 eliminar fila funciona

 correlativo funciona

 cálculos por fila funcionan

 selects dependientes funcionan

 duplicatedRows funciona si aplica

Edit

 edit es individual

 update es individual

 no rompe trazabilidad

18. Nombres y consistencia

 nombres de archivos coherentes

 nombres de rutas coherentes

 nombres de variables coherentes

 nombres de vistas coherentes

 nombres de css coherentes

 nombres de columnas consistentes con DB

19. Referencias de patrones

Antes de cerrar un módulo nuevo, revisar:

 PATRON_CRUD_SIMPLE_SOFTSRINK.md

 PATRON_CRUD_MULTIFILA_SOFTSRINK.md

 PATRON_MODELOS_SOFTSRINK.md

 PATRON_CONTROLADORES_SOFTSRINK.md

 PATRON_RUTAS_SOFTSRINK.md

 PATRON_VISTAS_BLADE_SOFTSRINK.md

 PATRON_DATATABLES_SOFTSRINK.md

 PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

20. Estado final del módulo
Nombre del módulo:

____________________________

Tipo:

 simple

 multifila

Estado actual:

 en desarrollo

 funcional

 pendiente pruebas

 terminado