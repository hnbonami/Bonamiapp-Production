@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Testzadel Details</h1>
        <div class="flex gap-3">
            <a href="{{ route('testzadels.edit', $testzadel) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                ✏️ Bewerken
            </a>
            <a href="{{ route('testzadels.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                ← Terug naar overzicht
            </a>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Testzadel Informatie -->
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">Testzadel Informatie</h3>
                    <div class="space-y-3">
                        <div><strong>Merk:</strong> {{ $testzadel->zadel_merk }}</div>
                        <div><strong>Model:</strong> {{ $testzadel->zadel_model }}</div>
                        @if($testzadel->zadel_type)
                            <div><strong>Type:</strong> {{ $testzadel->zadel_type }}</div>
                        @endif
                        @if($testzadel->zadel_breedte)
                            <div><strong>Breedte:</strong> {{ $testzadel->zadel_breedte }}mm</div>
                        @endif
                        @if($testzadel->zadel_beschrijving)
                            <div><strong>Beschrijving:</strong> {{ $testzadel->zadel_beschrijving }}</div>
                        @endif
                    </div>
                </div>

                <!-- Klant Informatie -->
                <div>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">Klant Informatie</h3>
                    <div class="space-y-3">
                        <div><strong>Naam:</strong> {{ $testzadel->klant->voornaam }} {{ $testzadel->klant->naam }}</div>
                        <div><strong>Email:</strong> {{ $testzadel->klant->email }}</div>
                        @if($testzadel->klant->telefoon)
                            <div><strong>Telefoon:</strong> {{ $testzadel->klant->telefoon }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Status Informatie -->
            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900">Status & Datums</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="text-sm text-gray-600">Status</div>
                        <div class="text-lg font-bold">{{ ucfirst($testzadel->status) }}</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="text-sm text-gray-600">Uitgeleend op</div>
                        <div class="text-lg font-bold">{{ $testzadel->uitleen_datum->format('d/m/Y') }}</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="text-sm text-gray-600">Dagen uitgeleend</div>
                        <div class="text-lg font-bold {{ $testzadel->is_laat ? 'text-red-600' : 'text-green-600' }}">
                            {{ $testzadel->dagen_uitgeleend }} dagen
                        </div>
                    </div>
                </div>

                @if($testzadel->verwachte_retour_datum || $testzadel->werkelijke_retour_datum)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    @if($testzadel->verwachte_retour_datum)
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="text-sm text-gray-600">Verwachte retour</div>
                        <div class="text-lg font-bold">{{ $testzadel->verwachte_retour_datum->format('d/m/Y') }}</div>
                    </div>
                    @endif
                    @if($testzadel->werkelijke_retour_datum)
                    <div class="bg-gray-50 p-4 rounded">
                        <div class="text-sm text-gray-600">Werkelijke retour</div>
                        <div class="text-lg font-bold">{{ $testzadel->werkelijke_retour_datum->format('d/m/Y') }}</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Foto -->
            @if($testzadel->foto_path)
            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900">Foto</h3>
                <img src="{{ asset('storage/' . $testzadel->foto_path) }}" alt="Testzadel foto" class="max-w-md rounded-lg shadow">
            </div>
            @endif

            <!-- Opmerkingen -->
            @if($testzadel->opmerkingen)
            <div class="mt-8">
                <h3 class="text-xl font-bold mb-4 text-gray-900">Opmerkingen</h3>
                <div class="bg-gray-50 p-4 rounded">
                    {{ $testzadel->opmerkingen }}
                </div>
            </div>
            @endif

            <!-- Acties -->
            <div class="mt-8 flex flex-wrap gap-3">
                @if($testzadel->status === 'uitgeleend')
                    @if($testzadel->klant->email)
                        <form method="POST" action="{{ route('testzadels.reminder', $testzadel) }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-2 px-4 rounded"
                                    onclick="return confirm('Email versturen naar {{ $testzadel->klant->voornaam }}?')">
                                📧 Herinnering versturen
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('testzadels.returned', $testzadel) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"
                                onclick="return confirm('Testzadel markeren als teruggebracht?')">
                            ✅ Markeer als ontvangen
                        </button>
                    </form>
                @endif

                <button onclick="if(confirm('Testzadel archiveren?')) { alert('Gearchiveerd! (Demo functie)'); }" 
                        class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                    📁 Archiveren
                </button>
            </div>
        </div>
    </div>
</div>
@endsection