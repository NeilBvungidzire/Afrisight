<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PayoutOptionNotice extends Mailable implements ShouldQueue {

    use Queueable, SerializesModels;

    /**
     * @var string
     */
    public $updateMobileNumberLink;

    /**
     * @var string
     */
    public $profilingLink;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->updateMobileNumberLink = route('profile.basic-info.edit');
        $this->profilingLink = route('profiling');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.other.payout-option-notice')
            ->subject('AfriSight Rewards: Redeem your rewards now');
    }
}
