<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group([
    'prefix'     => LaravelLocalization::setLocale(),
    'middleware' => [
        'localeSessionRedirect',
        'localeCookieRedirect',
    ],
], static function () {

    /**
     * Authentication
     */
    Auth::routes(['verify' => true]);
    // Override default auth routes.
    Route::get('/join-now', 'Auth\RegisterController@showRegistrationForm')->name('register');
    Route::post('/join-now', 'Auth\RegisterController@register');

    /**
     * Static pages
     */
    Route::get('/', 'PagesController@home')->name('home');
    Route::get('/about', 'PagesController@about')->name('about');
    Route::get('/rewards', 'PagesController@rewards')->name('rewards');
    Route::get('/contacts', 'PagesController@contacts')->name('contacts');
    Route::get('/privacy-policy', 'PagesController@privacyPolicy')->name('privacy-policy');
    Route::get('/terms-and-conditions', 'PagesController@termsAndConditions')->name('terms-and-conditions');

    /**
     * Contact
     */
    Route::get('/contacts/form', 'ContactFormController@form')->name('contacts.form');
    Route::post('/contacts/form', 'ContactFormController@submit')->name('contacts.submit');

    /**
     * Profile pages
     */
    Route::prefix('/profile')
        ->middleware(['auth', 'verified'])
        ->group(function () {

            // Basic info
            Route::get('/', 'Profile\BasicInfoController@show')
                ->name('profile.basic-info.show');
            Route::get('/edit', 'Profile\BasicInfoController@edit')
                ->name('profile.basic-info.edit');
            Route::put('/edit', 'Profile\BasicInfoController@update')
                ->name('profile.basic-info.update');

            // Survey opportunities
            Route::get('/surveys', 'Profile\SurveyOpportunitiesController@index')
                ->name('profile.surveys');

            // Security block
            Route::group([
                'prefix' => 'security',
            ], static function () {

                // Security overview
                Route::get('/', 'Profile\SecurityPageController')
                    ->name('profile.security');

                // Change password
                Route::get('password', 'Auth\ChangePasswordController@edit')
                    ->name('profile.password.edit');
                Route::put('password', 'Auth\ChangePasswordController@update')
                    ->name('profile.password.update');

                // Delete account
                Route::get('delete-account', 'Profile\DeleteAccountController@show')
                    ->name('profile.delete-account.show');
                Route::delete('delete-account', 'Profile\DeleteAccountController@delete')
                    ->name('profile.delete-account.delete');

                Route::get('change-email', 'Profile\ChangeEmailController@edit')
                    ->name('profile.change-email.edit');
                Route::post('change-email', 'Profile\ChangeEmailController@change')
                    ->name('profile.change-email.change');
                Route::get('change-email/verify', 'Profile\ChangeEmailController@verify')
                    ->name('profile.change-email.verify');

            });

            // Rewards overview
            Route::get('/rewards', 'Profile\RewardsController')
                ->name('profile.rewards');

            // Bank account
            Route::group([
                'prefix' => 'bank-accounts',
            ], function () {

                Route::get('/', 'PayoutV2\BankAccountController@index')
                    ->name('profile.bank_account');
                Route::post('/', 'PayoutV2\BankAccountController@save')
                    ->name('profile.bank_account');
                Route::delete('/', 'PayoutV2\BankAccountController@delete')
                    ->name('profile.bank_account');

                // Bank branch
                Route::get('branch/{bankAccountId}', 'PayoutV2\BankAccountController@selectBranch')
                    ->name('profile.bank_account.branch');
                Route::post('branch/{bankAccountId}', 'PayoutV2\BankAccountController@saveBranch')
                    ->name('profile.bank_account.branch');
            });

            // Payout V2
            Route::group([
                'prefix' => '/payout',
            ], function () {

                Route::get('/', 'PayoutV2\PayoutOptionsOverviewController')
                    ->name('profile.payout-v2.options');

                Route::group([
                    'middleware' => ['signed'],
                ], function () {

                    // Bank account
                    Route::group([
                        'prefix' => 'bank-account',
                    ], function () {

                        Route::get('/', 'PayoutV2\BankAccountPayoutController@start')
                            ->name('profile.payout-v2.bank-account.start');
                        Route::post('/', 'PayoutV2\BankAccountPayoutController@request')
                            ->name('profile.payout-v2.bank-account.request');
                        Route::post('calculate', 'PayoutV2\BankAccountPayoutController@calculateLocalAmount')
                            ->name('profile.payout-v2.bank-account.local-amount');
                    });

                    // Mobile top-up
                    Route::group([
                        'prefix' => 'mobile-top-up',
                    ], function () {

                        Route::get('/', 'PayoutV2\MobileTopUpPayoutController@start')
                            ->name('profile.payout-v2.mobile-top-up.start');
                        Route::post('handle-phone-number', 'PayoutV2\MobileTopUpPayoutController@handlePhoneNumber')
                            ->name('profile.payout-v2.mobile-top-up.handle-phone-number');
                        Route::get('check-operator', 'PayoutV2\MobileTopUpPayoutController@getOperator')
                            ->name('profile.payout-v2.mobile-top-up.get-operator');
                        Route::post('handle-operator', 'PayoutV2\MobileTopUpPayoutController@handleOperator')
                            ->name('profile.payout-v2.mobile-top-up.handle-operator');
                        Route::get('get-plan', 'PayoutV2\MobileTopUpPayoutController@getPlan')
                            ->name('profile.payout-v2.mobile-top-up.get-plan');
                        Route::post('request', 'PayoutV2\MobileTopUpPayoutController@request')
                            ->name('profile.payout-v2.mobile-top-up.request');
                    });

                    // PayPal
                    Route::group([
                        'prefix' => 'paypal',
                    ], function () {

                        Route::get('/', 'PayoutV2\PayPalPayoutController@start')
                            ->name('profile.payout-v2.paypal.start');
                        Route::post('/', 'PayoutV2\PayPalPayoutController@request')
                            ->name('profile.payout-v2.paypal.request');
                    });
                });
            });
        });

    /**
     * Profiling
     */
    if (config('profiling.enabled')) {
        Route::prefix('/profiling')
            ->middleware(['auth', 'verified'])
            ->group(static function () {
                Route::get('/', 'MemberProfilingController@index')->name('profiling');
                Route::post('/', 'MemberProfilingController@store');
            });
    }

    // Survey enrollment
    Route::group([
        'prefix' => '/enrollment',
    ], static function () {

        // Background qualification.
        Route::get('{uuid}/check', 'AudienceTargetingController@checkQualification')
            ->name('enrollment.background_qualification_check');

        // Predefined qualification params
        Route::get('{uuid}', 'AudienceTargetingController@questionnaire')
            ->name('enrollment.questionnaire');
        Route::post('{uuid}', 'AudienceTargetingController@processAnswer')
            ->name('enrollment.questionnaire');

        // Customized qualification.
        Route::get('/{uuid}/pdpwbmdh8v', 'SurveyScreening\MarketCube002Controller@entry')
            ->name('survey.market_cube_002');
        Route::post('/{uuid}/pdpwbmdh8v', 'SurveyScreening\MarketCube002Controller@handleAnswers')
            ->name('survey.market_cube_002');
    });

    /**
     * Inviting respondents to specific projects, without need for registration (will only need email). After passing
     * email, they will be redirected to the survey.
     */
    Route::get('inflow/{projectId}', 'ProjectInflowController@land')
        ->name('inflow');
    Route::post('inflow/{projectId}', 'ProjectInflowController@handleRespondent')
        ->name('inflow.start');

    /**
     * Facebook oauth
     */
    if (config('services.facebook.enabled')) {
        Route::get('/login/facebook', 'Social\FacebookController@login')
            ->middleware('guest')
            ->name('facebook.login');

        Route::get('/register/facebook', 'Social\FacebookController@register')
            ->middleware('guest')
            ->name('facebook.register');

        Route::get('/link/facebook', 'Social\FacebookController@link')
            ->middleware('auth')
            ->name('facebook.link');

        Route::delete('/unlink/facebook', 'Social\FacebookController@unlink')
            ->middleware('auth')
            ->name('facebook.unlink');
    }

    /**
     * Google oauth
     */
    if (config('services.google.enabled')) {
        Route::get('/login/google', 'Social\GoogleController@login')
            ->middleware('guest')
            ->name('google.login');

        Route::get('/register/google', 'Social\GoogleController@register')
            ->middleware('guest')
            ->name('google.register');

        Route::get('/link/google', 'Social\GoogleController@link')
            ->middleware('auth')
            ->name('google.link');

        Route::delete('/unlink/google', 'Social\GoogleController@unlink')
            ->middleware('auth')
            ->name('google.unlink');
    }

    /**
     * Survey end result page for the participant
     */
    Route::get('end-result/{cipher}', 'SurveyRedirectsController@feedback')
        ->name('survey-redirect.feedback');
    Route::get('capi/end-result', 'CAPI\SurveyRedirectsController@feedback')
        ->name('survey-redirect.capi.feedback');
});

/**
 * Admin resources
 */
Route::group([
    'prefix'     => '/admin',
    'middleware' => ['auth', 'verified'],
], static function () {

    Route::get('/', 'Admin\AdminDashboardController')->name('admin.dashboard');
    Route::resource('translation', 'Admin\TranslationController');
    Route::resource('profiling', 'Admin\ProfilingController');

    // Project
    Route::get('projects', 'Admin\ProjectsDashboardController@index')
        ->name('admin.projects.index');

    // Sample Provider
    Route::group([
        'prefix' => 'sample-supplier',
    ], static function () {

        Route::get('/', 'Admin\SampleProviderController@index')
            ->name('admin.sample-provider.index');

        Route::get('/create', 'Admin\SampleProviderController@create')
            ->name('admin.sample-provider.create');
        Route::post('/', 'Admin\SampleProviderController@store')
            ->name('admin.sample-provider.store');

        Route::get('/{id}/edit', 'Admin\SampleProviderController@edit')
            ->name('admin.sample-provider.edit');
        Route::put('/{id}', 'Admin\SampleProviderController@update')
            ->name('admin.sample-provider.update');

        Route::get('/{id}/dashboard', 'Admin\SampleProviderController@dashboard')
            ->name('admin.sample-provider.dashboard');
    });

    Route::group([
        'prefix' => 'project/{projectCode}',
    ], function () {

        Route::group([
            'prefix' => 'targets',
        ], static function () {

            // Overview
            Route::get('/', 'Admin\TargetTracksController@index')
                ->name('admin.projects.target_track.index');

            // Quota management
            Route::get('edit-quotas', 'Admin\TargetTracksController@editQuotas')
                ->name('admin.projects.target_track.edit_quotas');
            Route::post('update-quotas', 'Admin\TargetTracksController@updateQuotas')
                ->name('admin.projects.target_track.update_quotas');

            Route::post('update-complete-limit', 'Admin\TargetTracksController@updateCompleteLimit')
                ->name('admin.projects.target_track.update_complete_limit');

            // Target management
            Route::get('generate', 'Admin\TargetTracksController@generateTargets')
                ->name('admin.projects.target_track.generate');

            // Count management
            Route::get('recount-completes', 'Admin\TargetTracksController@recountCompletes')
                ->name('admin.projects.target_track.recount_completes');

        });

        // Respondents overview
        Route::get('respondents', 'Admin\ProjectController@showRespondents')
            ->name('admin.projects.respondents');

        Route::get('audience-selection', 'Admin\AudienceEngagementController@select')
            ->name('admin.projects.audience_selection');
        Route::get('audience-reselection', 'Admin\AudienceEngagementController@reselect')
            ->name('admin.projects.audience_reselection');

        // Incentive Packages
        Route::get('incentive-packages', 'Admin\IncentivePackageController@index')
            ->name('admin.projects.incentive-packages');
        Route::get('incentive-packages/create', 'Admin\IncentivePackageController@create')
            ->name('admin.projects.incentive-packages.create');
        Route::post('incentive-packages/create', 'Admin\IncentivePackageController@store')
            ->name('admin.projects.incentive-packages.store');
        Route::get('incentive-packages/allocate/{channel}/{id}', 'Admin\IncentivePackageController@allocate')
            ->name('admin.projects.incentive-packages.allocate');

        // Invite respondents
        Route::get('invite/select', 'Admin\InviteController@selectAudience')
            ->name('admin.invite.select');
        Route::post('invite/send-sms', 'Admin\InviteController@sendSms')
            ->name('admin.invite.send_sms');

        Route::get('approve-rewards', 'Admin\ProjectController@handleParticipants')
            ->name('admin.projects.approve_rewards');

        Route::get('switch-status', 'Admin\ProjectController@switchStatus')
            ->name('admin.projects.switch_status');

        // Manage participants
        Route::group([
            'prefix' => 'manage-participants',
        ], function () {

            // Filtering
            Route::get('filter', 'Admin\ManageProjectParticipantsController@filter')
                ->name('admin.projects.manage_participants.filter');
            Route::post('filter', 'Admin\ManageProjectParticipantsController@setFilter')
                ->name('admin.projects.manage_participants.filter');

            // Select action
            Route::get('select', 'Admin\ManageProjectParticipantsController@select')
                ->name('admin.projects.manage_participants.select');
            Route::post('select', 'Admin\ManageProjectParticipantsController@chooseAction')
                ->name('admin.projects.manage_participants.select');
        });
    });

    Route::group([
        'prefix' => 'account-quality',
    ], static function () {

        Route::get('/', 'Admin\AccountQualityManagement\AccountQualityController@index')
            ->name('admin.account-quality.index');

        // Email
        Route::group([
            'prefix' => 'email-blacklist',
        ], static function () {

            Route::get('/', 'Admin\AccountQualityManagement\EmailBlacklistController@index')
                ->name('admin.account-quality.email-blacklist.index');
            Route::get('search', 'Admin\AccountQualityManagement\EmailBlacklistController@search')
                ->name('admin.account-quality.email-blacklist.search');
            Route::post('search', 'Admin\AccountQualityManagement\EmailBlacklistController@submitSearchForm')
                ->name('admin.account-quality.email-blacklist.search');

            Route::get('ban', 'Admin\AccountQualityManagement\EmailBlacklistController@createBlacklist')
                ->name('admin.account-quality.email-blacklist.ban');
        });

        // Bank Account
        Route::group([
            'prefix' => 'bank-account-blacklist',
        ], static function () {

            Route::get('/', 'Admin\AccountQualityManagement\BankAccountBlacklistController@index')
                ->name('admin.account-quality.bank-account-blacklist.index');
            Route::get('found-cases', 'Admin\AccountQualityManagement\BankAccountBlacklistController@findPossibleCases')
                ->name('admin.account-quality.bank-account-blacklist.find-possible-cases');
            Route::get('search', 'Admin\AccountQualityManagement\BankAccountBlacklistController@search')
                ->name('admin.account-quality.bank-account-blacklist.search');
            Route::post('search', 'Admin\AccountQualityManagement\BankAccountBlacklistController@filter')
                ->name('admin.account-quality.bank-account-blacklist.search');

            Route::get('ban', 'Admin\AccountQualityManagement\BankAccountBlacklistController@createBlacklist')
                ->name('admin.account-quality.bank-account-blacklist.ban');
        });

        // Mobile Number
        Route::group([
            'prefix' => 'mobile-number-blacklist',
        ], static function () {

            Route::get('/', 'Admin\AccountQualityManagement\MobileNumberBlacklistController@index')
                ->name('admin.account-quality.mobile-number-blacklist.index');
            Route::get('search', 'Admin\AccountQualityManagement\MobileNumberBlacklistController@search')
                ->name('admin.account-quality.mobile-number-blacklist.search');
            Route::post('search', 'Admin\AccountQualityManagement\MobileNumberBlacklistController@submitSearchForm')
                ->name('admin.account-quality.mobile-number-blacklist.search');

            Route::get('ban', 'Admin\AccountQualityManagement\MobileNumberBlacklistController@createBlacklist')
                ->name('admin.account-quality.mobile-number-blacklist.ban');
        });
    });

    // Referral management
    Route::group([
        'prefix' => 'referral-management',
    ], static function () {

        Route::group([
            'prefix' => 'referral',
        ], static function () {

            Route::get('/', 'Admin\ReferralManagementController@index')
                ->name('admin.referral_management.overview');
            Route::post('/', 'Admin\ReferralManagementController@filter')
                ->name('admin.referral_management.overview');

            Route::get('recount', 'Admin\ReferralManagementController@recountAllReferral')
                ->name('admin.referral_management.recount_all_referral');

            Route::get('create', 'Admin\ReferralManagementController@createReferral')
                ->name('admin.referral_management.create_referral');
            Route::post('create', 'Admin\ReferralManagementController@storeReferral')
                ->name('admin.referral_management.store_referral');

            Route::get('{id}/edit', 'Admin\ReferralManagementController@editReferral')
                ->name('admin.referral_management.edit_referral');
            Route::put('{id}/update', 'Admin\ReferralManagementController@updateReferral')
                ->name('admin.referral_management.update_referral');

            Route::get('{id}', 'Admin\ReferralManagementController@viewReferral')
                ->name('admin.referral_management.view_referral');

            Route::get('{id}/recount', 'Admin\ReferralManagementController@recountReferral')
                ->name('admin.referral_management.recount_referral');

            Route::get('{id}/transactions', 'Admin\ReferralManagementController@handleReferralTransactions')
                ->name('admin.referral_management.handle_referral_transactions');
            Route::get('{id}/transactions/{respondentId}',
                'Admin\ReferralManagementController@generateReferralRewardTransaction')
                ->name('admin.referral_management.create_referral_transactions');

            // Engagement
            Route::get('{id}/engagement/{channel}', 'Admin\ReferralEngagementController@draftMessage')
                ->name('admin.referral_management.engagement.draft');
            Route::post('{id}/engagement/{channel}', 'Admin\ReferralEngagementController@sendMessage')
                ->name('admin.referral_management.engagement.send');
        });

        // Referrer
        Route::group([
            'prefix' => 'referrer',
        ], static function () {

            Route::get('/', 'Admin\ReferralManagementController@indexReferrer')
                ->name('admin.referral_management.overview_referrer');
            Route::post('/', 'Admin\ReferralManagementController@filterReferrer')
                ->name('admin.referral_management.overview_referrer');

            Route::get('create', 'Admin\ReferralManagementController@createReferrer')
                ->name('admin.referral_management.create_referrer');
            Route::post('create', 'Admin\ReferralManagementController@storeReferrer')
                ->name('admin.referral_management.store_referrer');

            Route::get('edit/{id}', 'Admin\ReferralManagementController@editReferrer')
                ->name('admin.referral_management.edit_referrer');
            Route::put('edit/{id}', 'Admin\ReferralManagementController@updateReferrer')
                ->name('admin.referral_management.update_referrer');

            Route::get('view/{id}', 'Admin\ReferralManagementController@viewReferrer')
                ->name('admin.referral_management.view_referrer');
        });
    });

    // Reward management
    Route::group([
        'prefix' => 'reward-management',
    ], static function () {

        // Dashboard
        Route::get('/', 'Admin\RewardManagement\DashboardController')
            ->name('admin.reward_management.dashboard');

        // Granting
        Route::get('granting', 'Admin\RewardManagement\GrantingController@index')
            ->name('admin.reward_management.granting');
        Route::post('granting/filter', 'Admin\RewardManagement\GrantingController@filterTransactions')
            ->name('admin.reward_management.granting.filter');

        Route::get('granting/check/{id}', 'Admin\RewardManagement\GrantingController@editTransaction')
            ->name('admin.reward_management.granting.edit');
        Route::post('granting/check/{id}', 'Admin\RewardManagement\GrantingController@updateTransaction')
            ->name('admin.reward_management.granting.update');

        // Payout
        Route::get('payout', 'Admin\RewardManagement\PayoutController@index')
            ->name('admin.reward_management.payout');
        Route::post('payout/filter', 'Admin\RewardManagement\PayoutController@filterTransactions')
            ->name('admin.reward_management.payout.filter');
        Route::get('payout/check/{id}', 'Admin\RewardManagement\PayoutController@editTransaction')
            ->name('admin.reward_management.payout.edit');
        Route::post('payout/check/{id}', 'Admin\RewardManagement\PayoutController@updateTransaction')
            ->name('admin.reward_management.payout.update');

        Route::group([
            'prefix' => 'member-account',
        ], static function () {

            // Person account
            Route::get('/', 'Admin\RewardManagement\PersonAccountController@overview')
                ->name('admin.reward_management.member-account');
            Route::post('/', 'Admin\RewardManagement\PersonAccountController@filter')
                ->name('admin.reward_management.member-account.filter');

            // Person account params
            Route::get('{id}/params', 'Admin\RewardManagement\AccountParamsController@edit')
                ->name('admin.reward_management.member-account.params');
            Route::post('{id}/params', 'Admin\RewardManagement\AccountParamsController@update')
                ->name('admin.reward_management.member-account.params');
        });

        // Cint transactions
        Route::group([
            'prefix' => 'cint',
        ], static function () {

            Route::get('transactions', 'Admin\RewardManagement\CintTransactionsController@index')
                ->name('admin.cint.transactions');
            Route::post('transactions/filter', 'Admin\RewardManagement\CintTransactionsController@filterTransactions')
                ->name('admin.cint.transactions.filter');

            Route::get('transaction/check/{id}', 'Admin\RewardManagement\CintTransactionsController@editTransaction')
                ->name('admin.cint.transactions.edit');
            Route::post('transaction/check/{id}', 'Admin\RewardManagement\CintTransactionsController@updateTransaction')
                ->name('admin.cint.transactions.update');
        });

        Route::group([
            'prefix' => 'transactions',
        ], static function () {

            // Import transactions
            Route::get('import', 'Admin\RewardManagement\ImportTransactionsController@importTransactions')
                ->name('admin.transactions.import');
            Route::post('import', 'Admin\RewardManagement\ImportTransactionsController@readTransactions')
                ->name('admin.transactions.import');
//            Route::get('undo', 'Admin\RewardManagement\ImportTransactionsController@undo');

        });

        Route::group([
            'prefix' => 'account-balance',
        ], static function () {

            Route::get('/', 'Admin\RewardManagement\AccountBalanceController@index')
                ->name('admin.reward_management.balance');
            Route::post('/filter', 'Admin\RewardManagement\AccountBalanceController@filter')
                ->name('admin.reward_management.balance.filter');

            Route::get('/{personId}/sync-cint-balance',
                'Admin\RewardManagement\AccountBalanceController@syncCintBalance')
                ->name('admin.reward_management.sync_cint_balance');

            Route::get('/{personId}', 'Admin\RewardManagement\AccountBalanceController@view')
                ->name('admin.account_balance.view');
        });
    });

    // Heineken project
    Route::get('heineken', 'HeinekenController@overview');
    Route::get('heineken/data/{survey}', 'HeinekenController@data');
    Route::get('heineken/table/{survey}', 'HeinekenController@table');

    Route::group([
        'prefix' => 'ipsos/instant-labs',
    ], static function () {

        Route::get('/', 'Admin\InstantLabsController@index')
            ->name('admin.instant_labs.dashboard');

        // Import
        Route::get('import-data/set', 'Admin\InstantLabsController@setData')
            ->name('admin.instant_labs.import_data.set');
        Route::get('import-data/import', 'Admin\InstantLabsController@importData')
            ->name('admin.instant_labs.import_data.import');
        Route::post('import-data/read', 'Admin\InstantLabsController@readData')
            ->name('admin.instant_labs.import_data.read');
        Route::get('import-data/check', 'Admin\InstantLabsController@checkData')
            ->name('admin.instant_labs.import_data.check');

        // Engagement
        Route::get('plan/find', 'Admin\InstantLabsController@findNotPlannedRespondents')
            ->name('admin.instant_labs.plan.find');
        Route::get('plan/queue', 'Admin\InstantLabsController@planRespondentsEngagements')
            ->name('admin.instant_labs.plan.queue');
    });
});

/**
 * Facebook oauth
 */
if (config('services.facebook.enabled')) {
    Route::get('/login/facebook/callback', 'Social\FacebookController@handleProviderCallback')
        ->name('facebook.callback');
}

/**
 * Google oauth
 */
if (config('services.google.enabled')) {
    Route::get('/login/google/callback', 'Social\GoogleController@handleProviderCallback')
        ->name('google.callback');
}

/**
 * Marketplace redirects
 */
Route::prefix('/marketplace/redirect/{marketplacePublicId}')
    ->middleware('marketplace.check')
    ->group(function () {
        Route::get('completed', 'Marketplace\RedirectsController@completed');
        Route::get('failed', 'Marketplace\RedirectsController@failed');
        Route::get('quota-reached', 'Marketplace\RedirectsController@quotaReached');
        Route::get('disqualified', 'Marketplace\RedirectsController@disqualified');
        Route::get('closed', 'Marketplace\RedirectsController@closed');
    });

/**
 * Respondent invitation landing page.
 */
Route::get('/invitation/{uuid}', 'RespondentInvitation@land')
    ->name('invitation.land');
Route::get('/invitation/{uuid}/start', 'RespondentInvitation@entry')
    ->name('invitation.entry');

/**
 * Survey Tool redirects
 */
Route::prefix('survey-redirect')->group(function () {
    Route::get('complete', 'SurveyRedirectsController@completed')->name('survey-redirect.complete');
    Route::get('screen-out', 'SurveyRedirectsController@screenOut')->name('survey-redirect.screen-out');
    Route::get('terminate', 'SurveyRedirectsController@terminated')->name('survey-redirect.terminate');
    Route::get('quota-full', 'SurveyRedirectsController@quotaReached')->name('survey-redirect.quota-full');
    Route::get('closed', 'SurveyRedirectsController@closed')->name('survey-redirect.closed');
});

// Intermediary routes
Route::group([
    'prefix' => '/invite-entry',
], static function () {

    Route::get('/', 'IntermediaryController@start')->name('intermediary.start');
    Route::get('/{uuid}/{status}', 'IntermediaryController@finish')->name('intermediary.finish');
});

// Referral (the referrer) management
Route::get('{locale}/referrer/{id}', 'ReferrerController@overview')
    ->middleware('signed')
    ->name('referral_management.referrer.overview');

Route::group([
    'prefix' => 'capi',
], static function () {

    // Admin
    Route::group([
        'prefix' => 'admin',
    ], static function () {

        Route::get('dashboard/{projectCode}', 'CAPI\ProgressOverviewController@project');

        Route::get('import', 'CAPI\SurveyEntryLinksController@edit')
            ->name('capi.admin.import');
        Route::post('import', 'CAPI\SurveyEntryLinksController@create')
            ->name('capi.admin.import');

    });

    // Fieldwork
    Route::group([
        'prefix' => 'fieldwork',
    ], static function () {

        Route::get('entry', 'CAPI\FieldworkController@entry')
            ->name('capi.fieldwork.entry');

        Route::get('start', 'CAPI\FieldworkController@start')
            ->name('capi.fieldwork.start');

    });

    // Redirect links
    Route::group([
        'prefix' => 'survey-redirect',
    ], static function () {

        Route::get('complete', 'CAPI\SurveyRedirectsController@completed')
            ->name('survey-redirect.capi.complete');
        Route::get('screen-out', 'CAPI\SurveyRedirectsController@screenOut')
            ->name('survey-redirect.capi.screen-out');
        Route::get('terminate', 'CAPI\SurveyRedirectsController@terminated')
            ->name('survey-redirect.capi.terminate');
        Route::get('quota-full', 'CAPI\SurveyRedirectsController@quotaReached')
            ->name('survey-redirect.capi.quota-full');
        Route::get('closed', 'CAPI\SurveyRedirectsController@closed')
            ->name('survey-redirect.capi.closed');

    });

});
