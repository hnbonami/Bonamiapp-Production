@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto pt-6 pb-12">
    <div class="mb-6">
        <a href="{{ route('klanten.show', $klant) }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar klantprofiel
        </a>
    </div>

    <h2 class="text-2xl font-semibold mb-6">Document Bewerken</h2>

    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <form action="{{ route('klanten.documenten.update', [$klant, $document]) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="naam" class="block text-sm font-medium text-gray-700 mb-2">Documentnaam</label>
                <input type="text" name="naam" id="naam" value="{{ old('naam', $document->naam ?? $document->titel) }}" required
                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                @error('naam')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="beschrijving" class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                <textarea name="beschrijving" id="beschrijving" rows="4"
                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('beschrijving', $document->beschrijving) }}</textarea>
                @error('beschrijving')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Bestandsinfo</h4>
                <dl class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Originele naam:</dt>
                        <dd class="text-gray-900">{{ $document->bestandsnaam }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Type:</dt>
                        <dd><span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ strtoupper($document->bestandstype) }}</span></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Grootte:</dt>
                        <dd class="text-gray-900">{{ number_format($document->bestandsgrootte / 1024, 0) }} KB</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Geüpload:</dt>
                        <dd class="text-gray-900">{{ $document->upload_datum ? \Carbon\Carbon::parse($document->upload_datum)->format('d-m-Y H:i') : $document->created_at->format('d-m-Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Opslaan
                </button>
                <a href="{{ route('klanten.show', $klant) }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 focus:outline-none">
                    Annuleren
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
