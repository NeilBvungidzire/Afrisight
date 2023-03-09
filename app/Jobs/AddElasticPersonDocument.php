<?php

namespace App\Jobs;

use App\Constants\DataPointAttribute;
use App\Country;
use App\DataPoint;
use App\Libraries\Elastic\Elastic;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

class AddElasticPersonDocument implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $documentStructure = [
        'person_id'        => null,
        'gender'           => null,
        'date_of_birth'    => null,
        'language_code'    => [],
        'country_code'     => null,
        'subdivision_code' => null,
        'city_name'        => null,
        'devices'          => null,
    ];

    /**
     * @var string
     */
    protected $index = 'afrisight_persons';

    /**
     * @var array
     */
    protected $persons;

    /**
     * Create a new job instance.
     *
     * @param array $persons
     */
    public function __construct(array $persons)
    {
        $this->persons = $persons;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $personsId = Arr::pluck($this->persons, 'id');

        $datapoints = DataPoint::query()
            ->whereIn('person_id', $personsId)
            ->get()
            ->groupBy(['person_id', 'attribute'])
            ->toArray();

        $documents = [];
        foreach ($this->persons as $person) {
            $personDatapoints = $datapoints[$person['id']] ?? [];
            $documents[] = $this->buildDocument($person, $personDatapoints);
        }

        Log::info('info', $documents);

//        $this->addDocuments($documents, 1);
    }

    private function buildDocument($person, $datapoints)
    {
        $document = $this->documentStructure;

        $countriesByIdCode = $this->getCountryCodes();
        $genders = [
            'm' => 'MALE',
            'w' => 'FEMALE',
        ];

        $countryCode = $countriesByIdCode[$person['country_id']] ?? null;
        if (isset($datapoints[DataPointAttribute::COUNTRY_CODE])) {
            $countryCode = $datapoints[DataPointAttribute::COUNTRY_CODE][0]['value'];
        }
        $document['country_code'] = $countryCode;

        $devices = [];
        if (isset($datapoints[DataPointAttribute::DESKTOP])) {
            $devices[] = DataPointAttribute::DESKTOP;
        }
        if (isset($datapoints[DataPointAttribute::MOBILE])) {
            $devices[] = DataPointAttribute::MOBILE;
        }
        if (isset($datapoints[DataPointAttribute::TABLET])) {
            $devices[] = DataPointAttribute::TABLET;
        }
        $document['devices'] = $devices;

        $cityName = null;
        if (isset($datapoints[DataPointAttribute::CITY_NAME])) {
            $cityName = $datapoints[DataPointAttribute::CITY_NAME][0]['value'];
        }
        $document['city_name'] = $cityName;

        $subdivisionCode = null;
        if (isset($datapoints[DataPointAttribute::SUBDIVISION_CODE])) {
            $subdivisionCode = $datapoints[DataPointAttribute::SUBDIVISION_CODE][0]['value'];
        }
        $document['subdivision_code'] = $subdivisionCode;

        $document['person_id'] = $person['id'];
        $document['language_code'] = empty($person['language_code']) ? [] : [$person['language_code']];
        $document['gender'] = $genders[$person['gender_code']] ?? null;
        $document['date_of_birth'] = empty($person['date_of_birth'])
            ? null
            : Date::createFromFormat('d-m-Y', $person['date_of_birth'])->format('Y-m-d');

        return $document;
    }

    /**
     * @param array $document
     * @param int $version
     *
     * @return array
     */
    private function addDocument(array $document, int $version)
    {
        $elastic = new Elastic();

        return $elastic->addDocument($this->index, $document['person_id'], $document, $version);
    }

    /**
     * @param array $documents
     * @param int $version
     *
     * @return array|callable
     */
    private function addDocuments(array $documents, int $version)
    {
        $elastic = new Elastic();

        return $elastic->addDocuments($this->index, 'person_id', $documents, $version);
    }

    /**
     * @return array
     */
    private function getCountryCodes(): array
    {
        try {
            return cache()->remember('COUNTRIES_BY_CODE', now()->addDays(10), function () {
                return $countries = Country::query()
                    ->pluck('iso_alpha_2', 'id')
                    ->toArray();
            });
        } catch (Exception $exception) {
            Log::error($exception->getMessage(), $exception->getTrace());

            return [];
        }
    }
}
