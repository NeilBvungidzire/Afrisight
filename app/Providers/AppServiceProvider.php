<?php

namespace App\Providers;

use App\MemberProfilingAnswer;
use App\Observers\MemberProfilingAnswerObserver;
use App\Observers\TransactionObserver;
use App\Transaction;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @param UrlGenerator $urlGenerator
     */
    public function boot(UrlGenerator $urlGenerator)
    {
        if (App::environment('production')) {
            $urlGenerator->forceScheme('https');
        }

        VerifyEmail::$toMailCallback = function ($notifiable, $verificationUrl) {
            return (new MailMessage)
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->replyTo(config('mail.from.address'), config('mail.from.name'))
                ->subject(__('email/new_registration.subject'))
                ->greeting(__('email/new_registration.greeting', ['name' => $notifiable->name]))
                ->line(__('email/new_registration.line-1'))
                ->action(__('email/new_registration.cta'), $verificationUrl)
                ->line(__('email/new_registration.line-2'));
        };

        $this->bootObservers();

        header('Service-Worker-Allowed', '/');
    }

    private function bootObservers()
    {
        Transaction::observe(TransactionObserver::class);
        MemberProfilingAnswer::observe(MemberProfilingAnswerObserver::class);
    }
}
