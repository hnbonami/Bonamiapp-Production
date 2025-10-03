@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Database Reparatie Tool</h1>
            
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <h3>Test Instellingen Update</h3>
                    <p>Test of het opslaan van instellingen werkt:</p>
                    
                    <form method="POST" action="{{ route('simple.test-save') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Voornaam:</label>
                                    <input type="text" name="voornaam" class="form-control" value="Test Naam">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Achternaam:</label>
                                    <input type="text" name="achternaam" class="form-control" value="Test Achternaam">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Adres:</label>
                                    <input type="text" name="adres" class="form-control" value="Test Straat 123">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Stad:</label>
                                    <input type="text" name="stad" class="form-control" value="Test Stad">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Test Opslaan</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3>Personal Data Tabel Status</h3>
                    
                    @php
                        try {
                            $tableExists = Schema::hasTable('personal_data');
                            if ($tableExists) {
                                $hasAdres = Schema::hasColumn('personal_data', 'adres');
                                $hasStad = Schema::hasColumn('personal_data', 'stad');
                                $hasPostcode = Schema::hasColumn('personal_data', 'postcode');
                                $userRecord = DB::table('personal_data')->where('user_id', Auth::id())->first();
                            }
                        } catch (Exception $e) {
                            $error = $e->getMessage();
                        }
                    @endphp
                    
                    @if(isset($error))
                        <div class="alert alert-danger">Fout: {{ $error }}</div>
                    @elseif(!$tableExists)
                        <div class="alert alert-warning">Personal_data tabel bestaat niet</div>
                        <form method="POST" action="{{ route('simple.fix') }}">
                            @csrf
                            <input type="hidden" name="action" value="create_table">
                            <button class="btn btn-success">Maak Personal_data Tabel</button>
                        </form>
                    @else
                        <div class="alert alert-success">✅ Personal_data tabel bestaat</div>
                        
                        @if(!$hasAdres || !$hasStad || !$hasPostcode)
                            <div class="alert alert-warning">⚠️ Adres velden ontbreken</div>
                            <form method="POST" action="{{ route('simple.fix') }}">
                                @csrf
                                <input type="hidden" name="action" value="add_fields">
                                <button class="btn btn-warning">Voeg Adres Velden Toe</button>
                            </form>
                        @else
                            <div class="alert alert-info">✅ Alle velden aanwezig</div>
                        @endif
                        
                        @if(!$userRecord)
                            <div class="alert alert-info">Geen record voor huidige gebruiker</div>
                            <form method="POST" action="{{ route('simple.fix') }}">
                                @csrf
                                <input type="hidden" name="action" value="create_user_record">
                                <button class="btn btn-primary">Maak Gebruiker Record</button>
                            </form>
                        @else
                            <div class="alert alert-success">✅ Gebruiker record bestaat</div>
                            <pre>{{ json_encode($userRecord, JSON_PRETTY_PRINT) }}</pre>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection