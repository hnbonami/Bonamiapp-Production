@extends('layouts.app')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Instagram posts</h1>
  </div>
  <div class="mb-4">
    <a href="{{ route('instagram.create') }}" class="rounded-full px-4 py-1 bg-emerald-100 text-emerald-800 font-bold text-sm">+instagram post maken</a>
  </div>

<div class="grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
@forelse($items as $p)
  <div class="relative group bg-white rounded-md shadow-sm overflow-hidden" style="pointer-events:auto;max-width:320px;">
    <div class="relative w-full" style="padding-top:100%;">
    @if($p->preview_path)
      <img src="{{ asset('storage/'.$p->preview_path) }}" alt="preview" class="absolute inset-0 w-full h-full object-cover" />
    @else
      <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-xs">Geen preview</div>
    @endif
    </div>
    <div class="px-1 py-[2px] flex items-center justify-between gap-1">
      <div class="text-[9px] truncate max-w-[60%]">{{ $p->title ?? 'Zonder titel' }}</div>
      <div class="flex items-center gap-0.5">
        <a href="{{ route('instagram.edit', $p) }}" aria-label="Bewerken" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-white text-black ring ring-black/10 shadow">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M4 20h4l10.243-10.243a2.5 2.5 0 10-3.536-3.536L4 16v4z"/></svg>
        </a>
        <form action="{{ route('instagram.destroy', $p) }}" method="POST" onsubmit="return confirm('Verwijderen?');" class="inline">
          @csrf
          @method('DELETE')
          <button type="submit" aria-label="Verwijderen" class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-100 text-rose-700 ring ring-black/10 shadow">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-2.5 h-2.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
          </button>
        </form>
      </div>
    </div>
  </div>
@empty
  <div class="text-gray-500">Nog geen posts opgeslagen.</div>
@endforelse
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection
