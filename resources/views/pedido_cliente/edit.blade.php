@extends('adminlte::page')

@section('title', 'Editar Pedido del Cliente')

@section('content_header')
    <h1>Editar Pedido del Cliente</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
@stop

@section('content')
<form action="{{ route('pedido_cliente.update', $pedido->Id_OF) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method">
    @csrf
    @method('PUT')

    <datalist id="pedido-productos-catalogo-list">
        @foreach ($productos as $producto)
            <option value="{{ $producto->Prod_Codigo }}">{{ $producto->Prod_Codigo }}</option>
        @endforeach
    </datalist>

    <div class="form-group">
        <label for="Nro_OF">Nro de OF</label>
        <input type="number" id="Nro_OF" name="Nro_OF" class="form-control" value="{{ $pedido->Nro_OF }}" required>
    </div>

    <div class="form-group">
        <label for="busqueda_codigo_producto">Busqueda Rapida por Codigo</label>
        <input type="text"
               id="busqueda_codigo_producto"
               class="form-control filtro-select"
               list="pedido-productos-catalogo-list"
               value="{{ $pedido->producto->Prod_Codigo ?? '' }}"
               placeholder="Buscar codigo">
    </div>

    <div class="form-group">
        <label for="Producto_Id">Producto</label>
        <select id="Producto_Id" name="Producto_Id" class="form-control filtro-select" required>
            <option value="">Seleccione</option>
            @foreach ($productos as $producto)
                <option value="{{ $producto->Id_Producto }}"
                        data-categoria="{{ $producto->Id_Prod_Categoria }}"
                        data-subcategoria="{{ $producto->Id_Prod_SubCategoria }}"
                        data-codigo="{{ $producto->Prod_Codigo }}"
                        data-descripcion="{{ $producto->Prod_Descripcion }}"
                        {{ $pedido->Producto_Id == $producto->Id_Producto ? 'selected' : '' }}>
                    {{ $producto->Prod_Codigo }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="categoria_nombre">Categoria</label>
        <input type="text" id="categoria_nombre" class="form-control" value="{{ $pedido->producto->categoria->Nombre_Categoria ?? '' }}" readonly>
    </div>

    <div class="form-group">
        <label for="subcategoria_nombre">Subcategoria</label>
        <input type="text" id="subcategoria_nombre" class="form-control" value="{{ $pedido->producto->subCategoria->Nombre_SubCategoria ?? '' }}" readonly>
    </div>

    <div class="form-group">
        <label for="Prod_Descripcion">Descripcion del Producto</label>
        <input type="text" id="Prod_Descripcion" class="form-control" value="{{ $pedido->producto->Prod_Descripcion ?? '' }}" readonly>
    </div>

    <div class="form-group">
        <label for="Fecha_del_Pedido">Fecha del Pedido</label>
        <input type="date" id="Fecha_del_Pedido" name="Fecha_del_Pedido" class="form-control" value="{{ optional($pedido->Fecha_del_Pedido)->format('Y-m-d') }}" required>
    </div>

    <div class="form-group">
        <label for="Cant_Fabricacion">Cantidad a Fabricar</label>
        <input type="number" id="Cant_Fabricacion" name="Cant_Fabricacion" class="form-control" value="{{ $pedido->Cant_Fabricacion }}" required>
    </div>

    <div class="form-group">
        <label for="Estado_Plani_Id">Estado del Pedido</label>
        <select name="Estado_Plani_Id" id="Estado_Plani_Id" class="form-control filtro-select" required>
            <option value="">Seleccione</option>
            @foreach ($estadosPlanificacion as $estado)
                <option value="{{ $estado->Estado_Plani_Id }}" {{ (int) $pedido->Estado_Plani_Id === (int) $estado->Estado_Plani_Id ? 'selected' : '' }}>
                    {{ $estado->Nombre_Estado }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="reg_Status">Estado del Registro</label>
        <select name="reg_Status" id="reg_Status" class="form-control filtro-select">
            <option value="1" {{ $pedido->reg_Status == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ $pedido->reg_Status == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="{{ route('pedido_cliente.index') }}" class="btn btn-default">Cancelar</a>
</form>
@stop

@section('js')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
$(function () {
    const productos = @json($productos);
    const categorias = @json($categorias);
    const subcategorias = @json($subcategorias);

    function actualizarCamposProducto(productoId, enfocarFecha = false) {
        const producto = productos.find((item) => String(item.Id_Producto) === String(productoId));

        if (!producto) {
            return false;
        }

        const categoria = categorias.find((item) => String(item.Id_Categoria) === String(producto.Id_Prod_Categoria));
        const subcategoria = subcategorias.find((item) => String(item.Id_SubCategoria) === String(producto.Id_Prod_SubCategoria));

        $('#Producto_Id').val(String(producto.Id_Producto));
        $('#busqueda_codigo_producto').val(producto.Prod_Codigo);
        $('#categoria_nombre').val(categoria ? categoria.Nombre_Categoria : '');
        $('#subcategoria_nombre').val(subcategoria ? subcategoria.Nombre_SubCategoria : '');
        $('#Prod_Descripcion').val(producto.Prod_Descripcion || '');

        if (enfocarFecha) {
            setTimeout(() => {
                const $fecha = $('#Fecha_del_Pedido');
                $fecha.trigger('focus');
            }, 80);
        }

        return true;
    }

    function seleccionarProductoPorCodigo(enfocarFecha = false) {
        const codigoIngresado = ($('#busqueda_codigo_producto').val() || '').trim().toLowerCase();
        const producto = productos.find((item) => item.Prod_Codigo.toLowerCase() === codigoIngresado);

        if (producto) {
            return actualizarCamposProducto(producto.Id_Producto, enfocarFecha);
        }

        return false;
    }

    $('#Producto_Id').on('change', function () {
        actualizarCamposProducto($(this).val(), false);
    });

    $('#busqueda_codigo_producto').on('change', function () {
        seleccionarProductoPorCodigo(true);
    });

    $('#busqueda_codigo_producto').on('blur', function () {
        seleccionarProductoPorCodigo(false);
    });

    $('#busqueda_codigo_producto').on('keydown', function (event) {
        if (event.key === 'Enter') {
            const encontrado = seleccionarProductoPorCodigo(true);

            if (encontrado) {
                event.preventDefault();
                $(this).trigger('blur');
            }
        }
    });

    function enfocarCantidadFabricacionEdit() {
        setTimeout(() => {
            $('#Cant_Fabricacion').trigger('focus').select();
        }, 40);
    }

    $('#Fecha_del_Pedido').on('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            enfocarCantidadFabricacionEdit();
        }
    });
});
</script>
@stop
