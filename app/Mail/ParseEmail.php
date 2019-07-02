<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ParseEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $parseInfo;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($parseInfo)
    {
        $this->parseInfo = $parseInfo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->from('message@buhgalter.online24.net.ua')
            ->subject('Ошибки парсера Buhgalter.online24.net.ua за последнюю неделю')
            ->view('emails.parseError')
            ->with('parseInfo', $this->parseInfo);
            ;
    }
}
