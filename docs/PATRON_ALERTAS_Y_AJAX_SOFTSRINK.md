# PATRON ALERTAS Y AJAX SOFTSRINK

## Objetivo

Este documento define el patrón oficial de SoftSrink_App_v2 para:

- alertas visuales con SweetAlert2
- formularios AJAX de alta
- formularios AJAX de edición
- respuestas JSON desde controladores
- tratamiento de errores 422 / 400 / 500
- manejo de duplicados por fila
- redirecciones automáticas luego del éxito

Este patrón está basado en el uso de:

- `public/js/form-ajax-submit.js`
- `public/js/form-edit-check.js`

---

# 1. Scripts globales oficiales

## Script para formularios de alta AJAX
Ubicación:
```text id="6r8b6e"
public/js/form-ajax-submit.js

Notas! 08/03/2026:

Se usa en:

create.blade.php

formularios multifila

formularios simples que guardan vía AJAX

Script para formularios de edición AJAX

Ubicación:

public/js/form-edit-check.js
Se usa en:

edit.blade.php

formularios individuales de actualización

formularios donde se quiera detectar “sin cambios”

2. Librería visual oficial

La librería estándar de alertas es:

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
Regla

Todo módulo que use:

create AJAX

edit AJAX

confirmación de delete

confirmación de restore

debe incluir SweetAlert2.

3. Patrón de formularios create con AJAX
Formulario obligatorio

Todo create.blade.php que trabaje con AJAX debe usar esta estructura:

<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">
    @csrf
</form>
Significado de atributos
data-ajax="true"

Le indica a form-ajax-submit.js que ese formulario debe interceptarse y enviarse por AJAX.

data-redirect-url

Indica adónde redirigir después del éxito si el backend no devuelve redirect.

4. Patrón de formularios edit con AJAX
Formulario obligatorio

Todo edit.blade.php que trabaje con AJAX debe usar esta estructura:

<form action="{{ route('modulo.update', $registro->Id_Modulo) }}"
      method="POST"
      data-edit-check="true"
      data-exclude-fields="_token,_method"
      data-redirect-url="{{ route('modulo.index') }}"
      data-success-message="Registro actualizado correctamente">
    @csrf
    @method('PUT')
</form>
Significado de atributos
data-edit-check="true"

Activa form-edit-check.js.

data-exclude-fields="_token,_method"

Evita que se comparen esos campos al detectar cambios.

data-redirect-url

Redirección posterior al éxito.

data-success-message

Texto personalizado del popup de éxito.

5. Patrón de respuesta JSON en create

Cuando el store() es AJAX, la respuesta estándar de éxito debe ser:

{
  "success": true,
  "message": "Registro creado correctamente.",
  "redirect": "/ruta/index"
}
Campos esperados
success

true = operación correcta

false = operación fallida

message

Texto para mostrar en SweetAlert.

redirect

Ruta de redirección posterior al éxito.

6. Patrón de respuesta JSON en update

Cuando el update() funciona con AJAX en edit.blade.php, la respuesta estándar de éxito debe ser:

{
  "success": true,
  "message": "Registro actualizado correctamente.",
  "redirect": "/ruta/index"
}
7. Patrón de respuesta para “sin cambios”

Cuando no hubo cambios al editar, el backend debe devolver algo como:

{
  "success": false,
  "type": "no_changes",
  "message": "No se realizaron cambios."
}

Esto permite que form-edit-check.js muestre una alerta especial de advertencia.

8. Patrón de errores de validación 422

Cuando Laravel valida y falla, la respuesta esperada es:

{
  "success": false,
  "errors": {
    "Campo_1": [
      "El campo Campo_1 es obligatorio."
    ]
  }
}
Comportamiento esperado

form-ajax-submit.js debe:

detectar status 422

construir HTML con los errores

mostrar popup de SweetAlert con lista de errores

9. Patrón de error general 400 / 500

Cuando algo falla en backend y no es una validación 422, la respuesta debe ser:

{
  "success": false,
  "message": "Ocurrió un error al guardar el registro."
}
Comportamiento esperado

El JS debe mostrar:

ícono error

título “Error”

mensaje devuelto por backend

10. Patrón para duplicados por fila

Este caso aplica sobre todo en módulos multifila:

pedido_cliente

fabricacion

mp_ingresos si corresponde

Respuesta estándar
{
  "success": false,
  "message": "Algunas filas tienen errores de validación.",
  "duplicatedRows": [2, 4, 5]
}
Comportamiento esperado del JS

form-ajax-submit.js debe:

mostrar alerta warning

indicar qué filas tienen duplicados

marcar esas filas en rojo

hacer scroll a la primera fila duplicada

11. Patrón de confirmación para eliminar

Toda eliminación AJAX debe usar SweetAlert antes del request.

Estructura estándar
Swal.fire({
    title: '¿Estás seguro?',
    text: "¡No podrás revertir esto!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminarlo'
}).then((result) => {
    if (result.isConfirmed) {
        // AJAX DELETE
    }
});
Respuesta esperada del backend para delete exitoso
{
  "success": true,
  "message": "Registro eliminado correctamente."
}
Comportamiento esperado del frontend

mostrar popup de éxito

recargar DataTable sin refrescar toda la página

o redirigir si corresponde

12. Patrón de confirmación para restaurar

Toda restauración debe usar SweetAlert antes del POST.

Estructura estándar
Swal.fire({
    title: '¿Estás seguro?',
    text: "¿Quieres restaurar este registro?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, restaurarlo'
}).then((result) => {
    if (result.isConfirmed) {
        // AJAX POST restore
    }
});
Comportamiento esperado

popup de éxito

redirect al index

o recarga de tabla si aplica

13. Patrón visual de alertas
Éxito

Usar:

icon: success

Ejemplo:

Swal.fire({
    icon: 'success',
    title: 'Éxito',
    text: 'Operación realizada correctamente.'
});
Error

Usar:

icon: error

Ejemplo:

Swal.fire({
    icon: 'error',
    title: 'Error',
    text: 'Ocurrió un error.'
});
Advertencia

Usar:

icon: warning

Ejemplo:

Swal.fire({
    icon: 'warning',
    title: 'Advertencia',
    text: 'No se detectaron cambios.'
});
14. Reglas para form-ajax-submit.js

Este script debe encargarse de:

interceptar submit de forms con data-ajax="true"

enviar FormData

procesar respuestas JSON

detectar 422

detectar duplicados por fila

mostrar SweetAlert

redirigir en éxito

Qué no debe hacer el formulario

No debe tener lógica AJAX personalizada duplicada si ya usa form-ajax-submit.js, salvo casos especiales.

Cuándo sí se justifica JS específico en la vista

Cuando el módulo tenga:

tabla dinámica

correlativos automáticos

cálculos por fila

selects dependientes

reglas técnicas especiales

En esos casos:

el submit lo sigue resolviendo form-ajax-submit.js

pero la lógica de generación de filas vive en la vista

15. Reglas para form-edit-check.js

Este script debe encargarse de:

guardar estado original del formulario

detectar si hubo cambios reales

bloquear submit si no hubo cambios

enviar update por AJAX si hubo cambios

mostrar popup de éxito, warning o error

redirigir al index

Ventaja

Evita:

updates innecesarios

guardar sin cambios

ruido en logs y trazabilidad

16. Reglas de backend para que el JS funcione bien
En store() y update():

devolver siempre JSON si el form usa AJAX

no mezclar redirect HTML en esos casos

incluir message

incluir redirect cuando corresponda

En destroy() AJAX:

devolver JSON

no devolver back()

En restore():

Puede usar:

redirect clásico

o AJAX + JSON

Pero conviene unificar criterio por módulo.

17. Buenas prácticas
Sí hacer

centralizar lógica AJAX en scripts globales

usar mensajes claros

usar SweetAlert siempre

devolver JSON consistente

loguear errores en backend

usar títulos claros: Éxito / Error / Advertencia

No hacer

mezclar alertas alert() del navegador si ya usás SweetAlert

devolver HTML en un endpoint AJAX

tener 3 formas distintas de responder el mismo tipo de operación

duplicar el mismo JS en muchas vistas sin necesidad

18. Casos típicos dentro de SoftSrink
Create simple

Ejemplo:

estado_planificacion.create

marcas_insumos.create

Patrón:

data-ajax="true"

form-ajax-submit.js

Edit simple

Ejemplo:

mp_ingresos.edit

pedido_cliente.edit

estado_planificacion.edit

Patrón:

data-edit-check="true"

form-edit-check.js

Create multifila

Ejemplo:

pedido_cliente.create

fabricacion.create

mp_ingresos.create

Patrón:

lógica de filas en la vista

submit final por form-ajax-submit.js

Delete AJAX en index

Ejemplo:

mp_ingresos.index

otros módulos con DataTable

Patrón:

confirmación SweetAlert

DELETE por AJAX

recarga del DataTable

19. Errores comunes a evitar
1. El formulario no tiene data-ajax="true"

Entonces el submit no será interceptado.

2. El formulario edit no tiene data-edit-check="true"

Entonces no detectará “sin cambios”.

3. El backend devuelve redirect clásico en vez de JSON

Rompe la lógica AJAX.

4. Falta message en la respuesta

La alerta queda vacía o genérica.

5. Falta redirect

No vuelve al index después del éxito.

6. No loguear errores backend

Dificulta depuración.

7. Duplicar lógica AJAX dentro de cada vista

Hace el proyecto más difícil de mantener.

20. Patrón oficial recomendado
Create

SweetAlert2

form-ajax-submit.js

respuesta JSON

Edit

SweetAlert2

form-edit-check.js

respuesta JSON

Delete

SweetAlert2

AJAX

recarga DataTable

Restore

SweetAlert2

POST

redirect o recarga

21. Archivos relacionados

Revisar también:

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_VISTAS_BLADE_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

22. Nota final

En SoftSrink_App_v2, toda operación importante del usuario debe intentar cumplir estas 3 reglas:

dar feedback visual claro

no recargar innecesariamente la página

mantener consistencia entre módulos

Por eso:

SweetAlert2 es obligatorio como patrón visual

AJAX es el patrón principal en create/edit/delete

las respuestas JSON deben ser consistentes