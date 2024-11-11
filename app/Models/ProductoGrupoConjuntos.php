<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes

// app\Models\Productos\GrupoConjuntos.php
class ProductoGrupoConjuntos extends Model
{
    use HasFactory;
    protected $table = 'producto_grupo_conjuntos';
    protected $primaryKey = 'Id_GrupoConjuntos';
    protected $fillable = ['Nombre_GrupoConjuntos'];
    public $timestamps = false;
}