<?php

namespace App\Http\Controllers\Social;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class BaseController extends Controller {

    /**
     * @var string
     */
    private $sessionKey = 'SOCIAL_LOGIN';

    /**
     * @var string
     */
    protected $provider;

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register()
    {
        return HandleRedirects::redirectToProvider($this->provider, $this->sessionKey, 'register');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login()
    {
        return HandleRedirects::redirectToProvider($this->provider, $this->sessionKey, 'login');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function link()
    {
        return HandleRedirects::redirectToProvider($this->provider, $this->sessionKey, 'link');
    }

    /**
     * @return RedirectResponse
     */
    public function unlink()
    {
        return HandleCallback::handleUnlinking($this->provider, auth()->user()->id);
    }

    /**
     * @return RedirectResponse
     */
    public function handleProviderCallback()
    {
        return HandleCallback::handleProviderCallback($this->provider, $this->sessionKey, auth()->user());
    }
}
