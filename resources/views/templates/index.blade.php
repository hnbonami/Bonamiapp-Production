@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Templates Overzicht</h1>
            
            <a href="{{ route('temp.create') }}" class="btn btn-primary mb-3">
                <i class="fas fa-plus"></i> Nieuw Template
            </a>

            @if($templates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Naam</th>
                                <th>Type</th>
                                <th>Beschrijving</th>
                                <th>Aangemaakt</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td>{{ $template->name ?? 'Zonder naam' }}</td>
                                    <td>{{ $template->type ?? 'Geen type' }}</td>
                                    <td>{{ $template->description ?? 'Geen beschrijving' }}</td>
                                    <td>{{ $template->created_at ? $template->created_at->format('d-m-Y H:i') : 'Onbekend' }}</td>
                                    <td>
                                        <a href="{{ route('temp.show', $template) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('temp.edit', $template) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('temp.destroy', $template) }}" method="POST" style="display: inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Weet je zeker dat je dit template wilt verwijderen?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <p>Er zijn nog geen templates aangemaakt.</p>
                    <a href="{{ route('temp.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Maak je eerste template aan
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
