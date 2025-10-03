@extends('layouts.app')
@section('content')

<div class="max-w-5xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8">
    <!-- Notities & Taken lijst -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em 1.4em 1.2em 1.4em;display:flex;flex-direction:column;min-width:260px;">
        <h1 class="text-2xl font-bold mb-4">Interne notities &amp; taken</h1>
        <div id="staff-notes-list" class="flex flex-col gap-4">
            @if(isset($notes) && count($notes))
                @foreach($notes as $note)
                    <div class="p-4 border-l-4 border-blue-300 bg-blue-50 rounded">
                        <div class="text-xs text-gray-500 mb-1">Aangemaakt op {{ $note->created_at->format('d-m-Y H:i') }} door <span class="font-semibold">{{ $note->user->name ?? 'Onbekend' }}</span></div>
                        <div class="prose max-w-none text-sm">{!! $note->content !!}</div>
                    </div>
                @endforeach
            @else
                <div class="text-gray-500">Geen interne notities of taken gevonden.</div>
            @endif
        </div>
    </div>
    <!-- Nieuwe notitie toevoegen -->
    <div style="background:#fff;border-radius:18px;box-shadow:0 2px 8px #e5e7eb;padding:1.2em 1.4em 1.2em 1.4em;display:flex;flex-direction:column;min-width:260px;">
        <h2 class="text-lg font-semibold mb-2">Nieuwe notitie of taak toevoegen</h2>
        <form method="POST" action="{{ route('staffnotes.update') }}" class="flex flex-col gap-2">
            @csrf
            <textarea id="staff-note-content" name="content" class="w-full border rounded p-2 mb-2" rows="5" placeholder="Voeg een interne notitie of taak toe..."></textarea>
            <button type="submit" class="text-black font-bold py-2 px-4 rounded self-end" style="background:#c8e1eb;">Opslaan</button>
        </form>
        <script src="/ckeditor/ckeditor/ckeditor.js"></script>
        <script>
        if (window.CKEDITOR) {
            CKEDITOR.replace('staff-note-content', {
                contentsCss: '/ckeditor/ckeditor/contents.css',
                height: 180
            });
            document.querySelector('form').addEventListener('submit', function() {
                for (var instanceName in CKEDITOR.instances) {
                    if (CKEDITOR.instances.hasOwnProperty(instanceName)) {
                        CKEDITOR.instances[instanceName].updateElement();
                    }
                }
            });
        }
        </script>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch("{{ route('staffnotes.markAllRead') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    });
});
</script>
@endpush
