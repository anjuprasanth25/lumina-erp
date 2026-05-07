<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeOnboardNotification extends Notification
{
    use Queueable;

    public string $token;
    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
        $setupUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Welcome to Lumina ERP — Setup Your Account Profile')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your enterprise administrator has provisioned your new employee user profile inside the Lumina ERP system.')
            ->line('To finalize your registration and access your company dashboards, click the activation button below to set up your personal access password:')
            ->action('Activate Account & Set Password', $setupUrl)
            ->line('Please note that this invitation link is temporary and will expire automatically for security compliance.')
            ->line('If you experience any issues logging in, please contact your internal corporate system administrator.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
