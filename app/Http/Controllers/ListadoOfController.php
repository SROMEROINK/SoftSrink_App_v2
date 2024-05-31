<?php
//app\Http\Controllers\ListadoOfController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Listado_OF;

class ListadoOfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el valor del filtro de la solicitud
        $filtroNroOF = $request->query('filtroNroOF');
    
        if ($filtroNroOF) {
            $listados_of = Listado_OF::where('Nro_OF', $filtroNroOF)->get();
        } else {
            $listados_of = Listado_OF::all();
        }
    
        // Pasar los resultados a la vista
        return view('Listado_de_OF.index', compact('listados_of', 'filtroNroOF'));
    }



    public function getIdProductoPorNroOf($nroOf)
    {
        try {
            $listado_of = Listado_OF::where('Nro_OF', $nroOf)->firstOrFail();
            return response()->json(['success' => true, 'id_producto' => $listado_of->Producto_Id]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Producto no encontrado.']);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
