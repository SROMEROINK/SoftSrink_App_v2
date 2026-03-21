@extends('adminlte::page')

@section('title', 'Editar Producto')

@section('content_header')
    <h1>Editar Producto</h1>
@stop

@section('content')
    @include('partials.navigation')

    <div class="card mt-3">
        <div class="card-header">
            <h3 class="card-title">Editar producto #{{ $producto->Id_Producto }}</h3>
        </div>

        <form method="POST"
              action="{{ route('productos.update', $producto->Id_Producto) }}"
              data-edit-check="true"
              data-exclude-fields="_token,_method"
              data-redirect-url="{{ route('productos.index') }}"
              data-success-message="Producto actualizado correctamente.">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Prod_Codigo">Codigo</label>
                            <input type="text" name="Prod_Codigo" id="Prod_Codigo" class="form-control" value="{{ old('Prod_Codigo', $producto->Prod_Codigo) }}" maxlength="255" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Id_Prod_Tipo">Tipo</label>
                            <select name="Id_Prod_Tipo" id="Id_Prod_Tipo" class="form-control" required>
                                <option value="">Seleccionar</option>
                                @foreach($tipos as $tipo)
                                    <option value="{{ $tipo->Id_Tipo }}" {{ old('Id_Prod_Tipo', $producto->Id_Prod_Tipo) == $tipo->Id_Tipo ? 'selected' : '' }}>
                                        {{ $tipo->Nombre_Tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Prod_CliId">Cliente</label>
                            <select name="Prod_CliId" id="Prod_CliId" class="form-control" required>
                                <option value="">Seleccionar</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->Cli_Id }}" {{ old('Prod_CliId', $producto->Prod_CliId) == $cliente->Cli_Id ? 'selected' : '' }}>
                                        {{ $cliente->Cli_Nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="Prod_Descripcion">Descripcion</label>
                    <input type="text" name="Prod_Descripcion" id="Prod_Descripcion" class="form-control" value="{{ old('Prod_Descripcion', $producto->Prod_Descripcion) }}" maxlength="255" required>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Id_Prod_Categoria">Categoria</label>
                            <select name="Id_Prod_Categoria" id="Id_Prod_Categoria" class="form-control" required>
                                <option value="">Seleccionar</option>
                                @foreach($categorias as $categoria)
                                    <option value="{{ $categoria->Id_Categoria }}" {{ old('Id_Prod_Categoria', $producto->Id_Prod_Categoria) == $categoria->Id_Categoria ? 'selected' : '' }}>
                                        {{ $categoria->Nombre_Categoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Id_Prod_SubCategoria">Subcategoria</label>
                            <select name="Id_Prod_SubCategoria" id="Id_Prod_SubCategoria" class="form-control" required>
                                <option value="">Seleccionar</option>
                                @foreach($subcategorias as $subcategoria)
                                    <option value="{{ $subcategoria->Id_SubCategoria }}" {{ old('Id_Prod_SubCategoria', $producto->Id_Prod_SubCategoria) == $subcategoria->Id_SubCategoria ? 'selected' : '' }}>
                                        {{ $subcategoria->Nombre_SubCategoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Id_Prod_GrupoSubcategoria">Grupo Subcategoria</label>
                            <select name="Id_Prod_GrupoSubcategoria" id="Id_Prod_GrupoSubcategoria" class="form-control">
                                <option value="">Seleccionar</option>
                                @foreach($gruposSubcategoria as $grupo)
                                    <option value="{{ $grupo->Id_GrupoSubCategoria }}" {{ old('Id_Prod_GrupoSubcategoria', $producto->Id_Prod_GrupoSubcategoria) == $grupo->Id_GrupoSubCategoria ? 'selected' : '' }}>
                                        {{ $grupo->Nombre_GrupoSubCategoria }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Id_Prod_GrupoConjuntos">Grupo Conjuntos</label>
                            <select name="Id_Prod_GrupoConjuntos" id="Id_Prod_GrupoConjuntos" class="form-control">
                                <option value="">Seleccionar</option>
                                @foreach($gruposConjuntos as $grupoConjunto)
                                    <option value="{{ $grupoConjunto->Id_GrupoConjuntos }}" {{ old('Id_Prod_GrupoConjuntos', $producto->Id_Prod_GrupoConjuntos) == $grupoConjunto->Id_GrupoConjuntos ? 'selected' : '' }}>
                                        {{ $grupoConjunto->Nombre_GrupoConjuntos }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Prod_N_Plano">Nro Plano</label>
                            <input type="number" name="Prod_N_Plano" id="Prod_N_Plano" class="form-control" value="{{ old('Prod_N_Plano', $producto->Prod_N_Plano) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Prod_Plano_Ultima_Revision">Ultima Revision Plano</label>
                            <input type="text" name="Prod_Plano_Ultima_Revision" id="Prod_Plano_Ultima_Revision" class="form-control" value="{{ old('Prod_Plano_Ultima_Revision', $producto->Prod_Plano_Ultima_Revision) }}" maxlength="50" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Prod_Material_MP">Material MP</label>
                            <select name="Prod_Material_MP" id="Prod_Material_MP" class="form-control">
                                <option value="">Sin definir</option>
                                @foreach($materialesMp as $material)
                                    <option value="{{ $material->Prod_Material_MP }}" {{ old('Prod_Material_MP', $producto->Prod_Material_MP) == $material->Prod_Material_MP ? 'selected' : '' }}>
                                        {{ $material->Prod_Material_MP }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="Prod_Diametro_de_MP">Diametro MP</label>
                            <select name="Prod_Diametro_de_MP" id="Prod_Diametro_de_MP" class="form-control">
                                <option value="">Sin definir</option>
                                @foreach($diametrosMp as $diametro)
                                    <option value="{{ $diametro->Prod_Diametro_de_MP }}" {{ old('Prod_Diametro_de_MP', $producto->Prod_Diametro_de_MP) == $diametro->Prod_Diametro_de_MP ? 'selected' : '' }}>
                                        {{ $diametro->Prod_Diametro_de_MP }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Prod_Codigo_MP">Codigo MP</label>
                            <input type="text" name="Prod_Codigo_MP" id="Prod_Codigo_MP" class="form-control" value="{{ old('Prod_Codigo_MP', $producto->Prod_Codigo_MP) }}" maxlength="50" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="Prod_Longitud_de_Pieza">Longitud de Pieza</label>
                            <input type="number" step="0.01" name="Prod_Longitud_de_Pieza" id="Prod_Longitud_de_Pieza" class="form-control" value="{{ old('Prod_Longitud_de_Pieza', $producto->Prod_Longitud_de_Pieza) }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="reg_Status">Estado</label>
                            <select name="reg_Status" id="reg_Status" class="form-control" required>
                                <option value="1" {{ old('reg_Status', $producto->reg_Status) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('reg_Status', $producto->reg_Status) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('productos.index') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/cards.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/shared/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/modulo_reutilizable/modulo_create.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/productos/edit.css') }}">
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/swal-utils.js') }}"></script>
    <script src="{{ asset('js/form-edit-check.js') }}"></script>
    <script>
        $(document).ready(function () {
            const material = $('#Prod_Material_MP');
            const diametro = $('#Prod_Diametro_de_MP');
            const codigo = $('#Prod_Codigo_MP');
            const diametroActual = @json(old('Prod_Diametro_de_MP', $producto->Prod_Diametro_de_MP));

            function actualizarCodigo() {
                const materialValue = material.val();
                const diametroValue = diametro.val();

                codigo.val(materialValue && diametroValue ? `${materialValue}_${diametroValue}` : '');
            }

            function cargarDiametros(diametroSeleccionado = '') {
                diametro.empty().append('<option value="">Sin definir</option>');

                if (!material.val()) {
                    actualizarCodigo();
                    return;
                }

                $.get("{{ route('productos.dependentFilters') }}", {
                    material_mp: material.val()
                }, function (data) {
                    data.diametrosMP.forEach(function (item) {
                        diametro.append(`<option value="${item.Prod_Diametro_de_MP}">${item.Prod_Diametro_de_MP}</option>`);
                    });

                    if (diametroSeleccionado && diametro.find(`option[value="${diametroSeleccionado}"]`).length) {
                        diametro.val(diametroSeleccionado);
                    }

                    actualizarCodigo();
                });
            }

            material.on('change', function () {
                cargarDiametros();
            });

            diametro.on('change', function () {
                actualizarCodigo();
            });

            cargarDiametros(diametroActual);
        });
    </script>
@stop
