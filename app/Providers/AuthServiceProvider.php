<?php

namespace App\Providers;

use App\Constants\Role;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider {

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();

        $this->setWebGates();

        $this->setApiGates();
    }

    /**
     * @return void
     */
    private function setWebGates() {
        Gate::before(static function ($user) {
            if ($user->role === Role::SUPER_ADMIN) {
                return true;
            }
        });

        Gate::define('administration', static function ($user) {
            return in_array($user->role, [Role::ADMIN, Role::TRANSLATOR, Role::MEMBERS_SUPPORT], true);
        });

        Gate::define('manage-translations', static function ($user) {
            return in_array($user->role, [Role::ADMIN, Role::TRANSLATOR], true);
        });

        Gate::define('manage-profiling', static function ($user) {
            return $user->role === Role::ADMIN;
        });

        Gate::define('manage-projects', static function ($user) {
            return $user->role === Role::ADMIN;
        });

        Gate::define('admin-projects', static function ($user) {
            return $user->role === Role::ADMIN;
        });

        Gate::define('heineken-project', static function ($user) {
            return $user->role === Role::ADMIN;
        });

        Gate::define('referral-management', static function ($user) {
            return in_array($user->role, [Role::ADMIN, Role::MEMBERS_SUPPORT], true);
        });

        Gate::define('reward-management', static function ($user) {
            return in_array($user->role, [Role::ADMIN, Role::MEMBERS_SUPPORT], true);
        });

        Gate::define('account-admin', static function ($user) {
            return $user->role === Role::ADMIN;
        });
    }

    private function setApiGates() {
        Gate::before(static function ($user) {
            if ($user->role === Role::SUPER_ADMIN) {
                return true;
            }
        });

        Gate::define('manage-own-data', static function ($user) {
            return $user->role === Role::ADMIN;
        });

        Gate::define('survey-redirect', static function ($user) {
            return $user->role === Role::MARKETPLACE;
        });
    }
}
