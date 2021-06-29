<?php
/**
 * Created by PhpStorm.
 * User: P0128147
 * Date: 2019/07/11
 * Time: 13:54
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactInvite extends Mailable
{
    use Queueable, SerializesModels;

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
            ->subject('【SITE】招待メール')
            ->text('emails.contactRequest'
                , [
                    'name' => $this->name,
                    'massage' => $this->massage,
                    'url' => $this->url]);
    }
}
