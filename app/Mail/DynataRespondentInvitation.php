<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DynataRespondentInvitation extends Mailable {

    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @param string $uuid
     */
    public function __construct(string $uuid)
    {
        $this->url = route('invitation.land', ['uuid' => $uuid]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('AfriSight Survey Invitation - We want your Opinion'))
            ->markdown('emails.dynata.invite');
    }
}
