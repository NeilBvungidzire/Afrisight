<?php

return [
    'back_to_payout_cta'     => 'Back to payout',
    'country_not_set'        => 'You need to set your country to be able to access a payout option.',
    'intro'                  => 'We support different payout methods. Depending on their availability in your country you will find those available for you below. You can request payout in case you have reached the minimum amount. You can find the minimal amount per payment method beneath.',
    'method'                 =>
        [
            'alternative'   =>
                [
                    'additional_note' =>
                        [
                            1 => 'Unfortunately, at this moment no on-demand payout method is available in your country. But, we are looking for possibilities so you can request the payout yourself when you want and the way fits you best.',
                        ],
                    'intro'           => 'We will send you the payment ourself after you reach the minimum of :amount :currency on your balance.',
                    'short_name'      => 'Alternative payout method',
                ],
            'bank_account'  =>
                [
                    'add_bank_account'           => 'Add a bank account',
                    'calculate_local_amount_cta' => 'Calculate :local_currency amount',
                    'fail_getting_bank_account'  => 'The chosen bank account could not be found. Please check your bank account and adjust as needed.',
                    'form'                       =>
                        [
                            'amount_to_redeem'        =>
                                [
                                    'info'             => 'Minimum you can redeem is :minimum_amount USD and maximum :maximum_amount USD at this moment. Make sure the amount you\'re going to request is equal to or between these ranges.',
                                    'label'            => 'Amount to redeem',
                                    'not_reached_info' => 'You have not yet reached the minimum amount to use this payout option. The minimum amount you need to have on your balance is :amount :currency.',
                                ],
                            'currency_amount'         =>
                                [
                                    'label' => ':currency amount',
                                ],
                            'footnote'                => 'By clicking on below ":cta_label" button your request to redeem the amount will be sent out and if everything is OK you will receive this on your bank account very soon. Please, take into account it will take a few days the selected bank will process this transaction and you receive it on your account. Especially when you request during the weekend. The status of your request you can follow on the payout page.',
                            'local_amount_pay_out'    =>
                                [
                                    'info'  => 'This is the amount you will receive on your account.',
                                    'label' => 'Amount on your account',
                                ],
                            'local_amount_to_redeem'  =>
                                [
                                    'info' => 'This is the amount you will receive in your bank account.',
                                ],
                            'transfer_fee'            =>
                                [
                                    'info_1' => ':local_amount :local_currency is the fee the bank charges to transfer the money to your account.',
                                    'info_2' => 'But, because we appreciate your efforts, we want to meet you on this part by compensating :compensation_amount :local_currency of the total fee amount of :total_fee_amount :local_currency. This amount is most times fixed. So, to get the most out of your reward we advise you to redeem less frequent, but the maximum amount you have available on your account, instead of small amounts.',
                                    'info_3' => 'This amount is most times fixed. So, to get the most out of your reward we advise you to redeem less frequent, but the maximum amount you have available on your account, instead of small amounts.',
                                    'label'  => 'Transfer fee',
                                ],
                            'your_bank_account_field' =>
                                [
                                    'info'        => 'Bank account you want to transfer your requested amount to.',
                                    'label'       => 'Bank account',
                                    'placeholder' => 'Choose your bank account',
                                ],
                        ],
                    'intro'                      => 'With this option, you can collect your rewards by transferring to your bank account.',
                    'local_amount_header'        => 'Local amounts',
                    'local_calculator'           =>
                        [
                            'intro' => 'Calculate the :local_currency amount you will receive from :base_currency amount you would like to transfer from your balance. This is just to calculate the local amount and won\'t do the actual transfer.',
                            'title' => ':base_currency to :local_currency calculator',
                        ],
                    'long_name'                  => 'Transfer to your bank account',
                    'manage_bank_account'        => 'Manage my bank accounts',
                    'page_1_intro'               => 'Set the USD amount you want to redeem from your collected rewards balance. In case you want to know the :local_currency amount, please set the USD amount and Click on ":cta_label". You then will get an overview of the :local_currency amount you will receive and the costs. You can then decide if this works for you and redeem your rewards.',
                    'request_payout'             =>
                        [
                            'title' => 'Request payout',
                        ],
                    'set_max_cta'                => 'Set max',
                    'short_name'                 => 'Bank account',
                ],
            'cint_paypal'   =>
                [
                    'intro'              => 'With this option, you can collect your rewards by transferring to your PayPal account.',
                    'long_name'          => 'Transfer to your PayPal account',
                    'page_1_intro'       => 'You can claim :amount :currency from your collected rewards balance via PayPal. After your payout request, you will receive an email with further instructions.',
                    'short_name'         => 'PayPal',
                    'successful_request' => 'Your request was sent successfully. Please check your email for further instructions.',
                ],
            'general'       =>
                [
                    'cancel_request_payout_cta'        => 'Cancel',
                    'failed_request'                   => 'Could not process your payout request. Please contact us for help.',
                    'inactive_reason'                  => 'This option is not available at this moment. This can have different reason(s), for example, country regulations, but we will try to make this option available as soon as possible. Please consider other payout options or come back another time to check this option\'s availability.',
                    'minimum_not_reached'              => 'You have not yet reached the minimum amount of :amount :currency for this option.',
                    'next_step_request_payout_cta'     => 'Next step',
                    'option_available'                 => 'You have reached the minimum of :minimum_amount :currency available for this payout option. You can make use of this payout option whenever you want. The maximum amount you can collect via this option is :maximum_amount :currency.',
                    'payout_transaction_narration'     => 'AfriSight payout',
                    'previous_step_request_payout_cta' => 'Previous step',
                    'request_amount_crossing_limits'   => 'The amount you want to redeem must be between the minimum and maximum.',
                    'request_payout_cta'               => 'Request payout',
                    'start_cta'                        => 'Check possibilities',
                    'successful_request'               => 'Your request was sent successfully. Please take into consideration, sometimes, it will take some time before you receive the payout.',
                    'unavailable_button'               => 'Unavailable at this moment',
                    'usage_requirement'                => 'You can use this payout option whenever you want. The only requirement is you have to have :min_amount :currency or more in your balance.',
                ],
            'mobile_money'  =>
                [
                    'long_name'  => 'Transfer to your mobile wallet',
                    'short_name' => 'Mobile money',
                ],
            'mobile_top_up' =>
                [
                    'form'                                   =>
                        [
                            'amount'       =>
                                [
                                    'label' => ':currency amount',
                                ],
                            'operator'     =>
                                [
                                    'label' => 'Mobile operator',
                                ],
                            'phone_number' =>
                                [
                                    'label' => 'Phone number',
                                ],
                            'plan'         =>
                                [
                                    'label'       => 'Plan',
                                    'placeholder' => 'Choose a plan',
                                ],
                        ],
                    'intro'                                  => 'With this option, you can collect your rewards by top-up your mobile phone number.',
                    'intro_extra'                            => 'Get paid out as mobile top-up by entering the mobile number you want to top-up, select the mobile operator for the given mobile number, and last but not least, set the amount you want to redeem as mobile top-up from your balance.',
                    'long_name'                              => 'Top-up your prepaid mobile credit',
                    'mobile_operator_not_found'              => 'Could not find the mobile operator for phone number.',
                    'mobile_operator_threshold_not_achieved' => 'Unfortunately, you can\'t redeem your reward via this operator with your current balance amount. The minimum amount you can redeem as a mobile top-up via this mobile operator (:operator_threshold) is lower than the maximum amount you have on your balance (:account_threshold).',
                    'operator_found'                         =>
                        [
                            'instructions' => 'Is this the mobile operator for the phone number you entered? If yes, click "Next step". Otherwise, click "Previous step" and double-check the phone number you have entered.',
                        ],
                    'operator_not_found'                     =>
                        [
                            'instructions' => 'Please double-check the phone number you have set. You can go back to the previous step and change the phone number. If still not found, this could be because the mobile operator for this phone number is not yet supported.',
                            'message'      => 'Could not find the mobile operator for the given phone number!',
                        ],
                    'page_1'                                 =>
                        [
                            'instructions' => 'Put the mobile phone number you want to top-up. Please make sure to also include the country code, for example, +3451293400343.',
                            'title'        => 'Phone number to top-up',
                        ],
                    'page_2'                                 =>
                        [
                            'instructions' => 'Check the mobile operator for phone number :phone_number.',
                            'title'        => 'Check mobile operator',
                        ],
                    'page_3'                                 =>
                        [
                            'instructions' =>
                                [
                                    'fixed' => 'Select one of the available mobile top-up plans.',
                                    'range' => 'Enter the amount to redeem as a mobile top-up.',
                                ],
                            'title'        => 'Set top-up plan',
                        ],
                    'phone_number_not_found'                 => 'Could not find the phone number.',
                    'short_name'                             => 'Mobile top-up',
                ],
        ],
    'payout_requests'        =>
        [
            'empty_list' => 'No payout requests found.',
            'intro'      => 'Here you will find an overview of your payout requests and the status.',
            'list'       =>
                [
                    'amount' =>
                        [
                            'label' => 'Amount (:currency)',
                        ],
                    'date'   =>
                        [
                            'label' => 'Date',
                        ],
                    'method' =>
                        [
                            'label' => 'Method',
                            'value' =>
                                [
                                    'other' => 'Other method',
                                ],
                        ],
                    'status' =>
                        [
                            'label' => 'Status',
                            'value' =>
                                [
                                    'approved' => 'Approved and should be reflected on your balance',
                                    'other'    => 'Being processed',
                                    'pending'  => 'Requested and being processed',
                                    'rejected' => 'Rejected',
                                ],
                        ],
                ],
            'title'      => 'Payout requests',
        ],
    'person_bank_account'    =>
        [
            'available_bank_accounts' =>
                [
                    'intro' => 'List of your bank accounts you can use to send your requested payout. No bank account set yet or you want to add another one? Look below for adding one.',
                    'title' => 'Available bank accounts',
                ],
            'bank_branch'             =>
                [
                    'form'  =>
                        [
                            'branch_code' => 'bank branch code',
                            'branch'      =>
                                [
                                    'label' => 'Select bank branch',
                                ],
                            'submit_cta'  => 'Set bank branch',
                        ],
                    'intro' => 'For your country and chosen bank, it\'s required to select the bank branch. Please select this from the below list.',
                ],
            'delete_cta'              => 'Delete',
            'edit_add_bank_account'   =>
                [
                    'form'            =>
                        [
                            'account_number' =>
                                [
                                    'label' => 'Account number',
                                ],
                            'add_cta'        => 'Add',
                            'bank'           =>
                                [
                                    'label'       => 'Bank',
                                    'placeholder' => 'Choose your bank',
                                ],
                            'cancel_cta'     => 'Cancel',
                            'edit_cta'       => 'Update',
                        ],
                    'intro'           => 'Fill in the form with the required bank account details. Make sure this information is correct to avoid the transfer being dismissed, which can result in not getting paid out.',
                    'title'           => ':type_label bank account',
                    'type_add_label'  => 'Add new',
                    'type_edit_label' => 'Edit',
                ],
            'edit_cta'                => 'Edit',
            'title'                   => 'My bank accounts',
        ],
    'request_fail_try_later' => 'Something went wrong. Please try later on again.',
];
