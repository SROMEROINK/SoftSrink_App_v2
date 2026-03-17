<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoTipo extends Model
{
    use SoftDeletes;

    protected $table = 'producto_tipo';
    protected $primaryKey = 'Id_Tipo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Nombre_Tipo',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'reg_Status' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function productos()
    {
        return $this->hasMany(Producto::class, 'Id_Prod_Tipo', 'Id_Tipo');
    }
}