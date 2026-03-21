<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoCategoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'producto_categoria';
    protected $primaryKey = 'Id_Categoria';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nombre_Categoria',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_Categoria' => 'integer',
        'reg_Status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function subcategorias()
    {
        return $this->hasMany(ProductoSubcategoria::class, 'Id_Categoria', 'Id_Categoria');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'Id_Prod_Categoria', 'Id_Categoria');
    }
}
