<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\TemplateKey;

return new class extends Migration
{
    public function up()
    {
        // Inspanningstest Template Keys toevoegen
        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_ALGEMEEN}}',
            'description' => 'Klantgegevens, testdatum, testtype en lichaamssamenstelling',
            'category' => 'inspanningstest',
        ]);

        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_TRAININGSTATUS}}',
            'description' => 'Slaapkwaliteit, eetlust, gevoel op training en stressniveau',
            'category' => 'inspanningstest',
        ]);

        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_TESTRESULTATEN}}',
            'description' => 'Gedetailleerde tabel met tijd, vermogen/snelheid, lactaat, hartslag en Borg',
            'category' => 'inspanningstest',
        ]);

        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_GRAFIEK}}',
            'description' => 'Hartslag & lactaat progressie grafiek met drempellijnen',
            'category' => 'inspanningstest',
        ]);

        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_DREMPELS}}',
            'description' => 'Aërobe en anaërobe drempelwaarden in tabelvorm',
            'category' => 'inspanningstest',
        ]);

        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_ZONES}}',
            'description' => 'Gedetailleerde trainingszones tabel (Bonami/Karvonen methode)',
            'category' => 'inspanningstest',
        ]);

        TemplateKey::create([
            'key' => '{{INSPANNINGSTEST_AI_ANALYSE}}',
            'description' => 'Complete AI-gegenereerde performance analyse en trainingsadvies',
            'category' => 'inspanningstest',
        ]);
    }

    public function down()
    {
        // Verwijder de inspanningstest keys
        TemplateKey::whereIn('key', [
            '{{INSPANNINGSTEST_ALGEMEEN}}',
            '{{INSPANNINGSTEST_TRAININGSTATUS}}',
            '{{INSPANNINGSTEST_TESTRESULTATEN}}',
            '{{INSPANNINGSTEST_GRAFIEK}}',
            '{{INSPANNINGSTEST_DREMPELS}}',
            '{{INSPANNINGSTEST_ZONES}}',
            '{{INSPANNINGSTEST_AI_ANALYSE}}',
        ])->delete();
    }
};
