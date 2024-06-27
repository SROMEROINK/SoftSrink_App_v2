<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index');
        $this->middleware('permission:ver produccion')->only('show');
        $this->middleware('permission:editar produccion')->only(['create', 'store']);
        $this->middleware('permission:editar produccion')->only(['edit', 'update']);
        $this->middleware('permission:editar produccion')->only('destroy');
    }


    public function getData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $productos = Producto::with([
                    'clasificacionPiezas',
                    'categoria',
                    'subFamilia',
                    'grupoSubFamilia',
                    'grupoConjuntos',
                    'cliente'
                ])->select(
                    'Id_Producto', 'Prod_Codigo', 'Prod_Descripcion', 
                    'Id_Prod_Clasificacion_Piezas', 'Id_Prod_Clase_Familia', 
                    'Id_Prod_Sub_Familia', 'Id_Prod_Grupos_de_Sub_Familia', 
                    'Id_Prod_Codigo_Conjuntos', 'Prod_CliId', 'Prod_N_Plano', 
                    'Prod_Plano_Ultima_Revisión', 'Prod_Material_MP', 'Prod_Diametro_de_MP', 
                    'Prod_Codigo_MP', 'Prod_Longitud_de_Pieza', 'Prod_Longitug_Total'
                );
    
                return DataTables::eloquent($productos)
                    ->addColumn('Nombre_Clasificacion', function ($producto) {
                        return $producto->clasificacionPiezas->Nombre_Clasificacion ?? '';
                    })
                    ->addColumn('Nombre_Categoria', function ($producto) {
                        return $producto->categoria->Nombre_Categoria ?? '';
                    })
                    ->addColumn('Nombre_SubCategoria', function ($producto) {
                        return $producto->subFamilia->Nombre_SubCategoria ?? '';
                    })
                    ->addColumn('Nombre_GrupoSubCategoria', function ($producto) {
                        return $producto->grupoSubFamilia->Nombre_GrupoSubCategoria ?? '';
                    })
                    ->addColumn('Nombre_GrupoConjuntos', function ($producto) {
                        return $producto->grupoConjuntos->Nombre_GrupoConjuntos ?? '';
                    })
                    ->addColumn('Cli_Nombre', function ($producto) {
                        return $producto->cliente->Cli_Nombre ?? '';
                    })
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filtro_clasificacion_piezas') && $request->filtro_clasificacion_piezas != '') {
                            $query->whereHas('clasificacionPiezas', function ($q) use ($request) {
                                $q->where('Nombre_Clasificacion', $request->filtro_clasificacion_piezas);
                            });
                        }
                        if ($request->has('filtro_familia') && $request->filtro_familia != '') {
                            $query->whereHas('categoria', function ($q) use ($request) {
                                $q->where('Nombre_Categoria', $request->filtro_familia);
                            });
                        }
                        if ($request->has('filtro_sub_familia') && $request->filtro_sub_familia != '') {
                            $query->whereHas('subFamilia', function ($q) use ($request) {
                                $q->where('Nombre_SubCategoria', $request->filtro_sub_familia);
                            });
                        }
                        if ($request->has('filtro_grupo_sub_familia') && $request->filtro_grupo_sub_familia != '') {
                            $query->whereHas('grupoSubFamilia', function ($q) use ($request) {
                                $q->where('Nombre_GrupoSubCategoria', $request->filtro_grupo_sub_familia);
                            });
                        }
                        if ($request->has('filtro_codigo_conjunto') && $request->filtro_codigo_conjunto != '') {
                            $query->whereHas('grupoConjuntos', function ($q) use ($request) {
                                $q->where('Nombre_GrupoConjuntos', $request->filtro_codigo_conjunto);
                            });
                        }
                        if ($request->has('filtro_cliente') && $request->filtro_cliente != '') {
                            $query->whereHas('cliente', function ($q) use ($request) {
                                $q->where('Cli_Nombre', $request->filtro_cliente);
                            });
                        }
                        if ($request->has('filtro_material_mp') && $request->filtro_material_mp != '') {
                            $query->where('Prod_Material_MP', $request->filtro_material_mp);
                        }
                        if ($request->has('filtro_diametro_mp') && $request->filtro_diametro_mp != '') {
                            $query->where('Prod_Diametro_de_MP', $request->filtro_diametro_mp);
                        }
                        if ($request->has('filtro_codigo_mp') && $request->filtro_codigo_mp != '') {
                            $query->where('Prod_Codigo_MP', $request->filtro_codigo_mp);
                        }
                        if ($request->has('filtro_id') && $request->filtro_id != '') {
                            $query->where('Id_Producto', 'like', "%{$request->filtro_id}%");
                        }
                        if ($request->has('filtro_codigo') && $request->filtro_codigo != '') {
                            $query->where('Prod_Codigo', 'like', "%{$request->filtro_codigo}%");
                        }
                        if ($request->has('filtro_descripcion') && $request->filtro_descripcion != '') {
                            $query->where('Prod_Descripcion', 'like', "%{$request->filtro_descripcion}%");
                        }
                        if ($request->has('filtro_plano') && $request->filtro_plano != '') {
                            $query->where('Prod_N_Plano', 'like', "%{$request->filtro_plano}%");
                        }
                        if ($request->has('filtro_revision_plano') && $request->filtro_revision_plano != '') {
                            $query->where('Prod_Plano_Ultima_Revisión', 'like', "%{$request->filtro_revision_plano}%");
                        }
                        if ($request->has('filtro_longitud_pieza') && $request->filtro_longitud_pieza != '') {
                            $query->where('Prod_Longitud_de_Pieza', 'like', "%{$request->filtro_longitud_pieza}%");
                        }
                        if ($request->has('filtro_longitud_total') && $request->filtro_longitud_total != '') {
                            $query->where('Prod_Longitug_Total', 'like', "%{$request->filtro_longitud_total}%");
                        }
                    })
                    ->make(true);
            }
        } catch (\Exception $e) {
            Log::error('Error in getData: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching data'], 500);
        }
    }
    
    public function index()
    {
        return view('productos.index');
    }
    
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Devolver la vista de creación
        return view('productos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            // Añade más validaciones según tus campos
        ]);

        // Crear un nuevo producto
        Producto::create($request->all());

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto creado con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        // Mostrar la vista de detalles del producto
        return view('productos.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        // Devolver la vista de edición y pasar el producto a esa vista
        return view('productos.edit', compact('producto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            // Añade más validaciones según tus campos
        ]);

        // Actualizar el producto
        $producto->update($request->all());

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        // Eliminar el producto
        $producto->delete();

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto eliminado con éxito');
    }
}
