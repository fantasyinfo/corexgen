<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentService
{
    public function add($modal, $data)
    {
        $mediaUrls = $this->createMedia($data);

        return collect($mediaUrls)->map(function ($media) use ($modal, $data) {
            return $modal->attachments()->create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'file_name' => $media['file_name'],
                'file_path' => $media['file_path'],
                'file_type' => $media['file_type'],
                'file_extension' => $media['file_extension'],
                'size' => $media['size'],
            ]);
        });
    }

    public function createMedia($data)
    {
        $files = $data['files'] ?? [$data['file']]; // Handle both single and multiple files
        $savedMedia = [];

        foreach ($files as $file) {
            // Generate file properties
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('attachments', $fileName, 'public');
            $fileType = $file->getMimeType();
            $fileExtension = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();

            // Save file details to array
            $savedMedia[] = [
                'file_name' => $fileName,
                'file_path' => Storage::url($filePath), // Generate a public URL
                'file_type' => $fileType,
                'file_extension' => $fileExtension,
                'size' => $fileSize,
            ];
        }

        return $savedMedia;
    }

    public function deleteMedia($attachment)
    {
        try {
            // Remove the file from storage
            if (Storage::exists(str_replace('/storage', '', $attachment->file_path))) {
                Storage::delete(str_replace('/storage', '', $attachment->file_path));
            }

            // Remove the attachment record
            $attachment->delete();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
