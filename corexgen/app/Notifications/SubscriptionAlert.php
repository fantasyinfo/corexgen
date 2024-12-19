<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionAlert extends Notification
{
    use Queueable;

    protected $companyName;
    protected $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct($companyName, $daysLeft)
    {
        $this->companyName = $companyName;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Subscription Alert')
            ->line("Your subscription for {$this->companyName} is expiring in {$this->daysLeft} days.")
            ->line('Please renew your subscription to avoid service interruptions.')
            ->action('Renew Subscription', url('/'))
            ->line('Thank you for using our service!');
    }
}

