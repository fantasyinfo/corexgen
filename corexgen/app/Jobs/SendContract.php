<?php
// Job class: App/Jobs/SendProposal.php
namespace App\Jobs;

use App\Mail\ContractEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendContract implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailSettings;
    protected $emailDetails;
    protected $contract;
    protected $viewPath;

    public function __construct($mailSettings, $emailDetails, $contract, $viewPath)
    {
        $this->mailSettings = $mailSettings;
        $this->emailDetails = $emailDetails;
        $this->contract = $contract;
        $this->viewPath = $viewPath;
    }

    public function handle()
    {
        // Generate PDF
        $pdf = PDF::loadView($this->viewPath, [
            'contract' => $this->contract
        ]);
        
        // Generate temporary file path for PDF
        $pdfPath = storage_path('app/temp/contract_' . $this->contract->id . '.pdf');
        
        // Save PDF temporarily
        $pdf->save($pdfPath);

        if (file_exists($pdfPath)) {
            \Log::info("PDF file exists at: {$pdfPath}");
        } else {
            \Log::error("PDF file not found at: {$pdfPath}");
        }

        // Configure mail settings for this specific email
        config([
            'mail.mailers.smtp.host' => $this->mailSettings['Mail Host'],
            'mail.mailers.smtp.port' => $this->mailSettings['Mail Port'],
            'mail.mailers.smtp.username' => $this->mailSettings['Mail Username'],
            'mail.mailers.smtp.password' => $this->mailSettings['Mail Password'],
            'mail.from.address' => $this->mailSettings['Mail From Address'],
            'mail.from.name' => $this->mailSettings['Mail From Name'] ?? config('app.name'),
        ]);

        // Send email with PDF attachment
        Mail::to($this->emailDetails['to'])
            ->send(new ContractEmail(
                $this->contract,
                $this->emailDetails,
                $pdfPath
            ));

        // Clean up - delete temporary PDF file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
    }
}