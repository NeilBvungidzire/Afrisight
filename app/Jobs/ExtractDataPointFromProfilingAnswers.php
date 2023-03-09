<?php

namespace App\Jobs;

use App\Services\DataPointService\DataPointService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ExtractDataPointFromProfilingAnswers implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $personId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($personId)
    {
        $this->personId = $personId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $dataPointService = new DataPointService();

        $subdivisionCode = $dataPointService->extract()->getSubdivisionCodeDataPoint($this->personId);
        if ( ! empty($subdivisionCode)) {
            $dataPointService->place()->setSubdivisionCodeDataPoint($this->personId, $subdivisionCode, 'PROFILING_QUESTIONNAIRE');
        }
    }

    public function tags(): array
    {
        return [
            'ExtractDataPointFromProfilingAnswers',
        ];
    }
}
