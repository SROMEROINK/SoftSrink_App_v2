<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class PedidoClienteMp extends Model
{
    use SoftDeletes;

    protected $table = 'pedido_cliente_mp';
    protected $primaryKey = 'Id_Pedido_MP';
    public $timestamps = true;

    protected $fillable = [
        'Id_OF',
        'Estado_Plani_Id',
        'Id_Maquina',
        'Nro_Maquina',
        'Familia_Maquina',
        'Scrap_Maquina',
        'Codigo_MP',
        'Materia_Prima',
        'Diametro_MP',
        'Nro_Ingreso_MP',
        'Pedido_Material_Nro',
        'Nro_Certificado_MP',
        'Longitud_Un_MP',
        'Largo_Pieza',
        'Frenteado',
        'Ancho_Cut_Off',
        'Sobrematerial_Promedio',
        'Largo_Total_Pieza',
        'MM_Totales',
        'Longitud_Barra_Sin_Scrap',
        'Cant_Barras_MP',
        'Fecha_Planificacion',
        'Responsable_Planificacion',
        'Cant_Piezas_Por_Barra',
        'Observaciones',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_OF' => 'integer',
        'Estado_Plani_Id' => 'integer',
        'Id_Maquina' => 'integer',
        'Scrap_Maquina' => 'decimal:2',
        'Nro_Ingreso_MP' => 'integer',
        'Longitud_Un_MP' => 'decimal:2',
        'Largo_Pieza' => 'decimal:2',
        'Frenteado' => 'decimal:2',
        'Ancho_Cut_Off' => 'decimal:2',
        'Sobrematerial_Promedio' => 'decimal:2',
        'Largo_Total_Pieza' => 'decimal:2',
        'MM_Totales' => 'decimal:2',
        'Longitud_Barra_Sin_Scrap' => 'decimal:2',
        'Cant_Barras_MP' => 'integer',
        'Fecha_Planificacion' => 'date',
        'Cant_Piezas_Por_Barra' => 'decimal:2',
        'reg_Status' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoCliente::class, 'Id_OF', 'Id_OF');
    }

    public function estadoPlanificacion()
    {
        return $this->belongsTo(EstadoPlanificacion::class, 'Estado_Plani_Id', 'Estado_Plani_Id');
    }

    public function salidaInicial()
    {
        return $this->hasOne(MpEgreso::class, 'Id_OF_Salidas_MP', 'Id_OF');
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
