{{-- resources/views/productos/index.blade.php --}}

@extends('adminlte::page')

@section('title', 'Lista de Productos')

@section('content_header')
    <h1>Lista de Productos</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Tu contenido va aquí -->
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Descripción</th>
                            <th>Clasificacion_Piezas</th>
                            <th>Familia</th>
                            <th>Sub-Familia</th>
                            <th>Grupo-Sub-Familia</th>
                            <th>Código_Conjunto</th>
                            <th>Cliente</th>
                            <th>Nº Plano</th>
                            <th>Ult.Revisión-Plano</th>
                            <th>Material MP</th>
                            <th>Diámetro MP</th>
                            <th>Código MP</th>
                            <th>Longitud de Pieza</th>
                            <th>Frenteado-Torneado</th>
                            <th>Ancho Inserto Tronzado</th>
                            <th>Scrap_Maquina_S1</th>
                            <th>Scrap_Maquina_S2</th>
                            <th>Scrap_Maquina_S3</th>
                            <th>Scrap_Maquina_H1</th>
                            <th>Scrap_Maquina_H2</th>
                            <th>Scrap_Maquina_H3</th>
                            <th>Scrap_Maquina_T1</th>
                            <th>Sobrematerial_Promedio</th>
                            <th>Prod_Longitug_Total</th>
                            <th>Acciones</th> {{-- Añade una columna para las acciones --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($productos as $producto)
                            <tr>
                                <td>{{ $producto->Prod_Codigo }}</td>
                                <td>{{ $producto->Prod_Descripcion }}</td>
                                <td>{{ optional($producto->clasificacionPiezas)->Nombre_Clasificacion }}</td>
                                <td>{{ optional($producto->categoria)->Nombre_Categoria }}</td>
                                <td>{{ optional($producto->subFamilia)->Nombre_SubCategoria }}</td>
                                <td>{{ optional($producto->grupoSubFamilia)->Nombre_GrupoSubCategoria }}</td>
                                <td>{{ optional($producto->grupoConjuntos)->Nombre_GrupoConjuntos }}</td>
                                <td>{{ optional($producto->cliente)->Cli_Nombre }}</td>
                                <td>{{ $producto->Prod_N_Plano }}</td>
                                <td>{{ $producto->Prod_Plano_Ultima_Revisión }}</td>
                                <td>{{ $producto->Prod_Material_MP }}</td>
                                <td>{{ $producto->Prod_Diametro_de_MP }}</td>
                                <td>{{ $producto->Prod_Codigo_MP }}</td>
                                <td>{{ $producto->Prod_Longitud_de_Pieza }}</td>
                                <td>{{ $producto->Prod_Frenteado }}</td>
                                <td>{{ $producto->Prod_Ancho_De_Tronzado }}</td>
                                <td>{{ $producto->Scrap_Maquina_S1 }}</td>
                                <td>{{ $producto->Scrap_Maquina_S2 }}</td>
                                <td>{{ $producto->Scrap_Maquina_S3 }}</td>
                                <td>{{ $producto->Scrap_Maquina_H1 }}</td>
                                <td>{{ $producto->Scrap_Maquina_H2 }}</td>
                                <td>{{ $producto->Scrap_Maquina_H3 }}</td>
                                <td>{{ $producto->Scrap_Maquina_T1 }}</td>
                                <td>{{ $producto->Prod_Sobrematerial_Promedio }}</td>
                                <td>{{ $producto->Prod_Longitug_Total }}</td>
                                <td>
                                    {{-- Botón Editar --}}
                                    <a href="{{ route('productos.edit', $producto->Id_Producto) }}" class="btn btn-xs btn-default">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    {{-- Botón Eliminar --}}
                                    <form action="{{ route('productos.destroy', $producto->Id_Producto) }}" method="POST" style="display:
                                    <form action="{{ route('productos.destroy', $producto->Id_Producto) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este producto?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $productos->links() }} {{-- Paginación --}}
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Aquí puedes incluir archivos CSS adicionales --}}
@stop

@section('js')
    {{-- Aquí puedes incluir archivos JavaScript adicionales --}}
@stop

