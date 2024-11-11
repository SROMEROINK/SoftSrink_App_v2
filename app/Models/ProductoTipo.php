<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes


// app\Models\ProductoTipo.php
class ProductoTipo extends Model
{
    use HasFactory;
    protected $table = 'producto_tipo';
    protected $primaryKey = 'Id_Tipo';
    protected $fillable = ['Nombre_Tipo'];
    public $timestamps = false;
}