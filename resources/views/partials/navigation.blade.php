{{-- resources/views/partials/navigation.blade.php --}}

<div class="btn-toolbar mb-2" role="toolbar" aria-label="Toolbar with button groups">
    <div class="btn-group mr-2" role="group" aria-label="First group">
        <button type="button" class="btn btn-warning" onclick="window.location.href='{{ route('productos.index') }}'">Listado de Productos</button>
        <button type="button" class="btn btn-success" onclick="window.location.href='{{ route('listado_of.index') }}'">Listado de OF</button>
        <button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('fabricacion.index') }}'">Registro de Fabricación</button>
        <button type="button" class="btn btn-info" onclick="window.location.href='{{ route('fechas_of.index') }}'">Fechas de Producción</button>
        <button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('entregas_productos.index') }}'">Listado de Entregas</button>
        <button type="button" class="btn btn-dark" id="clearFilters">Limpiar Filtros</button> <!-- Botón para limpiar filtros -->
    </div>
</div>
