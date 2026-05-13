<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\EstadoPlanificacion;
use App\Models\FechasOf;

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

    protected static function booted(): void
    {
        static::created(function (self $pedido) {
            self::syncFechasOfBase($pedido);
        });

        static::updated(function (self $pedido) {
            self::syncFechasOfBase($pedido);
        });
    }

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

    protected static function syncFechasOfBase(self $pedido): void
    {
        $fechas = FechasOf::query()
            ->where('Id_OF', $pedido->Id_OF)
            ->first();

        $payload = [
            'Nro_OF_fechas' => $pedido->Nro_OF,
            'Nro_Programa_H1' => $fechas->Nro_Programa_H1 ?? null,
            'Nro_Programa_H2' => $fechas->Nro_Programa_H2 ?? null,
            'Inicio_PAP' => $fechas->Inicio_PAP ?? '9999-12-31',
            'Hora_Inicio_PAP' => $fechas->Hora_Inicio_PAP ?? '00:00:00',
            'Fin_PAP' => $fechas->Fin_PAP ?? '9999-12-31',
            'Hora_Fin_PAP' => $fechas->Hora_Fin_PAP ?? '00:00:00',
            'Inicio_OF' => $fechas->Inicio_OF ?? '9999-12-31',
            'Finalizacion_OF' => $fechas->Finalizacion_OF ?? '9999-12-31',
            'Tiempo_Pieza' => $fechas->Tiempo_Pieza ?? 0,
            'Tiempo_Seg' => $fechas->Tiempo_Seg ?? 0,
            'reg_Status' => 1,
            'updated_by' => $pedido->updated_by ?? $pedido->created_by,
        ];

        if (!$fechas) {
            $payload['created_by'] = $pedido->created_by;
        }

        FechasOf::query()->updateOrCreate(
            ['Id_OF' => $pedido->Id_OF],
            $payload
        );
    }
}
