<?php

namespace App\Traits;

use App\Models\CRM\CRMSettings;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

trait IsSMTPValid
{
    public function checkSMTPWorking()
    {
        return $this->_isSMTPValid($this->_getMailSettings());
    }

    public function _isSMTPValid($mailSettings)
    {
        try {
            // Create the SMTP transport
            $transport = new EsmtpTransport(
                $mailSettings['Mail Host'] ?? '',
                $mailSettings['Mail Port'] ?? '',
                $mailSettings['Mail Encryption'] ?? ''
            );
            $transport->setUsername($mailSettings['Mail Username'] ?? '');
            $transport->setPassword($mailSettings['Mail Password'] ?? '');

            // Test the connection
            $transport->start();

            return true;
        } catch (\Exception $e) {
            \Log::error('SMTP Validation Error: ' . $e->getMessage());
            return false;
        }
    }

    public function _getMailSettings($companyId = null)
    {
        return CRMSettings::where('company_id', is_null($companyId) ?? Auth::user()->company_id)
            ->where('type', 'Mail')
            ->select('key', 'value')
            ->pluck('value', 'key');
    }
}
