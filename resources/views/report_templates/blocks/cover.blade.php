<div style="page-break-after:always;padding:20px;text-align:center;">
    @if(!empty($bikefit->images) && count($bikefit->images))
        <?php $img = $bikefit->images[0]; ?>
        <img src="{{ public_path('storage/' . $img->path) ? '/storage/' . $img->path : '' }}" style="width:100%;height:300px;object-fit:cover;" />
    @else
        <div style="height:300px;background:#eee;display:flex;align-items:center;justify-content:center;">Cover image</div>
    @endif
    <h1>{{ $bikefit->klant->naam ?? '' }} — Bikefit #{{ $bikefit->id }}</h1>
</div>
