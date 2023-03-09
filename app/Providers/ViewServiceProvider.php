<?php

namespace App\Providers;

use App\Http\View\Composers\ProfileComposer;
use App\Libraries\Project\ProjectUtils;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider {

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer([
            'profile.basic-info.show',
            'profile.basic-info.edit',
            'profile.survey-opportunities.index',
            'profile.security.index',
            'profile.delete-account.show',
            'profile.rewards.index',

            // Payout v2
            'profile.payout-v2.index',
            'profile.payout-v2.flutterwave-bank-account.start',
            'profile.payout-v2.reloadly-mobile-top-up.start',
            'profile.payout-v2.reloadly-mobile-top-up.operator',
            'profile.payout-v2.reloadly-mobile-top-up.plan',
            'profile.payout-v2.cint-paypal.start',

            // Bank account
            'profile.payout-v2.bank-account.index',
            'profile.payout-v2.bank-account.bank-branch',

            'profile.security.email-reset',
        ], ProfileComposer::class);

        $this->projectViewComposer();
    }

    private function projectViewComposer()
    {
        View::composer([
            'admin.projects.dashboard',
            'admin.projects.layout',
        ], static function (\Illuminate\View\View $view) {
            $partners = ProjectUtils::getPartnersProjects();

            $view->with('partners', $partners);
        });
    }
}
