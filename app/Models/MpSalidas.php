<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Listado_OF;
use App\Models\Ingreso_mp;
use App\Models\Proveedor;

class MpSalidas extends Model
{
    use HasFactory;

    protected $table = 'mp_salidas';
    protected $primaryKey = 'Id_Ingreso_MP';
    protected $fillable = [
        'Id_OF_Salidas_MP',
        'Cantidad_Unidades_MP',
        'Id_OF_Salidas_MP',
        'Cantidad_Unidades_MP_Preparadas',
        'Cantidad_MP_Adicionales',
        'Cant_Devoluciones',
        'Total_Salidas_MP',
        'Longitud_Unidad_MP',
        'Total_Mtros_Utilizados',
        'Fecha_del_Pedido_Produccion',
        'Responsable_Pedido_Produccion',
        'Nro_Pedido_MP',
        'Fecha_de_Entrega_Pedido_Calidad',
        'Responsable_de_entrega_Calidad'
    ];

    public $timestamps = false;
    public function listado_of()
    {
        return $this->belongsTo(Listado_OF::class, 'Id_OF_Salidas_MP');
    }

    public function producto()
    {
        return $this->hasOneThrough(Producto::class, Listado_OF::class, 'Nro_OF', 'Id_Producto', 'Id_OF', 'Producto_Id');
    }

    public function ingreso_mp()
    {
        return $this->belongsTo(Ingreso_mp::class, 'MP_Id', 'Nro_Ingreso_MP');
    }

    public function proveedor()
    {
        return $this->hasOneThrough(
            Proveedor::class, 
            Ingreso_mp::class, 
            'Id_OF', 
            'Prov_Id', 
            'Id_OF', 
            'Id_Proveedor'
        );
    }
}