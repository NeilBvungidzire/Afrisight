<?php

namespace App\Http\Controllers\Profile;

use App\Alert\Facades\Alert;
use App\ContactChange;
use App\Http\Controllers\Controller;
use App\Mail\ChangeEmail;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ChangeEmailController extends Controller {

    /*
        |--------------------------------------------------------------------------
        | Email Change Controller
        |--------------------------------------------------------------------------
        |
        | This controller allows the user to change his email address after he
        | verifies it through a message delivered to the enew email address.
        | This uses a temporarily signed url to validate the email change.
        |
        */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Only the authenticated user can change its email, but he should be able
        // to verify his email address using other device without having to be
        // authenticated. This happens a lot when they confirm by phone.
        $this->middleware('auth')->only(['change', 'edit', 'verify']);

        // A signed URL will prevent anyone except the User to change his email.
        $this->middleware('signed')->only(['verify']);
    }

    public function edit() {
        if ( ! $this->canChange()) {
            Alert::makeWarning(__('profile.sub_pages.email_change.alert.only_once_in_30_days'));

            return redirect()->route('profile.security');
        }

        if ($user = authUser()) {
            $user->person->syncCintData(true);
        }

        return view('profile.security.email-reset');
    }

    /**
     * Changes the user Email Address for a new one
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function change(Request $request): RedirectResponse {
        if ( ! $this->canChange()) {
            Alert::makeWarning(__('profile.sub_pages.email_change.alert.only_once_in_30_days'));

            return redirect()->route('profile.security');
        }

        $request->validate([
            'password'  => ['required', 'string'],
            'new_email' => ['required', 'email', 'unique:users,email', 'unique:persons,email,NULL,id,deleted_at,NULL'],
        ]);

        /** @var User $user */
        $user = authUser();
        if ( ! Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
            return redirect()->route('home');
        }

        // Send the email to the user
        Mail::to($request->new_email)->send(new ChangeEmail($user->id, $request->new_email));

        Alert::makeInfo(__('profile.sub_pages.email_change.alert.successful_request'));

        // Return the view
        return back();
    }

    /**
     * Verifies and completes the Email change
     *
     */
    public function verify(): RedirectResponse {
        if ( ! $this->canChange()) {
            Alert::makeWarning(__('profile.sub_pages.email_change.alert.only_once_in_30_days'));

            return redirect()->route('profile.security');
        }

        $validator = Validator::make(request()->all(), [
            'new_email' => ['required', 'email', 'unique:users,email', 'unique:persons,email,NULL,id,deleted_at,NULL'],
        ]);
        if ($validator->invalid()) {
            Alert::makeWarning(__('profile.sub_pages.email_change.alert.unsuccessful_verification'));

            return redirect()->route('profile.change-email.edit');
        }

        /** @var User $user */
        $user = authUser();
        if ( ! $user) {
            return redirect()->route('home');
        }

        $previousEmail = $user->email;

        // Change the Email
        $successfullyChanged = DB::transaction(static function () use ($user) {
            $userChanged = $user->update([
                'email' => request()->new_email,
            ]);

            $personChanged = $user->person()->update([
                'email' => request()->new_email,
            ]);

            return $userChanged && $personChanged;
        });

        if ($successfullyChanged) {
            ContactChange::create([
                'person_id'         => $user->person_id,
                'contact_reference' => ContactChange::EMAIL,
                'from'              => $previousEmail,
                'to'                => request()->new_email,
            ]);

            Alert::makeSuccess(__('profile.sub_pages.email_change.alert.successful_change', ['new_email' => request()->new_email]));
        } else {
            Alert::makeWarning(__('profile.sub_pages.email_change.alert.unsuccessful_verification'));
        }

        // And finally return the view telling the change has been done
        return redirect()->route('profile.security');
    }

    private function canChange(): bool {
        return ContactChange::canChange(authUser()->id, ContactChange::EMAIL);
    }
}
