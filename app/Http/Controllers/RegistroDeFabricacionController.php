<?php
//app\Http\Controllers\RegistroDeFabricacionController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\RegistroDeFabricacion;




class RegistroDeFabricacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index','showByNroOF');
        $this->middleware('permission:ver produccion')->only('show');
        $this->middleware('permission:editar produccion')->only(['create', 'store']);
        $this->middleware('permission:editar produccion')->only(['edit', 'update']);
        $this->middleware('permission:eliminar registros')->only('destroy');
    }



    public function getData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $registros_fabricacion = RegistroDeFabricacion::with([
                    'listado_of.producto.categoria', 
                    'creator', 
                    'updater'
                ])->select('Id_OF', 'Nro_OF', 'Id_Producto', 'Nro_Parcial', 'Cant_Piezas', 'Fecha_Fabricacion', 'Horario', 'Nombre_Operario', 'Turno', 'Cant_Horas_Extras', 'created_at', 'updated_at', 'created_by', 'updated_by');
    
                return DataTables::eloquent($registros_fabricacion)
                    ->addColumn('Prod_Codigo', function ($registro) {
                        return $registro->listado_of->producto->Prod_Codigo ?? '';
                    })
                    ->addColumn('Prod_Descripcion', function ($registro) {
                        return $registro->listado_of->producto->Prod_Descripcion ?? '';
                    })
                    ->addColumn('Nombre_Categoria', function ($registro) {
                        return $registro->listado_of->producto->categoria->Nombre_Categoria ?? '';
                    })
                    ->addColumn('Nro_Maquina', function ($registro) {
                        return $registro->listado_of->Nro_Maquina ?? '';
                    })
                    ->addColumn('Familia_Maquinas', function ($registro) {
                        return $registro->listado_of->Familia_Maquinas ?? '';
                    })
                    ->addColumn('creator', function ($registro) {
                        return $registro->creator->name ?? '';
                    })
                    ->addColumn('updater', function ($registro) {
                        return $registro->updater->name ?? '';
                    })
                    ->editColumn('created_at', function ($registro) {
                        return $registro->created_at ? $registro->created_at->format('Y-m-d H:i:s') : '';
                    })
                    ->editColumn('updated_at', function ($registro) {
                        return $registro->updated_at ? $registro->updated_at->format('Y-m-d H:i:s') : '';
                    })
                    ->filter(function ($query) use ($request) {
                        if ($request->has('filtro_categoria') && $request->filtro_categoria != '') {
                            $query->whereHas('listado_of.producto.categoria', function ($q) use ($request) {
                                $q->where('Nombre_Categoria', $request->filtro_categoria);
                            });
                        }
                        if ($request->has('filtro_maquina') && $request->filtro_maquina != '') {
                            $query->whereHas('listado_of', function ($q) use ($request) {
                                $q->where('Nro_Maquina', $request->filtro_maquina);
                            });
                        }
                        if ($request->has('filtro_familia') && $request->filtro_familia != '') {
                            $query->whereHas('listado_of', function ($q) use ($request) {
                                $q->where('Familia_Maquinas', $request->filtro_familia);
                            });
                        }
                        if ($request->has('filtro_nro_of') && $request->filtro_nro_of != '') {
                            $query->where('Nro_OF', $request->filtro_nro_of);
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
        return view('Fabricacion.index');
    }


    // public function indexWithFiltro(Request $request)
    // {
    //     $filtroNroOF = $request->query('filtroNroOF');
    //     $registros_fabricacion = RegistroDeFabricacion::with('listado_of.producto')->get();

    //     // Pasar los registros de fabricacón paginados a la vista correspondiente
    //     return view('Fabricacion.index', compact('registros_fabricacion', 'filtroNroOF'));
    // }
    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        return view('Fabricacion.create');
    }

    public function checkNroOFParcial(Request $request)
{
    $Nro_OF_Parcial = $request->input('Nro_OF_Parcial');
    $registroExistente = RegistroDeFabricacion::where('Nro_OF_Parcial', $Nro_OF_Parcial)->exists();

    return response()->json(['exists' => $registroExistente]);
}
    
    public function store(Request $request)
    {
        $messages = [
            'nro_of.*.required' => 'El número de OF es obligatorio.',
            'nro_parcial.*.required' => 'El número de parcial es obligatorio.',
            'Nro_OF_Parcial.*.unique' => 'El número de parcial ya ha sido registrado!.',
            'cant_piezas.*.required' => 'La cantidad de piezas es obligatoria.',
            'cant_piezas.*.numeric' => 'La cantidad de piezas debe ser un número.',
            'fecha_fabricacion.*.required' => 'La fecha de fabricación es obligatoria.',
            'fecha_fabricacion.*.date' => 'La fecha de fabricación no tiene un formato válido.',
            'horario.*.required' => 'El horario es obligatorio.',
            'operario.*.nullable' => 'El nombre del operario es obligatorio.',
            'turno.*.required' => 'El turno es obligatorio.',
            'cant_horas.*.required' => 'La cantidad de horas es obligatoria.',
            'cant_horas.*.numeric' => 'La cantidad de horas debe ser un número.'
        ];
    
        $validated = $request->validate([
            'nro_of.*' => 'required',
            'Id_Producto.*' => 'required',
            'nro_parcial.*' => 'required',
            'Nro_OF_Parcial.*' => 'required|unique:registro_de_fabricacion,Nro_OF_Parcial',
            'cant_piezas.*' => 'required|numeric',
            'fecha_fabricacion.*' => 'required|date',
            'horario.*' => 'required',
            'operario.*' => 'nullable|string|max:255',
            'turno.*' => 'required',
            'cant_horas.*' => 'required|numeric',
        ], $messages);
    
        $duplicatedRows = [];
        if (!empty($request->nro_of)) {
            foreach ($request->nro_of as $index => $nro_of) {
                if (isset($request->Id_Producto[$index], $request->nro_parcial[$index], $request->cant_piezas[$index], $request->fecha_fabricacion[$index], $request->horario[$index], $request->operario[$index], $request->turno[$index], $request->cant_horas[$index])) {
                    $Nro_OF_Parcial = $nro_of . '/' . $request->nro_parcial[$index];
    
                    $registroExistente = RegistroDeFabricacion::where('Nro_OF_Parcial', $Nro_OF_Parcial)->first();
                    if ($registroExistente) {
                        $duplicatedRows[] = $index + 1; // Sumar 1 para alinearlo con los números de fila en la tabla
                    } else {
                        $registro = new RegistroDeFabricacion();
                        $registro->Nro_OF = $nro_of;
                        $registro->Id_Producto = $request->Id_Producto[$index];
                        $registro->Nro_Parcial = $request->nro_parcial[$index];
                        $registro->Nro_OF_Parcial = $Nro_OF_Parcial;
                        $registro->Cant_Piezas = $request->cant_piezas[$index];
                        $registro->Fecha_Fabricacion = $request->fecha_fabricacion[$index];
                        $registro->Horario = $request->horario[$index];
                        $registro->Turno = $request->turno[$index];
                        $registro->Cant_Horas_Extras = $request->cant_horas[$index];
                        $registro->created_by = Auth::id();
                        $registro->updated_by = Auth::id();
    
                        if ($request->horario[$index] === 'H.Normales') {
                            $registro->Nombre_Operario = ''; 
                        } else {
                            $registro->Nombre_Operario = $request->operario[$index] ?? null;
                        }
    
                        $registro->save();
                    }
                }
            }
    
            if (!empty($duplicatedRows)) {
                return response()->json(['success' => false, 'message' => 'Algunas filas tienen errores de validación.', 'duplicatedRows' => $duplicatedRows], 400);
            }
    
            return response()->json(['success' => true, 'message' => 'Datos guardados correctamente!']);
        } else {
            return response()->json(['success' => false, 'message' => 'El número de OF es obligatorio.'], 400);
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function showByNroOF($nroOF)
    {
        $registros = RegistroDeFabricacion::where('Nro_OF', $nroOF)->orderBy('Nro_Parcial', 'asc')->get();
        $totalCantPiezas = $registros->sum('Cant_Piezas');

        // Agrega una verificación para asegurarte de que realmente se están recuperando datos.
        if ($registros->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron registros con ese Número de OF.');
        }

        return view('Fabricacion.show', compact('registros', 'totalCantPiezas'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) {
        $registro_fabricacion = RegistroDeFabricacion::findOrFail($id);
        return view('Fabricacion.edit', compact('registro_fabricacion'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $Id_OF)
    {
        $registro_fabricacion = RegistroDeFabricacion::find($Id_OF);
        if (!$registro_fabricacion) {
            return response()->json(['status' => 'error', 'message' => 'No se encontró el registro especificado.'], 404);
        }

        $registro_fabricacion->Nro_OF = $request->Nro_OF;
        $registro_fabricacion->Nro_Parcial = $request->Nro_Parcial;
        $registro_fabricacion->Nro_OF_Parcial = $request->Nro_OF_Parcial;
        $registro_fabricacion->Cant_Piezas = $request->Cant_Piezas;
        $registro_fabricacion->Fecha_Fabricacion = $request->Fecha_Fabricacion;
        $registro_fabricacion->Horario = $request->Horario;
        $registro_fabricacion->Nombre_Operario = $request->Nombre_Operario;
        $registro_fabricacion->Turno = $request->Turno;
        $registro_fabricacion->Cant_Horas_Extras = $request->Cant_Horas_Extras;
        $registro_fabricacion->updated_by = Auth::id(); // Registrar el usuario que actualiza el registro

        $registro_fabricacion->save();
        
        return response()->json(['status' => 'success', 'message' => 'Registro actualizado correctamente.']);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $Id_OF)
{
    try {
        $registro_fabricacion = RegistroDeFabricacion::findOrFail($Id_OF);
        $nroOF = $registro_fabricacion->Nro_OF; // Número de OF.
        $registro_fabricacion->delete();

        // Verifica si hay más registros con el mismo Nro_OF.
        $remaining = RegistroDeFabricacion::where('Nro_OF', $nroOF)->count();

        if ($remaining > 0) {
            // Si todavía hay registros, redirige a la vista show.
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado correctamente.', 'redirect' => route('fabricacion.showByNroOF', ['nroOF' => $nroOF])]);
        } else {
            // Si no hay más registros, redirige a la vista create.
            return response()->json(['status' => 'success', 'message' => 'Todos los registros eliminados. Creando nuevo.', 'redirect' => route('fabricacion.create')]);
        }
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro.']);
    }
}
}


