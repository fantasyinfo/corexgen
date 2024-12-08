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

        foreach (CRM_TENANT_SETTINGS as $setting) {
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => $setting['media_id'],
                'input_type' => $setting['input_type'],
                'is_tenant' => $setting['is_tenant'],
                'company_id' => $setting['company_id'],
                'updated_by' => Auth::id() ?? '1',
                'created_by' => Auth::id() ?? '1',
            ]);
        }
    }
}
