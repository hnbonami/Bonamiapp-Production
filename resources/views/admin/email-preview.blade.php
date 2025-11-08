@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Preview: {{ $template->name }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $template->description }}</p>
            </div>
            <a href="{{ route('admin.email.templates') }}" class="text-blue-600 hover:text-blue-800">
                ← Terug
            </a>
        </div>

        <!-- Preview Card -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Email Header -->
            <div class="bg-gray-100 px-6 py-4 border-b">
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="font-semibold text-gray-700 w-24">Onderwerp:</span>
                        <span class="text-gray-900">{{ $template->subject }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="font-semibold text-gray-700 w-24">Van:</span>
                        <span class="text-gray-600">{{ config('mail.from.name') }} &lt;{{ config('mail.from.address') }}&gt;</span>
                    </div>
                    <div class="flex items-center">
                        <span class="font-semibold text-gray-700 w-24">Type:</span>
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($template->type) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Email Content -->
            <div class="p-6">
                <div class="prose max-w-none">
                    {!! $content !!}
                </div>
            </div>

            <!-- Footer Info -->
            <div class="bg-gray-50 px-6 py-4 border-t">
                <p class="text-xs text-gray-500">
                    <strong>Let op:</strong> Dit is een preview met demo data. De echte email zal variabelen vervangen met actuele gegevens.
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-3">
            <a href="{{ route('admin.email.templates.edit', $template->id) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                Bewerken
            </a>
            <a href="{{ route('admin.email.templates') }}" 
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg transition">
                Sluiten
            </a>
        </div>
    </div>
</div>
@endsection