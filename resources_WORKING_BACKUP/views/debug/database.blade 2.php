@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Database Debug Informatie</h1>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="card mb-4">
                <div class="card-header">
                    <h3>Database Connectie</h3>
                </div>
                <div class="card-body">
                    <p><strong>Database Driver:</strong> {{ config('database.default') }}</p>
                    <p><strong>Database Name:</strong> {{ config('database.connections.mysql.database') }}</p>
                    <p><strong>Database Host:</strong> {{ config('database.connections.mysql.host') }}</p>
                    <p><strong>Database Port:</strong> {{ config('database.connections.mysql.port') }}</p>
                    <p><strong>Database Username:</strong> {{ config('database.connections.mysql.username') }}</p>
                    
                    @php
                        try {
                            DB::connection()->getPdo();
                            $connected = true;
                            $error = null;
                        } catch(Exception $e) {
                            $connected = false;
                            $error = $e->getMessage();
                        }
                    @endphp
                    
                    @if($connected)
                        <div class="alert alert-success">
                            ✅ Database connectie succesvol!
                        </div>
                    @else
                        <div class="alert alert-danger">
                            ❌ Database connectie gefaald: {{ $error }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h3>Tabellen Check</h3>
                </div>
                <div class="card-body">
                    @php
                        $tables = [];
                        $tableStatus = [];
                        try {
                            $tables = DB::select('SHOW TABLES');
                            foreach($tables as $table) {
                                $tableName = array_values((array)$table)[0];
                                try {
                                    $count = DB::table($tableName)->count();
                                    $tableStatus[$tableName] = ['exists' => true, 'count' => $count, 'error' => null];
                                } catch(Exception $e) {
                                    $tableStatus[$tableName] = ['exists' => false, 'count' => 0, 'error' => $e->getMessage()];
                                }
                            }
                        } catch(Exception $e) {
                            $tablesError = $e->getMessage();
                        }
                    @endphp
                    
                    @if(isset($tablesError))
                        <div class="alert alert-danger">
                            Kan tabellen niet ophalen: {{ $tablesError }}
                        </div>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tabel Naam</th>
                                    <th>Status</th>
                                    <th>Aantal Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tableStatus as $tableName => $status)
                                    <tr>
                                        <td>{{ $tableName }}</td>
                                        <td>
                                            @if($status['exists'])
                                                <span class="badge bg-success">✅ OK</span>
                                            @else
                                                <span class="badge bg-danger">❌ Error</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($status['exists'])
                                                {{ $status['count'] }} records
                                            @else
                                                {{ $status['error'] }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            @if(Auth::check())
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Huidige Gebruiker Data</h3>
                    </div>
                    <div class="card-body">
                        <p><strong>User ID:</strong> {{ Auth::user()->id }}</p>
                        <p><strong>Naam:</strong> {{ Auth::user()->name }}</p>
                        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                        
                        @php
                            try {
                                $userSettings = DB::table('personal_data')->where('user_id', Auth::user()->id)->first();
                                $personalDataExists = true;
                            } catch(Exception $e) {
                                $userSettingsError = $e->getMessage();
                                $personalDataExists = false;
                            }
                        @endphp
                        
                        @if(!$personalDataExists)
                            <div class="alert alert-warning">
                                Kan personal_data niet ophalen: {{ $userSettingsError }}
                            </div>
                            
                            <div class="alert alert-danger">
                                <h4>🔧 PROBLEEM GEDETECTEERD: Personal_data tabel ontbreekt</h4>
                                <p><strong>Dit is de oorzaak van je probleem met het opslaan van instellingen!</strong></p>
                                <hr>
                                <form method="POST" action="{{ route('debug.fix-database') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg" style="font-size: 18px; padding: 15px 30px;">
                                        🔧 HERSTEL PERSONAL_DATA TABEL NU
                                    </button>
                                </form>
                                <small class="d-block mt-2 text-muted">Klik op deze knop om de ontbrekende tabel te herstellen</small>
                            </div>
                        @elseif($userSettings)
                            <div class="alert alert-success">
                                <h5>✅ Personal Data gevonden:</h5>
                                <pre>{{ json_encode($userSettings, JSON_PRETTY_PRINT) }}</pre>
                                
                                @php
                                    // Check if address fields exist
                                    $hasAddressFields = property_exists($userSettings, 'adres') && 
                                                      property_exists($userSettings, 'stad') && 
                                                      property_exists($userSettings, 'postcode');
                                @endphp
                                
                                @if(!$hasAddressFields)
                                    <hr>
                                    <div class="alert alert-warning">
                                        <h6>⚠️ Adres velden ontbreken nog!</h6>
                                        <p>De velden voor adres, stad, postcode en land zijn nog niet toegevoegd.</p>
                                        <form method="POST" action="{{ route('debug.add-address-fields') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-warning">
                                                🏠 Voeg Adres Velden Toe
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        ✅ Alle benodigde velden zijn aanwezig. Je kunt nu proberen je instellingen op te slaan!
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-info">
                                Geen personal_data gevonden voor deze gebruiker, maar tabel bestaat wel.
                                <form method="POST" action="{{ route('debug.fix-user-settings') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Maak Personal Data Record Aan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h3>Instagram Debug</h3>
                </div>
                <div class="card-body">
                    @php
                        try {
                            $instagramCount = DB::table('instagram_posts')->count();
                            $instagramPosts = DB::table('instagram_posts')->latest()->limit(3)->get();
                            $instagramError = null;
                        } catch(Exception $e) {
                            $instagramError = $e->getMessage();
                            $instagramCount = 0;
                            $instagramPosts = [];
                        }
                        
                        // Test Instagram Model
                        $modelExists = class_exists('App\\Models\\InstagramPost');
                        
                        // Check routes
                        try {
                            $instagramRoutes = collect(Route::getRoutes())->filter(function($route) {
                                return str_contains($route->uri(), 'instagram');
                            })->pluck('uri', 'methods');
                        } catch(Exception $e) {
                            $instagramRoutes = collect([]);
                        }
                    @endphp
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Database Status</h5>
                            @if($instagramError)
                                <div class="alert alert-danger">
                                    Instagram tabel fout: {{ $instagramError }}
                                </div>
                            @else
                                <div class="alert alert-success">
                                    ✅ Instagram tabel werkt: {{ $instagramCount }} posts
                                </div>
                                @if($instagramPosts->count() > 0)
                                    <h6>Laatste posts:</h6>
                                    <ul>
                                        @foreach($instagramPosts as $post)
                                            <li>{{ $post->titel }} ({{ $post->status }})</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <h5>Model & Routes</h5>
                            @if($modelExists)
                                <div class="alert alert-success">✅ InstagramPost Model bestaat</div>
                            @else
                                <div class="alert alert-danger">❌ InstagramPost Model ontbreekt</div>
                            @endif
                            
                            @if($instagramRoutes->count() > 0)
                                <div class="alert alert-success">✅ Instagram routes gevonden:</div>
                                <ul>
                                    @foreach($instagramRoutes as $methods => $uri)
                                        <li>{{ $uri }} ({{ is_array($methods) ? implode(', ', $methods) : $methods }})</li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="alert alert-danger">❌ Geen Instagram routes gevonden</div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Direct test Instagram post aanmaken -->
                    <hr>
                    <h5>Direct Test Instagram Post</h5>
                    <form method="POST" action="{{ route('debug.test-instagram') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="titel" class="form-label">Titel:</label>
                                    <input type="text" class="form-control" id="titel" name="titel" value="Test Post {{ now()->format('H:i:s') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="caption" class="form-label">Caption:</label>
                                    <input type="text" class="form-control" id="caption" name="caption" value="Dit is een test caption">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Direct Instagram Post Aanmaken</button>
                    </form>
                    
                    @if(session('instagram_test_result'))
                        <div class="alert alert-{{ session('instagram_test_success') ? 'success' : 'danger' }} mt-3">
                            {{ session('instagram_test_result') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3>Test Database Write</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('debug.test-write') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="test_data" class="form-label">Test Data:</label>
                            <input type="text" class="form-control" id="test_data" name="test_data" value="Test {{ now() }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Test Database Write</button>
                    </form>
                    
                    @if(session('write_test_result'))
                        <div class="alert alert-{{ session('write_test_success') ? 'success' : 'danger' }} mt-3">
                            {{ session('write_test_result') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection