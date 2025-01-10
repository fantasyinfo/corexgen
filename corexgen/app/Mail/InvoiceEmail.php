<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $emailDetails;
    protected $pdfPath;

    /**
     * Create a new message instance.
     *
     * @param $invoice
     * @param $emailDetails
     * @param $pdfPath
     */
    public function __construct($invoice, $emailDetails, $pdfPath)
    {
        $this->invoice = $invoice;
        $this->emailDetails = $emailDetails;
        $this->pdfPath = $pdfPath;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject($this->emailDetails['subject'])
            ->from($this->emailDetails['from'])
            ->markdown('emails.invoice')
            ->with([
                'invoice' => $this->invoice
            ]);

        // Attach PDF if it exists
        if (file_exists($this->pdfPath)) {
            \Log::info("PDF file exists at: {$this->pdfPath}");
            $this->attach($this->pdfPath, [
                'as' => 'invoice_' . $this->invoice->id . '.pdf',
                'mime' => 'application/pdf',
            ]);
        } else {
            \Log::error("PDF file not found at: {$this->pdfPath}");
        }

        return $this;
    }
}
