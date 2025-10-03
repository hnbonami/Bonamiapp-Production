<?php

namespace App\Http\Controllers;

use App\Models\InstagramPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstagramPostController extends Controller
{
    public function index()
    {
        $items = InstagramPost::orderByDesc('created_at')->paginate(24);
        return view('instagram.index', compact('items'));
    }

    public function create()
    {
        $recent = InstagramPost::orderByDesc('created_at')->limit(12)->get();
        return view('instagram.create', compact('recent'));
    }

    public function edit(InstagramPost $post)
    {
        $recent = InstagramPost::orderByDesc('created_at')->limit(12)->get();
        return view('instagram.create', [ 'recent' => $recent, 'post' => $post ]);
    }

    public function upload(Request $request)
    {
        $request->validate(['image' => 'required|image|max:8192']);
        try {
            $path = $request->file('image')->store('instagram', 'public');
            $url = '/storage/' . ltrim($path, '/');
            return response()->json(['url' => $url, 'path' => $path]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Upload mislukt', 'message' => $e->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'titel' => 'nullable|string',
            'caption' => 'nullable|string',
            'afbeelding' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'status' => 'nullable|string|in:concept,gepubliceerd',
        ]);

        $post = InstagramPost::create([
            'titel' => $data['titel'] ?? 'Nieuwe Post',
            'caption' => $data['caption'] ?? null,
            'afbeelding' => $data['afbeelding'] ?? null,
            'hashtags' => $data['hashtags'] ?? null,
            'status' => $data['status'] ?? 'concept',
        ]);

        return response()->json(['ok' => true, 'id' => $post->id]);
    }

    public function update(Request $request, InstagramPost $post)
    {
        $data = $request->validate([
            'titel' => 'nullable|string',
            'caption' => 'nullable|string',
            'afbeelding' => 'nullable|string',
            'hashtags' => 'nullable|array',
            'status' => 'nullable|string|in:concept,gepubliceerd',
        ]);

        $post->update([
            'titel' => $data['titel'] ?? $post->titel,
            'caption' => $data['caption'] ?? $post->caption,
            'afbeelding' => $data['afbeelding'] ?? $post->afbeelding,
            'hashtags' => $data['hashtags'] ?? $post->hashtags,
            'status' => $data['status'] ?? $post->status,
        ]);

        return response()->json(['ok' => true]);
    }

    public function destroy(InstagramPost $post)
    {
        // Optionally delete preview file
        if ($post->preview_path && Storage::disk('public')->exists($post->preview_path)) {
            try { Storage::disk('public')->delete($post->preview_path); } catch (\Throwable $e) {}
        }
        $post->delete();
        return redirect()->route('instagram.index')->with('status', 'Post verwijderd');
    }

    // Template slot features removed
}
