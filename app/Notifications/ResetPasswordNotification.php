<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(private string $token) {}

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Reset Request')
            ->greeting("Hello {$notifiable->name},")
            ->line('You have requested to reset your password.')
            ->line("Your reset token is: **{$this->token}**")
            ->line('Use this token along with your email, new password, and password confirmation via the password reset API endpoint.')
            ->line('This token will expire in 60 minutes.')
            ->line('If you did not request a password reset, please ignore this email.');
    }
}
