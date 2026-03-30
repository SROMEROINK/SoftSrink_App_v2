{{-- resources/views/partials/navigation.blade.php --}}

<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Listado de Proveedores
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('marcas_insumos.index') }}'">Marcas Insumos</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('proveedores.index') }}'">Proveedores</button>
    </div>
</div>

<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Materia Prima
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_materia_prima.index') }}'">Materias Base</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('materia_prima.diametro.index') }}'">Di&aacute;metro MP</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_ingresos.index') }}'">Ingreso de Materia Prima</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_salidas_iniciales.index') }}'">Salidas Iniciales MP</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_egresos.index') }}'">Egreso de Materia Prima</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_stock.index') }}'">Stock MP</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('pedido_cliente_mp.index') }}'">Definicion MP Pedidos</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('mp_movimientos_adicionales.index') }}'">Movimientos Adicionales MP</button>
    </div>
</div>

<div class="btn-group">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Configuraci&oacute;n
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('estado_planificacion.index') }}'">
            Estados de Planificaci&oacute;n
        </button>
    </div>
</div>

<div class="btn-group">
    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Listado de Productos
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('productos.index') }}'">Productos</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('producto_tipo.index') }}'">Tipos de Producto</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('producto_categoria.index') }}'">Categorias</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('producto_subcategoria.index') }}'">Subcategorias</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('producto_grupo_subcategoria.index') }}'">Grupo Subcategoria</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('producto_grupo_conjuntos.index') }}'">Grupo Conjuntos</button>
    </div>
</div>
<div class="btn-group">
    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Pedido del Cliente
    </button>
    <div class="dropdown-menu">
        <button class="dropdown-item" onclick="window.location.href='{{ route('pedido_cliente.index') }}'">Pedido del Cliente</button>
        <button class="dropdown-item" onclick="window.location.href='{{ route('listado_of.index') }}'">Listado OF</button>
    </div>
</div>
<button type="button" class="btn btn-primary" onclick="window.location.href='{{ route('fabricacion.index') }}'">Registro de Fabricaci&oacute;n</button>
<button type="button" class="btn btn-info" onclick="window.location.href='{{ route('fechas_of.index') }}'">Fechas de Producci&oacute;n</button>
<button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('entregas_productos.index') }}'">Listado de Entregas</button>
<button type="button" class="btn btn-dark" id="clearFilters">Limpiar Filtros</button>

<style>
    .dropdown-menu {
        background-color: #f8f9fa;
    }

    .dropdown-item {
        color: #343a40;
    }

    .dropdown-item:hover {
        background-color: #007bff;
        color: #fff;
    }
</style>



