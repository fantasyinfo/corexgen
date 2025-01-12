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
        //// Log::info('SubscriptionCheckCommand started.');

        if (!$this->fetchSmtpDetails()) {
            // Log::error('SMTP details could not be fetched. Exiting command.');
            return;
        }

        $alertBeforeDays = [7, 6, 5, 4, 3, 2, 1];
        $today = now();
        //// Log::info('Fetching subscriptions with active end dates.');

        $subscriptions = Subscription::with('company')->select('id', 'end_date', 'company_id')
            ->where('end_date', '>=', $today)
            ->orderBy('end_date', 'desc')
            ->get()
            ->unique('company_id');

        // Log::info('Active subscriptions fetched.', ['count' => $subscriptions->count()]);

        foreach ($subscriptions as $subscription) {
            $daysLeft = $today->diffInDays($subscription->end_date, false);

            // Log::info("Processing subscription ID {$subscription->id}, Days Left: {$daysLeft}");

            if (in_array($daysLeft, $alertBeforeDays)) {
                // Log::info("Sending alert for subscription ID {$subscription->id}");
                $this->alertCompany($subscription, $daysLeft);
            }
        }

        // Log::info('Checking expired subscriptions.');

        // Handle expired subscriptions and deactivate their companies
        $expiredSubscriptions = Subscription::with('company')->select('id', 'end_date', 'company_id')
            ->where('end_date', '<', $today)
            ->get();

        // Log::info('Expired subscriptions fetched.', ['count' => $expiredSubscriptions->count()]);

        foreach ($expiredSubscriptions as $subscription) {
            // Log::info("Deactivating company for subscription ID {$subscription->id}");
            $this->deactivateCompany($subscription);
            $this->alertCompany($subscription, 0);
        }

        // Log::info("SubscriptionCheckCommand completed successfully.");
        $this->info("Subscription alerts processed successfully.");
    }

    private function alertCompany($subscription, $daysLeft)
    {
        $companyEmail = $subscription->company->email;
        // Log::info("Preparing to alert company ID {$subscription->company->id} for subscription ID {$subscription->id}");

        if ($companyEmail) {
            // Log::info("Notifying Company ID {$subscription->company->id} at email {$companyEmail}.");

            try {
                DB::table('notification_record')->insert([
                    'name' => 'Subscription Alert',
                    'type' => 'mail',
                    'company_id' => $subscription->company->id,
                    'data' => json_encode($subscription),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
                // Log::info("Notification record inserted successfully for company ID {$subscription->company->id}.");
            } catch (\Exception $e) {
                // Log::error("Failed to insert notification record for company ID {$subscription->company->id}: " . $e->getMessage());
            }

            // Log::info("Notification record created for Company ID {$subscription->company->id}.");

            Notification::route('mail', $companyEmail)
                ->notify(new SubscriptionAlert($subscription->company->name, $daysLeft));
        } else {
            // Log::warning("No email found for Company ID {$subscription->company->id}. Cannot send notification.");
        }
    }

    public function fetchSmtpDetails()
    {
        // Log::info('Fetching SMTP details from CRMSettings.');

        $smtpSettings = CRMSettings::where('is_tenant', '1')
            ->where('company_id', null)
            ->where('type', SETTINGS_MENU_ITEMS['Mail']['name'])
            ->distinct()
            ->get();

        if ($smtpSettings->isEmpty()) {
            $this->error('SMTP settings not found in the database.');
            // Log::error('SMTP settings are missing in CRMSettings.');
            return false;
        }

        // Log::info('SMTP settings fetched successfully.');

        // Extract individual settings
        $mailer = $smtpSettings->where('name', 'tenant_mail_provider')->first();
        $host = $smtpSettings->where('name', 'tenant_mail_host')->first();
        $port = $smtpSettings->where('name', 'tenant_mail_port')->first();
        $username = $smtpSettings->where('name', 'tenant_mail_username')->first();
        $password = $smtpSettings->where('name', 'tenant_mail_password')->first();
        $encryption = $smtpSettings->where('name', 'tenant_mail_encryption')->first();
        $from_address = $smtpSettings->where('name', 'tenant_mail_from_address')->first();
        $from_name = $smtpSettings->where('name', 'tenant_mail_from_name')->first();

        $portValue = $port && is_numeric($port->value) ? (int) $port->value : 465;

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

        // Log::info('SMTP configuration applied successfully.');

        return true;
    }

    private function deactivateCompany($subscription)
    {
        $company = $subscription->company;

        if ($company && $company->status !== 'DEACTIVE') {
            // Log::info("Deactivating company ID {$company->id} due to expired subscription ID {$subscription->id}");
            $company->status = 'DEACTIVE';
            $company->save();
            // Log::info("Company ID {$company->id} has been deactivated successfully.");
        } else {
            // Log::warning("Company ID {$subscription->company->id} is already deactivated or does not exist.");
        }
    }
}
