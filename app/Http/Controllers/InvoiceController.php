<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

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
        $extra = $invoice->extra ?? [];

        // 1. Lo que tú realmente guardas
        $pdf =
            data_get($extra, 'nubefact.enlace_del_pdf')      // https://...pdf
            ?? data_get($extra, 'nubefact.enlace')           // https://... (sin .pdf)
            ?? data_get($extra, 'enlace_del_pdf')            // por si algún día lo guardas arriba
            ?? data_get($extra, 'enlace')                    // por si algún día lo guardas arriba
            ?? null;

        if ($pdf) {
            // si es el enlace sin .pdf, igual Nubefact lo abre bonito en web
            return redirect()->away($pdf);
        }

        // Si llegamos acá es porque el invoice no tiene info de Nubefact guardada
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
