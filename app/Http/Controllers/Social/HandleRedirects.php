<?php

namespace App\Http\Controllers\Social;

use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HandleRedirects {

    /**
     * Redirect the user to the provider authentication page.
     *
     * @param string $provider
     * @param string $sessionKey
     * @param string $target
     *
     * @return RedirectResponse
     */
    public static function redirectToProvider(string $provider, string $sessionKey, string $target)
    {
        session()->put($sessionKey, [
            'target'   => $target,
            'language' => app()->getLocale(),
        ]);

        return Socialite::driver($provider)->redirect();
    }
}
