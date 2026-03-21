<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoGrupoConjuntos extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto_grupo_conjuntos';
    protected $primaryKey = 'Id_GrupoConjuntos';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nombre_GrupoConjuntos',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_GrupoConjuntos' => 'integer',
        'reg_Status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'Id_Prod_GrupoConjuntos', 'Id_GrupoConjuntos');
    }
}
