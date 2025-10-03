<?php
// Tijdelijke debug view voor sjablonen edit
// Deze view heeft minimale functionaliteit om te testen wat er mis gaat

$debug_vars = get_defined_vars();
unset($debug_vars['__data'], $debug_vars['obLevel']);
?>

@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Debug Sjablonen Edit</h1>
    
    <div class="debug-info">
        <h3>Beschikbare variabelen:</h3>
        <ul>
            @foreach($debug_vars as $name => $value)
                <li><strong>{{ $name }}:</strong> 
                    @if(is_null($value))
                        <span style="color: red;">NULL</span>
                    @elseif(is_array($value) || is_object($value))
                        {{ gettype($value) }} ({{ is_countable($value) ? count($value) : 'not countable' }})
                    @else
                        {{ gettype($value) }}
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    
    @if(isset($template))
        <div class="template-info">
            <h3>Template Info:</h3>
            <p><strong>Naam:</strong> {{ $template->naam ?? 'N/A' }}</p>
            <p><strong>Categorie:</strong> {{ $template->categorie ?? 'N/A' }}</p>
            <p><strong>Paginas:</strong> {{ $template->paginas ? $template->paginas->count() : 'Geen paginas' }}</p>
        </div>
    @endif
    
    @if(isset($templateKeys) && is_array($templateKeys))
        <div class="template-keys">
            <h3>Template Keys:</h3>
            @foreach($templateKeys as $category => $keys)
                <h4>{{ $category }}</h4>
                @if(is_array($keys))
                    @foreach($keys as $key)
                        <span class="badge">{{ is_object($key) ? $key->placeholder : $key }}</span>
                    @endforeach
                @endif
            @endforeach
        </div>
    @endif
    
    <a href="{{ route('sjablonen.index') }}" class="btn btn-secondary">Terug naar overzicht</a>
</div>
@endsection