<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app\Models\Productos\GrupoConjuntos.php
class GrupoConjuntos extends Model
{
    use HasFactory;
    protected $table = 'producto_grupo_conjuntos';
    protected $primaryKey = 'Id_GrupoConjuntos';
    protected $fillable = ['Nombre_GrupoConjuntos'];
    public $timestamps = false;
}