<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes

class MpMateriaPrima extends Model
{
    use HasFactory, SoftDeletes; // Aplica el trait SoftDeletes aquí

    // Nombre de la tabla en la base de datos
    protected $table = 'mp_materia_prima';

    // Clave primaria de la tabla
    protected $primaryKey = 'Id_Materia_Prima';

    // Campos permitidos para la asignación masiva
    protected $fillable = [
        'Nombre_Materia',
        'reg_Status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];

    // Deshabilitar timestamps automáticos si no los usas
    public $timestamps = true;
}

