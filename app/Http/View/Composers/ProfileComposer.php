<?php

namespace App\Http\View\Composers;

use App\Person;
use App\Services\AccountService\AccountService;
use App\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileComposer {

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Person
     */
    protected $person;

    /**
     * ProfileComposer constructor.
     */
    public function __construct() {
        if ( ! $this->user = Auth::user()) {
            return;
        }

        try {
            $this->person = cache()->remember('PERSON_BY_USER_ID_' . $this->user->id, now()->addHour(), function () {
                return $this->user->person;
            });
        } catch (Exception $e) {
            return;
        }
    }

    public function compose(View $view): void {
        $minimalProfileDataIsAvailable = $this->person->minimal_profile_data_is_available;
        $view->with('minimalProfileDataIsAvailable', $minimalProfileDataIsAvailable);

        // Set name of the current user.
        $name = $this->person->full_name ?? $this->user->name;
        $view->with('name', $name);

        $totalCalculatedRewardBalance = (new AccountService($this->person))->getBalance();
        $view->with('totalCalculatedRewardBalance', $totalCalculatedRewardBalance);

        // Check presence of mobile number.
        $mobileNumberSet = isset($this->person->mobile_number);
        $view->with('mobileNumberSet', $mobileNumberSet);
    }
}
