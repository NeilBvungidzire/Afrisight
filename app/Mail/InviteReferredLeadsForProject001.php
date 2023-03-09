<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteReferredLeadsForProject001 extends Mailable {

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
        $this->url = route('invitation.start', ['uuid' => $uuid]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Urgent Reminder: AfriSight Survey Invitation - We want your Opinion')
            ->markdown('emails.project001.referred-leads');
    }
}
