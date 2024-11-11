<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes

class MpDiametro extends Model
{
    use SoftDeletes;

    protected $table = 'mp_diametro';
    protected $primaryKey = 'Id_Diametro';
    protected $fillable = [
        'Valor_Diametro', 'reg_Status', 'created_by', 'updated_by', 'deleted_by'
    ];

    public $timestamps = true; // Habilitar la gestión automática de timestamps
}
