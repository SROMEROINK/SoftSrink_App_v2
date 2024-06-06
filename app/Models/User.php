<?php
// app\Models\User.php
namespace App\Models;

use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function adminlte_image()
    {
        return $this->photo ? asset('storage/' . $this->photo) : 'https://picsum.photos/seed/picsum/200/300';
    }

    public function adminlte_desc()
    {
        $roles = $this->getRoleNames(); // Obtiene todos los nombres de los roles del usuario
        return $roles->implode(', '); // Retorna los roles como una cadena separada por comas
    }

    public function adminlte_profile_url()
    {
        return route('profile.show', ['profile' => $this->id]);
    }
    
}
