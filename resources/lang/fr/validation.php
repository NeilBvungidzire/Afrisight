<?php

return [
    'accepted'             => 'L\' :attribute doit être accepté.',
    'active_url'           => 'L\':attribute n\'est pas une URL valide.',
    'after'                => 'L\':attribute doit être une date postérieure à :date.',
    'after_or_equal'       => 'L\':attribute doit être une date postérieure ou égale à :date.',
    'alpha'                => 'L\':attribute ne peut contenir que des lettres.',
    'alpha_dash'           => 'L\':attribute ne peut contenir que des lettres, des chiffres, des tirets et des traits de soulignement.',
    'alpha_num'            => 'L\':attribute ne peut contenir que des lettres et des chiffres.',
    'array'                => 'L\':attribute doit être un tableau.',
    'attributes'           =>
        [
            'country_id'    => 'pays dans lequel vous vous trouvez',
            'date_of_birth' => 'date de naissance',
            'email'         => 'adresse électronique',
            'first_name'    => 'prénom',
            'gender_code'   => 'sexe',
            'language_code' => 'langue',
            'last_name'     => 'nom',
            'mobile_number' => 'numéro de téléphone portable',
            'password'      => 'mot de passe',
        ],
    'before'               => 'L\':attribute doit être une date antérieure à :date.',
    'before_or_equal'      => 'L\':attribute doit être une date antérieure ou égale à :date.',
    'between'              =>
        [
            'array'   => 'L\':attribute doit être compris entre :min et :max éléments.',
            'file'    => 'L\':attribute doit être compris entre :min et :max kilo-octets.',
            'numeric' => 'L\':attribute doit être compris entre :min et :max.',
            'string'  => 'L\':attribute doit être compris entre :min et :max caractères.',
        ],
    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed'            => 'La confirmation de :attribute ne correspond pas.',
    'custom'               =>
        [
            'attribute-name' =>
                [
                    'rule-name' => 'message personnalisé',
                ],
            'mobile_number'  =>
                [
                    '*' => 'Assurez-vous de saisir le bon numéro de téléphone portable, avec le code du pays.',
                ],
            'usd_amount'     =>
                [
                    'less_than_maximum'             => 'Doit être inférieur au montant maximum.',
                    'minimum_threshold_not_reached' => 'Doit être supérieur au montant minimum.',
                ],
        ],
    'date'                 => 'L\':attribute n\'est pas une date valide.',
    'date_equals'          => 'L\':attribute doit être une date égale à :date.',
    'date_format'          => 'L\':attribute ne correspond pas au format :format.',
    'different'            => 'L\':attribute et :other doivent être différents.',
    'digits'               => 'L\':attribute doit comporter :digits chiffres.',
    'digits_between'       => 'L\':attribute doit être compris entre :min et :max chiffres.',
    'dimensions'           => 'L\':attribute a des dimensions d\'image non valides.',
    'distinct'             => 'Le champ :attribute a une valeur en double.',
    'email'                => 'L\':attribute doit être une adresse électronique valide.',
    'ends_with'            => 'L\':attribute doit se terminer par l\'un des messages suivants :values',
    'exists'               => 'L\':attribute sélectionné n\'est pas valable.',
    'file'                 => 'L\':attribute doit être une chaîne de caractères.',
    'filled'               => 'Le champ :attribute doit comporter une valeur.',
    'gt'                   =>
        [
            'array'   => 'L\':attribute doit être supérieur à :value éléments.',
            'file'    => 'L\':attribute doit être supérieur à :value kilo-octets.',
            'numeric' => 'L\':attribute doit être supérieur à la valeur :value.',
            'string'  => 'L\':attribute doit être supérieur à :value caractères.',
        ],
    'gte'                  =>
        [
            'array'   => 'L\':attribute doit comporter :value éléments ou plus.',
            'file'    => 'L\':attribute doit être supérieur ou égal à :value kilo-octets.',
            'numeric' => 'L\':attribute doit être supérieur ou égal à la valeur :value.',
            'string'  => 'L\':attribute doit comporter :value caractères ou plus.',
        ],
    'image'                => 'L\':attribute doit être une image.',
    'in'                   => 'L\':attribute sélectionné n\'est pas valable.',
    'in_array'             => 'Le champ :attribute n\'existe pas dans :other.',
    'integer'              => 'L\':attribute doit être un entier.',
    'ip'                   => 'L\':attribute doit être une adresse IP valide.',
    'ipv4'                 => 'L\':attribute doit être une adresse IPv4 valide.',
    'ipv6'                 => 'L\':attribute doit être une adresse IPv6 valide.',
    'json'                 => 'L\':attribute doit être une chaîne de caractères JSON valide.',
    'lt'                   =>
        [
            'array'   => 'L\':attribute doit être inférieur à :value éléments.',
            'file'    => 'L\':attribute doit être inférieur à :value kilo-octets.',
            'numeric' => 'L\':attribute doit être inférieur à la valeur :value.',
            'string'  => 'L\':attribute doit être inférieur à :value caractères.',
        ],
    'lte'                  =>
        [
            'array'   => 'L\':attribute ne doit pas être supérieur à :value éléments.',
            'file'    => 'L\':attribute doit être inférieur ou égal à :value kilo-octets.',
            'numeric' => 'L\':attribute doit être inférieur ou égal à la valeur :value.',
            'string'  => 'L\':attribute doit comporter :value caractères ou moins.',
        ],
    'max'                  =>
        [
            'array'   => 'L\':attribute ne peut pas comporter plus de :max éléments.',
            'file'    => 'L\':attribute ne peut pas être supérieur à :max kilo-octets.',
            'numeric' => 'L\':attribute ne peut pas être supérieur à la valeur :max.',
            'string'  => 'L\':attribute ne peut pas comporter plus de :max caractères.',
        ],
    'mimes'                => 'L\':attribute doit être un fichier de type :values.',
    'mimetypes'            => 'L\':attribute doit être un fichier de type :values.',
    'min'                  =>
        [
            'array'   => 'L\':attribute doit avoir au moins :min éléments.',
            'file'    => 'L\':attribute doit être au moins de :min kilo-octets.',
            'numeric' => 'L\':attribute doit être au moins de :min.',
            'string'  => 'L\':attribute doit comporter au moins :min caractères.',
        ],
    'not_in'               => 'L\':attribute sélectionné n\'est pas valable.',
    'not_regex'            => 'Le format :attribute n\'est pas valable.',
    'numeric'              => 'L\':attribute doit être un nombre.',
    'present'              => 'Le champ :attribute doit être présent.',
    'regex'                => 'Le format :attribute n\'est pas valable.',
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_if'          => 'Le champ :attribute est requis lorsque :other est :value.',
    'required_unless'      => 'Le champ :attribute est requis à moins que :other se trouve dans :values.',
    'required_with'        => 'Le champ :attribute est requis lorsque la valeur :values est présente.',
    'required_with_all'    => 'Le champ :attribute est requis lorsque les valeurs :values sont présentes.',
    'required_without'     => 'Le champ :attribute est requis lorsque la valeur :values n\'est pas présente.',
    'required_without_all' => 'Le champ :attribute est requis lorsqu\'aucune des valeurs :values sont présentes.',
    'same'                 => 'L\':attribute et :other doivent correspondre.',
    'size'                 =>
        [
            'array'   => 'L\':attribute doit être de :size éléments.',
            'file'    => 'L\':attribute doit être de :size kilo-octets.',
            'numeric' => 'L\':attribute doit être de :size.',
            'string'  => 'L\':attribute doit être de :size caractères.',
        ],
    'starts_with'          => 'L\':attribute doit commencer par l\'un des messages suivants : :values',
    'string'               => 'L\':attribute doit être une chaîne de caractères.',
    'timezone'             => 'L\':attribute doit être une zone valide.',
    'unique'               => 'L\':attribute est déjà pris.',
    'uploaded'             => 'Le téléchargement de :attribute a échoué.',
    'url'                  => 'Le format :attribute n\'est pas valable.',
    'uuid'                 => 'L\':attribute doit être un UUID valide.',
];
