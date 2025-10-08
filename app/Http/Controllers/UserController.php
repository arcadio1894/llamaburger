<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mozo;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        // Para el modal: lista de roles disponibles (por nombre y etiqueta)
        $roles = Role::orderBy('name')->get(['id','name']);
        return view('users.index', compact('roles'));
    }

    // (opcional) si luego quieres paginar/filtrar
    public function listar(Request $request)
    {
        $q   = trim($request->get('q',''));
        $per = (int)($request->get('per_page', 20));
        $per = max(5, min(50, $per));

        $query = User::query()->withTrashed()->orderBy('id','desc'); // 👈 incluye eliminados

        if ($q !== '') {
            $query->where(function($w) use ($q){
                $w->where('name','like',"%{$q}%")
                    ->orWhere('email','like',"%{$q}%");
            });
        }

        $p = $query->paginate($per);

        $tbody = view('users.partials.table-body', ['items' => $p->items()])->render();
        $pager = view('users.partials.pagination',  ['paginator' => $p])->render();

        return response()->json(['ok'=>true,'html'=>compact('tbody','pager')]);
    }

    public function store(Request $request)
    {
        // Validación + mensajes personalizados
        $v = Validator::make(
            $request->all(),
            [
                'name'        => ['required','string','max:120'],
                'email'       => ['required','email','max:150','unique:users,email'],
                'roles'       => ['required','array','min:1'],
                'roles.*'     => ['string', Rule::exists('roles','name')],
                // phone requerido SOLO si se eligió el rol 'distribuidor'
                'phone'       => [
                    Rule::requiredIf(function() use ($request) {
                        $roles = (array)$request->input('roles', []);
                        return in_array('distribuidor', $roles, true);
                    }),
                    'nullable','string','max:30'
                ],
            ],
            [
                'name.required'        => 'El nombre es obligatorio.',
                'email.required'       => 'El email es obligatorio.',
                'email.email'          => 'El formato de email no es válido.',
                'email.unique'         => 'El email ya está registrado por otro usuario.',
                'roles.required'       => 'Debes seleccionar al menos un rol.',
                'roles.array'          => 'El formato de los roles es inválido.',
                'roles.*.exists'       => 'Alguno de los roles seleccionados no existe.',
                'phone.required'       => 'El teléfono es obligatorio para distribuidores.',
                'phone.max'            => 'El teléfono no debe exceder 30 caracteres.',
            ]
        );

        if ($v->fails()) {
            // Importante: responder 422 con el formato estándar de Laravel (message + errors)
            return response()->json([
                'ok'     => false,
                'message'=> 'Revisa los datos ingresados.',
                'errors' => $v->errors(),
            ], 422);
        }

        $data       = $v->validated();
        $isMozo     = in_array('mozo', (array)$data['roles'], true);
        $isDistrib  = in_array('distribuidor', (array)$data['roles'], true);
        $tempPassword = 'password'; // o Str::random(10)

        try {
            $user = null;
            DB::transaction(function () use (&$user, $data, $tempPassword, $isMozo, $isDistrib) {
                $user = User::create([
                    'name'     => $data['name'],
                    'email'    => $data['email'],
                    'password' => Hash::make($tempPassword),
                ]);

                $user->syncRoles($data['roles']);

                if ($isMozo && !Mozo::where('user_id', $user->id)->exists()) {
                    Mozo::create([
                        'user_id' => $user->id,
                        'nombre'  => $user->name,
                        'activo'  => 1,
                    ]);
                }

                if ($isDistrib && !Distributor::where('user_id', $user->id)->exists()) {
                    Distributor::create([
                        'user_id' => $user->id,
                        'name'    => $user->name,
                        'phone'   => $data['phone'] ?? null,
                        'activo'  => 1,
                    ]);
                }
            });

            $rowHtml = view('users.partials.table-row', ['u' => $user])->render();

            return response()->json([
                'ok'            => true,
                'msg'           => 'Usuario creado correctamente.',
                'html'          => $rowHtml,
                'temp_password' => $tempPassword,
            ], 201);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok'  => false,
                'msg' => 'No se pudo crear el usuario.',
            ], 500);
        }
    }

    public function showJson(User $user)
    {
        $roles = $user->roles()->pluck('name')->all();
        $dist  = Distributor::where('user_id', $user->id)->first();

        return response()->json([
            'ok'   => true,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'roles' => $roles,
                'phone' => optional($dist)->phone, // ✅ compatible
                'is_distribuidor' => in_array('distribuidor', $roles, true),
                'is_mozo'         => in_array('mozo', $roles, true),
            ]
        ]);
    }

    public function update(Request $request, User $user)
    {
        // Validación con mensajes claros
        $v = Validator::make(
            $request->all(),
            [
                'name'      => ['required','string','max:120'],
                'email'     => ['required','email','max:150', Rule::unique('users','email')->ignore($user->id)],
                'roles'     => ['required','array','min:1'],
                'roles.*'   => ['string', Rule::exists('roles','name')],
                'phone'     => [
                    Rule::requiredIf(function() use ($request){
                        $roles = (array)$request->input('roles', []);
                        return in_array('distribuidor', $roles, true);
                    }),
                    'nullable','string','max:30'
                ],
            ],
            [
                'name.required'      => 'El nombre es obligatorio.',
                'email.required'     => 'El email es obligatorio.',
                'email.email'        => 'El formato de email no es válido.',
                'email.unique'       => 'El email ya está registrado por otro usuario.',
                'roles.required'     => 'Selecciona al menos un rol.',
                'roles.*.exists'     => 'Alguno de los roles seleccionados no existe.',
                'phone.required'     => 'El teléfono es obligatorio para distribuidores.',
            ]
        );

        if ($v->fails()) {
            return response()->json([
                'ok' => false, 'message' => 'Revisa los datos ingresados.', 'errors' => $v->errors()
            ], 422);
        }

        $data       = $v->validated();
        $isMozo     = in_array('mozo', (array)$data['roles'], true);
        $isDistrib  = in_array('distribuidor', (array)$data['roles'], true);

        try {
            DB::transaction(function () use ($user, $data, $isMozo, $isDistrib) {
                // actualizar usuario
                $user->update([
                    'name'  => $data['name'],
                    'email' => $data['email'],
                ]);

                // sincronizar roles
                $user->syncRoles($data['roles']);

                // MOZO: crear/activar o desactivar
                $mozo = Mozo::where('user_id', $user->id)->first();
                if ($isMozo) {
                    if (!$mozo) {
                        Mozo::create(['user_id'=>$user->id,'nombre'=>$user->name,'activo'=>1]);
                    } else {
                        $mozo->update(['nombre'=>$user->name, 'activo'=>1]);
                    }
                } else {
                    if ($mozo) { $mozo->update(['activo'=>0]); }
                }

                // DISTRIBUIDOR: crear/actualizar o desactivar
                $dist = Distributor::where('user_id', $user->id)->first();
                if ($isDistrib) {
                    if (!$dist) {
                        Distributor::create([
                            'user_id'=>$user->id, 'name'=>$user->name,
                            'phone'=>$data['phone'] ?? null, 'activo'=>1,
                        ]);
                    } else {
                        $dist->update([
                            'name'=>$user->name, 'phone'=>$data['phone'] ?? null, 'activo'=>1,
                        ]);
                    }
                } else {
                    if ($dist) { $dist->update(['activo'=>0]); }
                }
            });

            // devolver la fila actualizada (si quieres reemplazar en la tabla)
            $rowHtml = view('users.partials.table-row', ['u' => $user->fresh()])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Usuario actualizado correctamente.',
                'html' => $rowHtml,
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo actualizar el usuario.'], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            DB::transaction(function () use ($user) {
                $user->delete(); // soft delete
                Mozo::where('user_id', $user->id)->update(['activo'=>0]);
                Distributor::where('user_id', $user->id)->update(['activo'=>0]);
            });

            // Re-render de la fila, marcada como eliminado
            $rowHtml = view('users.partials.table-row', ['u' => $user->fresh()])->render();

            return response()->json(['ok'=>true,'msg'=>'Usuario eliminado.','html'=>$rowHtml]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo eliminar el usuario.'], 500);
        }
    }

    public function restore($id)
    {
        try {
            $user = User::withTrashed()->findOrFail($id);

            DB::transaction(function () use ($user) {
                $user->restore();

                // Reactivar mozo/distribuidor si existen
                Mozo::where('user_id', $user->id)->update(['activo'=>1]);
                Distributor::where('user_id', $user->id)->update(['activo'=>1]);
            });

            $rowHtml = view('users.partials.table-row', ['u' => $user->fresh()])->render();

            return response()->json(['ok'=>true,'msg'=>'Usuario restaurado.','html'=>$rowHtml]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo restaurar el usuario.'], 500);
        }
    }
}
