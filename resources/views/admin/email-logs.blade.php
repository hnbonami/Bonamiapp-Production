@extends('layouts.app')

@section('title', 'Email Logs')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">📧 Email Logs</h1>
        @if(Route::has('admin.email.index'))
            <a href="{{ route('admin.email.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                ← Terug naar Email Dashboard
            </a>
        @else
            <a href="/admin/email" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                ← Terug naar Email Dashboard
            </a>
        @endif
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <span class="text-white text-sm font-medium">📧</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Totaal Verstuurd</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_sent'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <span class="text-white text-sm font-medium">📅</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Vandaag</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['sent_today'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <span class="text-white text-sm font-medium">📊</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Deze Week</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['sent_this_week'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                            <span class="text-white text-sm font-medium">❌</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Gefaald</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['failed_count'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <span class="text-white text-sm font-medium">🔄</span>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Via Nieuwe Templates</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['template_emails'] ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Recente Email Logs</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">De laatste 10 verstuurde emails</p>
        </div>
        <ul class="divide-y divide-gray-200">
            @forelse($logs as $log)
            <li>
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                @if($log->status === 'sent')
                                    <div class="w-2.5 h-2.5 bg-green-400 rounded-full"></div>
                                @elseif($log->status === 'failed')
                                    <div class="w-2.5 h-2.5 bg-red-400 rounded-full"></div>
                                @else
                                    <div class="w-2.5 h-2.5 bg-yellow-400 rounded-full"></div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center">
                                    <p class="text-sm font-medium text-indigo-600">{{ $log->subject }}</p>
                                    @if(isset($log->template_id) && $log->template_id)
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Nieuwe Template
                                        </span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Legacy
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-2 flex">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <span>Naar: {{ $log->recipient_email }}</span>
                                        @if($log->recipient_name)
                                            <span class="ml-1">({{ $log->recipient_name }})</span>
                                        @endif
                                    </div>
                                </div>
                                @if($log->error_message)
                                    <div class="mt-1">
                                        <span class="text-xs text-red-600">Fout: {{ $log->error_message }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="text-sm text-gray-900">
                                {{ $log->created_at->format('H:i') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $log->created_at->format('d-m-Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li>
                <div class="px-4 py-4 sm:px-6 text-center text-gray-500">
                    <p>Nog geen emails verstuurd</p>
                </div>
            </li>
            @endforelse
        </ul>
    </div>

    <!-- Integration Status -->
    @if(isset($stats['template_emails']) && $stats['template_emails'] == 0 && $stats['total_sent'] > 0)
    <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">⚠️ Nog geen nieuwe templates gebruikt</h3>
                <div class="mt-1 text-sm text-yellow-700">
                    <p>Je hebt emails verstuurd, maar deze gebruiken nog de oude templates. Voer <code class="bg-yellow-100 px-1 rounded">php artisan email:find-and-fix</code> uit om je code te upgraden.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Debug Information (alleen in debug mode) -->
    @if(config('app.debug'))
    <div class="mt-8 bg-gray-50 border border-gray-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-gray-800">🔧 Debug Informatie</h3>
                <div class="mt-1 text-sm text-gray-600">
                    <p><strong>Database Tabel:</strong> {{ DB::getTablePrefix() }}email_logs</p>
                    <p><strong>Laatste Log Entry:</strong> 
                        @if($logs->count() > 0)
                            {{ $logs->first()->created_at->diffForHumans() }}
                        @else
                            Geen entries gevonden
                        @endif
                    </p>
                    <p><strong>Email Queue Driver:</strong> {{ config('queue.default') }}</p>
                    <p><strong>Mail Driver:</strong> {{ config('mail.default') }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection