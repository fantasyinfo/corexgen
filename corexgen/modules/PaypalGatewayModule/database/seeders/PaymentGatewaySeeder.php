<?php

namespace Modules\PaypalGatewayModule\Database\Seeders;


use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $gateway = [
            'name' => 'Paypal',
            'official_website' => 'paypal.com',
            'logo' => 'paypal.png',
            'type' => 'International',
            'config_key' => 'key___',
            'config_value' => 'sec___',
            'mode' => 'TEST',
            'status' => 'Active'
        ];


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

    public function rollback(): void
    {
        PaymentGateway::where('name', 'Paypal')->delete();
    }
}
