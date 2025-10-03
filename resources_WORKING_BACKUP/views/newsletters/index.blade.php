@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold mb-2">Nieuwsbrieven</h1>
    <a href="/nieuwsbrieven/nieuw" class="inline-block rounded-xl px-4 py-1 bg-blue-100 text-gray-900 font-semibold text-base shadow-sm hover:bg-blue-200 transition" style="text-decoration:none;z-index:10000;position:relative;">+ Nieuwsbrief aanmaken</a>
  </div>
  <div>
    <input type="text" placeholder="Zoek nieuwsbrief..." class="border rounded px-3 py-2" style="min-width:200px;" oninput="filterNewsletters(this.value)">
  </div>
</div>

<script>
function filterNewsletters(query) {
  query = query.toLowerCase();
  document.querySelectorAll('.nieuwsbrief-row').forEach(function(row) {
    var title = row.querySelector('.nieuwsbrief-titel')?.textContent.toLowerCase() || '';
    var subject = row.querySelector('.nieuwsbrief-onderwerp')?.textContent.toLowerCase() || '';
    row.style.display = (title.includes(query) || subject.includes(query)) ? '' : 'none';
  });
}
</script>

<div class="bg-white rounded-xl shadow">
  <!-- Grijze titelbalk -->
  <div class="grid grid-cols-3 px-6 py-3 rounded-t-xl" style="background:#f8f9fa;">
    <div class="font-semibold text-gray-600 text-sm">TITEL</div>
    <div class="font-semibold text-gray-600 text-sm">ONDERWERP</div>
    <div class="font-semibold text-gray-600 text-sm text-right">ACTIES</div>
  </div>
  <div class="divide-y">
    @forelse($items as $n)
      <div class="grid grid-cols-3 items-center p-3 nieuwsbrief-row">
        <div class="font-semibold nieuwsbrief-titel">{{ $n->title ?? 'Zonder titel' }}</div>
        <div class="text-sm text-gray-500 nieuwsbrief-onderwerp">{{ $n->subject }}</div>
        <div class="flex items-center gap-2 justify-end">
          <a href="{{ route('newsletters.edit', $n) }}" aria-label="Bewerk" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-800 hover:bg-orange-200 transition" title="Bewerk">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
          </a>
                    <!-- Bekijk-knop verwijderd: route 'newsletters.show' bestaat niet -->
          <form action="{{ route('newsletters.destroy', $n) }}" method="POST" onsubmit="return confirm('Nieuwsbrief verwijderen?');">
            @csrf
            @method('DELETE')
            <button type="submit" aria-label="Verwijderen" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-rose-100 text-rose-700 hover:bg-rose-200 transition" title="Verwijderen">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
            </button>
          </form>
        </div>
      </div>
    @empty
      <div class="p-3 text-gray-500">Nog geen nieuwsbrieven.</div>
    @endforelse
  </div>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
