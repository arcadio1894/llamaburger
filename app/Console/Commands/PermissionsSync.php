<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class PermissionsSync extends Command
{
    protected $signature = 'permissions:sync
                            {--dry : Muestra lo que haría, sin escribir}
                            {--prune : Elimina permisos que ya no están en config}';

    protected $description = 'Sincroniza permisos desde config/permissions.php con la BD.';

    public function handle()
    {
        // 👇 tu estructura es config('permissions.modules')
        $cfg = (array) config('permissions.modules', []);
        if (empty($cfg)) {
            $this->warn('No hay módulos definidos en config/permissions.php (clave "modules").');
            return 0;
        }

        $dry   = (bool) $this->option('dry');
        $prune = (bool) $this->option('prune');

        // name => id de los que existen actualmente
        $existing = Permission::query()->pluck('id', 'name')->toArray();
        $seen     = []; // nombres vistos en el config

        $created = 0;
        $updated = 0;
        $deleted = 0;

        // 👇 recorrer EXACTAMENTE lo que tienes en config('permissions.modules')
        foreach ($cfg as $moduleKey => $def) {
            $actions = isset($def['actions']) ? (array) $def['actions'] : [];

            foreach ($actions as $actionKey => $meta) {
                $name  = $moduleKey . '.' . $actionKey;
                $label = isset($meta[0]) ? $meta[0] : ucfirst($actionKey);
                $desc  = isset($meta[1]) ? $meta[1] : null;

                $seen[$name] = true;

                $perm = Permission::where('name', $name)->first();

                if (!$perm) {
                    if ($dry) {
                        $this->line("[create] {$name}  module={$moduleKey}  label=\"{$label}\"  desc=\"{$desc}\"");
                    } else {
                        Permission::create([
                            'name'        => $name,
                            'guard_name'  => 'web',
                            'module'      => $moduleKey, // requiere columna "module" en permissions
                            'label'       => $label,     // requiere columna "label"  en permissions (opcional)
                            'description' => $desc,      // requiere columna "description"
                        ]);
                    }
                    $created++;
                    continue;
                }

                // Actualizaciones si cambiaron metadatos
                $dirty = false;
                if ($perm->module !== $moduleKey) { $perm->module = $moduleKey; $dirty = true; }
                if (property_exists($perm, 'label') && $perm->label !== $label) { $perm->label = $label; $dirty = true; }
                if ($perm->description !== $desc) { $perm->description = $desc; $dirty = true; }

                if ($dirty) {
                    if ($dry) {
                        $this->line("[update] {$name}  module={$moduleKey}  label=\"{$label}\"  desc=\"{$desc}\"");
                    } else {
                        $perm->save();
                    }
                    $updated++;
                }
            }
        }

        // Eliminar los que ya no están en el config (solo si se pide --prune)
        if ($prune) {
            foreach ($existing as $name => $id) {
                if (!isset($seen[$name])) {
                    if ($dry) {
                        $this->line("[delete] {$name}");
                    } else {
                        Permission::where('name', $name)->delete();
                    }
                    $deleted++;
                }
            }
        }

        $this->info("Sync finalizado: created={$created}, updated={$updated}, deleted={$deleted} (dry=" . ($dry ? 'yes' : 'no') . ")");

        return 0;
    }
}
