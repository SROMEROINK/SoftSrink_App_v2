<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app\Models\Productos\ClasificacionPiezas.php
class ClasificacionPiezas extends Model
{
    use HasFactory;
    protected $table = 'producto_clasificacion';
    protected $primaryKey = 'Id_Clasificacion';
    protected $fillable = ['Nombre_Clasificacion'];
    public $timestamps = false;
}