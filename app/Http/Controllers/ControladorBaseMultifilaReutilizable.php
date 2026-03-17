<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\Modelo_base_reutilizable as Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ControladorBaseMultifilaReutilizable extends Controller
{
    use CheckForChanges;

    /**
     * Vista principal del módulo
     */
    public function index()
    {
        $totalRegistros = Modulo::count();

        return view('modulo_multifila_reutilizable.index', compact('totalRegistros'));
    }

    /**
     * Resumen para cards superiores
     */
    public function resumen()
    {
        return response()->json([
            'total'      => Modulo::withTrashed()->count(),
            'activos'    => Modulo::count(),
            'eliminados' => Modulo::onlyTrashed()->count(),
        ]);
    }

    /**
     * Filtros únicos para DataTables
     */
    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = Modulo::query();

            return response()->json([
                'campo_1' => $baseQuery->distinct()->pluck('Campo_1')->sort()->values(),
                'campo_2' => $baseQuery->distinct()->pluck('Campo_2')->sort()->values(),
                'campo_3' => $baseQuery->distinct()->pluck('Campo_3')->sort()->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters multifila: ' . $e->getMessage());

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
            $query = Modulo::query()
                ->select([
                    'Id_Modulo',
                    'Campo_1',
                    'Campo_2',
                    'Campo_3',
                    'reg_Status',
                    'created_at',
                    'updated_at',
                ])
                ->orderBy('Id_Modulo', 'desc');

            if ($request->filled('filtro_campo_1')) {
                $query->where('Campo_1', 'like', '%' . $request->filtro_campo_1 . '%');
            }

            if ($request->filled('filtro_campo_2')) {
                $query->where('Campo_2', 'like', '%' . $request->filtro_campo_2 . '%');
            }

            if ($request->filled('filtro_campo_3')) {
                $query->where('Campo_3', 'like', '%' . $request->filtro_campo_3 . '%');
            }

            return datatables()->of($query)
                ->addColumn('Estado_Texto', fn($row) => (int) $row->reg_Status === 1 ? 'Activo' : 'Inactivo')
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en getData multifila: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    /**
     * Formulario de alta multifila
     */
    public function create()
    {
        $ultimoRegistro = Modulo::orderBy('Id_Modulo', 'desc')->first();

        return view('modulo_multifila_reutilizable.create', compact('ultimoRegistro'));
    }

    /**
     * Guardado multifila
     *
     * IMPORTANTE:
     * Este método es plantilla base.
     * Debés adaptar:
     * - nombre de tabla en Rule::unique()
     * - campos reales del módulo
     * - lógica de duplicados si aplica por negocio
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Campo_1'   => 'required|array|min:1',
                'Campo_1.*' => [
                    'required',
                    'string',
                    'max:255',
                    'distinct',
                    Rule::unique('nombre_tabla', 'Campo_1')->whereNull('deleted_at'),
                ],

                'Campo_2'   => 'required|array|min:1',
                'Campo_2.*' => 'required|string|max:255',

                'Campo_3'   => 'nullable|array',
                'Campo_3.*' => 'nullable|string|max:255',

                'reg_Status' => 'nullable|in:0,1',
            ]);

            DB::beginTransaction();

            $duplicatedRows = [];

            foreach ($validatedData['Campo_1'] as $index => $campo1) {
                $registroExistente = Modulo::where('Campo_1', $campo1)->first();

                if ($registroExistente) {
                    $duplicatedRows[] = $index + 1;
                    continue;
                }

                Modulo::create([
                    'Campo_1'    => $validatedData['Campo_1'][$index],
                    'Campo_2'    => $validatedData['Campo_2'][$index] ?? null,
                    'Campo_3'    => $validatedData['Campo_3'][$index] ?? null,
                    'reg_Status' => $validatedData['reg_Status'] ?? 1,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }

            if (!empty($duplicatedRows)) {
                DB::rollBack();

                return response()->json([
                    'success'        => false,
                    'message'        => 'Algunas filas tienen errores de validación.',
                    'duplicatedRows' => $duplicatedRows
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Registros creados correctamente.',
                'redirect' => route('modulo.index'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear registros multifila', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear los registros: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mostrar un registro puntual
     */
    public function show($id)
    {
        $registro = Modulo::findOrFail($id);

        return view('modulo_multifila_reutilizable.show', compact('registro'));
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $registro = Modulo::findOrFail($id);

        return view('modulo_multifila_reutilizable.edit', compact('registro'));
    }

    /**
     * Actualización individual
     */
    public function update(Request $request, $id)
    {
        $registro = Modulo::findOrFail($id);

        $validatedData = $request->validate([
            'Campo_1' => [
                'required',
                'string',
                'max:255',
                Rule::unique('nombre_tabla', 'Campo_1')
                    ->ignore($id, 'Id_Modulo')
                    ->whereNull('deleted_at'),
            ],
            'Campo_2' => 'required|string|max:255',
            'Campo_3' => 'nullable|string|max:255',
            'reg_Status' => 'required|in:0,1',
        ]);

        return $this->updateIfChanged($registro, $validatedData, [
            'success_redirect'   => route('modulo.index'),
            'success_message'    => 'Registro actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by'     => true,
            'use_transaction'    => true,
            'normalize_data'     => false,
        ]);
    }

    /**
     * Eliminación lógica
     */
    public function destroy($id)
    {
        try {
            $registro = Modulo::findOrFail($id);

            $registro->deleted_by = Auth::id();
            $registro->save();
            $registro->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar registro multifila', [
                'id'      => $id,
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el registro.'
            ], 400);
        }
    }

    /**
     * Vista de eliminados
     */
    public function showDeleted()
    {
        $registrosEliminados = Modulo::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('modulo_multifila_reutilizable.deleted', compact('registrosEliminados'));
    }

    /**
     * Restaurar registro eliminado
     */
    public function restore($id)
    {
        try {
            $registro = Modulo::withTrashed()->findOrFail($id);
            $registro->restore();

            return redirect()->route('modulo.index')
                ->with('success', 'Registro restaurado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al restaurar registro multifila', [
                'id'    => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('modulo.index')
                ->with('error', 'No se pudo restaurar el registro.');
        }
    }
}