<?php

namespace App\Jobs;

use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportTransaction implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $personId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $initiator;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $newStatus;

    /**
     * @var array
     */
    protected $metaData;

    /**
     * Create a new job instance.
     *
     * @param int $personId
     * @param string $type
     * @param string $initiator
     * @param float $amount
     * @param string $newStatus
     * @param array $metaData
     */
    public function __construct(
        int $personId,
        string $type,
        string $initiator,
        float $amount,
        string $newStatus,
        array $metaData = []
    ) {
        $this->personId = $personId;
        $this->type = $type;
        $this->initiator = $initiator;
        $this->amount = $amount;
        $this->newStatus = $newStatus;
        $this->metaData = $metaData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Transaction::create([
            'person_id'  => $this->personId,
            'type'       => $this->type,
            'initiator'  => $this->initiator,
            'amount'     => $this->amount,
            'new_status' => $this->newStatus,
            'meta_data'  => $this->metaData,
        ]);
    }
}
