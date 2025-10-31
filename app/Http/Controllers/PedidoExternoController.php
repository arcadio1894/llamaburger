<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Models\Comanda;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoExternoController extends Controller
{
    // Lista (abiertos / en proceso)
    public function index(Request $req)
    {
        $q = Atencion::externos()
            ->with(['comandas' => function($q){
                $q->where('numero', 1)
                    ->with(['items:id,comanda_id,precio_unit,cantidad']) // << sin 'total'
                    ->withCount('items');
            }]);

        if ($search = trim($req->input('q'))) {
            $q->where(function($w) use ($search){
                $w->where('id', (int) $search)
                    ->orWhere('comentario', 'like', "%{$search}%");
            });
        }

        if ($estado = trim($req->input('estado'))) {
            $q->where('estado', $estado);
        }

        $sort = trim($req->input('sort', 'recientes'));
        if ($sort === 'antiguos') {
            $q->oldest('opened_at');
        } else {
            $q->latest('opened_at');
        }

        $atenciones = $q->paginate(18);

        $atenciones->getCollection()->transform(function ($a) {
            $c1 = $a->comandas->first();
            // Como "total" es un accesor, puedes sumar así:
            $a->monto_total = $c1 ? $c1->items->sum('total') : null;
            $a->items_count = $c1 ? $c1->items_count : null;
            return $a;
        });

        return view('pedido_externo.index', compact('atenciones'));
    }

    public function showComanda(Atencion $atencion)
    {
        abort_unless($atencion->tipo === 'externo', 404);

        $comanda = $atencion->comandas()->where('numero',1)->firstOrFail();
        // Puedes eager-load items aquí si quieres mostrarlos
        $comanda->load(['items' => function($q){ $q->orderBy('id'); }]);

        // 🔽 Construimos payloads compatibles con PHP 7.3
        $itemsPayload = $comanda->items->map(function($it){
            return [
                'server_id'  => (int) $it->id,
                'product_id' => (int) $it->product_id,
                'name'       => (string) $it->nombre,
                'price'      => (float) $it->precio_unit,
                'qty'        => (int) $it->cantidad,
                'opciones'   => $it->opciones ?? null,  // 🔰
                'has_options'=> $it->product ? $it->product->has_options : (!empty($it->opciones)), //
            ];
        })->values()->all();

        $totalsPayload = [
            'subtotal'  => (float) $comanda->subtotal,
            'descuento' => (float) ($comanda->descuento ?? 0),
            'igv'       => (float) $comanda->igv,
            'total'     => (float) $comanda->total,
        ];


        // Carga vista clonada para externo:
        return view('pedido_externo.show', compact('atencion','comanda','itemsPayload','totalsPayload'));
    }

    // Crea 1 atención externa + 1 comanda (siempre número 1)
    public function crear(Request $request)
    {
        try {
            $atencion = null;
            DB::transaction(function () use (&$atencion) {
                $atencion = Atencion::create([
                    'tipo'      => 'externo',
                    'mesa_id'   => null,
                    'mozo_id'   => null,
                    'personas'  => 1,
                    'comentario'=> null,
                    'estado'    => 'abierta',
                    'opened_at' => now(),
                ]);

                Comanda::create([
                    'atencion_id' => $atencion->id,
                    'numero'      => 1,
                    'estado'      => 'borrador',
                ]);
            });

            // Reutiliza tu pantalla de comanda:
            return redirect()->route('pedido.externo.comanda.show', [$atencion->id, 1])
                ->with('info', 'Pedido externo creado. Agrega productos y procede a pagar.');
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error','No se pudo crear el pedido externo.');
        }
    }

    // Cambia el CTA "IR A PAGAR" -> valida y redirige al flujo de pagos compartido
    public function irPagar(Request $request, Atencion $atencion)
    {
        abort_unless($atencion->tipo === 'externo', 404);

        $comanda = $atencion->comandas()->where('numero', 1)->first();
        if (!$comanda) {
            return back()->withErrors('La comanda no existe.');
        }
        if ($comanda->items()->count() === 0) {
            return back()->withErrors('Agrega al menos un producto.');
        }

        // Estado intermedio
        $atencion->update(['estado' => 'por_pagar']);

        // Redirige a la vista de pagos (la misma que usaremos para mesas)
        return redirect()->route('pagos.create', $atencion);
    }

    public function verDetalles(Atencion $atencion)
    {
        // 1. Traemos la invoice con items
        $invoice = Invoice::with('items')
            ->where('atencion_id', $atencion->id)
            ->first();

        // 2. Traemos (opcional) la primera comanda por si quieres mostrar mesa/mozo
        $comanda = $atencion->comandas()->with('items')->first();

        // 3. Cliente (desde invoice si existe)
        $cliente = [
            'nombre'    => $invoice ? $invoice->cliente_nombre : null,
            'documento' => $invoice ? $invoice->cliente_doc_num : null,
            'tipo_doc'  => $invoice ? $invoice->cliente_doc_tipo : null,
            'direccion' => $invoice ? $invoice->cliente_direccion : null,
        ];

        // 4. Items: si hay invoice, usamos invoice_items (cantidad + valor_unitario SIN IGV)
        if ($invoice) {
            $items = $invoice->items->map(function ($it) {
                $cant   = (float) ($it->cantidad ?? 0);
                $precio = (float) ($it->valor_unitario ?? 0);   // SIN IGV
                $total  = round($cant * $precio, 2);

                return [
                    'nombre'   => $it->descripcion ?? $it->nombre ?? '-',
                    'cantidad' => $cant,
                    'precio'   => $precio,
                    'total'    => $total,
                    'notas'    => $it->observacion ?? null,
                ];
            });
        } else {
            // fallback a comanda items (lo que tenías antes)
            $items = $comanda
                ? $comanda->items->map(function ($it) {
                    return [
                        'nombre'   => $it->nombre,
                        'cantidad' => $it->cantidad,
                        'precio'   => $it->precio,
                        'total'    => $it->total,
                        'notas'    => $it->observacion,
                    ];
                })
                : collect();
        }

        // 5. Totales al estilo “comprobante”
        // Si NO hay invoice, devolvemos totales vacíos y el front puede mostrar lo viejo.
        $totals = null;
        if ($invoice) {
            // subtotal = suma de (cantidad * valor_unitario)
            $subtotal = $items->sum('total');

            $descuento = (float) ($invoice->descuento ?? 0.0);

            // base imponible = subtotal - descuento
            $baseImponible = max(0, $subtotal - $descuento);

            // IGV: usa el de la invoice si ya vino. Si no, calcúlalo.
            $igv = round($baseImponible * 0.18, 2);

            // Propina: la estás guardando en op_inafecta
            $propina = (float) ($invoice->op_inafecta ?? 0.0);

            // total = base + igv + propina
            $totalPagar = round($baseImponible + $igv + $propina, 2);

            $totals = [
                'subtotal'        => round($subtotal, 2),
                'descuento'       => round($descuento, 2),
                'base_imponible'  => round($baseImponible, 2),
                'igv'             => round($igv, 2),
                'propina'         => round($propina, 2),
                'total'           => $totalPagar,
            ];
        }

        return response()->json([
            'ok' => true,
            'atencion' => [
                'id'         => $atencion->id,
                'created_at' => $atencion->created_at ? $atencion->created_at->toDateTimeString() : null,
            ],
            'cliente' => $cliente,

            // Por si tu JS usa todavía datos de comanda (mesa/mozo)
            'comanda' => $comanda ? [
                'id'     => $comanda->id,
                'numero' => $comanda->numero,
                'mesa'   => optional($comanda->atencion->mesa)->nombre,
                'mozo'   => optional($comanda->atencion->mozo)->nombre,
            ] : null,

            // 👇 lo importante
            'items'  => $items,
            'totals' => $totals,
        ]);
    }
}
