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
        'Stock_Inicial',
        'Devoluciones_Proveedor',
        'Ajuste_Stock',
        'Total_Salidas_MP',
        'Total_mm_Utilizados',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_Ingreso_MP' => 'integer',
        'Stock_Inicial' => 'integer',
        'Devoluciones_Proveedor' => 'integer',
        'Ajuste_Stock' => 'integer',
        'Total_Salidas_MP' => 'integer',
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
