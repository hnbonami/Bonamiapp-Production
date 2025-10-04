@php
use App\Models\StaffNote;
use Illuminate\Support\Str;
$user = Auth::user();
$isStaff = $user && in_array($user->role, ['admin', 'medewerker']);

// Haal notities op basis van gebruikersrol
$notes = StaffNote::with('user')
    ->visibleFor($user->role)
    ->orderBy('created_at', 'desc')
    ->take(3)
    ->get();
@endphp
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800">
                @if($isStaff)
                    📋 Staff Notities & Aankondigingen
                @else
                    📢 Nieuws & Aankondigingen
                @endif
            </h2>
            <a href="{{ route('staff-notes.index') }}" class="text-blue-600 hover:text-blue-800">
                Alle notities →
            </a>
        </div>
        
        <div class="space-y-12">
            @if($notes->count() > 0)
                @foreach($notes as $note)
                    <div class="border-l-4 border-[#c8e1eb] pl-6 py-4 mb-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-gray-800">                        {{ Str::limit(strip_tags($note->content), 100) }}</div>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $note->created_at->diffForHumans() }} door {{ $note->user->name }}
                                </p>
                            </div>
                            @if(!$note->read_at)
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Nieuw</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-center py-4">
                    @if($isStaff)
                        Nog geen notities. <a href="{{ route('staff-notes.create') }}" class="text-blue-600 hover:text-blue-800">Voeg er een toe →</a>
                    @else
                        Nog geen aankondigingen beschikbaar.
                    @endif
                </p>
            @endif
        </div>
    </div>
</div>
