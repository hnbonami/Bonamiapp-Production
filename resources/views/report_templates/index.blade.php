@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Report Templates</h1>
    <a href="{{ route('report_templates.create') }}" class="inline-block mb-4 px-3 py-2 bg-indigo-600 text-white rounded">Nieuwe template</a>
    <table class="w-full table-auto">
        <thead>
            <tr>
                <th class="text-left p-2">Naam</th>
                <th class="text-left p-2">Kind</th>
                <th class="text-left p-2">Status</th>
                <th class="p-2"></th>
            </tr>
        </thead>
        <tbody>
        @foreach($templates as $t)
            <tr class="border-t">
                <td class="p-2">{{ $t->name ?? '(zonder naam)' }}</td>
                <td class="p-2">{{ $t->kind ?? '-' }}</td>
                <td class="p-2">
                    <div class="flex items-center gap-2">
                        <span class="sr-only">Status</span>
                        <label class="flex items-center gap-2">
                            <input data-id="{{ $t->id }}" data-kind="{{ $t->kind }}" type="checkbox" class="template-toggle" {{ $t->is_active ? 'checked' : '' }} />
                            <span class="text-sm">{{ $t->is_active ? 'Actief' : 'Inactief' }}</span>
                        </label>
                    </div>
                </td>
                <td class="p-2 text-right">
                    <a href="{{ route('report_templates.edit', $t->id) }}" class="px-2 py-1 bg-gray-100 rounded mr-2">Bewerken</a>
                    <button data-id="{{ $t->id }}" class="delete-template inline-flex items-center justify-center w-7 h-7 rounded-full bg-rose-100 text-rose-700 shadow" title="Verwijder template" style="margin-right:2px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 001 1h8a1 1 0 001-1m-10 0V6a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.template-toggle').forEach(function(cb){
        cb.addEventListener('change', function(){
            const id = this.dataset.id;
            const checked = this.checked;
            fetch("{{ url('report-templates') }}/"+id+"/toggle", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            }).then(r=>r.json()).then(json=>{
                    if(!json.success){
                        alert('Kon status niet bijwerken');
                        cb.checked = !checked;
                    } else {
                        // update label text in same row
                        const row = cb.closest('tr');
                        const statusText = row.querySelector('.status-text');
                        if(statusText) statusText.textContent = json.is_active ? 'Actief' : 'Inactief';

                        // if activated, uncheck other checkboxes of same kind in the DOM
                        if(json.is_active && cb.dataset.kind){
                            document.querySelectorAll('.template-toggle').forEach(function(other){
                                if(other === cb) return;
                                if(other.dataset.kind === cb.dataset.kind){
                                    other.checked = false;
                                    const r = other.closest('tr');
                                    const st = r ? r.querySelector('.status-text') : null;
                                    if(st) st.textContent = 'Inactief';
                                }
                            });
                        }
                    }
            }).catch(e=>{ alert('Netwerkfout'); cb.checked = !checked; });
        });
    });

    document.querySelectorAll('.delete-template').forEach(function(btn){
        btn.addEventListener('click', function(){
            if(!confirm('Weet je zeker dat je dit sjabloon wilt verwijderen? Dit kan niet ongedaan worden gemaakt.')) return;
            const id = this.dataset.id;
            fetch("{{ url('report-templates') }}/"+id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'text/html'
                }
            }).then(r=>{
                if(r.ok){
                    // remove row
                    this.closest('tr').remove();
                } else {
                    alert('Kon sjabloon niet verwijderen');
                }
            }).catch(e=>{ alert('Netwerkfout'); });
        });
    });
});
</script>
@endpush
