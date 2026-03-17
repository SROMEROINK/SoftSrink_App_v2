<?php
// app/Http/Controllers/EstadoPlanificacionController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\EstadoPlanificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EstadoPlanificacionController extends Controller
{
    use CheckForChanges;

    /**
     * Vista principal
     */
    public function index()
    {
        $totalEstados = EstadoPlanificacion::count();

        return view('estado_planificacion.index', compact('totalEstados'));
    }

    /**
     * Resumen para tarjetas superiores
     */
    public function resumen()
    {
        return response()->json([
            'total'      => EstadoPlanificacion::withTrashed()->count(),
            'activos'    => EstadoPlanificacion::where('Status', 1)->count(),
            'eliminados' => EstadoPlanificacion::onlyTrashed()->count(),
        ]);
    }

    /**
     * Filtros únicos para DataTables
     */
    public function getUniqueFilters(Request $request)
{
    try {
        return response()->json([
            'status' => ['Activo', 'Inactivo']
        ]);
    } catch (\Exception $e) {
        Log::error('Error en getUniqueFilters EstadoPlanificacion: ' . $e->getMessage());

        return response()->json([
            'error' => 'Error al recuperar filtros únicos.'
        ], 500);
    }
}

    /**
     * Datos para DataTables
     */
    public function getData(Request $request)
{
    try {
        $query = EstadoPlanificacion::select([
            'Estado_Plani_Id',
            'Nombre_Estado',
            'Status',
            'created_at',
            'updated_at',
        ])->orderBy('Estado_Plani_Id', 'asc');

        if ($request->filled('filtro_id')) {
            $query->where('Estado_Plani_Id', 'like', '%' . $request->filtro_id . '%');
        }

        if ($request->filled('filtro_nombre')) {
            $query->where('Nombre_Estado', 'like', '%' . $request->filtro_nombre . '%');
        }

        if ($request->filled('filtro_status')) {
            $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
            $query->where('Status', $status);
        }

    return datatables()->of($query)
        ->addColumn('Estado_Texto', function ($estado) {
            return (int) $estado->Status === 1 ? 'Activo' : 'Inactivo';
        })
        ->editColumn('created_at', function ($estado) {
            return $estado->created_at
                ? $estado->created_at->format('d/m/Y H:i')
                : '-';
        })
        ->editColumn('updated_at', function ($estado) {
            return $estado->updated_at
                ? $estado->updated_at->format('d/m/Y H:i')
                : '-';
        })
        ->addColumn('acciones', function ($estado) {
            return '';
        })
        ->rawColumns(['acciones'])
        ->make(true);

    } catch (\Exception $e) {
        Log::error('Error en getData EstadoPlanificacion: ' . $e->getMessage());

        return response()->json([
            'error' => 'Error al recuperar los datos.'
        ], 500);
    }
}

    /**
     * Formulario de alta
     */
    public function create()
    {
        $ultimoEstado = EstadoPlanificacion::orderBy('Estado_Plani_Id', 'desc')->first();

        return view('estado_planificacion.create', compact('ultimoEstado'));
    }

    /**
     * Guardar nuevo estado
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_Estado' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('estado_planificacion', 'Nombre_Estado')->whereNull('deleted_at'),
                ],
                'Status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_Estado'] = trim($validatedData['Nombre_Estado']);
            $validatedData['Status'] = (int) $validatedData['Status'];
            $validatedData['created_by'] = Auth::id();

            EstadoPlanificacion::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado de planificación creado correctamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear estado_planificacion', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el estado de planificación: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mostrar detalle
     */
    public function show(string $id)
    {
        $estado_planificacion = EstadoPlanificacion::findOrFail($id);

        return view('estado_planificacion.show', compact('estado_planificacion'));
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id)
    {
        $estado = EstadoPlanificacion::findOrFail($id);

        return view('estado_planificacion.edit', compact('estado'));
    }

    /**
     * Actualizar estado
     */
    public function update(Request $request, string $id)
    {
        $estado = EstadoPlanificacion::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_Estado' => [
                'required',
                'string',
                'max:50',
                Rule::unique('estado_planificacion', 'Nombre_Estado')
                    ->ignore($id, 'Estado_Plani_Id')
                    ->whereNull('deleted_at'),
            ],
            'Status' => 'required|in:0,1',
        ]);

        $validatedData['Nombre_Estado'] = trim($validatedData['Nombre_Estado']);
        $validatedData['Status'] = (int) $validatedData['Status'];

        return $this->updateIfChanged($estado, $validatedData, [
            'success_redirect'  => route('estado_planificacion.index'),
            'success_message'   => 'Estado de planificación actualizado correctamente.',
            'no_changes_message'=> 'No se realizaron cambios.',
            'set_updated_by'    => true,
            'use_transaction'   => true,
            'normalize_data'    => false,
        ]);
    }

    /**
     * Eliminación lógica
     */
   public function destroy(string $id)
{
    try {
        DB::beginTransaction();

        $estado = EstadoPlanificacion::findOrFail($id);
        $estado->deleted_by = Auth::id();
        $estado->save();
        $estado->delete();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Estado de planificación eliminado correctamente.'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();

        Log::error('Error al eliminar estado_planificacion', [
            'error'   => $e->getMessage(),
            'usuario' => Auth::id(),
            'id'      => $id,
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar el estado de planificación.'
        ], 400);
    }
}
    /**
     * Vista de eliminados
     */
    public function showDeleted()
    {
        $estadosEliminados = EstadoPlanificacion::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('estado_planificacion.deleted', compact('estadosEliminados'));
    }

    /**
     * Restaurar registro eliminado
     */
    public function restore(string $id)
    {
        try {
            $estado = EstadoPlanificacion::withTrashed()->findOrFail($id);
            $estado->restore();

            return response()->json([
                'success' => true,
                'message' => 'Estado de planificación restaurado con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar estado_planificacion', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el estado.'
            ], 400);
        }
    }
}