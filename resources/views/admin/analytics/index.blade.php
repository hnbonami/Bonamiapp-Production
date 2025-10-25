<div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-2">Filter:</label>
    <select name="filter_type" class="form-select rounded-md border-gray-300">
        @if(auth()->user()->is_admin)
            <option value="organisatie" {{ !request('medewerker_id') ? 'selected' : '' }}>
                ✓ Automatisch (Mijn Organisatie)
            </option>
            <option value="" disabled>───────────────</option>
            @foreach($medewerkers as $medewerker)
                <option value="{{ $medewerker->id }}" {{ request('medewerker_id') == $medewerker->id ? 'selected' : '' }}>
                    {{ $medewerker->voornaam }} {{ $medewerker->achternaam }}
                </option>
            @endforeach
        @else
            <option value="medewerker" selected>
                Mijn Prestaties
            </option>
        @endif
    </select>
</div>