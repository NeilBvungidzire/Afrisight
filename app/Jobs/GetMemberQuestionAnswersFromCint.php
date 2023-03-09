<?php

namespace App\Jobs;

use App\Cint\CintApi;
use App\CintMemberQuestionAnswer;
use App\Person;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetMemberQuestionAnswersFromCint implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Person
     */
    private $person;

    /**
     * Create a new job instance.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        $client = new CintApi();

        $panelResource = $client->retrievePanel($this->person->country->iso_alpha_2);
        if ($panelResource->hasFailed()) {
            return null;
        }

        $panelistResource = $panelResource->retrievePanelistByEmail($this->person->email);
        if ($panelistResource->hasFailed()) {
            return null;
        } else {
            $panelistVariables = null;
            $panelistResource
                ->retrieveVariables()
                ->getResource(function ($successful, $content) use (&$panelistVariables) {
                    if ($successful && isset($content)) {
                        $panelistVariables = $content;
                    }
                });

            try {
                CintMemberQuestionAnswer::create([
                    'person_id' => $this->person->id,
                    'answers'   => $panelistVariables,
                ]);
            } catch (Exception $exception) {
                Log::error('Could not save member question answers retrieved from Cint.', [
                    'person'         => $this->person->toArray(),
                    'retrieved_data' => $panelistVariables,
                ]);
            }
        }
    }

    private function checkRequirements()
    {

    }
}
