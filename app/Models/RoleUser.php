<?php
// app\Models\RoleUser.php
namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RoleUser extends Model
{
    use HasFactory;
    protected $table = 'role_user'; // Asegúrate de que el nombre de la tabla sea correcto
    protected $primaryKey = 'id';
    protected $fillable = [
        'role_id',
        'user_id'
    ];
}