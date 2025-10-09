@extends('layouts.app')

@section('content')
<div class="container">
    <h1>🔍 Login Activity Debug</h1>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Vandaag</h5>
                    <h2>{{ $stats['total_logins_today'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Deze Week</h5>
                    <h2>{{ $stats['total_logins_week'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Unieke Vandaag</h5>
                    <h2>{{ $stats['unique_users_today'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5>Totaal</h5>
                    <h2>{{ $stats['total_login_activities'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Login Activities ({{ $loginActivities->total() }})</h5>
        </div>
        <div class="card-body">
            @if($loginActivities->count() > 0)
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gebruiker</th>
                            <th>Login Tijd</th>
                            <th>IP Adres</th>
                            <th>Browser</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($loginActivities as $activity)
                            <tr>
                                <td>
                                    @if($activity->user)
                                        {{ $activity->user->name }}<br>
                                        <small class="text-muted">{{ $activity->user->email }}</small>
                                    @else
                                        <span class="text-danger">Gebruiker niet gevonden</span>
                                    @endif
                                </td>
                                <td>{{ $activity->logged_in_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $activity->ip_address }}</td>
                                <td>
                                    <small>{{ Str::limit($activity->user_agent, 50) }}</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-center">
                    {{ $loginActivities->links() }}
                </div>
            @else
                <p class="text-muted">Geen login activities gevonden.</p>
            @endif
        </div>
    </div>
</div>
@endsection