<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LoginController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except('logout');

        $this->redirectTo = route('profile.surveys');
    }

    public function showLoginForm()
    {
        // Facebook social auth is not working in Opera Mini, so make sure you don't show it.
        $agent = new Agent();
        $isOperaMini = $agent->browser() === 'Opera Mini';

        return view('auth.login', compact('isOperaMini'));
    }

    /**
     * Redirect user after logout to correct homepage URL.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('home');
    }
}
