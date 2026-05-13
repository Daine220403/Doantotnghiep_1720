<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Redirect dựa trên role của user
        $user = Auth::user();
        $redirectRoute = 'admin.index';

        if ($user) {
            switch ($user->role) {
                case 'tour_guide':
                    $redirectRoute = 'guide.dashboard';
                    break;
                case 'staff':
                    $redirectRoute = 'staff.dashboard';
                    break;
                case 'staff_manager':
                    $redirectRoute = 'staff_manager.dashboard';
                    break;
                case 'tour_manager':
                    $redirectRoute = 'tour_manager.dashboard';
                    break;
                case 'admin':
                default:
                    $redirectRoute = 'admin.index';
                    break;
            }
        }

        return redirect()->intended(route($redirectRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Dang xuat thanh cong!');
    }
}
