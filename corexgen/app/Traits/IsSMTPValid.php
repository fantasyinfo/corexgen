<?php

namespace App\Traits;

use App\Models\CRM\CRMSettings;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Mail;

trait IsSMTPValid
{
    public function checkSMTPWorking()
    {
        return $this->_isSMTPValid($this->_getMailSettings());
    }

    public function _isSMTPValid($mailSettings)
    {
        try {
            // Configure mail settings for this specific email
            config([
                'mail.mailers.smtp.host' => $mailSettings['Mail Host'],
                'mail.mailers.smtp.port' => $mailSettings['Mail Port'],
                'mail.mailers.smtp.username' => $mailSettings['Mail Username'],
                'mail.mailers.smtp.password' => $mailSettings['Mail Password'],
                'mail.from.address' => $mailSettings['Mail From Address'],
                'mail.from.name' => $mailSettings['Mail From Name'] ?? config('app.name'),
            ]);

            Mail::raw('This is a test email to verify SMTP settings.', function ($message) {
                $message->to('test@example.com')
                    ->subject('SMTP Configuration Test');
            });

            return [
                'status' => true,
                'error' => null,
            ];
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('SMTP Validation Error: ' . $e->getMessage());

            return [
                'status' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function _getMailSettings($companyId = null)
    {
        return CRMSettings::where('company_id', is_null($companyId) ? Auth::user()->company_id : $companyId)
            ->where('type', 'Mail')
            ->select('key', 'value')
            ->pluck('value', 'key');
    }
}
