<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CRM\CRMSettings;
use App\Traits\MediaTrait;
use Illuminate\Support\Facades\Storage;

class CRMSettingsSeeder extends Seeder
{
    use MediaTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If no type is specified, seed all types

        $this->seedGeneralSettings();
        $this->seedMailSettings();
        $this->seedThemeSettings();

    }

    /**
     * Seed general settings
     */
    protected function seedGeneralSettings(): void
    {
        // Delete existing general settings
        CRMSettings::where('type', 'General')->where('is_tenant', '1')->delete();

        foreach (CRM_TENANT_GENERAL_SETTINGS as $setting) {
            $media = null;

            // Handle image-specific logic
            if ($setting['input_type'] == 'image') {
                if ($setting['name'] === 'tenant_company_logo') {
                    $relativePath = $setting['value']; // Relative path for Storage
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
                'is_tenant' => @$setting['is_tenant'] ?? false,
                'type' => 'General',
                'updated_by' => null,
                'created_by' => null,
            ]);
        }
    }

    /**
     * Seed mail settings
     */
    protected function seedMailSettings(): void
    {
        // Delete existing mail settings
        CRMSettings::where('type', 'Mail')->where('is_tenant', '1')->delete();

        foreach (CRM_TENANT_MAIL_SETTINGS as $setting) {
            // Create CRM setting
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => null,
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => @$setting['is_tenant'] ?? false,
                'type' => 'Mail',
                'updated_by' => null,
                'created_by' => null,
            ]);
        }
    }

    /**
     * seed theme settings
     */
    protected function seedThemeSettings()
    {
        // Delete existing mail settings
        CRMSettings::where('type', 'Theme')->where('is_tenant', '1')->delete();

        // light colors
        foreach (CRM_TENANT_THEME_LIGHT_SETTINGS as $setting) {
            // Create CRM setting
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => null,
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => @$setting['is_tenant'] ?? false,
                'type' => 'Theme',
                'updated_by' => null,
                'created_by' => null,
            ]);
        }

        // dark colors
        foreach (CRM_TENANT_THEME_DARK_SETTINGS as $setting) {
            // Create CRM setting
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => null,
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => @$setting['is_tenant'] ?? false,
                'type' => 'Theme',
                'updated_by' => null,
                'created_by' => null,
            ]);
        }
    }
}