<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListadoEntregaProducto extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'listado_entregas_productos';
    protected $primaryKey = 'Id_List_Entreg_Prod';
    public $timestamps = true;

    protected $fillable = [
        'Id_OF',
        'Nro_Parcial_Calidad',
        'Cant_Piezas_Entregadas',
        'Nro_Remito_Entrega_Calidad',
        'Fecha_Entrega_Calidad',
        'Inspector_Calidad',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_OF' => 'integer',
        'Cant_Piezas_Entregadas' => 'integer',
        'Nro_Remito_Entrega_Calidad' => 'integer',
        'Fecha_Entrega_Calidad' => 'date',
        'reg_Status' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoCliente::class, 'Id_OF', 'Nro_OF');
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
