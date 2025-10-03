<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportTemplate;

class ReportTemplateSeeder extends Seeder
{
    public function run()
    {
    $templates = [
        ['name'=>'Inspanning - Fietsen','slug'=>'inspanning-fietsen','kind'=>'inspanningstest_fietsen'],
        ['name'=>'Inspanning - Lopen','slug'=>'inspanning-lopen','kind'=>'inspanningstest_lopen'],
        ['name'=>'Standaard Bikefit','slug'=>'bikefit-standard','kind'=>'standaard_bikefit'],
        ['name'=>'Professionele Bikefit','slug'=>'bikefit-pro','kind'=>'professionele_bikefit'],
        ['name'=>'Zadeldrukmeting','slug'=>'zadeldruk','kind'=>'zadeldrukmeting'],
        ['name'=>'Maatbepaling','slug'=>'maatbepaling','kind'=>'maatbepaling'],
        ];
        foreach($templates as $i => $t){
            if (!ReportTemplate::where('slug', $t['slug'])->exists()){
                ReportTemplate::create([
                    'name' => $t['name'],
                    'slug' => $t['slug'],
                    'kind' => $t['kind'],
                    'json_layout' => json_encode([
                        ['type' => 'cover', 'label' => 'Cover'],
                        ['type' => 'measurements', 'label' => 'Metingen'],
                        ['type' => 'photo_gallery', 'label' => 'Foto galerij'],
                        ['type' => 'text', 'label' => 'Opmerkingen', 'props' => ['text' => 'Vul hier algemene opmerkingen in.']],
                    ], JSON_PRETTY_PRINT),
                    'is_active' => $i===0 ? true : false,
                    'created_by' => 1,
                ]);
            }
        }
    }
}
