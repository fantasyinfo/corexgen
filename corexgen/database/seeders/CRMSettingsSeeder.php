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

        foreach (CRM_SETTINGS as $setting) {
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => $setting['media_id'],
                'buyer_id' => 1,
                'is_super_user' => true,
                'updated_by' => 1,
                'created_by' => 1,
            ]);
        }
    }
}
