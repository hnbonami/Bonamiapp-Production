@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🔧 Trigger Bewerken</h1>
            <p class="text-gray-600 mt-2">Pas de instellingen van deze email trigger aan</p>
        </div>
        <a href="{{ route('admin.email.triggers') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Triggers
        </a>
    </div>

    <form method="POST" action="{{ route('admin.email.triggers.update', $trigger->id) }}" class="bg-white shadow rounded-lg p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Naam</label>
                <input type="text" name="name" id="name" 
                       value="{{ old('name', $trigger->name) }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email_template_id" class="block text-sm font-medium text-gray-700">Email Template</label>
                <select name="email_template_id" id="email_template_id" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" 
                                {{ old('email_template_id', $trigger->email_template_id) == $template->id ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>
                @error('email_template_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       {{ old('is_active', $trigger->is_active) ? 'checked' : '' }}
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Trigger actief
                </label>
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Trigger Type</label>
            <div class="mt-2 p-3 bg-gray-50 rounded-md">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $trigger->getTypeNameAttribute() }}
                </span>
                <p class="text-sm text-gray-600 mt-1">
                    @switch($trigger->type)
                        @case('testzadel_reminder')
                            Automatische herinneringen voor te late testzadel retours
                            @break
                        @case('birthday')
                            Verjaardagswensen voor klanten op hun verjaardag
                            @break
                        @case('welcome_customer')
                            Welkomst email voor nieuwe klanten
                            @break
                        @default
                            Algemene email trigger
                    @endswitch
                </p>
            </div>
        </div>

        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700">Huidige Template Preview</label>
            <div class="mt-2 p-4 bg-gray-50 rounded-md">
                <h4 class="font-medium">{{ $trigger->emailTemplate->name ?? 'Geen template' }}</h4>
                <p class="text-sm text-gray-600">{{ $trigger->emailTemplate->description ?? 'Geen beschrijving' }}</p>
                @if($trigger->emailTemplate)
                    <div class="mt-2">
                        <span class="text-xs text-gray-500">Onderwerp:</span>
                        <p class="text-sm">{{ $trigger->emailTemplate->subject }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Testzadel Specifieke Instellingen -->
        @if($trigger->type === 'testzadel_reminder')
        <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
            <h3 class="text-lg font-semibold text-blue-900 mb-4">🎯 Testzadel Herinnering Instellingen</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Herinnering Dagen -->
                <div>
                    <label for="reminder_days" class="block text-sm font-medium text-blue-700 mb-2">
                        📅 Verstuur herinnering na X dagen <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="number" name="reminder_days" id="reminder_days" 
                               value="{{ old('reminder_days', $trigger->conditions['reminder_days'] ?? 7) }}"
                               min="1" max="365" required
                               class="block w-24 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center font-medium">
                        <span class="text-sm text-blue-600 font-medium">dagen na uitlening</span>
                    </div>
                    <p class="mt-1 text-xs text-blue-600">
                        Huidig: {{ $trigger->conditions['reminder_days'] ?? 7 }} dagen
                    </p>
                    @error('reminder_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Herinnering Interval -->
                <div>
                    <label for="reminder_interval" class="block text-sm font-medium text-blue-700 mb-2">
                        ⏰ Interval tussen herinneringen
                    </label>
                    <div class="flex items-center space-x-3">
                        <input type="number" name="reminder_interval" id="reminder_interval" 
                               value="{{ old('reminder_interval', $trigger->settings['reminder_interval'] ?? 7) }}"
                               min="1" max="30"
                               class="block w-24 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center font-medium">
                        <span class="text-sm text-blue-600 font-medium">dagen tussen herinneringen</span>
                    </div>
                    <p class="mt-1 text-xs text-blue-600">
                        Voor meerdere herinneringen
                    </p>
                </div>
            </div>

            <div class="mt-6">
                <!-- Maximum Herinneringen -->
                <div>
                    <label for="max_reminders" class="block text-sm font-medium text-blue-700 mb-2">
                        🔢 Maximum aantal herinneringen
                    </label>
                    <select name="max_reminders" id="max_reminders"
                            class="block w-full md:w-1/2 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @for($i = 1; $i <= 10; $i++)
                            <option value="{{ $i }}" 
                                    {{ old('max_reminders', $trigger->settings['max_reminders'] ?? 3) == $i ? 'selected' : '' }}>
                                {{ $i }} herinnering{{ $i > 1 ? 'en' : '' }}
                            </option>
                        @endfor
                    </select>
                    <p class="mt-1 text-xs text-blue-600">
                        Totaal aantal keren dat een herinnering verstuurd wordt
                    </p>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="mt-6 p-4 bg-blue-100 rounded-md border border-blue-300">
                <h4 class="font-medium text-blue-900 text-sm mb-3">📋 Herinnering Schema Preview:</h4>
                <div class="text-sm text-blue-700 space-y-2" id="reminder-preview">
                    <div class="flex items-center space-x-2">
                        <span>📦</span>
                        <span>Testzadel uitgeleend op dag 0</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>📧</span>
                        <span>1e herinnering na <span id="first-reminder" class="font-bold text-blue-900">{{ $trigger->conditions['reminder_days'] ?? 7 }}</span> dagen</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>📧</span>
                        <span>2e herinnering na <span id="second-reminder" class="font-bold text-blue-900">{{ ($trigger->conditions['reminder_days'] ?? 7) + ($trigger->settings['reminder_interval'] ?? 7) }}</span> dagen</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span>📧</span>
                        <span>3e herinnering na <span id="third-reminder" class="font-bold text-blue-900">{{ ($trigger->conditions['reminder_days'] ?? 7) + (2 * ($trigger->settings['reminder_interval'] ?? 7)) }}</span> dagen</span>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Live preview update voor testzadel instellingen
            document.addEventListener('DOMContentLoaded', function() {
                function updatePreview() {
                    const reminderDays = parseInt(document.getElementById('reminder_days').value) || 7;
                    const reminderInterval = parseInt(document.getElementById('reminder_interval').value) || 7;
                    
                    document.getElementById('first-reminder').textContent = reminderDays;
                    document.getElementById('second-reminder').textContent = reminderDays + reminderInterval;
                    document.getElementById('third-reminder').textContent = reminderDays + (2 * reminderInterval);
                }
                
                const reminderDaysInput = document.getElementById('reminder_days');
                const reminderIntervalInput = document.getElementById('reminder_interval');
                
                if (reminderDaysInput && reminderIntervalInput) {
                    reminderDaysInput.addEventListener('input', updatePreview);
                    reminderIntervalInput.addEventListener('input', updatePreview);
                    
                    // Initial update
                    updatePreview();
                }
            });
        </script>
        @endif

        <div class="mt-8 flex justify-end space-x-4">
            <a href="{{ route('admin.email.triggers') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Annuleren
            </a>
            <button type="submit" 
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Opslaan
            </button>
        </div>
    </form>
</div>
@endsection