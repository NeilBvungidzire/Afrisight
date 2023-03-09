<?php

return [
    'annual_household_income_range'          =>
        [
            'question' => 'Quel est le revenu annuel total de votre ménage avant impôt au cours d\'une année type?',
        ],
    'biased_markets'                         =>
        [
            'answer'   =>
                [
                    'advertising_agencies'        => 'Agences de publicité',
                    'marketing'                   => 'Commercialisation',
                    'media_or_internet'           => 'Médias ou Internet',
                    'none'                        => 'Aucun d\'eux',
                    'public_relation_agencies'    => 'Agences de relations publiques',
                    'surveys_and_market_research' => 'Enquêtes et études de marché',
                ],
            'question' => 'Est-ce que vous ou un membre de votre famille travaillez dans l\'un des secteurs suivants?',
        ],
    'children_guardian'                      =>
        [
            'question' => 'Avez-vous des enfants de moins de 18 ans vivant dans votre foyer ? Veuillez mentionner uniquement les enfants dont vous êtes le parent ou le tuteur légal. (S\'il n\'y a pas d\'enfants de moins de 18 ans dans votre foyer, veuillez saisir "non") ?',
        ],
    'city'                                   =>
        [
            'other'    =>
                [
                    'label' => 'Autre',
                ],
            'question' => 'Dans quelle ville / territoire habitez-vous actuellement?',
        ],
    'device_restriction'                     =>
        [
            'alert'   => 'Pour cette enquête, vous avez besoin de l\'un des appareils suivants : :devices. Sinon, vous ne pourrez pas participer. Vous pouvez quitter l\'enquête maintenant et recommencer sur l\'un des appareils autorisés.',
            'DESKTOP' => 'Bureau',
            'MOBILE'  => 'Téléphone mobile',
            'TABLET'  => 'Tablette',
        ],
    'general'                                =>
        [
            'income_between'   => 'Entre :local_currency :min_local_amount et :local_currency :max_local_amount.',
            'income_less_than' => 'Moins de :local_currency :local_amount.',
            'income_more_than' => 'Plus de :local_currency :local_amount.',
            'no'               => 'Non',
            'no_income'        => 'Aucun revenu',
            'other'            => 'Autre',
            'webcam_access'    =>
                [
                    'question' => 'Avez-vous accès à une webcam?',
                ],
            'yes'              => 'Oui',
        ],
    'messaging_app'                          =>
        [
            'none_used_past_month' => 'Je n\'ai utilisé aucune application de messagerie au cours du dernier mois',
        ],
    'monthly_household_income_range'         =>
        [
            'question' => 'Quel est le revenu mensuel total de votre ménage avant impôts au cours d\'un mois type?',
        ],
    'monthly_personal_income_range'          =>
        [
            'question' => 'Quel est votre revenu personnel actuel avant impôt au cours d\'un mois type?',
        ],
    'parent_children_age_range'              =>
        [
            1          =>
                [
                    'option' =>
                        [
                            1 => 'Oui',
                            2 => 'Non',
                        ],
                ],
            'question' =>
                [
                    1 => 'Vous avez des enfants âgés de 1 mois à 3 ans?',
                    2 => 'Avez-vous des enfants âgés de 4 à 11 ans?',
                ],
        ],
    'sec_option_1'                           =>
        [
            'attributes' =>
                [
                    1  =>
                        [
                            'label' => 'Aide ménagère (travailleurs domestiques et / ou jardiniers)',
                        ],
                    10 =>
                        [
                            'label' => 'Machine à laver',
                        ],
                    11 =>
                        [
                            'label' => 'Télévision noir et blanc',
                        ],
                    12 =>
                        [
                            'label' => 'DVD (disque vidéo numérique)',
                        ],
                    13 =>
                        [
                            'label' => 'Câble satellite',
                        ],
                    14 =>
                        [
                            'label' => 'Téléphone (terrestre)',
                        ],
                    15 =>
                        [
                            'label' => 'Téléphone (mobile)',
                        ],
                    16 =>
                        [
                            'label' => 'Chauffeur personnel',
                        ],
                    17 =>
                        [
                            'label' => 'Plusieurs voitures',
                        ],
                    18 =>
                        [
                            'label' => 'L\'ordinateur',
                        ],
                    19 =>
                        [
                            'label' => 'Ordinateur portable',
                        ],
                    2  =>
                        [
                            'label' => 'Réfrigérateur / congélateur',
                        ],
                    20 =>
                        [
                            'label' => 'Générateur',
                        ],
                    21 =>
                        [
                            'label' => 'Gaz / cuisinière électrique / cuisinière',
                        ],
                    22 =>
                        [
                            'label' => 'Poêle au kérosène',
                        ],
                    23 =>
                        [
                            'label' => 'Charbon / bois',
                        ],
                    24 =>
                        [
                            'label' => 'Toilettes / WC à chasse d\'eau intérieure / extérieure',
                        ],
                    25 =>
                        [
                            'label' => 'Latrine à fosse',
                        ],
                    26 =>
                        [
                            'label' => 'Rien',
                        ],
                    27 =>
                        [
                            'label' => 'À l\'intérieur',
                        ],
                    28 =>
                        [
                            'label' => 'Robinet à tube extérieur',
                        ],
                    29 =>
                        [
                            'label' => 'Forage',
                        ],
                    3  =>
                        [
                            'label' => 'Vidéo',
                        ],
                    30 =>
                        [
                            'label' => 'bien',
                        ],
                    31 =>
                        [
                            'label' => 'Flux',
                        ],
                    32 =>
                        [
                            'label' => 'Primaire incomplet',
                        ],
                    33 =>
                        [
                            'label' => 'Primaire terminé',
                        ],
                    34 =>
                        [
                            'label' => 'Secondaire incomplet',
                        ],
                    35 =>
                        [
                            'label' => 'Secondaire terminé',
                        ],
                    36 =>
                        [
                            'label' => 'Université / Polytechnique: OND',
                        ],
                    37 =>
                        [
                            'label' => 'Université / Polytechnique: HND',
                        ],
                    38 =>
                        [
                            'label' => 'Post-universitaire incomplet',
                        ],
                    39 =>
                        [
                            'label' => 'Après l\'université terminée',
                        ],
                    4  =>
                        [
                            'label' => 'Voiture',
                        ],
                    40 =>
                        [
                            'label' => 'Analphabète / Aucun',
                        ],
                    41 =>
                        [
                            'label' => 'Faible densité',
                        ],
                    42 =>
                        [
                            'label' => 'Densité moyenne',
                        ],
                    43 =>
                        [
                            'label' => 'Haute densité',
                        ],
                    44 =>
                        [
                            'label' => 'Bungalow indépendant',
                        ],
                    45 =>
                        [
                            'label' => 'Maison et / ou Villa',
                        ],
                    46 =>
                        [
                            'label' => 'Plat',
                        ],
                    47 =>
                        [
                            'label' => 'Duplex',
                        ],
                    48 =>
                        [
                            'label' => 'Mini plat',
                        ],
                    49 =>
                        [
                            'label' => 'Chambre et salon',
                        ],
                    5  =>
                        [
                            'label' => 'TV couleur',
                        ],
                    50 =>
                        [
                            'label' => 'Chambre simple',
                        ],
                    51 =>
                        [
                            'label' => 'Haute direction / administration.',
                        ],
                    52 =>
                        [
                            'label' => 'Directeur général',
                        ],
                    53 =>
                        [
                            'label' => 'Chef de service / Senior Manager',
                        ],
                    54 =>
                        [
                            'label' => 'Directeur',
                        ],
                    55 =>
                        [
                            'label' => 'Professionnel (col blanc), par exemple, responsable marketing, médecin, avocat, ingénieurs, etc.',
                        ],
                    56 =>
                        [
                            'label' => 'Ouvriers qualifiés (mécaniciens, tailleurs, charpentiers, maçons)',
                        ],
                    57 =>
                        [
                            'label' => 'Travailleurs sans compétences',
                        ],
                    58 =>
                        [
                            'label' => 'Employés de bureau',
                        ],
                    59 =>
                        [
                            'label' => 'Chômeur / étudiant',
                        ],
                    6  =>
                        [
                            'label' => 'Système musical',
                        ],
                    60 =>
                        [
                            'label' => 'Adhésion à un club social / récréatif',
                        ],
                    61 =>
                        [
                            'label' => 'Voyager à l\'étranger pour les vacances',
                        ],
                    62 =>
                        [
                            'label' => 'Lisez régulièrement comme une habitude',
                        ],
                    63 =>
                        [
                            'label' => 'Passez du temps libre avec des amis',
                        ],
                    64 =>
                        [
                            'label' => 'Participez à des occasions sociales',
                        ],
                    65 =>
                        [
                            'label' => 'Comme la mode moderne',
                        ],
                    7  =>
                        [
                            'label' => 'Unité de climatisation (split)',
                        ],
                    8  =>
                        [
                            'label' => 'Climatisation',
                        ],
                    9  =>
                        [
                            'label' => 'Antenne satellite',
                        ],
                ],
            'question'   =>
                [
                    'cooking'                  => 'Lequel des articles utilisez-vous souvent ou possédez-vous (sélectionnez tout ce qui s\'applique)?',
                    'education_household_head' => 'Quel est le niveau d\'éducation de votre chef de ménage / soutien de famille (cela peut être vous ou quelqu\'un d\'autre)?',
                    'lifestyle'                => 'Quelles activités faites-vous pour les loisirs / le plaisir (sélectionnez tout ce qui s\'applique)?',
                    'main_water_source'        => 'Quelle est la principale source d\'eau de votre ménage?',
                    'occupation'               => 'Quelle est votre profession actuelle?',
                    'ownership'                => 'Est-ce que vous ou le ménage dans lequel vous vivez possédez / avez les articles énumérés ou l\'aide (sélectionnez tout ce qui s\'applique)?',
                    'residential_area'         => 'Dans quel quartier habitez-vous?',
                    'toilet_type'              => 'Laquelle des toilettes utilisez-vous souvent dans votre ménage?',
                    'type_house'               => 'Dans quel logement / logement habitez-vous?',
                ],
        ],
    'subdivision'                            =>
        [
            'CM-AD' =>
                [
                    'label' => 'Adamaoua',
                ],
            'CM-CE' =>
                [
                    'label' => 'Centre',
                ],
            'CM-EN' =>
                [
                    'label' => 'Extrème nord',
                ],
            'CM-ES' =>
                [
                    'label' => 'est',
                ],
            'CM-LT' =>
                [
                    'label' => 'Littoral',
                ],
            'CM-NO' =>
                [
                    'label' => 'Nord',
                ],
            'CM-NW' =>
                [
                    'label' => 'Nord Ouest',
                ],
            'CM-OU' =>
                [
                    'label' => 'Ouest',
                ],
            'CM-SU' =>
                [
                    'label' => 'Sud',
                ],
            'CM-SW' =>
                [
                    'label' => 'Sud-ouest',
                ],
        ],
    'subdivisions'                           =>
        [
            'district_city_town'  =>
                [
                    'question' => 'Dans quel district/ville/territoire habitez-vous actuellement?',
                ],
            'districts'           =>
                [
                    'question' => 'Dans quel territoire de district habitez-vous actuellement?',
                ],
            'governorate'         =>
                [
                    'question' => 'Dans quel gouvernorat vivez-vous actuellement?',
                ],
            'province_capital'    =>
                [
                    'question' => 'Dans quelle province/territoire de la capitale habitez-vous actuellement?',
                ],
            'province_prefecture' =>
                [
                    'question' => 'Dans quelle province/préfecture du territoire habitez-vous actuellement?',
                ],
            'regions'             =>
                [
                    'question' => 'Dans quelle région territoire habitez-vous actuellement?',
                ],
            'state'               =>
                [
                    'question' => 'Dans quel état vivez-vous actuellement?',
                ],
        ],
    'typical_monthly_household_income_range' =>
        [
            'question' => 'Quel est le revenu mensuel total de votre ménage avant impôts au cours d\'un mois type?',
        ],
];
