<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistroDeFabricacion;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function dataSource(Request $request)
    {
        $registros_fabricacion = RegistroDeFabricacion::all();
        return response()->json($registros_fabricacion);
    }     
}