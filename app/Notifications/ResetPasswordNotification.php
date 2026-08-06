<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    protected function buildMailMessage($url): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Reset Request')
            ->greeting('Hello!')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('This password reset link will expire in ' . $this->expirationMinutes() . ' minutes.')
            ->line('If you did not request a password reset, no further action is required.')
            ->salutation("Regards,\nDocument Tracking System");
    }

    protected function expirationMinutes(): int
    {
        return config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');
    }
}
