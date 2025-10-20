@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="mb-6">
                    <a href="{{ route('klanten.index') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Terug naar Klanten
                    </a>
                </div>

                <h1 class="text-3xl font-bold text-gray-900 mb-6">Inspanningstesten Importeren</h1>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Upload Sectie -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Excel Bestand Uploaden</h2>
                        
                        <form action="{{ route('inspanningstesten.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecteer Inspanningstesten Excel Bestand (.xlsx, .xls, .csv)
                                </label>
                                <input type="file" 
                                       name="file" 
                                       accept=".xlsx,.xls,.csv" 
                                       required
                                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:border-blue-500 p-2">
                                <p class="mt-1 text-sm text-gray-500 file-feedback">geen bestand geselecteerd</p>
                            </div>

                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest bg-orange-600 hover:bg-orange-700 transition ease-in-out duration-150">
                                📤 Inspanningstesten Importeren
                            </button>
                        </form>

                        <div class="mt-6">
                            <a href="{{ route('inspanningstesten.template') }}" 
                               class="inline-flex items-center px-6 py-3 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest transition ease-in-out duration-150"
                               style="background-color: #10b981; hover:background-color: #059669;">
                                📥 Download Inspanningstesten Template
                            </a>
                            <p class="mt-2 text-sm text-gray-600">Download een voorbeeldbestand om te zien hoe je de data moet structureren</p>
                        </div>
                    </div>

                    <!-- Instructies Sectie -->
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">Instructies</h2>
                        
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Belangrijk:</strong>
                                    </p>
                                    <ul class="list-disc list-inside text-sm text-yellow-700 mt-2">
                                        <li><strong>klant_email</strong> OF <strong>klant_naam</strong> is verplicht voor koppeling</li>
                                        <li>Klanten moeten al bestaan in het systeem</li>
                                        <li>Tests zonder geldige klant worden overgeslagen</li>
                                        <li>Bij dezelfde klant worden meerdere tests toegevoegd aan hetzelfde profiel</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                            <h3 class="font-semibold text-blue-800 mb-2">Belangrijkste Kolommen:</h3>
                            <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
                                <li><strong>klant_email/klant_naam</strong> (verplicht voor koppeling)</li>
                                <li><strong>datum, testtype</strong></li>
                                <li><strong>vo2max, ftp_watt, watt_kg_ratio</strong></li>
                                <li><strong>max_hartslag, rusthartslag</strong></li>
                                <li><strong>anaerobe/aerobe drempels</strong></li>
                                <li><strong>zone1-5 hartslag en watt</strong></li>
                                <li><strong>lactaat waarden</strong></li>
                                <li><strong>cadans, testduur, afstand</strong></li>
                            </ul>
                        </div>

                        <div class="mt-4 bg-gray-50 p-4 rounded">
                            <h3 class="font-semibold text-gray-800 mb-2">Tips:</h3>
                            <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                <li>Eerste rij = kolomnamen</li>
                                <li>Datum formaat: YYYY-MM-DD</li>
                                <li>Maximaal 10MB bestandsgrootte</li>
                                <li>Test eerst met een klein bestand</li>
                                <li>Klanten worden automatisch gekoppeld via email of naam</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // File input feedback
    const fileInput = document.querySelector('input[type="file"]');
    const feedback = document.querySelector('.file-feedback');
    
    if (fileInput && feedback) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            
            if (fileName) {
                feedback.textContent = 'Geselecteerd: ' + fileName;
                feedback.classList.remove('text-gray-500');
                feedback.classList.add('text-green-600');
            } else {
                feedback.textContent = 'geen bestand geselecteerd';
                feedback.classList.remove('text-green-600');
                feedback.classList.add('text-gray-500');
            }
        });
    }
    
    // Import form loading state
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.innerHTML = '⏳ Importeren...';
            button.disabled = true;
            
            // Re-enable after timeout as fallback
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 30000);
        });
    }
});
</script>
@endsection