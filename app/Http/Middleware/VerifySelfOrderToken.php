<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySelfOrderToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {        
        $token = $request->bearerToken();
        if(!$token)
            return response()->json(['success' => false, 'message' => 'Token is required'], 401);

        if($token !== env('SELF_ORDER_TOKEN'))
            return response()->json(['success' => false, 'message' => 'Invalid token'], 401);

        return $next($request);
    }
}
