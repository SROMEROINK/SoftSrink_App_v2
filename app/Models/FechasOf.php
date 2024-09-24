<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Listado_OF;
use App\Models\Producto;
use App\Models\ProductoCategoria;

class FechasOF extends Model
{
    use HasFactory;

    protected $table = 'fechas_of';
    protected $primaryKey = 'Id_Fechas';

    protected $fillable = [
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
        'created_by', // Si tienes los campos de auditoría
        'updated_by'  // Si tienes los campos de auditoría
    ];

    // Relación con Listado_OF (similar a RegistroDeFabricacion)
    public function listadoOf()
    {
        return $this->belongsTo(Listado_OF::class, 'Nro_OF_fechas', 'Nro_OF');
    }

    // Relación con Producto a través de Listado_OF (hasOneThrough)
    public function producto()
    {
        return $this->hasOneThrough(Producto::class, Listado_OF::class, 'Nro_OF', 'Id_Producto', 'Nro_OF_fechas', 'Producto_Id');
    }

    // Relación con la Categoría del Producto (similar a RegistroDeFabricacion)
    public function categoria()
    {
        return $this->hasOneThrough(ProductoCategoria::class, Producto::class, 'Id_Producto', 'Id_Categoria', 'Id_Producto', 'Id_Prod_Clase_Familia');
    }

    // Relación con usuarios (si usas los campos de auditoría)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
