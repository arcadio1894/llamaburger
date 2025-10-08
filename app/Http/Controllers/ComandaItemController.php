<?php

namespace App\Http\Controllers;

use App\Models\Comanda;
use App\Models\ComandaItem;
use App\Models\Mozo;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComandaItemController extends Controller
{
    // Seguridad básica: el mozo asignado a la atención puede operar la comanda
    protected function authorizeComanda(Comanda $comanda)
    {
        if (auth()->check()) {
            $mozo = Mozo::where('user_id', auth()->id())->where('activo', 1)->first();
            if ($mozo && $comanda->atencion->mozo_id !== $mozo->id) {
                abort(403, 'Acceso restringido.');
            }
        }
    }

    // Recalcular totales de la comanda
    protected function recalc(Comanda $comanda)
    {
        $items = $comanda->items()->get();

        // Subtotal = suma de precios finales (IGV incluido)
        $subtotal = 0;
        foreach ($items as $it) {
            $subtotal += $it->cantidad * $it->precio_unit; // precio_unit YA incluye IGV
        }

        $descuento = 0.0; // si aplicas descuentos, descuéntalo del subtotal primero
        $gravado   = max($subtotal - $descuento, 0);

        // IGV incluido → base = gravado / 1.18 ; igv = gravado - base
        $base = round($gravado / 1.18, 2);
        $igv  = round($gravado - $base, 2);
        $total = $gravado; // como los precios ya incluyen IGV, total = gravado

        $comanda->update([
            'subtotal'  => $base,   // puedes guardar 'base' como subtotal "sin IGV" si prefieres
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

    // POST /comandas/{comanda}/items  (crear o sumar +1)
    public function store(Request $request, Comanda $comanda)
    {
        $this->authorizeComanda($comanda);

        $data = $request->validate([
            'product_id' => ['required','integer','exists:products,id'],
            'cantidad'   => ['nullable','integer','min:1'], // opcional, default 1
        ]);
        $qty = $data['cantidad'] ?? 1;

        try {
            $payload = null;
            DB::transaction(function () use (&$payload, $comanda, $data, $qty) {

                // Si ya existe fila del mismo producto: sumamos
                $item = $comanda->items()->where('product_id', $data['product_id'])->first();

                if ($item) {
                    $item->update(['cantidad' => $item->cantidad + $qty]);
                } else {
                    // Traer producto para nombre/precio
                    $product = Product::findOrFail($data['product_id']);
                    $precio  = (float) $product->unit_price;

                    $item = ComandaItem::create([
                        'comanda_id'  => $comanda->id,
                        'product_id'  => $product->id,
                        'nombre'      => $product->full_name ?? $product->name ?? ('Prod '.$product->id),
                        'precio_unit' => $precio,
                        'cantidad'    => $qty,
                        'notas'       => null,
                    ]);
                }

                $totals = $this->recalc($comanda);

                $payload = [
                    'ok'    => true,
                    'item'  => [
                        'id'         => $item->id,          // server_id
                        'product_id' => $item->product_id,
                        'name'       => $item->nombre,
                        'price'      => (float)$item->precio_unit,
                        'qty'        => (int)$item->cantidad,
                    ],
                    'totals' => $totals,
                ];
            });

            return response()->json($payload, 201);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo agregar el producto.'], 500);
        }
    }

    // POST /comanda-items/{item}/inc  (delta +1 / -1)
    public function increment(Request $request, ComandaItem $item)
    {
        // asegurar que el usuario tiene acceso a la comanda
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
}
