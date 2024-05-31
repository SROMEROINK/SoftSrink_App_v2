<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ingreso_mp;

class MpIngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener los Ingresos_mp de la base de datos y paginarlos
        $ingresos_mp = Ingreso_mp::paginate(10); // Esto paginará los resultados, mostrando 10 Ingresos_mp de por página
    
        // Pasar los Ingresos_mp paginados a la vista correspondiente
        return view('Materia_Prima_Ingresos.index', compact('ingresos_mp'));
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
