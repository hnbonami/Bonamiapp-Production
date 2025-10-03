@extends('layouts.app')

@section('content')
<div class="container mx-auto p-8 text-center">
    <h1 class="text-2xl font-bold text-red-600 mb-4">{{ $message ?? 'Sjabloon niet gevonden.' }}</h1>
    <a href="{{ route('templates.index') }}" class="inline-block mt-4 px-6 py-2 bg-blue-100 text-blue-900 rounded shadow hover:bg-blue-200 transition">Terug naar sjablonenlijst</a>
</div>
@endsection
