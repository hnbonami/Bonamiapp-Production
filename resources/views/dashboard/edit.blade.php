@extends('layouts.app')

@section('content')
<div class="container" style="max-width:800px;margin:2em auto;padding:2em;">
    <div style="margin-bottom:2em;">
        <a href="{{ route('dashboard.index') }}" style="color:#3b82f6;text-decoration:none;display:inline-flex;align-items:center;gap:0.5em;">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Terug naar dashboard
        </a>
    </div>

    <h1 class="text-3xl font-bold text-gray-900" style="margin-bottom:1.5em;">Widget Bewerken</h1>

    @if($errors->any())
        <div style="background:#fef2f2;color:#dc2626;padding:1em;margin-bottom:1em;border-radius:5px;">
            <ul style="margin:0;padding-left:1.5em;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.widgets.update', $widget) }}" method="POST" enctype="multipart/form-data" style="background:#fff;padding:2em;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        @csrf
        @method('PUT')

        <!-- Widget Type (read-only) -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Widget Type</label>
            <input type="text" value="{{ ucfirst($widget->type) }}" disabled style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;background:#f3f4f6;">
        </div>

        <!-- Titel -->
        <div style="margin-bottom:1.5em;">
            <label for="title" style="display:block;font-weight:600;margin-bottom:0.5em;">Titel *</label>
            <input type="text" name="title" id="title" value="{{ old('title', $widget->title) }}" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
        </div>

        <!-- Content (voor text/metric) -->
        @if(in_array($widget->type, ['text', 'metric']))
        <div style="margin-bottom:1.5em;">
            <label for="content" style="display:block;font-weight:600;margin-bottom:0.5em;">
                @if($widget->type === 'text') Tekst @else Waarde @endif *
            </label>
            @if($widget->type === 'text')
                <textarea name="content" id="content" rows="6" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">{{ old('content', $widget->content) }}</textarea>
            @else
                <input type="text" name="content" id="content" value="{{ old('content', $widget->content) }}" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
            @endif
        </div>
        @endif

        <!-- Chart Type & Data -->
        @if($widget->type === 'chart')
        <div style="margin-bottom:1.5em;">
            <label for="chart_type" style="display:block;font-weight:600;margin-bottom:0.5em;">Grafiek Type *</label>
            <select name="chart_type" id="chart_type" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                <option value="diensten" {{ old('chart_type', $widget->chart_type) === 'diensten' ? 'selected' : '' }}>Diensten Verdeling</option>
                <option value="status" {{ old('chart_type', $widget->chart_type) === 'status' ? 'selected' : '' }}>Status Verdeling</option>
                <option value="omzet" {{ old('chart_type', $widget->chart_type) === 'omzet' ? 'selected' : '' }}>Omzet Trend</option>
                <option value="medewerker" {{ old('chart_type', $widget->chart_type) === 'medewerker' ? 'selected' : '' }}>Prestaties per Medewerker</option>
            </select>
        </div>
        @endif

        <!-- Button Text & URL -->
        @if($widget->type === 'button')
        <div style="margin-bottom:1.5em;">
            <label for="button_text" style="display:block;font-weight:600;margin-bottom:0.5em;">Knop Tekst *</label>
            <input type="text" name="button_text" id="button_text" value="{{ old('button_text', $widget->button_text) }}" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
        </div>
        <div style="margin-bottom:1.5em;">
            <label for="button_url" style="display:block;font-weight:600;margin-bottom:0.5em;">URL *</label>
            <input type="text" name="button_url" id="button_url" value="{{ old('button_url', $widget->button_url) }}" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;" placeholder="/klanten/create">
        </div>
        @endif

        <!-- Image Upload -->
        @if($widget->type === 'image')
        <div style="margin-bottom:1.5em;">
            <label for="image" style="display:block;font-weight:600;margin-bottom:0.5em;">Nieuwe Afbeelding</label>
            @if($widget->image_path)
                <div style="margin-bottom:1em;">
                    <img src="{{ asset('storage/' . $widget->image_path) }}" alt="Current image" style="max-width:300px;border-radius:8px;">
                </div>
            @endif
            <input type="file" name="image" id="image" accept="image/*" style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
            <small style="color:#6b7280;">Laat leeg om huidige afbeelding te behouden</small>
        </div>
        @endif

        <!-- Kleuren -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1em;margin-bottom:1.5em;">
            <div>
                <label for="background_color" style="display:block;font-weight:600;margin-bottom:0.5em;">Achtergrond Kleur</label>
                <input type="color" name="background_color" id="background_color" value="{{ old('background_color', $widget->background_color ?? '#ffffff') }}" style="width:100%;height:45px;border:1px solid #d1d5db;border-radius:7px;">
            </div>
            <div>
                <label for="text_color" style="display:block;font-weight:600;margin-bottom:0.5em;">Tekst Kleur</label>
                <input type="color" name="text_color" id="text_color" value="{{ old('text_color', $widget->text_color ?? '#111111') }}" style="width:100%;height:45px;border:1px solid #d1d5db;border-radius:7px;">
            </div>
        </div>

        <!-- Visibility -->
        <div style="margin-bottom:1.5em;">
            <label for="visibility" style="display:block;font-weight:600;margin-bottom:0.5em;">Zichtbaarheid</label>
            <select name="visibility" id="visibility" required style="width:100%;padding:0.8em;border:1px solid #d1d5db;border-radius:7px;">
                @if(in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin', 'super_admin']))
                    <option value="everyone" {{ old('visibility', $widget->visibility) === 'everyone' ? 'selected' : '' }}>👥 Iedereen (incl. klanten)</option>
                    <option value="medewerkers" {{ old('visibility', $widget->visibility) === 'medewerkers' ? 'selected' : '' }}>👔 Alleen Medewerkers</option>
                    <option value="only_me" {{ old('visibility', $widget->visibility) === 'only_me' ? 'selected' : '' }}>🔒 Alleen Ik</option>
                @else
                    <option value="medewerkers" {{ old('visibility', $widget->visibility) === 'medewerkers' ? 'selected' : '' }}>👔 Alleen Medewerkers</option>
                    <option value="only_me" {{ old('visibility', $widget->visibility) === 'only_me' ? 'selected' : '' }}>🔒 Alleen Ik</option>
                @endif
            </select>
            <small style="color:#6b7280;display:block;margin-top:0.5em;">
                @if(in_array(auth()->user()->role, ['admin', 'organisatie_admin', 'superadmin', 'super_admin']))
                    Als admin kan je kiezen wie de widget kan zien
                @else
                    Als medewerker kan je de widget delen met collega's of privé houden
                @endif
            </small>
        </div>

        <!-- Submit -->
        <div style="display:flex;gap:1em;">
            <button type="submit" style="background:#c8e1eb;color:#111;padding:0.8em 2em;border-radius:7px;font-weight:600;border:none;cursor:pointer;">
                Widget Opslaan
            </button>
            <a href="{{ route('dashboard.index') }}" style="background:#e5e7eb;color:#111;padding:0.8em 2em;border-radius:7px;font-weight:600;text-decoration:none;display:inline-block;">
                Annuleren
            </a>
        </div>
    </form>
</div>
@endsection