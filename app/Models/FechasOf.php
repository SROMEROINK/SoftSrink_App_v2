<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FechasOf extends Model
{
    use HasFactory;

    protected $table = 'fechas_of';
    protected $primaryKey = 'Id_Fechas';
    public $timestamps = true;

    protected $fillable = [
        'Id_OF',
        'Nro_OF_fechas',
        'Nro_Programa_H1',
        'Nro_Programa_H2',
        'Inicio_PAP',
        'Hora_Inicio_PAP',
        'Fin_PAP',
        'Hora_Fin_PAP',
        'Inicio_OF',
        'Finalizacion_OF',
        'Tiempo_Pieza',
        'Tiempo_Seg',
        'reg_Status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'Id_OF' => 'integer',
        'Nro_OF_fechas' => 'integer',
        'Inicio_PAP' => 'date',
        'Fin_PAP' => 'date',
        'Inicio_OF' => 'date',
        'Finalizacion_OF' => 'date',
        'Tiempo_Pieza' => 'decimal:2',
        'Tiempo_Seg' => 'integer',
        'reg_Status' => 'boolean',
    ];

    public function pedido()
    {
        return $this->belongsTo(PedidoCliente::class, 'Id_OF', 'Id_OF');
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
