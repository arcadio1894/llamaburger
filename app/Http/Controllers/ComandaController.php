<?php

namespace App\Http\Controllers;

use App\Events\ComandaCanceled;
use App\Events\ComandaCreated;
use App\Events\ComandaReadyForPickup;
use App\Events\ComandaStatusUpdated;
use App\Models\Agent;
use App\Models\Atencion;
use App\Models\Comanda;
use App\Models\DataGeneral;
use App\Models\Invoice;
use App\Models\Mozo;
use App\Models\PrintJob;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;


class ComandaController extends Controller
{
    protected function authorizeComanda(Comanda $comanda)
    {
        if ( $comanda->tipo == "mesa" ) {
            if (auth()->check()) {
                $mozo = Mozo::where('user_id', auth()->id())->where('activo', 1)->first();
                if ($mozo && $comanda->atencion->mozo_id !== $mozo->id) {
                    abort(403, 'Acceso restringido.');
                }
            }
        }

    }

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
        // Verifica si hay alguna comanda 'borrador' (no enviada aún)
        $tieneBorrador = $atencion->comandas()
            ->where('estado', 'borrador')
            ->exists();

        if ($tieneBorrador) {
            return back()
                ->with('warning', 'No puedes crear una nueva comanda mientras exista una en borrador.');
        }

        $nextN = (int) ($atencion->comandas()->max('numero') ?? 0) + 1;

        $comanda = DB::transaction(function() use ($atencion, $nextN) {
            return Comanda::create([
                'atencion_id' => $atencion->id,
                'numero'      => $nextN,
                'estado'      => 'borrador',
            ]);
        });

        return redirect()
            ->route('atenciones.comanda.show', [$atencion->id, $comanda->numero])
            ->with('success', "Comanda #{$comanda->numero} creada correctamente.");
    }

    public function send(Request $request, Comanda $comanda)
    {
        $tenantCurrent = env('TENANT_ID');
        $tenant = Tenant::where('name', $tenantCurrent)->first();
        $agent = Agent::where('tenant_id', $tenant->id)->first();

        // No reenviar si ya fue enviada
        if (in_array($comanda->estado, ['enviada','cocinando','servida', 'lista', 'cancelada'])) {
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

        $job = PrintJob::create([
            'id'           => (string) Str::uuid(),
            'tenant_id'    => $tenant->id,
            'agent_id'     => $agent->id,
            'comanda_id'   => $comanda->id,
            'printer_name' => $agent->name,
            'content'      => "",  // {header, lines, cut:true}
            'status'       => 'queued',
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
            'estado'   => 'lista',
            'ready_at' => Carbon::now('America/Lima'),
        ]);

        // sigue tu evento general
        broadcast(new ComandaStatusUpdated($comanda));

        // evento dirigido al mozo
        $mozoId = optional($comanda->atencion)->mozo_id; // <— aquí
        $mozo = Mozo::find($mozoId);
        if ($mozoId) {
            broadcast(new ComandaReadyForPickup($comanda, $mozo->user_id));
        }

        return response()->json([
            'ok'       => true,
            'id'       => $comanda->id,
            'estado'   => $comanda->estado,
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

    public function ticketVenta($invoiceId)
    {
        // Trae invoice con sus items
        $invoice = Invoice::with(['items'])->findOrFail($invoiceId);

        // IGV por defecto 18%, pero si lo guardas en BD úsalo
        $dataIGV = DataGeneral::where('name', 'igv_rate')->first();
        $igvNumber = $dataIGV->valueNumber;
        $igvRate = round($igvNumber / 100, 2);


        // Subtotal = suma(cantidad * valor_unitario [SIN IGV])
        $subtotal = $invoice->items->sum(function ($it) {
            return (float)$it->cantidad * (float)$it->valor_unitario;
        });

        // Descuento global del comprobante (resta a la base)
        $descuento = (float)($invoice->descuento ?? 0.0);

        // Base imponible = subtotal - descuento (nunca negativa)
        $base = max($subtotal - $descuento, 0);

        // IGV = base * igvRate
        $igv = round($base * $igvRate, 2);

        // Propina (no gravada). Tomada de op_inafecta según tu modelo de datos
        $propina = (float)($invoice->op_inafecta ?? 0.0);

        // Total a pagar = base + igv + propina
        $total = round($base + $igv + $propina, 2);

        $totals = [
            'subtotal'  => round($subtotal, 2),
            'descuento' => round($descuento, 2),
            'base'      => round($base, 2),
            'igv'       => round($igv, 2),
            'propina'   => round($propina, 2),
            'total'     => round($total, 2),
            'igvRate'   => $igvRate,
        ];

        // Renderiza el PDF (usa tu vista abajo)
        $pdf = Pdf::loadView('comanda.ticket-venta', compact('invoice', 'totals'))
            ->setPaper([0, 0, 226.8, 900], 'portrait');

        // Descarga o muestra en navegador
        return $pdf->stream("ticket-venta-{$invoice->id}.pdf");
        // return $pdf->stream();
    }

    public function cancel(Request $request, Comanda $comanda)
    {
        $this->authorizeComanda($comanda);

        if ($comanda->estado === 'cancelada') {
            return response()->json(['ok' => true, 'id' => $comanda->id, 'estado' => 'cancelada']);
        }

        if ($comanda->estado === 'servida') {
            return response()->json(['ok' => false, 'msg' => 'No puedes cancelar una comanda servida.'], 422);
        }

        DB::transaction(function () use ($comanda) {
            $comanda->update(['estado' => 'cancelada']);

            $comanda->update([
                'sent_to_kitchen_at' => null,
                'started_cooking_at' => null,
                'estimated_ready_at' => null,
                'ready_at'           => null,
                'delivered_at'       => null,
            ]);

            // ✅ Solo notificamos a la vista show de esta comanda
            broadcast(new ComandaCanceled($comanda));
        });

        return response()->json(['ok' => true, 'id' => $comanda->id, 'estado' => 'cancelada']);
    }

    public function reactivate(Request $request, Comanda $comanda)
    {
        $this->authorizeComanda($comanda);

        // Solo reactivar si está cancelada
        if ($comanda->estado !== 'cancelada') {
            return response()->json(['ok' => false, 'msg' => 'Solo puedes reactivar una comanda cancelada.'], 422);
        }

        DB::transaction(function () use ($comanda) {
            $comanda->update([
                'estado' => 'borrador',
            ]);
            // Broadcast para que Kanban/otras vistas se actualicen
            broadcast(new \App\Events\ComandaStatusUpdated($comanda));
        });

        return response()->json(['ok' => true, 'id' => $comanda->id, 'estado' => 'borrador']);
    }

    public function getDataComanda(Request $request)
    {
        $comanda_id = $request->get('comanda_id');

        $authHeader = $request->header('Authorization'); // "Bearer xxx"
        $tenantKey  = $request->header('X-Tenant-Id');   // "TENANT_abc123"
        $agentKey   = $request->header('X-Agent-Id');    // "AGENT_9f2c7e"
        $orden      = $request->orden;                  // "print_comanda"

        // 1) Auth básica
        if (!$authHeader || !str_contains($authHeader, 'Bearer ')) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $token       = trim(str_replace('Bearer ', '', $authHeader));
        $serverToken = trim(config('services.print_agent_token'));

        if ($token !== $serverToken) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        if (!$tenantKey || !$agentKey) {
            return response()->json(['ok' => false, 'msg' => 'Faltan headers de Tenant / Agent'], 401);
        }

        if (!$comanda_id) {
            return response()->json(['error' => 'comanda_id requerido'], 400);
        }

        $comanda = Comanda::with('items')->find($comanda_id);
        if (!$comanda) {
            return response()->json(['error' => 'comanda no encontrada'], 404);
        }

        // 2) Mapear el "código" de header al ID numérico
        $tenant = Tenant::where('name', $tenantKey)->first();
        $agent  = Agent::where('name', $agentKey)->first();

        if (!$tenant || !$agent) {
            return response()->json([
                'ok'  => false,
                'msg' => 'Tenant o Agent no registrados en el sistema',
                'tenant_key' => $tenantKey,
                'agent_key'  => $agentKey,
            ], 404);
        }

        if ($orden === 'print_comanda') {

            $job = PrintJob::where('tenant_id', $tenant->id)
                ->where('agent_id', $agent->id)
                ->where('comanda_id', $comanda_id)
                ->where('status', 'queued')
                ->orderBy('created_at')
                ->first();

            if (!$job) {
                // Aquí es donde hoy te devuelve 204
                return response()->noContent();
            }

            $job->status = 'taken';
            $job->save();

            $items = $comanda->items->map(function ($detail) {
                $ops = is_array($detail->opciones)
                    ? $detail->opciones
                    : (json_decode($detail->opciones ?? '[]', true) ?: []);

                $grupos = $ops['grupos'] ?? [];

                $groups = collect($grupos)->map(function ($grupo) use ($detail) {
                    $selections = collect($grupo['selecciones'] ?? [])->map(function ($sel) use ($detail) {
                        $label = $sel['name'] ?? $sel['nombre'] ?? '—';

                        return [
                            'label' => $label,
                            'qty'   => (float)$detail->cantidad,
                        ];
                    })->values()->all();

                    return [
                        'selections' => $selections,
                    ];
                })->values()->all();

                return [
                    'name'   => $detail->nombre,
                    'qty'    => (float)$detail->cantidad,
                    'groups' => $groups,
                ];
            })->values()->all();

            return response()->json([
                'id'                 => $comanda->id,
                'numero'             => $comanda->numero,
                'mesa'               => optional($comanda->atencion->mesa)->nombre,
                'mozo'               => optional($comanda->atencion->mozo)->nombre,
                'total'              => $comanda->total,
                'status'             => $this->mapComandaToKanban($comanda->estado),
                'send_to_kitchen_at' => $comanda->formatted_send_to_kitchen,
                'started_cooking_at' => $comanda->formatted_started_cooking,
                'estimated_ready_at' => $comanda->formatted_estimated_ready,
                'items'              => $items,
                'job_id'             => $job->id,
                'tenant_id'          => $job->tenant_id,
                'agent_id'           => $job->agent_id,
                'printer_name'       => $job->printer_name,
                'content'            => $job->content,
            ]);
        }

        return response()->json(['error' => 'orden no soportada'], 400);
    }
}
