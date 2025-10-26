@extends('layouts.app')

@section('title', 'Nieuwe Medewerker - Bonami Sportcoaching')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">➕ Nieuwe Medewerker</h1>
        <a href="{{ route('medewerkers.index') }}" class="text-gray-600 hover:text-gray-800">← Terug</a>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('medewerkers.store') }}" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="voornaam" class="block text-sm font-medium text-gray-700">Voornaam *</label>
                    <input type="text" id="voornaam" name="voornaam" value="{{ old('voornaam') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label for="achternaam" class="block text-sm font-medium text-gray-700">Achternaam *</label>
                    <input type="text" id="achternaam" name="achternaam" value="{{ old('achternaam') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div>
                <label for="telefoon" class="block text-sm font-medium text-gray-700">Telefoon</label>
                <input type="tel" id="telefoon" name="telefoon" value="{{ old('telefoon') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>

            <div>
                <label for="geslacht" class="block text-sm font-medium text-gray-700">Geslacht</label>
                <select id="geslacht" name="geslacht" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Selecteer geslacht...</option>
                    <option value="Man" {{ strtolower(old('geslacht', '')) === 'man' ? 'selected' : '' }}>Man</option>
                    <option value="Vrouw" {{ strtolower(old('geslacht', '')) === 'vrouw' ? 'selected' : '' }}>Vrouw</option>
                    <option value="Anders" {{ strtolower(old('geslacht', '')) === 'anders' ? 'selected' : '' }}>Anders</option>
                </select>
            </div>

            <div>
                <label for="rol" class="block text-sm font-medium text-gray-700">Rol *</label>
                <select id="rol" name="rol" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Selecteer rol...</option>
                    <option value="medewerker" {{ old('rol') === 'medewerker' ? 'selected' : '' }}>Medewerker</option>
                    <option value="admin" {{ old('rol') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('medewerkers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Annuleren</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Aanmaken</button>
            </div>
        </form>
    </div>
</div>
@endsection