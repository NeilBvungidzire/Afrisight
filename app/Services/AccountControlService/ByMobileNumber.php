<?php

namespace App\Services\AccountControlService;

use App\Blacklist;
use App\Constants\BannedBy;
use App\Person;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ByMobileNumber {

    /**
     * Check if minimal one of this persons bank accounts is in the list of banned bank accounts. If this person is
     * already banned, it might not be able to check this, because the bank accounts of this person might already have
     * been removed.
     *
     * @param  string  $mobileNumber  Requires the mobile number being according to format E.164
     * (https://en.wikipedia.org/wiki/E.164).
     * @return bool
     */
    public function isBanned(string $mobileNumber): bool {
        return DB::table('blacklists')
            ->where('banned_by', BannedBy::MOBILE_NUMBER)
            ->whereJsonContains('related_data', [
                'mobile_number' => $mobileNumber,
            ])
            ->where('deleted_at', null)
            ->exists();
    }

    /**
     * @param  string  $initiator
     * @param  string  $mobileNumber  Requires the mobile number being according to format E.164
     * (https://en.wikipedia.org/wiki/E.164).
     * @param  int|string|null  $personId
     * @return void
     */
    public function ban(string $initiator, string $mobileNumber, $personId = null): void {
        if ( ! $personData = $this->getPersonData($personId)) {
            return;
        }

        if ( ! $personCountryCode = $this->getPersonCountryCode($personData->country_id)) {
            return;
        }

        $saved = $this->addToBlacklist(
            $personId,
            $initiator,
            $personCountryCode,
            $mobileNumber,
            $personData->email
        );

        if ($saved) {
            $this->deleteAccount($personId);
        }
    }

    public function getBannedQueryBuilder(): Builder {
        return Blacklist::query()
            ->where('banned_by', BannedBy::MOBILE_NUMBER)
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
        if ( ! $person = Person::find($personId)) {
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
     * @return object|null
     */
    private function getPersonData(int $personId) {
        $result = DB::table('persons')
            ->where('id', $personId)
            ->first(['email', 'country_id']);

        if ( ! $result) {
            return null;
        }

        return $result;
    }

    /**
     * @param  int|string  $countryId
     * @return string|null
     */
    private function getPersonCountryCode($countryId): ?string {
        try {
            return cache()->remember('COUNTRY_CODE_BY_ID_' . $countryId, now()->addMonth(),
                function () use ($countryId) {
                    return DB::table('countries')
                        ->where('id', $countryId)
                        ->value('iso_alpha_2');
                });
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param  int  $personId
     * @param  string  $initiator
     * @param  string  $countryCode
     * @param  string  $mobileNumber
     * @param  string|null  $personEmail
     * @return bool
     */
    private function addToBlacklist(
        int $personId,
        string $initiator,
        string $countryCode,
        string $mobileNumber,
        string $personEmail = null
    ): bool {
        $relatedData = [
            'country_code'  => $countryCode,
            'mobile_number' => $mobileNumber,
        ];
        if ($personEmail) {
            $relatedData['email'] = $personEmail;
        }

        $data = [
            'banned_by'         => BannedBy::MOBILE_NUMBER,
            'related_data'      => json_encode($relatedData),
            'banned_person_ids' => json_encode([$personId]),
            'initiator'         => $initiator,
            'created_at'        => Date::now(),
            'updated_at'        => Date::now(),
        ];

        return DB::table('blacklists')->insert($data);
    }
}