<?php

namespace App\Console\Commands;

use App\Country;
use App\Person;
use Illuminate\Console\Command;
use Propaganistas\LaravelPhone\PhoneNumber;

class TryCorrectMobileNumber extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'person:mobile-number';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        [
            $countries,
            $limit,
            $direction,
        ] = $this->getInputs();

        $this->info('Number of persons: ' . $limit);
        $this->info('Direction: ' . $direction);
        $this->table(['ID', 'Code', 'Name'], $countries);
        if ( ! $this->confirm('Is this correct?')) {
            $this->info('Abandoned!');

            return;
        }

        foreach ($countries as $country) {
            $this->processCountry((int)$country['id'], $country['code'], $direction, (int)$limit);
        }

        return;
    }

    /**
     * @param int $countryId
     * @param string $countryCode
     * @param string $direction
     * @param int $limit
     */
    private function processCountry(int $countryId, string $countryCode, string $direction, int $limit)
    {
        $personQuery = Person::query()
            ->whereNull('deleted_at')
            ->where('mobile_number', '!=', '')
            ->where('country_id', '=', $countryId)
            ->orderBy('created_at', $direction);

        if ($limit) {
            $personQuery->limit($limit);
        }

        $persons = $personQuery->get();
        $bar = $this->output->createProgressBar($persons->count());

        $bar->start();
        $this->info('Country Code: ' . $countryCode);
        foreach ($persons as $person) {
            if (empty($person->mobile_number)) {
                continue;
            }

            $generateNumber = (string)PhoneNumber::make($person->mobile_number, $countryCode);

            $person->mobile_number = $generateNumber;
            $person->save();

            $bar->advance();
        }
        $bar->finish();
    }

    /**
     * @return array
     */
    private function getInputs()
    {
        $countryIds = $this->ask('What are the country IDs?');
        $countryIds = explode(',', $countryIds);

        $countries = [];
        Country::query()
            ->whereIn('id', $countryIds)
            ->each(function ($country) use (&$countries) {
                if ($code = $country->iso_alpha_2) {
                    $countries[$country->id] = [
                        'id'   => $country->id,
                        'code' => $code,
                        'name' => $country->name,
                    ];
                }
            });

        $limit = $this->ask('How many persons to target?', 'All');
        $direction = $this->choice('Which direct to go?', [
            1 => 'asc',
            2 => 'desc',
        ], 'asc');

        return [
            $countries,
            $limit,
            $direction,
        ];
    }
}
