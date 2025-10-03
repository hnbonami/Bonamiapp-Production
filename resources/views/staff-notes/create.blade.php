@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Nieuwe notitie toevoegen</h2>
                <a href="{{ route('staff-notes.index') }}" 
                   style="background:#f3f4f6;color:#374151;padding:0.5em 1em;border-radius:8px;text-decoration:none;font-weight:600;border:1px solid #d1d5db;box-shadow:0 2px 4px rgba(0,0,0,0.1);transition:all 0.2s ease;"
                   onmouseover="this.style.background='#e5e7eb'"
                   onmouseout="this.style.background='#f3f4f6'">
                    Terug
                </a>
            </div>

            <form method="POST" action="{{ route('staff-notes.store') }}">
                @csrf
                
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titel
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="{{ old('title') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="visibility" class="block text-sm font-medium text-gray-700 mb-2">
                        Zichtbaar voor
                    </label>
                    <select name="visibility" 
                            id="visibility" 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('visibility') border-red-500 @enderror"
                            required>
                        <option value="staff" {{ old('visibility') == 'staff' ? 'selected' : '' }}>
                            🏢 Alleen medewerkers & admin
                        </option>
                        <option value="all" {{ old('visibility') == 'all' ? 'selected' : '' }}>
                            👥 Alle gebruikers (medewerkers + klanten)
                        </option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Kies wie deze notitie mag zien op hun dashboard
                    </p>
                    @error('visibility')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Inhoud
                    </label>
                    <textarea name="content" 
                              id="content" 
                              rows="15" 
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                              placeholder="Typ hier de inhoud van je notitie..."
                              required>{{ old('content') }}</textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('staff-notes.index') }}" 
                       style="background:#f3f4f6;color:#374151;padding:0.5em 1em;border-radius:8px;text-decoration:none;font-weight:600;border:1px solid #d1d5db;box-shadow:0 2px 4px rgba(0,0,0,0.1);transition:all 0.2s ease;"
                       onmouseover="this.style.background='#e5e7eb'"
                       onmouseout="this.style.background='#f3f4f6'">
                        Annuleren
                    </a>
                    <button type="submit" 
                            style="background:#c8e1eb;color:#111;padding:0.5em 1.5em;border-radius:8px;font-weight:600;border:1px solid #b5d5e0;box-shadow:0 2px 4px rgba(0,0,0,0.1);cursor:pointer;transition:all 0.2s ease;"
                            onmouseover="this.style.background='#b5d5e0'"
                            onmouseout="this.style.background='#c8e1eb'">
                        Notitie opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Geen JavaScript nodig, gewoon direct submissie -->
@endsection