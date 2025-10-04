@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Bikefit bewerken</h1>
        <div class="flex space-x-3">
            <a href="{{ route('klanten.show', $klant->id) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Terug naar klant
            </a>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <div class="p-6">
            <!-- Bikefit update form placed before upload form -->
            <form id="bikefit-form" method="POST" action="{{ route('bikefit.update', ['klant' => $klant->id, 'bikefit' => $bikefit->id]) }}">
                @csrf
                @method('PUT')
                
                @include('bikefit._form', ['submitLabel' => '', 'isEdit' => true])

                <!-- Nieuw Uitleensysteem Component -->
                @include('components.bikefit-uitleensysteem')

                <!-- Buttons under uploaded files -->
                <div class="mt-6 mb-8">
                    <div class="bg-white border-t pt-4">
                        <div class="flex items-center justify-start gap-4">
                            <div>
                                <button type="button" onclick="submitBikefit('save_and_results')" class="inline-block text-white font-bold py-2 px-4 rounded shadow-md focus:outline-none" style="background-color:#16a34a!important;color:#ffffff!important;box-shadow:0 6px 12px rgba(6,95,70,0.15);border:1px solid rgba(0,0,0,0.06);">Opslaan</button>
                            </div>
                            <div>
                                <button type="button" onclick="submitBikefit('save_and_back')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Terug</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

         </div>
     </div>

    <!-- Upload box (moved here - above uploaded files) -->
    <form method="POST" action="{{ route('bikefit.upload', ['klant' => $klant->id, 'bikefit' => $bikefit->id]) }}" enctype="multipart/form-data" class="mt-6 mb-6">
        @csrf
        <div class="mb-4 bg-white border border-gray-300 rounded p-4 shadow-sm">
            <label for="file" class="block text-sm font-medium text-gray-700">Bestand uploaden</label>
            <div class="flex items-center gap-4 mt-2">
                <input type="file" name="file" id="file" class="form-input" required>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">Upload</button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Toegestane bestanden: PDF, PNG, JPG. Max grootte 10MB.</p>
        </div>
    </form>

     <div class="mt-8">
         <h3 class="text-lg font-semibold text-gray-800 mb-4">Geüploade bestanden</h3>
         <ul class="list-disc list-inside">
             @foreach($bikefit->uploads as $upload)
                 <li class="mb-2 flex items-center gap-2">
                     <form method="POST" action="{{ route('uploads.destroy', $upload->id) }}" class="inline-block">
                         @csrf
                         @method('DELETE')
                         <button type="submit" class="bg-red-600 text-white font-bold py-1 px-2 rounded hover:bg-red-800">
                             X
                         </button>
                     </form>
                     <a href="{{ route('uploads.show', $upload->id) }}" target="_blank" class="text-blue-600 hover:underline">
                         {{ basename($upload->path) }}
                     </a>
                 </li>
             @endforeach
         </ul>
     </div>

    <script>
        function submitBikefit(actionName) {
            var form = document.getElementById('bikefit-form');
            if(!form) {
                console.log('Form not found!');
                return;
            }
            // Remove existing temp inputs if present
            var existing = document.querySelector('#bikefit-form input[name="' + actionName + '"]');
            if(existing) existing.parentNode.removeChild(existing);
            // Create hidden input
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = actionName;
            input.value = '1';
            form.appendChild(input);
            console.log('Submitting form with action:', actionName);
            form.submit();
        }

        // Beenlengteverschil toggle functionaliteit
        document.addEventListener('DOMContentLoaded', function() {
            const beenlengteSelect = document.querySelector('select[name="beenlengteverschil"]');
            const beenlengteCmField = document.querySelector('[name="beenlengteverschil_cm"]');
            
            if (beenlengteSelect && beenlengteCmField) {
                const cmFieldContainer = beenlengteCmField.closest('.mb-4') || beenlengteCmField.closest('.form-group') || beenlengteCmField.parentElement;
                
                function toggleBeenlengteCmField() {
                    if (beenlengteSelect.value === '1') {
                        cmFieldContainer.style.display = 'block';
                    } else {
                        cmFieldContainer.style.display = 'none';
                    }
                }
                
                beenlengteSelect.addEventListener('change', toggleBeenlengteCmField);
                toggleBeenlengteCmField(); // Initial state
            }
        });
    </script>

    @if(session('upload_success'))
        <div id="upload-success" class="mb-6 p-4 bg-green-100 border border-green-400 text-green-800 rounded">
            Bestand succesvol geüpload! <br>
            <a href="{{ session('upload_link') }}" class="underline text-blue-700" target="_blank">Bekijk het geüploade bestand</a>
        </div>
    @endif
</div>
@endsection
