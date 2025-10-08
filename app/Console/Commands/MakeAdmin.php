<?php

namespace App\Console\Commands;

use App\Models\Mozo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin';
    protected $description = 'Crear rol admin con todos los permisos, asignarlo al usuario #1 y crear su Mozo';

    public function handle()
    {
        try {
            DB::transaction(function () {
                // 1) Usuario base (id=1)
                $user = User::find(1);
                if (!$user) {
                    $user = new User();
                    $user->id = 1; // forzar id=1
                }
                $user->name  = 'Jorge Gonzales';
                $user->email = 'jorge@gmail.com';
                // No tocamos el password si ya existe
                if (!$user->exists || !$user->password) {
                    $user->password = bcrypt('password'); // temporal
                }
                $user->save();

                $this->info("Usuario #{$user->id}: {$user->name} <{$user->email}> listo.");

                // 2) Rol admin
                $role = Role::firstOrCreate(
                    ['name' => 'admin', 'guard_name' => 'web']
                );
                $this->info("Rol 'admin' OK (id={$role->id}).");

                // 3) Asignar todos los permisos al rol
                $allPerms = Permission::all(['id', 'name']);
                $role->syncPermissions($allPerms);
                $this->info("Asignados ".count($allPerms)." permisos al rol 'admin'.");

                // 4) Asignar rol al usuario
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin');
                    $this->info("Rol 'admin' asignado al usuario #{$user->id}.");
                } else {
                    $this->info("Usuario #{$user->id} ya tenía el rol 'admin'.");
                }

                // 5) Crear/actualizar Mozo
                $mozo = Mozo::where('user_id', $user->id)->first();
                if (!$mozo) {
                    $mozo = Mozo::create([
                        'user_id' => $user->id,
                        'nombre'  => $user->name,
                        'activo'  => 1,
                    ]);
                    $this->info("Mozo creado (id={$mozo->id}).");
                } else {
                    $mozo->update([
                        'nombre' => $user->name,
                        'activo' => 1,
                    ]);
                    $this->info("Mozo actualizado (id={$mozo->id}).");
                }
            });

            $this->info('✅ Proceso completado.');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            report($e);
            $this->error('❌ Error: '.$e->getMessage());
            return Command::FAILURE;
        }
    }
}
