<?php
//app\Http\Controllers\RegistroDeFabricacionController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroDeFabricacion;

class RegistroDeFabricacionController extends Controller
{
    /**
     * Muestra la página principal de registros con un filtro opcional.
     */
    public function index(Request $request)
    {
        // Obtener el valor del filtro de la solicitud
        $filtroNroOF = $request->query('filtroNroOF');
        $registros_fabricacion = RegistroDeFabricacion::with('listado_of.producto')->get();
    
        // Pasar los registros de fabricacón paginados a la vista correspondiente
        return view('Fabricacion.index', compact('registros_fabricacion','filtroNroOF'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() // Abrir formulario de carga de registros
    {
        return view('Fabricacion.create');
    }

    

    /**
     * Guarda un nuevo registro en la base de datos.
     */
    public function store(Request $request)
    {
        $messages = [
            'nro_of.*.required' => 'El número de OF es obligatorio.',
            'nro_parcial.*.required' => 'El número de parcial es obligatorio.',
            'Nro_OF_Parcial.*.unique' => 'El número de OF parcial ya ha sido registrado.',
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
            'operario.*' => 'nullable|string|max:255', // Asegurando que es opcional
            'turno.*' => 'required',
            'cant_horas.*' => 'required|numeric',
        ], $messages);

        // Añadir log para depurar datos recibidos
        \Log::info('Nombre Operario:', $request->operario);

        if (!empty($request->nro_of)) {
            foreach ($request->nro_of as $index => $nro_of) {
                if (isset($request->Id_Producto[$index], $request->nro_parcial[$index], $request->cant_piezas[$index], $request->fecha_fabricacion[$index], $request->horario[$index], $request->operario[$index], $request->turno[$index], $request->cant_horas[$index])) {
                    $Nro_OF_Parcial = $nro_of . '/' . $request->nro_parcial[$index];

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

                    // Aquí se establece el campo operario basado en la condición del horario
                    if ($request->horario[$index] === 'H.Normales') {
                        $registro->Nombre_Operario = ''; // Asigna 'N/A' si el horario es 'H.Normales'
                    } else {
                        $registro->Nombre_Operario = $request->operario[$index] ?? null; // Utiliza el operario enviado o null si no se envía nada
                    }

                    $registro->save();
                }
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

        // Agrega una verificación para asegurarte de que realmente se están recuperando datos.
        if ($registros->isEmpty()) {
            return redirect()->back()->with('error', 'No se encontraron registros con ese Número de OF.');
        }

        return view('Fabricacion.show', compact('registros'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $registro_fabricacion = RegistroDeFabricacion::findOrFail($id);
        return view('Fabricacion.edit', compact('registro_fabricacion'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $Id_OF) // Actualizar un registro de fabricación
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

        $registro_fabricacion->save();
        
        // Redirección a la vista de detalles del registro actualizado con un mensaje de éxito
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
