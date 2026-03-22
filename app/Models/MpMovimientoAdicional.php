<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class MpMovimientoAdicional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mp_movimientos_adicionales';
    protected $primaryKey = 'Id_Movimiento_MP';
    public $timestamps = true;

    protected $fillable = [
        'Fecha_Movimiento',
        'Mes',
        'Anio',
        'Nro_Ingreso_MP',
        'Concatenar_Proveedor',
        'Materia_Prima',
        'Diametro_MP',
        'Nro_Certificado_MP',
        'Nro_OF',
        'Codigo_Producto',
        'Nro_Maquina',
        'Cantidad_Adicionales',
        'Cantidad_Devoluciones',
        'Longitud_Unidad_Mts',
        'Total_Mtros_Movimiento',
        'Autorizado_por',
        'Observaciones',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Fecha_Movimiento' => 'date',
        'Anio' => 'integer',
        'Nro_Ingreso_MP' => 'integer',
        'Nro_OF' => 'integer',
        'Cantidad_Adicionales' => 'integer',
        'Cantidad_Devoluciones' => 'integer',
        'Longitud_Unidad_Mts' => 'decimal:2',
        'Total_Mtros_Movimiento' => 'decimal:2',
        'reg_Status' => 'boolean',
    ];

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

    public function autorizador()
    {
        return $this->belongsTo(User::class, 'Autorizado_por');
    }
}
