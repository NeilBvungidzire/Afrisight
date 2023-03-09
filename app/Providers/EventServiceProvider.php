<?php

namespace App\Providers;

use App\Observers\PersonObserver;
use App\Observers\UserObserver;
use App\Person;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider {

    /**
     * The event listener mappings for the application.
     * @var array
     */
    protected $listen = [
        Registered::class              => [
            SendEmailVerificationNotification::class,
        ],
        'Illuminate\Auth\Events\Login' => [
            'App\Listeners\LogSuccessfulLogin',
        ],
    ];

    /**
     * Register any events for your application.
     * @return void
     */
    public function boot() {
        parent::boot();

        $this->registerObservers();
    }

    private function registerObservers() {
        Person::observe(PersonObserver::class);
        User::observe(UserObserver::class);
    }
}
