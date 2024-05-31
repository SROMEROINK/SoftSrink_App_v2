<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ListadoEntregaProducto;

class ListadoEntregaProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el valor del filtro de la solicitud
        $filtroNroOF_entregas = $request->query('filtroNroOF_entregas');
        $entrega_productos = ListadoEntregaProducto::with('listado_of.producto')->get();
    
        // Pasar los Ingresos_mp paginados a la vista correspondiente
        return view('Entregas_Productos.index', compact('entrega_productos','filtroNroOF_entregas'));
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
