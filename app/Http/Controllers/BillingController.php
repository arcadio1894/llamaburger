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
        // 1) Validación de entrada (incluye descuento/propina como objetos)
        $data = $req->validate([
            'tipo'              => ['required', Rule::in(['boleta','factura','ticket'])],
            'customer_id'       => ['nullable','exists:clientes,id'],
            'cliente_nombre'    => ['nullable','string','max:200'],
            'cliente_doc_tipo'  => ['nullable','string','max:10'],   // RUC/DNI
            'cliente_doc_num'   => ['nullable','string','max:20'],
            'cliente_direccion' => ['nullable','string','max:255'],

            // Ítems seleccionados (opcional: si no llegan, el service factura lo pendiente)
            'items'         => ['nullable','array'],
            'items.*.id'    => ['required_with:items','integer'],
            'items.*.qty'   => ['required_with:items','integer','min:1'],

            // Descuento global {tipo:'porc'|'fijo', valor: number}
            'descuento'         => ['nullable','array'],
            'descuento.tipo'    => ['nullable', Rule::in(['porc','fijo'])],
            'descuento.valor'   => ['nullable','numeric','min:0'],

            // Propina {tipo:'porc'|'fijo', valor: number}
            'propina'           => ['nullable','array'],
            'propina.tipo'      => ['nullable', Rule::in(['porc','fijo'])],
            'propina.valor'     => ['nullable','numeric','min:0'],

            // Pagos
            'pagos'                 => ['nullable','array'],
            'pagos.*.metodo'        => ['required_with:pagos', Rule::in(['efectivo','tarjeta','yape','plin','transferencia','mixto','otro'])],
            'pagos.*.monto'         => ['required_with:pagos','numeric','min:0.01'],
            'pagos.*.moneda'        => ['nullable','string','size:3'],
            'pagos.*.monto_recibido'=> ['nullable','numeric'],
            'pagos.*.vuelto'        => ['nullable','numeric'],
            'pagos.*.referencia'    => ['nullable','string','max:100'],
        ]);

        // 1.1) Compatibilidad hacia atrás (si llegan campos sueltos desde algún flujo viejo)
        if (empty($data['descuento']) && $req->filled('descuento_tipo')) {
            $data['descuento'] = [
                'tipo'  => $req->input('descuento_tipo'),
                'valor' => (float)$req->input('descuento_val', 0),
            ];
        }
        if (empty($data['propina']) && $req->filled('propina_tipo')) {
            $data['propina'] = [
                'tipo'  => $req->input('propina_tipo'),
                'valor' => (float)$req->input('propina_val', 0),
            ];
        }

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

        // 3) Armar payload para el Service (dejamos que el service aplique reglas: IGV incluido, prorrateo de descuento, propina inafecta)
        $selectedItems = $req->input('items', null); // [{id, qty}, ...]
        $billingData = [
            'tipo'              => $data['tipo'],
            'customer_id'       => $data['customer_id'] ?? null,
            'cliente_nombre'    => $data['cliente_nombre'] ?? null,
            'cliente_doc_tipo'  => $data['cliente_doc_tipo'] ?? null,
            'cliente_doc_num'   => $data['cliente_doc_num'] ?? null,
            'cliente_direccion' => $data['cliente_direccion'] ?? null,
            'descuento'         => $data['descuento'] ?? null, // {tipo, valor}
            'propina'           => $data['propina']   ?? null, // {tipo, valor}
        ];

        // 4) Generar invoice interno (contable) con tu servicio
        $invoice = $svc->handle(
            $atencion,
            $billingData,
            $data['pagos'] ?? null,
            $selectedItems
        );

        // 5) Si es ticket: no emitir a Nubefact
        if ($invoice->tipo === 'ticket') {
            return response()->json([
                'ok'               => true,
                'msg'              => 'Comprobante generado (ticket interno).',
                'invoice_id'       => $invoice->id,
                'comprobante_url'  => route('invoices.show', $invoice),
                'pdf_url'          => null,
            ]);
        }

        // 6) Boleta/Factura: emitir a Nubefact y devolver enlace PDF
        try {
            // IMPORTANTE: emitirConNubefact debe usar:
            // - precios con IGV incluido (como vienen de la BD)
            // - enviar descuento global (monto) y propina (total_inafecta)
            // - bases/igv resultantes según lo calculado en $invoice
            $nubefactRes = $this->emitirConNubefact($invoice);

            if (isset($nubefactRes['errors'])) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Error desde Nubefact: ' . $nubefactRes['errors']
                ], 500);
            }

            // Persistimos datos devueltos (serie/numero, hash, enlaces, etc.)
            $this->actualizarInvoiceConRespuesta($invoice, $nubefactRes);

            return response()->json([
                'ok'               => true,
                'msg'              => 'Comprobante generado y enviado a Nubefact.',
                'invoice_id'       => $invoice->id,
                'comprobante_url'  => route('invoices.show', $invoice),
                'pdf_url'          => $nubefactRes['enlace_del_pdf'] ?? null,
                'data'             => $nubefactRes,
            ]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok'      => false,
                'message' => 'Error al emitir con Nubefact: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Invoice $invoice)
    {
        // Aquí podrías renderizar una Blade con formato de comprobante
        // o bien retornar un PDF (Snappy/Dompdf) según config.
        return view('invoices.show', compact('invoice'));
    }

    private function emitirConNubefact(Invoice $invoice): array
    {
        $tipoComprobante = $invoice->tipo === 'factura' ? "1" : "2";

        $serieEnv = $invoice->tipo === 'factura'
            ? env('NUBEFACT_SERIE_FACTURA', 'FFF1')
            : env('NUBEFACT_SERIE_BOLETA',  'BBB1');
        $numeroEnv = "";

        $token = (string) env('NUBEFACT_TOKEN');
        $url   = (string) env('NUBEFACT_API_URL');

        $igvRate = 0.18;

        // --- Totales contables guardados ---
        $opGravada   = (float) $invoice->op_gravada;      // base sin descuento
        $opExonerada = (float) $invoice->op_exonerada;    // 0 en tu flujo
        $opInafecta  = (float) $invoice->op_inafecta;     // PROPINA
        $descuentoBI = (float) $invoice->descuento;       // descuento en BASE (sin IGV)

        // Conversión del descuento a BRUTO (con IGV), que es lo que ve el cajero en la UI
        $descuentoBruto = round($descuentoBI * (1 + $igvRate), 2); // p.ej. 10.00

        // 1) Construimos el vector con el BRUTO ORIGINAL por ítem
        $itemsOrig = [];
        $sumGrossOrig = 0.0;

        foreach ($invoice->items as $it) {
            // En tu persistencia guardamos ambos unitarios; usamos el con IGV
            $qty           = (float) $it->cantidad;
            $precioUnitCI  = (float) $it->precio_unitario;     // con IGV
            $gross         = round($precioUnitCI * $qty, 2);   // total con IGV por ítem

            $itemsOrig[] = [
                'ref'          => $it,            // referencia al modelo para extraer campos
                'qty'          => $qty,
                'precioCI'     => $precioUnitCI,  // con IGV
                'gross_orig'   => $gross,         // con IGV
            ];

            $sumGrossOrig += $gross;
        }

        // 2) Monto bruto objetivo (= lo que muestra tu UI sin propina)
        //    uiTotalBruto = sumGrossOrig - descuentoBruto
        $targetGross = round($sumGrossOrig - $descuentoBruto, 2);
        if ($targetGross < 0) { $targetGross = 0.00; }

        // 3) Prorrateo del DESCUENTO EN BRUTO por ítem con ajuste de céntimos (mayores restos)
        //    Para asegurar que Σ gross_new == targetGross
        $itemsTmp = [];
        $remainders = [];

        foreach ($itemsOrig as $idx => $row) {
            $weight   = ($sumGrossOrig > 0) ? ($row['gross_orig'] / $sumGrossOrig) : 0;
            $rebaja   = $descuentoBruto * $weight;               // rebaja proporcional (no redondeada)
            $grossNew = $row['gross_orig'] - $rebaja;            // bruto con IGV prorrateado
            $grossNewRounded = floor($grossNew * 100) / 100.0;   // redondeo hacia abajo a 2 dec
            $itemsTmp[$idx] = [
                'ref'        => $row['ref'],
                'qty'        => $row['qty'],
                'gross_new'  => $grossNewRounded,                 // provisional
            ];
            // guardamos la parte fraccional para repartir +0.01 a los mayores restos
            $remainders[$idx] = $grossNew - $grossNewRounded;     // 0..0.009999
        }

        // Ajuste de céntimos para que la suma case EXACTO con $targetGross
        $sumProvisional = array_sum(array_column($itemsTmp, 'gross_new'));
        $diffCents = (int) round(($targetGross - $sumProvisional) * 100); // puede ser positivo o 0

        if ($diffCents !== 0) {
            // Ordenamos índices por mayor resto (desc)
            arsort($remainders); // conserva claves
            foreach (array_keys($remainders) as $idx) {
                if ($diffCents === 0) break;
                $itemsTmp[$idx]['gross_new'] = round($itemsTmp[$idx]['gross_new'] + 0.01, 2);
                $diffCents--;
            }
        }

        // 4) Con el BRUTO prorrateado definitivo por ítem, derivamos BASE/IGV/unitarios
        $itemsPayload = [];
        $sumSub = 0.0; // base
        $sumIgv = 0.0; // igv
        $sumTot = 0.0; // total con IGV (= targetGross)

        foreach ($itemsTmp as $row) {
            /** @var \App\Models\InvoiceItem $it */
            $it     = $row['ref'];
            $qty    = $row['qty'];
            $gross  = $row['gross_new'];                  // total con IGV por ítem (ajustado)
            $precioUnitCI = ($qty > 0) ? round($gross / $qty, 6) : 0.0;      // con IGV
            $valorUnitSI  = round($precioUnitCI / (1 + $igvRate), 6);        // sin IGV

            $subtotal = round($gross / (1 + $igvRate), 2);  // base 2 dec
            $igv      = round($gross - $subtotal, 2);       // igv  2 dec

            $sumSub += $subtotal;
            $sumIgv += $igv;
            $sumTot += $gross;

            $itemsPayload[] = [
                "unidad_de_medida" => $it->unidad ?? "NIU",
                "codigo"           => $it->product_id ?? $it->id,
                "descripcion"      => $it->descripcion,
                "cantidad"         => $qty,

                "valor_unitario"   => $valorUnitSI,     // sin IGV (hasta 6 dec)
                "precio_unitario"  => $precioUnitCI,    // con IGV (hasta 6 dec)

                "subtotal"         => $subtotal,        // sin IGV (2 dec)
                "tipo_de_igv"      => "1",
                "igv"              => $igv,
                "total"            => $gross,           // con IGV (2 dec) == suma visible por ítem
            ];
        }

        // 5) Sumarios coherentes con lo que ve el cajero
        $totalGravada = round($sumSub, 2);                        // base neta (descuento aplicado)
        $totalIgv     = round($sumIgv, 2);
        $totalFinal   = round($sumTot + $opExonerada + $opInafecta, 2); // +propina

        // Tipo doc SUNAT
        $tipoDocSunat = "0";
        if ($invoice->cliente_doc_tipo === 'RUC') { $tipoDocSunat = "6"; }
        elseif ($invoice->cliente_doc_tipo === 'DNI') { $tipoDocSunat = "1"; }

        $payload = [
            "operacion"                   => "generar_comprobante",
            "tipo_de_comprobante"         => $tipoComprobante,
            "serie"                       => $serieEnv,
            "numero"                      => $numeroEnv,
            "codigo_unico"                => "INV-".$invoice->id,
            "sunat_transaction"           => "1",

            "cliente_tipo_de_documento"   => $tipoDocSunat,
            "cliente_numero_de_documento" => (string) ($invoice->cliente_doc_num ?? ''),
            "cliente_denominacion"        => (string) ($invoice->cliente_nombre ?? ''),
            "cliente_direccion"           => (string) ($invoice->cliente_direccion ?? ''),
            "cliente_email"               => (string) ($invoice->extra['cliente_email'] ?? ''),

            "fecha_de_emision"            => now()->format('d-m-Y'),
            "moneda"                      => "1",
            "porcentaje_de_igv"           => 18.00,

            "total_gravada"               => number_format($totalGravada, 2, '.', ''),
            "total_exonerada"             => number_format($opExonerada,  2, '.', ''),
            "total_inafecta"              => number_format($opInafecta,   2, '.', ''), // PROPINA
            "total_igv"                   => number_format($totalIgv,     2, '.', ''),
            "total"                       => number_format($totalFinal,   2, '.', ''),
            "total_a_pagar"               => number_format($totalFinal,   2, '.', ''),

            "items"                       => $itemsPayload,
        ];

        // MUY IMPORTANTE: no enviar "descuento_global" aquí, ya está prorrateado en los ítems.

        $response = Http::withHeaders([
            'Authorization' => 'Token token=' . $token,
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

        // Si quieres, aquí puedes agregar:
        // $extra['pdf_url'] = $res['enlace_del_pdf'] ?? null;
        // $extra['xml_url'] = $res['enlace_del_xml'] ?? null;
        // $extra['cdr_url'] = $res['enlace_del_cdr'] ?? null;
        // y volver a actualizar $invoice->extra.
    }
}
