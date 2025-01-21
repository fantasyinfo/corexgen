<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class NewCommentAdd extends Notification implements ShouldQueue
{
    use Queueable;

    public $modal;
    public $id;
    public $view;
    public $commentedBy;
    public $comment;
    protected $mailSettings;

    /**
     * Create a new notification instance.
     */
    public function __construct($modal, $id, $view, $commentedBy,$comment, $mailSettings)
    {
        $this->modal = $modal;
        $this->id = $id;
        $this->view = $view;
        $this->commentedBy = $commentedBy;
        $this->comment = $comment;
        $this->mailSettings = $mailSettings;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        // Dynamically configure mail settings
        Config::set([
            'mail.mailers.smtp.host' => $this->mailSettings['Mail Host'],
            'mail.mailers.smtp.port' => (int) $this->mailSettings['Mail Port'],
            'mail.mailers.smtp.username' => $this->mailSettings['Mail Username'],
            'mail.mailers.smtp.password' => $this->mailSettings['Mail Password'],
            'mail.from.address' => $this->mailSettings['Mail From Address'],
            'mail.from.name' => $this->mailSettings['Mail From Name'] ?? config('app.name'),
        ]);

        return (new MailMessage)
            ->subject('New Comment Added')
            ->view('emails.comment-add', [
                'modal' => $this->modal,
                'commentedBy' => $this->commentedBy,
                'comment' => $this->comment,
                'view' => $this->view,
                'id' => $this->id,
            ]);
    }
}
