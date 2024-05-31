<?php
namespace Database\Seeders;
// database\seeders\RolesAndPermissionsSeeder.php
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles if they don't exist
        $roleAdmin = Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $roleProduccion = Role::firstOrCreate(['name' => 'Producción', 'guard_name' => 'web']);
        $roleCalidad = Role::firstOrCreate(['name' => 'Control de Calidad', 'guard_name' => 'web']);
        $roleProduccionViewOnly = Role::firstOrCreate(['name' => 'Producción View Only', 'guard_name' => 'web']);

        // Create permissions if they don't exist
        $permissions = [
            'view produccion',
            'edit produccion',
            'view calidad',
            'edit calidad',
            'manage users',
            'view purchases',
            'view materia_prima',
            'view herramental',
            'see admin only'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $roleAdmin->syncPermissions(Permission::all());
        $roleProduccion->syncPermissions(['view produccion', 'edit produccion']);
        $roleCalidad->syncPermissions(['view calidad', 'edit calidad']);
        $roleProduccionViewOnly->syncPermissions(['view produccion']);
    }
}
