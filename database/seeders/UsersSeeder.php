<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $admin = User::create([
            'name' => 'Sergio Romero',
            'email' => 'sergio@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/sergio_romero_administrador.jpg', // Ruta de la foto
        ]);
        $admin->assignRole('Administrador');

        $produccion = User::create([
            'name' => 'Bernardo Abtt',
            'email' => 'bernardo@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/bernardo_abtt_produccion.jpg', // Ruta de la foto
        ]);
        $produccion->assignRole('Producción');

        $calidad = User::create([
            'name' => 'Gustavo Silva',
            'email' => 'gustavo@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/gustavo_silva_control_de_calidad.jpg', // Ruta de la foto
        ]);
        $calidad->assignRole('Control de Calidad');

        $produccionViewOnly = User::create([
            'name' => 'Tobias Berraz',
            'email' => 'tobias@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/tobias_berraz.jpg', // Ruta de la foto (actualizar si tienes la foto)
        ]);
        $produccionViewOnly->assignRole('Producción View Only');
    }
}
