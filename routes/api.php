<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::apiResource('language', 'API\LanguageController');

Route::prefix('report')->group(function () {
    Route::get('/registration/per-country', 'API\Report\RegistrationController@perCountry');
});

//Route::get('/country', 'API\CountryController@index');

Route::prefix('profiling-question')->group(function () {
    Route::get('/', 'API\ProfilingQuestionController@index');
    Route::post('/', 'API\ProfilingQuestionController@store');
    Route::put('/{id}', 'API\ProfilingQuestionController@update');
    Route::delete('/{id}', 'API\ProfilingQuestionController@destroy');
});

//Route::post('/cm/wa/webhook', function (Request $request) {
//    Log::info('CM WhatsApp webhook', $request->toArray());
//});

// IMPORTANT: Should be always last endpoint!
Route::fallback('API\FallbackController');
