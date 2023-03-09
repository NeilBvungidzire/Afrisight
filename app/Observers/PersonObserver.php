<?php

namespace App\Observers;

use App\Jobs\DeleteCintUser;
use App\Jobs\SyncCintUser;
use App\Jobs\SyncWithAudienceProfileService;
use App\Person;
use App\Services\AudienceProfileService\AudienceProfileService;
use Illuminate\Support\Carbon;

class PersonObserver {

    /**
     * Handle the person "created" event.
     *
     * @param  Person  $person
     *
     * @return void
     */
    public function creating(Person $person) {
        // Set attributes value
        $person->language_code = strtoupper(app()->getLocale());
        $person->currency_code = strtoupper(config('app.currency'));

        if ($person->isDirty('email')) {
            $person->email = $this->cleanEmail($person->email);
        }
    }

    /**
     * @param  Person  $person
     */
    public function created(Person $person) {
        // Sync with Cint
        SyncCintUser::dispatch($person)->delay(60);

        $this->syncDatapoints($person);
    }

    /**
     * Handle the person "updated" event.
     *
     * @param  Person  $person
     *
     * @return void
     */
    public function updated(Person $person) {
        if ($person->isDirty('email')) {
            $person->email = $this->cleanEmail($person->email);
        }

        $listAttributesOnAct = [
            'email',
            'country_id',
            'gender_code',
            'date_of_birth',
            'first_name',
            'last_name',
        ];

        foreach ($listAttributesOnAct as $attribute) {
            if ($person->isDirty($attribute) && $person->$attribute) {
                SyncCintUser::dispatch($person)->delay(5);
                break;
            }
        }

        $this->syncDatapoints($person);
    }

    /**
     * Handle the person "deleted" event.
     *
     * @param  Person  $person
     *
     * @return void
     */
    public function deleted(Person $person) {
        DeleteCintUser::dispatch($person)->delay(5);

        SyncWithAudienceProfileService::dispatch($person->id, [], 'delete')->delay(now()->addSecond());
    }

    /**
     * Handle the person "restored" event.
     *
     * @param  Person  $person
     *
     * @return void
     */
    public function restored(Person $person) {
        //
    }

    /**
     * Handle the person "force deleted" event.
     *
     * @param  Person  $person
     *
     * @return void
     */
    public function forceDeleted(Person $person) {
        SyncWithAudienceProfileService::dispatch($person->id, [], 'delete')->delay(now()->addSecond());
    }

    /**
     * @param  string  $email
     * @return string|null
     */
    private function cleanEmail(string $email): ?string {
        if (empty($email)) {
            return $email;
        }

        return str_replace(' ', '', strtolower($email));
    }

    private function syncDatapoints(Person $person) {
        if ($person->country_code) {
            $datapoints = [
                "country_code" => $person->country_code,
                "last_active"  => (new Carbon())->format('Y-m-d'),
            ];

            if ($person->date_of_birth) {
                $datapoints['date_of_birth'] = (new Carbon($person->date_of_birth))->format('Y-m-d');
            }

            if ($person->gender_code) {
                $datapoints['gender_code'] = ['m' => 'm', 'w' => 'f', 'u' => null][$person->gender_code];
            }

            SyncWithAudienceProfileService::dispatch($person->id, $datapoints)->delay(now()->addSecond());
        }
    }
}
