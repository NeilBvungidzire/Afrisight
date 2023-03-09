<?php

namespace App\Http\Controllers\Admin\AccountQualityManagement;

use App\Alert\Facades\Alert;
use App\Blacklist;
use App\Constants\BannedBy;
use App\Constants\BlacklistInitiator;
use App\Http\Controllers\Controller;
use App\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class EmailBlacklistController extends Controller {

    public function index() {
        $this->authorize('account-admin');

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 10;

        $blackists = Blacklist::query()
            ->where('banned_by', BannedBy::EMAIL)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return view('admin.account-quality.email-blacklist.index', compact('blackists'));
    }

    public function search() {
        $this->authorize('account-admin');

        $countries = cache()->remember('COUNTRIES_BY_ID_NAME', now()->addDays(30), function () {
            return DB::table('countries')->pluck('name', 'id')->toArray();
        });
        $countries['empty'] = 'Not Set';

        $boxOptions = [
            'person_id'     => 'Person ID',
            'account_email' => 'Bank account email (can be partially)',
            'bank_email'    => 'Bank account email (must be exact)',
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

        // By username
        $query = Person::withTrashed();

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

        if ($boxType === 'account_email' && $values = $this->cleanString($boxValue)) {
            $query->where(static function (Builder $builder) use ($values) {
                foreach ($values as $value) {
                    $builder->orWhere('email', 'like', "%${value}%");
                }
            });
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

        // Custom limit the numbers of records retrieved.
        $limit = request()->query('limit') ?? 10;

        $persons = $query->paginate($limit);

        return view('admin.account-quality.email-blacklist.search', compact('persons', 'countries', 'filters'));
    }

    public function submitSearchForm(): RedirectResponse {
        $this->authorize('account-admin');

        $filters = request()->all(['country', 'box_type', 'box_value']);

        return redirect()->route('admin.account-quality.email-blacklist.search', $filters);
    }

    public function createBlacklist(): RedirectResponse {
        if ( ! $id = request()->query('id')) {
            Alert::makeWarning('Person ID not set!');

            return redirect()->back();
        }

        if ( ! $person = Person::find($id)) {
            Alert::makeWarning("Could not find account with person ID ${id}! It can be because it does not exist or was already banned.");

            return redirect()->back();
        }

        $blacklist = Blacklist::query()
            ->where('banned_by', BannedBy::EMAIL)
            ->where('related_data', ['email' => $person->email])
            ->first();

        if ($blacklist) {
            $ids = (array) $blacklist->banned_person_ids;
            $blacklist->banned_person_ids = array_unique(array_merge($ids, [$id]));
            $blacklist->save();
        } else {
            Blacklist::create([
                'banned_by'         => BannedBy::EMAIL,
                'related_data'      => ['email' => $person->email],
                'banned_person_ids' => [$id],
                'initiator'         => BlacklistInitiator::ADMINISTRATOR,
            ]);
        }

        $this->deleteAccount($person);

        Alert::makeSuccess("Account with person ID ${id} is banned successfully.");

        return redirect()->back();
    }

    private function cleanString(string $subject): ?array {
        return array_filter(explode(',', str_replace(' ', '', $subject))) ?: null;
    }

    private function deleteAccount(Person $person): void {
        DB::transaction(static function () use ($person) {
            $person->delete();
            $person->user->delete();

            if ($person->user->socialAccounts->count()) {
                $person->user->socialAccounts()->delete();
            }
        });
    }
}
