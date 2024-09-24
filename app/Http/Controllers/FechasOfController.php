<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use App\Models\FechasOF;

class FechasOFController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only('index', 'getData');
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update']);
        $this->middleware('permission:eliminar produccion')->only('destroy');
    }

    public function index()
    {
        return view('fechas_of.index');
    }

    public function getData(Request $request)
    {
        try {
            if ($request->ajax()) {
                $fechas_of = FechasOF::with(['listadoOf.producto.categoria'])
                    ->select(
                        'Nro_OF_fechas',
                        'Nro_Programa_H1',
                        'Nro_Programa_H2',
                        'Inicio_PAP',
                        'Hora_Inicio_PAP',
                        'Fin_PAP',
                        'Hora_Fin_PAP',
                        'Inicio_OF',
                        'Finalizacion_OF',
                        'Tiempo_Pieza'
                    )->orderBy('Nro_OF_fechas', 'desc');

                // Aplicar los filtros según los parámetros recibidos
                if ($request->has('filtro_nro_of_fechas') && !empty($request->filtro_nro_of_fechas)) {
                    $fechas_of->where('Nro_OF_fechas', 'like', '%' . $request->filtro_nro_of_fechas . '%');
                }
                if ($request->has('filtro_prod_codigo') && !empty($request->filtro_prod_codigo)) {
                    $fechas_of->whereHas('listadoOf.producto', function ($query) use ($request) {
                        $query->where('Prod_Codigo', 'like', '%' . $request->filtro_prod_codigo . '%');
                    });
                }
                if ($request->has('filtro_prod_descripcion') && !empty($request->filtro_prod_descripcion)) {
                    $fechas_of->whereHas('listadoOf.producto', function ($query) use ($request) {
                        $query->where('Prod_Descripcion', 'like', '%' . $request->filtro_prod_descripcion . '%');
                    });
                }
                if ($request->has('filtro_categoria') && !empty($request->filtro_categoria)) {
                    $fechas_of->whereHas('listadoOf.producto.categoria', function ($query) use ($request) {
                        $query->where('Nombre_Categoria', $request->filtro_categoria);
                    });
                }
                if ($request->has('filtro_maquina') && !empty($request->filtro_maquina)) {
                    $fechas_of->whereHas('listadoOf', function ($query) use ($request) {
                        $query->where('Nro_Maquina', $request->filtro_maquina);
                    });
                }
                if ($request->has('filtro_familia') && !empty($request->filtro_familia)) {
                    $fechas_of->whereHas('listadoOf', function ($query) use ($request) {
                        $query->where('Familia_Maquinas', $request->filtro_familia);
                    });
                }

                // Otros filtros por programas, tiempos, fechas, etc.
                if ($request->has('filtro_nro_programa_h1') && !empty($request->filtro_nro_programa_h1)) {
                    $fechas_of->where('Nro_Programa_H1', 'like', '%' . $request->filtro_nro_programa_h1 . '%');
                }
                if ($request->has('filtro_nro_programa_h2') && !empty($request->filtro_nro_programa_h2)) {
                    $fechas_of->where('Nro_Programa_H2', 'like', '%' . $request->filtro_nro_programa_h2 . '%');
                }
                if ($request->has('filtro_inicio_pap') && !empty($request->filtro_inicio_pap)) {
                    $fechas_of->where('Inicio_PAP', $request->filtro_inicio_pap);
                }
                if ($request->has('filtro_hora_inicio_pap') && !empty($request->filtro_hora_inicio_pap)) {
                    $fechas_of->where('Hora_Inicio_PAP', $request->filtro_hora_inicio_pap);
                }
                if ($request->has('filtro_fin_pap') && !empty($request->filtro_fin_pap)) {
                    $fechas_of->where('Fin_PAP', $request->filtro_fin_pap);
                }
                if ($request->has('filtro_hora_fin_pap') && !empty($request->filtro_hora_fin_pap)) {
                    $fechas_of->where('Hora_Fin_PAP', $request->filtro_hora_fin_pap);
                }
                if ($request->has('filtro_inicio_of') && !empty($request->filtro_inicio_of)) {
                    $fechas_of->where('Inicio_OF', $request->filtro_inicio_of);
                }
                if ($request->has('filtro_finalizacion_of') && !empty($request->filtro_finalizacion_of)) {
                    $fechas_of->where('Finalizacion_OF', $request->filtro_finalizacion_of);
                }
                if ($request->has('filtro_tiempo_pieza') && !empty($request->filtro_tiempo_pieza)) {
                    $fechas_of->where('Tiempo_Pieza', '>=', $request->filtro_tiempo_pieza);
                }

                // Devolver los datos procesados por DataTables
                return DataTables::eloquent($fechas_of)
                    ->addColumn('Prod_Codigo', function ($registro) {
                        return $registro->listadoOf->producto->Prod_Codigo ?? '';
                    })
                    ->addColumn('Prod_Descripcion', function ($registro) {
                        return $registro->listadoOf->producto->Prod_Descripcion ?? '';
                    })
                    ->addColumn('Nombre_Categoria', function ($registro) {
                        return $registro->listadoOf->producto->categoria->Nombre_Categoria ?? '';
                    })
                    ->addColumn('Nro_Maquina', function ($registro) {
                        return $registro->listadoOf->Nro_Maquina ?? '';
                    })
                    ->addColumn('Familia_Maquinas', function ($registro) {
                        return $registro->listadoOf->Familia_Maquinas ?? '';
                    })
                    ->make(true);
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener los datos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los datos'], 500);
        }
    }

    public function create()
    {
        return view('fechas_of.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Nro_OF_fechas' => 'required|integer',
            'Inicio_PAP' => 'required|date',
            'Hora_Inicio_PAP' => 'required',
            'Fin_PAP' => 'required|date',
            'Hora_Fin_PAP' => 'required',
            'Inicio_OF' => 'required|date',
            'Finalizacion_OF' => 'required|date',
            'Tiempo_Pieza' => 'required|numeric|min:0',
        ]);

        try {
            $fechasOF = new FechasOF($validatedData);
            $fechasOF->save();

            return redirect()->route('fechas_of.index')->with('success', 'Registro creado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al guardar los datos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un error al guardar el registro.');
        }
    }

    public function edit($id)
    {
        $fechasOF = FechasOF::findOrFail($id);
        return view('fechas_of.edit', compact('fechasOF'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'Nro_OF_fechas' => 'required|integer',
            'Inicio_PAP' => 'required|date',
            'Hora_Inicio_PAP' => 'required',
            'Fin_PAP' => 'required|date',
            'Hora_Fin_PAP' => 'required',
            'Inicio_OF' => 'required|date',
            'Finalizacion_OF' => 'required|date',
            'Tiempo_Pieza' => 'required|numeric|min:0',
        ]);

        try {
            $fechasOF = FechasOF::findOrFail($id);
            $fechasOF->update($validatedData);

            return redirect()->route('fechas_of.index')->with('success', 'Registro actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al actualizar los datos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Hubo un error al actualizar el registro.');
        }
    }

    public function destroy($id)
    {
        try {
            $fechasOF = FechasOF::findOrFail($id);
            $fechasOF->delete();

            return response()->json(['status' => 'success', 'message' => 'Registro eliminado correctamente.']);
        } catch (\Exception $e) {
            Log::error('Error al eliminar el registro: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro.']);
        }
    }
}
