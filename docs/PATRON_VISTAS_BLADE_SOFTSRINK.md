# PATRON VISTAS BLADE SOFTSRINK

## Objetivo

Este documento define el patrón recomendado para las vistas Blade dentro de `SoftSrink_App_v2`.

Busca unificar:

- estructura general de las vistas
- uso de `adminlte::page`
- títulos y encabezados
- formularios simples y multifila
- integración con DataTables
- integración con SweetAlert2
- integración con scripts globales AJAX
- organización de botones
- uso de CSS por vista
- estructura de `index`, `create`, `edit`, `show` y `deleted`

---

# 1. Base obligatoria de las vistas

Toda vista principal del proyecto debe partir de:

```blade id="8l0f5f"
@extends('adminlte::page')

Notas! 08/03/2026:

Y normalmente debe incluir:

@section('title')

@section('content_header')

@section('content')

@section('css')

@section('js') si aplica

2. Estructura mínima recomendada
@extends('adminlte::page')

@section('title', 'Título de la vista')

@section('content_header')
    <h1>Título visible de la vista</h1>
@stop

@section('content')
    ...
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_vista.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop
3. Reglas generales de diseño
Sí hacer

mantener títulos claros

respetar el mismo estilo entre módulos

usar cards para separar bloques

usar formularios con form-group

usar form-control

mantener botones consistentes

usar tablas limpias y legibles

separar CSS por vista cuando sea necesario

No hacer

mezclar demasiada lógica de negocio dentro del Blade

repetir el mismo JS en 10 vistas distintas si ya existe script global

usar nombres inconsistentes entre ruta, vista y módulo

usar ids repetidos en filas dinámicas

4. Vista index.blade.php
Objetivo

Mostrar el listado principal del módulo.

Debe incluir normalmente

header o x-header-card

cards resumen si aplica

DataTable

filtros por columna

columna acciones

botón limpiar filtros

JS de carga AJAX y eliminación

Patrón base de index
@extends('adminlte::page')

@section('title', 'Nombre del Módulo')

@section('content_header')
<x-header-card 
    title="Nombre del Módulo"
    quantityTitle="Total de registros:"
    quantity="{{ $totalRegistros }}"
    buttonRoute="{{ route('modulo.create') }}"
    buttonText="Crear Registro"
    deletedRouteUrl="{{ route('modulo.deleted') }}"
    deletedButtonText="Ver Eliminados"
/>
@stop

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="total-registros">-</h3>
                    <p>Total de registros</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table id="tabla_modulo" class="table table-striped table-bordered w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Campo 1</th>
                    <th>Campo 2</th>
                    <th>Acciones</th>
                </tr>
                <tr class="filter-row">
                    <th></th>
                    <th><input type="text" id="filtro_campo_1" class="form-control filtro-texto" placeholder="Filtrar Campo 1"></th>
                    <th><input type="text" id="filtro_campo_2" class="form-control filtro-texto" placeholder="Filtrar Campo 2"></th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@stop
5. Cuándo usar x-header-card

Usar x-header-card cuando el módulo tenga:

título principal

cantidad total visible

botón crear

botón ver eliminados

Ejemplo real compatible con SoftSrink
<x-header-card 
    title="Ingresos de Materia Prima"
    quantityTitle="Total de Unidades Ingresadas:"
    quantity="{{ $totalIngresos }}"
    buttonRoute="{{ route('mp_ingresos.create') }}"
    buttonText="Crear Ingreso"
    deletedRouteUrl="{{ route('mp_ingresos.deleted') }}"
    deletedButtonText="Ver Ingresos Eliminados"
/>
6. Vista create.blade.php simple
Objetivo

Crear un solo registro por vez.

Reglas

usar @csrf

usar data-ajax="true" si el patrón es AJAX

usar data-redirect-url

incluir botón guardar y volver

usar form-ajax-submit.js

Ejemplo base simple
@extends('adminlte::page')

@section('title', 'Crear Registro')

@section('content_header')
    <h1>Crear Registro</h1>
@stop

@section('content')
<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">

    @csrf

    <div class="form-group">
        <label for="Campo_1">Campo 1</label>
        <input type="text" name="Campo_1" id="Campo_1" class="form-control" required>
    </div>

    <div class="form-group">
        <label for="Campo_2">Campo 2</label>
        <input type="text" name="Campo_2" id="Campo_2" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="{{ route('modulo.index') }}" class="btn btn-default">Cancelar</a>
</form>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
@stop
7. Vista create.blade.php multifila
Objetivo

Cargar varias filas en una sola operación.

Se usa en módulos como

pedido_cliente.create

fabricacion.create

mp_ingresos.create

Reglas

formulario con data-ajax="true"

tabla dinámica

tbody gestionado por JS

botón agregar fila

botón guardar

campos tipo campo[]

usar form-ajax-submit.js

Patrón base multifila
<form method="POST"
      action="{{ route('modulo.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('modulo.index') }}">

    @csrf

    <table class="table table-bordered" id="tablaModulo">
        <thead>
            <tr>
                <th>N° Fila</th>
                <th>Campo 1</th>
                <th>Campo 2</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <div class="btn-der">
        <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>
8. Reglas de JS en vistas multifila

Cuando el módulo sea multifila, el JS de la vista debe encargarse de:

generar filas

numerar filas

manejar correlativos

eliminar filas

cálculos automáticos por fila

selects dependientes si existen

Regla técnica importante

Siempre trabajar con:

var $fila = $(this).closest('tr');

para modificar solo la fila actual.

9. Vista edit.blade.php
Objetivo

Editar un único registro.

Reglas obligatorias

@csrf

@method('PUT')

data-edit-check="true"

data-exclude-fields="_token,_method"

data-redirect-url

data-success-message

script form-edit-check.js

Ejemplo base
@extends('adminlte::page')

@section('title', 'Editar Registro')

@section('content_header')
    <h1>Editar Registro</h1>
@stop

@section('content')
<form action="{{ route('modulo.update', $registro->Id_Modulo) }}"
      method="POST"
      data-edit-check="true"
      data-exclude-fields="_token,_method"
      data-redirect-url="{{ route('modulo.index') }}"
      data-success-message="Registro actualizado correctamente">

    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="Campo_1">Campo 1</label>
        <input type="text" class="form-control" id="Campo_1" name="Campo_1" value="{{ $registro->Campo_1 }}" required>
    </div>

    <div class="form-group">
        <label for="Campo_2">Campo 2</label>
        <input type="text" class="form-control" id="Campo_2" name="Campo_2" value="{{ $registro->Campo_2 }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('modulo.index') }}" class="btn btn-default">Cancelar</a>
</form>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
@stop
10. Vista show.blade.php
Objetivo

Mostrar un registro en modo detalle.

Reglas

diseño limpio

mostrar campos clave

mostrar relaciones si existen

botón volver

no sobrecargar de JS innecesario

Ejemplo base
@extends('adminlte::page')

@section('title', 'Detalle del Registro')

@section('content_header')
    <h1>Detalle del Registro</h1>
@stop

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Detalle del registro</h3>
        </div>
        <div class="card-body">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><strong>Campo 1:</strong> {{ $registro->Campo_1 }}</li>
                <li class="list-group-item"><strong>Campo 2:</strong> {{ $registro->Campo_2 }}</li>
                <li class="list-group-item"><strong>Creado en:</strong> {{ $registro->created_at }}</li>
                <li class="list-group-item"><strong>Actualizado en:</strong> {{ $registro->updated_at }}</li>
            </ul>
        </div>
        <div class="card-footer">
            <a href="{{ route('modulo.index') }}" class="btn btn-default">Volver a la lista</a>
        </div>
    </div>
</div>
@stop
11. Vista deleted.blade.php
Objetivo

Mostrar registros eliminados y restaurarlos.

Reglas

listar registros onlyTrashed()

botón restaurar por fila

confirmación con SweetAlert

volver al index

Ejemplo base
@extends('adminlte::page')

@section('title', 'Registros Eliminados')

@section('content')
<div class="container">
    <h1>Registros Eliminados</h1>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Campo 1</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registrosEliminados as $registro)
            <tr>
                <td>{{ $registro->Id_Modulo }}</td>
                <td>{{ $registro->Campo_1 }}</td>
                <td>
                    <form method="POST" action="{{ route('modulo.restore', $registro->Id_Modulo) }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Restaurar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <a href="{{ route('modulo.index') }}" class="btn btn-return">Volver</a>
</div>
@stop
12. Reglas de formularios
Todo formulario debe revisar

@csrf

action() correcto

método correcto

name alineado con el controlador

valores old() cuando sea necesario

required donde corresponda

En edición

sumar:

@method('PUT')

13. Reglas de naming de campos
Regla

Los name="" de los inputs deben coincidir exactamente con lo que espera el controlador.

Ejemplo correcto

Si el controlador valida:

'Nombre_Estado' => 'required|string|max:50',

entonces en Blade:

<input type="text" name="Nombre_Estado" id="Nombre_Estado" class="form-control">
14. Reglas para selects
Sí hacer

cargar opciones desde backend si son catálogos simples

usar AJAX si dependen de otro select

marcar selected en edit

usar textos claros en <option>

Ejemplo
<select name="Status" id="Status" class="form-control" required>
    <option value="1" {{ old('Status', $registro->Status) == 1 ? 'selected' : '' }}>Activo</option>
    <option value="0" {{ old('Status', $registro->Status) == 0 ? 'selected' : '' }}>Inactivo</option>
</select>
15. Reglas para botones
Botones estándar del proyecto

btn-primary → guardar / actualizar

btn-success → crear / agregar / restaurar

btn-info → ver

btn-danger → eliminar

btn-default o btn-secondary → volver / cancelar

btn-dark → limpiar filtros

Regla

Mantener la semántica del color entre módulos.

16. Reglas para CSS por vista

Cada vista importante puede tener un CSS específico.

Ubicación
public/vendor/adminlte/dist/css/
Nombres sugeridos
modulo_index.css
modulo_create.css
modulo_edit.css
modulo_show.css
modulo_deleted.css
En Blade
@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_index.css') }}">
@stop
17. Reglas para scripts globales
Create AJAX

Si el create usa AJAX, incluir:

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
Edit AJAX

Si el edit usa AJAX, incluir:

<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
18. Cuándo usar JS propio en la vista

Agregar JS propio en la vista cuando el módulo necesite:

correlativos automáticos

filas dinámicas

autocompletar campos

cálculos por fila

selects dependientes

validaciones visuales previas al submit

Ejemplos reales

pedido_cliente.create

fabricacion.create

mp_ingresos.create

mp_ingresos.edit con cálculo de metros y código MP

19. Reglas para evitar repetición
Sí hacer

reutilizar form-ajax-submit.js

reutilizar form-edit-check.js

reutilizar navegación parcial

reutilizar x-header-card

reutilizar estructura general

No hacer

reescribir el mismo flujo AJAX en cada vista si ya está cubierto por scripts globales

20. Reglas para navegación parcial

Si el proyecto usa una navegación compartida, incluir el parcial donde corresponda.

Ejemplo
@include('partials.navigation')

o el parcial específico del módulo si existiera.

21. Reglas de consistencia entre vistas

Toda familia de vistas de un módulo debería mantener:

mismo título base

mismo tono visual

mismos botones

mismos nombres de variables

mismo criterio de CSS

mismo criterio de alertas

22. Estructura sugerida de carpetas
Patrón recomendado
resources/views/modulo/index.blade.php
resources/views/modulo/create.blade.php
resources/views/modulo/edit.blade.php
resources/views/modulo/show.blade.php
resources/views/modulo/deleted.blade.php
Ejemplo real
resources/views/estado_planificacion/index.blade.php
resources/views/estado_planificacion/create.blade.php
resources/views/estado_planificacion/edit.blade.php
resources/views/estado_planificacion/show.blade.php
resources/views/estado_planificacion/deleted.blade.php
23. Errores comunes a evitar
1. No incluir @csrf

Rompe submit en Laravel.

2. No incluir @method('PUT') en edit

Rompe update.

3. Usar nombres de input distintos al controlador

Provoca errores de validación o datos vacíos.

4. No cargar scripts globales

Se pierde el patrón AJAX.

5. Duplicar ids en tablas dinámicas

Rompe el JS por fila.

6. No alinear nombre de vista con ruta y controlador

Genera desorden.

7. Mezclar demasiada lógica PHP/JS dentro del Blade

Hace difícil el mantenimiento.

24. Checklist rápido por vista
Index

 DataTable funcional

 filtros correctos

 botones acciones

 resumen si aplica

 botón limpiar filtros

Create

 @csrf

 data-ajax="true" si corresponde

 redirect correcto

 inputs correctos

 JS correcto

Edit

 @csrf

 @method('PUT')

 data-edit-check="true"

 redirect correcto

 valores cargados

Show

 datos claros

 relaciones visibles

 botón volver

Deleted

 restore funcional

 confirmación visual

 vuelta al index

25. Relación con otros patrones

Este archivo debe leerse junto con:

PATRON_CRUD_SIMPLE_SOFTSRINK.md

PATRON_CRUD_MULTIFILA_SOFTSRINK.md

PATRON_CONTROLADORES_SOFTSRINK.md

PATRON_ALERTAS_Y_AJAX_SOFTSRINK.md

PATRON_DATATABLES_SOFTSRINK.md

CHECKLIST_NUEVO_MODULO.md

26. Conclusión

En SoftSrink_App_v2, una vista Blade bien armada debe cumplir estas reglas:

ser clara

ser consistente con el resto del sistema

integrarse bien con Laravel + AdminLTE

respetar el patrón AJAX oficial

ser fácil de copiar y adaptar a nuevos módulos

Por eso, cada vista nueva debería construirse tomando este patrón como base antes de considerarse terminada.