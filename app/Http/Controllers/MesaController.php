<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MesaController extends Controller
{
    public function __construct()
    {
        // Si usas spatie/permission, después mapeas:
        // $this->middleware('permission:createMesa')->only(['store']);
        // $this->middleware('permission:editMesa')->only(['update']);
        // $this->middleware('permission:destroyMesa')->only(['destroy']);
        // $this->middleware('permission:restoreMesa')->only(['restore']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'sala_id'     => [
                'required', 'integer',
                // existe y NO está soft-deleted
                //Rule::exists('salas','id')->whereNull('deleted_at'),
            ],
            'nombre'      => [
                'required','string','max:120',
                // único por sala, ignorando eliminados lógicos vía regla custom abajo
            ],
            'estado'      => ['nullable', Rule::in(['libre','ocupada'])],
            'descripcion' => ['nullable','string','max:500'],
        ]);

        // Validación de único por sala + soft deletes:
        $exists = Mesa::withTrashed()
            ->where('sala_id', $data['sala_id'])
            ->where('nombre',  $data['nombre'])
            ->whereNull('deleted_at')   // chocaría con una activa
            ->exists();

        if ($exists) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Ya existe una mesa con ese nombre en la sala seleccionada.',
            ], 422);
        }

        try {
            $mesa = null;
            DB::transaction(function () use (&$mesa, $data) {
                if (empty($data['estado'])) $data['estado'] = 'libre';
                $mesa = Mesa::create($data);
            });

            $chipHtml = view('sala.partials.mesa-chip', ['mesa' => $mesa])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Mesa creada correctamente.',
                'mesa' => $mesa,
                'html' => $chipHtml,
            ], 201);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo crear la mesa.'], 500);
        }
    }

    public function update(Request $request, $mesa = null)
    {
        $id = $mesa ?? $request->input('id');
        if (!$id) {
            return response()->json(['ok'=>false,'msg'=>'ID requerido.'], 400);
        }

        $mesaModel = Mesa::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'nombre'      => ['required','string','max:120'],
            'estado'      => ['nullable', Rule::in(['libre','ocupada','cerrada'])],
            'descripcion' => ['nullable','string','max:500'],
        ]);

        // Único por sala (ignorando la propia mesa y respetando soft deletes en activos)
        $dup = Mesa::withTrashed()
            ->where('sala_id', $mesaModel->sala_id)
            ->where('nombre',  $data['nombre'])
            ->where('id', '!=', $mesaModel->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($dup) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Ya existe otra mesa con ese nombre en esta sala.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($mesaModel, $data) {
                if (empty($data['estado'])) unset($data['estado']); // no forzar si no vino
                $mesaModel->update($data);
            });

            $chipHtml = view('sala.partials.mesa-chip', ['mesa' => $mesaModel->fresh()])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Mesa actualizada correctamente.',
                'mesa' => $mesaModel->fresh(),
                'html' => $chipHtml,
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo actualizar la mesa.'], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'id' => ['required','integer','exists:mesas,id'],
        ]);

        $mesa = Mesa::findOrFail($request->id);

        try {
            DB::transaction(function () use ($mesa) {
                $mesa->delete(); // soft delete
            });

            // el modelo en memoria ya tiene deleted_at
            $chipHtml = view('sala.partials.mesa-chip', ['mesa' => $mesa])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Mesa eliminada (lógicamente).',
                'id'   => (int) $request->id,
                'html' => $chipHtml,
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo eliminar la mesa.'], 500);
        }
    }

    public function restore(Request $request)
    {
        $request->validate([
            'id' => ['required','integer'],
        ]);

        $mesa = Mesa::withTrashed()->findOrFail($request->id);

        try {
            DB::transaction(function () use ($mesa) {
                $mesa->restore();
            });

            $chipHtml = view('sala.partials.mesa-chip', ['mesa' => $mesa->fresh()])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Mesa restaurada correctamente.',
                'mesa' => $mesa->fresh(),
                'html' => $chipHtml,
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo restaurar la mesa.'], 500);
        }
    }
}
