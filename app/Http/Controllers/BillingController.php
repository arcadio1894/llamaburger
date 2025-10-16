<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Services\Billing\GenerateInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function facturar(Request $req, Atencion $atencion, GenerateInvoiceService $svc)
    {
        // Validación de entrada
        $data = $req->validate([
            'tipo' => ['required', Rule::in(['boleta','factura','ticket'])],
            'customer_id' => ['nullable','exists:clientes,id'],
            'cliente_nombre' => ['nullable','string','max:200'],
            'cliente_doc_tipo' => ['nullable','string','max:10'],   // p.e. RUC/DNI
            'cliente_doc_num'  => ['nullable','string','max:20'],
            'cliente_direccion'=> ['nullable','string','max:255'],

            'pagos' => ['nullable','array'],
            'pagos.*.metodo' => ['required_with:pagos', Rule::in(['efectivo','tarjeta','yape','plin','transferencia','mixto','otro'])],
            'pagos.*.monto'  => ['required_with:pagos','numeric','min:0.01'],
            'pagos.*.moneda' => ['nullable','string','size:3'],
            'pagos.*.monto_recibido' => ['nullable','numeric'],
            'pagos.*.vuelto' => ['nullable','numeric'],
            'pagos.*.referencia' => ['nullable','string','max:100'],
        ]);

        $invoice = $svc->handle(
            $atencion,
            [
                'tipo' => $data['tipo'],
                'customer_id' => $data['customer_id'] ?? null,
                'cliente_nombre' => $data['cliente_nombre'] ?? null,
                'cliente_doc_tipo' => $data['cliente_doc_tipo'] ?? null,
                'cliente_doc_num'  => $data['cliente_doc_num'] ?? null,
                'cliente_direccion'=> $data['cliente_direccion'] ?? null,
            ],
            $data['pagos'] ?? null
        );

        // Devolver JSON con la URL del comprobante (HTML/PDF)
        return response()->json([
            'ok' => true,
            'msg' => 'Comprobante generado.',
            'invoice_id' => $invoice->id,
            'comprobante_url' => route('invoices.show', $invoice), // crea esa ruta/vista
        ]);
    }

    public function show(\App\Models\Invoice $invoice)
    {
        // Aquí podrías renderizar una Blade con formato de comprobante
        // o bien retornar un PDF (Snappy/Dompdf) según config.
        return view('invoices.show', compact('invoice'));
    }
}
