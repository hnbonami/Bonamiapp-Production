<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Template;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = Template::orderBy('created_at', 'desc')->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        return view('templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'content' => 'required|string',
        ]);

        Template::create($request->all());

        return redirect()->route('temp.index')->with('success', 'Sjabloon succesvol aangemaakt.');
    }

    public function show(Template $template)
    {
        return view('templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        return view('templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'content' => 'required|string',
        ]);

        $template->update($request->all());

        return redirect()->route('temp.show', $template)->with('success', 'Sjabloon succesvol bijgewerkt.');
    }

    public function destroy(Template $template)
    {
        $template->delete();
        return redirect()->route('temp.index')->with('success', 'Sjabloon succesvol verwijderd.');
    }

    public function preview(Template $template)
    {
        return view('templates.preview', compact('template'));
    }

    public function editor(Template $template)
    {
        return view('templates.editor', compact('template'));
    }

    public function saveContent(Request $request, Template $template)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $template->update(['content' => $request->content]);

        return response()->json(['success' => true]);
    }
}
