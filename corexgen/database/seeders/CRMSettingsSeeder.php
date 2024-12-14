<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CRM\CRMSettings;
use Illuminate\Support\Facades\Auth;

class CRMSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        CRMSettings::truncate();

        foreach (CRM_TENANT_GENERAL_SETTINGS as $setting) {
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => $setting['media_id'],
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => $setting['is_tenant'],
                'type' => 'General',
                'updated_by' => Auth::id() ?? null,
                'created_by' => Auth::id() ?? null,
            ]);
        }
    }
}
