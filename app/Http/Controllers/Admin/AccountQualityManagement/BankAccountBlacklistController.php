<?php

namespace App\Http\Controllers\Admin\AccountQualityManagement;

use App\Alert\Facades\Alert;
use App\Constants\BlacklistInitiator;
use App\Http\Controllers\Controller;
use App\Person;
use App\Services\AccountControlService\AccountControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BankAccountBlacklistController extends Controller {

    public function index() {
        $this->authorize('account-admin');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 10;

        $blackists = AccountControlService::byBankAccount()
            ->getBannedQueryBuilder()
            ->paginate($limit);

        return view('admin.account-quality.bank-account-blacklist.index', compact('blackists'));
    }

    public function findPossibleCases() {
        $possibleCases = AccountControlService::byBankAccount()
            ->findPossibleCases();

        return view('admin.account-quality.bank-account-blacklist.possible-cases', compact('possibleCases'));
    }

    public function search() {
        $this->authorize('account-admin');

        $countries = cache()->remember('COUNTRIES_BY_ID_NAME', now()->addDays(30), function () {
            return DB::table('countries')->pluck('name', 'id')->toArray();
        });
        $countries['empty'] = 'Not Set';

        $boxOptions = [
            'person_id'           => 'Person ID',
            'bank_account_number' => 'Bank account number (must be exact)',
            'bank_email'          => 'Bank account email (must be exact)',
        ];

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
                'options'       => $boxOptions,
            ],
            'box_value' => [
                'label'         => 'Value',
                'type'          => 'text',
                'current_value' => request()->query('box_value'),
            ],
        ];

        $backgroundFilter = array_filter(request()->all([
            'country_code',
            'bank_code',
            'account_number',
        ]));
        $useBackgroundFilter = count($backgroundFilter) === 3;

        // By username
        $query = Person::withTrashed();

        if ($useBackgroundFilter) {
            $personsId = DB::table('bank_accounts')
                ->where('country_code', $backgroundFilter['country_code'])
                ->where('bank_code', $backgroundFilter['bank_code'])
                ->where('account_number', $backgroundFilter['account_number'])
                ->pluck('person_id');

            $query->whereIn('id', $personsId);
        } else {
            // By country
            if ( ! empty($filters['country']['current_value'])) {
                $value = ($filters['country']['current_value'] === 'empty') ? null : $filters['country']['current_value'];
                $query->where('country_id', $value);
            }

            // Transaction for specific box filtering
            $boxType = null;
            $boxValue = null;
            if ( ! empty($filters['box_type']['current_value']) && ! empty($filters['box_value']['current_value'])) {
                $boxType = $filters['box_type']['current_value'] ?? null;
                $boxValue = $filters['box_value']['current_value'] ?? null;
            }

            $values = null;
            if ($boxType === 'person_id' && $values = $this->cleanString($boxValue)) {
                $query->whereIn('id', $values);
            }

            if ($boxType === 'bank_account_number' && $values = $this->cleanString($boxValue)) {
                $personsId = DB::table('bank_accounts')
                    ->whereIn('account_number', $values)
                    ->pluck('person_id');

                $query->whereIn('id', $personsId);
            }

            if ($boxType === 'bank_email' && $values = $this->cleanString($boxValue)) {
                $personsId = DB::table('bank_accounts')
                    ->where(static function ($builder) use ($values) {
                        foreach ($values as $value) {
                            $builder->orWhereJsonContains('meta_data->email', $value);
                        }
                    })
                    ->pluck('person_id');

                $query->whereIn('id', $personsId);
            }

            if (empty($boxType) || empty($values)) {
                $query->where('id', null);
            }
        }

        $query->with('bankAccounts');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 10;

        $persons = $query->paginate($limit);

        return view('admin.account-quality.bank-account-blacklist.search', compact('persons',
            'countries', 'filters'));
    }

    public function filter(): RedirectResponse {
        $this->authorize('account-admin');

        $filters = request()->all(['country', 'box_type', 'box_value']);

        return redirect()->route('admin.account-quality.bank-account-blacklist.search', $filters);
    }

    public function createBlacklist(): RedirectResponse {
        $required = [
            'id',
            'country_code',
            'bank_code',
            'account_number',
        ];
        $params = array_filter(request()->all($required));
        if (count($params) !== count($required)) {
            Alert::makeWarning('Required parameters are not set!');

            return redirect()->back();
        }

        if ( ! $person = Person::find($params['id'])) {
            Alert::makeWarning("Could not find account with person ID {$params['id']}! It can be because it does not exist or was already banned.");

            return redirect()->back();
        }

        AccountControlService::byBankAccount()->ban(
            BlacklistInitiator::ADMINISTRATOR,
            $params['country_code'],
            $params['bank_code'],
            $params['account_number'],
            [$params['id']]
        );

        Alert::makeSuccess("Account with person ID {$person->id} is banned successfully.");

        return redirect()->back();
    }

    private function cleanString(string $subject): ?array {
        return array_filter(explode(',', str_replace(' ', '', $subject))) ?: null;
    }
}
