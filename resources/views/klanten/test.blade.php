<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Klanten Test View</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto p-8">
        <div class="bg-white rounded-lg shadow-2xl p-8 mb-6">
            <h1 class="text-4xl font-bold text-indigo-600 mb-2">🎯 KLANTEN TEST VIEW</h1>
            <p class="text-gray-600 text-lg">Dit bewijst dat de controller werkt!</p>
        </div>

        <div class="bg-white rounded-lg shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    📊 Gevonden: <span class="text-indigo-600">{{ $klanten->count() }}</span> klanten
                </h2>
                <div class="text-sm text-gray-500">
                    ✅ Controller: <strong>KlantController</strong>
                </div>
            </div>

            @if($klanten && $klanten->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-indigo-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Naam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-indigo-700 uppercase tracking-wider">Telefoon</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($klanten as $klant)
                                <tr class="hover:bg-indigo-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold">
                                                {{ substr($klant->voornaam ?? 'K', 0, 1) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $klant->voornaam ?? '' }} {{ $klant->naam ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $klant->email ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">{{ $klant->telefoonnummer ?? '-' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">🤷‍♂️</div>
                    <p class="text-xl text-gray-600">Geen klanten gevonden</p>
                </div>
            @endif
        </div>

        <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded">
            <p class="text-sm text-yellow-800">
                <strong>⚠️ LET OP:</strong> Dit is een test view zonder <code>&lt;x-app-layout&gt;</code> component. 
                Als je dit ziet, werkt de controller perfect en is het probleem in de layout!
            </p>
        </div>
    </div>
</body>
</html>
