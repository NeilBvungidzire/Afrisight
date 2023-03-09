<?php

namespace App\Http\Controllers\Profile;

use App\Constants\Gender;
use App\Country;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Propaganistas\LaravelPhone\PhoneNumber;

class BasicInfoController extends BaseController {

    public function show() {
        try {
            $person = cache()->remember($this->generateUserCacheKey(authUser()->id), now()->addHour(),
                static function () {
                    return auth()->user()->person;
                });
        } catch (Exception $e) {
            return redirect()->back();
        }

        $this->setDataPoints($person->id);

        $fields = [
            ['label' => __('model/person.first_name.label'), 'value' => $person->first_name],
            ['label' => __('model/person.last_name.label'), 'value' => $person->last_name],
            ['label' => __('model/person.date_of_birth.label'), 'value' => $person->date_of_birth],
            ['label' => __('model/person.gender.label'), 'value' => $person->gender],
            [
                'label' => __('model/person.country.label'),
                'value' => isset($person->country->name) ? $person->country->name : '',
            ],
            [
                'label' => __('model/person.language.label'),
                'value' => __('language.' . strtolower($person->language_code) . '.label'),
            ],
            ['label' => __('model/person.mobile_number.label'), 'value' => $person->mobile_number],
        ];

        return view('profile.basic-info.show', compact('fields'));
    }

    public function edit() {
        $person = auth()->user()->person;

        $countries = collect();
        if ($ipCountry = Country::getCountryByIp()) {
            $countries->add($ipCountry);
        }
        if ( ! isset($person->country) && $countries->count() === 0) {
            $countries = cache()->remember('COUNTRIES_ALL', now()->addDays(10), function () {
                return Country::all();
            });
        }

        $genders = Gender::getKeyWithLabel();

        $languages = [];
        foreach (config('app.available_languages') as $languageCode) {
            $languages[] = strtoupper($languageCode);
        }

        return view('profile.basic-info.edit', [
            'person'      => $person,
            'countries'   => $countries,
            'countryName' => isset($person->country) ? $person->country->name : null,
            'genders'     => $genders,
            'languages'   => $languages,
        ]);
    }

    public function update() {
        $data = request([
            'first_name',
            'last_name',
            'date_of_birth',
            'gender_code',
            'mobile_number',
            'country_id',
            'language_code',
        ]);

        $person = auth()->user()->person;

        $languages = [];
        foreach (config('app.available_languages') as $languageCode) {
            $languages[] = strtoupper($languageCode);
        }

        $validator = Validator::make($data, [
            'first_name'    => ['max:255'],
            'last_name'     => ['max:255'],
            'date_of_birth' => ['required', 'string', 'date_format:d-m-Y'],
            'gender_code'   => ['required', 'in:m,w,u'],
            'language_code' => ['required', Rule::in($languages)],
        ]);

        $validator->sometimes('country_id', ['required', 'exists:countries,id'], function () use ($person) {
            return ! isset($person->country);
        });

        if ( ! $validator->fails()) {
            $countryCode = 'AUTO';
            if ($person->country) {
                $countryCode = $person->country->iso_alpha_2;
            } else {
                $country = Country::find($data['country_id'], 'iso_alpha_2');
                $countryCode = $country->iso_alpha_2;
            }

            if ($countryCode !== 'AUTO') {
                $data['mobile_number'] = (string) PhoneNumber::make($data['mobile_number'], $countryCode);
            }

            $validator->sometimes('mobile_number', ['nullable', 'phone:' . $countryCode . ',mobile'],
                function () use ($person) {
                    return true;
                });
        }

        $validator->validate();

        if (isset($person->country)) {
            unset($data['country_id']);
        }

        $person->update($data);

        try {
            cache()->forget($this->generateUserCacheKey(authUser()->id));
        } catch (Exception $e) {
        }

        return redirect()->route('profile.basic-info.show');
    }

    private function generateUserCacheKey($userId): string {
        return "PERSON_BY_USER_ID_${userId}";
    }
}
