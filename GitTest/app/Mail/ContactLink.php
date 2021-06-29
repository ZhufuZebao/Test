<?php
/**
 * 事業者メールで認証キーを送る
 *
 * @author  Miyamoto
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactLink extends Mailable
{
    use Queueable, SerializesModels;

    protected $authKey;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($authKey)
    {
        $this->authKey = $authKey;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('【SITE】メールアドレス確認メール')
            ->text('emails.contactLink', ['authKey' => $this->authKey]);
    }
}
