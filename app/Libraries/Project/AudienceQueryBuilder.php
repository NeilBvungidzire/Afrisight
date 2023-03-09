<?php

namespace App\Libraries\Project;

use App\Constants\DataPointAttribute;
use App\Constants\RespondentStatus;
use App\Country;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AudienceQueryBuilder {

    /**
     * @var Builder
     */
    private $query;

    /**
     * AudienceQueryBuilderX constructor.
     */
    public function __construct()
    {
        $this->query = DB::table('persons');
    }

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->query;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit): AudienceQueryBuilder
    {
        $this->query->limit($limit);

        return $this;
    }

    public function orderBy(string $column = null, string $direction = 'asc'): AudienceQueryBuilder
    {
        if ($column === null) {
            $this->query->inRandomOrder();
        } else {
            $this->query->orderBy($column, $direction);
        }

        return $this;
    }

    /**
     * @param string[] $countryCodes
     *
     * @return $this
     * @throws Exception
     */
    public function setCountries(array $countryCodes): AudienceQueryBuilder
    {
        /** @var Collection $countriesIdByCode * */
        $countriesIdByCode = Cache::remember("COUNTRIES", now()->addDays(5), static function () {
            return Country::query()
                ->pluck('id', 'iso_alpha_2');
        });

        if ($countriesIdByCode->count() === 0) {
            throw new Exception("Could not find any country.");
        }

        if ($countriesIdByCode->keys()->intersect($countryCodes)->count() !== count($countryCodes)) {
            throw new Exception("Could not find one of the passed countries.");
        }

        $countriesId = $countriesIdByCode->only($countryCodes);

        $this->query->whereIn('country_id', $countriesId);

        return $this;
    }

    public function setSubdivisionCodes(array $subdivisionCodes): AudienceQueryBuilder
    {
        $personIds = DB::table('data_points')
            ->where('source_type', 'PROFILING_QUESTIONNAIRE')
            ->where('attribute', DataPointAttribute::SUBDIVISION_CODE)
            ->where(function (Builder $query) use ($subdivisionCodes) {
                foreach ($subdivisionCodes as $subdivisionCode) {
                    $query->orWhere('value', $subdivisionCode);
                }
            })
            ->pluck('person_id')
            ->toArray();

        $this->query->whereIn('id', $personIds);

        return $this;
    }

    /**
     * @param string[] $ageRanges
     *
     * @return $this
     */
    public function setAgeRanges(array $ageRanges): AudienceQueryBuilder
    {
        $this->query->where(function (Builder $query) use ($ageRanges) {
            foreach ($ageRanges as $ageRange) {
                $ages = explode('-', $ageRange);

                $lowestBirthDate = date('Y-m-d', strtotime('-' . $ages[1] . ' years'));
                $highestBirthDate = date('Y-m-d', strtotime('-' . $ages[0] . ' years'));

                $query->orWhereBetween('date_of_birth', [$lowestBirthDate, $highestBirthDate]);
            }
        });

        return $this;
    }

    /**
     * @param string[] $genderCodes
     * @param bool     $strict
     *
     * @return $this
     */
    public function setGenders(array $genderCodes, bool $strict = false): AudienceQueryBuilder
    {
        $this->query->where(function (Builder $query) use ($genderCodes, $strict) {
            $query->whereIn('gender_code', $genderCodes);

            if ( ! $strict) {
                $query->orWhere('gender_code', 'u');
            }
        });

        return $this;
    }

    /**
     * @param string[] $languageCodes
     * @return $this
     */
    public function setLanguage(array $languageCodes): AudienceQueryBuilder
    {
        $this->query->whereIn('language_code', $languageCodes);

        return $this;
    }

    /**
     * @param int[] $personsId
     *
     * @return $this
     */
    public function exclusivePersons(array $personsId): AudienceQueryBuilder
    {
        $this->query->whereIn('id', $personsId);

        return $this;
    }

    /**
     * @param int[] $personsId
     *
     * @return $this
     */
    public function excludedPersons(array $personsId): AudienceQueryBuilder
    {
        $this->query->whereNotIn('id', $personsId);

        return $this;
    }

    /**
     * @param string        $projectCode
     * @param string[]|null $statuses
     * @param bool          $statusesIsExcept
     * @return $this
     */
    public function excludeRespondents(string $projectCode, array $statuses = null, bool $statusesIsExcept = true): AudienceQueryBuilder
    {
        $this->query->whereNotIn('id', static function (Builder $builder) use ($projectCode, $statuses, $statusesIsExcept) {
            $builder->select('person_id')
                ->distinct()
                ->from('respondents')
                ->where('project_code', $projectCode);

            if ( ! empty($statuses)) {
                $statusesToCheck = ($statusesIsExcept) ? Arr::except(RespondentStatus::getConstants(), $statuses) : $statuses;
                $builder->whereIn('current_status', $statusesToCheck);
            }
        });

        return $this;
    }

    /**
     * @param string     $projectCode
     * @param array|null $statusesToIgnore
     * @return int[]
     */
    public static function getEngagedPersonsId(string $projectCode, array $statusesToIgnore = null): array
    {
        $respondents = DB::table('respondents')
            ->distinct()
            ->where('project_code', $projectCode);

        if ( ! empty($statusesToIgnore)) {
            $respondents->whereIn('current_status', Arr::except(RespondentStatus::getConstants(), $statusesToIgnore));
        }

        return $respondents->pluck('person_id')->toArray();
    }
}
