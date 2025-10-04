@extends('layouts.app')

@section('content')
<h1 class="text-2xl font-bold mb-2">Nieuwsbrief bewerken</h1>

<form id="metaForm" class="bg-white rounded-xl shadow p-3 mb-4">
  @csrf
  <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
    <div>
      <label class="text-sm text-gray-600">Titel</label>
      <input type="text" id="title" class="w-full border rounded px-2 py-1" value="{{ $newsletter->title }}" />
    </div>
    <div class="md:col-span-2">
      <label class="text-sm text-gray-600">Onderwerp</label>
      <input type="text" id="subject" class="w-full border rounded px-2 py-1" value="{{ $newsletter->subject }}" />
    </div>
    <div>
      <label class="text-sm text-gray-600">Afzender naam</label>
      <input type="text" id="from_name" class="w-full border rounded px-2 py-1" value="{{ $newsletter->from_name ?? '' }}" placeholder="Bonami Sportcoaching" />
    </div>
    <div>
      <label class="text-sm text-gray-600">Afzender e-mail</label>
      <input type="email" id="from_email" class="w-full border rounded px-2 py-1" value="{{ $newsletter->from_email ?? '' }}" placeholder="info@bonami-sportcoaching.be" />
    </div>
  </div>
  <div class="mt-3 flex gap-2">
    <button type="button" id="btn-save" class="rounded-full px-4 py-1 bg-emerald-100 text-emerald-800 font-bold text-sm">Opslaan</button>
    <a href="{{ route('newsletters.index') }}" class="rounded-full px-3 py-1 bg-gray-100 text-gray-800 text-sm">Terug</a>
  </div>
</form>

<div class="grid grid-cols-12 gap-4">
  <div class="col-span-12 md:col-span-4">
    <div class="bg-white rounded-xl shadow p-3">
      <h2 class="font-semibold mb-2">Blokken</h2>
      <div class="space-y-2" id="block-palette">
        <div class="draggable block-item" draggable="true" data-type="title" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Titel</div>
        <div class="draggable block-item" draggable="true" data-type="text" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Tekst</div>
        <div class="draggable block-item" draggable="true" data-type="image" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Afbeelding</div>
        <div class="draggable block-item" draggable="true" data-type="button" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Knop</div>
        <div class="draggable block-item" draggable="true" data-type="spacer" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Witruimte</div>
  <div class="draggable block-item" draggable="true" data-type="divider" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:8px;cursor:grab;">Lijn (volledige breedte)</div>
      </div>
      <div class="mt-4">
        <label class="text-sm text-gray-600">Afbeelding uploaden</label>
        <input type="file" id="imageUpload" accept="image/*" class="w-full border rounded px-2 py-1" />
        <div class="text-xs text-gray-500 mt-1">Max 4MB. Toevoegen via blok "Afbeelding".</div>
      </div>
    </div>
  </div>

  <div class="col-span-12 md:col-span-8">
    <div class="bg-white rounded-xl shadow p-3 mb-4">
      <h2 class="font-semibold mb-2">Canvas</h2>
      <div id="canvas" style="min-height:480px;border:2px dashed #e5e7eb;border-radius:12px;padding:12px;" class="dropzone"></div>
    </div>

    <div class="bg-white rounded-xl shadow p-3">
      <h2 class="font-semibold mb-2">Ontvangers</h2>
      <div class="space-y-2">
        <div class="flex items-center gap-2">
          <input type="radio" name="scope" value="all_klanten" id="scope_klanten" />
          <label for="scope_klanten">Alle klanten ({{ $klantenCount }})</label>
        </div>
        <div class="flex items-center gap-2">
          <input type="radio" name="scope" value="all_medewerkers" id="scope_medewerkers" />
          <label for="scope_medewerkers">Alle medewerkers ({{ $medewerkersCount }})</label>
        </div>
        <div class="flex items-center gap-2">
          <input type="radio" name="scope" value="custom" id="scope_custom" />
          <label for="scope_custom">Aangepaste lijst</label>
        </div>
        <div id="customList" class="hidden">
          <textarea id="customRecipients" class="w-full border rounded px-2 py-1 text-sm" rows="4" placeholder="Eén per lijn: naam <email@example.com>"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
          <input type="text" id="segment" class="border rounded px-2 py-1" placeholder="Segment (optioneel)" />
          <button type="button" id="btn-apply-recipients" class="rounded-full px-3 py-1 bg-gray-100 text-gray-800 text-sm">Toepassen</button>
          <button type="button" id="btn-send" class="rounded-full px-3 py-1 bg-rose-100 text-rose-700 text-sm">Verzenden (queue)</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
          <input type="email" id="test_email" class="border rounded px-2 py-1" placeholder="Test e-mail" />
          <input type="text" id="test_name" class="border rounded px-2 py-1" placeholder="Naam (optioneel)" />
          <button type="button" id="btn-test" class="rounded-full px-3 py-1 bg-emerald-100 text-emerald-800 text-sm">Testmail queueën</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="bg-white rounded-xl shadow p-3 mt-4">
  <h2 class="font-semibold mb-2">Voorbeeld</h2>
  <div class="flex items-center gap-2 mb-2">
    <a href="{{ route('newsletters.export', $newsletter) }}" class="rounded-full px-3 py-1 bg-gray-100 text-gray-800 text-sm">Download HTML</a>
  </div>
  <iframe id="previewFrame" class="w-full" style="min-height:380px;border:1px solid #e5e7eb;border-radius:12px"></iframe>
</div>

<script>
const NEWSLETTER_ID = {{ $newsletter->id }};
const palette = document.getElementById('block-palette');
const canvas = document.getElementById('canvas');
let dragType = null;

function defaultContent(type){
  switch(type){
    case 'title': return '<h2 style="margin:0">Nieuwe titel</h2>';
    case 'text': return '<p>Je tekst hier…</p>';
    case 'image': return '<p><em>Klik om afbeelding URL te plakken</em></p>';
  case 'button': return '<a href="#" style="display:inline-block;background:#c1dfeb;color:#111;padding:8px 12px;border-radius:8px;text-decoration:none;">Call to action</a>';
    case 'spacer': return '<div style="height:24px"></div>';
  case 'divider': return '<div style="height:1px;background:#e5e7eb;width:100%"></div>';
    default: return '<p>Blok</p>';
  }
}

function addBlock(type, content=null, settings={}){
  const wrapper = document.createElement('div');
  wrapper.className = 'newsletter-block';
  wrapper.dataset.type = type;
  wrapper.dataset.settings = JSON.stringify(settings || {});
  wrapper.style.border = '1px solid #e5e7eb';
  wrapper.style.borderRadius = '10px';
  wrapper.style.padding = '10px';
  wrapper.style.margin = '8px 4px';
  wrapper.style.background = '#fafafa';

  const header = document.createElement('div');
  header.style.display = 'flex';
  header.style.justifyContent = 'space-between';
  header.style.alignItems = 'center';
  header.style.marginBottom = '6px';
  header.innerHTML = `<span style="font-weight:600;color:#374151">${type.toUpperCase()}</span>
    <span class="inline-flex items-center gap-1">
+      <button onclick="editSettings(this)" title="Instellingen" class="rounded-full px-2 py-0.5 bg-gray-100 text-xs">⚙️</button>
       <button onclick="moveUp(this)" class="rounded-full px-2 py-0.5 bg-gray-100 text-xs">▲</button>
       <button onclick="moveDown(this)" class="rounded-full px-2 py-0.5 bg-gray-100 text-xs">▼</button>
       <button onclick="this.closest('.newsletter-block').remove(); updatePreview();" class="rounded-full px-2 py-0.5 bg-rose-100 text-rose-700 text-xs">Verwijder</button>
    </span>`;

  const contentDiv = document.createElement('div');
  contentDiv.contentEditable = true;
  contentDiv.style.outline = 'none';
  contentDiv.style.minHeight = '28px';
  contentDiv.style.padding = '6px 8px';
  contentDiv.style.background = '#fff';
  contentDiv.innerHTML = content || defaultContent(type);
  contentDiv.addEventListener('blur', updatePreview);

  wrapper.appendChild(header);
  wrapper.appendChild(contentDiv);
  canvas.appendChild(wrapper);
}

palette.querySelectorAll('.draggable').forEach(el => {
  el.addEventListener('dragstart', e => { dragType = e.target.dataset.type; });
});
canvas.addEventListener('dragover', e => { e.preventDefault(); });
canvas.addEventListener('drop', e => { e.preventDefault(); if (!dragType) return; addBlock(dragType); dragType = null; updatePreview(); });

function moveUp(btn){
  const block = btn.closest('.newsletter-block');
  if (block.previousElementSibling) { canvas.insertBefore(block, block.previousElementSibling); updatePreview(); }
}
function moveDown(btn){
  const block = btn.closest('.newsletter-block');
  if (block.nextElementSibling) { canvas.insertBefore(block.nextElementSibling, block); updatePreview(); }
}

function collectBlocks(){
  return Array.from(canvas.querySelectorAll('.newsletter-block')).map((el, i) => ({
    type: el.dataset.type,
    position: i,
    content: el.querySelector('div[contenteditable]')?.innerHTML || '',
    settings: JSON.parse(el.dataset.settings || '{}')
  }));
}

async function saveAll(){
  const payload = {
    title: document.getElementById('title').value || null,
    subject: document.getElementById('subject').value || '',
    from_name: document.getElementById('from_name').value || null,
    from_email: document.getElementById('from_email').value || null,
    blocks: collectBlocks()
  };
  const res = await fetch("{{ route('newsletters.save', $newsletter) }}", {
    method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify(payload)
  });
  if(!res.ok){ alert('Opslaan mislukt'); return; }
}

async function updatePreview(){
  await saveAll();
  document.getElementById('previewFrame').src = "{{ route('newsletters.preview', $newsletter) }}?t="+Date.now();
}

document.getElementById('btn-save').addEventListener('click', async()=>{ await saveAll(); alert('Opgeslagen'); });

// Upload logic
document.getElementById('imageUpload').addEventListener('change', async (e) => {
  const file = e.target.files[0]; if(!file) return;
  const fd = new FormData(); fd.append('image', file);
  const res = await fetch("{{ route('newsletters.upload') }}", { method:'POST', headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: fd});
  const data = await res.json();
  if (data.url) {
    // Add an image block with url
    addBlock('image', `<img src="${data.url}" alt="" style="max-width:100%"/>`, {image_url: data.url});
    updatePreview();
  }
  e.target.value = '';
});

// Recipients
document.querySelectorAll('input[name="scope"]').forEach(r => {
  r.addEventListener('change', () => {
    document.getElementById('customList').classList.toggle('hidden', r.value !== 'custom' || !r.checked);
  });
});

document.getElementById('btn-apply-recipients').addEventListener('click', async () => {
  const scope = document.querySelector('input[name="scope"]:checked')?.value || 'all_klanten';
  const segment = document.getElementById('segment').value || null;
  const payload = { scope, segment };
  if (scope === 'custom') {
    const raw = document.getElementById('customRecipients').value.split('\n').map(x => x.trim()).filter(Boolean);
    payload.custom = raw.map(line => {
      const m = line.match(/^(.*)<([^>]+)>$/);
      if (m) { return { name: m[1].trim(), email: m[2].trim(), type: 'klant' }; }
      return { name: null, email: line, type: 'klant' };
    });
  }
  const res = await fetch("{{ route('newsletters.recipients', $newsletter) }}", { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify(payload) });
  if (res.ok) alert('Ontvangers ingesteld'); else alert('Mislukt');
});

document.getElementById('btn-test').addEventListener('click', async () => {
  await saveAll();
  const email = document.getElementById('test_email').value; const name = document.getElementById('test_name').value || null;
  if (!email) { alert('Vul test e-mail in'); return; }
  const res = await fetch("{{ route('newsletters.test', $newsletter) }}", { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: JSON.stringify({ email, name }) });
  if (res.ok) alert('Testmail gequeueëd'); else alert('Mislukt');
});

document.getElementById('btn-send').addEventListener('click', async () => {
  if (!confirm('Ben je zeker? Dit queueët verzending naar alle ingestelde ontvangers.')) return;
  await saveAll();
  const res = await fetch("{{ route('newsletters.send', $newsletter) }}", { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}'} });
  if (res.ok) alert('Verzending gequeueëd'); else alert('Mislukt');
});

// Load existing blocks
@php $blocks = $newsletter->blocks; @endphp
@foreach($blocks as $b)
addBlock(@json($b->type), @json($b->content), @json($b->settings ?? []));
@endforeach
updatePreview();

function editSettings(btn){
  const block = btn.closest('.newsletter-block');
  const type = block.dataset.type;
  const settings = JSON.parse(block.dataset.settings || '{}');
  if (type === 'image') {
    const useUpload = confirm('Wil je een foto uploaden vanaf je toestel?\nKlik op OK om te uploaden, of Annuleren om een URL te plakken.');
    if (useUpload) {
      const picker = document.createElement('input');
      picker.type = 'file';
      picker.accept = 'image/*';
      picker.style.display = 'none';
      document.body.appendChild(picker);
      picker.addEventListener('change', async () => {
        const file = picker.files && picker.files[0];
        document.body.removeChild(picker);
        if (!file) return;
        try {
          const fd = new FormData();
          fd.append('image', file);
          const res = await fetch("{{ route('newsletters.upload') }}", { method:'POST', headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}, body: fd});
          if (!res.ok) throw new Error('Upload mislukt');
          const data = await res.json();
          if (!data.url) throw new Error('Geen URL ontvangen');
          settings.image_url = data.url;
          block.dataset.settings = JSON.stringify(settings);
          const editable = block.querySelector('[contenteditable]');
          editable.innerHTML = `<img src="${data.url}" alt="" style="max-width:100%"/>`;
          updatePreview();
        } catch (e) {
          alert('Upload mislukt. Probeer opnieuw of plak een URL.');
        }
      }, { once: true });
      picker.click();
    } else {
      const url = prompt('Afbeelding URL', settings.image_url || '');
      if (url !== null) {
        settings.image_url = url.trim();
        block.dataset.settings = JSON.stringify(settings);
        const editable = block.querySelector('[contenteditable]');
        editable.innerHTML = url ? `<img src="${url}" alt="" style="max-width:100%"/>` : '<p><em>Klik om afbeelding URL te plakken</em></p>';
        updatePreview();
      }
    }
  } else if (type === 'button') {
    const href = prompt('Knop URL', settings.button_url || 'https://');
    if (href !== null) {
      settings.button_url = href.trim();
      block.dataset.settings = JSON.stringify(settings);
      updatePreview();
    }
  } else if (type === 'spacer') {
    const h = prompt('Hoogte in px', (settings.height ?? 24));
    if (h !== null) {
      const height = Math.max(0, parseInt(h, 10) || 0);
      settings.height = height;
      block.dataset.settings = JSON.stringify(settings);
      const editable = block.querySelector('[contenteditable]');
      editable.innerHTML = `<div style=\"height:${height}px\"></div>`;
      updatePreview();
    }
  } else if (type === 'divider') {
    const color = prompt('Kleur (hex of naam)', settings.color || '#e5e7eb');
    if (color === null) return;
    const h = prompt('Dikte in px', (settings.height ?? 1));
    if (h !== null) {
      const height = Math.max(1, parseInt(h, 10) || 1);
      settings.color = color.trim();
      settings.height = height;
      block.dataset.settings = JSON.stringify(settings);
      const editable = block.querySelector('[contenteditable]');
      editable.innerHTML = `<div style=\"height:${height}px;background:${settings.color};width:100%\"></div>`;
      updatePreview();
    }
  } else {
    alert('Geen extra instellingen voor dit blok.');
  }
}
</script>
@endsection
