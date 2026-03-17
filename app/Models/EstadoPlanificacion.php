<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EstadoPlanificacion extends Model
{
    use SoftDeletes;

    protected $table = 'estado_planificacion';
    protected $primaryKey = 'Estado_Plani_Id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nombre_Estado',
        'Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Status' => 'integer',
    ];

    // IDs útiles
    public const SIN_REALIZAR = 1;
    public const EN_PROCESO   = 2;
    public const FINALIZADA   = 3;
    public const SUSPENDIDA   = 4;
    public const LIBERACION   = 6;
    public const LIBRE        = 7;
}