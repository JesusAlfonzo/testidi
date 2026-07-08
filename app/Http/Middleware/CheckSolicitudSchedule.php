<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class CheckSolicitudSchedule
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Si el usuario tiene permisos de aprobación o es Superadmin, tiene acceso total todos los días
        if ($user && ($user->hasRole('Superadmin') || $user->can('solicitudes_aprobar'))) {
            return $next($request);
        }

        // Obtener el día actual en el huso horario de Caracas
        $now = Carbon::now('America/Caracas');
        $dayOfWeek = $now->dayOfWeek;

        // Martes (2) o Miércoles (3)
        if ($dayOfWeek === Carbon::TUESDAY || $dayOfWeek === Carbon::WEDNESDAY) {
            return $next($request);
        }

        // De lo contrario, bloquear acceso
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'El módulo de solicitudes solo está disponible para solicitantes los días martes y miércoles.'
            ], 403);
        }

        return redirect()->route('home')->with('error', 'El módulo de solicitudes solo está disponible para solicitantes los días martes y miércoles.');
    }
}
