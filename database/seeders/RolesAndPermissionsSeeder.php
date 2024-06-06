<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crear permisos
        $permissions = [
            'ver produccion',
            'editar produccion',
            'ver calidad',
            'editar calidad',
            'administrar usuarios',
            'ver compras',
            'ver materia prima',
            'ver herramental',
            'ver solo administrador',
            'editar compras'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles y asignar permisos
        $adminRole = Role::firstOrCreate(['name' => 'Administrador']);
        $adminRole->syncPermissions(Permission::all());

        $productionRole = Role::firstOrCreate(['name' => 'Producción']);
        $productionRole->syncPermissions([
            'ver produccion',
            'editar produccion',
            'ver compras',
            'ver materia prima',
            'ver herramental'
        ]);

        $qualityRole = Role::firstOrCreate(['name' => 'Control de Calidad']);
        $qualityRole->syncPermissions([
            'ver produccion',
            'ver calidad',
            'editar calidad',
            'ver compras',
            'ver materia prima'
        ]);

        $viewOnlyRole = Role::firstOrCreate(['name' => 'Producción View Only']);
        $viewOnlyRole->syncPermissions([
            'ver produccion',
            'ver calidad'
        ]);
    }
}
