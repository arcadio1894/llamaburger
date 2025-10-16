<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Models\ComandaItem;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function create(Atencion $atencion)
    {
        $comandaIds = $atencion->comandas()->pluck('id');

        $items = ComandaItem::with('product')
            ->whereIn('comanda_id', $comandaIds)
            ->orderBy('id','asc')
            ->get()
            ->map(function ($it) {
                $pagado = (int) ($it->pagado_cant ?? 0); // si no existe, será 0
                $it->restante = max(0, (int)$it->cantidad - $pagado);
                return $it;
            })
            ->filter(function ($it) {
                return $it->restante > 0;
            })
            ->values();

        // ⚠️ No usar \App\Cliente (no existe aún)
        $clientes = []; // más adelante lo llenaremos desde la tabla clientes

        $payment_methods = PaymentMethod::active()->orderBy('id', 'desc')->get();

        return view('pago.create', [
            'atencion'  => $atencion,
            'items'     => $items,
            'esExterno' => $atencion->tipo === 'externo',
            'clientes'  => $clientes, // <- se envía vacío por ahora
            'payment_methods' => $payment_methods
        ]);
    }


    /*public function store(Request $req)
    {
        // Valida payload
        $data = $req->validate([
            'atencion_id'     => 'required|integer',
            'metodo'          => 'required|in:efectivo,tarjeta,transferencia,yape,plin',
            'cliente_id'      => 'nullable|integer',
            'descuento_tipo'  => 'nullable|in:porc,fijo',
            'descuento_val'   => 'nullable|numeric|min:0',
            'propina_tipo'    => 'nullable|in:porc,fijo',
            'propina_val'     => 'nullable|numeric|min:0',
            'items'           => 'required|array',
            'items.*.id'      => 'required|integer',
            'items.*.qty'     => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($data) {
            // Crea el pago
            $pago = \App\Pago::create([
                'atencion_id'    => $data['atencion_id'],
                'cliente_id'     => $data['cliente_id'] ?? null,
                'metodo'         => $data['metodo'],
                'descuento_tipo' => $data['descuento_tipo'] ?? null,
                'descuento_val'  => $data['descuento_val']  ?? 0,
                'propina_tipo'   => $data['propina_tipo']   ?? null,
                'propina_val'    => $data['propina_val']    ?? 0,
                'monto_total'    => 0, // se recalcula abajo
            ]);

            $total = 0;
            foreach ($data['items'] as $linea) {
                $ci = \App\ComandaItem::findOrFail($linea['id']);
                $qty = (int)$linea['qty'];

                // Precio unitario “congelado” al momento del pago:
                $precio = (float)$ci->precio_unit;
                $subtotal = $precio * $qty;
                $total += $subtotal;

                $pago->items()->create([
                    'comanda_item_id' => $ci->id,
                    'cantidad'        => $qty,
                    'precio_unit'     => $precio,
                    'subtotal'        => $subtotal,
                ]);

                // Marcar cantidad pagada (si llevas un acumulado en comanda_items)
                $ci->increment('pagado_cant', $qty);
            }

            // Aplica descuento y propina
            $desc = 0;
            if (($data['descuento_tipo'] ?? null) === 'porc') $desc = $total * (float)$data['descuento_val'] / 100;
            if (($data['descuento_tipo'] ?? null) === 'fijo') $desc = (float)$data['descuento_val'];
            $desc = max(0, min($desc, $total));

            $prop = 0;
            if (($data['propina_tipo'] ?? null) === 'porc') $prop = ($total - $desc) * (float)$data['propina_val'] / 100;
            if (($data['propina_tipo'] ?? null) === 'fijo') $prop = (float)$data['propina_val'];

            $pago->update([
                'monto_total' => round(($total - $desc + $prop), 2),
            ]);
        });

        return redirect()->route('pagos.create', $req->atencion_id)->with('ok','Pago registrado');
    }*/
}
