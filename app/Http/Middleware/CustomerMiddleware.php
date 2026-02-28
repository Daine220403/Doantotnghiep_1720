<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chưa đăng nhập
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Nếu không phải customer
        if (Auth::user()->role !== 'customer') {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
