<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class GuideMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('signin')->with('error', 'Bạn cần đăng nhập để truy cập trang này.');
        }

        if (!in_array(Auth::user()->role, ['tour_guide', 'admin'])) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
