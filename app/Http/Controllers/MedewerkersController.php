<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MedewerkersController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Hier wordt de token gebruikt om de rol van de gebruiker te bepalen
        $token = $request->input('token');

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            // Bij het aanmaken van User voor medewerker, gebruik token type voor juiste rol
            'role' => $token ? $token->type : 'medewerker', // Dit wordt automatisch 'medewerker' uit de invitation token
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['De opgegeven gegevens komen niet overeen met onze records.'],
            ]);
        }

        $user = Auth::user();

        return response()->json(['user' => $user]);
    }
}