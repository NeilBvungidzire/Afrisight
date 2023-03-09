<?php

return [
    'money-balance' => ':amount USD',
    'point-balance' => ':amount points',
    'sub_pages'     =>
        [
            'delete_account'       =>
                [
                    'cancel_text'  => 'Cancel',
                    'confirm_text' => 'I\'m sure',
                    'delete_link'  => 'Delete account',
                    'heading'      => 'Delete account',
                ],
            'edit_general_info'    =>
                [
                    'cancel_text' => 'Cancel',
                    'fields'      =>
                        [
                            'country'       =>
                                [
                                    'default_value' => 'Choose the country you live',
                                    'label'         => 'Country based',
                                ],
                            'date_of_birth' =>
                                [
                                    'info'  => 'Format day-month-year, for example 24-03-1985.',
                                    'label' => 'Date of birth',
                                ],
                            'first_name'    =>
                                [
                                    'label' => 'First name',
                                ],
                            'gender'        =>
                                [
                                    'default_value' => 'Choose your gender',
                                    'label'         => 'Gender',
                                ],
                            'last_name'     =>
                                [
                                    'label' => 'Last name',
                                ],
                            'mobile_number' =>
                                [
                                    'info'        => 'Needed for payout, mobile top-up or SMS invitation. Please fill in with country code. For example, +123936438354.',
                                    'label'       => 'Mobile number (with country code)',
                                    'placeholder' => 'Example, +123936438354',
                                ],
                        ],
                    'heading'     => 'Edit general info',
                    'save_text'   => 'Update',
                ],
            'email_change'         =>
                [
                    'alert'   =>
                        [
                            'only_once_in_30_days'      => 'You can only change this once in 30 days!',
                            'successful_change'         => 'Your email address is been changed to :new_email successfully.',
                            'successful_request'        => 'We have sent you an email to your new email address. Please make sure to open this email and follow the instructions within one hour!',
                            'unsuccessful_verification' => 'Could not change your email address. Please make sure to follow the instruction!',
                        ],
                    'form'    =>
                        [
                            'current_password' =>
                                [
                                    'info_text'   => 'For security reasons, you have to verify your identity by entering your password.',
                                    'placeholder' => 'Current password',
                                ],
                            'new_email'        =>
                                [
                                    'placeholder' => 'Your new email address',
                                ],
                            'submit'           =>
                                [
                                    'label' => 'Request Change',
                                ],
                        ],
                    'heading' => 'Change email',
                ],
            'general_info'         =>
                [
                    'cta_text' => 'Edit',
                    'heading'  => 'General info',
                ],
            'linked_accounts'      =>
                [
                    'facebook'   => 'Facebook',
                    'google'     => 'Google',
                    'heading'    => 'Linked accounts',
                    'link'       => 'Link',
                    'not_linked' => 'Not linked',
                    'unlink'     => 'Unlink',
                ],
            'login_details'        =>
                [
                    'email'    =>
                        [
                            'info_text' => 'You are allowed to change your email only every 30 days and it seems like you have already done this in the past 30 days.',
                            'label'     => 'Email',
                        ],
                    'heading'  => 'Login details',
                    'password' =>
                        [
                            'change_password' => 'Change',
                            'label'           => 'Password',
                            'set_password'    => 'Set password',
                        ],
                ],
            'payout'               =>
                [
                    'able'                   =>
                        [
                            'cta_text' => 'Request payout of :amount USD',
                            'line_1'   => 'You can request payout via :method. Your balance has reached the minimum of :amount USD.',
                        ],
                    'heading'                => 'Payout',
                    'intro'                  =>
                        [
                            'line_1' => 'You can request payout in case you have reached the minimal amount. You can find the minimal amount per payment method beneath.',
                            'line_2' => 'After your payout request you will receive an email with further instructions.',
                        ],
                    'payout_request_failed'  => 'Could not request payout. Please contact us for help.',
                    'payout_request_succeed' => 'Your request was send successfully. Please check your email for further instructions.',
                    'unable'                 =>
                        [
                            'line_1' => 'You can\'t yet request payout via :method. Your balance has not yet reached the minimum of :threshold USD. Your balance at this moment is :amount USD.',
                        ],
                    'wrong_payout_method'    => 'Wrong payment method chosen.',
                ],
            'profiling'            =>
                [
                    'heading'     => 'Profiling',
                    'submit_text' => 'Save profiling data',
                ],
            'rewards'              =>
                [
                    'heading'                 => 'Rewards',
                    'intro'                   => 'Only the rewards which are not granted or are not yet approved, so not yet on your balance, you will find here. The rewards which are already granted and on your balance you will not see here.',
                    'list'                    =>
                        [
                            'amount' => 'Amount (USD)',
                            'date'   => 'Date',
                            'status' => 'Status',
                            'title'  => 'Rewards overview',
                            'type'   => 'Type',
                        ],
                    'no_reward_yet'           => 'No reward yet. To enhance your chance for rewards, make sure to fill in your :link as much and correct as possible.',
                    'no_reward_yet_link_text' => 'profiling questions',
                    'notes'                   =>
                        [
                            'statuses' =>
                                [
                                    'approved' => 'Approved = Reward approved and should be reflected on your balance.',
                                    'denied'   => 'Denied = Reward will not be granted. This could have a lot of reasons, but one of those reasons could be because of not taking the exercise seriously.',
                                    'pending'  => 'Pending = Reward pending for the project to wrap-up.',
                                    'title'    => 'Statuses',
                                ],
                            'title'    => 'Notes',
                        ],
                    'status'                  =>
                        [
                            'approved' => 'Approved',
                            'default'  => 'Rewarded',
                            'denied'   => 'Denied',
                            'pending'  => 'Pending',
                        ],
                    'type'                    =>
                        [
                            'default'  => 'Participation',
                            'referral' => 'Referral',
                            'survey'   => 'Survey',
                        ],
                ],
            'security'             =>
                [
                    'heading' => 'Security',
                ],
            'survey_opportunities' =>
                [
                    'heading'       => 'Surveys',
                    'notification'  =>
                        [
                            'line_1' => 'You have not complete your profile. Please do this so we can give you surveys.',
                            'line_2' => 'If you get this warning and you already have completed your profile, please contact us.',
                        ],
                    'opportunities' =>
                        [
                            'action_start'     => 'Start survey',
                            'column_incentive' => 'Reward',
                            'column_loi'       => 'Length (minutes)',
                            'heading'          => 'Available surveys',
                            'no_survey'        => 'No survey available at this moment.',
                        ],
                ],
        ],
];
