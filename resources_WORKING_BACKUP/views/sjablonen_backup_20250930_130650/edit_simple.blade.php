@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>{{ $sjabloon->naam }} - Bewerken</h1>
            
            <form method="POST" action="{{ route('sjablonen.update', $sjabloon) }}">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label class="form-label">Naam</label>
                    <input type="text" name="naam" class="form-control" value="{{ $sjabloon->naam }}" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Categorie</label>
                    <select name="categorie" class="form-control" required>
                        <option value="bikefit" {{ $sjabloon->categorie === 'bikefit' ? 'selected' : '' }}>Bikefit</option>
                        <option value="inspanningstest" {{ $sjabloon->categorie === 'inspanningstest' ? 'selected' : '' }}>Inspanningstest</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Testtype</label>
                    <input type="text" name="testtype" class="form-control" value="{{ $sjabloon->testtype }}">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Beschrijving</label>
                    <textarea name="beschrijving" class="form-control">{{ $sjabloon->beschrijving }}</textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Opslaan</button>
                <a href="{{ route('sjablonen.index') }}" class="btn btn-secondary">Terug</a>
            </form>
        </div>
    </div>
</div>
@endsection