<?php

namespace App\Http\Controllers\PayoutV2;

use App\Http\Controllers\Controller;
use App\Person;
use App\Services\AccountService\AccountService;
use App\Services\AccountService\Contracts\PayoutOptionContract;

class PayoutOptionUtilsController extends Controller {

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var AccountService
     */
    protected $accountService;

    /**
     * @todo replace the option availability checking logic to here, by passing the method as param. This removes repeated logic.
     */
    protected function initialize(): void
    {
        $this->person = authUser()->person;

        $this->accountService = new AccountService($this->person);
    }

    protected function checkAvailability(string $method): ?PayoutOptionContract
    {
        if ( ! authUser()->hasVerifiedEmail()) {
            return null;
        }

        if ( ! $this->accountService->requiredPersonDataAvailable()) {
            return null;
        }

        $payoutOptions = $this->accountService->getPayoutOptions($method);
        if (empty($payoutOptions) || ! isset($payoutOptions[$method]) || ! $payoutOptions[$method]->isActive()) {
            return null;
        }
        return $payoutOptions[$method];
    }
}
