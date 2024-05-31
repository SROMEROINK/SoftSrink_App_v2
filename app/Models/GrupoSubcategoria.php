<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app\Models\Productos\GrupoSubcategoria.php
class GrupoSubcategoria extends Model
{
    use HasFactory;
    protected $table = 'producto_grupo_subcategoria';
    protected $primaryKey = 'Id_GrupoSubCategoria';
    protected $fillable = ['Nombre_GrupoSubCategoria'];
    public $timestamps = false;
}

