<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CheckForChanges;
use App\Models\ProductoTipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProductoTipoController extends Controller
{
    use CheckForChanges;

    /**
     * Vista principal
     */
    public function index()
    {
        $totalTipos = ProductoTipo::count();

        return view('productos.tipos.index', compact('totalTipos'));
    }

    /**
     * Resumen para tarjetas superiores
     */
    public function resumen()
    {
        return response()->json([
            'total'      => ProductoTipo::withTrashed()->count(),
            'activos'    => ProductoTipo::where('reg_Status', 1)->count(),
            'eliminados' => ProductoTipo::onlyTrashed()->count(),
        ]);
    }

    /**
     * Filtros únicos para DataTables
     */
    public function getUniqueFilters(Request $request)
    {
        try {
            $baseQuery = ProductoTipo::query();

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $baseQuery->where('reg_Status', $status);
            }

            $nombres = $baseQuery->select('Nombre_Tipo')
                ->distinct()
                ->orderBy('Nombre_Tipo')
                ->pluck('Nombre_Tipo')
                ->values();

            $status = collect(['Activo', 'Inactivo']);

            return response()->json([
                'nombres' => $nombres,
                'status'  => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getUniqueFilters ProductoTipo: ' . $e->getMessage());

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
            $query = ProductoTipo::select([
                'Id_Tipo',
                'Nombre_Tipo',
                'reg_Status',
                'created_at',
                'updated_at',
            ])->orderBy('Id_Tipo', 'asc');

            if ($request->filled('filtro_id')) {
                $query->where('Id_Tipo', $request->filtro_id);
            }

            if ($request->filled('filtro_nombre')) {
                $query->whereRaw('LOWER(Nombre_Tipo) LIKE ?', ['%' . strtolower($request->filtro_nombre) . '%']);
            }

            if ($request->filled('filtro_status')) {
                $status = strtolower($request->filtro_status) === 'activo' ? 1 : 0;
                $query->where('reg_Status', $status);
            }

            return datatables()->of($query)
                ->addColumn('Status_Texto', function ($tipo) {
                    return (int) $tipo->reg_Status === 1 ? 'Activo' : 'Inactivo';
                })
                ->addColumn('acciones', function ($tipo) {
                    return '';
                })
                ->rawColumns(['acciones'])
                ->make(true);

        } catch (\Exception $e) {
            Log::error('Error en getData ProductoTipo: ' . $e->getMessage());

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
        $ultimoTipo = ProductoTipo::orderBy('Id_Tipo', 'desc')->first();

        return view('productos.tipos.create', compact('ultimoTipo'));
    }

    /**
     * Guardar nuevo tipo
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'Nombre_Tipo' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('producto_tipo', 'Nombre_Tipo')->whereNull('deleted_at'),
                ],
                'reg_Status' => 'required|in:0,1',
            ]);

            DB::beginTransaction();

            $validatedData['Nombre_Tipo'] = trim($validatedData['Nombre_Tipo']);
            $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];
            $validatedData['created_by'] = Auth::id();

            ProductoTipo::create($validatedData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de producto creado correctamente.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al crear producto_tipo', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el tipo de producto: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mostrar detalle
     */
    public function show(string $id)
    {
        $producto_tipo = ProductoTipo::findOrFail($id);

        return view('productos.tipos.show', compact('producto_tipo'));
    }

    /**
     * Formulario de edición
     */
    public function edit(string $id)
    {
        $tipo = ProductoTipo::findOrFail($id);

        return view('productos.tipos.edit', compact('tipo'));
    }

    /**
     * Actualizar tipo
     */
    public function update(Request $request, string $id)
    {
        $tipo = ProductoTipo::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_Tipo' => [
                'required',
                'string',
                'max:100',
                Rule::unique('producto_tipo', 'Nombre_Tipo')
                    ->ignore($id, 'Id_Tipo')
                    ->whereNull('deleted_at'),
            ],
            'reg_Status' => 'required|in:0,1',
        ]);

        $validatedData['Nombre_Tipo'] = trim($validatedData['Nombre_Tipo']);
        $validatedData['reg_Status'] = (int) $validatedData['reg_Status'];

        return $this->updateIfChanged($tipo, $validatedData, [
            'success_redirect'   => route('producto_tipo.index'),
            'success_message'    => 'Tipo de producto actualizado correctamente.',
            'no_changes_message' => 'No se realizaron cambios.',
            'set_updated_by'     => true,
            'use_transaction'    => true,
            'normalize_data'     => false,
        ]);
    }

    /**
     * Eliminación lógica
     */
    public function destroy(string $id)
    {
        try {
            $tipo = ProductoTipo::findOrFail($id);

            $tipo->deleted_by = Auth::id();
            $tipo->save();
            $tipo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de producto eliminado correctamente.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar producto_tipo', [
                'error'   => $e->getMessage(),
                'usuario' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el tipo de producto.'
            ], 400);
        }
    }

    /**
     * Vista de eliminados
     */
    public function showDeleted()
    {
        $tiposEliminados = ProductoTipo::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->get();

        return view('productos.tipos.deleted', compact('tiposEliminados'));
    }

    /**
     * Restaurar registro eliminado
     */
    public function restore(string $id)
    {
        try {
            $tipo = ProductoTipo::withTrashed()->findOrFail($id);
            $tipo->restore();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de producto restaurado con éxito.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar producto_tipo', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar el tipo de producto.'
            ], 400);
        }
    }
}
