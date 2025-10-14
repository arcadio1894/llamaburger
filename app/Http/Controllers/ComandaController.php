<?php

namespace App\Http\Controllers;

use App\Events\ComandaCreated;
use App\Models\Atencion;
use App\Models\Comanda;
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
            ->whereIn('estado', ['enviada','cocinando','servida'])
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
            ];
        })->values();

        return response()->json([
            'ok'      => true,
            'tickets' => $tickets,
        ]);
    }

    private function mapComandaToKanban($status)
    {
        // Mapea tu estado → columna del kanban
        switch (trim(strtolower($status))) {
            case 'enviada':    return 'created';    // Recibido
            case 'cocinando':  return 'processing'; // Cocinando
            case 'servida':    return 'shipped';    // En Trayecto (listo para recoger)
            default:           return 'created';
        }
    }
}
