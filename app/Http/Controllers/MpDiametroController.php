<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpDiametro;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class MpDiametroController extends Controller

{
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = MpDiametro::select('Id_Diametro', 'Valor_Diametro', 'reg_Status')->orderBy('Id_Diametro', 'asc');

            // Filtros dinámicos
            if ($request->filled('filtro_diametro')) {
                $query->where('Valor_Diametro', 'like', '%' . $request->filtro_diametro . '%');
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
        $totalDiametros = MpDiametro::count(); // Cuenta todos los diámetros
        return view('materia_prima.diametro.index', compact('totalDiametros'));
    }

    public function create()
    {
        return view('materia_prima.diametro.create');
    }

    public function store(Request $request)
{
    $validatedData = $request->validate([
        'Valor_Diametro' => 'required|string|max:255|unique:mp_diametro,Valor_Diametro,NULL,Id_Diametro,deleted_at,NULL',
        'reg_Status' => 'required|in:0,1',
    ], [
        'Valor_Diametro.required' => 'El valor del diámetro es obligatorio.',
        'Valor_Diametro.unique' => 'Este diámetro ya existe.',
        'reg_Status.required' => 'El estado es obligatorio.',
        'reg_Status.in' => 'El estado debe ser Activo o Inactivo.',
    ]);

    try {
        \DB::beginTransaction();
        $validatedData['created_by'] = Auth::id();
        MpDiametro::create($validatedData);
        \DB::commit();
        return response()->json(['success' => true, 'message' => 'Diámetro creado exitosamente.']);
    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error('Error al crear el diámetro:', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Error al crear el diámetro: ' . $e->getMessage()], 400);
    }
}

    public function edit($Id_Diametro)
    {
        $diametro = MpDiametro::findOrFail($Id_Diametro);
        return view('materia_prima.diametro.edit', compact('diametro'));
    }

    public function update(Request $request, $id)
    {
        $diametro = MpDiametro::findOrFail($id);

        $validatedData = $request->validate([
            'Valor_Diametro' => 'required|string|max:255',
            'reg_Status' => 'required|in:0,1',
        ]);

        $diametro->fill($validatedData);
        if ($diametro->isDirty()) {
            $diametro->updated_by = Auth::id();
            $diametro->save();
            return redirect()->route('mp_diametro.index')->with('success', 'Diámetro actualizado correctamente.');
        } else {
            return back()->with('warning', 'No se realizaron cambios.');
        }
    }

    public function destroy($Id_Diametro)
{
    try {
        $diametro = MpDiametro::findOrFail($Id_Diametro);
        $diametro->deleted_by = Auth::id(); // Asigna el ID del usuario que elimina el registro
        $diametro->save();
        $diametro->delete(); // Soft delete
        return response()->json(['success' => 'Diámetro eliminado correctamente']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Error al eliminar el diámetro'], 400);
    }
}

public function restore($id)
{
    try {
        $diametro = MpDiametro::withTrashed()->findOrFail($id);
        $diametro->restore();
        return redirect()->route('mp_diametro.index')->with('success', 'Diámetro restaurado con éxito');
    } catch (\Exception $e) {
        return redirect()->route('mp_diametro.index')->with('error', 'Error al restaurar el diámetro');
    }
}

public function showDeleted()
{
    $diametros = MpDiametro::onlyTrashed()->get(); // Recupera solo los diámetros eliminados
    return view('materia_prima.diametro.deleted', ['diametros' => $diametros]);
}
}
