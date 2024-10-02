<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Ingreso_mp;

class PedidoCliente extends Model
{
    use HasFactory;
    protected $table = 'pedido_cliente'; // Alias para la nueva tabla
    protected $primaryKey = 'Id_OF';
    protected $fillable = [
        "Id_OF",
        "Nro_OF",
        "Producto_Id",
        "Fecha_del_Pedido",
        "Cant_Fabricacion"
    ];
    
    public $timestamps = false;

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto_Id', 'Id_Producto');
    }

    // Relación con Categoria a través de Producto
    public function categoria()
    {
        return $this->hasOneThrough(
            Categoria::class,
            Producto::class,
            'Id_Producto',       // Foreign key en Producto
            'Id_Categoria',      // Primary key en Categoria
            'Producto_Id',       // Foreign key en Listado_OF
            'Id_Prod_Clase_Familia' // Foreign key en Producto
        );
    }

       /* // Relación con Subcategoria a través de Producto
        public function Subcategoria()
        {
            return $this->hasOneThrough(
                SubCategoria::class,
                Producto::class,
                'Id_Producto',       // Foreign key en Producto
                'Id_Categoria',      // Primary key en Categoria
                'Producto_Id',       // Foreign key en Listado_OF
                'Id_Prod_Clase_Familia' // Foreign key en Producto
            );
        }*/

}
