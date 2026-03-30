<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listado_OF extends Model
{
    use SoftDeletes;

    protected $table = 'listado_of';
    protected $primaryKey = 'Id_OF';
    public $timestamps = true;

    protected $fillable = [
        'Nro_OF',
        'Producto_Id',
        'Fecha_del_Pedido',
        'Cant_Fabricacion',
        'Nro_Maquina',
        'MP_Id',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Nro_OF' => 'integer',
        'Producto_Id' => 'integer',
        'MP_Id' => 'integer',
        'Cant_Fabricacion' => 'integer',
        'Fecha_del_Pedido' => 'date',
        'reg_Status' => 'boolean',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Producto_Id', 'Id_Producto');
    }

    public function ingreso_mp()
    {
        return $this->belongsTo(MpIngreso::class, 'MP_Id', 'Id_MP');
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
