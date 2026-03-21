@extends('adminlte::page')

@section('title', 'Crear Nuevo Pedido del Cliente')

@section('content_header')
    <h1>Crear Nuevo Pedido del Cliente</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/filters.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_create.css') }}">
@stop

@section('content')
<form method="POST"
      action="{{ route('pedido_cliente.store') }}"
      data-ajax="true"
      data-redirect-url="{{ route('pedido_cliente.index') }}">
    @csrf

    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <strong>Ultimo Nro OF registrado:</strong>
            <span id="ultimo-nro-of-label">-</span>
        </div>
        <div>
            <strong>Proximo Nro OF sugerido:</strong>
            <span id="proximo-nro-of-label">-</span>
        </div>
    </div>

    <div class="modo-toolbar">
        <button type="button" class="btn btn-secondary" id="modo-familia-btn">Ingreso por familia</button>
        <button type="button" class="btn btn-primary active" id="modo-rapido-btn">Ingreso rapido por codigo</button>
    </div>

    <datalist id="productos-catalogo-list">
        @foreach ($productosCatalogo as $producto)
            <option value="{{ $producto->Prod_Codigo }}">{{ $producto->Prod_Codigo }}</option>
        @endforeach
    </datalist>

    <table class="table table-bordered custom-font centered-form modo-rapido" id="tablaListadoOF">
        <thead>
            <tr>
                <th class="col-nro-fila">Nro Fila</th>
                <th class="col-nro-of">Nro OF</th>
                <th class="col-busqueda-rapida">Busqueda Rapida</th>
                <th class="col-categoria-producto">Categoria</th>
                <th class="col-subcategoria-producto">Subcategoria</th>
                <th class="col-codigo-producto">Codigo Producto</th>
                <th class="col-id-producto">ID Producto</th>
                <th class="col-descripcion">Descripcion</th>
                <th class="col-fecha-del-pedido">Fecha del Pedido</th>
                <th class="col-cant-fabricacion">Cant. Fabricacion</th>
                <th class="col-estado-pedido">Estado del Pedido</th>
                <th class="col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="btn-der">
        <button type="button" class="btn btn-success" id="agregarFila">Agregar Fila</button>
        <input type="submit" class="btn btn-primary" value="Guardar Cambios">
    </div>
</form>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/swal-utils.js') }}"></script>
<script src="{{ asset('js/form-ajax-submit.js') }}"></script>
<script>
$(document).ready(function () {
    let filaCounter = 1;
    let ultimoNroOF = 0;
    let modoActual = 'familia';
    const categorias = @json($categorias);
    const subcategorias = @json($subcategorias);
    const productosCatalogo = @json($productosCatalogo);
    const estadosPlanificacion = @json($estadosPlanificacion);

    const opcionesEstadoPedido = [
        '<option value="">Seleccione Estado</option>',
        ...estadosPlanificacion.map((estado) => {
            const selected = String(estado.Estado_Plani_Id) === '9' ? 'selected' : '';
            return `<option value="${estado.Estado_Plani_Id}" ${selected}>${estado.Nombre_Estado}</option>`;
        })
    ].join('');

    const opcionesCategoria = [
        '<option value="">Seleccione Categoria</option>',
        ...categorias.map((categoria) => `<option value="${categoria.id}">${categoria.nombre}</option>`)
    ].join('');

    $.ajax({
        url: '/pedido_cliente/ultimo-nro-of',
        type: 'GET',
        success: function (response) {
            if (response.success) {
                ultimoNroOF = response.ultimo_nro_of ? parseInt(response.ultimo_nro_of, 10) : 0;
                $('#ultimo-nro-of-label').text(ultimoNroOF || '-');
                $('#proximo-nro-of-label').text(ultimoNroOF + 1);
                inicializarPrimeraFila(ultimoNroOF + 1);
            }
        }
    });

    function inicializarPrimeraFila(valorOF) {
        const $newRow = $(generarFila(filaCounter, valorOF));
        $('#tablaListadoOF tbody').append($newRow);
        actualizarEstadoFila($newRow);
        filaCounter++;
    }

    function generarFila(numeroFila, nroOF) {
        return `<tr>
            <td>${numeroFila}</td>
            <td><input type="number" name="nro_of[]" class="form-control" value="${nroOF || ''}" required></td>
            <td class="col-busqueda-rapida">
                <input type="text" class="form-control input-busqueda-rapida" list="productos-catalogo-list" placeholder="Buscar codigo">
            </td>
            <td>
                <select class="form-control filtro-select select-categoria-producto" name="categoria_producto[]">
                    ${opcionesCategoria}
                </select>
            </td>
            <td>
                <select class="form-control filtro-select select-subcategoria-producto" name="subcategoria_producto[]">
                    <option value="">Seleccione Subcategoria</option>
                </select>
            </td>
            <td>
                <select class="form-control filtro-select select-codigo-producto" name="codigo_producto[]">
                    <option value="">Seleccione Codigo</option>
                </select>
                <input type="hidden" name="producto_id[]">
            </td>
            <td><input type="text" name="producto_id_display[]" class="form-control" readonly></td>
            <td><input type="text" name="descripcion_producto[]" class="form-control" readonly></td>
            <td><input type="date" name="fecha_del_pedido[]" class="form-control" value="{{ date('Y-m-d') }}" required></td>
            <td><input type="number" name="cant_fabricacion[]" class="form-control" min="1" step="1" required></td>
            <td>
                <select name="estado_plani_id[]" class="form-control filtro-select" required>
                    ${opcionesEstadoPedido}
                </select>
            </td>
            <td><button type="button" class="btn btn-danger eliminarFila">Eliminar</button></td>
        </tr>`;
    }

    function aplicarModo(modo) {
        modoActual = modo;
        $('#tablaListadoOF')
            .removeClass('modo-familia modo-rapido')
            .addClass(modo === 'rapido' ? 'modo-rapido' : 'modo-familia');

        $('#modo-familia-btn').toggleClass('active btn-primary', modo === 'familia').toggleClass('btn-secondary', modo !== 'familia');
        $('#modo-rapido-btn').toggleClass('active btn-primary', modo === 'rapido').toggleClass('btn-secondary', modo !== 'rapido');
    }

    function getSubcategoriasPorCategoria(categoriaId) {
        return subcategorias.filter((subcategoria) => String(subcategoria.Id_Categoria) === String(categoriaId));
    }

    function getProductosFiltrados(categoriaId, subcategoriaId) {
        return productosCatalogo.filter((producto) => {
            const matchCategoria = !categoriaId || String(producto.Id_Prod_Categoria) === String(categoriaId);
            const matchSubcategoria = !subcategoriaId || String(producto.Id_Prod_SubCategoria) === String(subcategoriaId);

            return matchCategoria && matchSubcategoria;
        });
    }

    function cargarSubcategoriasEnFila($fila, categoriaId, selectedSubcategoria = '') {
        const $subcategoriaSelect = $fila.find('.select-subcategoria-producto');
        const opciones = [
            '<option value="">Seleccione Subcategoria</option>',
            ...getSubcategoriasPorCategoria(categoriaId).map((subcategoria) => `<option value="${subcategoria.id}">${subcategoria.nombre}</option>`)
        ].join('');

        $subcategoriaSelect.html(opciones);
        if (selectedSubcategoria) {
            $subcategoriaSelect.val(String(selectedSubcategoria));
        }
    }

    function cargarCodigosEnFila($fila, categoriaId, subcategoriaId, selectedProductoId = '') {
        const $codigoProducto = $fila.find('.select-codigo-producto');
        const opciones = [
            '<option value="">Seleccione Codigo</option>',
            ...getProductosFiltrados(categoriaId, subcategoriaId).map((producto) => `<option value="${producto.Id_Producto}">${producto.Prod_Codigo}</option>`)
        ].join('');

        $codigoProducto.html(opciones);
        if (selectedProductoId) {
            $codigoProducto.val(String(selectedProductoId));
        }
    }

    function completarFilaConProducto($fila, producto) {
        if (!producto) {
            return;
        }

        $fila.find('.input-busqueda-rapida').val(producto.Prod_Codigo);
        $fila.find('.select-categoria-producto').val(String(producto.Id_Prod_Categoria));
        cargarSubcategoriasEnFila($fila, producto.Id_Prod_Categoria, producto.Id_Prod_SubCategoria);
        cargarCodigosEnFila($fila, producto.Id_Prod_Categoria, producto.Id_Prod_SubCategoria, producto.Id_Producto);
        $fila.find('input[name="producto_id[]"]').val(producto.Id_Producto);
        $fila.find('input[name="producto_id_display[]"]').val(producto.Id_Producto);
        $fila.find('input[name="descripcion_producto[]"]').val(producto.Prod_Descripcion);
        actualizarEstadoFila($fila);
    }

    function limpiarProductoEnFila($fila) {
        $fila.find('.select-categoria-producto').val('');
        $fila.find('.select-subcategoria-producto').html('<option value="">Seleccione Subcategoria</option>');
        $fila.find('.select-codigo-producto').html('<option value="">Seleccione Codigo</option>');
        $fila.find('input[name="producto_id[]"]').val('');
        $fila.find('input[name="producto_id_display[]"]').val('');
        $fila.find('input[name="descripcion_producto[]"]').val('');
    }

    function seleccionarProductoPorCodigo($fila, enfocarFecha = false) {
        const codigoIngresado = ($fila.find('.input-busqueda-rapida').val() || '').trim().toLowerCase();
        const producto = productosCatalogo.find((item) => item.Prod_Codigo.toLowerCase() === codigoIngresado);

        if (!codigoIngresado) {
            limpiarProductoEnFila($fila);
            actualizarEstadoFila($fila);
            return false;
        }

        if (producto) {
            completarFilaConProducto($fila, producto);

            if (enfocarFecha) {
                setTimeout(() => {
                    const $fecha = $fila.find('input[name="fecha_del_pedido[]"]');
                    $fecha.trigger('focus');
                }, 80);
            }

            return true;
        }

        limpiarProductoEnFila($fila);
        actualizarEstadoFila($fila);

        return false;
    }

    function filaCompleta($fila) {
        const categoria = $fila.find('.select-categoria-producto').val();
        const subcategoria = $fila.find('.select-subcategoria-producto').val();
        const codigo = $fila.find('.select-codigo-producto').val();
        const productoId = $fila.find('input[name="producto_id[]"]').val();
        const fecha = $fila.find('input[name="fecha_del_pedido[]"]').val();
        const cantidad = $fila.find('input[name="cant_fabricacion[]"]').val();
        const estado = $fila.find('select[name="estado_plani_id[]"]').val();

        return Boolean(categoria && subcategoria && codigo && productoId && fecha && cantidad && estado);
    }

    function actualizarEstadoFila($fila) {
        const codigoRapido = ($fila.find('.input-busqueda-rapida').val() || '').trim();
        const productoId = $fila.find('input[name="producto_id[]"]').val();
        const codigoInvalido = modoActual === 'rapido' && codigoRapido !== '' && !productoId;

        $fila.removeClass('row-complete row-incomplete row-invalid');
        $fila.find('.input-busqueda-rapida').removeClass('input-invalid');

        if (codigoInvalido) {
            $fila.addClass('row-invalid');
            $fila.find('.input-busqueda-rapida').addClass('input-invalid');
            return;
        }

        $fila.addClass(filaCompleta($fila) ? 'row-complete' : 'row-incomplete');
    }

    function actualizarEstadosFilas() {
        $('#tablaListadoOF tbody tr').each(function () {
            actualizarEstadoFila($(this));
        });
    }

    $(document).on('change', '.select-categoria-producto', function () {
        const $fila = $(this).closest('tr');
        const categoriaId = $(this).val();
        const $subcategoriaSelect = $fila.find('.select-subcategoria-producto');
        const $codigoProducto = $fila.find('.select-codigo-producto');

        $subcategoriaSelect.empty().append('<option value="">Seleccione Subcategoria</option>');
        $codigoProducto.empty().append('<option value="">Seleccione Codigo</option>');
        $fila.find('input[name="producto_id[]"]').val('');
        $fila.find('input[name="producto_id_display[]"]').val('');
        $fila.find('input[name="descripcion_producto[]"]').val('');
        if (modoActual === 'familia') {
            $fila.find('.input-busqueda-rapida').val('');
        }
        actualizarEstadoFila($fila);

        if (categoriaId) {
            cargarSubcategoriasEnFila($fila, categoriaId);
            actualizarEstadoFila($fila);
        }
    });

    $(document).on('change', '.select-categoria-producto, .select-subcategoria-producto', function () {
        const $fila = $(this).closest('tr');
        const categoriaId = $fila.find('.select-categoria-producto').val();
        const subcategoriaId = $fila.find('.select-subcategoria-producto').val();

        if (categoriaId && subcategoriaId) {
            cargarCodigosEnFila($fila, categoriaId, subcategoriaId);
            actualizarEstadoFila($fila);
        }
    });

    $(document).on('change', '.select-codigo-producto', function () {
        const $fila = $(this).closest('tr');
        const codigoProductoId = $(this).val();
        const producto = productosCatalogo.find((item) => String(item.Id_Producto) === String(codigoProductoId));

        if (producto) {
            completarFilaConProducto($fila, producto);
        }
    });

    $(document).on('change', '.input-busqueda-rapida', function () {
        const $fila = $(this).closest('tr');
        seleccionarProductoPorCodigo($fila, true);
    });

    $(document).on('blur', '.input-busqueda-rapida', function () {
        const $fila = $(this).closest('tr');
        seleccionarProductoPorCodigo($fila, false);
    });

    $(document).on('keydown', '.input-busqueda-rapida', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const $fila = $(this).closest('tr');
            const encontrado = seleccionarProductoPorCodigo($fila, true);

            if (encontrado) {
                $(this).trigger('blur');
                return;
            }

            $(this).trigger('focus').select();
            SwalUtils.error('El codigo ingresado no existe en la base de productos. Debes seleccionar un codigo valido.');
        }
    });

    function enfocarCantidadFabricacion($fila) {
        setTimeout(() => {
            $fila.find('input[name="cant_fabricacion[]"]').trigger('focus').select();
        }, 40);
    }

    $(document).on('keydown', 'input[name="fecha_del_pedido[]"]', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            enfocarCantidadFabricacion($(this).closest('tr'));
        }
    });

    $('#agregarFila').click(function () {
        const ultimoValorOF = parseFloat($('#tablaListadoOF tbody tr:last').find('input[name="nro_of[]"]').val()) || ultimoNroOF;
        const $newRow = $(generarFila(filaCounter, ultimoValorOF + 1));
        $('#tablaListadoOF tbody').append($newRow);
        actualizarEstadoFila($newRow);
        $('#proximo-nro-of-label').text(ultimoValorOF + 2);
        filaCounter++;
    });

    $(document).on('click', '.eliminarFila', function () {
        $(this).closest('tr').remove();
    });

    $(document).on('input change', '#tablaListadoOF input, #tablaListadoOF select', function () {
        if ($(this).attr('name') === 'cant_fabricacion[]') {
            const valorActual = $(this).val();

            if (valorActual !== '' && parseInt(valorActual, 10) < 1) {
                $(this).val(1);
            }
        }

        actualizarEstadoFila($(this).closest('tr'));
    });

    $(document).on('focusin', '#tablaListadoOF input, #tablaListadoOF select, #tablaListadoOF button', function () {
        $('#tablaListadoOF tbody tr').removeClass('row-active');
        const $fila = $(this).closest('tr');
        $fila.addClass('row-active');
        actualizarEstadoFila($fila);
    });

    $(document).on('click', '#tablaListadoOF tbody tr', function () {
        $('#tablaListadoOF tbody tr').removeClass('row-active');
        $(this).addClass('row-active');
    });

    $('form[data-ajax="true"]').on('submit', function (event) {
        let hayFilasInvalidas = false;

        $('#tablaListadoOF tbody tr').each(function () {
            const $fila = $(this);
            actualizarEstadoFila($fila);

            if ($fila.hasClass('row-invalid')) {
                hayFilasInvalidas = true;
            }
        });

        if (hayFilasInvalidas) {
            event.preventDefault();
            SwalUtils.error('Hay filas con codigos de producto que no existen en la base de datos. Selecciona un codigo valido antes de guardar.');
            const $primeraFilaInvalida = $('#tablaListadoOF tbody tr.row-invalid').first();

            if ($primeraFilaInvalida.length) {
                $primeraFilaInvalida.find('.input-busqueda-rapida').trigger('focus').select();
            }
        }
    });

    $('#modo-familia-btn').on('click', function () {
        aplicarModo('familia');
    });

    $('#modo-rapido-btn').on('click', function () {
        aplicarModo('rapido');
    });

    aplicarModo('rapido');
    actualizarEstadosFilas();
});
</script>
@stop
