<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstantLabsQuantQualReminder1h extends Mailable {

    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.instant-labs.quant-qual-reminder-1h')
            ->subject("Opportunity to receive {$this->data['INCENTIVETOTAL']} : appointment is in one hour");
    }
}
