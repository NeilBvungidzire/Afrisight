<?php

namespace App\Http\Controllers\Admin\RewardManagement;

use App\Constants\RespondentStatus;
use App\Constants\TransactionType;
use App\Http\Controllers\Admin\BaseController;
use App\Libraries\Payout\Constants\TransactionStatus;
use App\Person;
use App\Respondent;
use App\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GrantingController extends BaseController {

    public function index()
    {
        $this->authorize('reward-management');

        $typesOptions = TransactionType::getRewardingTypeConstants();
        $box1Options = [
            'project_code',
            'respondent_id',
            'person_id',
            'email',
            'uuid',
        ];

        $filters = [
            'type'      => [
                'label'         => 'Transaction type (all)',
                'type'          => 'select',
                'current_value' => request()->query('type'),
                'options'       => array_combine($typesOptions, $typesOptions),
            ],
            'status'    => [
                'label'         => 'Transaction status (all)',
                'type'          => 'select',
                'current_value' => request()->query('status'),
                'options'       => TransactionStatus::getConstants(),
            ],
            'box_type'  => [
                'label'         => 'Specific (none)',
                'type'          => 'select',
                'current_value' => request()->query('box_type'),
                'options'       => array_combine($box1Options, $box1Options),
            ],
            'box_value' => [
                'label'         => 'Value',
                'type'          => 'text',
                'current_value' => request()->query('box_value'),
            ],
        ];

        $transactionsQuery = Transaction::query();

        // Transaction type filtering
        $transactionsQuery->whereIn('type', empty($filters['type']['current_value'])
            ? $typesOptions
            : [$filters['type']['current_value']]);

        // Transaction status filtering
        $transactionsQuery->whereIn('status', empty($filters['status']['current_value'])
            ? TransactionStatus::getConstants()
            : [$filters['status']['current_value']]);

        // Transaction for specific box filtering
        if ( ! empty($filters['box_type']['current_value']) && ! empty($filters['box_value']['current_value'])) {
            $boxType = $filters['box_type']['current_value'];
            $boxValue = $filters['box_value']['current_value'];

            $respondentsId = null;
            if ($boxType === 'project_code') {
                $respondentsId = Respondent::query()
                    ->whereIn('current_status', [
                        RespondentStatus::COMPLETED,
                        RespondentStatus::POST_DISQUALIFIED,
                    ])
                    ->where('project_code', $boxValue)
                    ->pluck('id')
                    ->toArray();
            } elseif ($boxType === 'respondent_id') {
                $respondentsId = [$boxValue];
            } elseif ($boxType === 'uuid') {
                $respondentsId = Respondent::query()
                    ->where('current_status', RespondentStatus::COMPLETED)
                    ->where('uuid', $boxValue)
                    ->pluck('id')
                    ->toArray();
            }

            if (in_array($boxType, ['project_code', 'respondent_id', 'uuid'])) {
                if (empty($respondentsId)) {
                    // Avoid getting all when no person ID is found.
                    $transactionsQuery->where('person_id', null);
                } else {
                    $transactionsQuery->where(function (Builder $query) use ($respondentsId) {
                        foreach ($respondentsId as $id) {
                            $query->orWhereJsonContains('meta_data->respondent_id', (int)$id);
                        }
                    });
                }
            }

            if ($boxType === 'email') {
                $personId = Person::query()->where('email', $boxValue)->value('id');
                $transactionsQuery->where('person_id', $personId);
            }
            if ($boxType === 'person_id') {
                $transactionsQuery->where('person_id', $boxValue);
            }
        }

        // Order
        $transactionsQuery->orderBy('updated_at', 'desc');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 30;

        $transactions = $transactionsQuery->paginate($limit);

        return view('admin.reward-management.granting', compact('transactions', 'filters'));
    }

    public function filterTransactions(): RedirectResponse
    {
        $this->authorize('reward-management');

        $filters = request()->all(['type', 'status', 'box_type', 'box_value']);

        return redirect()->route('admin.reward_management.granting', $filters);
    }

    public function editTransaction(int $id)
    {
        $this->authorize('reward-management');

        $transaction = Transaction::find($id);

        if ( ! $transaction) {
            return redirect()->back();
        }

        $statuses = TransactionStatus::getConstants();
        $types = TransactionType::getRewardingTypeConstants();
        $types = array_combine($types, $types);

        return view('admin.reward-management.edit-reward-transactions', compact('transaction', 'statuses', 'types'));
    }

    public function updateTransaction(int $id)
    {
        $this->authorize('reward-management');

        $data = request()->all([
            'amount',
            'new_status',
            'type',
        ]);

        $types = TransactionType::getRewardingTypeConstants();

        Validator::make($data, [
            'amount'     => ['required', 'numeric'],
            'new_status' => ['required', Rule::in(TransactionStatus::getConstants())],
            'type'       => ['required', Rule::in($types)],
        ])->validate();

        $transaction = Transaction::find($id);
        if ( ! $transaction) {
            return redirect()->back();
        }

        $transaction->update($data);

        return redirect()->route('admin.reward_management.granting');
    }
}
