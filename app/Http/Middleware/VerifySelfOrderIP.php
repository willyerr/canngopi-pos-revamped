<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySelfOrderIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only accept request from application order
        if($request->ip() !== env('SELF_ORDER_IP') && $request->ip() !== '127.0.0.1')
            return response()->json(['success' => false, 'message' => 'IP address denied'], 401);

        return $next($request);
    }
}
