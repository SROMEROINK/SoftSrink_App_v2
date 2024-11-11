<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes

// app\Models\Productos\Categoria.php
class ProductoCategoria extends Model
{
    use HasFactory;
    protected $table = 'producto_categoria';
    protected $primaryKey = 'Id_Categoria';
    protected $fillable = ['Nombre_Categoria'];
    public $timestamps = false;

    // Relación uno a muchos: Una categoría tiene muchas subcategorías
public function subcategorias()
{
    return $this->hasMany(ProductoSubCategoria::class, 'Id_Categoria', 'Id_Categoria');
}


}



