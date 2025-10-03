<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportTemplate;
use Illuminate\Support\Str;

class ReportTemplateController extends Controller
    /**
     * Upload achtergrond PDF voor sjablonen.
     */
    public function uploadBackground(Request $request)
    {
        $request->validate([
            'background_pdf' => 'required|file|mimes:pdf|max:20000',
        ]);
        $file = $request->file('background_pdf');
        $targetPath = public_path('backgrounds/background.pdf');
        // Zorg dat de map bestaat
        if (!file_exists(public_path('backgrounds'))){
            mkdir(public_path('backgrounds'), 0775, true);
        }
        $file->move(public_path('backgrounds'), 'background.pdf');
        return redirect()->back()->with('success', 'Achtergrond PDF geüpload!');
    }
{
    public function index()
    {
        $templates = ReportTemplate::orderByDesc('is_active')->orderBy('name')->get();
        return view('report_templates.index', compact('templates'));
    }

    public function edit($id)
    {
        $template = ReportTemplate::findOrFail($id);
        return view('report_templates.edit', compact('template'));
    }

    public function create()
    {
        $template = new ReportTemplate();
        $template->json_layout = json_encode([['type' => 'cover'], ['type' => 'measurements'], ['type' => 'photo_gallery']]);
        return view('report_templates.edit', compact('template'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'json_layout' => 'nullable|string',
        ]);
        $data['slug'] = Str::slug($data['name'] ?? 'template');
        $data['created_by'] = auth()->id();
        $template = ReportTemplate::create($data);
        return redirect()->route('report_templates.edit', $template->id)->with('success', 'Template aangemaakt');
    }

    public function update(Request $request, $id)
    {
        $template = ReportTemplate::findOrFail($id);
        $data = $request->validate([
            'name' => 'nullable|string',
            'json_layout' => 'nullable|string',
        ]);
        $template->update($data);
        return redirect()->route('report_templates.edit', $template->id)->with('success', 'Template opgeslagen');
    }

    public function activate($id)
    {
        $t = ReportTemplate::findOrFail($id);
        // If this template has a kind, only deactivate templates of the same kind.
        if (!empty($t->kind)) {
            ReportTemplate::where('kind', $t->kind)->update(['is_active' => false]);
        } else {
            // fallback: deactivate all templates (legacy behavior)
            ReportTemplate::query()->update(['is_active' => false]);
        }
        $t->is_active = true;
        $t->save();
        return redirect()->route('report_templates.index')->with('success', 'Template geactiveerd');
    }

    /**
     * Toggle active state (AJAX-friendly).
     */
    public function toggle(Request $request, $id)
    {
        $t = ReportTemplate::findOrFail($id);
        if ($t->is_active) {
            $t->is_active = false;
            $t->save();
        } else {
            // deactivate others of same kind
            if (!empty($t->kind)) {
                ReportTemplate::where('kind', $t->kind)->update(['is_active' => false]);
            } else {
                ReportTemplate::query()->update(['is_active' => false]);
            }
            $t->is_active = true;
            $t->save();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'is_active' => (bool)$t->is_active]);
        }

        return redirect()->route('report_templates.index')->with('success', 'Status bijgewerkt');
    }

    /**
     * Delete a template.
     */
    public function destroy($id)
    {
        $t = ReportTemplate::findOrFail($id);
        $t->delete();
        return redirect()->route('report_templates.index')->with('success', 'Template verwijderd');
    }

    public function preview(Request $request)
    {
        $payload = $request->validate([
            'layout' => 'nullable|string',
        ]);
        $layout = $payload['layout'] ?? null;
        // create a sample bikefit object (minimal)
        $bikefit = new \App\Models\Bikefit();
        $bikefit->id = 0;
        $bikefit->lengte_cm = 175;
        $bikefit->binnenbeenlengte_cm = 82;
        $bikefit->armlengte_cm = 64;
        $bikefit->klant = (object)['naam' => 'Voorbeeld Klant'];

        $renderer = new \App\Services\ReportTemplateRenderer();
        // if no layout provided, try to use the active template
        if (empty($layout)) {
            $active = \App\Models\ReportTemplate::where('is_active', true)->first();
            if ($active) {
                $html = $renderer->renderTemplateForBikefit($active, $bikefit);
            } else {
                $html = '<div style="padding:16px">Geen actieve template gevonden.</div>';
            }
        } else {
            $html = $renderer->renderTemplateForBikefit($layout, $bikefit);
        }
        return response($html, 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Upload achtergrond PDF voor sjablonen.
     */
    public function uploadBackground(Request $request)
    {
        $request->validate([
            'background_pdf' => 'required|file|mimes:pdf|max:20000',
        ]);
        $file = $request->file('background_pdf');
        $targetPath = public_path('backgrounds/background.pdf');
        // Zorg dat de map bestaat
        if (!file_exists(public_path('backgrounds'))){
            mkdir(public_path('backgrounds'), 0775, true);
        }
        $file->move(public_path('backgrounds'), 'background.pdf');
        return redirect()->back()->with('success', 'Achtergrond PDF geüpload!');
    }
}
