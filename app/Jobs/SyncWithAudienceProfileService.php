<?php

namespace App\Jobs;

use App\Services\AudienceProfileService\AudienceProfileService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncWithAudienceProfileService implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $personId;

    /**
     * @var array
     */
    protected $datapoints;

    /**
     * @var string
     */
    protected $action;

    /**
     * Create a new job instance.
     *
     * @param  int  $personId
     * @param  array  $datapoints
     * @param  string  $action
     */
    public function __construct(int $personId, array $datapoints = [], string $action = 'save') {
        $this->personId = $personId;
        $this->datapoints = $datapoints;
        $this->action = $action;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        $audienceProfileService = new AudienceProfileService();

        switch ($this->action) {
            case 'save':
                $audienceProfileService->storeSinglePersonDatapoints($this->personId, ['base' => $this->datapoints]);
                break;

            case 'delete':
                $audienceProfileService->deleteSinglePersonDatapoints($this->personId);
                break;
        }
    }

    public function tags(): array {
        return [
            'SyncWithAudienceProfileService',
        ];
    }
}
