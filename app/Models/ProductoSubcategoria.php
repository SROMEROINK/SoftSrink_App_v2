<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoSubcategoria extends Model
{
    use SoftDeletes;

    protected $table = 'producto_subcategoria';
    protected $primaryKey = 'Id_SubCategoria';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Id_Categoria',
        'Nombre_SubCategoria',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_SubCategoria' => 'integer',
        'Id_Categoria' => 'integer',
        'reg_Status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'Id_Categoria', 'Id_Categoria');
    }

    public function productos()
    {
        return $this->hasMany(Producto::class, 'Id_Prod_SubCategoria', 'Id_SubCategoria');
    }
}
