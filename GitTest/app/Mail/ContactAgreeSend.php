<?php
/**
 * Androidアプリのコンタクト承認メール送信
 *
 * @author  Miyamoto
 */
namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactAgreeSend extends Mailable
{
    protected $name;
    protected $massage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $massage)
    {
        $this->massage = $massage;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->massage) {
            $text = $this->name."さんからのメッセージ：\n".$this->massage;
            $this->massage = $text;
        }

        return $this
        ->from('noreply@conit.site')
        ->subject('【SITE】招待承認メール')
        ->text('emails.contactAgree'
            , [
                'name' => $this->name,
                'massage' => $this->massage]);
    }
}
