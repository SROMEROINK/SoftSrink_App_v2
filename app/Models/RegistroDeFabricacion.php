<?php
// app\Models\RegistroDeFabricacion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroDeFabricacion extends Model
{
    use HasFactory;

    protected $table = 'registro_de_fabricacion';
    protected $primaryKey = 'Id_OF';

    protected $fillable = [
        'Id_OF',
        'Nro_OF',
        'Id_Producto',
        'Nro_Parcial',
        'Nro_OF_Parcial',
        'Cant_Piezas',
        'Fecha_Fabricacion',
        'Horario',
        'Nombre_Operario',
        'Turno',
        'Cant_Horas_Extras',
        'Ultima_Carga',
        'Status_OF',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    public $timestamps = true;

    protected $casts = [
        'Fecha_Fabricacion' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoCliente::class, 'Nro_OF', 'Id_OF');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'Id_Producto', 'Id_Producto');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
