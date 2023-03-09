<?php

namespace App\Http\Controllers\API;

use App\Country;
use App\Http\Resources\CountryResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;

class CountryController extends ApiController {

    /**
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('manage-own-data');

        return response()->json([
            'error' => false,
            'data'  => CountryResource::collection(Country::all()),
        ]);
    }
}
