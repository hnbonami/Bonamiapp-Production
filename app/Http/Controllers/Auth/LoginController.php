<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LoginActivity;

class LoginController extends Controller
{
    protected $redirectTo = '/dashboard';

    /**
     * Show the application's login form.
     */
    public function showLoginForm()
    {
        // Probeer eerst je oorspronkelijke login view
        if (view()->exists('login')) {
            return view('login');
        } elseif (view()->exists('auth.login')) {
            return view('auth.login');
        } else {
            // Fallback naar een simpele login view
            return view('auth.login');
        }
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Log the login activity
            $this->logLoginActivity($request, Auth::user());
            
            return redirect()->intended($this->redirectTo);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        \Log::info('🔥 LOGOUT TRIGGERED', [
            'user_id' => $user ? $user->id : 'NO_USER',
            'user_name' => $user ? $user->name : 'NO_USER',
        ]);
        
        // Update the most recent login activity with logout time
        if ($user) {
            $latestActivity = LoginActivity::where('user_id', $user->id)
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
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    private function logLoginActivity($request, $user)
    {
        $userAgent = $request->userAgent();

        \Log::info('🔥 LOGIN ACTIVITY CREATED', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'ip_address' => $request->ip(),
        ]);

        LoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'device' => $this->getDeviceType($userAgent),
            'browser' => $this->getBrowserName($userAgent),
            'platform' => $this->getPlatformName($userAgent),
            'location' => $this->getLocationFromIp($request->ip()),
            'status' => 'success',
            'logged_in_at' => now(),
        ]);
    }

    private function getDeviceType($userAgent)
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/Tablet/', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }

    private function getBrowserName($userAgent)
    {
        if (preg_match('/Chrome/', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            return 'Edge';
        } else {
            return 'Unknown';
        }
    }

    private function getPlatformName($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iOS/', $userAgent)) {
            return 'iOS';
        } else {
            return 'Unknown';
        }
    }

    private function getLocationFromIp($ip)
    {
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Localhost';
        }
        return 'Unknown';
    }
}