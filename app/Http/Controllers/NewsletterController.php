<?php

namespace App\Http\Controllers;

use App\Models\Klant;
use App\Models\Medewerker;
use App\Models\Newsletter;
use App\Models\NewsletterBlock;
use App\Models\NewsletterRecipient;
use App\Jobs\SendNewsletterJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewsletterController extends Controller
{
    public function index()
    {
        $items = Newsletter::orderByDesc('created_at')->paginate(20);
        return view('newsletters.index', compact('items'));
    }

    public function destroy(Newsletter $newsletter)
    {
        $newsletter->delete();
        return redirect()->route('newsletters.index')->with('success', 'Nieuwsbrief verwijderd.');
    }

    public function create()
    {
        $draft = Newsletter::create([
            'title' => 'Nieuwe nieuwsbrief',
            'subject' => 'Onderwerp',
            'created_by' => auth()->id(),
        ]);
        return redirect()->route('newsletters.edit', $draft);
    }

    public function edit(Newsletter $newsletter)
    {
        $newsletter->load('blocks');
        // Provide basic segments: all klanten, all medewerkers
        $klantenCount = Klant::count();
        $medewerkersCount = Medewerker::count();
        return view('newsletters.edit', compact('newsletter','klantenCount','medewerkersCount'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096',
        ]);
        $path = $request->file('image')->store('newsletter-images', 'public');
        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    public function save(Request $request, Newsletter $newsletter)
    {
        $data = $request->validate([
            'title' => 'nullable|string',
            'subject' => 'required|string',
            'from_name' => 'nullable|string',
            'from_email' => 'nullable|email',
            'blocks' => 'required|array',
            'blocks.*.type' => 'required|string',
            'blocks.*.content' => 'nullable|string',
            'blocks.*.settings' => 'array',
        ]);

        DB::transaction(function () use ($newsletter, $data) {
            $newsletter->update(collect($data)->except('blocks')->toArray());
            $newsletter->blocks()->delete();
            foreach ($data['blocks'] as $i => $b) {
                NewsletterBlock::create([
                    'newsletter_id' => $newsletter->id,
                    'type' => $b['type'],
                    'position' => $i,
                    'content' => $b['content'] ?? null,
                    'settings' => $b['settings'] ?? [],
                ]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function recipients(Request $request, Newsletter $newsletter)
    {
        $data = $request->validate([
            'scope' => 'required|string', // all_klanten, all_medewerkers, custom
            'custom' => 'array', // [{type, id, email, name}]
            'segment' => 'nullable|string',
        ]);

        DB::transaction(function () use ($newsletter, $data) {
            $newsletter->recipients()->delete();

            $rows = [];
            if ($data['scope'] === 'all_klanten') {
                Klant::query()->whereNotNull('email')->chunk(500, function ($chunk) use (&$rows, $newsletter, $data) {
                    foreach ($chunk as $k) {
                        $rows[] = [
                            'newsletter_id' => $newsletter->id,
                            'type' => 'klant',
                            'recipient_id' => $k->id,
                            'email' => $k->email,
                            'name' => trim(($k->voornaam ?? '') . ' ' . ($k->achternaam ?? '')) ?: $k->naam ?? null,
                            'segment' => $data['segment'] ?? null,
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                });
            } elseif ($data['scope'] === 'all_medewerkers') {
                Medewerker::query()->whereNotNull('email')->chunk(500, function ($chunk) use (&$rows, $newsletter, $data) {
                    foreach ($chunk as $m) {
                        $rows[] = [
                            'newsletter_id' => $newsletter->id,
                            'type' => 'medewerker',
                            'recipient_id' => $m->id,
                            'email' => $m->email,
                            'name' => trim(($m->voornaam ?? '') . ' ' . ($m->achternaam ?? '')) ?: $m->naam ?? null,
                            'segment' => $data['segment'] ?? null,
                            'status' => 'pending',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                });
            } else {
                foreach ($data['custom'] ?? [] as $c) {
                    $rows[] = [
                        'newsletter_id' => $newsletter->id,
                        'type' => $c['type'] ?? 'klant',
                        'recipient_id' => $c['id'] ?? null,
                        'email' => $c['email'],
                        'name' => $c['name'] ?? null,
                        'segment' => $data['segment'] ?? null,
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($rows)) {
                foreach (array_chunk($rows, 1000) as $chunk) {
                    NewsletterRecipient::insert($chunk);
                }
            }
        });

        return response()->json(['ok' => true]);
    }

    public function preview(Newsletter $newsletter)
    {
        $newsletter->load('blocks');
        $html = $this->renderEmailHtml($newsletter);
        return response()->make($html);
    }

    public function sendTest(Request $request, Newsletter $newsletter)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string'
        ]);

        dispatch(new SendNewsletterJob($newsletter->id, $data['email'], $data['name'] ?? null, true));
        return response()->json(['queued' => true]);
    }

    public function sendAll(Newsletter $newsletter)
    {
        $newsletter->update(['status' => 'sending']);
        foreach ($newsletter->recipients()->where('status', 'pending')->cursor() as $r) {
            dispatch(new SendNewsletterJob($newsletter->id, $r->email, $r->name, false, $r->id));
        }
        return response()->json(['queued' => true]);
    }

    public function export(Newsletter $newsletter)
    {
        $newsletter->load('blocks');
        $html = $this->renderEmailHtml($newsletter);
        $filename = 'newsletter-'.$newsletter->id.'.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    private function renderEmailHtml(Newsletter $newsletter): string
    {
        // Basic responsive table layout with inline styles (email-friendly)
        $blocks = $newsletter->blocks;
        $content = '';
        foreach ($blocks as $b) {
            switch ($b->type) {
                case 'title':
                    $content .= '<tr><td style="padding:16px 20px;font-family:Arial,Helvetica,sans-serif;font-size:20px;font-weight:bold;color:#111">'
                        . $b->content . '</td></tr>';
                    break;
                case 'text':
                    $content .= '<tr><td style="padding:10px 20px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.6;color:#111">'
                        . $b->content . '</td></tr>';
                    break;
                case 'image':
                    $url = data_get($b->settings, 'image_url');
                    $content .= '<tr><td style="padding:8px 0;text-align:center"><img src="'.e($url).'" alt="" style="max-width:100%;border:0;display:block;margin:0 auto"/></td></tr>';
                    break;
                case 'button':
                    $label = 'Bekijk';
                    $label = strip_tags($b->content ?: $label);
                    $href = data_get($b->settings, 'button_url', '#');
                    $bg = data_get($b->settings, 'button_bg', '#c1dfeb');
                    $color = data_get($b->settings, 'button_color', '#111');
                    $content .= '<tr><td style="padding:16px 20px;text-align:center"><a href="'.e($href).'" style="background:'.e($bg).';color:'.e($color).';text-decoration:none;padding:10px 16px;border-radius:8px;display:inline-block">'.e($label).'</a></td></tr>';
                    break;
                case 'spacer':
                    $height = (int) (data_get($b->settings, 'height', 24));
                    $content .= '<tr><td style="height:'.$height.'px">&nbsp;</td></tr>';
                    break;
                case 'divider':
                    $height = (int) (data_get($b->settings, 'height', 1));
                    $color = data_get($b->settings, 'color', '#e5e7eb');
                    $content .= '<tr><td style="padding:0 20px"><div style="height:'.$height.'px;background:'.e($color).';width:100%"></div></td></tr>';
                    break;
            }
        }

        $wrapper = '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"/><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/></head>'
            . '<body style="margin:0;padding:0;background:#f4f4f5"><table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background:#f4f4f5">'
            . '<tr><td align="center" style="padding:24px 8px"><table role="presentation" cellpadding="0" cellspacing="0" width="600" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden">'
            . $content
            . '</table><div style="font-family:Arial,Helvetica,sans-serif;color:#6b7280;font-size:12px;padding-top:8px">&copy; '.date('Y').' Bonami Sportcoaching</div></td></tr></table></body></html>';
        return $wrapper;
    }
}
