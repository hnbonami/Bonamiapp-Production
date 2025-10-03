<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsItemController extends Controller
{
    // Toon alle nieuwsberichten (voor dashboard en beheer)
    public function index()
    {
        $newsItems = NewsItem::orderByDesc('created_at')->get();
        return view('news.index', compact('newsItems'));
    }

    // Toon formulier voor nieuw bericht (alleen admin/medewerker)
    public function create()
    {
        $this->authorize('create', NewsItem::class);
        return view('news.create');
    }

    // Sla nieuw bericht op
    public function store(Request $request)
    {
        $this->authorize('create', NewsItem::class);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $data['created_by'] = Auth::id();
        NewsItem::create($data);
        return redirect()->route('news.index')->with('success', 'Nieuwsbericht toegevoegd!');
    }

    // Toon één nieuwsbericht
    public function show(NewsItem $news)
    {
        return view('news.show', compact('news'));
    }

    // Toon formulier voor bewerken (alleen admin/medewerker)
    public function edit(NewsItem $news)
    {
        $this->authorize('update', $news);
        return view('news.edit', compact('news'));
    }

    // Werk nieuwsbericht bij
    public function update(Request $request, NewsItem $news)
    {
        $this->authorize('update', $news);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $news->update($data);
        return redirect()->route('news.index')->with('success', 'Nieuwsbericht bijgewerkt!');
    }

    // Verwijder nieuwsbericht
    public function destroy(NewsItem $news)
    {
        $this->authorize('delete', $news);
        $news->delete();
        return redirect()->route('news.index')->with('success', 'Nieuwsbericht verwijderd!');
    }
}
