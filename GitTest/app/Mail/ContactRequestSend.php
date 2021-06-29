<?php
/**
 * Androidアプリのコンタクト申請メール送信
 *
 * @author  Miyamoto
 */
namespace App\Mail;

use Illuminate\Mail\Mailable;

class ContactRequestSend extends Mailable
{
    protected $name;
    protected $massage;
    protected $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $massage, $url)
    {
        $this->name = $name;
        $this->massage = $massage;
        $this->url = $url;
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
        ->subject('【SITE】招待メール')
        ->text('emails.contactRequest'
            , [
                'name' => $this->name,
                'massage' => $this->massage,
                'url' => $this->url]);
    }
}
