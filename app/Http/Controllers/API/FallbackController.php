<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class FallbackController extends Controller {

    /**
     * @return JsonResponse
     */
    public function __invoke()
    {
        return response()->json([
            'message' => 'Endpoint not found. If error persists, contact support.tech@afrisight.com',
        ], 404);
    }
}
