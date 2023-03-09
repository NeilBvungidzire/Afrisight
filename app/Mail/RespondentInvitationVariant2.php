<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RespondentInvitationVariant2 extends Mailable {

    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $url;

    /**
     * @var int
     */
    public $loi;

    /**
     * @var float
     */
    public $incentive;

    /**
     * @var bool
     */
    private $reminder;

    /**
     * Create a new message instance.
     *
     * @param string $uuid
     * @param int $loi
     * @param float $incentive
     * @param string|null $language
     * @param bool $reminder
     */
    public function __construct(string $uuid, array $incentive, string $language = null, bool $reminder = false)
    {
        $this->url = route('invitation.land', ['uuid' => $uuid]);
        $this->incentive = $incentive;
        $this->reminder = $reminder;

        if ($language) {
            $this->locale($language);
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = __('email/survey_invite_variant_1.initial.subject');
        if ($this->reminder) {
            $subject = __('email/survey_invite_variant_1.reminder.subject');
        }

        return $this
            ->subject($subject)
            ->markdown('emails.survey.invite_variant_2');
    }
}
