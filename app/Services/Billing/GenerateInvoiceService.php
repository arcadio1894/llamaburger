<?php
/**
 * Created by PhpStorm.
 * User: Milly
 * Date: 16/10/2025
 * Time: 05:26 PM
 */
namespace App\Services\Billing;

use App\Models\Atencion;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateInvoiceService
{
    /**
     * @param Atencion $atencion  Debe estar en estado 'a_pagar'
     * @param array $billingData  [
     *   'tipo' => 'boleta'|'factura'|'ticket',
     *   'customer_id' => int|null,
     *   // override de snapshot:
     *   'cliente_nombre','cliente_doc_tipo','cliente_doc_num','cliente_direccion'
     * ]
     * @param array|null $paymentData  [
     *   ['metodo'=>'efectivo','monto'=>..., 'monto_recibido'=>..., 'referencia'=>...],
     *   ...
     * ]
     * @return Invoice
     * @throws \Throwable
     */
    public function handle(Atencion $atencion, array $billingData, ?array $paymentData = null): Invoice
    {
        if ($atencion->estado !== 'a_pagar') {
            throw ValidationException::withMessages([
                'atencion' => 'La atención debe estar en estado a_pagar para facturar.'
            ]);
        }

        // Recolectar ítems desde comandas "cerrables" (no borrador/enviada), y/o una vista/materialización
        $comandas = $atencion->comandas()
            ->whereNotIn('estado', ['borrador','enviada'])
            ->with('items')
            ->get();

        $allItems = $comandas->flatMap->items;
        if ($allItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'No hay ítems confirmados para facturar.'
            ]);
        }

        // Reglas de impuestos simples (18% IGV)
        $igvRate = 0.18;

        // Armar montos
        $op_gravada = 0; $op_exonerada = 0; $op_inafecta = 0; $descuento = 0; $igv = 0; $total = 0;

        // Snapshot cliente
        $customer = $billingData['customer_id'] ? \App\Models\Cliente::find($billingData['customer_id']) : null;
        $tipo = $billingData['tipo'] ?? 'ticket';

        // Validación básica por tipo (boleta -> DNI, factura -> RUC)
        if ($tipo === 'boleta' && empty($billingData['cliente_doc_num']) && $customer && $customer->dni) {
            $billingData['cliente_doc_tipo'] = $billingData['cliente_doc_tipo'] ?? 'DNI';
            $billingData['cliente_doc_num']  = $customer->dni;
        }
        if ($tipo === 'factura' && empty($billingData['cliente_doc_num']) && $customer && $customer->ruc) {
            $billingData['cliente_doc_tipo'] = $billingData['cliente_doc_tipo'] ?? 'RUC';
            $billingData['cliente_doc_num']  = $customer->ruc;
        }
        if ($tipo === 'factura' && ($billingData['cliente_doc_tipo'] ?? '') !== 'RUC') {
            throw ValidationException::withMessages([
                'cliente_doc_tipo' => 'Para FACTURA el documento debe ser RUC.'
            ]);
        }

        return DB::transaction(function () use (
            $atencion, $tipo, $customer, $billingData, $allItems,
            $igvRate, &$op_gravada, &$op_exonerada, &$op_inafecta, &$descuento, &$igv, &$total, $paymentData
        ) {
            // Numeración
            $serie = null; $numero = null;
            if (in_array($tipo, ['boleta','factura'])) {
                $counter = DB::table('invoice_counters')->where('tipo', $tipo)->lockForUpdate()->first();
                if (!$counter) {
                    throw new \RuntimeException("No hay contador configurado para {$tipo}.");
                }
                $serie = $counter->serie;
                $numero = $counter->next_number;

                // incrementar
                DB::table('invoice_counters')
                    ->where('id', $counter->id)
                    ->update(['next_number' => $counter->next_number + 1]);
            }

            // Crear cabecera
            $inv = new Invoice();
            $inv->fill([
                'atencion_id' => $atencion->id,
                'customer_id' => $customer?->id,
                'tipo'        => $tipo,
                'serie'       => $serie,
                'numero'      => $numero,
                'cliente_nombre'    => $billingData['cliente_nombre']    ?? $customer?->nombre,
                'cliente_doc_tipo'  => $billingData['cliente_doc_tipo']  ?? null,
                'cliente_doc_num'   => $billingData['cliente_doc_num']   ?? null,
                'cliente_direccion' => $billingData['cliente_direccion'] ?? $customer?->direccion,
                'moneda'      => 'PEN',
                'estado'      => 'emitido',
                'issue_date'  => now(),
            ]);
            $inv->save();

            // Ítems
            foreach ($allItems as $it) {
                $cantidad = (float) $it->cantidad;
                $precioConIgv = (float) $it->precio_unit; // asumiendo precio_unit incluye IGV
                $valorUnit = round($precioConIgv / (1 + $igvRate), 6);
                $subtotal  = round($valorUnit * $cantidad, 2);
                $igvItem   = round($subtotal * $igvRate, 2);
                $totalItem = round($precioConIgv * $cantidad, 2);

                InvoiceItem::create([
                    'invoice_id'     => $inv->id,
                    'product_id'     => $it->product_id,
                    'descripcion'    => $it->nombre,
                    'cantidad'       => $cantidad,
                    'unidad'         => 'NIU',
                    'valor_unitario' => $valorUnit,
                    'precio_unitario'=> $precioConIgv,
                    'subtotal'       => $subtotal,
                    'igv'            => $igvItem,
                    'total'          => $totalItem,
                    'afectacion'     => '10', // gravado
                ]);

                $op_gravada += $subtotal;
                $igv        += $igvItem;
                $total      += $totalItem;
            }

            // Descuentos globales (si manejas cupones/bonos, aplícalos aquí)
            $inv->op_gravada = round($op_gravada, 2);
            $inv->op_exonerada = round($op_exonerada, 2);
            $inv->op_inafecta  = round($op_inafecta, 2);
            $inv->descuento    = round($descuento, 2);
            $inv->igv          = round($igv, 2);
            $inv->total        = round($total - $descuento, 2);
            $inv->save();

            // Registrar pagos (opcional)
            if (!empty($paymentData)) {
                foreach ($paymentData as $p) {
                    Payment::create([
                        'atencion_id'    => $atencion->id,
                        'invoice_id'     => $inv->id,
                        'metodo'         => $p['metodo'],
                        'monto'          => $p['monto'],
                        'moneda'         => $p['moneda'] ?? 'PEN',
                        'monto_recibido' => $p['monto_recibido'] ?? null,
                        'vuelto'         => $p['vuelto'] ?? null,
                        'referencia'     => $p['referencia'] ?? null,
                        'estado'         => 'aplicado',
                        'paid_at'        => now(),
                        'user_id'        => auth()->id(),
                    ]);
                }
            }

            // Cerrar la atención y liberar mesa si corresponde
            $atencion->update(['estado' => 'pagado']);
            if ($atencion->mesa_id) {
                $atencion->mesa()->update(['estado' => 'libre']);
            }

            return $inv;
        });
    }
}