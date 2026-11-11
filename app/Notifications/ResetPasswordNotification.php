<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Email reset password berbahasa Indonesia (PBI-03).
 */
class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset Password AbisLulus')
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line('Kami menerima permintaan untuk mengatur ulang password akun AbisLulus kamu.')
            ->action('Atur Ulang Password', $this->resetUrl($notifiable))
            ->line("Link ini berlaku selama {$expire} menit.")
            ->line('Jika kamu tidak meminta reset password, abaikan email ini. Password kamu tidak akan berubah.')
            ->salutation('Salam, Tim AbisLulus');
    }
}
