<?php
/**
 * コンタクト承認依頼、承認のメール送信用
 *
 * @author  Miyamoto
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Contracts\Queue\ShouldQueue;

class ContactSent extends Mailable
{
    use Queueable, SerializesModels;

    protected $template;
    protected $title;
    protected $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $subject, $message)
    {
        $this->template = $template;
        $this->title    = $subject;
        $this->text     = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.'. $this->template)
        ->text('emails.'. $this->template)
        ->subject($this->title)
        ->with([
                'subject' => $this->title,
                'text' => $this->text,
        ]);
    }
}
