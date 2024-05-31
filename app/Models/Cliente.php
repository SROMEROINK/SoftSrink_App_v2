<?php

namespace App\Models; // Asegúrate de que el namespace sea correcto

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes'; // El nombre correcto de la tabla en tu base de datos
    protected $primaryKey = 'Cli_Id'; // La clave primaria de la tabla
    protected $fillable = ['Cli_Nombre']; // Los campos que quieres que sean asignables en masa

    public $timestamps = false; // Desactiva los timestamps si tu tabla no tiene las columnas 'created_at' y 'updated_at'
}
