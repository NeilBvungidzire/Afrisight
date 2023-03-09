<?php

namespace App\Cint;

use App\CintUser;
use App\Person;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Cint {

    /**
     * @var bool
     */
    private $allowedSync = false;

    /**
     * @var Person|null
     */
    private $person = null;

    /**
     * @var CintUser|null
     *
     * @todo Maybe only retrieving the ID via DB class???
     */
    private $cintUser = null;

    /**
     * @var CintApi
     */
    private $client;

    /**
     * @param Person $person
     *
     * @return self
     */
    public function initialize(Person $person): Cint
    {
        $this->person = $person;

        $this->setAllowedSync();
        $this->setCintUser();
        $this->setClient();

        return $this;
    }

    /**
     * Decide if this member can be synced with Cint.
     */
    private function setAllowedSync(): void
    {
        $this->allowedSync = true;
    }

    /**
     * Retrieve and set CintUser
     */
    private function setCintUser(): void
    {
        if ( ! $this->person) {
            $this->cintUser = null;

            return;
        }

        if ( ! is_null($this->cintUser)) {
            return;
        }

        // Try to find the CintUser in the DB.
        $cintUser = CintUser::where('person_id', $this->person->id)->first();

        if ($cintUser) {
            $this->cintUser = $cintUser;

            return;
        }

        $cintUser = CintUser::create([
            'person_id'    => $this->person->id,
            'allowed_sync' => $this->allowedSync,
        ]);
        if ($cintUser) {
            $this->cintUser = $cintUser;

            return;
        }

        $this->cintUser = null;
    }

    /**
     *
     */
    private function setClient(): void
    {
        $this->client = new CintApi();
    }

    /**
     * @param bool $needVerifiedEmail
     *
     * @return bool
     */
    public function syncPanelist(bool $needVerifiedEmail = true): bool
    {
        if ( ! $this->cintUser) {
            return false;
        }

        if ( ! $this->allowedSync) {
            return false;
        }

        if ( ! $this->person->country) {
            return false;
        }

        if ( ! $this->person->user) {
            return false;
        }

        if ($needVerifiedEmail && ! $this->person->user->email_verified_at) {
            return false;
        }

        $panelResource = $this->client->retrievePanel($this->person->country->iso_alpha_2);
        if ($panelResource->hasFailed()) {
            return false;
        }

        if ($memberId = $this->cintUser->meta_data['member_id'] ?? null) {
            $panelistResource = $panelResource->retrievePanelistByMemberId($memberId);
        } else {
            $panelistResource = $panelResource->retrievePanelistByEmail($this->person->email);
        }
        $panelist = null;
        $panelistResource
            ->retrievePanelist()
            ->getResource(function ($successful, $content) use (&$panelist) {
                if ($successful && isset($content['panelist'])) {
                    $panelist = $content['panelist'];
                }
            });

        // Couldn't find a panelist on Cint platform. Now create new one with our data.
        $data = $this->mapToCintData($this->person);
        if ( ! $panelist) {
            $panelResource->resetSuccessState()
                ->addPanelist($data)
                ->getResource(function ($successful, $content) use (&$panelist) {
                    if ($successful) {
                        $panelist = $content['panelist'];
                    }
                });
        }

        // Found a panelist on Cint platform. Now update it with our data and retrieve this new one.
        if ($panelist) {
            $panelistResource->updatePanelist($data)
                ->retrievePanelist()
                ->getResource(function ($successful, $content) use (&$panelist) {
                    if ($successful && isset($content['panelist'])) {
                        $panelist = $content['panelist'];
                    }
                });
        }

        if ( ! $panelist) {
            return false;
        }

        return $this->cintUser->update([
            'cint_id'        => (int)$panelist['key'],
            'meta_data'      => $panelist,
            'reward_balance' => $this->retrieveRewardBalanceAmount($panelist),
        ]);
    }

    public function deletePanelist(): bool
    {
        if ( ! $this->allowedSync) {
            return false;
        }

        if ( ! $this->person->country) {
            return false;
        }

        if ( ! $this->cintUser) {
            return false;
        }

        $panelResource = $this->client->retrievePanel($this->person->country->iso_alpha_2);
        if ($panelResource->hasFailed()) {
            return false;
        }

        $panelistResource = $panelResource->retrievePanelistByEmail($this->person->email);
        if ($panelistResource->hasFailed()) {
            return false;
        }

        return ( ! $panelistResource->deletePanelist()->hasFailed() && $this->cintUser->delete());
    }

    /**
     * @return array
     */
    public function getSurveyOpportunities(): array
    {
        if ( ! $this->allowedSync) {
            return [];
        }

        if ( ! $this->person->country) {
            return [];
        }

        if ( ! $this->cintUser) {
            return [];
        }

        $panelResource = $this->client->retrievePanel($this->person->country->iso_alpha_2);
        if ($panelResource->hasFailed()) {
            return [];
        }

        $panelistResource = $panelResource->retrievePanelistByEmail($this->person->email);
        if ($panelistResource->hasFailed()) {
            return [];
        }

        // Also update the CintUser with the retrieved panelist data, so we can update the money balance.
        $panelist = null;
        $panelistResource
            ->retrievePanelist()
            ->getResource(function ($successful, $content) use (&$panelist) {
                if ($successful && isset($content['panelist'])) {
                    $panelist = $content['panelist'];
                }
            });

        if ($panelist) {
            $this->cintUser->update([
                'cint_id'   => (int)$panelist['key'],
                'meta_data' => $panelist,
                'reward_balance' => $this->retrieveRewardBalanceAmount($panelist),
            ]);
        }

        $surveysAvailable = [];
        $panelistResource->retrieveSurveyOpportunities()
            ->getResource(function ($successful, $surveyOpportunities) use (&$surveysAvailable) {
                if ( ! $successful) {
                    return;
                }

                foreach ($surveyOpportunities as $surveyOpportunity) {
                    $surveysAvailable[] = [
                        'length_of_interview' => data_get($surveyOpportunity, 'length_of_interview', null),
                        'survey_link'         => data_get($surveyOpportunity, 'links.0.href', null),
                        'incentive'           => [
                            'amount'   => data_get($surveyOpportunity, 'incentive.amount', null),
                            'currency' => data_get($surveyOpportunity, 'incentive.currency', null),
                        ],
                    ];
                }
            });

        return $surveysAvailable;
    }

    /**
     * @param array $panelistData
     *
     * @return float
     */
    private function retrieveRewardBalanceAmount(array $panelistData)
    {
        $string = '0.00 USD';

        if ($panelistData) {
            $string = data_get($panelistData, 'rewards.rewards_balance.balance', $string);
        }

        $exploded = explode(' ', $string);
        $probablyAmount = floatval($exploded[0]);

        if ( ! is_float($probablyAmount)) {
            Log::channel('cint')->warning('Could not format balance from Cint into amount.', $exploded);

            return (float)0;
        }

        return $probablyAmount;
    }

    /**
     * @return int
     */
    public function getCalculatedPoints(): int
    {
        $this->setCintUser();

        if ($this->cintUser) {
            return $this->calculatePointsFromMoney($this->cintUser->meta_data['balance_money']);
        }

        return 0;
    }

    /**
     * @param float $cintIncentiveBalance
     *
     * @return int
     */
    private function calculatePointsFromMoney(float $cintIncentiveBalance): int
    {
        // @todo Calculate points from money and return.
        // @todo If couldn't calculate, log the money amount and CintUser for analysis purposes.

        return 0;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    private function mapToCintData(Person $person): array
    {
        $data = [
            'member_id'     => $person->id,
            'email_address' => $person->email,
            'first_name'    => $person->first_name,
            'last_name'     => $person->last_name,
        ];

        if ($person->date_of_birth) {
            try {
                $data['date_of_birth'] = (new Carbon($person->date_of_birth))->format('Y-m-d');
            } catch (Exception $exception) {
                Log::channel('cint')->error('Could not format date of birth', [
                    'person_data' => $person->toArray(),
                ]);
            }
        }

        if ($person->gender_code) {
            $genderMapping = [
                'u' => 'u',
                'w' => 'f',
                'm' => 'm',
            ];

            $data['gender'] = $genderMapping[$person->gender_code] ?? 'u';
        }

        return $data;
    }
}
