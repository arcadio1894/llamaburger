<?php

namespace App\Http\Controllers;

use App\Models\Mozo;
use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalaController extends Controller
{
    public function __construct()
    {
        // Si ya estás usando spatie/permission, habilita los permisos:
        /*$this->middleware('permission:listSala')->only(['index']);
        $this->middleware('permission:createSala')->only(['store']);
        $this->middleware('permission:editSala')->only(['update']);
        $this->middleware('permission:destroySala')->only(['destroy']);
        $this->middleware('permission:restoreSala')->only(['restore']);*/
    }

    /**
     * GET /salas  (vista principal)
     * Muestra la vista con todas las salas y por defecto las mesas de la primera sala.
     */
    public function index()
    {
        // Trae salas (incluye borradas si quieres mostrarlas en una pestaña aparte)
        $salas = Sala::orderBy('id')->get();

        $firstSala = $salas->first();

        // Si ya tienes el modelo Mesa y relación $sala->mesas()
        $mesas = collect();
        if ($firstSala && method_exists($firstSala, 'mesas')) {
            $mesas = $firstSala->mesas()->orderBy('id')->get();
        }

        // Mozos activos para el combo
        $mozos = Mozo::where('activo', 1)->orderBy('nombre')->get(['id','nombre','user_id']);

        // Si el usuario logueado es un mozo, obtener su id de mozo
        $currentMozo = null;
        if (auth()->check()) {
            $currentMozo = Mozo::where('user_id', auth()->id())
                ->where('activo',1)->first();
        }

        // Renderiza la vista normal (desde aquí todo lo demás será por AJAX con modales)
        return view('sala.index', [
            'salas'     => $salas,
            'firstSala' => $firstSala,
            'mesas'     => $mesas,
            'mozos'        => $mozos,
            'currentMozo'  => $currentMozo, // <- importante
        ]);


    }

    public function config(Request $request)
    {
        // Trae TODAS (incluye soft-deleted para mantener chips grises en lista)
        $salas = Sala::withTrashed()->orderBy('id')->get();

        // Si pidieron una sala específica por query (?sala_id=)
        $selectedId = $request->get('sala_id');

        if ($selectedId) {
            // Puede ser eliminada, así que withTrashed
            $firstSala = Sala::withTrashed()->find($selectedId);
        } else {
            // Preferir una sala activa; si no hay, usar la primera (aunque esté eliminada)
            $firstSala = $salas->firstWhere('deleted_at', null) ?? $salas->first();
        }

        // Mesas de la sala seleccionada (si tienes la relación definida)
        $mesas = collect();
        if ($firstSala && method_exists($firstSala, 'mesas')) {
            $mesas = $firstSala->mesas()->orderBy('id')->get();
        }

        return view('sala.config', [
            'salas'     => $salas,
            'firstSala' => $firstSala, // puede ser eliminada; el chip debe marcarse .deleted
            'mesas'     => $mesas,
        ]);
    }

    /**
     * POST /salas  (crear sala vía AJAX)
     * Espera: { nombre, descripcion }
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => [
                'required','string','max:120',
                Rule::unique('salas','nombre')->whereNull('deleted_at'),
            ],
            'descripcion' => ['nullable','string','max:500'],
        ]);

        try {
            $preCount = Sala::count(); // ¿hay salas antes de crear?

            $sala = null;
            DB::transaction(function () use (&$sala, $data) {
                $sala = Sala::create($data);
            });

            // Si es la primera sala, la dejamos seleccionada (active)
            $isFirst = ($preCount === 0);

            // Render del CHIP de sala (no "button")
            $chipHtml = view('sala.partials.sala-chip', [
                'sala'   => $sala,
                'active' => $isFirst, // si es la primera, aparece activa
            ])->render();

            // Si es la primera, también devuelve el grid de mesas vacío
            $mesasHtml = null;
            if ($isFirst) {
                $mesasHtml = view('sala.partials.mesas-grid', [
                    'mesas' => collect(),
                ])->render();
            }

            return response()->json([
                'ok'        => true,
                'msg'       => 'Sala creada correctamente.',
                'sala'      => $sala,
                'html'      => $chipHtml,   // el chip para append/reemplazar
                'setActive' => $isFirst,    // bandera para el front
                'mesasHtml' => $mesasHtml,  // solo cuando es la primera
            ], 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok'  => false,
                'msg' => 'No se pudo crear la sala.',
            ], 500);
        }
    }

    public function configMesas($salaId)
    {
        $sala = Sala::find($salaId);
        if (!$sala) {
            return response()->json(['ok' => false, 'msg' => 'Sala no encontrada.'], 404);
        }

        $mesas = method_exists($sala, 'mesas')
            ? $sala->mesas()->orderBy('id')->get()
            : collect();

        $html = view('sala.partials.mesas-grid', compact('mesas'))->render();

        return response()->json([
            'ok'   => true,
            'html' => $html,
            'sala' => [
                'id'     => $sala->id,
                'nombre' => $sala->nombre,
            ],
        ]);
    }

    /**
     * POST /salas/update  (actualizar sala vía AJAX)
     * OJO: tu ruta usa {category}; lo hago tolerante: toma el ID de la ruta o del body.
     * Espera: { nombre, descripcion }
     */
    public function update(Request $request, $sala = null)
    {
        // ID desde la ruta o desde el payload
        $id = $sala ?? $request->input('id');

        // Si no mandan ID, 400
        if (!$id) {
            return response()->json(['ok' => false, 'msg' => 'ID requerido.'], 400);
        }

        $salaModel = Sala::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'nombre' => [
                'required', 'string', 'max:120',
                Rule::unique('salas', 'nombre')
                    ->ignore($salaModel->id)          // permitir el mismo nombre en el mismo id
                    ->whereNull('deleted_at'),         // pero no duplicar con registros activos
            ],
            'descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            DB::transaction(function () use ($salaModel, $data) {
                $salaModel->update($data);
            });

            // Render del CHIP actualizado (sin forzar active)
            $chipHtml = view('sala.partials.sala-chip', [
                'sala'   => $salaModel->fresh(),
                'active' => false, // el front decide si conservar .active
            ])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Sala actualizada correctamente.',
                'sala' => $salaModel->fresh(),
                'html' => $chipHtml,
            ], 200);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok'  => false,
                'msg' => 'No se pudo actualizar la sala.',
            ], 500);
        }
    }

    /**
     * POST /salas/destroy  (soft delete vía AJAX)
     * Espera: { id }
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => ['required','integer','exists:salas,id'],
        ]);

        $sala = Sala::findOrFail($request->id);

        //dd($sala);

        try {
            DB::transaction(function () use ($sala) {
                $sala->delete(); // soft delete
            });

            // Render del chip “eliminado”
            $chipHtml = view('sala.partials.sala-chip', [
                'sala'   => $sala,     // ya contiene deleted_at
                'active' => false,
            ])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Sala eliminada (lógicamente).',
                'id'   => (int) $request->id,
                'html' => $chipHtml,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo eliminar la sala.'], 500);
        }
    }

    /**
     * POST /salas/restore  (restaurar soft delete vía AJAX)
     * Espera: { id }
     */
    public function restore(Request $request)
    {
        $request->validate([
            'id' => ['required','integer'],
        ]);

        $sala = Sala::withTrashed()->findOrFail($request->id);

        try {
            DB::transaction(function () use ($sala) {
                $sala->restore();
            });

            $chipHtml = view('sala.partials.sala-chip', [
                'sala'   => $sala->fresh(),
                'active' => false,
            ])->render();

            return response()->json([
                'ok'   => true,
                'msg'  => 'Sala restaurada correctamente.',
                'sala' => $sala->fresh(),
                'html' => $chipHtml,
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo restaurar la sala.'], 500);
        }
    }

    public function mesasMozo($salaId)
    {
        // Acepta también salas soft-deleted si quieres, pero para mozo usualmente solo activas
        $sala = Sala::whereNull('deleted_at')->findOrFail($salaId);

        $mesas = method_exists($sala, 'mesas')
            ? $sala->mesas()->whereNull('deleted_at')->orderBy('id')->get()
            : collect();

        $html = view('sala.partials.mesas-list', compact('mesas'))->render();

        return response()->json([
            'ok'   => true,
            'html' => $html,
            'sala' => ['id' => $sala->id, 'nombre' => $sala->nombre],
        ]);
    }
}
