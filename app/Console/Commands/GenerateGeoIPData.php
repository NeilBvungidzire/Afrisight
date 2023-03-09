<?php

namespace App\Console\Commands;

use App\Constants\DataPointAttribute;
use App\DataPoint;
use App\Person;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Ipdata\ApiClient\Ipdata;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

class GenerateGeoIPData extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoip:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set data points based on GeoIP';

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
            $countryIds,
            $genderCode,
            $ageRange,
            $order,
            $limit,
            $ignoreAlreadyHandledPersons,
        ] = $this->getInputs();

        $this->table([
            'Attribute',
            'Value',
        ], [
            ['Country ID', implode(',', $countryIds)],
            ['Gender Code', $genderCode],
            ['Age Range', $ageRange],
            ['Order', $order],
            ['Limit', $limit],
            ['Ignore handled person', $ignoreAlreadyHandledPersons],
        ]);

        if ( ! $this->confirm('Is this correct?')) {
            $this->info('Cancelled...');

            return null;
        }

        $ageRanges = $this->explodeAgeRange($ageRange);
        $dateRanges = $this->generateAgeRangeDates($ageRanges['min'], $ageRanges['max']);

        $targetedPersons = $this->getTargetedPersons($countryIds, $dateRanges, $genderCode, $limit, $order,
            (bool)$ignoreAlreadyHandledPersons);
        $this->setData($targetedPersons);
    }

    /**
     * @param Collection $persons
     *
     * @return void
     */
    private function setData(Collection $persons)
    {
        // Feedback
        $this->info('Total persons found: ' . count($persons));

        $persons = $persons->keyBy('user_id');
        $userIds = $persons->keys()->toArray();

        $personTracks = [];
        $registeredPersonIds = [];
        DB::table('user_tracks')
            ->selectRaw('user_id, ip_address, max(created_at) AS oldest')
            ->whereIn('user_id', $userIds)
            ->groupBy(['user_id', 'ip_address'])
            ->orderBy('oldest', 'desc') // Order by latest visit by user.
            ->get()
            ->each(function ($result) use (&$personTracks, $persons, &$registeredPersonIds) {
                $userId = (int)$result->user_id;

                // Make sure the data on person vs user is available.
                if ( ! isset($persons[$userId])) {
                    return;
                }
                $personId = $persons[$userId]['person_id'];

                // Only set the latest IP registered for this user/person.
                if (in_array($personId, $registeredPersonIds)) {
                    return;
                }
                $registeredPersonIds[] = $personId;

                $ipAddress = (string)$result->ip_address;
                $personTracks[$ipAddress][] = $personId;
            });

        // Feedback
        $this->info('Total IP addresses found: ' . count($personTracks));

        try {
            $httpClient = new Psr18Client();
            $psr17Factory = new Psr17Factory();

            $apiKey = config('services.ipdata.api_key');
            if (empty($apiKey)) {
                return;
            }
            $ipdata = new Ipdata($apiKey, $httpClient, $psr17Factory);

            $countGeoIPDataFound = 0;
            $countWriteAttempts = 0;

            $bar = $this->output->createProgressBar(count($registeredPersonIds));
            $bar->start();

            // Set chunk of 100 max, because bulk request has limit of 100. See https://docs.ipdata.co/api-reference/bulk-lookup
            $ipChunks = collect(array_keys($personTracks))->chunk(50)->toArray();
            foreach ($ipChunks as $index => $ipChunk) {
                // Is needed to reset the index. Somehow resetting the keys will not work with the bulk request.
                $geoIPData = $ipdata->buildLookup(array_values($ipChunk));

                // Handle found GeoIP data from IP addresses search.
                foreach ($geoIPData as $data) {
                    if ( ! isset($data['ip'])) {
                        continue;
                    }
                    $countGeoIPDataFound++;

                    if ( ! isset($personTracks[$data['ip']])) {
                        continue;
                    }

                    // Different persons can have same IP address.
                    foreach ($personTracks[$data['ip']] as $personId) {
                        $this->saveData($data, $personId);
                        $countWriteAttempts++;

                        $bar->advance();
                    }
                }
            }
            $bar->finish();

            // Feedback
            $this->info('Total GeoIP addresses found: ' . $countGeoIPDataFound);
            $this->info('Total write attempts: ' . $countWriteAttempts);

            unset($personTracks);
            unset($registeredPersonIds);
        } catch (Exception $exception) {
            return;
        }
    }

    /**
     * @param array $geoIPData
     * @param int|string $personId
     */
    private function saveData(array $geoIPData, $personId)
    {
        $sourceType = 'GEO_IP';
        $generalData = [
            'person_id'   => $personId,
            'source_type' => $sourceType,
        ];

        // Country
        if (isset($geoIPData['country_code'])) {
            $data = array_merge($generalData, [
                'attribute' => DataPointAttribute::COUNTRY_CODE,
                'value'     => $geoIPData['country_code'],
            ]);

            DataPoint::updateOrCreate($data, $data);
        }

        // ISO-3166-2 subdivision
        if (isset($geoIPData['region_code']) && isset($geoIPData['country_code'])) {
            $data = array_merge($generalData, [
                'attribute' => DataPointAttribute::SUBDIVISION_CODE,
                'value'     => $geoIPData['country_code'] . '-' . $geoIPData['region_code'],
            ]);

            DataPoint::updateOrCreate($data, $data);
        }

        // City
        if (isset($geoIPData['city'])) {
            $data = array_merge($generalData, [
                'attribute' => DataPointAttribute::CITY_NAME,
                'value'     => $geoIPData['city'],
            ]);

            DataPoint::updateOrCreate($data, $data);
        }

        // Geographic coordinate
        if (isset($geoIPData['latitude']) && isset($geoIPData['longitude'])) {
            $value = 'lat:' . $geoIPData['latitude'] . '|long:' . $geoIPData['longitude'];
            $data = array_merge($generalData, [
                'attribute' => DataPointAttribute::GEO_COORDINATES,
                'value'     => $value,
            ]);

            DataPoint::updateOrCreate($data, $data);
        }

        // Known threat
        if (isset($geoIPData['threat'])) {
            $isThreat = 0;
            foreach ($geoIPData['threat'] as $threat) {
                if ($threat) {
                    $isThreat = 1;
                }
            }
            $data = array_merge($generalData, [
                'attribute' => DataPointAttribute::THREAT,
                'value'     => $isThreat,
            ]);

            DataPoint::updateOrCreate($data, $data);
        }
    }

    /**
     * @param int|string[] $countryIds
     * @param array $dateRange
     * @param string $genderCode
     * @param int $limit
     * @param string $order
     * @param bool $ignoreAlreadyHandledPersons
     *
     * @return Collection
     */
    private function getTargetedPersons(
        $countryIds,
        $dateRange,
        $genderCode,
        $limit,
        $order = 'DESC',
        bool $ignoreAlreadyHandledPersons = true
    ) {
        $personsQuery = Person::query()
            ->select(['persons.id AS person_id', 'users.id AS user_id'])
            ->join('users', 'persons.id', '=', 'users.person_id')
            // Country
            ->whereIn('persons.country_id', $countryIds)
            // Age
            ->where('persons.date_of_birth', '>', $dateRange['max'])
            ->where('persons.date_of_birth', '<', $dateRange['min']);

        // Gender
        if ($genderCode) {
            $personsQuery->where('persons.gender_code', $genderCode);
        }

        // Ignore previously handled persons.
        if ($ignoreAlreadyHandledPersons) {
            $personsQuery->whereRaw('persons.id NOT IN (SELECT dp.person_id FROM data_points AS dp GROUP BY dp.person_id)');
        }

        return $personsQuery
            ->orderBy('persons.created_at', $order)
            ->limit($limit)
            ->get();
    }

    /**
     * @return array
     */
    private function getInputs()
    {
        $countryIds = $this->ask('Country ID\'s?');
        $countryIds = explode(',', $countryIds);
        $genderAnswer = $this->choice('Gender code?', ['Both', 'Male', 'Female'], 'Both');
        $ageRange = $this->ask('Age range?');
        $order = $this->choice('Order?', ['ASC', 'DESC'], 'DESC');
        $ignoreAlreadyHandledPersons = $this->confirm('Ignore already handled persons?', true);
        $limit = $this->ask('Limit?', 100);

        switch ($genderAnswer) {

            case 'Male':
                $genderCode = 'm';
                break;

            case 'Female':
                $genderCode = 'w';
                break;

            default:
                $genderCode = null;
                break;
        }

        return [
            $countryIds,
            $genderCode,
            $ageRange,
            $order,
            $limit,
            $ignoreAlreadyHandledPersons,
        ];
    }

    /**
     * @param string $range
     *
     * @return int[]
     */
    private function explodeAgeRange(string $range)
    {
        if (strpos($range, '+') !== false) {
            $explodedRange = explode('+', $range);
            $ranges = [
                'min' => (int)$explodedRange[0],
                'max' => 100,
            ];
        } elseif (strpos($range, '-') !== false) {
            $explodedRange = explode('-', $range);

            $ranges = [
                'min' => (int)$explodedRange[0],
                'max' => (int)$explodedRange[1],
            ];
        } else {
            $ranges = [
                'min' => (int)$range,
                'max' => (int)$range,
            ];
        }

        return $ranges;
    }

    /**
     * @param int|string $min
     * @param int|string $max
     *
     * @return array
     */
    private function generateAgeRangeDates($min, $max)
    {
        return [
            // Youngest date from now.
            'min' => Carbon::now()->subYears($min)->toDateString(),
            // Oldest date from now.
            'max' => Carbon::now()->subYears($max)->subYear()->addDay()->toDateString(),
        ];
    }
}
