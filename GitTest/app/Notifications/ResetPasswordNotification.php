<?php
/**
 * ログインパスワード・リセット用のクラス
 *
 * @author  Miyamoto
 */

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
//use Illuminate\Notifications\Notification;
//use Illuminate\Notifications\Messages\MailMessage;

//class ResetPasswordNotification extends Notification //ResetPassword
class ResetPasswordNotification extends ResetPassword
{    /**
     * The password reset token.
     *
     * @var string
     */
    public $token;

    /**
     * Create a notification instance.
     *
     * @param  string  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('パスワードの再発行')
        ->line('パスワード再発行のリクエストを受け付けました')
        ->action('パスワードを再発行する', route('password.reset', $this->token))
        ->line('もしこのメールに心当たりがない場合は、何もしなくていいです');
    }

}
