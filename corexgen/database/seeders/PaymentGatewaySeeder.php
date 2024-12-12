<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //



        PaymentGateway::truncate();

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
