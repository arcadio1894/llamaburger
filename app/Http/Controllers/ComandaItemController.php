<?php

namespace App\Http\Controllers;

use App\Models\Comanda;
use App\Models\ComandaItem;
use App\Models\Mozo;
use App\Models\Product;
use App\Models\Selection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComandaItemController extends Controller
{
    // ===== Seguridad básica: el mozo asignado a la atención puede operar la comanda
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

    // ===== Recalcular totales de la comanda (precios incluyen IGV)
    protected function recalc(Comanda $comanda)
    {
        $items = $comanda->items()->get();

        $subtotal = 0.0;
        foreach ($items as $it) {
            $subtotal += $it->cantidad * $it->precio_unit; // precio_unit YA incluye IGV
        }

        $descuento = 0.0; // ajusta si manejas descuentos
        $gravado   = max($subtotal - $descuento, 0);

        // IGV incluido → base = gravado / 1.18 ; igv = gravado - base
        $base  = round($gravado / 1.18, 2);
        $igv   = round($gravado - $base, 2);
        $total = $gravado;

        $comanda->update([
            'subtotal'  => $base,
            'descuento' => $descuento,
            'igv'       => $igv,
            'total'     => $total,
        ]);

        return [
            'subtotal'  => (float)$base,
            'descuento' => (float)$descuento,
            'igv'       => (float)$igv,
            'total'     => (float)$total,
        ];
    }

    // ===== Helpers nuevos =====
    /** Decodifica JSON de opciones enviado por el front */
    protected function decodeSnapshot(?string $json): ?array
    {
        if (!$json) return null;
        $arr = json_decode($json, true);
        return is_array($arr) ? $arr : null;
    }

    /**
     * Precio unitario = unit_price del producto + sum(additional_price) de selections elegidas.
     * Espera snapshot como:
     * { grupos: [ { option_id, tipo, selecciones:[{ selection_id, ... }] }, { tipo:"texto", valor:"..." } ] }
     */
    protected function calcularPrecioUnit(Product $product, ?array $snapshot): float
    {
        $base = (float) ($product->unit_price ?? 0);

        if (!$snapshot || empty($snapshot['grupos'])) {
            return round($base, 2);
        }

        $selectionIds = [];
        foreach ($snapshot['grupos'] as $g) {
            if (!empty($g['selecciones'])) {
                foreach ($g['selecciones'] as $s) {
                    if (isset($s['selection_id'])) {
                        $selectionIds[] = (int) $s['selection_id'];
                    }
                }
            }
        }

        if (empty($selectionIds)) {
            return round($base, 2);
        }

        // Traigo deltas en 1 sola consulta
        $deltas = Selection::whereIn('id', $selectionIds)->pluck('additional_price', 'id');

        $extra = 0.0;
        foreach ($selectionIds as $id) {
            $extra += (float) ($deltas[$id] ?? 0);
        }

        return round($base + $extra, 2);
    }

    /** Payload homogéneo para el front */
    protected function itemPayload(ComandaItem $item): array
    {
        return [
            'id'         => (int) $item->id,
            'product_id' => (int) $item->product_id,
            'name'       => (string) $item->nombre,
            'price'      => (float) $item->precio_unit,
            'qty'        => (int) $item->cantidad,
            'opciones'   => $item->opciones ?? null,
        ];
    }

    // ===== POST /comandas/{comanda}/items  (crear)
    // Regla: si VIENEN opciones → SIEMPRE crea una línea nueva (no agrupar).
    //        si NO hay opciones → agrupa por product_id como antes.
    public function store(Request $request, Comanda $comanda)
    {
        $this->authorizeComanda($comanda);

        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'cantidad'   => ['nullable','integer','min:1'],
            'opciones'   => ['nullable','string'], // JSON snapshot
        ]);
        $qty      = max(1, (int) ($data['cantidad'] ?? 1));
        $snapshot = $this->decodeSnapshot($data['opciones'] ?? null);

        try {
            $payload = null;

            DB::transaction(function () use (&$payload, $comanda, $data, $qty, $snapshot) {

                $product = Product::findOrFail($data['product_id']);
                $precio  = $this->calcularPrecioUnit($product, $snapshot);

                if ($snapshot) {
                    // Con opciones → SIEMPRE crear nueva línea
                    $item = ComandaItem::create([
                        'comanda_id'  => $comanda->id,
                        'product_id'  => $product->id,
                        'nombre'      => $product->full_name ?? $product->name ?? ('Prod '.$product->id),
                        'precio_unit' => $precio,
                        'cantidad'    => $qty,
                        'estado'      => 'pendiente',
                        'opciones'    => $snapshot, // se castea a json en el modelo
                    ]);
                } else {
                    // Sin opciones → agrupar por product_id como antes
                    $item = $comanda->items()->where('product_id', $product->id)
                        ->whereNull('opciones') // solo agrupo con ítems SIN opciones
                        ->first();

                    if ($item) {
                        $item->update(['cantidad' => $item->cantidad + $qty]);
                    } else {
                        $item = ComandaItem::create([
                            'comanda_id'  => $comanda->id,
                            'product_id'  => $product->id,
                            'nombre'      => $product->full_name ?? $product->name ?? ('Prod '.$product->id),
                            'precio_unit' => $precio,
                            'cantidad'    => $qty,
                            'estado'      => 'pendiente',
                            'opciones'    => null,
                        ]);
                    }
                }

                $totals = $this->recalc($comanda);

                $payload = [
                    'ok'     => true,
                    'item'   => $this->itemPayload($item),
                    'totals' => $totals,
                ];
            });

            return response()->json($payload, 201);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo agregar el producto.'], 500);
        }
    }

    // ===== POST /comanda-items/{item}/update  (editar opciones y/o cantidad)
    public function update(Request $request, ComandaItem $item)
    {
        // asegurar acceso
        $comanda = $item->comanda()->with('atencion')->firstOrFail();
        $this->authorizeComanda($comanda);

        $data = $request->validate([
            'cantidad' => ['nullable','integer','min:1'],
            'opciones' => ['nullable','string'], // JSON snapshot
        ]);

        $qty      = (int) ($data['cantidad'] ?? $item->cantidad);
        $snapshot = $this->decodeSnapshot($data['opciones'] ?? null);
        if (!$snapshot) {
            // si no enviaron, preservamos las actuales (puede ser null)
            $snapshot = $item->opciones;
        }

        try {
            $resp = null;

            DB::transaction(function () use (&$resp, $item, $comanda, $qty, $snapshot) {

                $product    = $item->product()->firstOrFail();
                $precioUnit = $this->calcularPrecioUnit($product, $snapshot);

                $item->update([
                    'cantidad'    => $qty,
                    'opciones'    => $snapshot,   // puede ser null para simples
                    'precio_unit' => $precioUnit,
                ]);

                $totals = $this->recalc($comanda);

                $resp = [
                    'ok'     => true,
                    'item'   => $this->itemPayload($item->fresh()),
                    'totals' => $totals,
                ];
            });

            return response()->json($resp, 200);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo actualizar el item.'], 500);
        }
    }

    // ===== POST /comanda-items/{item}/inc  (delta +1 / -1)
    public function increment(Request $request, ComandaItem $item)
    {
        // asegurar acceso
        $comanda = $item->comanda()->with('atencion')->firstOrFail();
        $this->authorizeComanda($comanda);

        $data = $request->validate([
            'delta' => ['required','integer','in:-1,1'],
        ]);

        try {
            $resp = null;

            DB::transaction(function () use (&$resp, $item, $comanda, $data) {
                $newQty = $item->cantidad + $data['delta'];

                if ($newQty <= 0) {
                    $item->delete();
                    $removed = true;
                } else {
                    $item->update(['cantidad' => $newQty]);
                    $removed = false;
                }

                $totals = $this->recalc($comanda);

                $resp = [
                    'ok'      => true,
                    'removed' => $removed,
                    'qty'     => $removed ? 0 : (int)$item->cantidad,
                    'totals'  => $totals,
                ];
            });

            return response()->json($resp, 200);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo actualizar el item.'], 500);
        }
    }

    // (tienes también destroy si lo necesitas; lo dejé fuera porque no lo mostraste en el snippet)
}
