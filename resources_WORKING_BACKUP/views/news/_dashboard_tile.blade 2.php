@if($newsItems->count())
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Bonami Nieuwsberichten</h2>
        <ul class="divide-y divide-gray-200">
            @foreach($newsItems as $news)
                <li class="py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-lg">{{ $news->title }}</div>
                            <div class="text-gray-600 text-sm mb-1">Geplaatst op {{ $news->created_at->format('d-m-Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="prose max-w-none mt-2">{!! $news->content !!}</div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
