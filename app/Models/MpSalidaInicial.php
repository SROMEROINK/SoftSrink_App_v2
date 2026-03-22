<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MpSalidaInicial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mp_salidas_iniciales';
    protected $primaryKey = 'Id_Ingreso_MP';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Id_Ingreso_MP',
        'Cantidad_Unidades_MP',
        'Cantidad_Unidades_MP_Preparadas',
        'Cantidad_MP_Adicionales',
        'Total_Salidas_MP',
        'Devoluciones_Unidades_MP',
        'Total_Unidades',
        'Longitud_Unidad_MP',
        'Total_mm_Utilizados',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_Ingreso_MP' => 'integer',
        'Cantidad_Unidades_MP' => 'integer',
        'Cantidad_Unidades_MP_Preparadas' => 'integer',
        'Cantidad_MP_Adicionales' => 'integer',
        'Total_Salidas_MP' => 'integer',
        'Devoluciones_Unidades_MP' => 'integer',
        'Total_Unidades' => 'integer',
        'Longitud_Unidad_MP' => 'decimal:2',
        'Total_mm_Utilizados' => 'decimal:2',
        'reg_Status' => 'boolean',
    ];

    public function ingresoMp()
    {
        return $this->belongsTo(MpIngreso::class, 'Id_Ingreso_MP', 'Id_MP');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
