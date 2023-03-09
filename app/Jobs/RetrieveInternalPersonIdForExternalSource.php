<?php

namespace App\Jobs;

use App\Cint\CintApi;
use App\CintUser;
use App\Constants\Currency;
use App\ExternalRespondent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class RetrieveInternalPersonIdForExternalSource implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $externalRespondentId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $externalRespondentId)
    {
        $this->externalRespondentId = $externalRespondentId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ( ! $externalRespondent = ExternalRespondent::find($this->externalRespondentId)) {
            return;
        }

        $projectCountryCode = DB::table('projects')
            ->where('project_code', $externalRespondent->project_code)
            ->value('country_code');
        if ( ! $projectCountryCode) {
            return;
        }

        $respondentData = null;
        (new CintApi())
            ->retrieveRespondentByGuid($projectCountryCode, $externalRespondent->external_id)
            ->getResource(function ($successful, $content) use (&$respondentData) {
                if ( ! $successful) {
                    return;
                }

                $respondentData = $content['panel_respondent'] ?? null;
            });
        if ( ! $respondentData) {
            return;
        }

        if ( ! $cintId = $respondentData['panelist_id'] ?? null) {
            return;
        }
        $personId = CintUser::query()
            ->where('cint_id', $cintId)
            ->value('person_id');
        if ( ! $personId) {
            return;
        }

        $currency = $respondentData['incentives']['currency'] ?? null;
        $amount = $respondentData['incentives']['amount'] ?? null;
        $usdAmount = (strtoupper($currency) === Currency::USD && $amount)
            ? $amount
            : null;

        $externalRespondent->person_id = $personId;
        $metaData = $externalRespondent->meta_data;
        $metaData = array_merge((array)$metaData, [
            'loi'        => $respondentData['loi'] ?? null,
            'ir'         => $respondentData['ir'] ?? null,
            'usd_amount' => $usdAmount,
        ]);
        $externalRespondent->meta_data = $metaData;
        $externalRespondent->save();
    }
}
