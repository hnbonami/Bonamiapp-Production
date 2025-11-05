@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">📊 Database Tools</h1>
                 <h2 class="text-2xl font-bold text-gray-900 mb-4">Data Importeren</h2>
                <p class="text-gray-600 mb-8">Upload Excel bestanden om grote hoeveelheden klanten en bikefits in één keer toe te voegen.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Klanten Import -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                                <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 ml-3">Klanten Toevoegen</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Import klanten gegevens uit Excel bestand</p>
                        
                        <div class="mb-4">
                            <a href="/import/klanten" class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-gray-800 uppercase tracking-widest transition" style="background-color: #c8e1eb;">
                                📤 Import Klanten
                            </a>
                        </div>
                    </div>

                    <!-- Bikefits Import -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                                <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 ml-3">Bikefits Toevoegen</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Import bikefit gegevens uit Excel bestand</p>
                        
                        <div class="mb-4">
                            <a href="/import/bikefits" class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-gray-800 uppercase tracking-widest transition" style="background-color: #c8e1eb;">
                                📤 Import Bikefits
                            </a>
                        </div>
                    </div>

                    <!-- Inspanningstesten Import -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                                <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800 ml-3">Testen Toevoegen</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Import inspanningstest gegevens uit Excel bestand</p>
                        
                        <div class="mb-4">
                            <a href="/import/inspanningstesten" class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-gray-800 uppercase tracking-widest transition" style="background-color: #c8e1eb;">
                                📤 Import Insp. testen
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Data Exporteren -->
                <div class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">Data Exporteren</h2>
                    <p class="text-gray-600 mb-6">Download alle data uit de database als Excel bestanden voor backup of analyse.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Download Alle Klanten -->
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 ml-3">Download Alle Klanten</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Export alle klantgegevens naar Excel bestand</p>
                            <a href="{{ route('klanten.export') }}" class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-gray-800 uppercase tracking-widest transition mb-3" style="background-color: #c8e1eb;">
                                💾 Download Klanten
                            </a>
                            <div>
                                <small class="text-gray-600">
                                    Bevat: Naam, email, telefoon, adres, geboortedatum, sport, niveau
                                </small>
                            </div>
                        </div>

                        <!-- Download Alle Bikefits -->
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 ml-3">Download Alle Bikefits</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Export alle bikefit gegevens naar Excel</p>
                            <a href="{{ route('bikefits.export') }}" class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-gray-800 uppercase tracking-widest transition mb-3" style="background-color: #c8e1eb;">
                                💾 Download Bikefits
                            </a>
                            <div>
                                <small class="text-gray-600">
                                    Bevat: Klant info, metingen, posities, aanpassingen, resultaten
                                </small>
                            </div>
                        </div>

                        <!-- Download Alle Inspanningstesten -->
                        <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                            <div class="flex items-center mb-4">
                                <div class="p-3 rounded-lg" style="background-color: #c8e1eb;">
                                    <svg class="w-6 h-6" style="color: #111;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 ml-3">Download Alle Testen</h3>
                            </div>
                            <p class="text-gray-600 mb-4">Export alle inspanningstest gegevens naar Excel</p>
                            <a href="{{ route('inspanningstesten.export') }}" class="inline-flex items-center px-6 py-3 rounded-lg font-semibold text-sm text-gray-800 uppercase tracking-widest transition mb-3" style="background-color: #c8e1eb;">
                                💾 Download Testen
                            </a>
                            <div>
                                <small class="text-gray-600">
                                    Bevat: Klant info, testdata, VO2max, zones, vermogen, hartslag
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