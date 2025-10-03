@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Preview: {{ $template->name }}</h1>
    <style>
        {!! $template->css_content !!}
    </style>
    <div class="border p-3 mb-3"
        @if($template->background && Str::startsWith($template->background, 'backgrounds/') && preg_match('/\.(jpg|jpeg|png|gif)$/i', $template->background))
            style="background: url('{{ asset('storage/' . $template->background) }}') center/cover no-repeat; min-height:400px;"
        @else
            style="background:#f8f9fa;"
        @endif
    >
        {!! $template->html_content !!}
        @if($template->background && Str::endsWith($template->background, '.pdf'))
            <div class="mt-4">
                <a href="{{ asset('storage/' . $template->background) }}" target="_blank" class="underline font-semibold">Download achtergrond (PDF)</a>
            </div>
        @endif
    </div>
    <a href="{{ route('templates.index') }}" class="btn btn-secondary">Terug</a>
</div>
@endsection
