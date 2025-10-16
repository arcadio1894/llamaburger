<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Models\Category;
use App\Models\Comanda;
use App\Models\Mesa;
use App\Models\Mozo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AtencionController extends Controller
{
    // POST /mesas/{mesa}/abrir
    public function abrir(Request $request, Mesa $mesa)
    {
        $data = $request->validate([
            'mozo_id'   => ['required','integer', Rule::exists('mozos','id')->whereNull('deleted_at')],
            'personas'  => ['required','integer','min:1','max:50'],
            'comentario'=> ['nullable','string','max:500'],
        ]);

        // Evitar 2 atenciones abiertas sobre la misma mesa
        $existsOpen = Atencion::whereNotNull('mesa_id')
            ->where('mesa_id', $mesa->id)
            ->where('estado','abierta')
            ->exists();

        if ($existsOpen) {
            return response()->json([
                'ok'=>false,
                'msg'=>'La mesa ya tiene una atención abierta.'
            ], 409);
        }

        try {
            $atencion = null;
            DB::transaction(function () use (&$atencion, $mesa, $data) {
                // 1) crear atención
                $atencion = Atencion::create([
                    'mesa_id'   => $mesa->id,
                    'mozo_id'   => $data['mozo_id'],
                    'personas'  => $data['personas'],
                    'comentario'=> $data['comentario'] ?? null,
                    'estado'    => 'abierta',
                    'opened_at' => now(),
                ]);

                // 2) crear comanda #1 por defecto
                Comanda::create([
                    'atencion_id' => $atencion->id,
                    'numero'      => 1,
                    'estado'      => 'borrador',
                ]);

                // 3) marcar mesa ocupada (opcional)
                $mesa->update(['estado' => 'ocupada']);
            });

            return response()->json([
                'ok'           => true,
                'msg'          => 'Mesa abierta correctamente.',
                'atencion_id'  => $atencion->id,
                'redirect_url' => route('atenciones.comanda.show', [$atencion->id, 1]),
            ], 201);

        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'ok'=>false,
                'msg'=>'No se pudo abrir la mesa.'
            ], 500);
        }
    }

    // GET /atenciones/{atencion}
    public function show(Atencion $atencion)
    {
        // Si el usuario es mozo, permitir solo si es el asignado
        if (auth()->check()) {
            $mozoActual = Mozo::where('user_id', auth()->id())
                ->where('activo', 1)
                ->first();

            if ($mozoActual && $atencion->mozo_id !== $mozoActual->id) {
                abort(403, 'Acceso restringido a esta mesa.');
            }
        }

        $atencion->load([
            'mesa',
            'mozo',
            'comandas' => function($q) {
                $q->orderBy('numero');
            }
        ]);

        return view('atenciones.show', compact('atencion'));
    }

    // GET /dashboard/atenciones/{atencion}/comanda/{numero}
    public function showComanda(Atencion $atencion, $numero)
    {
        // Autorización: si es mozo, solo su mesa
        if (auth()->check()) {
            $mozoActual = Mozo::where('user_id', auth()->id())
                ->where('activo', 1)->first();

            if ($mozoActual && $atencion->mozo_id !== $mozoActual->id) {
                abort(403, 'Acceso restringido a esta mesa.');
            }
        }

        // Cargar comanda solicitada + todas para tabs
        $atencion->load(['mesa','mozo']);
        $comandas = $atencion->comandas()->orderBy('numero')->get();
        $comanda  = $atencion->comandas()->where('numero', $numero)->firstOrFail();

        // Puedes eager-load items aquí si quieres mostrarlos
        $comanda->load(['items' => function($q){ $q->orderBy('id'); }]);

        // 🔽 Construimos payloads compatibles con PHP 7.3
        $itemsPayload = $comanda->items->map(function($it){
            return [
                'server_id'  => (int) $it->id,
                'product_id' => (int) $it->product_id,
                'name'       => (string) $it->nombre,
                'price'      => (float) $it->precio_unit,
                'qty'        => (int) $it->cantidad,
                'opciones'   => $it->opciones ?? null,  // 🔰
                'has_options'=> $it->product ? $it->product->has_options : (!empty($it->opciones)), //
            ];
        })->values()->all();

        $totalsPayload = [
            'subtotal'  => (float) $comanda->subtotal,
            'descuento' => (float) ($comanda->descuento ?? 0),
            'igv'       => (float) $comanda->igv,
            'total'     => (float) $comanda->total,
        ];

        return view('atenciones.show', compact(
            'atencion','comandas','comanda','itemsPayload','totalsPayload'
        ));
    }

    // POST /atenciones/{atencion}/cerrar
    public function cerrar(Request $request, Atencion $atencion)
    {
        try {
            DB::transaction(function () use ($atencion) {
                $atencion->update([
                    'estado'    => 'cerrada',
                    'closed_at' => now(),
                ]);
                // liberar mesa
                $atencion->mesa()->update(['estado' => 'libre']);
            });

            return response()->json(['ok'=>true,'msg'=>'Mesa desocupada.','redirect_url'=>route('salas.index')]);

        } catch (\Throwable $e) {
            report($e);
            return response()->json(['ok'=>false,'msg'=>'No se pudo cerrar la atención.'], 500);
        }
    }

    public function checkAcceso(Mesa $mesa)
    {
        // Buscar atención abierta de esa mesa
        $atencion = Atencion::where('mesa_id', $mesa->id)
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();

        // Identificar mozo actual (si el usuario está vinculado)
        $currentMozo = null;
        if (auth()->check()) {
            $currentMozo = Mozo::where('user_id', auth()->id())
                ->where('activo',1)->first();
        }

        if (!$atencion) {
            // Mesa libre: se puede abrir
            return response()->json([
                'ok'          => true,
                'estado'      => 'libre',
                'allow_open'  => true,
            ]);
        }

        // Mesa ocupada: permitir acceso solo al mozo asignado (o a un admin si lo manejas)
        $canAccess = $currentMozo && $currentMozo->id === (int) $atencion->mozo_id;

        return response()->json([
            'ok'            => true,
            'estado'        => 'ocupada',
            'atencion_id'   => $atencion->id,
            'mozo_id'       => $atencion->mozo_id,
            'mozo_nombre'   => optional($atencion->mozo)->nombre,
            'can_access'    => $canAccess,
            'redirect_url'  => $canAccess ? route('atenciones.show', $atencion->id) : null,
        ]);
    }

    public function productos()
    {
        // Obtener categorías activas con sus productos
        /*$categories = Category::with(['products' => function ($q) {
            $q->where('enable_status', 1)
                ->with(['productTypes.type' => function ($t) {
                    $t->where('active', 1);
                }]);
        }])
            ->where('enable_status', 1)
            ->orderBy('name')
            ->get();

        return response()->json([
            'ok' => true,
            'categories' => $categories,
        ]);*/
        $categories = Category::with([
            'products' => function($q){
                $q->withCount(['options as options_count' => function($q){ $q->where('active',1); }]);
                // si vas a abrir modal con un fetch aparte, basta el flag:
                $q->with(['options' => function($q) {
                    $q->where('active',1)->with(['selections' => function($q){
                        $q->where('active',1)->with('product:id,full_name,unit_price,image');
                    }]);
                }]);
            }
        ])->get();

        // Respuesta (ejemplo)
        return response()->json([
            'ok' => true,
            'categories' => $categories->map(function($cat){
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'products' => $cat->products->map(function($p){
                        return [
                            'id'           => $p->id,
                            'name'         => $p->name,
                            'full_name'    => $p->full_name,
                            'unit_price'   => $p->unit_price,
                            'image'        => $p->image,
                            'has_options'  => $p->has_options, // 🔰 clave para el front
                            // opcional: mandar toda la estructura si prefieres
                            'options'      => $p->options->map(function($o){
                                return [
                                    'id'          => $o->id,
                                    'description' => $o->description, // título del grupo
                                    'quantity'    => (int) $o->quantity, // min/max exacto (ver abajo)
                                    'type'        => $o->type, // 'picker' | 'addon' | 'texto' (ver mapeo)
                                    'selections'  => $o->selections->map(function($s){
                                        return [
                                            'id'               => $s->id,
                                            'product_id'       => $s->product_id,
                                            'name'             => $s->product->name ?? '',
                                            'delta'            => (float) ($s->additional_price ?? 0),
                                            'image'            => $s->product->image ?? null,
                                        ];
                                    })->values(),
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }
}
