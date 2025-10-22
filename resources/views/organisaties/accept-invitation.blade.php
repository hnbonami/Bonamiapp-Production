<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uitnodiging Accepteren - Bonami Sportcoaching</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo/Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">🎉 Welkom!</h1>
                <p class="text-gray-600">Bonami Sportcoaching Platform</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-xl shadow-lg p-8">
                <div class="mb-6" style="background-color: #c8e1eb; padding: 20px; border-radius: 8px; text-align: center;">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $organisatie->naam }}</h2>
                    <p class="text-gray-700 mt-2">{{ $organisatie->email }}</p>
                </div>

                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <p class="text-gray-600 mb-6">
                    Maak een admin account aan om te beginnen met het Bonami Sportcoaching platform.
                </p>

                <form action="{{ route('organisatie.process-invitation', ['token' => $token]) }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Naam -->
                    <div>
                        <label for="naam" class="block text-sm font-semibold text-gray-700 mb-2">Volledige Naam *</label>
                        <input type="text" name="naam" id="naam" required value="{{ old('naam') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Jan Janssen">
                        @error('naam')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email Adres *</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="jan@organisatie.nl">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Wachtwoord -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Wachtwoord *</label>
                        <input type="password" name="password" id="password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Minimaal 8 tekens">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Wachtwoord Bevestiging -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Bevestig Wachtwoord *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Herhaal wachtwoord">
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full py-3 text-gray-900 font-bold rounded-lg transition hover:opacity-90" style="background-color: #c8e1eb;">
                        ✅ Account Aanmaken en Beginnen
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Heb je al een account? 
                    <a href="{{ route('login') }}" class="font-semibold hover:underline" style="color: #08474f;">Log hier in</a>
                </p>
            </div>

            <p class="text-center text-xs text-gray-500 mt-6">
                © {{ date('Y') }} Bonami Sportcoaching. Alle rechten voorbehouden.
            </p>
        </div>
    </div>
</body>
</html>
