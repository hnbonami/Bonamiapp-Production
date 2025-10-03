<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nieuw Sjabloon') }}
            </h2>
            <a href="{{ route('sjablonen.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Terug
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('sjablonen.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="naam" class="block text-sm font-medium text-gray-700 mb-2">
                                Sjabloon Naam
                            </label>
                            <input type="text" 
                                   name="naam" 
                                   id="naam" 
                                   value="{{ old('naam') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   required>
                            @error('naam')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="categorie" class="block text-sm font-medium text-gray-700 mb-2">
                                Type Sjabloon
                            </label>
                            <select name="categorie" 
                                    id="categorie" 
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    onchange="toggleTestTypeMapping()"
                                    required>
                                <option value="">Selecteer type...</option>
                                <option value="bikefit" {{ old('categorie') === 'bikefit' ? 'selected' : '' }}>Bikefit</option>
                                <option value="inspanningstest" {{ old('categorie') === 'inspanningstest' ? 'selected' : '' }}>Inspanningstest</option>
                                <option value="algemeen" {{ old('categorie') === 'algemeen' ? 'selected' : '' }}>Algemeen</option>
                            </select>
                            @error('categorie')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="testtype-mapping" class="mb-4" style="display: none;">
                            <label for="testtype_mapping" class="block text-sm font-medium text-gray-700 mb-2">
                                Testtype Koppeling
                            </label>
                            <input type="text" 
                                   name="testtype_mapping" 
                                   id="testtype_mapping" 
                                   value="{{ old('testtype_mapping') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Bijv: professionele bikefit, recreatieve bikefit, VO2 max test, etc.">
                            <p class="text-xs text-gray-500 mt-1">Dit moet exact overeenkomen met het testtype uit je bikefit/inspanningstest formulieren</p>
                            @error('testtype_mapping')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Beschrijving (optioneel)
                            </label>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3"
                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      placeholder="Korte beschrijving van het sjabloon...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Sjabloon Aanmaken
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTestTypeMapping() {
            const typeSelect = document.getElementById('type');
            const testTypeMappingDiv = document.getElementById('testtype-mapping');
            
            if (typeSelect.value === 'bikefit' || typeSelect.value === 'inspanningstest') {
                testTypeMappingDiv.style.display = 'block';
                document.getElementById('testtype_mapping').required = true;
            } else {
                testTypeMappingDiv.style.display = 'none';
                document.getElementById('testtype_mapping').required = false;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleTestTypeMapping();
        });
    </script>
</x-app-layout>