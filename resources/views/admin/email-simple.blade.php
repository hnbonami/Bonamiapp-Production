@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">📧 Email Beheer</h1>
        <p class="text-gray-600 mt-2">Centraal beheer voor alle email functionaliteit</p>
    </div>

    <!-- Success Message -->
    <div class="mb-6 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-green-800">Email systeem basis succesvol geladen!</h3>
                <p class="mt-1 text-sm text-green-700">Alle core functionaliteit werkt nog steeds perfect.</p>
            </div>
        </div>
    </div>

    <!-- Current Email Features -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🔧 Huidige Email Features</h3>
            <div class="space-y-3">
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Testzadel herinneringen (handmatig)</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Automatische welkomstmails nieuwe klanten</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Automatische welkomstmails nieuwe medewerkers</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Automatische verjaardagsmails</span>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">🚀 Geplande Uitbreidingen</h3>
            <div class="space-y-3">
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Email template beheer ✅</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-green-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Automatische triggers configuratie ✅</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-yellow-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Email logs en statistieken</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="w-3 h-3 bg-yellow-400 rounded-full mr-3"></span>
                    <span class="text-gray-900">Bulk email functionaliteit</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">🎛️ Snelle Acties</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('testzadels.index') }}" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-medium text-gray-900">Testzadel Herinneringen</h4>
                    <p class="text-sm text-gray-500">Verstuur herinneringen voor uitstaande testzadels</p>
                </div>
            </a>

            <a href="{{ route('admin.email.templates') }}" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-medium text-gray-900">Email Templates</h4>
                    <p class="text-sm text-gray-500">Beheer en bewerk alle email templates</p>
                </div>
            </a>

            <a href="{{ route('admin.email.triggers') }}" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-medium text-gray-900">Automatische Triggers</h4>
                    <p class="text-sm text-gray-500">Test en beheer email automatisering</p>
                </div>
            </a>

            <a href="{{ route('admin.email.settings') }}" 
               class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex-shrink-0">
                    <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-medium text-gray-900">Email Instellingen</h4>
                    <p class="text-sm text-gray-500">Logo, kleuren en branding beheren</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Development Info -->
    <div class="mt-8 bg-green-50 border border-green-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-medium text-green-800">� Bestaande Templates Migreren</h3>
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