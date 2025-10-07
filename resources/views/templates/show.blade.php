@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Sjabloon: {{ $template->name }}</h1>
    <div class="mb-3">
        <strong>Type:</strong> {{ $template->type }}
    </div>
    <div class="mb-3">
        <strong>HTML inhoud:</strong>
        <pre>{{ $template->html_content }}</pre>
    </div>
    <div class="mb-3">
        <strong>CSS inhoud:</strong>
        <pre>{{ $template->css_content }}</pre>
    </div>
    <a href="{{ route('temp.edit', $template) }}" class="btn btn-warning">Bewerken</a>
    <a href="{{ route('temp.index') }}" class="btn btn-secondary">Terug</a>
</div>
@endsection
