<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShouldUseGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $type): Response
    {
        auth()->shouldUse($type);

        if ($type == 'student-jwt' && (auth()->payload()['role'] != 'student')) return response()->json(['error'=>'Unauthorized'], 401);
        if ($type == 'admin-jwt' && (auth()->payload()['role'] != 'admin')) return response()->json(['error'=>'Unauthorized'], 401);
        
        return $next($request);
    }
}
