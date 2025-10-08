<?php

namespace App\Console\Commands;

use App\Models\Mozo;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin';
    protected $description = 'Crear rol admin con todos los permisos, asignarlo al usuario #1 y crear su Mozo';

    public function handle()
    {
        $guard = config('auth.defaults.guard', 'web');

        try {
            // limpia caché de spatie
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            DB::transaction(function () use ($guard) {
                // 1) Usuario base (id=1)
                $user = User::find(1);
                if (!$user) {
                    $user = new User();
                    $user->id = 1;
                }
                $user->name  = 'Jorge Gonzales';
                $user->email = 'jorge@gmail.com';
                if (!$user->exists || !$user->password) {
                    $user->password = bcrypt('password'); // temporal
                }
                $user->save();
                $this->info("Usuario #{$user->id}: {$user->name} <{$user->email}> listo.");

                // 2) Normaliza guards de permisos/roles (auto-fix)
                Permission::whereNull('guard_name')->orWhere('guard_name','')->update(['guard_name'=>$guard]);
                Role::whereNull('guard_name')->orWhere('guard_name','')->update(['guard_name'=>$guard]);

                // 3) Rol admin con guard correcto
                $role = Role::firstOrCreate(
                    ['name' => 'admin', 'guard_name' => $guard]
                );
                $this->info("Rol 'admin' OK (id={$role->id}, guard={$role->guard_name}).");

                // 4) Toma todos los permisos con el mismo guard
                $allPerms = Permission::where('guard_name', $guard)->get();
                $role->syncPermissions($allPerms);
                $this->info("Asignados ".$allPerms->count()." permisos al rol 'admin'.");

                // 5) Asignar rol al usuario
                if (!$user->hasRole('admin')) {
                    $user->assignRole('admin'); // usa el guard del rol
                    $this->info("Rol 'admin' asignado al usuario #{$user->id}.");
                } else {
                    $this->info("Usuario #{$user->id} ya tenía el rol 'admin'.");
                }

                // 6) Crear/actualizar Mozo
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

            // limpia caché otra vez por si acaso
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $this->info('✅ Proceso completado.');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            report($e);
            $this->error('❌ Error: '.$e->getMessage());
            return Command::FAILURE;
        }
    }
}
