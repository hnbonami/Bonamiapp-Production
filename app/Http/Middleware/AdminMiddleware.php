<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and is admin
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has admin role (adjust this logic based on your user model)
        if (!auth()->user()->is_admin && auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}