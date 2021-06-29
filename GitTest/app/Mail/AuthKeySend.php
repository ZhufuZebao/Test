<?php
/**
 * Androidアプリの新規ユーザー登録で認証キーを送る
 *
 * @author  Miyamoto
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AuthKeySend extends Mailable
{
    use Queueable, SerializesModels;

    protected $authkey;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($authkey)
    {
        $this->authkey = $authkey;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->from('noreply@conit.site')
        ->subject('【SITE】メールアドレス確認メール')
        ->text('emails.authkey', ['authkey' => $this->authkey]);
    }
}
