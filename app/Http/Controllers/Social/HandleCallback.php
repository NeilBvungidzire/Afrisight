<?php

namespace App\Http\Controllers\Social;

use App\Alert\Facades\Alert;
use App\Mail\RegistrationViaSocialMedia;
use App\Person;
use App\SocialAccount;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class HandleCallback {

    /**
     * @var string
     */
    private static $sessionKey;

    /**
     * @var string
     */
    private static $provider;

    /**
     * @var string
     */
    private static $target;

    /**
     * @var SocialiteUser
     */
    private static $socialiteUser;

    /**
     * @var User|null
     */
    private static $user = null;

    /**
     * @var string
     */
    private static $language;

    /**
     * @param string $provider
     * @param string $sessionKey
     * @param User|null $user
     *
     * @return RedirectResponse
     */
    public static function handleProviderCallback(string $provider, string $sessionKey, $user = null)
    {
        self::$provider = $provider;
        self::$sessionKey = $sessionKey;

        // Pull session data.
        $sessionData = session()->pull(self::$sessionKey);

        self::$target = $sessionData['target'];

        // Set language which was passed via session.
        self::setLanguage($sessionData['language']);

        // Handle denial from user end.
        if ( ! request()->has('code') || request()->has('denied')) {
            Alert::makeWarning(__('auth.social.could-not-link-social-account'));

            switch (self::$target) {
                case 'register':
                case 'login':
                    return redirect(self::getRoutePath('register'));

                case 'link':
                    return redirect(self::getRoutePath('profile.security'));
            }

            return redirect(self::getRoutePath('login'));
        }

        self::$socialiteUser = self::getSocialiteUser(self::$provider);
        self::$user = $user;

        if (empty(self::$target)) {
            Log::error('Target not set/found during Socialite usage.', [
                'intention'   => 'user want to use social account.',
                'provider'    => self::$provider,
                'provider_id' => self::$socialiteUser->getId(),
                'target'      => self::$target,
            ]);

            return redirect(self::getRoutePath('contacts'));
        }

        switch (self::$target) {
            case 'register':
                return self::handleRegistration();

            case 'login':
                return self::handleLoggingIn();

            case 'link':
                if (empty($user)) {
                    return redirect(self::getRoutePath('login'));
                }

                return self::handleLinking($user);
        }

        return redirect(self::getRoutePath('contacts'));
    }

    /**
     * @return RedirectResponse
     */
    private static function handleRegistration()
    {
        $socialAccount = self::getSocialAccountWithRelatedUser();

        // This SocialiteUser already have an SocialAccount with attached User.
        if ( ! empty($socialAccount) && ! empty($socialAccount->user)) {
            auth()->login($socialAccount->user);

            session()->flash('status', __('auth.social-account-already-linked'));

            return redirect(self::getRoutePath('profile.security'));
        }

        // Email is required to register. We couldn't retrieve the email from this SocialiteUser.
        $email = self::$socialiteUser->getEmail();
        if (empty($email)) {
            Log::info('Social account did not contain email address', [
                'intention'   => 'user want to register',
                'provider'    => self::$provider,
                'provider_id' => self::$socialiteUser->getId(),
            ]);

            session()->flash('status', __('auth.social.does-not-contain-email'));

            return redirect(self::getRoutePath('register'));
        }

        // No SocialAccount found, but we found an User with the same email as the SocialiteUser. Try to link them.
        $user = self::getUserByEmail($email);
        if ( ! empty($user)) {
            if ( ! self::attachSocialAccountWithUser($user)) {
                Alert::makeDanger(__('auth.social.could-not-link-social-account'));

                return redirect(self::getRoutePath('register'));
            }

            auth()->login($user);

            Alert::makeSuccess(__('auth.social.account-already-exist-and-now-linked'));

            return redirect()->intended(self::getRoutePath('profile.surveys'));
        }

        // Create new User with attached SocialAccount for this SocialiteUser.
        if ( ! empty($newUser = self::createFullAccount($email))) {
            // Send email to created User.
            $language = app()->getLocale();
            Mail::later(now()->addSeconds(10), new RegistrationViaSocialMedia($newUser, $language));

            auth()->login($newUser);

            return redirect(self::getRoutePath('profile.basic-info.edit'));
        }

        session()->flash('status', __('auth.social.could-not-register'));

        return redirect(self::getRoutePath('register'));
    }

    /**
     * @return RedirectResponse
     */
    private static function handleLoggingIn()
    {
        $socialAccount = self::getSocialAccountWithRelatedUser();

        // Found User with attached SocialUser account via this SocialiteUser.
        if ( ! empty($socialAccount) && ! empty($socialAccount->user)) {
            auth()->login($socialAccount->user);

            // Member should first add minimal data we need to profile.
            $person = $socialAccount->user->person;
            if ($person && ! $person->minimal_profile_data_is_available) {
                return redirect(self::getRoutePath('profile.basic-info.edit'));
            }

            return redirect()->intended(self::getRoutePath('profile.surveys'));
        }

        // We need email to login.
        $email = self::$socialiteUser->getEmail();
        if (empty($email)) {
            Log::info('Social account did not contain email address', [
                'intention'   => 'user want to login',
                'provider'    => self::$provider,
                'provider_id' => self::$socialiteUser->getId(),
            ]);

            session()->flash('status', __('auth.social.does-not-contain-email'));

            return redirect(self::getRoutePath('login'));
        }

        // User found with the same email address as the SocialiteUser, so try to link them.
        $user = self::getUserByEmail($email);
        if ( ! empty($user)) {
            if ( ! self::attachSocialAccountWithUser($user)) {
                Alert::makeDanger(__('auth.social.could-not-link-social-account'));

                return redirect(self::getRoutePath('login'));
            }

            auth()->login($user);

            Alert::makeSuccess(__('auth.social.account-already-exist-and-now-linked'));

            return redirect(self::getRoutePath('profile.security'));
        }

        session()->flash('status', __('auth.social.account-not-know'));

        return redirect(self::getRoutePath('register'));
    }

    /**
     * @param User $user
     *
     * @return RedirectResponse
     */
    private static function handleLinking(User $user)
    {
        // Check if this SocialiteUser is not already linked with other User.
        $socialAccount = self::getSocialAccountWithRelatedUser();
        if ( ! empty($socialAccount)) {
            Alert::makeDanger(__('auth.social.social-account-already-linked-with-other-user'));

            return redirect(self::getRoutePath('profile.security'));
        }

        if ( ! self::attachSocialAccountWithUser($user)) {
            Alert::makeDanger(__('auth.social.could-not-link-social-account'));

            return redirect(self::getRoutePath('profile.security'));
        }

        Alert::makeSuccess(__('auth.social.account-successfully-linked'));

        return redirect(self::getRoutePath('profile.security'));
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    private static function attachSocialAccountWithUser(User $user)
    {
        return DB::transaction(function () use ($user) {
            $createdSocialAccount = $user->socialAccounts()->create([
                'provider_id' => self::$socialiteUser->getId(),
                'provider'    => self::$provider,
                'other_data'  => [
                    'email'    => self::$socialiteUser->getEmail(),
                    'name'     => self::$socialiteUser->getName(),
                    'nickname' => self::$socialiteUser->getNickname(),
                    'avatar'   => self::$socialiteUser->getAvatar(),
                ],
            ]);

            // Try to set first and last name by guessing it from full from from SocialiteUser.
            $updatedPerson = $user->person()->update(self::guessFirstAndLastName(self::$socialiteUser->getName()));

            return ( ! empty($createdSocialAccount) && ! empty($updatedPerson));
        });
    }

    /**
     * @param string $provider
     * @param int $userId
     *
     * @return RedirectResponse
     */
    public static function handleUnlinking($provider, int $userId)
    {
        SocialAccount::where('provider', $provider)
            ->where('user_id', $userId)
            ->delete();

        Alert::makeInfo(__('auth.social.account-successfully-unlinked'));

        return redirect(self::getRoutePath('profile.security'));
    }

    /**
     * @param string $email
     *
     * @return User|null
     */
    private static function createFullAccount(string $email)
    {
        $userData = [
            'email' => $email,
        ];

        $personData = array_merge([
            'email' => $email,
        ], self::guessFirstAndLastName(self::$socialiteUser->getName()));

        $socialAccountData = [
            'provider_id' => self::$socialiteUser->getId(),
            'provider'    => self::$provider,
            'other_data'  => [
                'email'    => self::$socialiteUser->getEmail(),
                'name'     => self::$socialiteUser->getName(),
                'nickname' => self::$socialiteUser->getNickname(),
                'avatar'   => self::$socialiteUser->getAvatar(),
            ],
        ];

        $newUser = DB::transaction(function () use ($userData, $personData, $socialAccountData) {
            // Create Person.
            $person = Person::create($personData);

            // Create User and relate with created Person.
            $user = new User(array_merge($userData, ['person_id' => $person->id]));
            // Mark User email as verified, because we can assume social media platform already did this.
            $user->forceFill(['email_verified_at' => Date::now()])->save();

            // Create SocialAccount and relate with created User.
            $user->socialAccounts()->create($socialAccountData);

            return $user;
        });

        return $newUser ?? null;
    }

    /**
     * Get SocialiteUser.
     *
     * @param string $provider
     *
     * @return SocialiteUser
     */
    private static function getSocialiteUser(string $provider)
    {
        return Socialite::driver($provider)->user();
    }

    /**
     * @return SocialAccount|null
     */
    private static function getSocialAccountWithRelatedUser()
    {
        $socialAccount = SocialAccount::with('user')
            ->where('provider', self::$provider)
            ->where('provider_id', self::$socialiteUser->getId())
            ->first();

        if (empty($socialAccount)) {
            return null;
        }

        return $socialAccount;
    }

    /**
     * @param string $email
     *
     * @return User|null
     */
    private static function getUserByEmail(string $email)
    {
        $user = User::where('email', $email)->first();

        if (empty($user)) {
            return null;
        }

        return $user;
    }

    /**
     * Guess first and last name from string.
     *
     * @param string|null $fullName
     *
     * @return array
     */
    private static function guessFirstAndLastName(string $fullName = null)
    {
        $name = [
            'first_name' => null,
            'last_name'  => null,
        ];

        if (empty($fullName)) {
            return $name;
        }

        $exploded = explode(' ', $fullName);

        if (count($exploded) === 1) {
            $name['first_name'] = $fullName;

            return $name;
        }

        $name['first_name'] = array_shift($exploded);
        $name['last_name'] = implode(' ', $exploded);

        return $name;
    }

    /**
     * @param string|null $language
     */
    private static function setLanguage($language = null)
    {
        // Set as fallback current locale, which probably is the default language.
        if (empty($language)) {
            $language = app()->getLocale();
        } else {
            app()->setLocale($language);
        }
        self::$language = $language;
    }

    /**
     * @return string
     */
    private static function getLanguage()
    {
        return self::$language;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private static function getRoutePath(string $name)
    {
        $routePath = route($name, [], false);

        return LaravelLocalization::getLocalizedURL(self::getLanguage(), $routePath);
    }
}
