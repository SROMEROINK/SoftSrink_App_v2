<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedores';
    protected $primaryKey = 'Prov_Id';
    protected $fillable = ['Prov_Nombre'];
    public $timestamps = false;
}
