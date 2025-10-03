<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConvertPdfBackgroundToImage extends Command
{
    protected $signature = 'background:convert {pdfPath} {outputDir}';
    protected $description = 'Converteer PDF achtergrond naar PNG voor rapporten';

    public function handle()
    {
        $pdfPath = $this->argument('pdfPath');
        $outputDir = $this->argument('outputDir');
        if (!file_exists($pdfPath)) {
            $this->error('PDF niet gevonden: ' . $pdfPath);
            return 1;
        }
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }
        try {
            // Gebruik pdf2image via shell (Python)
            $outputFile = $outputDir . '/background.png';
            $cmd = "python3 -c 'from pdf2image import convert_from_path; images = convert_from_path(\"$pdfPath\", dpi=200); images[0].save(\"$outputFile\", \"PNG\")'";
            exec($cmd, $output, $exitCode);
            if ($exitCode === 0 && file_exists($outputFile)) {
                $this->info('PNG succesvol aangemaakt: ' . $outputFile);
                return 0;
            } else {
                $this->error('Conversie mislukt.');
                return 2;
            }
        } catch (\Exception $e) {
            $this->error('Fout: ' . $e->getMessage());
            return 3;
        }
    }
}
