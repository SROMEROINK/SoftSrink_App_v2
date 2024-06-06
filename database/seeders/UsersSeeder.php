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
            'photo' => 'path/to/sergio_romero_administrador.jpg',
        ]);
        $admin->assignRole('Administrador');
        $admin->givePermissionTo('ver produccion', 'editar produccion'); // Asigna permisos específicos

        $produccion = User::create([
            'name' => 'Bernardo Abtt',
            'email' => 'bernardo@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/bernardo_abtt_produccion.jpg',
        ]);
        $produccion->assignRole('Producción');
        $produccion->givePermissionTo('ver produccion', 'editar produccion'); // Asigna permisos específicos

        $calidad = User::create([
            'name' => 'Gustavo Silva',
            'email' => 'gustavo@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/gustavo_silva_control_de_calidad.jpg',
        ]);
        $calidad->assignRole('Control de Calidad');
        $calidad->givePermissionTo('ver produccion', 'ver calidad', 'editar calidad'); // Asigna permisos específicos

        $produccionViewOnly = User::create([
            'name' => 'Tobias Berraz',
            'email' => 'tobias@example.com',
            'password' => Hash::make('password'),
            'photo' => 'path/to/tobias_berraz.jpg',
        ]);
        $produccionViewOnly->assignRole('Producción View Only');
        $produccionViewOnly->givePermissionTo('ver produccion'); // Asigna permisos específicos
    }
}
