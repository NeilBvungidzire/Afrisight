<?php

return [
    'accepted'             => 'O :attribute deve ser aceite.',
    'active_url'           => 'O :attribute não é uma URL válida.',
    'after'                => 'O :attribute deve ser uma data após a :date.',
    'after_or_equal'       => 'O :attribute deve ser uma data após ou igual a :date.',
    'alpha'                => 'O :attribute deve conter letras apenas.',
    'alpha_dash'           => 'O :attribute deve conter apenas letras, números, travessões e underscores.',
    'alpha_num'            => 'O :attribute deve conter apenas letras e números.',
    'array'                => 'O :attribute deve ser uma matriz.',
    'attributes'           =>
        [
            'country_id'    => 'país em que se encontra',
            'date_of_birth' => 'data de nascimento',
            'email'         => 'endereço de e-mail',
            'first_name'    => 'primeiro nome',
            'gender_code'   => 'sexo',
            'language_code' => 'linguagem',
            'last_name'     => 'último nome',
            'mobile_number' => 'número de telemóvel',
            'password'      => 'palavra-passe',
        ],
    'before'               => 'O :attribute deve ser uma data antes de :date.',
    'before_or_equal'      => 'O :attribute deve ser uma data anterior ou igual à :date.',
    'between'              =>
        [
            'array'   => 'O :attribute deve situar-se entre o número :min e :max Itens.',
            'file'    => 'O :attribute deve situar-se entre o número :min e :max kilobytes.',
            'numeric' => 'O :attribute deve situar-se entre o número :min e :max.',
            'string'  => 'O :attribute deve situar-se entre o número :min e :max caracteres.',
        ],
    'boolean'              => 'O campo do :attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'A confirmação do :attribute não confere.',
    'custom'               =>
        [
            'attribute-name' =>
                [
                    'rule-name' => 'mensagem personalizada',
                ],
            'mobile_number'  =>
                [
                    '*' => 'Certifique-se de preencher o número de celular correto, com o código do país.',
                ],
            'usd_amount'     =>
                [
                    'less_than_maximum'             => 'Deve ser inferior ao valor máximo.',
                    'minimum_threshold_not_reached' => 'Deve ser maior que o valor mínimo.',
                ],
        ],
    'date'                 => 'O :attribute não é uma data válida.',
    'date_equals'          => 'O :attribute deve ser uma data igual à :date.',
    'date_format'          => 'O :attribute não corresponde ao formato :format.',
    'different'            => 'O :attribute e o :other devem ser diferentes.',
    'digits'               => 'O :attribute deve consistir em :digits dígitos.',
    'digits_between'       => 'O :attribute deve situar-se entre o número :min e :max dígitos.',
    'dimensions'           => 'O :attribute tem dimensões de imagem inválidas.',
    'distinct'             => 'O campo :attribute tem um valor duplicado.',
    'email'                => 'O :attribute deve ser um endereço de e-mail válido.',
    'ends_with'            => 'O :attribute deve terminar com um dos seguintes: :values',
    'exists'               => 'O :attribute selecionado é inválido.',
    'file'                 => 'O :attribute deve ser um ficheiro.',
    'filled'               => 'O campo :attribute deve ter um valor.',
    'gt'                   =>
        [
            'array'   => 'O :attribute deve ser maior do que o :value itens.',
            'file'    => 'O :attribute deve ser maior do que o :value kilobytes.',
            'numeric' => 'O :attribute deve ser maior do que o :value.',
            'string'  => 'O :attribute deve ser maior do que o :value caracteres.',
        ],
    'gte'                  =>
        [
            'array'   => 'O :attribute deve ter :value items ou mais.',
            'file'    => 'O :attribute deve ser maior ou igual ao :value in kilobytes.',
            'numeric' => 'O :attribute deve ser maior ou igual ao :value.',
            'string'  => 'O :attribute deve ser maior ou igual ao :value caracteres.',
        ],
    'image'                => 'O :attribute deve ser uma imagem.',
    'in'                   => 'O :attribute selecionado é inválido.',
    'in_array'             => 'O campo :attribute não existe em :other.',
    'integer'              => 'O :attribute deve ser um número inteiro.',
    'ip'                   => 'O :attribute deve ser um endereço de IP válido.',
    'ipv4'                 => 'O :attribute deve ser um endereço de IPv4 válido.',
    'ipv6'                 => 'O :attribute deve ser um endereço de IPv6 válido.',
    'json'                 => 'O :attribute deve ser uma string JSON válida.',
    'lt'                   =>
        [
            'array'   => 'O :attribute deve ser menor do que o :value items.',
            'file'    => 'O :attribute deve ser menor do que o :value kilobytes.',
            'numeric' => 'O :attribute deve ser menor do que o :value.',
            'string'  => 'O :attribute deve ser menor do que o :value caracteres.',
        ],
    'lte'                  =>
        [
            'array'   => 'O :attribute não poderá ter mais do que o :value items.',
            'file'    => 'O :attribute deve ser menor ou igual ao :value kilobytes.',
            'numeric' => 'O :attribute deve ser menor ou igual ao :value.',
            'string'  => 'O :attribute deve ser menor ou igual ao :value caracteres.',
        ],
    'max'                  =>
        [
            'array'   => 'O :attribute não poderá ter mais do que o valor :max items.',
            'file'    => 'O :attribute não poderá ser maior do que o valor :max kilobytes.',
            'numeric' => 'O :attribute não poderá ser maior do que o valor :max.',
            'string'  => 'O :attribute não poderá ser maior do que o valor :max caracteres.',
        ],
    'mimes'                => 'O :attribute deve ser um arquivo do tipo :values.',
    'mimetypes'            => 'O :attribute deve ser um arquivo do tipo :values.',
    'min'                  =>
        [
            'array'   => 'O :attribute deve ter um valor :min itens.',
            'file'    => 'O :attribute deve ter um valor :min kilobytes.',
            'numeric' => 'O :attribute deve ter um valor :min',
            'string'  => 'O :attribute deve ter um valor :min caracteres.',
        ],
    'not_in'               => 'O :attribute selecionado é inválido.',
    'not_regex'            => 'O formato do :attribute é inválido.',
    'numeric'              => 'O :attribute deve ser um número.',
    'present'              => 'O campo :attribute deve estar presente.',
    'regex'                => 'O formato do :attribute é inválido.',
    'required'             => 'O campo do :attribute é obrigatório.',
    'required_if'          => 'O campo :attribute é obrigatório quando o :other é um :value.',
    'required_unless'      => 'O campo :attribute é obrigatório, a não ser que o :other esteja em :values.',
    'required_with'        => 'O campo :attribute é obrigatório quando os :values estiverem presentes.',
    'required_with_all'    => 'O campo :attribute é obrigatório quando os :values estiverem presentes.',
    'required_without'     => 'O campo :attribute é obrigatório quando os :values não estiverem presentes.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum dos :values estiverem presentes.',
    'same'                 => 'O :attribute e :other devem coincidir.',
    'size'                 =>
        [
            'array'   => 'O :attribute deve ter o :size itens.',
            'file'    => 'O :attribute deve ter o :size kilobytes.',
            'numeric' => 'O :attribute deve ser do :size.',
            'string'  => 'O :attribute deve ter o :size characters.',
        ],
    'starts_with'          => 'O :attribute deve iniciar com um dos seguintes: :values',
    'string'               => 'A :attribute deve ser uma sequência.',
    'timezone'             => 'O :attribute deve ser uma zona válida.',
    'unique'               => 'O :attribute já está a ser utilizado.',
    'uploaded'             => 'Não foi possível carregar o :attribute.',
    'url'                  => 'O formato do :attribute é inválido.',
    'uuid'                 => 'O :attribute deve ser uma UUID válida.',
];
