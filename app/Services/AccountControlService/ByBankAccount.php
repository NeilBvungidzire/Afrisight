<?php

namespace App\Services\AccountControlService;

use App\Blacklist;
use App\Constants\BannedBy;
use App\Person;
use App\Services\AccountService\Constants\PayoutMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ByBankAccount {

    /**
     * Check if minimal one of this persons bank accounts is in the list of banned bank accounts. If this person is
     * already banned, it might not be able to check this, because the bank accounts of this person might already have
     * been removed.
     *
     * @param  int  $personId  Person ID.
     * @return bool
     */
    public function isBanned(int $personId): bool {
        $bankAccounts = $this->getPersonBankAccounts($personId);
        if ($bankAccounts->isEmpty()) {
            return false;
        }

        foreach ($bankAccounts as $bankAccount) {
            $found = DB::table('blacklists')
                ->where('banned_by', BannedBy::BANK_ACCOUNT)
                ->whereJsonContains('related_data', [
                    'bank_code'      => $bankAccount->bank_code,
                    'country_code'   => $bankAccount->country_code,
                    'account_number' => $bankAccount->account_number,
                ])
                ->where('deleted_at', null)
                ->exists();

            if ($found) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string  $initiator
     * @param  string  $countryCode
     * @param  string  $bankCode
     * @param  string  $accountNumber
     * @param  bool|string[]|int[]  $toBanPersonIds  Whether to ban the specific person IDs only or not. If false, it
     * will ban the bank account, but none of the persons. If true, it will ban this bank account and all persons with
     * this bank account.
     * @return void
     */
    public function ban(
        string $initiator,
        string $countryCode,
        string $bankCode,
        string $accountNumber,
        $toBanPersonIds = false
    ): void {
        $personIdsToBan = [];
        if (is_array($toBanPersonIds)) {
            $personIdsToBan = $toBanPersonIds;
        } elseif ($toBanPersonIds === true) {
            $personIdsToBan = DB::table('bank_accounts')
                ->where('country_code', $countryCode)
                ->where('bank_code', $bankCode)
                ->where('account_number', $accountNumber)
                ->pluck('person_id')
                ->toArray();
            $personIdsToBan = array_values(array_unique($personIdsToBan));
        }

        $data = [
            'banned_by'         => BannedBy::BANK_ACCOUNT,
            'related_data'      => json_encode([
                'country_code'   => $countryCode,
                'bank_code'      => $bankCode,
                'account_number' => $accountNumber,
            ]),
            'banned_person_ids' => $personIdsToBan ? json_encode($personIdsToBan) : null,
            'initiator'         => $initiator,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];

        $saved = DB::table('blacklists')->insert($data);
        if ( ! $saved) {
            return;
        }

        foreach ($personIdsToBan as $personId) {
            $this->deleteAccount($personId);
        }
    }

    public function getBannedQueryBuilder(): Builder {
        return Blacklist::query()
            ->where('banned_by', BannedBy::BANK_ACCOUNT)
            ->orderBy('created_at', 'desc');
    }

    public function findPossibleCases(): Collection {
        return DB::table('bank_accounts')
            ->selectRaw(DB::raw('country_code, bank_code, account_number, count(*) AS cases'))
            ->where('deleted_at', null)
            ->groupBy([
                'country_code',
                'bank_code',
                'account_number',
            ])
            ->having('cases', '>', 1)
            ->orderByDesc('cases')
            ->get();
    }

    private function deleteAccount(int $personId): void {
        if ( ! $person = Person::with(['user', 'user.socialAccounts'])->find($personId)) {
            return;
        }

        DB::transaction(static function () use ($person) {
            $person->delete();
            $person->user->delete();

            if ($person->user->socialAccounts->count()) {
                $person->user->socialAccounts()->delete();
            }
        });
    }

    /**
     * @param  int  $personId
     * @return Collection
     */
    private function getPersonBankAccounts(int $personId): Collection {
        return DB::table('bank_accounts')
            ->where('type', PayoutMethod::BANK_ACCOUNT)
            ->where('person_id', $personId)
            ->where('deleted_at', null)
            ->get(['country_code', 'bank_code', 'account_number']);
    }

    /**
     * @param  int  $personId
     * @return string|null
     */
    private function getPersonEmail(int $personId): ?string {
        return DB::table('persons')
            ->where('id', $personId)
            ->value('email');
    }
}