<?php
// app\Models\MpIngreso.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Proveedor; // Importa el modelo correcto

class MpIngreso extends Model
{
    use HasFactory;

    protected $table = 'mp_ingreso';
    protected $primaryKey = 'Id_MP';
    protected $fillable = [
        // Asegúrate de que estos campos corresponden a las columnas en tu tabla 'mp_ingreso'
 

'Nro_Ingreso_MP',
'Nro_Pedido',
'Nro_Remito',
'Fecha_Ingreso',
'Nro_OC',
'Id_Proveedor',
'Materia_Prima',
'Diametro_MP',
'Codigo_MP',
'Nro_Certificado_MP',
'Detalle_Origen_MP',
'Unidades_MP',
'Longitud_Unidad_MP',
'Mts_Totales',
'Kilos_Totales',
'Ultima_Carga',
'Prod_habilitado_deshabilitado',
'created_at',
'created_by',
'updated_at',
'updated_by',
'deleted_at',
'deleted_by'

    
  
    ];

    public $timestamps = false;

    public function proveedor()
    {
        // La clave foránea en ingreso_mp: 'Id_Proveedor'
        // La clave primaria en proveedores: 'Prov_Id'
        return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id'); // Cambia el nombre del modelo a Proveedor y especifica correctamente el namespace
    }
}



