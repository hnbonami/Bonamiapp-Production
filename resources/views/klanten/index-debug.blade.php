{{-- 🔥 EMERGENCY DEBUG VIEW - Simpele klantenlijst zonder fancy features --}}
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEBUG - Klantenlijst</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-3xl font-bold mb-6">🔧 DEBUG - Klantenlijst</h1>
            
            <div class="mb-4 p-4 bg-blue-100 border border-blue-400 rounded">
                <p class="font-bold">✅ Controller werkt!</p>
                <p>Aantal klanten: {{ $klanten->count() }}</p>
                <p>User: {{ auth()->user()->name ?? 'Onbekend' }}</p>
                <p>Role: {{ auth()->user()->role ?? 'Onbekend' }}</p>
                <p>Email: {{ auth()->user()->email ?? 'Onbekend' }}</p>
            </div>

            @if($klanten->count() === 0)
                <div class="p-4 bg-yellow-100 border border-yellow-400 rounded">
                    <p>⚠️ Geen klanten gevonden</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Naam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefoon</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($klanten as $klant)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">{{ $klant->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $klant->voornaam }} {{ $klant->naam }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $klant->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $klant->telefoonnummer ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded {{ $klant->status === 'Actief' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $klant->status ?? 'Onbekend' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="mt-6 space-x-4">
                <a href="{{ route('klanten.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    ➕ Nieuwe Klant
                </a>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    ← Terug naar Dashboard
                </a>
            </div>
        </div>
    </div>
</body>
</html>
