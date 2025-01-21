<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class TasksStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $user;
    public $newStatus;
    protected $mailSettings;

    /**
     * Create a new notification instance.
     */
    public function __construct($task, $user, $newStatus, $mailSettings)
    {
        $this->task = $task;
        $this->user = $user;
        $this->newStatus = $newStatus;
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
            ->subject('Task Status Updated')
            ->view('emails.task-status-updated', [
                'task' => $this->task,
                'newStatus' => $this->newStatus,
                'user' => $this->user,
            ]);
    }
}
