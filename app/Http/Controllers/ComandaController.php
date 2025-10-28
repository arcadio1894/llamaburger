<?php

namespace App\Http\Controllers;

use App\Events\ComandaCreated;
use App\Events\ComandaStatusUpdated;
use App\Models\Atencion;
use App\Models\Comanda;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComandaController extends Controller
{
    public function getOrCreateBorrador(Request $request, Atencion $atencion)
    {
        // podrías validar permisos del mozo aquí
        $comanda = $atencion->comandas()
            ->where('estado','borrador')
            ->orderBy('numero','desc')
            ->first();

        if (!$comanda) {
            // siguiente número
            $nextN = (int) ($atencion->comandas()->max('numero') ?? 0) + 1;
            $comanda = DB::transaction(function() use ($atencion, $nextN) {
                return Comanda::create([
                    'atencion_id' => $atencion->id,
                    'numero'      => $nextN,
                    'estado'      => 'borrador',
                ]);
            });
        }

        return response()->json(['ok'=>true, 'comanda'=>$comanda]);
    }

    public function createNext(Request $request, Atencion $atencion)
    {
        $nextN = (int) ($atencion->comandas()->max('numero') ?? 0) + 1;

        $comanda = DB::transaction(function() use ($atencion, $nextN) {
            return Comanda::create([
                'atencion_id' => $atencion->id,
                'numero'      => $nextN,
                'estado'      => 'borrador',
            ]);
        });

        // Si quieres JSON (AJAX) devuelve JSON; si la crearás con botón normal, redirige:
        if ($request->wantsJson()) {
            return response()->json(['ok'=>true, 'comanda'=>$comanda], 201);
        }

        return redirect()->route('atenciones.comanda.show', [$atencion->id, $comanda->numero])
            ->with('success', "Comanda #{$comanda->numero} creada.");
    }

    public function send(Request $request, Comanda $comanda)
    {
        // No reenviar si ya fue enviada
        if (in_array($comanda->estado, ['enviada','cocinando','servida'])) {
            return response()->json([
                'ok' => false,
                'msg' => 'Esta comanda ya fue enviada a cocina.'
            ]);
        }

        // marca interna opcional
        $comanda->update([
            'estado' => 'enviada',            // si usas un campo; si no, omite
            'sent_to_kitchen_at' => now(),    // si lo tienes; si no, omite
        ]);

        broadcast(new ComandaCreated($comanda));

        return response()->json(['ok' => true, 'msg' => 'Comanda enviada a cocina.']);
    }

    public function openTickets()
    {
        // Ajusta si tus nombres de estado exactos difieren
        // Estados de comanda: 'borrador' (no va a kanban), 'enviada', 'cocinando', 'servida'
        $comandas = Comanda::with(['atencion.mesa','atencion.mozo'])
            ->whereIn('estado', ['enviada','cocinando','lista'])
            ->orderBy('id','desc')
            ->get();

        $tickets = $comandas->map(function($c){
            return [
                'id'          => 'comanda_' . $c->id,     // ID único para el kanban
                'comanda_id'  => (int)$c->id,
                'numero'      => (int)$c->numero,
                'mesa'        => optional($c->atencion->mesa)->nombre,
                'mozo'        => optional($c->atencion->mozo)->nombre,
                'total'       => (float)$c->total,
                'items'       => (int)($c->items()->count()),
                'status'      => $this->mapComandaToKanban($c->estado), // created/processing/shipped
                'estimated_ready_at' => $c->estimated_ready_at,
                'estimated_minutes' => $c->estimated_minutes,
            ];
        })->values();

        return response()->json([
            'ok'      => true,
            'tickets' => $tickets,
        ]);
    }

    private function mapComandaToKanban($status)
    {
        // Normalizamos el valor recibido
        $s = trim(strtolower($status));

        switch ($s) {
            case 'enviada':
                return 'created';      // 📥 Recibido en cocina
            case 'cocinando':
                return 'processing';   // 🔥 En preparación
            case 'lista':
                return 'shipped';      // 🍽️ Listo para entregar
            case 'servida':
                return 'completed';    // ✅ Entregado al cliente (fuera del kanban)
            case 'cancelada':
                return 'cancelled';    // ❌ Pedido cancelado (fuera del kanban)
            default:
                return 'created';      // Valor por defecto
        }
    }

    public function show(Comanda $comanda)
    {
        // agrega relaciones si existen: mesa, mozo, etc.
        return response()->json([
            'id'    => $comanda->id,
            'numero'=> $comanda->numero,
            'mesa'  => optional($comanda->atencion->mesa)->nombre,
            'mozo'  => optional($comanda->atencion->mozo)->nombre,
            'total' => $comanda->total,
            'status'=> $this->mapComandaToKanban($comanda->estado),
            'started_cooking_at' => $comanda->started_cooking_at,
            'estimated_minutes'  => $comanda->estimated_minutes,
            'estimated_ready_at' => $comanda->estimated_ready_at,
            'ready_at'           => $comanda->ready_at,
            'delivered_at'       => $comanda->delivered_at,
        ]);
    }

    public function start(Request $req, Comanda $comanda)
    {
        $req->validate([
            'estimated_minutes' => 'required|integer|min:1|max:600',
        ]);

        $start = now('America/Lima');
        $eta = (clone $start)->addMinutes((int)$req->input('estimated_minutes'));

        $comanda->update([
            'estado' => 'cocinando',
            'started_cooking_at' => $start,
            'estimated_minutes' => (int)$req->input('estimated_minutes'),
            'estimated_ready_at' => $eta,
        ]);

        // 🔥 EMITIR EVENTO (repinta en Kanban)
        broadcast(new ComandaStatusUpdated($comanda));

        return response()->json([
            'ok' => true,
            'estimated_ready_at' => $eta->toDateTimeString(),
        ]);
    }

    public function ready(Comanda $comanda)
    {
        $comanda->update([
            'estado'   => 'lista',                              // Kanban → "Listo"
            'ready_at' => Carbon::now('America/Lima'),
        ]);

        // 🔔 Emitir evento para repintar en el Kanban (mismo canal que .comanda.created)
        broadcast(new ComandaStatusUpdated($comanda));

        return response()->json([
            'ok' => true,
            'id' => $comanda->id,
            'estado' => $comanda->estado,
            'ready_at' => optional($comanda->ready_at)->toDateTimeString(),
        ]);
    }

    public function deliver(Comanda $comanda)
    {
        $comanda->update([
            'estado'       => 'servida',
            'delivered_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }
}
