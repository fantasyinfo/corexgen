<?php
namespace App\Console\Commands;

use App\Models\CRM\CRMSettings;
use App\Models\Subscription;
use App\Notifications\SubscriptionAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SubscriptionCheckCommand extends Command
{
    protected $signature = 'app:subscription-check';
    protected $description = 'Checking Company Subscription and alerting the notifications';

    public function handle()
    {

        if (!$this->fetchSmtpDetails()) {
            return;
        }

        $alertBeforeDays = [7, 6, 5, 4, 3, 2, 1];
        $today = now();

        $subscriptions = Subscription::with('company')->select('id', 'end_date', 'company_id')
            ->where('end_date', '>=', $today)
            ->orderBy('end_date', 'desc')
            ->get()
            ->unique('company_id');

        //Log::info('Processing Subscriptions:', ['count' => $subscriptions->count()]);

        foreach ($subscriptions as $subscription) {
            $daysLeft = $today->diffInDays($subscription->end_date, false);

           // Log::info("Subscription ID {$subscription->id} has {$daysLeft} days left.");

            if (in_array($daysLeft, $alertBeforeDays)) {
                $this->alertCompany($subscription, $daysLeft);
            }
        }

        $this->info("Subscription alerts processed successfully.");
    }

    private function alertCompany($subscription, $daysLeft)
    {
        $companyEmail = $subscription->company->email;

        if ($companyEmail) {
           //Log::info("Notifying Company ID {$subscription->company->id} at email {$companyEmail}.");

            DB::table('notification_record')->insert([
                'name' => 'Subscription Alert',
                'type' => 'mail',
                'company_id' => $subscription->company->id,
                'data' => json_encode($subscription),
                'updated_at' => now(),
                'created_at' => now(),
            ]);

            Notification::route('mail', $companyEmail)
                ->notify(new SubscriptionAlert($subscription->company->name, $daysLeft));
        } else {
            Log::warning("No email found for Company ID {$subscription->company->id}.");
        }
    }

    public function fetchSmtpDetails()
    {
        // Fetch SMTP details from CRMSettings
        $smtpSettings = CRMSettings::where('is_tenant', '1')
            ->where('company_id', null)
            ->where('type', SETTINGS_MENU_ITEMS['Mail']['name'])
            ->distinct()
            ->get();

        if ($smtpSettings->isEmpty()) {
            $this->error('SMTP settings not found in the database.');
            return false;
        }

        // Extract individual settings
        $mailer = $smtpSettings->where('name', 'tenant_mail_provider')->first();
        $host = $smtpSettings->where('name', 'tenant_mail_host')->first();
        $port = $smtpSettings->where('name', 'tenant_mail_port')->first();
        $username = $smtpSettings->where('name', 'tenant_mail_username')->first();
        $password = $smtpSettings->where('name', 'tenant_mail_password')->first();
        $encryption = $smtpSettings->where('name', 'tenant_mail_encryption')->first();
        $from_address = $smtpSettings->where('name', 'tenant_mail_from_address')->first();
        $from_name = $smtpSettings->where('name', 'tenant_mail_from_name')->first();

        // Explicitly handle port as an integer
        $portValue = $port && is_numeric($port->value) ? (int) $port->value : 465;

        // Validate and set configuration values
        config([
            'mail.default' => $mailer->value ?? 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => $host->value ?? 'smtp.gmail.com',
                'port' => $portValue,
                'encryption' => $encryption->value ?? 'ssl',
                'username' => $username->value ?? 'example@gmail.com',
                'password' => $password->value ?? 'secret',
            ],
            'mail.from' => [
                'address' => $from_address->value ?? 'example@gmail.com',
                'name' => $from_name->value ?? 'Core X Gen',
            ],
        ]);

        // Verify final configuration
        //Log::info('Final Mail Configuration', config('mail.mailers.smtp'));

        $this->info('SMTP settings loaded successfully.');

        return true;
    }


}
