@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg mx-auto">
        <div class="text-center">
            <h1 class="text-2xl font-bold mb-4 text-gray-800">Bestand niet gevonden</h1>
            
            <p class="text-gray-600 mb-4">
                Het bestand <code class="bg-gray-100 px-2 py-1 rounded">{{ $upload->path }}</code> kon niet worden gevonden.
            </p>
            
            <p class="text-gray-600 mb-6">
                Het bestand is mogelijk verwijderd of verplaatst.
            </p>

            <a href="{{ url()->previous() }}" class="inline-block bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition-colors">
                Ga terug
            </a>
        </div>
    </div>
</div>
@endsection