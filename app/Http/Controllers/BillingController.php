<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Models\Invoice;
use App\Services\Billing\GenerateInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class BillingController extends Controller
{
    public function facturar(Request $req, Atencion $atencion, GenerateInvoiceService $svc)
    {
        // 1) Validación de entrada
        $data = $req->validate([
            'tipo' => ['required', Rule::in(['boleta','factura','ticket'])],
            'customer_id' => ['nullable','exists:clientes,id'],
            'cliente_nombre' => ['nullable','string','max:200'],
            'cliente_doc_tipo' => ['nullable','string','max:10'],   // RUC/DNI
            'cliente_doc_num'  => ['nullable','string','max:20'],
            'cliente_direccion'=> ['nullable','string','max:255'],

            // Ítems (opcionales si reconstruyes en el service; aquí solo pasan ids/qty)
            'items' => ['nullable','array'],
            'items.*.id'  => ['required_with:items','integer'],
            'items.*.qty' => ['required_with:items','integer','min:1'],

            // Pagos
            'pagos' => ['nullable','array'],
            'pagos.*.metodo' => ['required_with:pagos', Rule::in(['efectivo','tarjeta','yape','plin','transferencia','mixto','otro'])],
            'pagos.*.monto'  => ['required_with:pagos','numeric','min:0.01'],
            'pagos.*.moneda' => ['nullable','string','size:3'],
            'pagos.*.monto_recibido' => ['nullable','numeric'],
            'pagos.*.vuelto' => ['nullable','numeric'],
            'pagos.*.referencia' => ['nullable','string','max:100'],
        ]);

        // 2) Validación condicional de método de pago (backend)
        if (!empty($data['pagos'])) {
            foreach ($data['pagos'] as $p) {
                if (in_array($p['metodo'], ['yape','plin']) && empty($p['referencia'])) {
                    return response()->json(['ok'=>false,'message'=>'El código de operación es obligatorio para Yape/Plin.'], 422);
                }
                if ($p['metodo'] === 'efectivo') {
                    if (!isset($p['monto_recibido']) || $p['monto_recibido'] <= 0) {
                        return response()->json(['ok'=>false,'message'=>'El monto recibido es obligatorio para efectivo.'], 422);
                    }
                }
            }
        }

        $selectedItems = $req->input('items', null); // [{id, qty}, ...]

        // 3) Generar invoice interno (contable) con tu servicio
        $invoice = $svc->handle(
            $atencion,
            [
                'tipo' => $data['tipo'],
                'customer_id' => $data['customer_id'] ?? null,
                'cliente_nombre' => $data['cliente_nombre'] ?? null,
                'cliente_doc_tipo' => $data['cliente_doc_tipo'] ?? null,
                'cliente_doc_num'  => $data['cliente_doc_num'] ?? null,
                'cliente_direccion'=> $data['cliente_direccion'] ?? null,
                // si luego quieres pasar items/discount/propina, agrégalo al service
            ],
            $data['pagos'] ?? null,
            $selectedItems // <- NUEVO
        );

        // 4) Si es ticket: no emitir a Nubefact, no devolver pdf
        if ($invoice->tipo === 'ticket') {
            return response()->json([
                'ok' => true,
                'msg' => 'Comprobante generado (ticket interno).',
                'invoice_id' => $invoice->id,
                'comprobante_url' => route('invoices.show', $invoice),
                'pdf_url' => null,
            ]);
        }

        // 5) Boleta/Factura: emitir a Nubefact y devolver enlace PDF
        try {
            $nubefactRes = $this->emitirConNubefact($invoice); // <- construir desde Invoice/Items

            // Si Nubefact devolvió error
            if (isset($nubefactRes['errors'])) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Error desde Nubefact: ' . $nubefactRes['errors']
                ], 500);
            }

            // Persistimos datos devueltos (serie/numero si Nubefact los asigna, enlaces, etc.)
            $this->actualizarInvoiceConRespuesta($invoice, $nubefactRes);

            return response()->json([
                'ok' => true,
                'msg' => 'Comprobante generado y enviado a Nubefact.',
                'invoice_id' => $invoice->id,
                'comprobante_url' => route('invoices.show', $invoice),
                'pdf_url' => $nubefactRes['enlace_del_pdf'] ?? null, // <- lo que necesita el front
                'data' => $nubefactRes,
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok' => false,
                'message' => 'Error al emitir con Nubefact: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(\App\Models\Invoice $invoice)
    {
        // Aquí podrías renderizar una Blade con formato de comprobante
        // o bien retornar un PDF (Snappy/Dompdf) según config.
        return view('invoices.show', compact('invoice'));
    }

    private function emitirConNubefact(Invoice $invoice): array
    {
        $tipoComprobante = $invoice->tipo === 'factura' ? "1" : "2";

        // 1) Serie desde .env (DEBE existir en Nubefact)
        $serieEnv = $invoice->tipo === 'factura'
            ? env('NUBEFACT_SERIE_FACTURA', 'FFF1')
            : env('NUBEFACT_SERIE_BOLETA',  'BBB1');

        // 2) Deja el número vacío para que Nubefact asigne correlativo
        $numeroEnv = "";

        $token = env('NUBEFACT_TOKEN');
        $url   = env('NUBEFACT_API_URL');

        $items = [];
        foreach ($invoice->items as $it) {
            $items[] = [
                "unidad_de_medida" => $it->unidad ?? "NIU",
                "codigo"           => $it->product_id ?? $it->id,
                "descripcion"      => $it->descripcion,
                "cantidad"         => (float)$it->cantidad,
                "valor_unitario"   => (float)$it->valor_unitario,   // sin IGV
                "precio_unitario"  => (float)$it->precio_unitario,  // con IGV
                "subtotal"         => (float)$it->subtotal,         // sin IGV
                "tipo_de_igv"      => "1",
                "igv"              => (float)$it->igv,
                "total"            => (float)$it->total,            // con IGV
            ];
        }

        $totalGravada = (float)$invoice->op_gravada;
        $totalIgv     = (float)$invoice->igv;
        $total        = (float)$invoice->total;
        $descuento    = (float)$invoice->descuento;

        $payload = [
            "operacion"               => "generar_comprobante",
            "tipo_de_comprobante"     => $tipoComprobante,         // 1=Factura, 2=Boleta
            "serie"                   => $serieEnv,                // <- SERIE válida en Nubefact
            "numero"                  => $numeroEnv,               // <- dejar vacío para que asigne
            "codigo_unico"            => "INV-".$invoice->id,      // idempotencia
            "sunat_transaction"       => "1",

            "cliente_tipo_de_documento"   => ($invoice->cliente_doc_tipo === 'RUC') ? "6" : "1",
            "cliente_numero_de_documento" => $invoice->cliente_doc_num,
            "cliente_denominacion"        => $invoice->cliente_nombre,
            "cliente_direccion"           => $invoice->cliente_direccion ?: "",
            "cliente_email"               => $invoice->extra['cliente_email'] ?? "",

            "fecha_de_emision"        => now()->format('d-m-Y'),
            "moneda"                  => "1",
            "porcentaje_de_igv"       => 18.00,

            "total_gravada"           => number_format(max($totalGravada - $descuento, 0), 2, '.', ''),
            "total_igv"               => number_format($totalIgv, 2, '.', ''),
            "total"                   => number_format($total, 2, '.', ''),
            "total_a_pagar"           => number_format($total, 2, '.', ''),
            "items"                   => $items,
        ];

        if ($descuento > 0) {
            $payload["descuento_global"] = number_format($descuento, 2, '.', '');
            $payload["total_descuento"]  = number_format($descuento, 2, '.', '');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Token token='.$token,
            'Content-Type'  => 'application/json',
        ])->post($url, $payload);

        return $response->json();
    }

    private function actualizarInvoiceConRespuesta(Invoice $invoice, array $res)
    {
        $extra = $invoice->extra ?? [];
        $extra['nubefact'] = $res;

        $invoice->update([
            'serie'  => $res['serie']  ?? $invoice->serie,
            'numero' => $res['numero'] ?? $invoice->numero,
            'extra'  => $extra,
        ]);

        // Si deseas, aquí podrías descargar PDF/XML/CDR y guardar filenames en extra.
        // Como acordamos, por ahora devolvemos el enlace_del_pdf directamente.
    }
}
