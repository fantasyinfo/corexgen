<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ProposalEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $proposal;
    public $emailDetails;
    protected $pdfPath;

    /**
     * Create a new message instance.
     *
     * @param $proposal
     * @param $emailDetails
     * @param $pdfPath
     */
    public function __construct($proposal, $emailDetails, $pdfPath)
    {
        $this->proposal = $proposal;
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
            ->markdown('emails.proposal')
            ->with([
                'proposal' => $this->proposal,
                'details' => $this->emailDetails['details'],
                'template' => $this->emailDetails['template'],
            ]);

        // Attach PDF if it exists
        if (file_exists($this->pdfPath)) {
            \Log::info("PDF file exists at: {$this->pdfPath}");
            $this->attach($this->pdfPath, [
                'as' => 'proposal_' . $this->proposal->id . '.pdf',
                'mime' => 'application/pdf',
            ]);
        } else {
            \Log::error("PDF file not found at: {$this->pdfPath}");
        }

        return $this;
    }
}
