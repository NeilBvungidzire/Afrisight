<?php

return [
    'money-balance' => ':amount USD',
    'point-balance' => ':amount points',
    'sub_pages'     =>
        [
            'delete_account'       =>
                [
                    'cancel_text'  => 'Cancelar',
                    'confirm_text' => 'Tenho a certeza',
                    'delete_link'  => 'Eliminar a conta',
                    'heading'      => 'Eliminar a conta',
                ],
            'edit_general_info'    =>
                [
                    'cancel_text' => 'Cancelar',
                    'fields'      =>
                        [
                            'country'       =>
                                [
                                    'default_value' => 'Escolha o país em que reside',
                                    'label'         => 'País em que se encontra',
                                ],
                            'date_of_birth' =>
                                [
                                    'info'  => 'Formato dia-mês-ano, por exemplo 24-03-1985',
                                    'label' => 'Data de nascimento',
                                ],
                            'first_name'    =>
                                [
                                    'label' => 'Primeiro nome',
                                ],
                            'gender'        =>
                                [
                                    'default_value' => 'Escolha o seu género',
                                    'label'         => 'Sexo',
                                ],
                            'last_name'     =>
                                [
                                    'label' => 'Último nome',
                                ],
                            'mobile_number' =>
                                [
                                    'info'        => 'Necessário para o pagamento, o recarregamento do telemóvel ou para convite via SMS. Por favor, preencha com o código do seu país. Por exemplo +123936438354.',
                                    'label'       => 'Número de telemóvel (com código do país)',
                                    'placeholder' => 'Por exemplo, +123936438354',
                                ],
                        ],
                    'heading'     => 'Editar informação geral',
                    'save_text'   => 'Atualizar',
                ],
            'email_change'         =>
                [
                    'alert'   =>
                        [
                            'only_once_in_30_days'      => 'Você só pode alterar isso uma vez em 30 dias!',
                            'successful_change'         => 'Seu endereço de e-mail foi alterado para :new_email com sucesso.',
                            'successful_request'        => 'Enviamos um e-mail para seu novo endereço de e-mail. Certifique-se de abrir este e-mail e seguir as instruções dentro de uma hora!',
                            'unsuccessful_verification' => 'Não foi possível alterar seu endereço de e-mail. Por favor, certifique-se de seguir as instruções!',
                        ],
                    'form'    =>
                        [
                            'current_password' =>
                                [
                                    'info_text'   => 'Por motivos de segurança, você deve verificar sua identidade digitando sua senha.',
                                    'placeholder' => 'Senha atual',
                                ],
                            'new_email'        =>
                                [
                                    'placeholder' => 'Seu novo endereço de e-mail',
                                ],
                            'submit'           =>
                                [
                                    'label' => 'Solicitar alteração',
                                ],
                        ],
                    'heading' => 'Mude o e-mail',
                ],
            'general_info'         =>
                [
                    'cta_text' => 'Editar',
                    'heading'  => 'Informação geral',
                ],
            'linked_accounts'      =>
                [
                    'facebook'   => 'Facebook',
                    'google'     => 'Google',
                    'heading'    => 'Contas vinculadas',
                    'link'       => 'Vincular',
                    'not_linked' => 'Não vinculado',
                    'unlink'     => 'Desvincular',
                ],
            'login_details'        =>
                [
                    'email'    =>
                        [
                            'info_text' => 'Você tem permissão para alterar seu e-mail apenas a cada 30 dias e parece que já fez isso nos últimos 30 dias.',
                            'label'     => 'E-mail',
                        ],
                    'heading'  => 'Detalhes de login',
                    'password' =>
                        [
                            'change_password' => 'Alterar',
                            'label'           => 'Palavra-passe',
                            'set_password'    => 'Definir a palavra-passe',
                        ],
                ],
            'payout'               =>
                [
                    'able'                   =>
                        [
                            'cta_text' => 'Solicitar o pagamento de :amount USD',
                            'line_1'   => 'Pode solicitar o pagamento via :method. O seu saldo atingiu o valor mínimo de :amount USD.',
                        ],
                    'heading'                => 'Pagamento',
                    'intro'                  =>
                        [
                            'line_1' => 'Pode solicitar o pagamento caso tenha atingido o valor mínimo. Pode encontrar o valor mínimo de acordo com a forma de pagamento abaixo.',
                            'line_2' => 'Após a solicitação de pagamento, vai receber um e-mail com mais instruções.',
                        ],
                    'payout_request_failed'  => 'Não foi possível solicitar o pagamento Por favor, entre em contato connosco para que possamos ajudá-lo(a).',
                    'payout_request_succeed' => 'O seu pedido foi enviado com sucesso. Para mais instruções verifique o seu e-mail.',
                    'unable'                 =>
                        [
                            'line_1' => 'Ainda não pode solicitar o pagamento via :method. O seu saldo ainda não atingiu o valor mínimo de :threshold USD. O seu saldo neste momento é :amount USD.',
                        ],
                    'wrong_payout_method'    => 'Método de pagamento escolhido errado.',
                ],
            'profiling'            =>
                [
                    'heading'     => 'Criação de perfil',
                    'submit_text' => 'Guardar os dados do perfil criado',
                ],
            'rewards'              =>
                [
                    'heading'                 => 'Compensações',
                    'intro'                   => 'Apenas as recompensas que não foram concedidas ou ainda não foram aprovadas, portanto, ainda não no seu saldo, você encontrará aqui. As recompensas que já estão concedidas e no seu saldo você não verá aqui.',
                    'list'                    =>
                        [
                            'amount' => 'Quantidade (USD)',
                            'date'   => 'Encontro',
                            'status' => 'Status',
                            'title'  => 'Visão geral das recompensas',
                            'type'   => 'Tipo',
                        ],
                    'no_reward_yet'           => 'Nenhuma recompensa ainda. Para aumentar sua chance de obter recompensas, certifique-se de preencher os :link e corrigir o máximo possível.',
                    'no_reward_yet_link_text' => 'perguntas de criação de perfil',
                    'notes'                   =>
                        [
                            'statuses' =>
                                [
                                    'approved' => 'Aprovado = Prêmio aprovado e deve ser refletido em seu saldo.',
                                    'denied'   => 'Negado = a recompensa não será concedida. Isso pode ter vários motivos, mas um deles pode ser o fato de não levar o exercício a sério.',
                                    'pending'  => 'Pendente = Recompensa pendente para conclusão do projeto.',
                                    'title'    => 'Status',
                                ],
                            'title'    => 'Notas',
                        ],
                    'status'                  =>
                        [
                            'approved' => 'Aprovado',
                            'default'  => 'Recompensado',
                            'denied'   => 'Negado',
                            'pending'  => 'Pendente',
                        ],
                    'type'                    =>
                        [
                            'default'  => 'Participação',
                            'referral' => 'Referência',
                            'survey'   => 'Pesquisa',
                        ],
                ],
            'security'             =>
                [
                    'heading' => 'Segurança',
                ],
            'survey_opportunities' =>
                [
                    'heading'       => 'Inquéritos',
                    'notification'  =>
                        [
                            'line_1' => 'Não acabou de criar o seu perfil. Faça esse procedimento para que possamos enviar-lhe inquéritos.',
                            'line_2' => 'Caso receba este aviso e já tenha acabado de criar o seu perfil, entre em contato connosco.',
                        ],
                    'opportunities' =>
                        [
                            'action_start'     => 'Iniciar o inquérito',
                            'column_incentive' => 'Compensações',
                            'column_loi'       => 'Duração (minutos)',
                            'heading'          => 'Inquéritos disponíveis',
                            'no_survey'        => 'Não há nenhum inquérito disponível no momento.',
                        ],
                ],
        ],
];
