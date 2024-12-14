<?php

namespace App\Traits;

use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait MediaTrait
{
    /**
     * Create a new media record and store the file.
     *
     * @param 
     * @param array $attributes
     * @return Media
     */
    public function createMedia($file, $attributes = [])
    {
        if (is_string($file)) {
            // Handle file path for seeding
            $absolutePath = storage_path('app/public/' . $file);
            $filePath = $file; // Relative path for database
            $fileName = basename($file);
            $fileMimeType = mime_content_type($absolutePath);
            $fileExtension = pathinfo($absolutePath, PATHINFO_EXTENSION);
            $fileSize = filesize($absolutePath);
        } else {
            // Handle UploadedFile object for runtime uploads
            $filePath = $file->store($attributes['folder'] ?? 'uploads', 'public');
            $fileName = $file->getClientOriginalName();
            $fileMimeType = $file->getMimeType();
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }
    
        return Media::create([
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $fileMimeType,
            'file_extension' => $fileExtension,
            'size' => $fileSize,
            'company_id' => $attributes['company_id'] ?? null,
            'is_tenant' => $attributes['is_tenant'] ?? false,
            'updated_by' => $attributes['updated_by'] ?? null,
            'created_by' => $attributes['created_by'] ?? null,
        ]);
    }
    
    

    /**
     * Update a media record, optionally deleting the old file.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param Media|null $oldMedia
     * @param array $attributes
     * @return Media
     */
    public function updateMedia($file, $oldMedia = null, $attributes = [])
    {
        if ($oldMedia) {
            Storage::disk('public')->delete($oldMedia->file_path);
            $oldMedia->delete();
        }

        return $this->createMedia($file, $attributes);
    }
}
