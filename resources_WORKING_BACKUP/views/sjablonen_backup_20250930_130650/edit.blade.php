@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>{{ $sjabloon->naam }} - Bewerken</h1>
                <a href="{{ route('sjablonen.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Terug naar Overzicht
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('sjablonen.update', $sjabloon->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Sjabloon Gegevens</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="naam" class="form-label">Sjabloon Naam *</label>
                                    <input type="text" 
                                           name="naam" 
                                           id="naam" 
                                           class="form-control @error('naam') is-invalid @enderror" 
                                           value="{{ old('naam', $sjabloon->naam) }}" 
                                           required>
                                    @error('naam')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="categorie" class="form-label">Categorie *</label>
                                    <select name="categorie" 
                                            id="categorie" 
                                            class="form-select @error('categorie') is-invalid @enderror" 
                                            required>
                                        <option value="bikefit" {{ old('categorie', $sjabloon->categorie) === 'bikefit' ? 'selected' : '' }}>
                                            Bikefit
                                        </option>
                                        <option value="inspanningstest" {{ old('categorie', $sjabloon->categorie) === 'inspanningstest' ? 'selected' : '' }}>
                                            Inspanningstest
                                        </option>
                                    </select>
                                    @error('categorie')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="testtype" class="form-label">Test Type</label>
                                    <input type="text" 
                                           name="testtype" 
                                           id="testtype" 
                                           class="form-control @error('testtype') is-invalid @enderror"
                                           value="{{ old('testtype', $sjabloon->testtype) }}"
                                           placeholder="Bijv. road_bikefit">
                                    @error('testtype')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="beschrijving" class="form-label">Beschrijving</label>
                            <textarea name="beschrijving" 
                                      id="beschrijving" 
                                      class="form-control @error('beschrijving') is-invalid @enderror" 
                                      rows="2">{{ old('beschrijving', $sjabloon->beschrijving) }}</textarea>
                            @error('beschrijving')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Sjabloon Inhoud</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Template Keys - Klik om toe te voegen:</label>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addToContent('@{{klant.naam}}')">@{{klant.naam}}</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addToContent('@{{klant.email}}')">@{{klant.email}}</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addToContent('@{{klant.telefoon}}')">@{{klant.telefoon}}</button>
                                
                                @if($sjabloon->categorie === 'bikefit')
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addToContent('{{bikefit.lengte_cm}}')">{{bikefit.lengte_cm}}</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addToContent('{{bikefit.testtype}}')">{{bikefit.testtype}}</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addToContent('{{bikefit.fietsmerk}}')">{{bikefit.fietsmerk}}</button>
                                @endif

                                @if($sjabloon->categorie === 'inspanningstest')
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="addToContent('{{test.max_hartslag}}')">{{test.max_hartslag}}</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="addToContent('{{test.vo2_max}}')">{{test.vo2_max}}</button>
                                    <button type="button" class="btn btn-sm btn-outline-info" onclick="addToContent('{{test.testtype}}')">{{test.testtype}}</button>
                                @endif
                            </div>
                        </div>
                        
                        <textarea name="inhoud" 
                                  id="inhoud" 
                                  class="form-control @error('inhoud') is-invalid @enderror" 
                                  rows="15" 
                                  placeholder="Voer hier de sjabloon inhoud in. Gebruik {{variabele}} voor dynamische velden.">{{ old('inhoud', $sjabloon->inhoud) }}</textarea>
                        @error('inhoud')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Gebruik template keys zoals @{{klant.naam}} voor dynamische inhoud.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('sjablonen.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuleren
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Wijzigingen Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function addToContent(templateKey) {
    const textarea = document.getElementById('inhoud');
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(cursorPos);
    
    textarea.value = textBefore + templateKey + textAfter;
    textarea.focus();
    textarea.setSelectionRange(cursorPos + templateKey.length, cursorPos + templateKey.length);
}
</script>
@endsection