<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TemplateController extends Controller
{
    // Overzicht van alle sjablonen
    public function index()
    {
        $templates = Template::all();
        return view('templates.index', compact('templates'));
    }

    public function editor($id)
    {
        $template = Template::findOrFail($id);
        // Genereer sleutellijst (dummy: alle {{key}} in content)
        preg_match_all('/{{\s*(\w+)\s*}}/', $template->content ?? '', $matches);
        $keys = $matches[1] ?? [];
        // Live preview: render raw HTML (later: met data)
        $previewHtml = $template->content;
        return view('templates.editor', compact('template', 'keys', 'previewHtml'));
    }
    // Formulier voor nieuw sjabloon
    public function create()
    {
        $mobiliteit = [
            [
                'test' => 'Straight Leg Raise (hamstrings)',
                'links' => [
                    'score' => 'Gemiddeld',
                    'color' => 'bg-yellow-400',
                    'desc' => 'De lengte van de hamstrings is meestal voldoende voor recreatief fietsen. Toch kan bij lange ritten of een sportieve positie spanning ontstaan in rug of bekken. Gerichte stretching of mobiliteitsoefeningen kunnen helpen dit risico te verkleinen.'
                ],
                'rechts' => [
                    'score' => 'Gemiddeld',
                    'color' => 'bg-yellow-400',
                    'desc' => 'De lengte van de hamstrings is meestal voldoende voor recreatief fietsen. Toch kan bij lange ritten of een sportieve positie spanning ontstaan in rug of bekken. Gerichte stretching of mobiliteitsoefeningen kunnen helpen dit risico te verkleinen.'
                ]
            ],
            [
                'test' => 'Knieflexie (rectus femoris)',
                'links' => [
                    'score' => 'Gemiddeld',
                    'color' => 'bg-yellow-400',
                    'desc' => 'De spierlengte is meestal voldoende voor de meeste vormen van fietsen. Bij intensieve belasting kan er echter spanning of lichte kniepijn optreden. Gerichte stretching of mobiliteitsoefeningen kunnen helpen om dit te verbeteren.'
                ],
                'rechts' => [
                    'score' => 'Gemiddeld',
                    'color' => 'bg-yellow-400',
                    'desc' => 'De spierlengte is meestal voldoende voor de meeste vormen van fietsen. Bij intensieve belasting kan er echter spanning of lichte kniepijn optreden. Gerichte stretching of mobiliteitsoefeningen kunnen helpen om dit te verbeteren.'
                ]
            ],
            [
                'test' => 'Heup endorotatie',
                'links' => [
                    'score' => 'Gemiddeld',
                    'color' => 'bg-yellow-400',
                    'desc' => 'De endorotatie is voldoende voor de meeste fietsers. Toch kan bij hogere intensiteit of lange ritten lichte asymmetrie of spanning optreden. Extra mobiliteit kan helpen om dit te verbeteren.'
                ],
                'rechts' => [
                    'score' => 'Gemiddeld',
                    'color' => 'bg-yellow-400',
                    'desc' => 'De endorotatie is voldoende voor de meeste fietsers. Toch kan bij hogere intensiteit of lange ritten lichte asymmetrie of spanning optreden. Extra mobiliteit kan helpen om dit te verbeteren.'
                ]
            ],
        ];
        $mobiliteit_tabel_html = view('components.mobiliteit-tabel', compact('mobiliteit'))->render();
        return view('templates.create', compact('mobiliteit_tabel_html'));
    }

    // Opslaan nieuw sjabloon
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'html_contents' => 'required|array',
            'html_contents.*' => 'required|string',
            'css_content' => 'nullable',
            'background_urls' => 'nullable|array',
            'background_urls.*' => 'nullable|string',
        ]);
        $data['html_contents'] = json_encode($data['html_contents']);
        // Sla background_urls[] op als background_images (JSON array van objects met path)
        if (!empty($data['background_urls'])) {
            $filtered = array_filter($data['background_urls'], function($url) {
                return !empty($url) && $url !== 'null';
            });
            if (count($filtered) > 0) {
                $data['background_images'] = json_encode(array_map(function($url) {
                    return [ 'path' => $url ];
                }, $filtered));
            } else {
                $data['background_images'] = null;
            }
        } else {
            $data['background_images'] = null;
        }
        $template = Template::create($data);
        return redirect()->route('templates.index');
    }

    // Formulier voor bewerken
    public function edit(Template $template)
    {
        try {
            return view('templates.edit', compact('template'));
        } catch (ModelNotFoundException $e) {
            return response()->view('templates.notfound', ['message' => 'Sjabloon niet gevonden.'], 404);
        }
    }

    // Opslaan bewerking
    public function update(Request $request, Template $template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'html_contents' => 'required|array',
            'html_contents.*' => 'required|string',
            'css_content' => 'nullable',
            'background_urls' => 'nullable|array',
            'background_urls.*' => 'nullable|string',
        ]);
        $data['html_contents'] = json_encode($data['html_contents']);
        // Sla background_urls[] op als background_images (JSON array van objects met path)
        if (!empty($data['background_urls'])) {
            $filtered = array_filter($data['background_urls'], function($url) {
                return !empty($url) && $url !== 'null';
            });
            if (count($filtered) > 0) {
                $data['background_images'] = json_encode(array_map(function($url) {
                    return [ 'path' => $url ];
                }, $filtered));
            } else {
                $data['background_images'] = null;
            }
        } else {
            $data['background_images'] = null;
        }
        $template->update($data);
        return redirect()->route('templates.index');
    }

    // Verwijderen
    public function destroy(Template $template)
    {
        try {
            $template->delete();
            return redirect()->route('templates.index');
        } catch (ModelNotFoundException $e) {
            return response()->view('templates.notfound', ['message' => 'Sjabloon niet gevonden.'], 404);
        }
    }

    // Detail/voorbeeld
    public function show(Template $template)
    {
        try {
            return view('templates.show', compact('template'));
        } catch (ModelNotFoundException $e) {
            return response()->view('templates.notfound', ['message' => 'Sjabloon niet gevonden.'], 404);
        }
    }

    // Dupliceren
    public function duplicate(Template $template)
    {
        $copy = $template->replicate();
        $copy->name = $template->name . ' (kopie)';
        $copy->save();
        return redirect()->route('templates.index');
    }

    // Preview met dummy data
    public function preview(Template $template)
    {
        $dummyData = [
            'name' => 'Voorbeeld Bikefit',
            'type' => $template->type,
            // ... voeg hier meer dummy data toe indien gewenst ...
        ];
        return view('templates.preview', compact('template', 'dummyData'));
    }
}
