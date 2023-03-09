<?php

namespace App\Mail;

use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Contact extends Mailable {

    use Queueable, SerializesModels;

    public $data;

    public $additionalData = null;

    /**
     * Create a new message instance.
     *
     * @param array       $data
     * @param string|null $language
     */
    public function __construct(array $data, string $language = null)
    {
        $this->data = $data;

        $this->setAdditionalData($data['email_address']);

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
    public function build(): Contact
    {
        return $this
            ->to(config('mail.from.address'), config('mail.from.name'))
            ->replyTo($this->data['email_address'], $this->data['name'])
            ->subject("[{$this->data['subject_code']}] - {$this->data['subject']}")
            ->markdown('emails.contact.contact');
    }

    /**
     * @param string $email
     *
     * @return void
     */
    private function setAdditionalData(string $email): void
    {
        $this->additionalData = [
            'country_code'      => __('Not available'),
            'date_registration' => __('Not available'),
            'mobile_number'     => __('Not available'),
            'has_cint_account'  => __('No'),
        ];

        if ( ! $person = Person::with('country', 'cintUser')->where('email', $email)->first()) {
            return;
        }

        $this->additionalData['date_registration'] = $person->created_at;
        $this->additionalData['mobile_number'] = $person->mobile_number;

        if ($person->country) {
            $this->additionalData['country_code'] = $person->country->iso_alpha_2;
        }

        if ($person->cintUser && $person->cintUser->cint_id) {
            $this->additionalData['has_cint_account'] = __('Yes');
        }
    }
}
