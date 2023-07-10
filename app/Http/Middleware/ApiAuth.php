<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the API token from the request header
        $token = $request->header('api_token');

        // Check if the token is valid
        if ($token && User::where('api_token', $token)->exists()) {
            // If yes, continue the request
            return $next($request);
        } else {
            // If no, return a 401 unauthorized response
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
