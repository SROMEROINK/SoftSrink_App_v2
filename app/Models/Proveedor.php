<?php


// app\Models\Proveedor.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importamos la clase SoftDeletes

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';
    protected $primaryKey = 'Prov_Id';
    protected $fillable = [
        'Prov_Nombre', 'Prov_Detalle', 'Nombre_Contacto', 'Nro_Telefono',
        'Es_Proveedor_MP', 'Es_Proveedor_Herramientas',
        'created_by', 'updated_by', 'deleted_by','reg_Status'
    ];

    public $timestamps = true; // Habilitar la gestión automática de timestamps
}
