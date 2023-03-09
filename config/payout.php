<?php

use App\Constants\Currency;
use App\Services\AccountService\Constants\PayoutMethod;
use App\Services\AccountService\Constants\Provider;

return [

    'options_configs' => [
        // Nigeria
        'NG' => [
            PayoutMethod::BANK_ACCOUNT  => [
                'active'            => true,
                'currency'          => Currency::NGN,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::NGN,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => false,
                'currency'          => Currency::NGN,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Ghana
        'GH' => [
            PayoutMethod::BANK_ACCOUNT  => [
                'active'            => true,
                'currency'          => Currency::GHS,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::GHS,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::GHS,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // South Africa
        'ZA' => [
            PayoutMethod::BANK_ACCOUNT  => [
                'active'            => true,
                'currency'          => Currency::ZAR,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::ZAR,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::ZAR,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
            PayoutMethod::PAYPAL        => [
                'active'            => true,
                'currency'          => Currency::USD,
                'provider'          => Provider::CINT,
                'minimal_threshold' => 4,
                'maximum_amount'    => 10,
                'fee_compensation'  => 0,
                'params'            => [
                    'payment_method_id' => 353,
                ],
            ],
        ],
        // Kenya
        'KE' => [
            PayoutMethod::BANK_ACCOUNT  => [
                'active'            => true,
                'currency'          => Currency::KES,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::KES,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::KES,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
            PayoutMethod::PAYPAL        => [
                'active'            => true,
                'currency'          => Currency::USD,
                'provider'          => Provider::CINT,
                'minimal_threshold' => 4,
                'maximum_amount'    => 10,
                'fee_compensation'  => 0,
                'params'            => [
                    'payment_method_id' => 1399,
                ],
            ],
        ],
        // Uganda
        'UG' => [
            PayoutMethod::BANK_ACCOUNT => [
                'active'            => true,
                'currency'          => Currency::UGX,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::UGX,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::UGX,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Ethiopia
        'ET' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::ETB,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
            //            PayoutMethod::MOBILE_MONEY => [
            //                'currency'          => Currency::ETB,
            //                'active'            => true,
            //                'provider'          => Provider::BEL_CASH,
            //                'minimal_threshold' => 3,
            //                'maximum_amount'    => 15,
            //                'fee_compensation'  => 1,
            //                'params'            => [
            //                    'debit_currency' => Currency::USD,
            //                ],
            //            ],
        ],
        // Tanzania
        'TZ' => [
            PayoutMethod::BANK_ACCOUNT  => [
                'active'            => true,
                'currency'          => Currency::TZS,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::TZS,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Mozambique
        'MZ' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::MZN,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Algeria
        'DZ' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::DZD,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Angola
        'AO' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::AOA,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 1,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Benin
        'BJ' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::XOF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Botswana
        'BW' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::BWP,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'BF' => [],
        'BI' => [],
        // Cameroon
        'CM' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::XAF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'CV' => [],
        'CF' => [],
        'TD' => [],
        'KM' => [],
        // Congo-Brazzaville
        'CG' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::XAF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Congo
        'CD' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::CDF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Côte d'Ivoire
        'CI' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::XOF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'DJ' => [],
        // Egypt
        'EG' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::EGP,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 2,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'GQ' => [],
        'ER' => [],
        'GA' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::XAF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Gambia
        'GM' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::GMD,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'GN' => [],
        'GW' => [],
        'LS' => [],
        // Liberia
        'LR' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::LRD,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'LY' => [],
        'MG' => [],
        'MW' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::MWK,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'ML' => [],
        'MR' => [],
        'MU' => [],
        // Morocco
        'MA' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::MAD,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Namibia
        'NA' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::NAD,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 2,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'NE' => [],
        // Rwanda
        'RW' => [
            PayoutMethod::BANK_ACCOUNT  => [
                'active'            => true,
                'currency'          => Currency::RWF,
                'provider'          => Provider::FLUTTERWAVE,
                'minimal_threshold' => 5,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0.5,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::RWF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Senegal
        'SN' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::XOF,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        'SC' => [],
        'SL' => [],
        'SO' => [],
        'SS' => [],
        'SD' => [],
        'SZ' => [],
        'TG' => [],
        'TN' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::TND,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Zambia
        'ZM' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::ZMW,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 2,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
        // Zimbabwe
        'ZW' => [
            PayoutMethod::MOBILE_TOP_UP => [
                'active'            => true,
                'currency'          => Currency::ZWD,
                'provider'          => Provider::RELOADLY,
                'minimal_threshold' => 3,
                'maximum_amount'    => 15,
                'fee_compensation'  => 0,
                'params'            => [
                    'debit_currency' => Currency::USD,
                ],
            ],
        ],
    ],

];
