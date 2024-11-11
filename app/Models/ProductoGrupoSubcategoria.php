<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes

// app\Models\Productos\GrupoSubcategoria.php
class ProductoGrupoSubcategoria extends Model
{
    use HasFactory;
    protected $table = 'producto_grupo_subcategoria';
    protected $primaryKey = 'Id_GrupoSubCategoria';
    protected $fillable = ['Nombre_GrupoSubCategoria'];
    public $timestamps = false;

        // Relación: Cada subcategoría pertenece a una categoría
        public function categoria()
        {
            return $this->belongsTo(ProductoCategoria::class, 'Id_Categoria', 'Id_Categoria');
        }
        
}

