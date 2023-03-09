<?php

namespace App\Observers;

use App\Jobs\SyncCintUser;
use App\User;

class UserObserver {

    /**
     * Handle the user "created" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function creating(User $user)
    {
        // Privacy policy consent
        $user->privacy_policy_consent = [
            'all' => true,
        ];

        if ($user->isDirty('email')) {
            $user->email = $this->cleanEmail($user->email);
        }
    }

    /**
     * Handle the user "updated" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function updated(User $user)
    {
        if ($user->isDirty('email_verified_at') && $user->email_verified_at && $person = $user->person) {
            SyncCintUser::dispatch($person)->delay(30);
        }

        if ($user->isDirty('email')) {
            $user->email = $this->cleanEmail($user->email);
        }
    }

    /**
     * Handle the user "deleted" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the user "restored" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the user "force deleted" event.
     *
     * @param User $user
     *
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }

    /**
     * @param string $email
     * @return string|null
     */
    private function cleanEmail(string $email): ?string
    {
        if (empty($email)) {
            return $email;
        }

        return str_replace(' ', '', strtolower($email));
    }
}
