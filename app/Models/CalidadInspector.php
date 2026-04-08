<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalidadInspector extends Model
{
    use HasFactory;

    protected $table = 'calidad_inspectores';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function entregas()
    {
        return $this->hasMany(ListadoEntregaProducto::class, 'Id_Inspector_Calidad');
    }
}
