<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Proveedor;
use App\Models\MpDiametro;
use App\Models\MpMateriaPrima;
use App\Models\MpSalidaInicial;

class MpIngreso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mp_ingreso';
    protected $primaryKey = 'Id_MP';

    protected $fillable = [
        'Nro_Ingreso_MP',
        'Nro_Pedido',
        'Nro_Remito',
        'Fecha_Ingreso',
        'Nro_OC',
        'Id_Proveedor',
        'Id_Materia_Prima',
        'Id_Diametro_MP',
        'Codigo_MP',
        'Nro_Certificado_MP',
        'Detalle_Origen_MP',
        'Unidades_MP',
        'Longitud_Unidad_MP',
        'Mts_Totales',
        'Kilos_Totales',
        'Ultima_Carga',
        'reg_Status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by',
    ];

    public $timestamps = true;

    protected $casts = [
        'Fecha_Ingreso' => 'date',
        'Unidades_MP' => 'integer',
        'Longitud_Unidad_MP' => 'decimal:2',
        'Mts_Totales' => 'decimal:2',
        'Kilos_Totales' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (self $ingreso) {
            $stockInicial = (int) ($ingreso->Unidades_MP ?? 0);
            $totalMts = round($stockInicial * (float) ($ingreso->Longitud_Unidad_MP ?? 0), 2);

            MpSalidaInicial::query()->firstOrCreate(
                ['Id_Ingreso_MP' => $ingreso->Id_MP],
                [
                    'Stock_Inicial' => $stockInicial,
                    'Devoluciones_Proveedor' => 0,
                    'Ajuste_Stock' => 0,
                    'Total_Salidas_MP' => $stockInicial,
                    'Total_mm_Utilizados' => $totalMts,
                    'reg_Status' => 1,
                    'created_by' => $ingreso->created_by,
                    'updated_by' => $ingreso->updated_by,
                ]
            );
        });
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
    }

    public function materiaPrima()
    {
        return $this->belongsTo(MpMateriaPrima::class, 'Id_Materia_Prima', 'Id_Materia_Prima');
    }

    public function diametro()
    {
        return $this->belongsTo(MpDiametro::class, 'Id_Diametro_MP', 'Id_Diametro');
    }

    public function salidaInicial()
    {
        return $this->hasOne(MpSalidaInicial::class, 'Id_Ingreso_MP', 'Id_MP');
    }
}
