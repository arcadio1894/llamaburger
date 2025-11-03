<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Distributor;

class EnsureDistributor
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Si el usuario tiene rol de "administrador", permitir acceso total
        if ($user->hasRole('administrador')) {
            // Seteamos null o un "distribuidor global" para que el controlador sepa
            $request->attributes->set('is_admin', true);
            return $next($request);
        }

        // Si no es admin, verificar que sea un distribuidor activo
        $distributor = Distributor::where('user_id', $user->id)->first();
        if (!$distributor || !$distributor->activo) {
            abort(403, 'No tienes acceso de distribuidor.');
        }

        // Adjuntamos datos al request
        $request->attributes->set('is_admin', false);
        $request->attributes->set('distributor', $distributor);

        return $next($request);
    }
}