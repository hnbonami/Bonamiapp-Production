@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">📊 Database Tools</h1>
                <p class="text-gray-600 mb-8">Upload Excel bestanden om grote hoeveelheden klanten en bikefits in één keer toe te voegen.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Klanten Import -->
                    <div class="bg-blue-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="text-xl font-semibold text-blue-800">Klanten Toevoegen</h3>
                        </div>
                        <p class="text-blue-600 mb-4">Import klanten gegevens uit Excel bestand</p>
                        
                        <div class="mb-4">
                            <a href="{{ route('klanten.template') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mb-3">
                                📥 Download Template
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                        
                        <div class="mb-4">
                            <a href="/import/klanten" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md font-semibold text-sm text-gray-800 uppercase tracking-widest transition ease-in-out duration-150" style="background-color: #c8e1eb; hover:background-color: #b3d4df;">
                                📤 Import Klanten
                            </a>
                        </div>
                    </div>

                    <!-- Bikefits Import -->
                    <div class="bg-green-50 p-6 rounded-lg">
                        <div class="flex items-center mb-4">
                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <h3 class="text-xl font-semibold text-green-800">Bikefits Toevoegen</h3>
                        </div>
                        <p class="text-green-600 mb-4">Import bikefit gegevens uit Excel bestand</p>
                        
                        <div class="mb-4">
                            <a href="{{ route('bikefit.template') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium mb-3">
                                📥 Download Template
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                        
                        <div class="mb-4">
                            <a href="/import/bikefits" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md font-semibold text-sm text-gray-800 uppercase tracking-widest transition ease-in-out duration-150" style="background-color: #c8e1eb; hover:background-color: #b3d4df;">
                                📤 Import Bikefits
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Data Exporteren -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Data Exporteren</h2>
                    <p class="text-gray-600 mb-6">Download alle data uit de database als Excel bestanden voor backup of analyse.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Download Alle Klanten -->
                        <div class="bg-purple-50 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <h3 class="text-xl font-semibold text-purple-800">Download Alle Klanten</h3>
                            </div>
                            <p class="text-purple-600 mb-4">Export alle klantgegevens naar Excel</p>
                            <a href="{{ route('klanten.export') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md font-semibold text-sm text-gray-800 uppercase tracking-widest transition ease-in-out duration-150 mb-3" style="background-color: #c8e1eb; hover:background-color: #b3d4df;">
                                💾 Download Klanten
                            </a>
                            <div>
                                <small class="text-purple-600">
                                    Bevat: Naam, email, telefoon, adres, geboortedatum, sport, niveau
                                </small>
                            </div>
                        </div>

                        <!-- Download Alle Bikefits -->
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <div class="flex items-center mb-4">
                                <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <h3 class="text-xl font-semibold text-yellow-800">Download Alle Bikefits</h3>
                            </div>
                            <p class="text-yellow-600 mb-4">Export alle bikefit gegevens naar Excel</p>
                            <a href="{{ route('bikefits.export') }}" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md font-semibold text-sm text-gray-800 uppercase tracking-widest transition ease-in-out duration-150 mb-3" style="background-color: #c8e1eb; hover:background-color: #b3d4df;">
                                💾 Download Bikefits
                            </a>
                            <div>
                                <small class="text-yellow-600">
                                    Bevat: Klant info, metingen, posities, aanpassingen, resultaten
                                </small>
                            </div>
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
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            const feedback = this.parentNode.querySelector('.file-feedback');
            
            if (fileName) {
                feedback.textContent = 'Geselecteerd: ' + fileName;
            } else {
                feedback.textContent = 'geen bestand geselecteerd';
            }
        });
    });
    
    // Import form loading states
    document.querySelectorAll('.import-form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            button.innerHTML = '⏳ Importeren...';
            button.disabled = true;
            
            // Re-enable after timeout as fallback
            setTimeout(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 15000);
        });
    });
});
</script>
@endsection