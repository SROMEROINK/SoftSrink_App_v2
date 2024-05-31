<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app\Models\Productos\SubFamilia.php
class SubFamilia extends Model
{
    use HasFactory;
    protected $table = 'producto_subcategoria';
    protected $primaryKey = 'Id_SubCategoria';
    protected $fillable = ['Nombre_SubCategoria'];
    public $timestamps = false;
}
