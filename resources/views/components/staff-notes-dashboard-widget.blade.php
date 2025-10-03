@php
    use Illuminate\Support\Str;
    // Dummy data voor test
    $notes = [
        (object)[
            'content' => 'Voorbeeldnotitie: Vergeet de meeting niet!',
            'created_at' => now(),
            'user' => (object)['name' => 'Admin'],
        ],
        (object)[
            'content' => 'Testtaak: Vul het rapport aan.',
            'created_at' => now()->subDay(),
            'user' => (object)['name' => 'Medewerker'],
        ],
    ];
    $unread = 2;
@endphp
<div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em 1.4em;min-width:260px;max-width:100%;display:flex;flex-direction:column;gap:0.7em;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1em;">
        <div style="font-size:1.15em;font-weight:700;">Notities & Taken</div>
        @if($unread > 0)
            <span style="background:#e11d48;color:#fff;font-size:13px;font-weight:700;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;">{{$unread}}</span>
        @endif
    </div>
    <ul style="margin:0;padding:0;list-style:none;">
        @forelse($notes as $note)
            <li style="margin-bottom:0.5em;">
                <div style="font-size:0.98em;font-weight:600;line-height:1.2;">{!! Str::limit(strip_tags($note->content), 60) !!}</div>
                <div style="font-size:0.85em;color:#6b7280;">{{ $note->created_at->format('d-m H:i') }} door {{ $note->user->name ?? 'Onbekend' }}</div>
            </li>
        @empty
            <li style="color:#888;font-size:0.95em;">Geen notities of taken.</li>
        @endforelse
    </ul>
    <a href="/staff-notes" style="margin-top:0.5em;align-self:flex-end;font-size:0.97em;color:#0d9488;font-weight:600;text-decoration:underline;">Bekijk alles</a>
</div>
@props(['notes' => collect()])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Notities & Taken</h2>
            <a href="{{ route('staffnotes.index') }}" class="text-blue-600 hover:text-blue-800">Alle notities</a>
        </div>
        
        <div class="space-y-4">
            @forelse($notes as $note)
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-gray-800">{!! $note->content !!}</div>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $note->created_at->diffForHumans() }} door {{ $note->user->name }}
                            </p>
                        </div>
                        @if(isset($note->read) && !$note->read)
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Nieuw</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Geen recente notities</p>
            @endforelse
        </div>
    </div>
</div>
