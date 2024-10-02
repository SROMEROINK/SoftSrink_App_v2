<?php
// app\Models\Producto.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';
    protected $primaryKey = 'Id_Producto';
    protected $fillable = [
        // Asegúrate de que estos campos corresponden a las columnas en tu tabla 'productos'
        'Prod_Codigo', 'Prod_Descripcion', 'Id_Prod_Clasificacion_Piezas', 'Id_Prod_Clase_Familia',
        'Id_Prod_Sub_Familia', 'Id_Prod_Grupos_de_Sub_Familia', 'Id_Prod_Codigo_Conjuntos',
        'Prod_CliId', 'Prod_N_Plano', 'Prod_Plano_Ultima_Revisión', 'Prod_Material_MP',
        'Prod_Diametro_de_MP', 'Prod_Codigo_MP', 'Prod_Longitud_de_Pieza', 'Prod_Frenteado',
        'Prod_Ancho_De_Tronzado', 'Scrap_Maquina_S1', 'Scrap_Maquina_S2', 'Scrap_Maquina_S3',
        'Scrap_Maquina_H1', 'Scrap_Maquina_H2', 'Scrap_Maquina_H3', 'Scrap_Maquina_T1',
        'Prod_Sobrematerial_Promedio', 'Prod_Longitug_Total'
    ];

    public $timestamps = false;

    // Relaciones
    public function clasificacionPiezas()
    {
        return $this->belongsTo(ProductoClasificacion::class, 'Id_Prod_Clasificacion_Piezas', 'Id_Clasificacion');
    }

    public function categoria()
    {
        return $this->belongsTo(ProductoCategoria::class, 'Id_Prod_Clase_Familia', 'Id_Categoria');
    }

    public function subCategoria()
    {
        return $this->belongsTo(ProductoSubCategoria::class, 'Id_Prod_Sub_Familia', 'Id_SubCategoria');
    }

    public function grupoSubCategoria()
    {
        return $this->belongsTo(ProductoGrupoSubcategoria::class, 'Id_Prod_Grupos_de_Sub_Familia', 'Id_GrupoSubCategoria');
    }

// En app\Models\Productos\Producto.php

public function grupoConjuntos()
{
    return $this->belongsTo(ProductoGrupoConjuntos::class, 'Id_Prod_Codigo_Conjuntos', 'Id_GrupoConjuntos');
}

    public function cliente()
{
    // La clave foránea en Producto: 'Prod_CliId'
    // La clave primaria en Cliente: 'Cli_Id'
    return $this->belongsTo(Cliente::class, 'Prod_CliId', 'Cli_Id');
}


}


