@extends('layouts.app')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📧 Email Beheer</h1>
        <p class="text-gray-600 mt-2">Centraal beheer voor alle email functionaliteit</p>
    </div>

    <!-- Quick Actions - Horizontal Layout -->
    <div class="flex space-x-4 mb-8">

        <a href="{{ route('admin.email.templates') }}" 
           class="flex-1 bg-white shadow rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <svg class="h-6 w-6 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h4 class="text-sm font-medium text-gray-900">Templates</h4>
        </a>

        <a href="{{ route('admin.email.triggers') }}" 
           class="flex-1 bg-white shadow rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <svg class="h-6 w-6 text-orange-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <h4 class="text-sm font-medium text-gray-900">Triggers</h4>
        </a>

        <a href="{{ route('admin.email.logs') }}" 
           class="flex-1 bg-white shadow rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <svg class="h-6 w-6 text-red-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h4 class="text-sm font-medium text-gray-900">Logs</h4>
        </a>

        <a href="{{ route('admin.email.settings') }}" 
           class="flex-1 bg-white shadow rounded-lg p-4 text-center hover:shadow-md transition-shadow">
            <svg class="h-6 w-6 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h4 class="text-sm font-medium text-gray-900">Instellingen</h4>
        </a>
    </div>

    <!-- Bulk Email Section -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">📬 Bulk Email Acties</h3>
                <p class="text-sm text-gray-600">Verstuur emails naar meerdere ontvangers tegelijk</p>
            </div>
        </div>

        @if(isset($subscriptionStats))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-600">Klanten</div>
                <div class="text-2xl font-bold text-blue-900">{{ $subscriptionStats['total_klanten'] }}</div>
                <div class="text-xs text-blue-600">{{ $subscriptionStats['subscribed_klanten'] }} geabonneerd</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-green-600">Medewerkers</div>
                <div class="text-2xl font-bold text-green-900">{{ $subscriptionStats['total_medewerkers'] }}</div>
                <div class="text-xs text-green-600">{{ $subscriptionStats['subscribed_medewerkers'] }} geabonneerd</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-yellow-600">Afgemeld</div>
                <div class="text-2xl font-bold text-yellow-900">{{ $subscriptionStats['unsubscribed_total'] }}</div>
                <div class="text-xs text-yellow-600">Unsubscribed</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-600">Subscription Rate</div>
                <div class="text-2xl font-bold text-purple-900">{{ $subscriptionStats['subscription_rate'] }}%</div>
                <div class="text-xs text-purple-600">Actief geabonneerd</div>
            </div>
        </div>
        @endif

        <form id="bulkEmailForm" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">Email Template</label>
                    <select name="template_id" id="template_id" required 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Selecteer een template...</option>
                        @if(isset($templates))
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}">{{ $template->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Email Onderwerp</label>
                    <input type="text" name="subject" id="subject" required 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Voer het email onderwerp in...">
                </div>
            </div>

            <div class="mb-6">
                <label for="custom_message" class="block text-sm font-medium text-gray-700 mb-2">Extra Bericht (Optioneel)</label>
                <textarea name="custom_message" id="custom_message" rows="3"
                          class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                          placeholder="Voeg een persoonlijk bericht toe..."></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <button type="button" onclick="setBulkAction('customers')" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-800 hover:text-gray-900"
                        style="background-color: #c8e1eb; border-color: #c8e1eb;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Alle Klanten
                    @if(isset($subscriptionStats))
                        <span class="ml-1 text-xs">({{ $subscriptionStats['subscribed_klanten'] }})</span>
                    @endif
                </button>

                <button type="button" onclick="setBulkAction('employees')" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-800 hover:text-gray-900"
                        style="background-color: #c8e1eb; border-color: #c8e1eb;">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0h-8m8 0v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6h16z"/>
                    </svg>
                    Alle Medewerkers
                    @if(isset($subscriptionStats))
                        <span class="ml-1 text-xs">({{ $subscriptionStats['subscribed_medewerkers'] }})</span>
                    @endif
                </button>

                <button type="button" onclick="showPreview()" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Preview
                </button>

                <a href="{{ route('admin.email.unsubscribed') }}" 
                   class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636"/>
                    </svg>
                    Unsubscribed
                    @if(isset($subscriptionStats))
                        <span class="ml-1 text-xs">({{ $subscriptionStats['unsubscribed_total'] }})</span>
                    @endif
                </a>
            </div>
        </form>

        <script>
        function setBulkAction(action) {
            const form = document.getElementById('bulkEmailForm');
            
            if (!validateForm()) {
                return;
            }
            
            if (action === 'customers') {
                form.action = '{{ route("admin.email.bulk.customers") }}';
            } else if (action === 'employees') {
                form.action = '{{ route("admin.email.bulk.employees") }}';
            }
            
            if (confirm('Weet je zeker dat je deze bulk email wilt versturen?')) {
                form.submit();
            }
        }
        
        function validateForm() {
            const templateId = document.getElementById('template_id').value;
            const subject = document.getElementById('subject').value;
            
            if (!templateId) {
                alert('Selecteer eerst een email template');
                return false;
            }
            
            if (!subject.trim()) {
                alert('Voer een email onderwerp in');
                return false;
            }
            
            return true;
        }
        
        function showPreview() {
            if (!validateForm()) {
                return;
            }
            
            const formData = new FormData();
            formData.append('template_id', document.getElementById('template_id').value);
            formData.append('subject', document.getElementById('subject').value);
            formData.append('custom_message', document.getElementById('custom_message').value);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch('{{ route("admin.email.preview") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Create preview modal
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
                    modal.onclick = function(e) {
                        if (e.target === modal) {
                            document.body.removeChild(modal);
                        }
                    };
                    
                    modal.innerHTML = `
                        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-gray-900">📧 Email Preview</h3>
                                <button onclick="document.body.removeChild(this.closest('.fixed'))" 
                                        class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="mb-4">
                                <strong>Onderwerp:</strong> ${data.subject}
                            </div>
                            <div class="border rounded-lg p-4 bg-gray-50 max-h-96 overflow-y-auto">
                                ${data.body}
                            </div>
                            <div class="mt-4 text-sm text-gray-500">
                                <p>📝 Dit is een preview met voorbeeld data (Jan Janssen)</p>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(modal);
                } else {
                    alert('Fout bij laden preview: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Fout bij laden preview');
            });
        }
        </script>
    </div>

    <!-- Geplande Uitbreidingen -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">🚀 Geplande Uitbreidingen</h3>
        <ul class="space-y-2">
            <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="line-through text-gray-500">Email template beheer</span>
            </li>
            <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="line-through text-gray-500">Automatische triggers configuratie</span>
            </li>
            <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="line-through text-gray-500">Email logs en statistieken</span>
            </li>
            <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="line-through text-gray-500">Bulk email functionaliteit</span>
            </li>
            <li class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span class="line-through text-gray-500">Unsubscribe systeem</span>
            </li>
            <li class="flex items-center">
                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gray-700">Email analytics en tracking</span>
            </li>
            <li class="flex items-center">
                <svg class="w-5 h-5 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span class="text-gray-700">Email scheduling</span>
            </li>
        </ul>
    </div>

    <!-- Development Info -->
    <div class="mt-8 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-green-800">📧 Bestaande Templates Migreren</h3>
                <div class="mt-1 text-sm text-green-700">
                    <p><strong>GEVONDEN:</strong> Je hebt bestaande email templates in <code>/resources/views/emails/</code></p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            📧 birthday.blade.php
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            🚴‍♂️ testzadel-reminder.blade.php
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            👋 klant-invitation.blade.php
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            ✅ account_created.blade.php
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            👤 medewerker-invitation.blade.php
                        </span>
                    </div>
                </div>
            </div>
            <div class="ml-4">
                <form action="{{ route('admin.email.migrate-templates') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Migreer Templates
                    </button>
                </form>
            </div>
        </div>
        <div class="mt-3 text-xs text-green-600">
            <p>Dit converteert je bestaande templates naar het nieuwe systeem met je logo en branding! ✨</p>
        </div>
    </div>
</div>
@endsection