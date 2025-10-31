<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SecureFileUpload
{
    /**
     * Valideer en upload een avatar afbeelding
     */
    public static function uploadAvatar(UploadedFile $file, ?string $oldPath = null): string
    {
        // Validatie regels uit config
        $allowedMimes = config('security.uploads.allowed_mimes.images');
        $maxSize = config('security.uploads.max_size.avatar');
        $dimensions = config('security.uploads.dimensions.avatar');
        
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
        $filename = 'avatar_' . uniqid() . '_' . time() . '.' . $extension;
        
        // Upload naar veilige locatie
        return $file->storeAs('avatars', $filename, 'public');
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