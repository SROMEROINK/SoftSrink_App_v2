<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MpSalidas;

class MpSalidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el valor del filtro de la solicitud
        $filtroNroOF = $request->query('filtroNroOF');
        $salidas_mp = MpSalidas::with('listado_of.producto')->get();
    
        
        return view('Materia_Prima_Salidas.index', compact('salidas_mp','filtroNroOF'));
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
