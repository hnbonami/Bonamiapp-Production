@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sjabloon Editor</h1>
    <div class="row">
        <!-- Sleutellijst -->
        <div class="col-md-4">
            <h4>Sleutels</h4>
            <ul class="list-group" id="key-list">
                @foreach($keys as $key)
                    <li class="list-group-item">{{ $key }}</li>
                @endforeach
            </ul>
        </div>
        <!-- Editor + Live Preview -->
        <div class="col-md-8">
            <form method="POST" action="{{ route('templates.update', $template->id) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="content">Sjabloon inhoud</label>
                    <textarea id="template-content" name="content" class="form-control" rows="12">{{ old('content', $template->content ?? '') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Opslaan</button>
            </form>
            <hr>
            <h4>Live Preview</h4>
            <div class="card p-3" id="live-preview">
                {!! $previewHtml ?? '' !!}
            </div>
        </div>
    </div>
</div>

<script>
    // Simple live preview (updates on textarea change)
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('template-content');
        const preview = document.getElementById('live-preview');
        textarea.addEventListener('input', function() {
            // For demo: just show raw HTML
            preview.innerHTML = textarea.value;
        });
    });
</script>
@endsection
