<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Proveedor;
use App\Models\MpIngreso;

class MpEgreso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mp_salidas';
    protected $primaryKey = 'Id_Ingreso_MP';

    protected $fillable = [
        'Id_OF_Salidas_MP',
        'Cantidad_Unidades_MP',
        'Cantidad_Unidades_MP_Preparadas',
        'Cantidad_MP_Adicionales',
        'Cant_Devoluciones',
        'Total_Salidas_MP',
        'Total_Mtros_Utilizados',
        'Fecha_del_Pedido_Produccion',
        'Responsable_Pedido_Produccion',
        'Nro_Pedido_MP',
        'Fecha_de_Entrega_Pedido_Calidad',
        'Responsable_de_entrega_Calidad',
        'Ultima_Carga',
        'reg_Status',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
        'deleted_by'
    ];

    public $timestamps = true; // Habilitar la gestión automática de timestamps

    // Relación con la tabla de proveedores a través de MpIngreso
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
    }

    // Relación con MpIngreso para obtener detalles de los ingresos de materia prima
    public function mpIngreso()
    {
        return $this->belongsTo(MpIngreso::class, 'Id_Ingreso_MP', 'Nro_Ingreso_MP');
    }
}
