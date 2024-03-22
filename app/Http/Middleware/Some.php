<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Some
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // $response->header('access-control-expose-headers', 'Set-Cookie');
        $response->cookie('randomcookie', 'lorem ipsum dolor sit amet', 60*60, '/', 'localhost', null, false);
        // $response->withCookie(cookie('key', $value));
        return $response;
    }
}
