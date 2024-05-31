<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app\Models\Productos\Categoria.php
class Categoria extends Model
{
    use HasFactory;
    protected $table = 'producto_categoria';
    protected $primaryKey = 'Id_Categoria';
    protected $fillable = ['Nombre_Categoria'];
    public $timestamps = false;
}

