<?php

namespace App\Http\Controllers\Admin\RewardManagement;

use App\Alert\Facades\Alert;
use App\Cint\Facades\Cint;
use App\Constants\TransactionType;
use App\Country;
use App\Http\Controllers\Admin\BaseController;
use App\Person;
use App\Respondent;
use App\Services\AccountService\AccountService;
use App\Services\AccountService\Constants\Balances;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountBalanceController extends BaseController {

    public function index()
    {
        $this->authorize('reward-management');

        $boxOptions = [
            'project_code',
            'respondent_id',
            'person_id',
            'email',
            'actual_balance',
        ];

        $countries = cache()->remember('COUNTRIES_BY_ID_NAME', now()->addDays(30), function () {
            return DB::table('countries')->pluck('name', 'id')->toArray();
        });

        $filters = [
            'country'   => [
                'label'         => 'Country (all)',
                'type'          => 'select',
                'current_value' => request()->query('country'),
                'options'       => $countries,
            ],
            'box_type'  => [
                'label'         => 'Specific (none)',
                'type'          => 'select',
                'current_value' => request()->query('box_type'),
                'options'       => array_combine($boxOptions, $boxOptions),
            ],
            'box_value' => [
                'label'         => 'Value',
                'type'          => 'text',
                'current_value' => request()->query('box_value'),
            ],
        ];

        $personQuery = Person::withTrashed();

        if ( ! empty($filters['country']['current_value'])) {
            $personQuery->where('country_id', $filters['country']['current_value']);
        }

        // Specific box filtering
        if ( ! empty($filters['box_type']['current_value']) && $filters['box_value']['current_value'] !== null) {
            $boxType = $filters['box_type']['current_value'];
            $boxValue = $filters['box_value']['current_value'];

            $personsId = null;
            if ($boxType === 'project_code') {
                $personsId = DB::table('respondents')
                    ->where('project_code', $boxValue)
                    ->pluck('person_id')
                    ->toArray();
            }

            if ($boxType === 'respondent_id') {
                $personsId = DB::table('respondents')
                    ->where('id', $boxValue)
                    ->pluck('person_id')
                    ->toArray();
            }

            if (in_array($boxType, ['project_code', 'respondent_id'])) {
                if (empty($personsId)) {
                    // Avoid getting all when no person ID is found.
                    $personQuery->where('id', null);
                } else {
                    $personQuery->whereIn('id', $personsId);
                }
            }

            if ($boxType === 'email') {
                $personQuery->where('email', $boxValue);
            }

            if ($boxType === 'person_id') {
                $personQuery->where('id', $boxValue);
            }

            if ($boxType === 'actual_balance') {
                if (Str::contains($boxValue, '<')) {
                    $value = (float)Str::replaceFirst('<', '', $boxValue);
                    $personQuery->where('reward_balance', '<', $value);
                } elseif (Str::contains($boxValue, '>')) {
                    $value = (float)Str::replaceFirst('>', '', $boxValue);
                    $personQuery->where('reward_balance', '>', $value);
                } else {
                    $personQuery->where('reward_balance', '=', (float)$boxValue);
                }
            }
        }

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 30;

        $persons = $personQuery->paginate($limit);

        foreach ($persons as $person) {
            $accountService = new AccountService($person);
            $person->calculatedBalance = $accountService->getBalance(true);
        }

        return view('admin.account_balance.index', compact('persons', 'filters', 'countries'));
    }

    public function filter()
    {
        $this->authorize('reward-management');

        $filters = request()->all(['box_type', 'box_value', 'country', 'actual_balance']);

        return redirect()->route('admin.reward_management.balance', $filters);
    }

    public function syncCintBalance(int $personId)
    {
        $this->authorize('reward-management');

        $person = Person::withTrashed()->find($personId);

        if ( ! $person) {
            Alert::makeDanger("Could not sync person balance on Cint. Please note this person's ID, which is ${personId}, and contact us.");

            return redirect()->back();
        }

        if (Cint::initialize($person)->syncPanelist(false)) {
            Alert::makeSuccess('Person balance is now being synced with Cint.');
        } else {
            Alert::makeDanger("Could not sync person balance on Cint. Please note this person's ID, which is ${personId}, and contact us.");
        }

        // Refresh cached amounts.
        AccountService::clearCachedBalance($personId, Balances::CINT);

        return redirect()->back();
    }

    public function view(int $personId)
    {
        $this->authorize('reward-management');

        $person = Person::withTrashed()
            ->with('user')
            ->with([
                'transactions' => function ($query) {
                    $query->orderBy('updated_at', 'asc');
                },
            ])
            ->with([
                'respondent' => function ($query) {
                    $query->orderBy('updated_at', 'asc');
                },
            ])
            ->where('id', $personId)
            ->first();

        $accountService = new AccountService($person);
        $person->calculatedBalance = $accountService->getBalance(true);
        $person->cintBalance = $accountService->getBalance(true, Balances::CINT);

        $countries = cache()->remember('COUNTRIES_BY_ID_NAME', now()->addDays(30), function () {
            return Country::query()->pluck('name', 'id')->toArray();
        });

        foreach ($person->transactions as $transaction) {
            if ($transaction->type !== TransactionType::SURVEY_REWARDING) {
                continue;
            }

            $projectCode = $transaction->meta_data['project_code'] ?? null;
            if ($projectCode) {
                $transaction->project_code = $projectCode;
                continue;
            }

            $respondentId = $transaction->meta_data['respondent_id'] ?? null;
            if ( ! $respondentId) {
                continue;
            }

            $projectCode = Respondent::query()
                ->where('id', $respondentId)
                ->value('project_code');
            if ( ! $projectCode) {
                continue;
            }

            $transaction->project_code = $projectCode;
        }

        return view('admin.account_balance.view', compact('person', 'countries'));
    }
}
