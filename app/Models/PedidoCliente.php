<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\EstadoPlanificacion;

class PedidoCliente extends Model
{
    use SoftDeletes;

    protected $table = 'pedido_cliente';
    protected $primaryKey = 'Id_OF';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nro_OF','Producto_Id','Fecha_del_Pedido','Cant_Fabricacion',
        'Estado_Plani_Id','reg_Status','created_by','updated_by','deleted_by',
    ];

    protected $casts = [
        'Estado_Plani_Id'   => 'integer',
        'reg_Status'        => 'boolean',
        'Fecha_del_Pedido'  => 'date',
        'Cant_Fabricacion'  => 'integer',
        'Nro_OF'            => 'integer',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto_Id', 'Id_Producto');
    }

    public function estadoPlanificacion()
    {
        return $this->belongsTo(EstadoPlanificacion::class, 'Estado_Plani_Id', 'Estado_Plani_Id');
    }

    public function definicionMp()
    {
        return $this->hasOne(PedidoClienteMp::class, 'Id_OF', 'Id_OF');
    }

    public function fechas() // 1:1
{
    return $this->hasOne(FechasOf::class, 'Id_OF', 'Id_OF');
}

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function updater() { return $this->belongsTo(User::class, 'updated_by'); }

    // Helpers para usar directo en vistas/datatable
    public function getCategoriaNombreAttribute()
    {
        return $this->producto->categoria->Nombre_Categoria ?? null;
    }
    public function getSubcategoriaNombreAttribute()
    {
        return $this->producto->subCategoria->Nombre_SubCategoria ?? null;
    }
}
