<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Ingreso_mp;


class Listado_OF extends Model
{
    use HasFactory;
    protected $table = 'listado_of';
    protected $primaryKey = 'Id_OF';
    protected $fillable = [
        // Asegúrate de que estos campos corresponden a las columnas en tu tabla 'listado_of'
            "Id_OF",
            "Nro_OF",
            "Estado_Planificacion",
            "Estado",
            "Producto_Id",
            "Revision_Plano_1",
            "Revision_Plano_2",
            "Fecha_del_Pedido",
            "Cant_Fabricacion",
            "Nro_Maquina",
            "Familia_Maquinas",
            "MP_Id",
            "Pedido_de_MP",
            "Tiempo_Pieza_Real",
            "Tiempo_Pieza_Aprox",
            "Cant_Unidades_MP",
            "Cant_Piezas_Por_Unidad_MP"

        ];
        
        public $timestamps = false;

        public function producto()
        {
            // La clave foránea en Listado_OF: 'Producto_Id'
            // La clave primaria en producto: 'Id_Producto'
            return $this->belongsTo('App\Models\Producto', 'Producto_Id', 'Id_Producto'); // Cambia el nombre del modelo a Producto y especifica correctamente el namespace
        }


        public function categoria()
        {    // La clave foránea en Listado_OF: 'Producto_Id'
            // La clave primaria en categoria: 'Id_Categoria'
            return $this->belongsTo(Categoria::class, 'Id_Prod_Clase_Familia', 'Id_Categoria');
        }

        
        public function  ingreso_mp()
        {
            // La clave foránea en listado_of: 'MP_Id'
            // La clave primaria en ingreso_mp: 'Id_MP'
            return $this->belongsTo(Ingreso_mp::class, 'MP_Id', 'Nro_Ingreso_MP'); // Cambia el nombre del modelo a ingreso_mp y especifica correctamente el namespace
        }
    }
    
    