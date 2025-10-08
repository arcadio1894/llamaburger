<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules  = config('modules');
        $actions  = config('permission_actions');

        foreach ($modules as $moduleKey => $moduleLabel) {
            foreach ($actions as $actionKey => $actionLabel) {
                // Puedes filtrar acciones por módulo si quieres
                if ($moduleKey === 'dashboard' && !in_array($actionKey, ['view','manage'])) {
                    continue;
                }

                $name = "{$moduleKey}.{$actionKey}"; // ej. salas.create

                $perm = Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    ['module' => $moduleKey, 'description' => "{$actionLabel} {$moduleLabel}"]
                );
            }
        }

        // Rol admin con todo
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        // Rol mozo (ejemplo mínimo)
        $mozo = Role::firstOrCreate(['name' => 'mozo', 'guard_name' => 'web']);
        $mozo->syncPermissions(
            Permission::whereIn('name', [
                'dashboard.view',
                'mesas.view',
                'atenciones.create','atenciones.view','atenciones.update',
                'comandas.create','comandas.view','comandas.update',
                'pagos.view', // si ven estado de pago
            ])->get()
        );
    }
}
