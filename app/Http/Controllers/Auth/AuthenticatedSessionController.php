<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
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

        return redirect()->intended('/dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        \Log::info('🔥 LOGOUT TRIGGERED IN AuthenticatedSessionController', [
            'user_id' => $user ? $user->id : 'NO_USER',
            'user_name' => $user ? $user->name : 'NO_USER',
        ]);
        
        // Update the most recent login activity with logout time
        if ($user) {
            $latestActivity = \App\Models\LoginActivity::where('user_id', $user->id)
                ->whereNull('logged_out_at')
                ->latest('logged_in_at')
                ->first();
                
            if ($latestActivity) {
                $latestActivity->update(['logged_out_at' => now()]);
                \Log::info('🔥 LOGOUT TIME UPDATED', [
                    'activity_id' => $latestActivity->id,
                    'logged_out_at' => now(),
                ]);
            } else {
                \Log::warning('🔥 NO ACTIVE SESSION FOUND FOR LOGOUT', [
                    'user_id' => $user->id,
                ]);
            }
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
