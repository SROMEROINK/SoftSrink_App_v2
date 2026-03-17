<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\Modelo_base_reutilizable as Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuloController extends Controller
{
    use CheckForChanges;

    public function index()
    {
        $totalRegistros = Modulo::count();

        return view('modulo_reutilizable.index', compact('totalRegistros'));
    }

    public function resumen()
    {
        return response()->json([
            'total'      => Modulo::withTrashed()->count(),
            'activos'    => Modulo::count(),
            'eliminados' => Modulo::onlyTrashed()->count(),
        ]);
    }

    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = Modulo::query();

            return response()->json([
                'campo_1' => $baseQuery->distinct()->pluck('Campo_1')->sort()->values(),
                'campo_2' => $baseQuery->distinct()->pluck('Campo_2')->sort()->values(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters Modulo: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar filtros únicos.'
            ], 500);
        }
    }

    public function getData(Request $request)
    {
        try {
            $query = Modulo::query()
                ->select([
                    'Id_Modulo',
                    'Campo_1',
                    'Campo_2',
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

            return datatables()->of($query)
                ->addColumn('Estado_Texto', fn($row) => (int) $row->reg_Status === 1 ? 'Activo' : 'Inactivo')
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en getData Modulo: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al recuperar los datos.'
            ], 500);
        }
    }

    public function create()
    {
        $ultimoRegistro = Modulo::orderBy('Id_Modulo', 'desc')->first();

        return view('modulo_reutilizable.create', compact('ultimoRegistro'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Campo_1' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('nombre_tabla', 'Campo_1')->whereNull('deleted_at'),
                ],
                'Campo_2' => 'required|string|max:255',
                'reg_Status' => 'nullable|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['created_by'] = Auth::id();
            $validatedData['reg_Status'] = $validatedData['reg_Status'] ?? 1;

            Modulo::create($validatedData);

            DB::commit();

            return response()->json([
                'success'  => true,
                'message'  => 'Registro creado correctamente.',
                'redirect' => route('modulo.index'),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear registro del módulo', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el registro: ' . $e->getMessage()
            ], 400);
        }
    }

    public function show($id)
    {
        $registro = Modulo::findOrFail($id);

        return view('modulo_reutilizable.show', compact('registro'));
    }

    public function edit($id)
    {
        $registro = Modulo::findOrFail($id);

        return view('modulo_reutilizable.edit', compact('registro'));
    }

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
            Log::error('Error al eliminar registro', [
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

    public function showDeleted()
    {
        $registrosEliminados = Modulo::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('modulo_reutilizable.deleted', compact('registrosEliminados'));
    }

    public function restore($id)
    {
        try {
            $registro = Modulo::withTrashed()->findOrFail($id);
            $registro->restore();

            return redirect()->route('modulo.index')
                ->with('success', 'Registro restaurado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al restaurar registro', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('modulo.index')
                ->with('error', 'No se pudo restaurar el registro.');
        }
    }
}