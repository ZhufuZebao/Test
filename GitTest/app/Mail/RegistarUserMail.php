<?php
/**
 * Androidアプリの新規ユーザー登録確認メールを送る
 *
 * @author  Owada
 */
namespace App\Mail;

use Illuminate\Mail\Mailable;

class RegistarUserMail extends Mailable
{

    protected $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
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
        ->subject('【SITE】ユーザー登録完了メール')
        ->text('emails.registarUser'
            , ['mail' => $this->user->email,
               'name' => $this->user->name]);
    }
}
