<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ProjectAssingeeToUser extends Notification implements ShouldQueue
{
    use Queueable;

    public $project;
    public $assigneeBy;
    public $assigneeTo;
    protected $mailSettings;

    /**
     * Create a new notification instance.
     */
    public function __construct($project, $assigneeBy,$assigneeTo, $mailSettings)
    {
        $this->project = $project;
        $this->assigneeBy = $assigneeBy;
        $this->assigneeTo = $assigneeTo;
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
            ->subject('New Project Assignee')
            ->view('emails.project-assignee', [
                'project' => $this->project,
                'assigneeBy' => $this->assigneeBy,
                'assigneeTo' => $this->assigneeTo,
            ]);
    }
}
