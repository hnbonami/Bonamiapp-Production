@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Staff Notities & Aankondigingen</h2>
                @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']))
                    <a href="{{ route('staff-notes.create') }}" 
                       style="background:#c8e1eb;color:#111;padding:0.5em 1em;border-radius:8px;text-decoration:none;font-weight:600;border:1px solid #b5d5e0;box-shadow:0 2px 4px rgba(0,0,0,0.1);transition:all 0.2s ease;"
                       onmouseover="this.style.background='#b5d5e0'"
                       onmouseout="this.style.background='#c8e1eb'">
                        + Nieuwe notitie
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($notes->count() > 0)
                <div class="space-y-6">
                    @foreach($notes as $note)
                        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $note->title }}</h3>
                                        @if($note->visibility == 'all')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                👥 Alle gebruikers
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                🏢 Alleen staff
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 mb-3">
                                        Door {{ $note->user->name }} • {{ $note->created_at->format('d-m-Y H:i') }}
                                    </div>
                                </div>
                                
                                @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']))
                                    <div class="flex space-x-2 ml-4">
                                        <a href="{{ route('staff-notes.edit', $note) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            Bewerken
                                        </a>
                                        <form method="POST" 
                                              action="{{ route('staff-notes.destroy', $note) }}" 
                                              class="inline"
                                              onsubmit="return confirm('Weet je zeker dat je deze notitie wilt verwijderen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                Verwijderen
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="prose max-w-none text-gray-700" style="line-height: 1.6;">
                                {!! $note->content !!}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $notes->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 text-lg">Nog geen notities beschikbaar.</p>
                    @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']))
                        <a href="{{ route('staff-notes.create') }}" 
                           style="background:#c8e1eb;color:#111;padding:0.5em 1em;border-radius:8px;text-decoration:none;font-weight:600;border:1px solid #b5d5e0;box-shadow:0 2px 4px rgba(0,0,0,0.1);transition:all 0.2s ease;display:inline-block;margin-top:1em;"
                           onmouseover="this.style.background='#b5d5e0'"
                           onmouseout="this.style.background='#c8e1eb'">
                            Voeg de eerste notitie toe
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection