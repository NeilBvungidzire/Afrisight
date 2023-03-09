<?php

return [
    'back_to_payout_cta'     => 'Voltar para pagamento',
    'country_not_set'        => 'Você precisa definir seu país para poder acessar uma opção de pagamento.',
    'intro'                  => 'Oferecemos suporte a diferentes métodos de pagamento. Dependendo da disponibilidade em seu país, você encontrará os disponíveis para você abaixo. Você pode solicitar o pagamento caso tenha atingido o valor mínimo. Você pode encontrar o valor mínimo por método de pagamento abaixo.',
    'method'                 =>
        [
            'alternative'   =>
                [
                    'additional_note' =>
                        [
                            1 => 'Infelizmente, neste momento, nenhum método de pagamento sob demanda está disponível em seu país. Porém, estamos em busca de possibilidades para que você mesmo possa solicitar o pagamento quando quiser e da maneira que melhor lhe convier.',
                        ],
                    'intro'           => 'Enviaremos o pagamento nós mesmos depois que você atingir o mínimo de :amount :currency em seu saldo.',
                    'short_name'      => 'Método de pagamento alternativo',
                ],
            'bank_account'  =>
                [
                    'add_bank_account'           => 'Adicionar uma conta bancária',
                    'calculate_local_amount_cta' => 'Calcular o valor de :local_currency',
                    'fail_getting_bank_account'  => 'A conta bancária escolhida não foi encontrada. Verifique sua conta bancária e ajuste conforme necessário.',
                    'form'                       =>
                        [
                            'amount_to_redeem'        =>
                                [
                                    'info'             => 'O mínimo que você pode resgatar é de :minimum_amount USD e o máximo de :maximum_amount USD neste momento. Certifique-se de que o valor que você solicitará seja igual ou entre esses intervalos.',
                                    'label'            => 'Quantidade a resgatar',
                                    'not_reached_info' => 'Você ainda não atingiu o valor mínimo para usar esta opção de pagamento. O valor mínimo que você precisa ter em seu saldo é :amount :currency.',
                                ],
                            'currency_amount'         =>
                                [
                                    'label' => 'Quantidade :currency',
                                ],
                            'footnote'                => 'Ao clicar no botão ":cta_label" abaixo, sua solicitação de resgate do valor será enviada e se tudo estiver OK você receberá em sua conta em breve. Por favor, leve em consideração que levará alguns dias até que o banco selecionado processe esta transação e você a receberá em sua conta. Especialmente quando você solicitar durante o fim de semana. O status de sua solicitação você pode acompanhar na página de pagamento.',
                            'local_amount_pay_out'    =>
                                [
                                    'info'  => 'Este é o valor que você receberá em sua conta.',
                                    'label' => 'Quantia em sua conta',
                                ],
                            'local_amount_to_redeem'  =>
                                [
                                    'info' => 'Este é o valor que você receberá em sua conta bancária.',
                                ],
                            'transfer_fee'            =>
                                [
                                    'info_1' => ':local_amount :local_currency é a taxa que o banco cobra para transferir o dinheiro para sua conta.',
                                    'info_2' => 'Mas, como agradecemos seus esforços, queremos conhecê-lo nesta parte, compensando :compensation_amount :local_currency do valor total da taxa de :total_fee_amount :local_currency. Este montante é quase sempre fixo. Portanto, para aproveitar ao máximo sua recompensa, aconselhamos que você resgate com menos frequência, mas o valor máximo que você tem disponível em sua conta, ao invés de pequenos valores.',
                                    'info_3' => 'Este montante é quase sempre fixo. Portanto, para aproveitar ao máximo sua recompensa, aconselhamos que você resgate com menos frequência, mas o valor máximo que você tem disponível em sua conta, ao invés de pequenos valores.',
                                    'label'  => 'Taxa de transferência',
                                ],
                            'your_bank_account_field' =>
                                [
                                    'info'        => 'Conta bancária para a qual deseja transferir o valor solicitado.',
                                    'label'       => 'Conta bancária',
                                    'placeholder' => 'Escolha sua conta bancária',
                                ],
                        ],
                    'intro'                      => 'Com esta opção, você pode coletar suas recompensas transferindo para sua conta bancária.',
                    'local_amount_header'        => 'Quantidades locais',
                    'local_calculator'           =>
                        [
                            'intro' => 'Calcule o valor :local_currency que você receberá do valor :base_currency que deseja transferir do seu saldo. Isso serve apenas para calcular o valor local e não fará a transferência real.',
                            'title' => 'Calculadora :base_currency a :local_currency',
                        ],
                    'long_name'                  => 'Transferência para sua conta bancária',
                    'manage_bank_account'        => 'Gerenciar minhas contas bancárias',
                    'page_1_intro'               => 'Defina o valor em dólares que você deseja resgatar de seu saldo de recompensas coletado. Caso deseje saber o valor :local_currency, defina o valor em USD e clique em ":cta_label". Em seguida, você terá uma visão geral da quantia :local_currency que receberá e dos custos. Você pode então decidir se isso funciona para você e resgatar suas recompensas.',
                    'request_payout'             =>
                        [
                            'title' => 'Solicitar pagamento',
                        ],
                    'set_max_cta'                => 'Definir max',
                    'short_name'                 => 'Conta bancária',
                ],
            'cint_paypal'   =>
                [
                    'intro'              => 'Com esta opção, você pode coletar suas recompensas transferindo para sua conta do PayPal.',
                    'long_name'          => 'Transferir para sua conta do PayPal',
                    'page_1_intro'       => 'Você pode resgatar :amount :currency de seu saldo de recompensas coletado via PayPal. Após sua solicitação de pagamento, você receberá um e-mail com mais instruções.',
                    'short_name'         => 'PayPal',
                    'successful_request' => 'Sua solicitação foi enviada com sucesso. Verifique seu e-mail para obter mais instruções.',
                ],
            'general'       =>
                [
                    'cancel_request_payout_cta'        => 'Cancelar',
                    'failed_request'                   => 'Não foi possível processar sua solicitação de pagamento. Entre em contato conosco para obter ajuda.',
                    'inactive_reason'                  => 'Esta opção não está disponível no momento. Isso pode ter diferentes motivos, por exemplo, regulamentos do país, mas tentaremos disponibilizar essa opção o mais rápido possível. Considere outras opções de pagamento ou volte em outra ocasião para verificar a disponibilidade desta opção.',
                    'minimum_not_reached'              => 'Você ainda não atingiu a quantidade mínima de :amount :currency para esta opção.',
                    'next_step_request_payout_cta'     => 'Próxima Etapa',
                    'option_available'                 => 'Você atingiu o mínimo de :minimum_amount :currency disponível para esta opção de pagamento. Você pode usar esta opção de pagamento sempre que quiser. O valor máximo que você pode coletar por meio desta opção é :maximum_amount :currency.',
                    'payout_transaction_narration'     => 'Pagamento AfriSight',
                    'previous_step_request_payout_cta' => 'Passo anterior',
                    'request_amount_crossing_limits'   => 'O valor que você deseja resgatar deve estar entre o mínimo e o máximo.',
                    'request_payout_cta'               => 'Solicitar pagamento',
                    'start_cta'                        => 'Verifique as possibilidades',
                    'successful_request'               => 'Sua solicitação foi enviada com sucesso. Leve em consideração que, às vezes, levará algum tempo antes de você receber o pagamento.',
                    'unavailable_button'               => 'Indisponível neste momento',
                    'usage_requirement'                => 'Você pode usar esta opção de pagamento sempre que quiser. O único requisito é que você tenha :min_amount :currency ou mais em seu saldo.',
                ],
            'mobile_money'  =>
                [
                    'long_name'  => 'Transfira para sua carteira móvel',
                    'short_name' => 'Dinheiro móvel',
                ],
            'mobile_top_up' =>
                [
                    'form'                                   =>
                        [
                            'amount'       =>
                                [
                                    'label' => 'Quantia em :currency',
                                ],
                            'operator'     =>
                                [
                                    'label' => 'Operador móvel',
                                ],
                            'phone_number' =>
                                [
                                    'label' => 'Número de telefone',
                                ],
                            'plan'         =>
                                [
                                    'label'       => 'Plano',
                                    'placeholder' => 'Escolha um plano',
                                ],
                        ],
                    'intro'                                  => 'Com esta opção, você pode coletar suas recompensas recarregando seu número de celular.',
                    'intro_extra'                            => 'Receba como recarga de celular inserindo o número do celular que deseja recarregar, selecione a operadora de celular para o número de celular fornecido e, por último, mas não menos importante, defina o valor que deseja resgatar como recarga de celular de seu Saldo.',
                    'long_name'                              => 'Recarregue seu crédito de celular pré-pago',
                    'mobile_operator_not_found'              => 'Não foi possível encontrar a operadora móvel para o número de telefone.',
                    'mobile_operator_threshold_not_achieved' => 'Infelizmente, você não pode resgatar sua recompensa por meio desta operadora com o valor do seu saldo atual. O valor mínimo que você pode resgatar como recarga de celular por meio desta operadora de celular (:operator_threshold) é inferior ao valor máximo que você tem em seu saldo (:account_threshold).',
                    'operator_found'                         =>
                        [
                            'instructions' => 'Esta é a operadora móvel do número de telefone que você inseriu? Em caso afirmativo, clique em "Próxima etapa". Caso contrário, clique em "Etapa anterior" e verifique o número de telefone que você digitou.',
                        ],
                    'operator_not_found'                     =>
                        [
                            'instructions' => 'Verifique novamente o número de telefone que você configurou. Você pode voltar à etapa anterior e alterar o número de telefone. Se ainda não for encontrado, pode ser porque a operadora móvel para este número de telefone ainda não é compatível.',
                            'message'      => 'Não foi possível encontrar a operadora móvel para o número de telefone fornecido!',
                        ],
                    'page_1'                                 =>
                        [
                            'instructions' => 'Coloque o número do celular que você deseja recarregar. Certifique-se de incluir também o código do país, por exemplo, +3451293400343.',
                            'title'        => 'Número de telefone para recarregar',
                        ],
                    'page_2'                                 =>
                        [
                            'instructions' => 'Verifique a operadora de celular para o número de telefone :phone_number.',
                            'title'        => 'Verifique a operadora de celular',
                        ],
                    'page_3'                                 =>
                        [
                            'instructions' =>
                                [
                                    'fixed' => 'Selecione um dos planos de recarga de celular disponíveis.',
                                    'range' => 'Insira o valor a resgatar como recarga de celular.',
                                ],
                            'title'        => 'Definir plano de recarga',
                        ],
                    'phone_number_not_found'                 => 'Não foi possível encontrar o número de telefone.',
                    'short_name'                             => 'Recarga de celular',
                ],
        ],
    'payout_requests'        =>
        [
            'empty_list' => 'Nenhum pedido de pagamento encontrado.',
            'intro'      => 'Aqui você encontrará uma visão geral de suas solicitações de pagamento e o status.',
            'list'       =>
                [
                    'amount' =>
                        [
                            'label' => 'Montante (:currency)',
                        ],
                    'date'   =>
                        [
                            'label' => 'Encontro',
                        ],
                    'method' =>
                        [
                            'label' => 'Método',
                            'value' =>
                                [
                                    'other' => 'Outro método',
                                ],
                        ],
                    'status' =>
                        [
                            'label' => 'Status',
                            'value' =>
                                [
                                    'approved' => 'Aprovado e deve refletir em seu saldo',
                                    'other'    => 'Sendo processado',
                                    'pending'  => 'Solicitado e sendo processado',
                                    'rejected' => 'Rejeitado',
                                ],
                        ],
                ],
            'title'      => 'Pedidos de pagamento',
        ],
    'person_bank_account'    =>
        [
            'available_bank_accounts' =>
                [
                    'intro' => 'Lista de suas contas bancárias que você pode usar para enviar o pagamento solicitado. Nenhuma conta bancária definida ainda ou deseja adicionar outra? Veja abaixo para adicionar um.',
                    'title' => 'Contas bancárias disponíveis',
                ],
            'bank_branch'             =>
                [
                    'form'  =>
                        [
                            'branch_code' => 'código da agência do banco',
                            'branch'      =>
                                [
                                    'label' => 'Selecione agência bancária',
                                ],
                            'submit_cta'  => 'Definir agência bancária',
                        ],
                    'intro' => 'Para o seu país e banco escolhido, é necessário selecionar a agência bancária. Selecione isto na lista abaixo.',
                ],
            'delete_cta'              => 'Excluir',
            'edit_add_bank_account'   =>
                [
                    'form'            =>
                        [
                            'account_number' =>
                                [
                                    'label' => 'Número da conta',
                                ],
                            'add_cta'        => 'Adicionar',
                            'bank'           =>
                                [
                                    'label'       => 'Banco',
                                    'placeholder' => 'Escolha seu banco',
                                ],
                            'cancel_cta'     => 'Cancelar',
                            'edit_cta'       => 'Atualizar',
                        ],
                    'intro'           => 'Preencha o formulário com os dados bancários necessários. Certifique-se de que essas informações estejam corretas para evitar que a transferência seja cancelada, o que pode resultar no não pagamento.',
                    'title'           => 'Conta bancária :type_label',
                    'type_add_label'  => 'Adicionar novo',
                    'type_edit_label' => 'Editar',
                ],
            'edit_cta'                => 'Editar',
            'title'                   => 'Minhas contas bancárias',
        ],
    'request_fail_try_later' => 'Algo deu errado. Por favor, tente novamente mais tarde.',
];
