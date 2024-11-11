<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes
use App\Models\Proveedor;
use App\Models\User;

class MarcasInsumos extends Model
{
    use HasFactory, SoftDeletes; // Aplica el trait SoftDeletes aquí

    protected $table = 'marcas_insumos'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'Id_Marca';

    protected $fillable = [
        'Nombre_marca',
        'Id_Proveedor',
        'reg_Status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public $timestamps = true; // Habilitar la gestión automática de timestamps

    // Relación con la tabla de proveedores
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
    }

    // Relación con el usuario que creó el registro
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    // Relación con el usuario que actualizó el registro
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    // Relación con el usuario que eliminó el registro
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }
}
