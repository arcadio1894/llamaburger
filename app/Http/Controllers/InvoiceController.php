<?php

namespace App\Http\Controllers;

use App\Models\DataGeneral;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index(Request $req)
    {
        // listado con relaciones para evitar N+1
        $invoices = Invoice::with(['customer','payments','items'])
            ->latest('issue_date')
            ->paginate(20);

        return view('invoice.index', compact('invoices'));
    }

    // Detalle de productos (para popup)
    public function items(Invoice $invoice)
    {
        $items = $invoice->items()
            ->select(['id','descripcion as nombre','cantidad','precio_unitario','total'])
            ->get()
            ->map(function ($i) {
                return [
                    'nombre'  => $i->nombre ?? ($i->descripcion ?? ''),
                    'cantidad'=> (float)$i->cantidad,
                    'precio'  => (float)($i->precio_unitario ?? 0),
                    'total'   => (float)($i->total ?? 0),
                ];
            });

        return response()->json(['ok'=>true,'items'=>$items]);
    }

    // Imprimir PDF (Nubefact)
    public function imprimir(Invoice $invoice)
    {
        // 1) Si el comprobante es "ticket": renderizamos nuestro PDF interno
        if (strtolower($invoice->tipo ?? '') === 'ticket') {
            // Trae items
            $invoice->load(['items']);

            // IGV: DataGeneral(name='igv_rate') en porcentaje (p.ej. 18), fallback 18%
            $igvRate = optional(DataGeneral::where('name', 'igv_rate')->first())->valueNumber;
            $igvRate = $igvRate !== null ? round(((float) $igvRate) / 100, 2) : 0.18;

            // Subtotal = suma(cantidad * valor_unitario [SIN IGV])
            $subtotal = $invoice->items->sum(function ($it) {
                return (float) $it->cantidad * (float) $it->valor_unitario;
            });

            // Descuento global
            $descuento = (float) ($invoice->descuento ?? 0.0);

            // Base imponible
            $base = max($subtotal - $descuento, 0);

            // IGV
            $igv = round($base * $igvRate, 2);

            // Propina (no gravada)
            $propina = (float) ($invoice->op_inafecta ?? 0.0);

            // Total a pagar
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

            // Renderiza el PDF del ticket
            $pdf = Pdf::loadView('comanda.ticket-venta', compact('invoice', 'totals'))
                ->setPaper([0, 0, 226.8, 900], 'portrait');

            return $pdf->stream("ticket-venta-{$invoice->id}.pdf");
        }

        // 2) Para boleta/factura: usar enlace de Nubefact como antes
        $extra = $invoice->extra ?? [];

        $pdf =
            data_get($extra, 'nubefact.enlace_del_pdf')      // https://...pdf
            ?? data_get($extra, 'nubefact.enlace')           // https://... (sin .pdf)
            ?? data_get($extra, 'enlace_del_pdf')            // alterno
            ?? data_get($extra, 'enlace')                    // alterno
            ?? null;

        if ($pdf) {
            return redirect()->away($pdf);
        }

        return back()->withErrors('No se encontró el enlace al PDF del comprobante en "extra.nubefact".');

    }

    // Anular comprobante (stub)
    public function void(Invoice $invoice, Request $req)
    {
        // TODO: integrar con Nubefact (nota de crédito o anulación según normativa)
        // Por ahora solo marcamos estado localmente como 'anulado' si procede.
        $invoice->update(['estado' => 'anulado']);

        return response()->json(['ok'=>true]);
    }
}
