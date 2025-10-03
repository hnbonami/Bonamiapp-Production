@extends('layouts.app')
@section('content')
<div style="background:#fffbcc;color:#7a6000;padding:10px;border:1px solid #f6e05e;border-radius:6px;margin-bottom:12px;">
    DEBUG: Minimal Dashboard (MIN) geladen — tijdelijke testbalk
    <span style="font-size:12px;color:#9a7f00;">(verwijder ik na test)</span>
    <!-- DASHBOARD-MIN-MARKER -->
    @php /* MARKER */ @endphp
    PING
  </div>

<h1 class="text-2xl font-bold mb-4">Dashboard (min)</h1>

<ul class="list-disc pl-5 space-y-2">
    <li>Klanten: {{ \App\Models\Klant::count() }}</li>
    <li>Medewerkers: {{ \App\Models\Medewerker::count() }}</li>
    <li><a class="text-blue-600 underline" href="{{ route('nieuwsbrief') }}">Nieuwsbrief</a></li>
    @auth
        <li>Ingelogd als: {{ auth()->user()->name }} ({{ auth()->user()->role }})</li>
    @endauth
</ul>
@endsection
