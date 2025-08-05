@extends('adminlte::page')
{{-- resources\views\pedido_cliente\edit.blade.php --}}

@section('title', 'Editar Pedido del Cliente')

@section('content_header')
    <h1>Editar Pedido del Cliente</h1>
@stop

@section('content')
<form action="{{ route('pedido_cliente.update', $pedido->Id_OF) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label for="Nro_OF">N° de OF:</label>
        <input type="number" id="Nro_OF" name="Nro_OF" class="form-control" value="{{ $pedido->Nro_OF }}" required>
    </div>

    <div class="form-group">
    <label for="categoria_id">Categoría:</label>
    <select id="categoria_id" class="form-control filtro-select" required>
        <option value="">Seleccione</option>
        @foreach ($categorias as $categoria)
            <option value="{{ $categoria->Id_Categoria }}" {{ $categoria->Id_Categoria == $pedido->producto->Id_Prod_Clase_Familia ? 'selected' : '' }}>
                {{ $categoria->Nombre_Categoria }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="subcategoria_id">Subcategoría:</label>
    <select id="subcategoria_id" class="form-control filtro-select" required>
        <option value="">Seleccione</option>
        @foreach ($subcategorias as $sub)
            <option value="{{ $sub->Id_SubCategoria }}" {{ $sub->Id_SubCategoria == $pedido->producto->Id_Prod_Sub_Familia ? 'selected' : '' }}>
                {{ $sub->Nombre_SubCategoria }}
            </option>
        @endforeach
    </select>
</div>


    <div class="form-group">
    <label for="Producto_Id">Producto:</label>
    <select id="Producto_Id" name="Producto_Id" class="form-control" required>
        <option value="">Seleccione</option>
        @foreach ($productos as $producto)
            <option value="{{ $producto->Id_Producto }}" 
                data-categoria="{{ $producto->Id_Prod_Clase_Familia }}"
                data-subcategoria="{{ $producto->Id_SubCategoria }}"
                {{ $pedido->Producto_Id == $producto->Id_Producto ? 'selected' : '' }}>
                {{ $producto->Prod_Codigo }}
            </option>
        @endforeach
    </select>
</div>


    <div class="form-group">
        <label for="Prod_Descripcion">Descripción del Producto:</label>
        <input type="text" id="Prod_Descripcion" class="form-control" value="{{ $pedido->producto->Prod_Descripcion ?? '' }}" readonly>
    </div>

    <div class="form-group">
        <label for="Fecha_del_Pedido">Fecha del Pedido:</label>
        <input type="date" id="Fecha_del_Pedido" name="Fecha_del_Pedido" class="form-control" value="{{ $pedido->Fecha_del_Pedido }}" required>
    </div>

    <div class="form-group">
        <label for="Cant_Fabricacion">Cantidad a Fabricar:</label>
        <input type="number" id="Cant_Fabricacion" name="Cant_Fabricacion" class="form-control" value="{{ $pedido->Cant_Fabricacion }}" required>
    </div>

    <div class="form-group">
        <label for="reg_Status">Estado:</label>
        <select name="reg_Status" id="reg_Status" class="form-control">
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


<script>
    const productos = @json($productos);
    const categoriaActual = "{{ $pedido->producto->Id_Prod_Clase_Familia }}";
    const subcategoriaActual = "{{ $pedido->producto->Id_Prod_Sub_Familia }}";
</script>

<script>

// Función para mostrar/ocultar productos según categoría y subcategoría
function filtrarProductos(reset = false) {
    const categoriaId = $('#categoria_id').val();
    const subcategoriaId = $('#subcategoria_id').val();

    $('#Producto_Id option').each(function () {
        const prodCat = $(this).data('categoria');
        const prodSub = $(this).data('subcategoria');

        if (!categoriaId || !subcategoriaId) {
            $(this).show();
            return;
        }

        if (prodCat == categoriaId && prodSub == subcategoriaId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });

    if (reset) {
        $('#Producto_Id').val('').trigger('change');
    }
}


function cargarSubcategorias(categoriaId, subcategoriaSeleccionada = null) {
    $.ajax({
        url: `{{ route('productos.subcategorias') }}`,
        method: 'GET',
        data: { categoria: categoriaId },
        success: function(data) {
            const subcategoriaSelect = $('#subcategoria_id');
            subcategoriaSelect.empty();
            subcategoriaSelect.append('<option value="">Seleccione</option>');

            data.forEach(sub => {
                subcategoriaSelect.append(
                    `<option value="${sub.id}" ${sub.id == subcategoriaSeleccionada ? 'selected' : ''}>${sub.nombre}</option>`
                );
            });

            if (subcategoriaSeleccionada) {
    subcategoriaSelect.val(subcategoriaSeleccionada).trigger('change');
}

        }
    });
}


$(document).ready(function() {
    // Cargar subcategorías en base a categoría actual al iniciar
    if (categoriaActual) {
        $('#categoria_id').val(categoriaActual);
        cargarSubcategorias(categoriaActual, subcategoriaActual);
    }

    // Cargar productos si ya tenemos subcategoría
    if (categoriaActual && subcategoriaActual) {
        cargarProductos(categoriaActual, subcategoriaActual, "{{ $pedido->Producto_Id }}");
    }

    // Al cambiar categoría → cargar subcategorías
    $('#categoria_id').on('change', function () {
        const categoriaId = $(this).val();
        $('#Producto_Id').empty().append('<option value="">Seleccione</option>'); // Reiniciar productos
        cargarSubcategorias(categoriaId); // ← solo cargamos, sin selección
    });

    // Al cambiar subcategoría → cargar productos
    $('#subcategoria_id').on('change', function () {
        const categoriaId = $('#categoria_id').val();
        const subcategoriaId = $(this).val();
        cargarProductos(categoriaId, subcategoriaId);
    });

    // Al cambiar producto → actualizar descripción
    $('#Producto_Id').on('change', function () {
        const selectedId = parseInt($(this).val());
        const producto = productos.find(p => p.Id_Producto === selectedId);
        $('#Prod_Descripcion').val(producto ? producto.Prod_Descripcion : '');
    });
});


<script>


    
    function cargarProductos(categoriaId, subcategoriaId, productoSeleccionado = null) {
        $.ajax({
            url: '/productos/codigos',
            method: 'GET',
            data: {
                categoria: categoriaId,
                subcategoria: subcategoriaId
            },
        success: function(response) {
            const productoSelect = $('#Producto_Id');
            productoSelect.empty();
            productoSelect.append('<option value="">Seleccione</option>');
            
            response.data.forEach(producto => {
                productoSelect.append(
                    `<option value="${producto.id}" ${productoSeleccionado == producto.id ? 'selected' : ''}>
                        ${producto.codigo}
                        </option>`
                    );
                });
                
            // Actualiza descripción si ya hay un producto seleccionado
            if (productoSeleccionado) {
                $('#Producto_Id').trigger('change');
            }
        }
    });
}

</script>


<script>
    $(document).ready(function() {
        // Inicializar SweetAlert2
        Swal.fire({
            icon: 'info',
            title: 'Editar Pedido del Cliente',
            text: 'Asegúrese de que todos los campos estén correctamente llenos antes de enviar el formulario.',
            showConfirmButton: true,
            timer: 5000,
            timerProgressBar: true
        });                                                                                                                                                                                                                              


        // Lógica para detectar cambios
        const originalValues = {
            Nro_OF: $('#Nro_OF').val(),
            Producto_Id: $('#Producto_Id').val(),
            Fecha_del_Pedido: $('#Fecha_del_Pedido').val(),
            Cant_Fabricacion: $('#Cant_Fabricacion').val(),
            reg_Status: $('#reg_Status').val(),
        };

        $('form').on('submit', function(e) {
            e.preventDefault();

            let hasChanges = false;
            for (const key in originalValues) {
                const original = String(originalValues[key]).trim();
                const actual = String($('#' + key).val()).trim();

                if (original !== actual) {
                    hasChanges = true;
                    break;
                }
            }

            if (!hasChanges) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin cambios',
                    text: 'No se detectaron cambios en el formulario.',
                    showConfirmButton: true,
                });
                return;
            }

            // Enviar AJAX si hay cambios
            var formData = $(this).serialize();
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pedido actualizado correctamente',
                        showConfirmButton: false,
                        timer: 1500
                    });
                    setTimeout(function() {
                        window.location.href = "{{ route('pedido_cliente.index') }}";
                    }, 1500);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al actualizar el pedido',
                        showConfirmButton: true
                    });
                }
            });
        });
    });
</script>
@stop
