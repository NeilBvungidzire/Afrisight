<?php

return [
    'back_to_payout_cta'     => 'Retour au paiement',
    'country_not_set'        => 'Vous devez définir votre pays pour pouvoir accéder à une option de paiement.',
    'intro'                  => 'Nous prenons en charge différentes méthodes de paiement. En fonction de leur disponibilité dans votre pays, vous trouverez ci-dessous ceux disponibles pour vous. Vous pouvez demander un paiement si vous avez atteint le montant minimum. Vous trouverez ci-dessous le montant minimal par mode de paiement.',
    'method'                 =>
        [
            'alternative'   =>
                [
                    'additional_note' =>
                        [
                            1 => 'Malheureusement, pour le moment, aucune méthode de paiement à la demande n\'est disponible dans votre pays. Mais nous recherchons des possibilités afin que vous puissiez demander le paiement vous-même quand vous le souhaitez et de la manière qui vous convient le mieux.',
                        ],
                    'intro'           => 'Nous vous enverrons le paiement nous-mêmes après avoir atteint le minimum de :amount :currency sur votre solde.',
                    'short_name'      => 'Méthode de paiement alternative',
                ],
            'bank_account'  =>
                [
                    'add_bank_account'           => 'Ajouter un compte bancaire',
                    'calculate_local_amount_cta' => 'Calculer le montant :local_currency',
                    'fail_getting_bank_account'  => 'Le compte bancaire choisi est introuvable. Veuillez vérifier votre compte bancaire et ajuster si nécessaire.',
                    'form'                       =>
                        [
                            'amount_to_redeem'        =>
                                [
                                    'info'             => 'Le minimum que vous pouvez échanger est de :minimum_amount USD et le maximum de :maximum_amount USD pour le moment. Assurez-vous que le montant que vous allez demander est égal ou compris entre ces fourchettes.',
                                    'label'            => 'Montant à échanger',
                                    'not_reached_info' => 'Vous n\'avez pas encore atteint le montant minimum pour utiliser cette option de paiement. Le montant minimum que vous devez avoir sur votre solde est de :amount :currency.',
                                ],
                            'currency_amount'         =>
                                [
                                    'label' => 'Montant :currency',
                                ],
                            'footnote'                => 'En cliquant sur le bouton ci-dessous ":cta_label", votre demande de remboursement du montant sera envoyée et si tout va bien, vous la recevrez très bientôt sur votre compte bancaire. Veuillez prendre en compte que cela prendra quelques jours, la banque sélectionnée traitera cette transaction et vous la recevrez sur votre compte. Surtout lorsque vous demandez pendant le week-end. Vous pouvez suivre le statut de votre demande sur la page de paiement.',
                            'local_amount_pay_out'    =>
                                [
                                    'info'  => 'C\'est le montant que vous recevrez sur votre compte.',
                                    'label' => 'Montant sur votre compte',
                                ],
                            'local_amount_to_redeem'  =>
                                [
                                    'info' => 'C\'est le montant que vous recevrez sur votre compte bancaire.',
                                ],
                            'transfer_fee'            =>
                                [
                                    'info_1' => ':local_amount :local_currency correspond aux frais facturés par la banque pour transférer l\'argent sur votre compte.',
                                    'info_2' => 'Mais, parce que nous apprécions vos efforts, nous souhaitons vous rencontrer sur cette partie en compensant :compensation_amount :local_currency du montant total des frais de :total_fee_amount :local_currency. Ce montant est la plupart du temps fixe. Ainsi, pour tirer le meilleur parti de votre récompense, nous vous conseillons d\'utiliser moins fréquemment, mais le montant maximum dont vous disposez sur votre compte, au lieu de petits montants.',
                                    'info_3' => 'Ce montant est la plupart du temps fixe. Ainsi, pour tirer le meilleur parti de votre récompense, nous vous conseillons d\'utiliser moins fréquemment, mais le montant maximum dont vous disposez sur votre compte, au lieu de petits montants.',
                                    'label'  => 'Frais de transfert',
                                ],
                            'your_bank_account_field' =>
                                [
                                    'info'        => 'Compte bancaire sur lequel vous souhaitez transférer le montant demandé.',
                                    'label'       => 'Compte bancaire',
                                    'placeholder' => 'Choisissez votre compte bancaire',
                                ],
                        ],
                    'intro'                      => 'Avec cette option, vous pouvez récupérer vos récompenses en les transférant sur votre compte bancaire.',
                    'local_amount_header'        => 'Montants locaux',
                    'local_calculator'           =>
                        [
                            'intro' => 'Calculez le montant :local_currency que vous recevrez du montant :base_currency que vous souhaitez transférer de votre solde. Ceci est juste pour calculer le montant local et ne fera pas le transfert réel.',
                            'title' => 'Calculatrice :base_currency à :local_currency',
                        ],
                    'long_name'                  => 'Virement sur votre compte bancaire',
                    'manage_bank_account'        => 'Gérer mes comptes bancaires',
                    'page_1_intro'               => 'Définissez le montant en USD que vous souhaitez utiliser à partir du solde de vos récompenses collectées. Si vous souhaitez connaître le montant :local_currency, veuillez définir le montant en USD et cliquez sur ":cta_label". Vous aurez alors un aperçu du montant :local_currency que vous recevrez et des coûts. Vous pouvez ensuite décider si cela fonctionne pour vous et échanger vos récompenses.',
                    'request_payout'             =>
                        [
                            'title' => 'Demande Paiement',
                        ],
                    'set_max_cta'                => 'Définir max',
                    'short_name'                 => 'Compte bancaire',
                ],
            'cint_paypal'   =>
                [
                    'intro'              => 'Avec cette option, vous pouvez collecter vos récompenses en les transférant sur votre compte PayPal.',
                    'long_name'          => 'Transférer sur votre compte PayPal',
                    'page_1_intro'       => 'Vous pouvez réclamer :amount :currency à partir du solde de vos récompenses collectées via PayPal. Après votre demande de paiement, vous recevrez un e-mail avec des instructions supplémentaires.',
                    'short_name'         => 'PayPal',
                    'successful_request' => 'Votre demande a été envoyée avec succès. Veuillez vérifier votre courrier électronique pour obtenir des instructions supplémentaires.',
                ],
            'general'       =>
                [
                    'cancel_request_payout_cta'        => 'Annuler',
                    'failed_request'                   => 'Impossible de traiter votre demande de paiement. Veuillez nous contacter pour obtenir de l\'aide.',
                    'inactive_reason'                  => 'Cette option n\'est pas disponible pour le moment. Cela peut avoir différentes raisons, par exemple, les réglementations nationales, mais nous essaierons de rendre cette option disponible dès que possible. Veuillez envisager d\'autres options de paiement ou revenez une autre fois pour vérifier la disponibilité de cette option.',
                    'minimum_not_reached'              => 'Vous n\'avez pas encore atteint le montant minimum de :amount :currency pour cette option.',
                    'next_step_request_payout_cta'     => 'L\'étape suivante',
                    'option_available'                 => 'Vous avez atteint le minimum de :minimum_amount :currency disponible pour cette option de paiement. Vous pouvez utiliser cette option de paiement quand vous le souhaitez. Le montant maximum que vous pouvez collecter via cette option est de :maximum_amount :currency.',
                    'payout_transaction_narration'     => 'Paiement AfriSight',
                    'previous_step_request_payout_cta' => 'Étape précédente',
                    'request_amount_crossing_limits'   => 'Le montant que vous souhaitez utiliser doit être compris entre le minimum et le maximum.',
                    'request_payout_cta'               => 'Demande Paiement',
                    'start_cta'                        => 'Vérifier les possibilités',
                    'successful_request'               => 'Votre demande a été envoyée avec succès. Veuillez prendre en considération, parfois, cela prendra un certain temps avant de recevoir le paiement.',
                    'unavailable_button'               => 'Indisponible pour le moment',
                    'usage_requirement'                => 'Vous pouvez utiliser cette option de paiement quand vous le souhaitez. La seule exigence est que vous devez avoir :min_amount :currency ou plus dans votre solde.',
                ],
            'mobile_money'  =>
                [
                    'long_name'  => 'Transfert vers votre portefeuille mobile',
                    'short_name' => 'Argent mobile',
                ],
            'mobile_top_up' =>
                [
                    'form'                                   =>
                        [
                            'amount'       =>
                                [
                                    'label' => 'Montant :currency',
                                ],
                            'operator'     =>
                                [
                                    'label' => 'Opérateur mobile',
                                ],
                            'phone_number' =>
                                [
                                    'label' => 'Numéro de téléphone',
                                ],
                            'plan'         =>
                                [
                                    'label'       => 'Plan',
                                    'placeholder' => 'Choisissez un plan',
                                ],
                        ],
                    'intro'                                  => 'Avec cette option, vous pouvez récupérer vos récompenses en rechargeant votre numéro de téléphone mobile.',
                    'intro_extra'                            => 'Soyez payé sous forme de recharge mobile en saisissant le numéro de mobile que vous souhaitez recharger, sélectionnez l\'opérateur mobile pour le numéro de mobile donné et, enfin et surtout, définissez le montant que vous souhaitez utiliser comme recharge mobile à partir de votre équilibre.',
                    'long_name'                              => 'Rechargez votre crédit mobile prépayé',
                    'mobile_operator_not_found'              => 'Impossible de trouver l\'opérateur mobile pour le numéro de téléphone.',
                    'mobile_operator_threshold_not_achieved' => 'Malheureusement, vous ne pouvez pas échanger votre récompense via cet opérateur avec le montant de votre solde actuel. Le montant minimum que vous pouvez utiliser comme recharge mobile via cet opérateur mobile (:operator_threshold) est inférieur au montant maximum que vous avez sur votre solde (:account_threshold).',
                    'operator_found'                         =>
                        [
                            'instructions' => 'S\'agit-il de l\'opérateur mobile du numéro de téléphone que vous avez entré? Si oui, cliquez sur "Étape suivante". Sinon, cliquez sur "Étape précédente" et revérifiez le numéro de téléphone que vous avez entré.',
                        ],
                    'operator_not_found'                     =>
                        [
                            'instructions' => 'Veuillez vérifier le numéro de téléphone que vous avez défini. Vous pouvez revenir à l\'étape précédente et modifier le numéro de téléphone. S\'il n\'est toujours pas trouvé, cela peut être dû au fait que l\'opérateur mobile de ce numéro de téléphone n\'est pas encore pris en charge.',
                            'message'      => 'Impossible de trouver l\'opérateur mobile pour le numéro de téléphone indiqué!',
                        ],
                    'page_1'                                 =>
                        [
                            'instructions' => 'Mettez le numéro de téléphone mobile que vous souhaitez recharger. Assurez-vous d\'inclure également le code du pays, par exemple +3451293400343.',
                            'title'        => 'Numéro de téléphone à recharger',
                        ],
                    'page_2'                                 =>
                        [
                            'instructions' => 'Vérifiez l\'opérateur mobile pour le numéro de téléphone :phone_number.',
                            'title'        => 'Vérifier l\'opérateur mobile',
                        ],
                    'page_3'                                 =>
                        [
                            'instructions' =>
                                [
                                    'fixed' => 'Sélectionnez l\'un des plans de recharge mobile disponibles.',
                                    'range' => 'Saisissez le montant à utiliser comme recharge mobile.',
                                ],
                            'title'        => 'Définir un plan de recharge',
                        ],
                    'phone_number_not_found'                 => 'Impossible de trouver le numéro de téléphone.',
                    'short_name'                             => 'Recharge mobile',
                ],
        ],
    'payout_requests'        =>
        [
            'empty_list' => 'Aucune demande de paiement trouvée.',
            'intro'      => 'Vous trouverez ici un aperçu de vos demandes de paiement et de leur statut.',
            'list'       =>
                [
                    'amount' =>
                        [
                            'label' => 'Montant (:currency)',
                        ],
                    'date'   =>
                        [
                            'label' => 'Date',
                        ],
                    'method' =>
                        [
                            'label' => 'Méthode',
                            'value' =>
                                [
                                    'other' => 'Autre méthode',
                                ],
                        ],
                    'status' =>
                        [
                            'label' => 'Statut',
                            'value' =>
                                [
                                    'approved' => 'Approuvé et devrait être reflété sur votre solde',
                                    'other'    => 'Être en cours de traitement',
                                    'pending'  => 'Demandé et en cours de traitement',
                                    'rejected' => 'Rejeté',
                                ],
                        ],
                ],
            'title'      => 'Demandes de paiement',
        ],
    'person_bank_account'    =>
        [
            'available_bank_accounts' =>
                [
                    'intro' => 'Liste de vos comptes bancaires que vous pouvez utiliser pour envoyer le paiement demandé. Pas encore de compte bancaire défini ou vous souhaitez en ajouter un autre? Regardez ci-dessous pour en ajouter un.',
                    'title' => 'Comptes bancaires disponibles',
                ],
            'bank_branch'             =>
                [
                    'form'  =>
                        [
                            'branch_code' => 'code agence bancaire',
                            'branch'      =>
                                [
                                    'label' => 'Sélectionnez une agence bancaire',
                                ],
                            'submit_cta'  => 'Définir une succursale bancaire',
                        ],
                    'intro' => 'Pour votre pays et la banque choisie, il est nécessaire de sélectionner l\'agence bancaire. Veuillez le sélectionner dans la liste ci-dessous.',
                ],
            'delete_cta'              => 'Effacer',
            'edit_add_bank_account'   =>
                [
                    'form'            =>
                        [
                            'account_number' =>
                                [
                                    'label' => 'Numéro de compte',
                                ],
                            'add_cta'        => 'Ajouter',
                            'bank'           =>
                                [
                                    'label'       => 'Banque',
                                    'placeholder' => 'Choisissez votre banque',
                                ],
                            'cancel_cta'     => 'Annuler',
                            'edit_cta'       => 'Mise à jour',
                        ],
                    'intro'           => 'Remplissez le formulaire avec les coordonnées bancaires requises. Assurez-vous que ces informations sont correctes pour éviter que le transfert ne soit rejeté, ce qui peut entraîner le non-paiement.',
                    'title'           => 'Compte bancaire :type_label',
                    'type_add_label'  => 'Ajouter nouveau',
                    'type_edit_label' => 'Éditer',
                ],
            'edit_cta'                => 'Éditer',
            'title'                   => 'Mes comptes bancaires',
        ],
    'request_fail_try_later' => 'Un problème est survenu. Veuillez réessayer plus tard.',
];
