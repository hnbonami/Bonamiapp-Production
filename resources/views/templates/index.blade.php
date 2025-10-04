@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold mb-2">Sjablonenlijst</h1>
    <a href="/sjabloon-manager/create" class="inline-block rounded-xl px-4 py-1 bg-blue-100 text-gray-900 font-semibold text-base shadow-sm hover:bg-blue-200 transition" style="text-decoration:none;z-index:10000;position:relative;">+ Sjabloon aanmaken</a>
    </div>
    <div>
        <input type="text" placeholder="Zoek sjabloon..." class="border rounded px-3 py-2" style="min-width:200px;" oninput="filterTemplates(this.value)">
    </div>
</div>

<script>
function filterTemplates(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.sjabloon-row').forEach(function(row) {
        var name = row.querySelector('.sjabloon-naam')?.textContent.toLowerCase() || '';
        var type = row.querySelector('.sjabloon-type')?.textContent.toLowerCase() || '';
        row.style.display = (name.includes(query) || type.includes(query)) ? '' : 'none';
    });
}
</script>

<div class="bg-white rounded-xl shadow">
    <!-- Grijze titelbalk -->
    <div class="grid grid-cols-3 px-6 py-3 rounded-t-xl" style="background:#f8f9fa;">
        <div class="font-semibold text-gray-600 text-sm">NAAM</div>
        <div class="font-semibold text-gray-600 text-sm">TYPE</div>
        <div class="font-semibold text-gray-600 text-sm text-right">ACTIES</div>
    </div>
    <div class="divide-y">
        @forelse($templates as $template)
            <div class="grid grid-cols-3 items-center p-3 sjabloon-row">
                <div class="font-semibold sjabloon-naam">{{ $template->name ?? 'Zonder naam' }}</div>
                <div class="text-sm text-gray-500 sjabloon-type">{{ $template->type }}</div>
                <div class="flex items-center gap-2 justify-end">
                    <a href="/sjablonen/{{ $template->id }}/edit" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800 hover:bg-orange-200 transition" title="Bewerk">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                    </a>
                    <a href="/sjablonen/{{ $template->id }}" aria-label="Bekijk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition" title="Bekijk">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <form action="/sjablonen/{{ $template->id }}/duplicate" method="POST" class="inline">
                        @csrf
                        <button type="submit" aria-label="Dupliceer" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition" title="Dupliceer">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                        </button>
                    </form>
                    <form action="/sjablonen/{{ $template->id }}" method="POST" onsubmit="return confirm('Sjabloon verwijderen?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="Verwijderen" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-700 hover:bg-rose-200 transition" title="Verwijderen">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-3 text-gray-500">Nog geen sjablonen.</div>
        @endforelse
    </div>
</div>
@endsection
