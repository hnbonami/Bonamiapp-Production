@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">🎂 Verjaardag Beheer</h1>
        <p class="text-gray-600">Automatische verjaardags emails voor klanten en medewerkers</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex gap-4">
        <button onclick="sendManualEmails()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
            📧 Verstuur Nu Handmatig
        </button>
        
        <a href="{{ route('debug.birthday.check') }}" target="_blank" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium">
            🔍 Debug Info
        </a>
        
        <a href="{{ route('debug.birthday.test') }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
            🧪 Test Email
        </a>
    </div>

    <!-- Today's Birthdays -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            🎉 Vandaag Jarig ({{ $todayBirthdays['total'] }})
        </h2>
        
        @if($todayBirthdays['total'] > 0)
            <!-- Klanten -->
            @if($todayBirthdays['klanten']->count() > 0)
                <div class="mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">👥 Klanten</h3>
                    <div class="space-y-2">
                        @foreach($todayBirthdays['klanten'] as $klant)
                            <div class="flex items-center justify-between bg-blue-50 p-3 rounded-lg">
                                <div>
                                    <span class="font-medium">{{ $klant->voornaam }} {{ $klant->naam }}</span>
                                    <span class="text-gray-600 ml-2">{{ $klant->email }}</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    @if($klant->geboortedatum)
                                        {{ $klant->geboortedatum->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <!-- Medewerkers -->
            @if($todayBirthdays['medewerkers']->count() > 0)
                <div class="mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">👨‍💼 Medewerkers</h3>
                    <div class="space-y-2">
                        @foreach($todayBirthdays['medewerkers'] as $medewerker)
                            <div class="flex items-center justify-between bg-green-50 p-3 rounded-lg">
                                <div>
                                    <span class="font-medium">{{ $medewerker->voornaam }} {{ $medewerker->naam }}</span>
                                    <span class="text-gray-600 ml-2">{{ $medewerker->email }}</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    @if($medewerker->geboortedatum)
                                        {{ $medewerker->geboortedatum->format('d/m/Y') }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            <p class="text-gray-500 italic">Niemand jarig vandaag 😔</p>
        @endif
    </div>

    <!-- Upcoming Birthdays -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
            📅 Komende Verjaardagen (7 dagen)
        </h2>
        
        @if($upcomingBirthdays->count() > 0)
            <div class="space-y-4">
                @foreach($upcomingBirthdays as $dayData)
                    <div class="border-l-4 border-yellow-400 pl-4">
                        <h3 class="font-medium text-gray-900 mb-2">
                            {{ $dayData['date']->format('l d/m/Y') }}
                            <span class="text-sm text-gray-500">({{ $dayData['date']->diffForHumans() }})</span>
                        </h3>
                        
                        <!-- Klanten -->
                        @foreach($dayData['klanten'] as $klant)
                            <div class="text-sm text-blue-600 mb-1">
                                👥 {{ $klant->voornaam }} {{ $klant->naam }} ({{ $klant->email }})
                            </div>
                        @endforeach
                        
                        <!-- Medewerkers -->
                        @foreach($dayData['medewerkers'] as $medewerker)
                            <div class="text-sm text-green-600 mb-1">
                                👨‍💼 {{ $medewerker->voornaam }} {{ $medewerker->naam }} ({{ $medewerker->email }})
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 italic">Geen verjaardagen de komende 7 dagen</p>
        @endif
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-medium text-blue-900 mb-2">ℹ️ Systeem Informatie</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• Automatische emails worden dagelijks om 09:00 verstuurd</li>
            <li>• Alleen gebruikers met een geldig email adres krijgen een birthday email</li>
            <li>• Emails worden verstuurd vanuit info@bonami-sportcoaching.be</li>
            <li>• Command: <code class="bg-blue-100 px-2 py-1 rounded">php artisan birthday:send-emails</code></li>
        </ul>
    </div>
</div>

<script>
function sendManualEmails() {
    if (!confirm('Weet je zeker dat je nu handmatig birthday emails wilt versturen?')) {
        return;
    }
    
    const button = event.target;
    button.disabled = true;
    button.textContent = '📧 Versturen...';
    
    fetch('{{ route("admin.birthdays.send.manual") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Birthday emails succesvol verstuurd!\n\n' + data.output);
            location.reload();
        } else {
            alert('❌ Error: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        alert('❌ Network error: ' + error.message);
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = '📧 Verstuur Nu Handmatig';
    });
}
</script>
@endsection