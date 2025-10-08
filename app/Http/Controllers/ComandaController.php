<?php

namespace App\Http\Controllers;

use App\Models\Atencion;
use App\Models\Comanda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComandaController extends Controller
{
    public function getOrCreateBorrador(Request $request, Atencion $atencion)
    {
        // podrías validar permisos del mozo aquí
        $comanda = $atencion->comandas()
            ->where('estado','borrador')
            ->orderBy('numero','desc')
            ->first();

        if (!$comanda) {
            // siguiente número
            $nextN = (int) ($atencion->comandas()->max('numero') ?? 0) + 1;
            $comanda = DB::transaction(function() use ($atencion, $nextN) {
                return Comanda::create([
                    'atencion_id' => $atencion->id,
                    'numero'      => $nextN,
                    'estado'      => 'borrador',
                ]);
            });
        }

        return response()->json(['ok'=>true, 'comanda'=>$comanda]);
    }

    public function createNext(Request $request, Atencion $atencion)
    {
        $nextN = (int) ($atencion->comandas()->max('numero') ?? 0) + 1;

        $comanda = DB::transaction(function() use ($atencion, $nextN) {
            return Comanda::create([
                'atencion_id' => $atencion->id,
                'numero'      => $nextN,
                'estado'      => 'borrador',
            ]);
        });

        // Si quieres JSON (AJAX) devuelve JSON; si la crearás con botón normal, redirige:
        if ($request->wantsJson()) {
            return response()->json(['ok'=>true, 'comanda'=>$comanda], 201);
        }

        return redirect()->route('atenciones.comanda.show', [$atencion->id, $comanda->numero])
            ->with('success', "Comanda #{$comanda->numero} creada.");
    }
}
