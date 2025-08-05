@extends('adminlte::page')

{{-- resources\views\pedido_cliente\create.blade.php --}}

@section('title', 'Crear Nueva Orden de Fabricación')

@section('content_header')
    <h1>Crear Nueva Orden de Fabricación</h1>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/pedido_cliente_create.css') }}">
@stop

@section('content')
<form method="post" action="{{ route('pedido_cliente.store') }}">
    @csrf
    <table class="table table-bordered custom-font centered-form" id="tablaListadoOF">
        <thead>
            <tr>
                <th class="col-nro-fila">N° Fila</th>
                <th class="col-nro-of">N° OF</th>
                <th class="col-categoria-producto">Categoría de Producto</th>
                <th class="col-subcategoria-producto">Subcategoría de Producto</th>
                <th class="col-codigo-producto">Código de Producto</th>
                <th class="col-id-producto">ID del Producto</th>
                <th class="col-descripcion">Descripción</th> <!-- Nueva columna de Descripción -->
                <th class="col-fecha-del-pedido">Fecha del Pedido</th>
                <th class="col-cant-fabricacion">Cantidad de Fabricación</th>
                <th class="col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Aquí se irán agregando filas dinámicamente -->
        </tbody>
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
<script>
$(document).ready(function() {
    var filaCounter = 1;
    var ultimoNroOF = 0; // Variable para almacenar el último número de OF

    // Obtener el último número de OF al cargar la vista
    $.ajax({
    url: '/pedido_cliente/ultimo-nro-of',
    type: 'GET',
    success: function(response) {
        console.log("Respuesta AJAX:", response); // Verificar la respuesta
        if (response.success) {
            // Actualizar la variable con el último número de OF real
            ultimoNroOF = response.ultimo_nro_of ? parseInt(response.ultimo_nro_of) : 0;
            inicializarPrimeraFila(ultimoNroOF + 1); // Inicializar la primera fila con el siguiente valor de OF
        } else {
            console.error('Error al obtener el último número de OF.');
        }
    },
    error: function(jqXHR, textStatus, errorThrown) {
        console.error('Error en la llamada AJAX para obtener el último número de OF:', textStatus, errorThrown);
    }
});

    // Función para inicializar la primera fila con el número de OF inicial
    function inicializarPrimeraFila(valorOF) {
        var $newRow = $(generarFila(filaCounter, valorOF)); // Crear la primera fila con el valor OF inicial
        $('#tablaListadoOF tbody').append($newRow); // Agregar la fila inicial
        cargarCategorias($newRow.find('.select-categoria-producto')); // Cargar categorías solo en la nueva fila
        filaCounter++;
    }

    // Función para generar una nueva fila de la tabla
    function generarFila(filaCounter, nroOF) {
        nroOF = nroOF || ''; // Si no se pasa un valor para Nro_OF, dejarlo vacío
        return `<tr>
                    <td>${filaCounter}</td>
                    <td><input type="number" name="nro_of[]" class="form-control" value="${nroOF}" required></td>
                    <td>
                        <select class="form-control select-categoria-producto" name="categoria_producto[]">
                            <option value="">Seleccione Categoría</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control select-subcategoria-producto" name="subcategoria_producto[]">
                            <option value="">Seleccione Subcategoría</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-control select-codigo-producto" name="codigo_producto[]">
                            <option value="">Seleccione Código</option>
                        </select>
                        <input type="hidden" name="producto_id[]">
                    </td>
                    <td><input type="text" name="producto_id_display[]" class="form-control" readonly></td>
                    <td><input type="text" name="descripcion_producto[]" class="form-control" readonly></td>
                    <td><input type="date" name="fecha_del_pedido[]" class="form-control" required></td>
                    <td><input type="number" name="cant_fabricacion[]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger eliminarFila">Eliminar</button></td>
                </tr>`;
    }


    // Cargar categorías solo en la fila especificada
    function cargarCategorias($select) {
        $.ajax({
            url: '/productos/categorias',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $select.empty().append('<option value="">Seleccione Categoría</option>');
                    $.each(response.data, function(index, categoria) {
                        $select.append('<option value="' + categoria.id + '">' + categoria.nombre + '</option>');
                    });
                } else {
                    console.error('Error al cargar las categorías.');
                }
            }
        });
    }

    // Cargar subcategorías según la categoría seleccionada
    $(document).on('change', '.select-categoria-producto', function() {
        var $fila = $(this).closest('tr');
        var categoriaId = $(this).val();

        if (categoriaId) {
            $.ajax({
                url: '{{ route("productos.subcategorias") }}',
                type: 'GET',
                data: { categoria: categoriaId },
                success: function(response) {
                    if (response.success) {
                        var $subcategoriaSelect = $fila.find('.select-subcategoria-producto');
                        $subcategoriaSelect.empty().append('<option value="">Seleccione Subcategoría</option>');
                        $.each(response.data, function(index, subcategoria) {
                            $subcategoriaSelect.append('<option value="' + subcategoria.id + '">' + subcategoria.nombre + '</option>');
                        });
                    }
                }
            });
        }
    });

    // Cargar códigos de producto cuando se seleccionan categoría y subcategoría
    $(document).on('change', '.select-categoria-producto, .select-subcategoria-producto', function() {
        var $fila = $(this).closest('tr');
        var categoriaId = $fila.find('.select-categoria-producto').val();
        var subcategoriaId = $fila.find('.select-subcategoria-producto').val();

        if (categoriaId && subcategoriaId) {
            $.ajax({
                url: '/productos/codigos',
                type: 'GET',
                data: {
                    categoria: categoriaId,
                    subcategoria: subcategoriaId
                },
                success: function(response) {
                    if (response.success) {
                        var $codigoProducto = $fila.find('.select-codigo-producto');
                        $codigoProducto.empty().append('<option value="">Seleccione Código</option>');
                        $.each(response.data, function(index, producto) {
                            $codigoProducto.append('<option value="' + producto.id + '">' + producto.codigo + '</option>');
                        });
                    }
                }
            });
        }
    });

   // Al seleccionar un código de producto, actualizar el ID y la descripción del producto
   $(document).on('change', '.select-codigo-producto', function () {
        var $fila = $(this).closest('tr');
        var codigoProductoId = $(this).val();
        
        $fila.find('input[name="producto_id[]"]').val(codigoProductoId);
        $fila.find('input[name="producto_id_display[]"]').val(codigoProductoId);

        // Obtener la descripción del producto
        $.ajax({
            url: `/productos/${codigoProductoId}/descripcion`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $fila.find('input[name="descripcion_producto[]"]').val(response.descripcion);
                }
            },
            error: function() {
                console.error('Error al obtener la descripción del producto.');
            }
        });
    });

    // Al agregar una nueva fila, incrementar correctamente el valor de OF
    $('#agregarFila').click(function() {
        var ultimoValorOF = parseFloat($('#tablaListadoOF tbody tr:last').find('input[name="nro_of[]"]').val()) || ultimoNroOF;
        var $newRow = $(generarFila(filaCounter, ultimoValorOF + 1)); // Crear nueva fila con el siguiente Nro_OF
        $('#tablaListadoOF tbody').append($newRow);
        cargarCategorias($newRow.find('.select-categoria-producto'));
        filaCounter++;
    });

    // Eliminar fila
    $(document).on('click', '.eliminarFila', function() {
        $(this).closest('tr').remove();
    });


    $('form').submit(function(event) {
        event.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    title: response.success ? 'Éxito' : 'Error',
                    text: response.message,
                    icon: response.success ? 'success' : 'error',
                    confirmButtonText: 'OK'
                }).then(function() {
                    if (response.success) {
                        location.reload();
                    }
                });
            },
            error: function(xhr) {
                var response = JSON.parse(xhr.responseText);
                Swal.fire('Error', response.message, 'error');
            }
        });
    });
});



</script>
@stop
