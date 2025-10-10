<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductOptionsController extends Controller
{
    public function show(Product $product)
    {
        // Carga solo opciones activas y sus selecciones activas + el producto de la selección
        $product->load([
            'options' => function ($q) {
                $q->where('active', 1)
                    ->with(['selections' => function ($q) {
                        $q->where('active', 1)->with(['product:id,full_name,unit_price']);
                    }]);
            },
        ]);

        // Mapeo: si solo tienes 'quantity' en Option, lo usamos como min = max = quantity
        $groups = $product->options->map(function ($o) {
            $min = $o->min ?? $o->quantity ?? 0;  // si algún día agregas columna 'min'
            $max = $o->max ?? $o->quantity ?? 0;  // o 'max'; si no, exacto 'quantity'

            return [
                'option_id'   => (int) $o->id,
                'descripcion' => (string) $o->description,
                'tipo'        => (string) $o->type,     // 'picker' | 'addon' | 'texto'
                'min'         => (int) $min,
                'max'         => (int) $max,
                'items'       => $o->type === 'texto' ? [] : $o->selections->map(function ($s) {
                    return [
                        'selection_id' => (int) $s->id,
                        'product_id'   => (int) $s->product_id,
                        'name'         => (string) optional($s->product)->full_name,
                        'delta'        => (float) ($s->additional_price ?? 0),
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return response()->json([
            'ok'     => true,
            'groups' => $groups,
        ]);
    }
}
