<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class DocumentCompressionService
{
    /**
     * Comprimeer een geüpload bestand indien mogelijk
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $maxSizeKb Maximum grootte in KB (standaard 50MB)
     * @return \Illuminate\Http\UploadedFile|false
     */
    public function compressIfNeeded($file, int $maxSizeKb = 51200)
    {
        $fileSizeKb = $file->getSize() / 1024;
        $mimeType = $file->getMimeType();

        // Als bestand klein genoeg is, return origineel
        if ($fileSizeKb <= $maxSizeKb) {
            return $file;
        }

        Log::info("Bestand te groot ({$fileSizeKb}KB), probeer te comprimeren...");

        // Comprimeer afbeeldingen
        if (str_starts_with($mimeType, 'image/')) {
            return $this->compressImage($file, $maxSizeKb);
        }

        // Voor PDFs en andere documenten, return origineel
        // (PDF compressie vereist externe libraries)
        Log::warning("Kan bestandstype {$mimeType} niet comprimeren");
        return false;
    }

    /**
     * Comprimeer een afbeelding
     */
    private function compressImage($file, int $maxSizeKb)
    {
        try {
            $image = Image::make($file->getRealPath());
            
            // Schaal afbeelding naar max 2000px breed
            $image->resize(2000, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Sla gecomprimeerde versie op
            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '_compressed.jpg';
            $image->save($tempPath, 85); // 85% kwaliteit

            // Check nieuwe grootte
            $newSizeKb = filesize($tempPath) / 1024;
            
            if ($newSizeKb <= $maxSizeKb) {
                Log::info("Afbeelding gecomprimeerd van {$file->getSize()}KB naar {$newSizeKb}KB");
                
                // Maak nieuwe UploadedFile van gecomprimeerde versie
                return new \Illuminate\Http\UploadedFile(
                    $tempPath,
                    $file->getClientOriginalName(),
                    'image/jpeg',
                    null,
                    true
                );
            }

            // Als nog steeds te groot, return false
            Log::warning("Gecomprimeerde afbeelding nog steeds te groot ({$newSizeKb}KB)");
            return false;

        } catch (\Exception $e) {
            Log::error("Fout bij comprimeren afbeelding: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valideer bestandsgrootte
     */
    public function validateSize($file, int $maxSizeKb = 51200): bool
    {
        $fileSizeKb = $file->getSize() / 1024;
        return $fileSizeKb <= $maxSizeKb;
    }

    /**
     * Format bestandsgrootte leesbaar
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}
