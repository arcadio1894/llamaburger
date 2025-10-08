<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $modules = config('modules'); // ['salas'=>'Salas', ...]
        return view('permissions.index', compact('modules'));
    }

    // AJAX: lista paginada con filtros
    public function listar(Request $request)
    {
        $qName   = trim($request->get('name', ''));
        $qModule = trim($request->get('module', ''));
        $page    = max(1, (int)$request->get('page', 1));
        $perPage = min(50, max(5, (int)$request->get('per_page', 20)));

        $query = Permission::query()->orderBy('module')->orderBy('name');

        if ($qName !== '') {
            $query->where('name', 'like', "%{$qName}%");
        }
        if ($qModule !== '') {
            $query->where('module', $qModule);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $tbody = view('permissions.partials.table-body', [
            'items' => $paginator->items(),
        ])->render();

        $pager = view('permissions.partials.pagination', [
            'paginator' => $paginator,
        ])->render();

        return response()->json([
            'ok'   => true,
            'html' => [
                'tbody' => $tbody,
                'pager' => $pager,
            ],
            'meta' => [
                'total' => $paginator->total(),
                'page'  => $paginator->currentPage(),
            ],
        ]);
    }

    // AJAX: crear permiso
    public function store(Request $request)
    {
        $data = $request->validate([
            'module'      => ['required', Rule::in(array_keys(config('modules')))],
            'action'      => ['required', 'string', 'max:40'],  // p.e. create, view, update...
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $name = "{$data['module']}.{$data['action']}";

        $permission = Permission::firstOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            ['module' => $data['module'], 'description' => $data['description'] ?? null]
        );

        // Fila renderizada para inyectar
        $rowHtml = view('permissions.partials.table-row', ['p' => $permission])->render();

        return response()->json([
            'ok'   => true,
            'msg'  => 'Permiso creado correctamente.',
            'html' => $rowHtml,
        ], 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'module'      => ['required', Rule::in(array_keys(config('modules')))],
            'action'      => ['required', 'string', 'max:40'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $newName = "{$data['module']}.{$data['action']}";

        // validar unicidad del name excepto el actual
        $exists = Permission::where('name', $newName)
            ->where('id', '!=', $permission->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'ok'  => false,
                'msg' => "Ya existe el permiso {$newName}."
            ], 422);
        }

        $permission->update([
            'name'        => $newName,
            'module'      => $data['module'],
            'description' => $data['description'] ?? null,
            'guard_name'  => 'web',
        ]);

        $rowHtml = view('permissions.partials.table-row', ['p' => $permission->fresh()])->render();

        return response()->json([
            'ok'   => true,
            'msg'  => 'Permiso actualizado.',
            'html' => $rowHtml,
        ]);
    }

    public function destroy(Permission $permission)
    {
        // Spatie borra y limpia pivotes (role_has_permissions / model_has_permissions)
        $id = $permission->id;
        $permission->delete();

        return response()->json([
            'ok'  => true,
            'msg' => 'Permiso eliminado.',
            'id'  => $id,
        ]);
    }
}
