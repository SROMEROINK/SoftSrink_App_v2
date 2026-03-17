@extends('adminlte::page')
{{-- resources\views\pedido_cliente\edit.blade.php --}}

@section('title', 'Editar Pedido del Cliente')

@section('content_header')
    <h1>Editar Pedido del Cliente</h1>
@stop

@section('content')
<form action="{{ route('pedido_cliente.update', $pedido->Id_OF) }}" method="POST" data-edit-check="true" data-exclude-fields="_token,_method">
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
                data-subcategoria="{{ $producto->Id_Prod_Sub_Familia }}"
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
<script src="{{ asset('js/form-edit-check.js') }}"></script>
<script>
const productos           = @json($productos);
const categoriaActual     = @json($pedido->producto->Id_Prod_Clase_Familia);
const subcategoriaActual  = @json($pedido->producto->Id_Prod_Sub_Familia);
const productoActual      = @json($pedido->Producto_Id);

// helpers que DEVUELVEN promesa
function fetchSubcategorias(categoriaId) {
  return $.get(`{{ route('productos.subcategorias') }}`, { categoria: categoriaId })
          .then(resp => Array.isArray(resp) ? resp : resp.data);
}
function fetchProductos(categoriaId, subcategoriaId) {
  return $.get('/productos/codigos', { categoria: categoriaId, subcategoria: subcategoriaId })
          .then(resp => resp.data);
}

function cargarSubcategorias(categoriaId, seleccionada = null) {
  return fetchSubcategorias(categoriaId).then(arr => {
    const $s = $('#subcategoria_id');
    $s.empty().append('<option value="">Seleccione</option>');
    arr.forEach(sub => $s.append(`<option value="${sub.id}">${sub.nombre}</option>`));
    if (seleccionada) $s.val(String(seleccionada));
  });
}

function cargarProductos(categoriaId, subcategoriaId, seleccionado = null) {
  return fetchProductos(categoriaId, subcategoriaId).then(arr => {
    const $p = $('#Producto_Id');
    $p.empty().append('<option value="">Seleccione</option>');
    arr.forEach(prod => $p.append(`<option value="${prod.id}">${prod.codigo}</option>`));
    if (seleccionado) $p.val(String(seleccionado));
    // actualizar descripción
    const found = productos.find(p => String(p.Id_Producto) === String($p.val()));
    $('#Prod_Descripcion').val(found ? found.Prod_Descripcion : '');
  });
}

$(function () {
  // INIT: setear categoría y encadenar cargas respetando el orden
  if (categoriaActual) {
    $('#categoria_id').val(String(categoriaActual));
    cargarSubcategorias(categoriaActual, subcategoriaActual)
      .then(() => {
        if (subcategoriaActual) {
          return cargarProductos(categoriaActual, subcategoriaActual, productoActual);
        }
      });
  }

  // cambio de categoría -> recargar subcats y limpiar productos/desc
  $('#categoria_id').on('change', function () {
    const cat = $(this).val();
    $('#Producto_Id').empty().append('<option value="">Seleccione</option>');
    $('#Prod_Descripcion').val('');
    if (cat) cargarSubcategorias(cat);
  });

  // cambio de subcategoría -> recargar productos
  $('#subcategoria_id').on('change', function () {
    const cat = $('#categoria_id').val();
    const sub = $(this).val();
    $('#Prod_Descripcion').val('');
    if (cat && sub) cargarProductos(cat, sub);
  });

  // cambio de producto -> actualizar descripción
  $('#Producto_Id').on('change', function () {
    const sel = productos.find(p => String(p.Id_Producto) === String($(this).val()));
    $('#Prod_Descripcion').val(sel ? sel.Prod_Descripcion : '');
  });

  // (tu lógica de submit con SweetAlert puede quedar igual)
});
</script>
@stop
