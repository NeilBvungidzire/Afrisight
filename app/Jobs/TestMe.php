<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class TestMe implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $key = "test_throttling";
        \Illuminate\Support\Facades\Redis::throttle($key)->allow(1)->every(120)->then(function () {
            $this->runJob();
        }, function () {
            // Could not obtain lock...
            $this->release();
        });
    }

    private function runJob(): void
    {
        Log::channel('testing')->info("Run TestMe job with ID: {$this->id}");
    }
}
