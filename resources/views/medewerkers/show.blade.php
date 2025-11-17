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
    <h2 class="text-2xl font-semibold mb-4 text-left">Profiel van {{ $medewerker->voornaam }} {{ $medewerker->achternaam }}</h2>

    <!-- Compacte Header met avatar en kerngegevens -->
    <div class="mb-6">
        @php
            // AVATAR PATH - productie compatibel (EXACT ZELFDE ALS KLANTEN)
            $avatarPath = $medewerker->avatar_path ?? null;
            $cacheKey = optional($medewerker->updated_at)->timestamp ?? time();
            $avatarUrl = null;
            
            if ($avatarPath) {
                // Database bevat: 'avatars/medewerkers/filename.png'
                // Omzetten naar: 'uploads/avatars/medewerkers/filename.png'
                if (app()->environment('production')) {
                    $avatarUrl = url(str_replace('avatars/', 'uploads/avatars/', $avatarPath));
                } else {
                    $avatarUrl = asset('storage/' . $avatarPath);
                }
                
                \Log::info('🖼️ Medewerker Avatar URL gegenereerd', [
                    'avatar_path' => $avatarPath,
                    'avatar_url' => $avatarUrl,
                    'environment' => app()->environment()
                ]);
            }
        @endphp
        
        <!-- Mobile: Avatar links, Rol + Email rechts ernaast -->
        <div class="flex items-start gap-4 mb-4 md:hidden">
            <!-- Avatar met overlay - links uitgelijnd op alle devices -->
            <div class="relative flex-shrink-0" style="width:120px;height:120px;">
                <form action="{{ route('medewerkers.update', $medewerker) }}" method="POST" enctype="multipart/form-data" id="avatar-form" style="margin: 0;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="voornaam" value="{{ $medewerker->voornaam }}">
                    <input type="hidden" name="achternaam" value="{{ $medewerker->achternaam }}">
                    <input type="hidden" name="email" value="{{ $medewerker->email }}">
                    <input type="hidden" name="geslacht" value="{{ $medewerker->geslacht }}">
                    <input type="hidden" name="rol" value="{{ $medewerker->role }}">
                    <input type="hidden" name="status" value="{{ $medewerker->status }}">
                    <label for="avatar-upload" style="cursor: pointer; display: block; position: relative;">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}?t={{ $cacheKey }}" alt="Avatar" class="rounded-lg object-cover" style="width:120px;height:120px;" />
                        @else
                            <div class="rounded-lg bg-gray-200 flex items-center justify-center text-gray-600 font-semibold" style="width:120px;height:120px;font-size:48px;">
                                {{ strtoupper(substr($medewerker->voornaam,0,1)) }}
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
            
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-500">Volledige naam</p>
                <p class="mt-1 text-lg text-gray-900 truncate">{{ $medewerker->voornaam }} {{ $medewerker->achternaam }}</p>
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:gap-4 mt-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold rounded-full {{ $medewerker->status === 'Actief' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} px-3 py-1">
                            {{ $medewerker->status }}
                        </span>
                        @if($medewerker->geslacht)
                            <span class="text-xs font-semibold bg-gray-100 text-gray-800 rounded-full px-3 py-1">
                                {{ $medewerker->geslacht }}
                            </span>
                        @endif
                    </div>
                    <div class="hidden sm:block sm:flex-1"></div>
                    <div class="text-sm text-gray-500 mt-2 sm:mt-0">
                        @if($medewerker->email)
                            <a href="mailto:{{ $medewerker->email }}" class="hover:underline">{{ $medewerker->email }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop: Avatar bovenaan, Rol + Email onderaan -->
        <div class="hidden md:block">
            <div class="grid grid-cols-2 gap-x-10 gap-y-4">
                <!-- Rij 1 -->
                <div>
                    <p class="text-sm font-medium text-gray-500">Volledige naam</p>
                    <p class="mt-2 text-lg text-gray-900">{{ $medewerker->voornaam }} {{ $medewerker->achternaam }}</p>
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
