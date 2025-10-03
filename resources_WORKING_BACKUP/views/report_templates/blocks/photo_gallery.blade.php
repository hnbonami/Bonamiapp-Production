<div style="padding:12px;">
    <h2>Foto's</h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @foreach($bikefit->images as $i)
            <div style="width:30%;">
                <img src="/storage/{{ $i->path }}" style="width:100%;height:120px;object-fit:cover;" />
                <div style="font-size:11px">{{ $i->caption }}</div>
            </div>
        @endforeach
    </div>
</div>
