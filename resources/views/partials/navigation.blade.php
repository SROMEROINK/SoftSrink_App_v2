{{-- resources/views/partials/navigation.blade.php --}}

<!-- Botón Materia Prima con sub-botonera desplegable -->
<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Listado de Proveedores
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('marcas_insumos.index') }}'">Marcas Insumos</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('proveedores.index') }}'">Proveedores</button>
    </div>
</div>

<!-- Botón Materia Prima con sub-botonera desplegable -->
<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Materia Prima
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_materia_prima.index') }}'">Materias Base</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('materia_prima.diametro.index') }}'">Diámetro MP</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_ingresos.index') }}'">Ingreso de Materia Prima</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_egresos.index') }}'">Egreso de Materia Prima</button>
    </div>
</div>

<button type="button" class="btn btn-warning" onclick="window.location.href='{{ route('productos.index') }}'">Listado de Productos</button>
<button type="button" class="btn btn-success" onclick="window.location.href='{{ route('pedido_cliente.index') }}'">Pedido del Cliente</button>
<button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('fabricacion.index') }}'">Registro de Fabricación</button>
<button type="button" class="btn btn-info" onclick="window.location.href='{{ route('fechas_of.index') }}'">Fechas de Producción</button>
<button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('entregas_productos.index') }}'">Listado de Entregas</button>
<button type="button" class="btn btn-dark" id="clearFilters">Limpiar Filtros</button> <!-- Botón para limpiar filtros -->

<style>
    .dropdown-menu {
        background-color: #f8f9fa; /* Fondo claro */
    }

    .dropdown-item {
        color: #343a40; /* Color del texto */
    }

    .dropdown-item:hover {
        background-color: #007bff; /* Fondo azul al pasar el ratón */
        color: #fff; /* Texto blanco en hover */
    }
</style>
