<?php
// app\Http\Controllers\MpMateriaPrimaController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpMateriaPrima;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class MpMateriaPrimaController extends Controller
{
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MpMateriaPrima::select('Id_Materia_Prima', 'Nombre_Materia', 'reg_Status')->orderBy('Id_Materia_Prima', 'asc');

            // Filtros dinámicos
            if ($request->filled('filtro_materia')) {
                $query->where('Nombre_Materia', 'like', '%' . $request->filtro_materia . '%');
            }
            if ($request->filled('filtro_estado')) {
                $query->where('reg_Status', $request->filtro_estado);
            }

            return DataTables::eloquent($query)
                ->addColumn('action', function ($data) {
                    return '<button type="button" class="btn btn-primary btn-sm">Editar</button>';
                })
                ->make(true);
        }
    }

    public function index()
    {
        $totalMaterias = MpMateriaPrima::count(); // Cuenta todas las materias primas
        return view('materia_prima.materias_base.index', compact('totalMaterias'));
    }

    public function create()
    {
        return view('materia_prima.materias_base.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'Nombre_Materia' => 'required|string|max:255|unique:mp_materia_prima,Nombre_Materia,NULL,Id_Materia_Prima,deleted_at,NULL',
            'reg_Status' => 'required|in:0,1',
        ], [
            'Nombre_Materia.required' => 'El nombre de la materia prima es obligatorio.',
            'Nombre_Materia.unique' => 'Esta materia prima ya existe.',
            'reg_Status.required' => 'El estado es obligatorio.',
            'reg_Status.in' => 'El estado debe ser Activo o Inactivo.',
        ]);

        try {
            \DB::beginTransaction();
            $validatedData['created_by'] = Auth::id();
            MpMateriaPrima::create($validatedData);
            \DB::commit();
            return response()->json(['success' => true, 'message' => 'Materia prima creada exitosamente.']);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear la materia prima:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al crear la materia prima: ' . $e->getMessage()], 400);
        }
    }

    public function edit($Id_Materia_Prima)
    {
        $materiaBase = MpMateriaPrima::findOrFail($Id_Materia_Prima);
        return view('materia_prima.materias_base.edit', compact('materiaBase'));
    }

    public function update(Request $request, $id)
    {
        $materiaPrima = MpMateriaPrima::findOrFail($id);

        $validatedData = $request->validate([
            'Nombre_Materia' => 'required|string|max:255',
            'reg_Status' => 'required|in:0,1',
        ]);

        $materiaPrima->fill($validatedData);
        if ($materiaPrima->isDirty()) {
            $materiaPrima->updated_by = Auth::id();
            $materiaPrima->save();
            return redirect()->route('mp_materia_prima.index')->with('success', 'Materia prima actualizada correctamente.');
        } else {
            return back()->with('warning', 'No se realizaron cambios.');
        }
    }

    public function destroy($Id_Materia_Prima)
    {
        try {
            // Buscar la materia prima por su ID
            $materiaPrima = MpMateriaPrima::findOrFail($Id_Materia_Prima);
    
            // Asignar el ID del usuario que elimina el registro
            $materiaPrima->deleted_by = Auth::id();
            $materiaPrima->save();
    
            // Soft delete
            $materiaPrima->delete();
    
            return response()->json(['success' => 'Materia prima eliminada correctamente']);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar la materia prima:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Error al eliminar la materia prima'], 400);
        }
    }
    
    public function restore($id)
    {
        try {
            // Restaurar una materia prima eliminada
            $materiaPrima = MpMateriaPrima::withTrashed()->findOrFail($id);
            $materiaPrima->restore();
    
            return redirect()->route('mp_materia_prima.index')->with('success', 'Materia prima restaurada con éxito');
        } catch (\Exception $e) {
            \Log::error('Error al restaurar la materia prima:', ['error' => $e->getMessage()]);
            return redirect()->route('mp_materia_prima.index')->with('error', 'Error al restaurar la materia prima');
        }
    }
    
    public function showDeleted()
    {
        // Recupera solo las materias primas eliminadas
        $materiasPrimas = MpMateriaPrima::onlyTrashed()->get();
    
        return view('materia_prima.materias_base.deleted', ['materiasPrimas' => $materiasPrimas]);
    }
}


