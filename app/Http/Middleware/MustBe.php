<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Arr;

class MustBe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        // var_dump($roles); return null;
        if (in_array('admin', $roles)) {
            if (!$user->hasRole('admin') && !$user->hasRole('super_admin')) return response()->json(['error'=>'Unauthorized'], 401);
        } else if (!$user->hasRole($roles)) return response()->json(['error'=>'Unauthorized'], 401);
        
        
        return $next($request);
    }
}
