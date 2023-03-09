<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\URL;

class ProjectInflowRegistration extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @param string $password
     */
    public function __construct(string $password)
    {
        $this->password = $password;

        $this->url = URL::route('login');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.project_inflow.registration')
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('email/inflow.registration.subject'));
    }
}
