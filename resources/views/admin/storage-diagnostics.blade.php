@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">
                🔧 Storage Diagnostics - Code Check
            </h1>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {!! session('success') !!}
            </div>
            @endif

            <!-- Status Check -->
            <div class="bg-green-50 border border-green-300 p-4 rounded-lg mb-6">
                <h2 class="text-lg font-bold text-green-800 mb-3">✅ Folders Zijn Gesynchroniseerd!</h2>
                <p class="text-green-700">
                    Er staan <strong>15 avatars</strong> in beide folders. Het probleem ligt waarschijnlijk in de code.
                </p>
            </div>

            <!-- Code Checks -->
            <div class="bg-yellow-50 border border-yellow-300 p-6 rounded-lg mb-6">
                <h2 class="text-xl font-semibold text-yellow-800 mb-4">🔍 Mogelijke Problemen</h2>
                
                @php
                    $checks = [];
                    
                    // 1. Check .env APP_URL
                    $appUrl = config('app.url');
                    $checks[] = [
                        'name' => 'APP_URL in .env',
                        'value' => $appUrl,
                        'ok' => str_contains($appUrl, 'hannesbonami.be'),
                        'fix' => 'Zet in .env: APP_URL=https://hannesbonami.be'
                    ];
                    
                    // 2. Check filesystem disk
                    $defaultDisk = config('filesystems.default');
                    $checks[] = [
                        'name' => 'Default filesystem disk',
                        'value' => $defaultDisk,
                        'ok' => true,
                        'fix' => null
                    ];
                    
                    // 3. Check public disk URL
                    $publicDiskUrl = config('filesystems.disks.public.url');
                    $checks[] = [
                        'name' => 'Public disk URL',
                        'value' => $publicDiskUrl,
                        'ok' => str_contains($publicDiskUrl, '/storage'),
                        'fix' => 'Check config/filesystems.php'
                    ];
                    
                    // 4. Test avatar URL generatie
                    $testAvatarPath = 'avatars/test.jpg';
                    $testUrl = Storage::disk('public')->url($testAvatarPath);
                    $checks[] = [
                        'name' => 'Avatar URL test',
                        'value' => $testUrl,
                        'ok' => str_contains($testUrl, '/storage/avatars/'),
                        'fix' => 'Storage::disk(\'public\')->url() genereert verkeerde URL'
                    ];
                    
                    // 5. Check of KlantController bestaat
                    $klantControllerExists = file_exists(app_path('Http/Controllers/KlantController.php'));
                    $checks[] = [
                        'name' => 'KlantController.php',
                        'value' => $klantControllerExists ? 'Bestaat' : 'NIET GEVONDEN',
                        'ok' => $klantControllerExists,
                        'fix' => 'Upload app/Http/Controllers/KlantController.php'
                    ];
                @endphp
                
                <div class="space-y-3">
                    @foreach($checks as $check)
                    <div class="p-3 bg-white rounded border {{ $check['ok'] ? 'border-green-300' : 'border-red-300' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-semibold">{{ $check['name'] }}</span>
                            <span class="text-sm {{ $check['ok'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $check['ok'] ? '✓ OK' : '✗ PROBLEEM' }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <strong>Waarde:</strong> <code class="bg-gray-100 px-2 py-1 rounded">{{ $check['value'] }}</code>
                        </div>
                        @if(!$check['ok'] && $check['fix'])
                        <div class="mt-2 text-sm text-red-600">
                            <strong>Fix:</strong> {{ $check['fix'] }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Controleer Welke Bestanden Ontbreken -->
            <div class="bg-red-50 border border-red-300 p-6 rounded-lg mb-6">
                <h2 class="text-xl font-semibold text-red-800 mb-4">📋 Controleer Deze Bestanden op de Server</h2>
                
                <p class="text-gray-700 mb-3">Controleer via FileZilla of deze bestanden geüpload zijn:</p>
                
                <div class="space-y-2 text-sm font-mono bg-white p-4 rounded">
                    <div>✓ app/Http/Controllers/KlantController.php</div>
                    <div>✓ app/Http/Controllers/MedewerkerController.php</div>
                    <div>✓ app/Http/Controllers/ProfileSettingsController.php</div>
                    <div>✓ config/filesystems.php</div>
                    <div>✓ .env (met correcte APP_URL)</div>
                    <div>✓ resources/views/klanten/show.blade.php</div>
                    <div>✓ resources/views/profile/settings.blade.php</div>
                </div>
                
                <div class="mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded">
                    <p class="text-yellow-800 font-semibold">
                        🔍 Check Specifiek:
                    </p>
                    <ul class="list-disc list-inside text-yellow-700 text-sm mt-2">
                        <li>Is <code>app/Http/Controllers/KlantController.php</code> de <strong>nieuwste versie</strong>?</li>
                        <li>Staat in de <code>.env</code> file: <code>APP_URL=https://hannesbonami.be</code>?</li>
                        <li>Is <code>config/filesystems.php</code> geüpload?</li>
                    </ul>
                </div>
            </div>

            <!-- Live Test -->
            <div class="bg-blue-50 border border-blue-300 p-6 rounded-lg mb-6">
                <h2 class="text-xl font-semibold text-blue-800 mb-4">🧪 Live Avatar URL Test</h2>
                
                @php
                    // Haal een echte klant avatar op uit de database
                    $testKlant = \App\Models\Klant::whereNotNull('avatar')->first();
                    
                    // Check of de avatar kolom bestaat in medewerkers tabel
                    $medewerkerHasAvatar = Schema::hasColumn('medewerkers', 'avatar');
                @endphp
                
                @if($testKlant)
                <div class="mb-4 p-4 bg-white rounded border border-gray-300">
                    <p class="font-semibold mb-2 text-gray-800">✓ Test Klant Avatar:</p>
                    <div class="text-sm space-y-2">
                        <p><strong>Database pad:</strong> <code class="bg-gray-100 px-2 py-1 rounded">{{ $testKlant->avatar }}</code></p>
                        <p><strong>Gegenereerde URL:</strong> <code class="bg-gray-100 px-2 py-1 rounded">{{ Storage::disk('public')->url($testKlant->avatar) }}</code></p>
                        <p class="mt-2"><strong>Kan de browser dit laden?</strong></p>
                        <div class="mt-2 p-3 bg-gray-50 rounded" id="avatar-test-{{ $testKlant->id }}">
                            <img src="{{ Storage::disk('public')->url($testKlant->avatar) }}" 
                                 alt="Test Avatar" 
                                 class="w-20 h-20 rounded-full border-2 border-gray-300"
                                 onerror="document.getElementById('avatar-test-{{ $testKlant->id }}').innerHTML='<span class=&quot;text-red-600 font-semibold&quot;>❌ PROBLEEM: Browser kan afbeelding niet laden!<br>URL: {{ Storage::disk('public')->url($testKlant->avatar) }}</span>'">
                        </div>
                    </div>
                </div>
                @else
                <div class="mb-4 p-4 bg-yellow-100 rounded border border-yellow-300">
                    <p class="text-yellow-800">⚠️ Geen klanten met avatar gevonden in database.</p>
                </div>
                @endif
                
                @if(!$medewerkerHasAvatar)
                <div class="p-4 bg-red-100 rounded border border-red-300">
                    <p class="text-red-800 font-semibold">❌ PROBLEEM GEVONDEN!</p>
                    <p class="text-red-700 text-sm mt-2">
                        De <code>medewerkers</code> tabel heeft <strong>geen avatar kolom</strong>!
                    </p>
                    <p class="text-red-700 text-sm mt-2">
                        Je moet een database migratie maken om de avatar kolom toe te voegen.
                    </p>
                </div>
                @endif
            </div>

            <!-- Back Button -->
            <div class="mt-6 flex items-center justify-between">
                <a href="{{ route('dashboard.index') }}" class="text-blue-600 hover:text-blue-800">
                    ← Terug naar Dashboard
                </a>
                
                <a href="{{ route('klanten.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Ga naar Klanten →
                </a>
            </div>
        </div>
    </div>
</div>
@endsection