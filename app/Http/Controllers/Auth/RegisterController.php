<?php

namespace App\Http\Controllers\Auth;

use App\Constants\Gender;
use App\Country;
use App\Http\Controllers\Controller;
use App\Person;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;

class RegisterController extends Controller {

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

        $this->redirectTo = route('verification.notice');
    }

    public function showRegistrationForm()
    {
        // Facebook social auth is not working in Opera Mini, so make sure you don't show it.
        $isOperaMini = (new Agent())->browser() === 'Opera Mini';

        $countries = collect();
        if ($ipCountry = Country::getCountryByIp()) {
            $countries->add($ipCountry);
        }

        if ($countries->count() === 0) {
            $countries = cache()->remember('COUNTRIES_ALL', now()->addDays(10), static function () {
                return Country::all();
            });
        }

        $genders = Gender::getKeyWithLabel();

        return view('auth.register', compact('countries', 'genders', 'isOperaMini'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // Strip whitespace from the beginning and end of a string.
        $data['email'] = strtolower(trim($data['email']));

        return Validator::make($data, [

            // User's data
            'email'                  => ['required', 'string', 'email:strict', 'max:255', 'unique:users'],
            'password'               => ['required', 'string', 'min:8'],

            // Person's data
            'country_id'             => ['required', 'exists:countries,id'],
            'date_of_birth'          => ['required', 'string', 'date_format:d-m-Y'],
            'gender_code'            => ['required', 'in:m,w,u'],
            'privacy_policy_consent' => ['required'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $userData = [
            'email'                  => $data['email'],
            'password'               => Hash::make($data['password']),
            'privacy_policy_consent' => $data['privacy_policy_consent'],
        ];

        $personData = [
            'email'         => $data['email'],
            'date_of_birth' => $data['date_of_birth'],
            'gender_code'   => $data['gender_code'],
            'country_id'    => $data['country_id'],
        ];

        $user = DB::transaction(static function () use ($userData, $personData) {
            $person = Person::create($personData);

            return User::create(array_merge($userData, [
                'person_id' => $person->id,
            ]));
        });

        return $user;
    }
}
