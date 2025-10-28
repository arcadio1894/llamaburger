<?php
/**
 * Created by PhpStorm.
 * User: Milly
 * Date: 16/10/2025
 * Time: 05:26 PM
 */
namespace App\Services\Billing;

use App\Models\Atencion;
use App\Models\Cliente;
use App\Models\ComandaItem;
use App\Models\ComandaItemLiquidacion;
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
    public function handle(Atencion $atencion, array $billingData, ?array $paymentData = null, ?array $selectedItems = null)
    {
        if ($atencion->estado !== 'por_pagar') {
            throw ValidationException::withMessages([
                'atencion' => 'La atención debe estar en estado por_pagar para facturar.'
            ]);
        }

        // =====================================================
        // 1) Ítems seleccionados (o fallback: todo pendiente)
        // =====================================================
        $allItems = collect();

        if (!empty($selectedItems)) {
            $ids = collect($selectedItems)->pluck('id')->map(function($v){ return (int)$v; })->filter()->all();

            $items = ComandaItem::withSum('liquidaciones as pagado_qty', 'qty')
                ->whereIn('id', $ids)
                ->get()
                ->keyBy('id');

            foreach ($selectedItems as $row) {
                $id  = isset($row['id']) ? (int)$row['id'] : 0;
                $req = isset($row['qty']) && is_numeric($row['qty']) ? (int)$row['qty'] : 0;
                if ($id <= 0 || $req <= 0) continue;

                /** @var ComandaItem|null $it */
                $it = $items->get($id);
                if (!$it) continue;

                $pagado   = (int)($it->pagado_qty ?? 0);
                $restante = max(0, (int)$it->cantidad - $pagado);
                $qty      = ($restante > 0) ? min($req, $restante) : 0;
                if ($qty <= 0) continue;

                $allItems->push([
                    'comanda_item_id' => $it->id,
                    'product_id'      => $it->product_id,
                    'nombre'          => $it->nombre,
                    'cantidad'        => $qty,
                    'precio_unit'     => (float)$it->precio_unit, // <-- PRECIO CON IGV
                ]);
            }
        }

        if ($allItems->isEmpty()) {
            $comandas = $atencion->comandas()
                ->where('estado', '!=', 'borrador')
                ->with(['items.liquidaciones'])
                ->get();

            foreach ($comandas->flatMap->items as $it) {
                $pagado   = (int)$it->liquidaciones->sum('qty');
                $restante = max(0, (int)$it->cantidad - $pagado);
                if ($restante <= 0) continue;

                $allItems->push([
                    'comanda_item_id' => $it->id,
                    'product_id'      => $it->product_id,
                    'nombre'          => $it->nombre,
                    'cantidad'        => $restante,
                    'precio_unit'     => (float)$it->precio_unit, // <-- PRECIO CON IGV
                ]);
            }
        }

        if ($allItems->isEmpty()) {
            throw ValidationException::withMessages(['items' => 'No hay ítems válidos para facturar.']);
        }

        // =====================================================
        // 2) Parámetros & totales acumuladores
        // =====================================================
        $igvRate = 0.18; // 18%
        // acumuladores "brutos" (antes de descuento)
        $sumBase   = 0.0; // suma de VALOR (sin igv) de las líneas
        $sumIgv    = 0.0; // suma de IGV por línea
        $sumTotal  = 0.0; // suma de TOTALES (precio con igv) de las líneas

        // para cabecera que guardaremos
        $op_gravada   = 0.0;
        $op_exonerada = 0.0;
        $op_inafecta  = 0.0; // aquí caerá la propina
        $descuento    = 0.0;
        $igv          = 0.0;
        $total        = 0.0;

        // =====================================================
        // 3) Cliente snapshot
        // =====================================================
        $customer = !empty($billingData['customer_id']) ? Cliente::find($billingData['customer_id']) : null;
        $tipo     = $billingData['tipo'] ?? 'ticket';

        if ($tipo === 'boleta' && empty($billingData['cliente_doc_num']) && $customer && $customer->dni) {
            $billingData['cliente_doc_tipo'] = 'DNI';
            $billingData['cliente_doc_num']  = $customer->dni;
        }
        if ($tipo === 'factura' && empty($billingData['cliente_doc_num']) && $customer && $customer->ruc) {
            $billingData['cliente_doc_tipo'] = 'RUC';
            $billingData['cliente_doc_num']  = $customer->ruc;
        }
        if ($tipo === 'factura' && ($billingData['cliente_doc_tipo'] ?? '') !== 'RUC') {
            throw ValidationException::withMessages(['cliente_doc_tipo' => 'Para FACTURA el documento debe ser RUC.']);
        }

        // =====================================================
        // 4) Transacción principal
        // =====================================================
        return DB::transaction(function () use (
            $atencion, $tipo, $customer, $billingData, $allItems,
            $igvRate, &$sumBase, &$sumIgv, &$sumTotal,
            &$op_gravada, &$op_exonerada, &$op_inafecta, &$descuento, &$igv, &$total, $paymentData
        ) {
            // Numeración local solo para tickets
            $serie = $numero = null;
            if ($tipo === 'ticket') {
                $counter = DB::table('invoice_counters')->where('tipo', 'ticket')->lockForUpdate()->first();
                if ($counter) {
                    $serie  = $counter->serie;
                    $numero = $counter->next_number;
                    DB::table('invoice_counters')->where('id', $counter->id)->update(['next_number' => $counter->next_number + 1]);
                }
            }

            // Snapshots de cliente (compatibles con PHP 7.3)
            $customerId     = $customer ? $customer->id : null;
            $clienteNombre  = isset($billingData['cliente_nombre']) ? $billingData['cliente_nombre'] : ($customer ? $customer->nombre : null);
            $clienteDocTipo = isset($billingData['cliente_doc_tipo']) ? $billingData['cliente_doc_tipo'] : null;

            if (isset($billingData['cliente_doc_num'])) {
                $clienteDocNum = $billingData['cliente_doc_num'];
            } else {
                $clienteDocNum = $customer ? ($customer->ruc ?: ($customer->dni ?: null)) : null;
            }

            $clienteDireccion = isset($billingData['cliente_direccion']) ? $billingData['cliente_direccion'] : ($customer ? $customer->direccion : null);

            // Crear cabecera preliminar (totales se actualizan luego)
            $inv = new Invoice();
            $inv->fill([
                'atencion_id'       => $atencion->id,
                'customer_id'       => $customerId,
                'tipo'              => $tipo,
                'serie'             => $serie,
                'numero'            => $numero,
                'cliente_nombre'    => $clienteNombre,
                'cliente_doc_tipo'  => $clienteDocTipo,
                'cliente_doc_num'   => $clienteDocNum,
                'cliente_direccion' => $clienteDireccion,
                'moneda'            => 'PEN',
                'estado'            => 'emitido',
                'issue_date'        => now(),
                'extra'             => [
                    'igv_incluido' => true,  // marca para auditoría
                ],
            ]);
            $inv->save();

            // -------------------------
            // Detalle (precios con IGV)
            // -------------------------
            foreach ($allItems as $sel) {
                $cantidad     = (float)$sel['cantidad'];
                $precioConIgv = (float)$sel['precio_unit'];            // <-- CON IGV
                $valorUnit    = round($precioConIgv / (1 + $igvRate), 6); // sin IGV para SUNAT
                $subtotal     = round($valorUnit * $cantidad, 2);         // base línea
                $igvItem      = round($subtotal * $igvRate, 2);           // igv línea
                $totalItem    = round($precioConIgv * $cantidad, 2);      // con IGV

                $invoiceItem = InvoiceItem::create([
                    'invoice_id'      => $inv->id,
                    'comanda_item_id' => $sel['comanda_item_id'],
                    'product_id'      => $sel['product_id'],
                    'descripcion'     => $sel['nombre'],
                    'cantidad'        => $cantidad,
                    'unidad'          => 'NIU',
                    'valor_unitario'  => $valorUnit,
                    'precio_unitario' => $precioConIgv,
                    'subtotal'        => $subtotal,
                    'igv'             => $igvItem,
                    'total'           => $totalItem,
                    'afectacion'      => '10', // gravado - onerosa
                ]);

                ComandaItemLiquidacion::create([
                    'comanda_item_id' => $sel['comanda_item_id'],
                    'invoice_id'      => $inv->id,
                    'invoice_item_id' => $invoiceItem->id,
                    'qty'             => (int)$cantidad,
                    'monto'           => (float)$totalItem,
                ]);

                // Acumular brutos
                $sumBase  += $subtotal;
                $sumIgv   += $igvItem;
                $sumTotal += $totalItem; // con IGV
            }

            // -------------------------
            // Descuento global (con IGV)
            // -------------------------
            // $billingData['descuento'] puede venir como ['tipo'=>'porc'|'fijo','valor'=>float]
            $descuento = 0.0;
            if (!empty($billingData['descuento']) && is_array($billingData['descuento'])) {
                $tipoDesc = $billingData['descuento']['tipo'] ?? '';
                $valDesc  = (float)($billingData['descuento']['valor'] ?? 0);
                if ($tipoDesc === 'porc') {
                    $descuento = $sumTotal * ($valDesc / 100.0); // sobre total con IGV
                } elseif ($tipoDesc === 'fijo') {
                    $descuento = $valDesc;
                }
                if ($descuento < 0) $descuento = 0;
                if ($descuento > $sumTotal) $descuento = $sumTotal;
            }

            // Prorrateo del descuento entre base e IGV (por tasa)
            $descBase = round($descuento / (1 + $igvRate), 2);
            $descIgv  = round($descuento - $descBase, 2);

            // -------------------------
            // Propina (no gravada)
            // -------------------------
            // Se calcula sobre el consumo neto (total con IGV - descuento)
            $consumoNeto = $sumTotal - $descuento; // sigue incluyendo IGV
            if ($consumoNeto < 0) $consumoNeto = 0;

            $propina = 0.0;
            if (!empty($billingData['propina']) && is_array($billingData['propina'])) {
                $tipoProp = $billingData['propina']['tipo'] ?? '';
                $valProp  = (float)($billingData['propina']['valor'] ?? 0);
                if ($tipoProp === 'porc') {
                    $propina = $consumoNeto * ($valProp / 100.0);
                } elseif ($tipoProp === 'fijo') {
                    $propina = $valProp;
                }
                if ($propina < 0) $propina = 0;
            }

            // -------------------------
            // Totales cabecera
            // -------------------------
            $op_gravada   = max(0, round($sumBase - $descBase, 2));
            $igv          = max(0, round($sumIgv  - $descIgv,  2));
            $op_exonerada = 0.00; // si en futuro hay exoneradas, sumar aquí
            $op_inafecta  = round($propina, 2); // propina no gravada (visible y sumable)

            // total final a pagar = (base neta + igv neto + exonerada) + propina
            $total = round(($op_gravada + $igv + $op_exonerada) + $op_inafecta, 2);

            $inv->update([
                'op_gravada'   => $op_gravada,
                'op_exonerada' => $op_exonerada,
                'op_inafecta'  => $op_inafecta,
                'descuento'    => round($descuento, 2), // Monto total de dto (incluye IGV)
                'igv'          => $igv,
                'total'        => $total,
                'extra'        => array_merge($inv->extra ?? [], [
                    'sumBase_bruto'   => round($sumBase, 2),
                    'sumIgv_bruto'    => round($sumIgv, 2),
                    'sumTotal_bruto'  => round($sumTotal, 2),
                    'descBase'        => $descBase,
                    'descIgv'         => $descIgv,
                    'consumo_neto'    => round($consumoNeto, 2),
                ]),
            ]);

            // -------------------------
            // Pagos
            // -------------------------
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

            // -------------------------
            // Cierre de atención
            // -------------------------
            $existenPendientes = ComandaItem::whereHas('comanda', function ($q) use ($atencion) {
                $q->where('atencion_id', $atencion->id);
            })
                ->whereRaw('comanda_items.cantidad > COALESCE((SELECT SUM(l.qty) FROM comanda_item_liquidaciones l WHERE l.comanda_item_id = comanda_items.id), 0)')
                ->exists();

            if (!$existenPendientes) {
                $atencion->update(['estado' => 'cerrada']);
                if ($atencion->mesa_id) {
                    $atencion->mesa()->update(['estado' => 'libre']);
                }
            }

            return $inv;
        });
    }

}