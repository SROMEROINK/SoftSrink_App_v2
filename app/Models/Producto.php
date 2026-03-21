<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';
    protected $primaryKey = 'Id_Producto';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Prod_Codigo',
        'Prod_Descripcion',
        'Id_Prod_Tipo',
        'Id_Prod_Categoria',
        'Id_Prod_SubCategoria',
        'Id_Prod_GrupoSubcategoria',
        'Id_Prod_GrupoConjuntos',
        'Prod_CliId',
        'Prod_N_Plano',
        'Prod_Plano_Ultima_Revision',
        'Prod_Material_MP',
        'Prod_Diametro_de_MP',
        'Prod_Codigo_MP',
        'Prod_Longitud_de_Pieza',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'Id_Producto' => 'integer',
        'Id_Prod_Tipo' => 'integer',
        'Id_Prod_Categoria' => 'integer',
        'Id_Prod_SubCategoria' => 'integer',
        'Id_Prod_GrupoSubcategoria' => 'integer',
        'Id_Prod_GrupoConjuntos' => 'integer',
        'Prod_CliId' => 'integer',
        'Prod_N_Plano' => 'integer',
        'Prod_Longitud_de_Pieza' => 'decimal:2',
        'reg_Status' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'deleted_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function productoTipo()
    {
        return $this->belongsTo(ProductoTipo::class, 'Id_Prod_Tipo', 'Id_Tipo');
    }

    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'Id_Prod_Categoria', 'Id_Categoria');
    }

    public function subCategoria()
    {
        return $this->belongsTo(ProductoSubcategoria::class, 'Id_Prod_SubCategoria', 'Id_SubCategoria');
    }

    public function grupoSubCategoria()
    {
        return $this->belongsTo(ProductoGrupoSubcategoria::class, 'Id_Prod_GrupoSubcategoria', 'Id_GrupoSubCategoria');
    }

    public function grupoConjuntos()
    {
        return $this->belongsTo(ProductoGrupoConjuntos::class, 'Id_Prod_GrupoConjuntos', 'Id_GrupoConjuntos');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'Prod_CliId', 'Cli_Id');
    }
}
