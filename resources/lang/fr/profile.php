<?php

return [
    'money-balance' => ':amount USD',
    'point-balance' => ':amount points',
    'sub_pages'     =>
        [
            'delete_account'       =>
                [
                    'cancel_text'  => 'Annuler',
                    'confirm_text' => 'Je suis certain(e)',
                    'delete_link'  => 'Supprimer le compte',
                    'heading'      => 'Supprimer le compte',
                ],
            'edit_general_info'    =>
                [
                    'cancel_text' => 'Annuler',
                    'fields'      =>
                        [
                            'country'       =>
                                [
                                    'default_value' => 'Choisissez le pays dans lequel vous vivez',
                                    'label'         => 'Pays dans lequel vous vous trouvez',
                                ],
                            'date_of_birth' =>
                                [
                                    'info'  => 'Format jour-mois-année, par exemple 24-03-1985.',
                                    'label' => 'Date de naissance',
                                ],
                            'first_name'    =>
                                [
                                    'label' => 'Prénom',
                                ],
                            'gender'        =>
                                [
                                    'default_value' => 'Choisissez votre sexe',
                                    'label'         => 'Sexe',
                                ],
                            'last_name'     =>
                                [
                                    'label' => 'Nom',
                                ],
                            'mobile_number' =>
                                [
                                    'info'        => 'Nécessaire pour le paiement, le rechargement du téléphone portable ou l\'invitation par SMS. Veuillez indiquer le code du pays. Par exemple, +123936438354.',
                                    'label'       => 'Numéro de téléphone portable (avec l\'indicatif du pays)',
                                    'placeholder' => 'Par exemple, +123936438354',
                                ],
                        ],
                    'heading'     => 'Modifier les infos générales',
                    'save_text'   => 'Mise à jour',
                ],
            'email_change'         =>
                [
                    'alert'   =>
                        [
                            'only_once_in_30_days'      => 'Vous ne pouvez changer cela qu\'une fois tous les 30 jours!',
                            'successful_change'         => 'Votre adresse e-mail a été changée en :new_email avec succès.',
                            'successful_request'        => 'Nous vous avons envoyé un e-mail à votre nouvelle adresse e-mail. Veuillez vous assurer d\'ouvrir cet e-mail et de suivre les instructions dans l\'heure qui suit!',
                            'unsuccessful_verification' => 'Impossible de modifier votre adresse e-mail. Veuillez vous assurer de suivre les instructions!',
                        ],
                    'form'    =>
                        [
                            'current_password' =>
                                [
                                    'info_text'   => 'Pour des raisons de sécurité, vous devez vérifier votre identité en saisissant votre mot de passe.',
                                    'placeholder' => 'Mot de passe actuel',
                                ],
                            'new_email'        =>
                                [
                                    'placeholder' => 'Votre nouvelle adresse e-mail',
                                ],
                            'submit'           =>
                                [
                                    'label' => 'Demander un changement',
                                ],
                        ],
                    'heading' => 'Changer l\'e-mail',
                ],
            'general_info'         =>
                [
                    'cta_text' => 'Modifier',
                    'heading'  => 'Infos générales',
                ],
            'linked_accounts'      =>
                [
                    'facebook'   => 'Facebook',
                    'google'     => 'Google',
                    'heading'    => 'Comptes associés',
                    'link'       => 'Lien',
                    'not_linked' => 'Pas associé',
                    'unlink'     => 'Désassocier',
                ],
            'login_details'        =>
                [
                    'email'    =>
                        [
                            'info_text' => 'Vous n\'êtes autorisé à modifier votre e-mail que tous les 30 jours et il semble que vous l\'ayez déjà fait au cours des 30 derniers jours.',
                            'label'     => 'Courriel',
                        ],
                    'heading'  => 'Informations de connexion',
                    'password' =>
                        [
                            'change_password' => 'Modifier',
                            'label'           => 'Mot de passe',
                            'set_password'    => 'Définir le mot de passe',
                        ],
                ],
            'payout'               =>
                [
                    'able'                   =>
                        [
                            'cta_text' => 'Demander le paiement de :amount USD',
                            'line_1'   => 'Vous pouvez demander le paiement via :method. Votre solde a atteint le minimum de :amount USD.',
                        ],
                    'heading'                => 'Paiement',
                    'intro'                  =>
                        [
                            'line_1' => 'Vous pouvez demander le paiement si vous avez atteint le montant minimal. Vous trouverez ci-dessous le montant minimal par mode de paiement.',
                            'line_2' => 'Après votre demande de paiement, vous recevrez un courriel contenant des instructions supplémentaires.',
                        ],
                    'payout_request_failed'  => 'N\'a pas pu demander le paiement. Veuillez nous contacter pour obtenir de l\'aide.',
                    'payout_request_succeed' => 'Votre demande a été envoyée avec succès. Veuillez consulter votre courriel pour de plus amples instructions.',
                    'unable'                 =>
                        [
                            'line_1' => 'Vous ne pouvez pas demander le paiement via :method. Votre solde n\'a pas encore atteint le minimum de :threshold USD. Votre solde à ce moment est de :amount USD.',
                        ],
                    'wrong_payout_method'    => 'Mauvaise méthode de paiement choisie.',
                ],
            'profiling'            =>
                [
                    'heading'     => 'Profilage',
                    'submit_text' => 'Sauvegarder les données de profilage',
                ],
            'rewards'              =>
                [
                    'heading'                 => 'Récompenses',
                    'intro'                   => 'Seules les récompenses qui ne sont pas accordées ou qui ne sont pas encore approuvées, donc pas encore sur votre solde, vous les trouverez ici. Vous ne verrez pas ici les récompenses déjà accordées et sur votre solde.',
                    'list'                    =>
                        [
                            'amount' => 'Montant (USD)',
                            'date'   => 'Date',
                            'status' => 'Statut',
                            'title'  => 'Aperçu des récompenses',
                            'type'   => 'Type',
                        ],
                    'no_reward_yet'           => 'Pas encore de récompense. Pour augmenter vos chances de récompenses, assurez-vous de remplir vos :link autant et corriger que possible.',
                    'no_reward_yet_link_text' => 'questions de profilage',
                    'notes'                   =>
                        [
                            'statuses' =>
                                [
                                    'approved' => 'Approuvé = Récompense approuvée et devrait être reflétée sur votre solde.',
                                    'denied'   => 'Refusé = La récompense ne sera pas accordée. Cela pourrait avoir de nombreuses raisons, mais l\'une de ces raisons pourrait être le fait de ne pas prendre l\'exercice au sérieux.',
                                    'pending'  => 'En attente = Récompense en attente pour la conclusion du projet.',
                                    'title'    => 'Statuts',
                                ],
                            'title'    => 'Remarques',
                        ],
                    'status'                  =>
                        [
                            'approved' => 'Approuvé',
                            'default'  => 'Récompensé',
                            'denied'   => 'Refusé',
                            'pending'  => 'En attente',
                        ],
                    'type'                    =>
                        [
                            'default'  => 'Participation',
                            'referral' => 'Référence',
                            'survey'   => 'Enquête',
                        ],
                ],
            'security'             =>
                [
                    'heading' => 'Sécurité',
                ],
            'survey_opportunities' =>
                [
                    'heading'       => 'Enquêtes',
                    'notification'  =>
                        [
                            'line_1' => 'Vous n\'avez pas complété votre profil. Veuillez le faire pour que nous puissions vous donner des enquêtes.',
                            'line_2' => 'Si vous recevez cet avertissement et que vous avez déjà complété votre profil, veuillez nous contacter.',
                        ],
                    'opportunities' =>
                        [
                            'action_start'     => 'Commencer l\'enquête',
                            'column_incentive' => 'Récompense',
                            'column_loi'       => 'Durée (minutes)',
                            'heading'          => 'Enquêtes disponibles',
                            'no_survey'        => 'Il n\'y a pas d\'enquête disponible pour le moment.',
                        ],
                ],
        ],
];
