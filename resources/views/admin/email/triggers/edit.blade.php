@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">⚙️ Trigger Bewerken</h1>
            <p class="text-gray-600 mt-1">Configureer wanneer en hoe vaak emails automatisch worden verstuurd</p>
        </div>
        <a href="{{ route('admin.email.triggers') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar Triggers
        </a>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('admin.email.triggers.update', $trigger->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Er zijn fouten opgetreden:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="space-y-6">
                <!-- Naam -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Naam <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" 
                           value="{{ old('name', $trigger->name) }}" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Template -->
                <div>
                    <label for="email_template_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Email Template <span class="text-red-500">*</span>
                    </label>
                    <select name="email_template_id" id="email_template_id" required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecteer template...</option>
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

                <!-- Trigger Actief -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', $trigger->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        ✅ Trigger actief
                    </label>
                </div>

                <!-- Testzadel Instellingen (conditionally shown) -->
                @if($trigger->trigger_type === 'testzadel_reminder')
                <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">🎯 Testzadel Herinnering Instellingen</h3>
                    
                    <div class="space-y-4">
                        <!-- Herinnering Dagen -->
                        <div>
                            <label for="reminder_days" class="block text-sm font-medium text-blue-700 mb-2">
                                📅 Verstuur herinnering na X dagen <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center space-x-3">
                                <input type="number" name="reminder_days" id="reminder_days" 
                                       value="{{ old('reminder_days', $trigger->trigger_data['days_before_due'] ?? 7) }}"
                                       min="1" max="365" required
                                       class="block w-24 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center font-medium">
                                <span class="text-sm text-blue-600 font-medium">dagen na uitlening</span>
                            </div>
                            <p class="mt-1 text-xs text-blue-600">
                                Huidig: {{ $trigger->trigger_data['days_before_due'] ?? 7 }} dagen
                            </p>
                        </div>

                        <!-- Maximum Herinneringen -->
                        <div>
                            <label for="max_reminders" class="block text-sm font-medium text-blue-700 mb-2">
                                🔢 Maximum aantal herinneringen
                            </label>
                            <select name="max_reminders" id="max_reminders"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" 
                                            {{ old('max_reminders', $trigger->settings['max_reminders'] ?? 3) == $i ? 'selected' : '' }}>
                                        {{ $i }} herinnering{{ $i > 1 ? 'en' : '' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Verjaardag Instellingen -->
                @if($trigger->trigger_type === 'birthday')
                <div class="mt-8 p-6 bg-green-50 rounded-lg border border-green-200">
                    <h3 class="text-lg font-semibold text-green-900 mb-4">🎂 Verjaardag Email Instellingen</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="birthday_time" class="block text-sm font-medium text-green-700 mb-2">
                                ⏰ Verstuur tijd
                            </label>
                            <input type="time" name="birthday_time" id="birthday_time" 
                                   value="{{ old('birthday_time', $trigger->settings['send_time'] ?? '09:00') }}"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>
                @endif

                <!-- Doorverwijzing Instellingen -->
                @if($trigger->trigger_type === 'referral_thank_you')
                <div class="mt-8 p-6 bg-purple-50 rounded-lg border border-purple-200">
                    <h3 class="text-lg font-semibold text-purple-900 mb-4">🤝 Doorverwijzing Email Instellingen</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="send_immediately" id="send_immediately" value="1" 
                                   {{ old('send_immediately', $trigger->settings['send_immediately'] ?? true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="send_immediately" class="ml-2 block text-sm text-purple-900">
                                Direct versturen bij doorverwijzing
                            </label>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Stats -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">📊 Statistieken</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-gray-500">Emails Verstuurd</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $trigger->emails_sent ?? 0 }}</div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-gray-500">Laatste Run</div>
                        <div class="text-sm text-gray-900">
                            {{ $trigger->last_run_at ? $trigger->last_run_at->format('d-m-Y H:i') : 'Nog nooit uitgevoerd' }}
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-gray-500">Status</div>
                        <div class="text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $trigger->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $trigger->is_active ? 'Actief' : 'Inactief' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="mt-8 flex justify-end space-x-3 border-t border-gray-200 pt-6">
                <a href="{{ route('admin.email.triggers') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Annuleren
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Trigger Opslaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection