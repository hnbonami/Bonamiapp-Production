@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Email Migratie</h1>
                    <p class="mt-2 text-gray-600">Migreer oude email systemen naar nieuwe Bonami Email Setup</p>
                </div>
                <a href="/admin/email" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ← Terug naar Email Admin
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Migration Status -->
        <div class="bg-white shadow rounded-lg mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Migratie Status</h3>
            </div>
            <div class="px-6 py-4">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Email Templates Tabel</span>
                        <span class="text-green-600">✅ Bestaat</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Email Triggers Tabel</span>
                        <span class="text-green-600">✅ Bestaat</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Email Logs Tabel</span>
                        <span class="text-green-600">✅ Bestaat</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Email Subscriptions Tabel</span>
                        <span class="text-green-600">✅ Bestaat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Migration Actions -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Migratie Acties</h3>
            </div>
            <div class="px-6 py-4 space-y-6">
                
                <!-- Full Migration -->
                <div class="border border-blue-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-blue-900 mb-2">Volledige Email Migratie</h4>
                    <p class="text-gray-600 mb-4">
                        Voert alle email gerelateerde migraties uit en zet standaard templates en triggers op.
                    </p>
                    <form action="/admin/email/migration" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                            🚀 Start Volledige Migratie
                        </button>
                    </form>
                </div>

                <!-- Repair Migration -->
                <div class="border border-orange-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-orange-900 mb-2">Repareer Email Systeem</h4>
                    <p class="text-gray-600 mb-4">
                        Repareert bestaande email triggers en templates. Gebruik dit als er problemen zijn.
                    </p>
                    <div class="flex space-x-4">
                        <button onclick="repairTriggers()" 
                                class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors">
                            🔧 Repareer Triggers
                        </button>
                        <button onclick="setupTriggers()" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors">
                            ⚙️ Setup Triggers
                        </button>
                    </div>
                </div>

                <!-- Manual Commands -->
                <div class="border border-gray-200 rounded-lg p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Handmatige Commando's</h4>
                    <p class="text-gray-600 mb-4">
                        Voor gevorderde gebruikers: voer deze commando's uit in de terminal.
                    </p>
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <code class="text-sm">
                            # Voer email migraties uit<br>
                            php artisan migrate --path=database/migrations<br><br>
                            
                            # Repareer email triggers<br>
                            php artisan email:cleanup-triggers --fix<br><br>
                            
                            # Setup standaard triggers<br>
                            php artisan db:seed --class=EmailTriggerSeeder<br><br>
                            
                            # Test email systeem<br>
                            php artisan email:migrate --test
                        </code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Migration Info -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Migratie Informatie</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p><strong>Veiligheid:</strong> Alle migraties worden veilig uitgevoerd en controleren of tabellen al bestaan.</p>
                        <p><strong>Data:</strong> Bestaande data wordt behouden tijdens migraties.</p>
                        <p><strong>Rollback:</strong> Migraties kunnen worden teruggedraaid via Laravel's migrate:rollback.</p>
                        <p><strong>Backup:</strong> Maak altijd een database backup voordat je migraties uitvoert.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function repairTriggers() {
    if (confirm('Weet je zeker dat je de email triggers wilt repareren?')) {
        fetch('/admin/email/repair-triggers', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Email triggers succesvol gerepareerd!');
                location.reload();
            } else {
                alert('❌ Fout bij repareren: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Er is een fout opgetreden.');
            console.error('Error:', error);
        });
    }
}

function setupTriggers() {
    if (confirm('Weet je zeker dat je de standaard triggers wilt opzetten?')) {
        fetch('/admin/email/setup-triggers', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                alert('✅ Standaard triggers succesvol opgezet!');
                location.reload();
            } else {
                alert('❌ Fout bij opzetten van triggers.');
            }
        })
        .catch(error => {
            alert('❌ Er is een fout opgetreden.');
            console.error('Error:', error);
        });
    }
}
</script>
@endsection