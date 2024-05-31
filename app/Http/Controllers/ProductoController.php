<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producto;

class ProductoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ver produccion')->only(['index', 'show']);
        $this->middleware('permission:editar produccion')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener los productos de la base de datos y paginarlos
        $productos = Producto::paginate(10); // Esto paginará los resultados, mostrando 10 productos por página
    
        // Pasar los productos paginados a la vista correspondiente
        return view('productos.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Devolver la vista de creación
        return view('productos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            // Añade más validaciones según tus campos
        ]);

        // Crear un nuevo producto
        Producto::create($request->all());

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto creado con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        // Mostrar la vista de detalles del producto
        return view('productos.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Producto $producto)
    {
        // Devolver la vista de edición y pasar el producto a esa vista
        return view('productos.edit', compact('producto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric',
            // Añade más validaciones según tus campos
        ]);

        // Actualizar el producto
        $producto->update($request->all());

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        // Eliminar el producto
        $producto->delete();

        // Redirigir a la lista de productos con un mensaje de éxito
        return redirect()->route('productos.index')->with('success', 'Producto eliminado con éxito');
    }
}
