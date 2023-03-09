<?php

namespace App\Jobs;

use App\Referral;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RecountReferrals implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $referralId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $referralId)
    {
        $this->referralId = $referralId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var Referral $referral */
        $referral = Referral::find($this->referralId);
        if ( ! $referral) {
            return;
        }

        $referral->recountReferrals()->save();
    }
}
