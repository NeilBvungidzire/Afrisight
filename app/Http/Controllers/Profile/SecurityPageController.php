<?php

namespace App\Http\Controllers\Profile;

use App\ContactChange;
use Jenssegers\Agent\Agent;

class SecurityPageController extends BaseController {

    public function __invoke() {
        $socialAccountsRaw = request()->user()->socialAccounts ?? [];

        $socialAccounts = [];
        $socialAccountsRaw->each(function ($socialAccount) use (&$socialAccounts) {
            $socialAccounts[$socialAccount['provider']] = [
                'email' => $socialAccount['email'],
            ];
        });

        // Facebook social auth is not working in Opera Mini, so make sure you don't show it.
        $agent = new Agent();
        $isOperaMini = $agent->browser() === 'Opera Mini';

        $canChangeEmail = ContactChange::canChange(authUser()->id, ContactChange::EMAIL);

        return view('profile.security.index', [
            'hasPassword'    => ! empty(auth()->user()->password),
            'socialAccounts' => $socialAccounts,
            'isOperaMini'    => $isOperaMini,
            'canChangeEmail' => $canChangeEmail,
        ]);
    }
}
