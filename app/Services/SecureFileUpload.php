<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureFileUpload
{
    /**
     * Valideer en upload een avatar afbeelding
     * EXACT HETZELFDE SYSTEEM ALS KlantController->update()
     */
    public static function uploadAvatar(UploadedFile $file, ?string $oldAvatarPath = null): string
    {
        \Log::info('🔵 uploadAvatar START', [
            'has_file' => !!$file,
            'old_path' => $oldAvatarPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize()
        ]);
        
        // Verwijder oude avatar indien aanwezig (EXACT ZOALS KLANTCONTROLLER)
        if ($oldAvatarPath && \Storage::disk('public')->exists($oldAvatarPath)) {
            \Storage::disk('public')->delete($oldAvatarPath);
            \Log::info('🗑️ Oude avatar verwijderd', ['path' => $oldAvatarPath]);
        }

        // Zorg ervoor dat de klanten directory bestaat
        $avatarDir = storage_path('app/public/avatars/klanten');
        if (!is_dir($avatarDir)) {
            mkdir($avatarDir, 0755, true);
        }

        // Upload nieuwe avatar DIRECT naar storage/app/public/avatars/klanten/
        // EXACT ZOALS KLANTCONTROLLER: gebruik storeAs()
        $filename = \Illuminate\Support\Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Gebruik Laravel Storage facade - sla op in avatars/klanten subdirectory
        $path = $file->storeAs('avatars/klanten', $filename, 'public');
        
        \Log::info('✅ Avatar geüpload', [
            'klant_id' => 'via_service',
            'filename' => $filename,
            'path' => $path,
            'full_path' => storage_path('app/public/' . $path),
            'file_exists' => file_exists(storage_path('app/public/' . $path)),
        ]);

        return $path;
    }
    
    /**
     * Valideer en upload een document
     */
    public static function uploadDocument(UploadedFile $file, string $folder = 'documents', ?string $oldPath = null): string
    {
        // Extra mime type check
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = config('security.uploads.mime_types.documents');
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \Exception('Ongeldig bestandstype gedetecteerd.');
        }
        
        // Delete oude bestand
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        
        // Genereer veilige bestandsnaam
        $extension = $file->getClientOriginalExtension();
        $filename = 'doc_' . uniqid() . '_' . time() . '.' . $extension;
        
        // Upload naar veilige locatie
        return $file->storeAs($folder, $filename, 'public');
    }
    
    /**
     * Valideer en upload een bikefit foto
     */
    public static function uploadBikefitFoto(UploadedFile $file, ?string $oldPath = null): string
    {
        // Extra mime type check
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = config('security.uploads.mime_types.images');
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \Exception('Ongeldig bestandstype gedetecteerd.');
        }
        
        // Delete oude bestand
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
        
        // Genereer veilige bestandsnaam
        $extension = $file->getClientOriginalExtension();
        $filename = 'bikefit_' . uniqid() . '_' . time() . '.' . $extension;
        
        // Upload naar veilige locatie
        return $file->storeAs('bikefits', $filename, 'public');
    }
    
    /**
     * Verwijder een bestand veilig
     */
    public static function deleteFile(?string $path): bool
    {
        if (!$path) {
            return false;
        }
        
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        
        return false;
    }
    
    /**
     * Get validation rules voor avatar upload
     */
    public static function getAvatarValidationRules(): array
    {
        $allowedMimes = implode(',', config('security.uploads.allowed_mimes.images'));
        $maxSize = config('security.uploads.max_size.avatar');
        $dimensions = config('security.uploads.dimensions.avatar');
        
        return [
            'required',
            'image',
            "mimes:{$allowedMimes}",
            "max:{$maxSize}",
            "dimensions:min_width={$dimensions['min_width']},min_height={$dimensions['min_height']},max_width={$dimensions['max_width']},max_height={$dimensions['max_height']}"
        ];
    }
    
    /**
     * Get validation rules voor document upload
     */
    public static function getDocumentValidationRules(): array
    {
        $allowedMimes = implode(',', config('security.uploads.allowed_mimes.documents'));
        $maxSize = config('security.uploads.max_size.document');
        
        return [
            'required',
            "mimes:{$allowedMimes}",
            "max:{$maxSize}"
        ];
    }
}