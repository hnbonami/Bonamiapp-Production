<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;

class FileCompressionService
{
    /**
     * Comprimeer een afbeelding
     */
    public function compressImage(UploadedFile $file, int $quality = 85): array
    {
        try {
            $originalSize = $file->getSize();
            
            // Maak image instance
            $image = Image::make($file->getRealPath());
            
            // Resize als te groot (max 1920x1080, behoud aspect ratio)
            if ($image->width() > 1920 || $image->height() > 1080) {
                $image->resize(1920, 1080, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Maak tijdelijk bestand voor gecomprimeerde versie
            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Comprimeer op basis van type
            if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg'])) {
                $image->save($tempPath, $quality);
            } elseif (strtolower($file->getClientOriginalExtension()) === 'png') {
                // PNG compressie (0-9, waar 9 = meeste compressie)
                $image->save($tempPath, 90); // PNG quality is andersom
            } else {
                $image->save($tempPath);
            }
            
            $compressedSize = filesize($tempPath);
            
            Log::info('Image compressed', [
                'original' => $originalSize,
                'compressed' => $compressedSize,
                'ratio' => round((1 - ($compressedSize / $originalSize)) * 100, 1) . '%'
            ]);
            
            return [
                'success' => true,
                'path' => $tempPath,
                'original_size' => $originalSize,
                'compressed_size' => $compressedSize,
                'compression_ratio' => round((1 - ($compressedSize / $originalSize)) * 100, 1)
            ];
            
        } catch (\Exception $e) {
            Log::error('Image compression failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'path' => $file->getRealPath(),
                'original_size' => $file->getSize(),
                'compressed_size' => $file->getSize(),
                'compression_ratio' => 0
            ];
        }
    }

    /**
     * Comprimeer een PDF (optioneel, vereist Ghostscript)
     */
    public function compressPdf(UploadedFile $file): array
    {
        try {
            // Check of Ghostscript beschikbaar is
            $gsPath = $this->findGhostscript();
            
            if (!$gsPath) {
                Log::info('Ghostscript not available, skipping PDF compression');
                return [
                    'success' => false,
                    'path' => $file->getRealPath(),
                    'original_size' => $file->getSize(),
                    'compressed_size' => $file->getSize(),
                    'compression_ratio' => 0,
                    'message' => 'Ghostscript niet beschikbaar'
                ];
            }
            
            $originalSize = $file->getSize();
            $tempPath = sys_get_temp_dir() . '/' . uniqid() . '.pdf';
            
            // Ghostscript command voor PDF compressie
            $command = sprintf(
                '%s -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s',
                escapeshellarg($gsPath),
                escapeshellarg($tempPath),
                escapeshellarg($file->getRealPath())
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0 && file_exists($tempPath)) {
                $compressedSize = filesize($tempPath);
                
                Log::info('PDF compressed', [
                    'original' => $originalSize,
                    'compressed' => $compressedSize,
                    'ratio' => round((1 - ($compressedSize / $originalSize)) * 100, 1) . '%'
                ]);
                
                return [
                    'success' => true,
                    'path' => $tempPath,
                    'original_size' => $originalSize,
                    'compressed_size' => $compressedSize,
                    'compression_ratio' => round((1 - ($compressedSize / $originalSize)) * 100, 1)
                ];
            }
            
            // Fallback als compressie mislukt
            return [
                'success' => false,
                'path' => $file->getRealPath(),
                'original_size' => $originalSize,
                'compressed_size' => $originalSize,
                'compression_ratio' => 0
            ];
            
        } catch (\Exception $e) {
            Log::error('PDF compression failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'path' => $file->getRealPath(),
                'original_size' => $file->getSize(),
                'compressed_size' => $file->getSize(),
                'compression_ratio' => 0
            ];
        }
    }

    /**
     * Optimaliseer opslag op basis van bestandstype
     */
    public function optimizeStorage(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        // Afbeeldingen comprimeren
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return $this->compressImage($file);
        }
        
        // PDF comprimeren
        if ($extension === 'pdf') {
            return $this->compressPdf($file);
        }
        
        // Andere bestanden: geen compressie
        return [
            'success' => false,
            'path' => $file->getRealPath(),
            'original_size' => $file->getSize(),
            'compressed_size' => $file->getSize(),
            'compression_ratio' => 0,
            'message' => 'Geen compressie voor dit bestandstype'
        ];
    }

    /**
     * Zoek Ghostscript executable
     */
    private function findGhostscript(): ?string
    {
        $possiblePaths = [
            '/usr/bin/gs',
            '/usr/local/bin/gs',
            '/opt/homebrew/bin/gs', // macOS Homebrew
            'C:\Program Files\gs\gs9.56.1\bin\gswin64c.exe', // Windows
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Probeer via PATH
        exec('which gs', $output, $returnCode);
        if ($returnCode === 0 && !empty($output[0])) {
            return $output[0];
        }
        
        return null;
    }

    /**
     * Get compressie ratio tussen twee groottes
     */
    public function getCompressionRatio(int $original, int $compressed): float
    {
        if ($original === 0) {
            return 0;
        }
        
        return round((1 - ($compressed / $original)) * 100, 1);
    }
}
