<?php

namespace App\Jobs;

use App\Cint\Facades\Cint;
use App\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCintUser implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * Create a new job instance.
     *
     * @param Person $person
     * @param bool $strict
     */
    public function __construct(Person $person, bool $strict = true)
    {
        $this->person = $person;
        $this->strict = $strict;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Cint::initialize($this->person)->syncPanelist($this->strict);
    }

    public function tags()
    {
        return [
            'SyncCintUser',
            'SyncCintUserPerson:' . $this->person->id,
        ];
    }
}
