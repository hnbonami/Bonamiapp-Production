@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2>🔧 Admin Dashboard</h2>
                    <p class="mb-0 text-muted">Beheer van alle admin functies</p>
                </div>

                <div class="card-body">
                    <!-- Beschikbare Admin Functies -->
                    <div class="mb-4">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-check-circle"></i> Beschikbare Functies
                        </h4>
                        <div class="row">
                            <!-- Database Tools -->
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid #007bff !important;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                                <i class="fas fa-database fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Database Tools</h5>
                                                <small class="text-muted">Beheer database & notities</small>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-3">Bekijk en beheer database tabellen, staff notities en systeemdata.</p>
                                        <a href="{{ route('admin.database.tools') }}" class="btn btn-primary w-100 py-3">
                                            <i class="fas fa-external-link-alt me-2"></i> 
                                            <strong>Database Tools Openen</strong>
                                            <br><small class="text-white-50">Klik hier om database beheer te openen</small>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Birthdays -->
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid #28a745 !important;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                                <i class="fas fa-birthday-cake fa-2x text-success"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Verjaardagen</h5>
                                                <small class="text-muted">Klant & medewerker verjaardagen</small>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-3">Overzicht van komende verjaardagen en automatische email herinneringen.</p>
                                        <a href="{{ route('birthdays.index') }}" class="btn btn-success w-100 py-3">
                                            <i class="fas fa-external-link-alt me-2"></i> 
                                            <strong>Verjaardagen Bekijken</strong>
                                            <br><small class="text-white-50">Klik hier om verjaardagen te beheren</small>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Testzadel Management -->
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0" style="border-left: 4px solid #17a2b8 !important;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                                                <i class="fas fa-bicycle fa-2x text-info"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Testzadel Beheer</h5>
                                                <small class="text-muted">Uitlening & retour beheer</small>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-3">Beheer testzadel uitleningen, herinneringen en retour administratie.</p>
                                        <a href="{{ route('testzadels.index') }}" class="btn btn-info w-100 py-3">
                                            <i class="fas fa-external-link-alt me-2"></i> 
                                            <strong>Testzadels Beheren</strong>
                                            <br><small class="text-white-50">Klik hier om testzadel uitleningen te beheren</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Geplande Functies -->
                    <div class="mb-4">
                        <h4 class="text-warning mb-3">
                            <i class="fas fa-clock"></i> Geplande Functies
                        </h4>
                        <div class="row">
                            <!-- Email Management -->
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0 bg-light" style="border-left: 4px solid #ffc107 !important;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                                                <i class="fas fa-envelope fa-2x text-warning"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Email Beheer</h5>
                                                <small class="text-muted">Template & verzend beheer</small>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-3">Beheer email templates, automatische mailings en verzendstatistieken.</p>
                                        <button class="btn btn-outline-warning w-100 py-3" disabled>
                                            <i class="fas fa-hourglass-half me-2"></i> 
                                            <strong>Binnenkort Beschikbaar</strong>
                                            <br><small class="text-muted">Email beheer wordt nog ontwikkeld</small>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- System Settings -->
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0 bg-light" style="border-left: 4px solid #6c757d !important;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                                                <i class="fas fa-cogs fa-2x text-secondary"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Systeem Instellingen</h5>
                                                <small class="text-muted">App configuratie</small>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-3">Beheer applicatie instellingen, gebruikersrechten en systeemconfiguratie.</p>
                                        <button class="btn btn-outline-secondary w-100 py-3" disabled>
                                            <i class="fas fa-hourglass-half me-2"></i> 
                                            <strong>Binnenkort Beschikbaar</strong>
                                            <br><small class="text-muted">Systeem instellingen worden nog ontwikkeld</small>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Reports -->
                            <div class="col-md-6 col-xl-4 mb-4">
                                <div class="card h-100 shadow-sm border-0 bg-light" style="border-left: 4px solid #343a40 !important;">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="rounded-circle bg-dark bg-opacity-10 p-3 me-3">
                                                <i class="fas fa-chart-bar fa-2x text-dark"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title mb-1">Rapporten & Analytics</h5>
                                                <small class="text-muted">Statistieken & inzichten</small>
                                            </div>
                                        </div>
                                        <p class="card-text text-muted small mb-3">Bekijk uitgebreide statistieken, rapporten en analytics van alle modules.</p>
                                        <button class="btn btn-outline-dark w-100 py-3" disabled>
                                            <i class="fas fa-hourglass-half me-2"></i> 
                                            <strong>Binnenkort Beschikbaar</strong>
                                            <br><small class="text-muted">Rapporten & analytics worden nog ontwikkeld</small>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info sectie -->
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info border-0 shadow-sm">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-info bg-opacity-15 p-3 me-3">
                                        <i class="fas fa-info-circle fa-2x text-info"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Admin Dashboard</h5>
                                        <p class="mb-0">
                                            Welkom in het admin dashboard! Hier vind je alle beheer functies van de Bonami applicatie. 
                                            Nieuwe functies worden geleidelijk toegevoegd en verbeterd.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection