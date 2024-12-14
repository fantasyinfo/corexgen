<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CRM\CRMSettings;
use App\Traits\MediaTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CRMSettingsSeeder extends Seeder
{

    use MediaTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CRMSettings::truncate(); // Clear previous settings

        foreach (CRM_TENANT_GENERAL_SETTINGS as $setting) {
            $media = null;

            // Handle image-specific logic
            if ($setting['input_type'] == 'image') {
                if ($setting['name'] === 'tenant_company_logo') {
                    $relativePath =  $setting['value']; // Relative path for Storage
                    $absolutePath = storage_path('app/public/' . $relativePath); // Absolute path for file operations
            
                    if (Storage::disk('public')->exists($relativePath)) {
                        $media = $this->createMedia($relativePath, [
                            'folder' => 'logos',
                            'created_by' => 1, // Fixed admin ID
                            'updated_by' => 1,
                        ]);
                    } else {
                        \Log::warning("File not found for media creation: {$absolutePath}");
                    }
                }
            }

            // Create CRM setting
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => $media->id ?? null,
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => $setting['is_tenant'],
                'type' => 'General',
                'updated_by' => 1, // Fixed admin ID
                'created_by' => 1,
            ]);
        }
    }
}
