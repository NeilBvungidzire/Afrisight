<?php

namespace App\Listeners;

use App\Jobs\SyncWithAudienceProfileService;
use Illuminate\Support\Carbon;

class LogSuccessfulLogin {

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event) {
        SyncWithAudienceProfileService::dispatch(
            $event->user->person_id,
            ['last_active' => (new Carbon())->format('Y-m-d')]
        )->delay(now()->addSecond());
    }
}
