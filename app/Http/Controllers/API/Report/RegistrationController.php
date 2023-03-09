<?php

namespace App\Http\Controllers\API\Report;

use App\Country;
use App\Http\Controllers\API\ApiController;
use App\Person;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegistrationController extends ApiController {

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function perCountry()
    {
        $this->authorize('manage-own-data');

        $countriesById = Country::all()->keyBy('id')->toArray();

        $persons = Person::select(DB::raw('COUNT(*) AS count, YEAR(created_at) AS year, MONTH(created_at) AS month, country_id AS countryId'))
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at), country_id'))
            ->get();

        $membersWithCountryCode = $persons->map(function ($person) use ($countriesById) {
            $person['countryCode'] = null;
            if (isset($countriesById[$person['countryId']])) {
                $person['countryCode'] = $countriesById[$person['countryId']]['iso_alpha_2'];
            }

            return $person;
        });

        $groupedByPeriod = $membersWithCountryCode->groupBy(function ($item) {
            return $item['year'] . '-' . $item['month'];
        });

        return response()->json([
            'error' => false,
            'data'  => $groupedByPeriod->toArray(),
        ]);
    }
}
