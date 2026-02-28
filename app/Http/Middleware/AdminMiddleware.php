<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Nếu chưa đăng nhập
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Nếu không phải admin
        if (Auth::user()->role == 'customer') {
            return redirect()->route('home')->with('info', 'Bạn không có quyền truy cập trang này.');
        }
        return $next($request);
    }
}
