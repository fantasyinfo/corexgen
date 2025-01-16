<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //


        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PaymentGateway::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach (PAYMENT_GATEWAYS as $gateway) {
            PaymentGateway::create([
                'name' => $gateway['name'],
                'official_website' => $gateway['official_website'],
                'logo' => $gateway['logo'],
                'type' => $gateway['type'],
                'config_key' => $gateway['config_key'],
                'config_value' => $gateway['config_value'],
                'mode' => $gateway['mode'],
                'status' => $gateway['status']
            ]);
        }
    }
}
