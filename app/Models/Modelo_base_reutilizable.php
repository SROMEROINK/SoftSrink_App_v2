<?php
// app\Models\Modelo_base_reutilizable.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Modelo_base_reutilizable extends Model
{
    use SoftDeletes;

    protected $table = 'nombre_tabla';
    protected $primaryKey = 'Id_Modulo';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'Campo_1',
        'Campo_2',
        'Campo_3',
        'reg_Status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'reg_Status' => 'integer',
    ];

    // Relaciones ejemplo
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}