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
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();
        if ($user->role === 'admin' || $user->role === 'coach') {
            return redirect()->intended(route('dashboard', absolute: false));
        } elseif ($user->role === 'klant') {
            // Zoek de klant op basis van e-mail
            $klant = \App\Models\Klant::where('email', $user->email)->first();
            if ($klant) {
                return redirect()->intended(route('klanten.show', ['klanten' => $klant->id]));
            } else {
                return redirect('/');
            }
        } else {
            return redirect('/');
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
