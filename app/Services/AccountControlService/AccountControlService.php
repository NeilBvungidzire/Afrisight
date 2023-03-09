<?php

namespace App\Services\AccountControlService;

class AccountControlService {

    public static function byBankAccount(): ByBankAccount {
        return new ByBankAccount();
    }

    public static function byMobileNumber(): ByMobileNumber {
        return new ByMobileNumber();
    }
}