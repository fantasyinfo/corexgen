<?php
// Job class: App/Jobs/SendProposal.php
namespace App\Jobs;

use App\Mail\InvoiceEmail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailSettings;
    protected $emailDetails;
    protected $invoice;
    protected $viewPath;

    public function __construct($mailSettings, $emailDetails, $invoice, $viewPath)
    {
        $this->mailSettings = $mailSettings;
        $this->emailDetails = $emailDetails;
        $this->invoice = $invoice;
        $this->viewPath = $viewPath;
    }

     /**
     * send invoice on email job
     */
    public function handle()
    {
        // Generate PDF
        $pdf = PDF::loadView($this->viewPath, [
            'invoice' => $this->invoice
        ]);
        
        // Generate temporary file path for PDF
        $pdfPath = storage_path('app/temp/invoice_' . $this->invoice->id . '.pdf');
        
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
            'mail.mailers.smtp.port' => (int) $this->mailSettings['Mail Port'],
            'mail.mailers.smtp.username' => $this->mailSettings['Mail Username'],
            'mail.mailers.smtp.password' => $this->mailSettings['Mail Password'],
            'mail.from.address' => $this->mailSettings['Mail From Address'],
            'mail.from.name' => $this->mailSettings['Mail From Name'] ?? config('app.name'),
        ]);

        // Send email with PDF attachment
        Mail::to($this->emailDetails['to'])
            ->send(new InvoiceEmail(
                $this->invoice,
                $this->emailDetails,
                $pdfPath
            ));

        // Clean up - delete temporary PDF file
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
    }
}