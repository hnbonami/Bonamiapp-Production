@extends('layouts.app')

@section('content')
<div class="max-w-4xl pt-0 pb-6">
    <h2 class="text-2xl font-semibold mb-6">Profiel van {{ $medewerker->voornaam }} {{ $medewerker->naam }}</h2>

    <!-- Header met avatar en kerngegevens -->
    <div class="flex items-start mb-6" style="gap:3.75rem;">
        <div class="flex flex-col items-start gap-2">
            @php
                // Genereer correcte avatar URL
                if ($medewerker->avatar_path) {
                    $avatarUrl = app()->environment('production') 
                        ? asset('uploads/' . $medewerker->avatar_path)
                        : asset('storage/' . $medewerker->avatar_path);
                } else {
                    $avatarUrl = null;
                }
            @endphp
            @if($avatarUrl)
                <img src="{{ $avatarUrl }}" alt="Avatar" class="rounded-lg object-cover flex-none" style="width:200px;height:200px;" />
            @else
                <div class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold flex-none" style="width:200px;height:200px;font-size:72px;">
                    {{ strtoupper(substr($medewerker->voornaam,0,1)) }}
                </div>
            @endif
            <form method="POST" action="{{ route('medewerkers.update', $medewerker->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="file" name="avatar" id="avatarMedewerkerInline" accept="image/*" style="display:none;">
                <div class="flex items-center gap-2">
                    <button type="button" id="avatarCamBtnMedewerkerInline" aria-label="Maak foto" title="Maak foto" class="bg-gray-100 text-gray-800 rounded-full" style="width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 1px 3px #e0e7ff;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M7 7h2l1.2-1.6A2 2 0 0 1 12 4h0a2 2 0 0 1 1.8 1.4L15 7h2a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-7a3 3 0 0 1 3-3Z" stroke="#111" stroke-width="1.5"/>
                            <circle cx="12" cy="13" r="3.5" stroke="#111" stroke-width="1.5"/>
                            <path d="M19 5v4M17 7h4" stroke="#111" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <button type="button" id="avatarBtnMedewerkerInline" class="rounded-full px-3 py-1 bg-gray-100 text-gray-800 font-semibold text-xs">Wijzig foto</button>
                </div>
                <button type="submit" id="avatarSubmitMedewerkerInline" style="display:none;" class="rounded-full px-3 py-1 bg-indigo-100 text-indigo-800 font-semibold text-xs">Opslaan</button>
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function(){
                const input = document.getElementById('avatarMedewerkerInline');
                const trigger = document.getElementById('avatarBtnMedewerkerInline');
                const camBtn = document.getElementById('avatarCamBtnMedewerkerInline');
                const submit = document.getElementById('avatarSubmitMedewerkerInline');
                if (trigger && input) {
                    trigger.addEventListener('click', function(){
                        input.removeAttribute('capture');
                        input.click();
                    });
                }
                if (camBtn && input) {
                    camBtn.addEventListener('click', function(){
                        input.setAttribute('capture', 'environment');
                        input.click();
                    });
                }
                input?.addEventListener('change', function(){
                    if (input.files && input.files[0]) {
                        submit.style.display='inline-block';
                        trigger.textContent='Kies opnieuw';
                    }
                });
            });
            </script>
        </div>
    <div class="grid grid-cols-2 gap-x-10 gap-y-4">
            <!-- Rij 1 -->
            <div>
                <p class="text-sm font-medium text-gray-500">Volledige naam</p>
                <p class="mt-2 text-lg text-gray-900">{{ $medewerker->voornaam }} {{ $medewerker->naam }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">E-mailadres</p>
                <p class="mt-2 text-lg text-gray-900">{{ $medewerker->email }}</p>
            </div>
            <!-- Rij 2 -->
            <div>
                <p class="text-sm font-medium text-gray-500">Geslacht</p>
                <p class="mt-2 text-lg text-gray-900">{{ $medewerker->geslacht ?? 'Niet opgegeven' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p class="mt-2 text-lg">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $medewerker->status === 'Actief' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $medewerker->status }}</span>
                </p>
            </div>
            <!-- Rij 3 -->
            <div>
                <p class="text-sm font-medium text-gray-500">Geboortedatum</p>
                <p class="mt-2 text-lg text-gray-900">{{ $medewerker->geboortedatum ? \Carbon\Carbon::parse($medewerker->geboortedatum)->format('d-m-Y') : 'Niet opgegeven' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Functie</p>
                <p class="mt-2 text-lg text-gray-900">{{ $medewerker->functie ?? 'Niet opgegeven' }}</p>
            </div>
        </div>
    </div>

    <div class="mt-8 border-t pt-6">
                <h3 class="text-xl font-semibold mb-4">Rechten</h3>
                <div class="flex items-center space-x-6">
                    <div class="flex items-center">
                        @if($medewerker->bikefit)
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @endif
                        <span class="ml-2 text-lg">Bikefit</span>
                    </div>
                    <div class="flex items-center">
                        @if($medewerker->inspanningstest)
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @endif
                        <span class="ml-2 text-lg">Inspanningstest</span>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-start space-x-3">
                <a href="{{ route('medewerkers.index') }}" class="rounded-full px-4 py-1 bg-gray-100 text-gray-800 font-bold text-sm flex items-center justify-center">
                    Terug naar overzicht
                </a>
                <a href="{{ route('medewerkers.edit', $medewerker->id) }}" class="rounded-full px-4 py-1 bg-indigo-100 text-indigo-800 font-bold text-sm flex items-center justify-center">
                    Bewerk profiel
                </a>
            </div>
    
</div>
@endsection
