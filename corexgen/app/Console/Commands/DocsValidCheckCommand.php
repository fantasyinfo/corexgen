<?php
namespace App\Console\Commands;

use App\Models\CRM\CRMContract;
use App\Models\CRM\CRMEstimate;
use App\Models\CRM\CRMProposals;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Expire all the docs if valid / due date is passed DocsValidCheckCommand
 */
class DocsValidCheckCommand extends Command
{
    protected $signature = 'app:docs-check';
    protected $description = 'Checking Company Proposals, Contracts, Estimates Expiry Dates & Update Status.';

    /**
     * expire proposals, estimates, contracts, invoice of handle
     * @return void
     */
    public function handle()
    {
        //Log::info('DocsValidCheckCommand started.');

        $this->expireProposals();
        $this->expireEstimates();
        $this->expireContracts();
        $this->overDueInvoice();

        //Log::info('DocsValidCheckCommand completed.');
    }

    /**
     * expire proposals,
     */
    private function expireProposals()
    {
        $today = now();

        // Check expired proposals
        $expiredProposals = CRMProposals::with('company')
            ->whereNotNull('valid_date')
            ->where('valid_date', '<', $today)
            ->get();

        //Log::info('Expired proposals fetched.', ['count' => $expiredProposals->count()]);

        foreach ($expiredProposals as $proposal) {
            $companyId = $proposal->company->id ?? 'Unknown';
            // Log::info("Expiring proposal ID {$proposal->id} for company ID {$companyId}");
            $proposal->update(['status' => 'EXPIRED']);
        }
    }

    /**
     * expire estimates,
     */
    private function expireEstimates()
    {
        $today = now();

        // Check expired estimates
        $expiredEstimates = CRMEstimate::with('company')
            ->whereNotNull('valid_date')
            ->where('valid_date', '<', $today)
            ->get();

        //Log::info('Expired estimates fetched.', ['count' => $expiredEstimates->count()]);

        foreach ($expiredEstimates as $estimate) {
            $companyId = $estimate->company->id ?? 'Unknown';
            // Log::info("Expiring estimate ID {$estimate->id} for company ID {$companyId}");
            $estimate->update(['status' => 'EXPIRED']);
        }
    }

    /**
     * expire contracts,
     */
    private function expireContracts()
    {
        $today = now();

        // Check expired contracts
        $expiredContracts = CRMContract::with('company')
            ->whereNotNull('valid_date')
            ->where('valid_date', '<', $today)
            ->get();

        //Log::info('Expired contracts fetched.', ['count' => $expiredContracts->count()]);

        foreach ($expiredContracts as $contract) {
            $companyId = $contract->company->id ?? 'Unknown';
            // Log::info("Expiring contract ID {$contract->id} for company ID {$companyId}");
            $contract->update(['status' => 'EXPIRED']);
        }
    }
    /**
     * expire dueinvoice,
     */
    private function overDueInvoice()
    {
        $today = now();

        // Check expired contracts
        $dueDatesOfInvoices = Invoice::with('company')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->get();

        //Log::info('Expired contracts fetched.', ['count' => $dueDatesOfInvoices->count()]);

        foreach ($dueDatesOfInvoices as $invoice) {
            $companyId = $invoice->company->id ?? 'Unknown';
            // Log::info("Expiring invoice ID {$invoice->id} for company ID {$companyId}");
            $invoice->update(['status' => 'OVERDUE']);
        }
    }
}
