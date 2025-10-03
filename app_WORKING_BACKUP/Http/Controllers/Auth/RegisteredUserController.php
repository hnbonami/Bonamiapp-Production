<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountCreatedMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // role may be omitted in tests or for self-registration; default to 'klant'
            'role' => ['sometimes', 'in:klant,medewerker,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->input('role', 'klant'),
        ]);

        // Mail versturen met login en wachtwoord
        $loginUrl = url('/login');
        Mail::to($user->email)->send(new AccountCreatedMail($user->name, $user->email, $request->password, $loginUrl));

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))->with('success', 'Admin toegevoegd en loginmail verzonden!');
    }
}
