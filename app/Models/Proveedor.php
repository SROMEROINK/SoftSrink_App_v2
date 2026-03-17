<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';
    protected $primaryKey = 'Prov_Id';

    protected $fillable = [
        'Prov_Nombre',
        'Prov_Detalle',
        'Es_Proveedor_MP',
        'Es_Proveedor_Herramientas',
        'Nombre_Contacto',
        'Nro_Telefono',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }
}