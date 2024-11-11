<?php
// app\Models\MpIngreso.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Importa la clase SoftDeletes
use App\Models\Proveedor;
use App\Models\MpDiametro;
use App\Models\MpMateriaPrima;

class MpIngreso extends Model
{
    use HasFactory, SoftDeletes; // Aplica el trait SoftDeletes aquí;

    protected $table = 'mp_ingreso'; // Actualiza el nombre de la tabla
    protected $primaryKey = 'Id_MP';

    protected $fillable = [
        'Nro_Ingreso_MP',
        'Nro_Pedido',
        'Nro_Remito',
        'Fecha_Ingreso',
        'Nro_OC',
        'Id_Proveedor',
        'Id_Materia_Prima', // Actualiza el nombre del campo para reflejar la nueva relación
        'Id_Diametro_MP',      // Actualiza el nombre del campo para reflejar la nueva relación
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
        'deleted_by'
    ];

    public $timestamps = true; // Habilitar la gestión automática de timestamps

    // Relación con la tabla de proveedores
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'Id_Proveedor', 'Prov_Id');
    }

    // Nueva relación con la tabla mp_materia_prima
    public function materiaPrima()
    {
        return $this->belongsTo(MpMateriaPrima::class, 'Id_Materia_Prima', 'Id_Materia_Prima');
    }

    // Nueva relación con la tabla mp_diametro
    public function diametro()
    {
        return $this->belongsTo(MpDiametro::class, 'Id_Diametro_MP', 'Id_Diametro');
    }
}
