@extends('layouts.app')

@section('content')
<div style="max-width:800px;margin:2em auto;padding:2em;">
    <h1 style="font-size:2em;font-weight:700;margin-bottom:0.5em;">Widget Bewerken</h1>
    <p style="color:#666;margin-bottom:2em;">Pas de instellingen van je widget aan</p>

    <form action="{{ route('dashboard.widgets.update', $widget) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- Type (readonly) -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Widget Type</label>
            <input type="text" value="{{ ucfirst($widget->type) }}" disabled style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;background:#f5f5f5;">
            <input type="hidden" name="type" value="{{ $widget->type }}">
        </div>

        <!-- Titel -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Titel</label>
            <input type="text" name="title" value="{{ old('title', $widget->title) }}" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
        </div>

        @if($widget->type === 'text')
        <!-- Content voor Text widget -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Tekst Content</label>
            <textarea name="content" rows="5" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">{{ old('content', $widget->content) }}</textarea>
        </div>
        @endif

        @if($widget->type === 'metric')
        <!-- Content voor Metric widget -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Waarde</label>
            <input type="text" name="content" value="{{ old('content', $widget->content) }}" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
        </div>
        @endif

        @if($widget->type === 'button')
        <!-- Button settings -->
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Knop Tekst</label>
            <input type="text" name="button_text" value="{{ old('button_text', $widget->button_text) }}" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
        </div>
        <div style="margin-bottom:1.5em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Knop URL</label>
            <input type="text" name="button_url" value="{{ old('button_url', $widget->button_url) }}" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
        </div>
        @endif

        <!-- Styling -->
        <h3 style="font-size:1.3em;font-weight:700;margin:2em 0 1em;">Styling</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5em;margin-bottom:1.5em;">
            <div>
                <label style="display:block;font-weight:600;margin-bottom:0.5em;">Achtergrond</label>
                <input type="color" name="background_color" value="{{ old('background_color', $widget->background_color ?? '#ffffff') }}" style="width:100%;height:50px;border:1px solid #ddd;border-radius:7px;">
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:0.5em;">Tekstkleur</label>
                <input type="color" name="text_color" value="{{ old('text_color', $widget->text_color ?? '#000000') }}" style="width:100%;height:50px;border:1px solid #ddd;border-radius:7px;">
            </div>
        </div>

        <!-- Afmetingen -->
        <h3 style="font-size:1.3em;font-weight:700;margin:2em 0 1em;">Afmetingen</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5em;margin-bottom:1.5em;">
            <div>
                <label style="display:block;font-weight:600;margin-bottom:0.5em;">Breedte (1-12)</label>
                <input type="number" name="grid_width" value="{{ old('grid_width', $widget->grid_width) }}" min="1" max="12" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
            </div>
            <div>
                <label style="display:block;font-weight:600;margin-bottom:0.5em;">Hoogte (1-12)</label>
                <input type="number" name="grid_height" value="{{ old('grid_height', $widget->grid_height) }}" min="1" max="12" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
            </div>
        </div>

        <!-- Zichtbaarheid -->
        <div style="margin-bottom:2em;">
            <label style="display:block;font-weight:600;margin-bottom:0.5em;">Zichtbaarheid</label>
            <select name="visibility" style="width:100%;padding:0.8em;border:1px solid #ddd;border-radius:7px;">
                <option value="everyone" {{ old('visibility', $widget->visibility) === 'everyone' ? 'selected' : '' }}>Iedereen</option>
                <option value="medewerkers" {{ old('visibility', $widget->visibility) === 'medewerkers' ? 'selected' : '' }}>Alleen medewerkers</option>
                <option value="only_me" {{ old('visibility', $widget->visibility) === 'only_me' ? 'selected' : '' }}>Alleen ik</option>
            </select>
        </div>

        <!-- Buttons -->
        <div style="display:flex;gap:1em;">
            <button type="submit" style="flex:1;background:#c8e1eb;color:#111;padding:1em 2em;border-radius:7px;font-weight:600;border:none;cursor:pointer;">
                Widget Bijwerken
            </button>
            <a href="{{ route('dashboard.index') }}" style="flex:1;background:#e5e7eb;color:#111;padding:1em 2em;border-radius:7px;font-weight:600;text-align:center;text-decoration:none;display:inline-block;">
                Annuleren
            </a>
        </div>
    </form>
</div>
@endsection