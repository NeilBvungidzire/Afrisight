<?php

namespace App\Mail;

use App\MedicalPractitioner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SurveyInviteMedicalPractitioner extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $surveyLink;

    /**
     * Create a new message instance.
     *
     * @param string $surveyLink
     */
    public function __construct(string $surveyLink)
    {
        $this->surveyLink = $surveyLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.medical-practitioner.survey-invite')
            ->subject('Knowledge, Attitude and Practice (KAP) Survey for healthcare Professionals in Uganda');
    }
}
