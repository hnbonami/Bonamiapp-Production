@extends('layouts.app')

@section('content')

@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;padding:1em;margin-bottom:1em;border-radius:5px;">
        {{ session('error') }}
    </div>
@endif


<div class="max-w-4xl pt-0 pb-6">
    <h2 class="text-2xl font-semibold mb-4 text-left">Profiel van {{ $klant->voornaam }} {{ $klant->naam }}</h2>

    <!-- Compacte Header met avatar en kerngegevens -->
    <div class="mb-6">
        <!-- Mobile: Avatar links, Geslacht + Email rechts ernaast -->
        <div class="flex items-start gap-4 mb-4 md:hidden">
            <!-- Avatar met overlay - links uitgelijnd op alle devices -->
            <div class="relative flex-shrink-0" style="width:120px;height:120px;">
                <form action="{{ route('klanten.avatar', $klant) }}" method="POST" enctype="multipart/form-data" id="avatar-form" style="margin: 0;">
                    @csrf
                    <label for="avatar-upload" style="cursor: pointer; display: block; position: relative;">
                        @php
                            // EXACT dezelfde logica als topbar in app.blade.php
                            // Voor ingelogde klant: gebruik auth()->user()->avatar_path
                            // Voor andere klanten: gebruik klant->user->avatar_path of klant->avatar_path
                            $avatarPath = null;
                            
                            if (Auth::check() && Auth::user()->email === $klant->email) {
                                // Ingelogde klant bekijkt eigen profiel
                                $avatarPath = Auth::user()->avatar_path;
                            } elseif ($klant->user && $klant->user->avatar_path) {
                                // Andere klant met gekoppelde user
                                $avatarPath = $klant->user->avatar_path;
                            } elseif ($klant->avatar_path) {
                                // Fallback naar klant avatar
                                $avatarPath = $klant->avatar_path;
                            }
                        @endphp
                        
                        @if($avatarPath)
                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="Avatar" class="rounded-lg object-cover" style="width:120px;height:120px;" />
                        @else
                            <div class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold" style="width:120px;height:120px;font-size:48px;">
                                {{ strtoupper(substr($klant->voornaam,0,1)) }}
                            </div>
                        @endif
                        <!-- Camera overlay icon -->
                        <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(200, 225, 235, 0.95); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                        </div>
                    </label>
                    <input type="file" id="avatar-upload" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>
            </div>
            
            <!-- Mobile: Geslacht + Email rechts van avatar -->
            <div class="flex-1 space-y-3">
                <div>
                    <p class="text-sm font-medium text-gray-500">Geslacht</p>
                    <p class="mt-1 text-base text-gray-900">{{ $klant->geslacht ?? 'Niet opgegeven' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">E-mailadres</p>
                    <p class="mt-1 text-base text-gray-900 break-all">{{ $klant->email }}</p>
                </div>
            </div>
        </div>
        
        <!-- Mobile only: Geboortedatum + Status onder avatar -->
        <div class="grid grid-cols-2 gap-4 md:hidden">
            <div>
                <p class="text-sm font-medium text-gray-500">Geboortedatum</p>
                <p class="mt-1 text-base text-gray-900">{{ $klant->geboortedatum ? \Carbon\Carbon::parse($klant->geboortedatum)->format('d-m-Y') : 'Niet opgegeven' }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Status</p>
                <p class="mt-1">
                    <span class="px-2.5 py-1 inline-flex text-sm font-semibold rounded-full {{ ($klant->status ?? '') === 'Actief' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $klant->status ?? 'Onbekend' }}</span>
                </p>
            </div>
        </div>
        
        <!-- Desktop: Alle 3 kolommen naast elkaar -->
        <div class="hidden md:flex md:items-start" style="gap: 2rem;">
            <!-- Avatar -->
            <div class="relative flex-shrink-0" style="width:120px;height:120px;">
                <form action="{{ route('klanten.avatar', $klant) }}" method="POST" enctype="multipart/form-data" id="avatar-form-desktop" style="margin: 0;">
                    @csrf
                    <label for="avatar-upload-desktop" style="cursor: pointer; display: block; position: relative;">
                        @if($avatarPath)
                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="Avatar" class="rounded-lg object-cover" style="width:120px;height:120px;" />
                        @else
                            <div class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold" style="width:120px;height:120px;font-size:48px;">
                                {{ strtoupper(substr($klant->voornaam,0,1)) }}
                            </div>
                        @endif
                        <!-- Camera overlay icon -->
                        <div style="position: absolute; bottom: 4px; right: 4px; background: rgba(200, 225, 235, 0.95); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#333" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                        </div>
                    </label>
                    <input type="file" id="avatar-upload-desktop" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>
            </div>
            
            <!-- Desktop: Info grid - 2 kolommen met gelijke breedte -->
            <div class="flex-1 grid grid-cols-2 gap-6">
                <!-- Kolom 1 - Geslacht + Email -->
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Geslacht</p>
                        <p class="mt-1 text-base text-gray-900">{{ $klant->geslacht ?? 'Niet opgegeven' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">E-mailadres</p>
                        <p class="mt-1 text-base text-gray-900 break-all">{{ $klant->email }}</p>
                    </div>
                </div>
                
                <!-- Kolom 2 - Geboortedatum + Status -->
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Geboortedatum</p>
                        <p class="mt-1 text-base text-gray-900">{{ $klant->geboortedatum ? \Carbon\Carbon::parse($klant->geboortedatum)->format('d-m-Y') : 'Niet opgegeven' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="mt-1">
                            <span class="px-2.5 py-1 inline-flex text-sm font-semibold rounded-full {{ ($klant->status ?? '') === 'Actief' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $klant->status ?? 'Onbekend' }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Snelle Acties sectie -->
    @php $user = auth()->user(); @endphp
    @if($user && ($user->isBeheerder() || $user->isMedewerker()))
    <div style="margin-top:1.5em;">
        <h3 style="font-size:1.2em;margin-bottom:0.75em;">Snelle Acties</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            
            @if($user->isBeheerder() || ($user->isMedewerker() && $user->bikefit))
            <!-- Bikefit Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div style="background:#c8e1eb;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="5.5" cy="17.5" r="3.5"/><circle cx="18.5" cy="17.5" r="3.5"/><path d="M15 6l3.4 9H18"/><path d="M5.5 17.5L8 10l4 1"/><path d="M12 11l4-5h2.5"/><path d="M12 11l-3 6"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Bikefit</h4>
                </div>
                <p class="text-xs text-gray-600 mb-3">Nieuwe bikefit meting toevoegen</p>
                <a href="{{ route('bikefit.create', $klant->id) }}" class="block w-full text-center" style="background:#c8e1eb;color:#111;padding:0.5rem 0.75rem;border-radius:6px;text-decoration:none;font-weight:600;font-size:0.813rem;">
                    + Toevoegen
                </a>
            </div>
            @endif
            
            @if($user->isBeheerder() || ($user->isMedewerker() && $user->inspanningstest))
            <!-- Inspanningstest Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div style="background:#c8e1eb;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Inspanningstest</h4>
                </div>
                <p class="text-xs text-gray-600 mb-3">Nieuwe insp.test toevoegen</p>
                <a href="{{ route('inspanningstest.create', $klant->id) }}" class="block w-full text-center" style="background:#c8e1eb;color:#111;padding:0.5rem 0.75rem;border-radius:6px;text-decoration:none;font-weight:600;font-size:0.813rem;">
                    + Toevoegen
                </a>
            </div>
            @endif
            
            @if($user->isBeheerder() || ($user->isMedewerker() && $user->upload_documenten))
            <!-- Document Upload Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div style="background:#c8e1eb;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Document</h4>
                </div>
                <p class="text-xs text-gray-600 mb-3">Document uploaden voor klant</p>
                <button type="button" onclick="openUploadModal()" class="block w-full text-center" style="background:#c8e1eb;color:#111;padding:0.5rem 0.75rem;border-radius:6px;font-weight:600;font-size:0.813rem;border:none;cursor:pointer;">
                    Upload
                </button>
            </div>
            @endif
            
            @if($user->isBeheerder() || $user->isMedewerker())
            <!-- Uitnodiging Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-2 mb-2">
                    <div style="background:#c8e1eb;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-900 text-sm">Uitnodiging</h4>
                </div>
                <p class="text-xs text-gray-600 mb-3">Profieluitnodiging versturen</p>
                <form action="{{ route('klanten.sendInvitation', $klant) }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full text-center" style="background:#c8e1eb;color:#111;padding:0.5rem 0.75rem;border-radius:6px;font-weight:600;font-size:0.813rem;border:none;cursor:pointer;">
                        Versturen
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Upload Modal -->
<div id="uploadModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div class="bg-white rounded-lg shadow-xl" style="width:90%;max-width:600px;padding:2rem;position:relative;">
        <button onclick="closeUploadModal()" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#6b7280;">&times;</button>
        
        <h3 style="font-size:1.25rem;font-weight:600;margin-bottom:1.5rem;">Document Uploaden</h3>
        
        <form action="{{ route('klanten.documenten.store', $klant) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:1rem;">
                <label for="modal-document" class="block text-sm font-medium text-gray-700 mb-2">Selecteer bestand *</label>
                <input type="file" name="document" id="modal-document" required 
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" style="padding:0.625rem 0.75rem;">
            </div>
            <div style="margin-bottom:1rem;">
                <label for="modal-naam" class="block text-sm font-medium text-gray-700 mb-2">Naam (optioneel)</label>
                <input type="text" name="naam" id="modal-naam" placeholder="Document naam..."
                       class="block w-full text-sm border border-gray-300 rounded-lg" style="padding:0.625rem 0.75rem;">
            </div>
            <div style="margin-bottom:1.5rem;">
                <label for="modal-beschrijving" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving (optioneel)</label>
                <input type="text" name="beschrijving" id="modal-beschrijving" placeholder="Korte beschrijving..."
                       class="block w-full text-sm border border-gray-300 rounded-lg" style="padding:0.625rem 0.75rem;">
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" onclick="closeUploadModal()" style="background:#e5e7eb;color:#374151;padding:0.625rem 1.5rem;border-radius:6px;font-weight:600;border:none;cursor:pointer;">
                    Annuleren
                </button>
                <button type="submit" style="background:#c8e1eb;color:#111;padding:0.625rem 1.5rem;border-radius:6px;font-weight:600;border:none;cursor:pointer;">
                    Uploaden
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Sluit modal bij klik buiten de modal - alleen als modal bestaat
const uploadModal = document.getElementById('uploadModal');
if (uploadModal) {
    uploadModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeUploadModal();
        }
    });
}
</script>

<div style="margin-top:2em;">
    <h3 style="font-size:1.2em;margin-bottom:0.75em;">Testgeschiedenis en documenten</h3>

    @php
        $bikefits = $klant->bikefits->map(function($b) { $b->type = 'bikefit'; return $b; });
        $inspanningstests = $klant->inspanningstests->map(function($i) { $i->type = 'inspanningstest'; return $i; });
        $documenten = $klant->documenten->map(function($d) { $d->type = 'document'; return $d; });
        $testen = $bikefits->concat($inspanningstests)->concat($documenten)->sortByDesc(function($item) {
            if ($item->type === 'bikefit') {
                return $item->datum ?? $item->created_at;
            } elseif ($item->type === 'inspanningstest') {
                return $item->testdatum ?? $item->created_at;
            } else {
                return $item->upload_datum ?? $item->created_at;
            }
        });
    @endphp
    @if($testen->count())
        <div class="overflow-x-auto bg-white/80 rounded-xl shadow border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medewerker</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</th>
                            </tr>
                        </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach($testen as $test)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                            @if($test->type === 'bikefit')
                                @if($test->datum)
                                    {{ is_string($test->datum) ? \Carbon\Carbon::parse($test->datum)->format('d-m-Y') : $test->datum->format('d-m-Y') }}
                                @else
                                    Datum onbekend
                                @endif
                            @elseif($test->type === 'document')
                                {{ $test->upload_datum ? \Carbon\Carbon::parse($test->upload_datum)->format('d-m-Y') : $test->created_at->format('d-m-Y') }}
                            @else
                                @if($test->testdatum)
                                    {{ is_string($test->testdatum) ? \Carbon\Carbon::parse($test->testdatum)->format('d-m-Y') : $test->testdatum->format('d-m-Y') }}
                                @else
                                    Datum onbekend
                                @endif
                            @endif
                        </td>
                        @if($test instanceof App\Models\Bikefit)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst($test->testtype ?? 'Bikefit') }}
                            </td>
                        @elseif($test instanceof App\Models\Inspanningstest)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ucfirst($test->testtype ?? 'Inspanningstest') }}
                            </td>
                        @elseif($test instanceof App\Models\KlantDocument)
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-gray-900">{{ $test->titel ?? 'Document' }}</span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        {{ strtoupper($test->bestandstype) }}
                                    </span>
                                </div>
                            </td>
                        @else
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ class_basename($test) }}
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $test->user ? $test->user->name : 'Onbekend' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right align-top">
                            <div class="action-buttons flex flex-row flex-nowrap items-center justify-end gap-2">
                                {{-- Preview/Download knoppen - ALTIJD zichtbaar voor iedereen --}}
                                @if($test->type === 'bikefit')
                                    <a href="{{ route('bikefit.sjabloon-rapport', ['klant' => $klant->id, 'bikefit' => $test->id]) }}" aria-label="Bekijk Rapport" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Bekijk Rapport">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                @elseif($test->type === 'inspanningstest')
                                    <a href="{{ route('inspanningstest.sjabloon-rapport', ['klant' => $klant->id, 'test' => $test->id]) }}" aria-label="Bekijk Rapport" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Bekijk Rapport">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                @elseif($test->type === 'document')
                                    <a href="{{ route('klanten.documenten.download', [$klant, $test]) }}" aria-label="Download Document" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800" title="Download Document">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                @endif
                                
                                {{-- Edit/Duplicate/Delete knoppen - ALLEEN voor admin/medewerkers --}}
                            @if($test->type === 'bikefit')
                                @if($user && ($user->isBeheerder() || ($user->isMedewerker() && $user->bikefit)))
                                <a href="{{ route('bikefit.edit', [$klant->id, $test->id]) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                <form action="{{ route('bikefit.duplicate', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" aria-label="Dupliceer" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800" title="Dupliceer">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('bikefit.destroy', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je zeker dat je deze bikefit wilt verwijderen?')" aria-label="Wis" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-800" title="Wis">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                                    </button>
                                </form>
                                @endif
                            @elseif($test->type === 'document')
                                @if($user && ($user->isBeheerder() || ($user->isMedewerker() && $user->upload_documenten)))
                                <form action="{{ route('klanten.documenten.destroy', [$klant, $test]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je zeker dat je dit document wilt verwijderen?')" aria-label="Wis" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-800" title="Wis">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                                    </button>
                                </form>
                                @endif
                            @else
                                @if($user && ($user->isBeheerder() || ($user->isMedewerker() && $user->inspanningstest)))
                                <a href="{{ route('inspanningstest.edit', [$klant->id, $test->id]) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800" title="Bewerk">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                </a>
                                <form action="{{ route('inspanningstest.duplicate', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" aria-label="Dupliceer" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800" title="Dupliceer">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                    </button>
                                </form>
                                <form action="{{ route('inspanningstest.destroy', [$klant->id, $test->id]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Weet je zeker dat je deze test wilt verwijderen?')" aria-label="Wis" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-800" title="Wis">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                                    </button>
                                </form>
                                @endif
                            @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div style="color:#6b7280;">Nog geen testen geregistreerd.</div>
    @endif
</div>

@endsection
