<?php

namespace App\Http\Controllers\Profile;

use App\User;
use Illuminate\Support\Facades\DB;

class DeleteAccountController extends BaseController {

    public function show()
    {
        return view('profile.delete-account.show');
    }

    public function delete()
    {
        $user = User::find(auth()->user()->id);

        DB::transaction(function () use ($user) {
            $user->delete();
            $user->person->delete();

            if ($user->socialAccounts->count()) {
                $user->socialAccounts()->delete();
            }
        });

        return redirect()->route('home');
    }
}
