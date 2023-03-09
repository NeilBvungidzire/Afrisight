<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationViaSocialMedia extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $name;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string|null $language
     */
    public function __construct(User $user, string $language = null)
    {
        $this->setData($user);

        if ( ! $language) {
            $language = app()->getLocale();
        }
        $this->locale($language);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->email, $this->name)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->subject(__('email/new_registration_social_media.subject'))
            ->markdown('emails.social-media.registration');
    }

    /**
     * Set view data.
     *
     * @param User $user
     */
    private function setData(User $user)
    {
        $this->email = $user->email;
        $this->name = __('AfriSight member');
    }
}
