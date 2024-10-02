<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategoria extends Model
{
    protected $table = 'producto_subcategoria';
    protected $primaryKey = 'Id_SubCategoria';
    public $timestamps = false;

    // Relación correcta con la tabla de productos usando el campo Id_Prod_Sub_Familia
    public function productos()
    {
        return $this->hasMany(Producto::class, 'Id_Prod_Sub_Familia', 'Id_SubCategoria');
    }
}

