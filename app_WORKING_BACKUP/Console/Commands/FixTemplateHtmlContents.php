<?php
// app/Console/Commands/FixTemplateHtmlContents.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTemplateHtmlContents extends Command
{
    protected $signature = 'fix:template-html-contents';
    protected $description = 'Zet alle templates met string html_contents om naar een JSON-array.';

    public function handle()
    {
        $templates = DB::table('templates')->get();
        $fixedCount = 0;
        foreach ($templates as $template) {
            $decoded = json_decode($template->html_contents, true);
            if (is_string($decoded)) {
                $fixed = json_encode([$decoded]);
                DB::table('templates')->where('id', $template->id)->update([
                    'html_contents' => $fixed
                ]);
                $this->info("Template #{$template->id} gefixt (string -> array)");
                $fixedCount++;
            }
        }
        $this->info("Klaar. {$fixedCount} templates aangepast.");
        return 0;
    }
}
