<?php

namespace App\Models;
// app\Models\ListadoEntregaProducto.php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes
use App\Models\Producto;
use App\Models\Listado_OF;
use App\Models\Ingreso_mp;
use App\Models\Proveedor;

class ListadoEntregaProducto extends Model
{
    use HasFactory;

    protected $table = 'listado_entregas_productos';
    protected $primaryKey = 'Id_List_Entreg_Prod';
    protected $fillable = [
        "Id_OF",
        "Nro_Parcial_Calidad",
        "Cant_Piezas_Entregadas",
        "Nro_Remito_Entrega_Calidad",
        "Fecha_Entrega_Calidad",
        "Inspector_Calidad"       
    ];
    public $timestamps = false;

    public function listado_of()
    {
        return $this->belongsTo(Listado_OF::class, 'Id_OF');
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
