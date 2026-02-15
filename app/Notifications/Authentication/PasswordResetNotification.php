<?php

namespace App\Notifications\Authentication;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends Notification
{
    use Queueable;

    protected $resetUrl;
    protected $userName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $resetUrl, string $userName)
    {
        $this->resetUrl = $resetUrl;
        $this->userName = $userName;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting('Hello ' . $this->userName . ',')
            ->line('You requested to reset your password. Click the button below to set a new password.')
            ->action('Reset Password', $this->resetUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Store notification in database.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reset_url' => $this->resetUrl,
            'user_name' => $this->userName,
        ];
    }
}
