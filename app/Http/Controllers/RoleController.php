<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        // Mapa de permisos agrupados por módulo para armar la grilla en el modal
        $perms = Permission::orderBy('module')->orderBy('name')->get([
            'id','name','module','description'
        ])->groupBy('module');

        // Transformar a estructura ligera para JS: { modulo: [{id, name, action, description}] }
        $permMap = [];
        foreach ($perms as $module => $rows) {
            $permMap[$module ?: 'otros'] = $rows->map(function($p){
                $module = $p->module ?: \Illuminate\Support\Str::before($p->name, '.');
                $action = \Illuminate\Support\Str::after($p->name, $module.'.');
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'module' => $module,
                    'action' => $action,
                    'description' => $p->description,
                ];
            })->values();
        }

        $modules = config('modules');

        return view('roles.index', [
            'modules' => $modules,
            'permMap' => $permMap,
        ]);
    }

    public function create()
    {
        // Permisos agrupados por módulo para la grilla
        $perms = Permission::orderBy('module')->orderBy('name')->get(['id','name','module','description'])
            ->groupBy('module');

        // Etiquetas de módulos para títulos bonitos
        $modules = config('modules');

        return view('roles.create', compact('perms','modules'));
    }

    // Tabla paginada + filtro por nombre
    public function listas(Request $request)
    {
        $qName   = trim($request->get('name', ''));
        $page    = max(1, (int)$request->get('page', 1));
        $perPage = min(50, max(5, (int)$request->get('per_page', 20)));

        $query = Role::query()->orderBy('name');

        if ($qName !== '') {
            $query->where('name', 'like', "%{$qName}%");
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $tbody = view('roles.partials.table-body', [
            'items' => $paginator->items(),
        ])->render();

        $pager = view('roles.partials.pagination', [
            'paginator' => $paginator,
        ])->render();

        return response()->json([
            'ok'   => true,
            'html' => ['tbody' => $tbody, 'pager' => $pager],
            'meta' => ['total' => $paginator->total(), 'page' => $paginator->currentPage()],
        ]);
    }

    // IDs de permisos del rol (para editar)
    public function perms(Role $role)
    {
        return response()->json([
            'ok'  => true,
            'ids' => $role->permissions()->pluck('id')->all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:80','unique:roles,name'],
            //'description'     => ['nullable','string','max:255'],          // solo si tienes esta columna en roles
            'permissions'     => ['array'],
            'permissions.*'   => ['integer','exists:permissions,id'],
        ]);

        // Crear rol
        $role = Role::create([
            'name'       => $data['name'],
            'guard_name' => 'web',
            // si agregaste columna description en la tabla roles:
            //'description' => $data['description'] ?? null,
        ]);

        // Asignar permisos
        $role->syncPermissions($data['permissions'] ?? []);

        // Si es AJAX (nuestro caso desde la vista de crear), devolver JSON con redirect
        if ($request->ajax()) {
            return response()->json([
                'ok'           => true,
                'msg'          => 'Rol creado correctamente.',
                'redirect_url' => route('roles.index'),
                // opcional: si quieres seguir usando la tabla por AJAX en otro flujo
                // 'html'         => view('roles.partials.table-row', ['r' => $role])->render(),
            ], 201);
        }

        // Fallback no-AJAX
        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        // permisos agrupados por módulo
        $perms = Permission::orderBy('module')->orderBy('name')
            ->get(['id','name','module','description'])
            ->groupBy('module');

        // ids seleccionados del rol
        $selectedIds = $role->permissions()->pluck('id')->all();

        $modules = config('modules');

        return view('roles.edit', compact('role','perms','modules','selectedIds'));
    }

    public function update(Request $request, Role $role)
    {

        $data = $request->validate([
            'name'          => ['required','string','max:80', Rule::unique('roles','name')->ignore($role->id)],
            'permissions'   => ['array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        if ($request->ajax()) {
            return response()->json([
                'ok'  => true,
                'msg' => 'Rol actualizado correctamente.',
                'role'=> $role->only(['id','name'])
            ]);
        }

        // fallback si alguien envía el form normal
        return redirect()
            ->route('roles.index')
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role)
    {
        $id = $role->id;
        $role->delete();

        return response()->json(['ok'=>true,'msg'=>'Rol eliminado.','id'=>$id]);
    }
}
