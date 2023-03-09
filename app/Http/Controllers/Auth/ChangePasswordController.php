<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller {

    public function edit()
    {
        return view('auth.change-password', [
            'hasPassword' => ! empty(auth()->user()->password),
        ]);
    }

    public function update()
    {
        $user = auth()->user();
        $hasPassword = ! empty($user->password);
        $data = request(['old_password', 'password', 'password_confirmation']);

        // Validate fields
        $validator = Validator::make($data, [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $validator->sometimes('old_password', ['required', 'string'], function () use ($hasPassword) {
            return $hasPassword;
        });

        $validator->after(function ($validator) use ($user, $data, $hasPassword) {
            if ($hasPassword && ! Hash::check($data['old_password'], $user->password)) {
                $validator->errors()->add('old_password', __('auth.current_password_incorrect'));
            }
        });

        $validator->validate();

        if ( ! $user->update(['password' => Hash::make($data['password'])])) {
            request()->session()->flash('status', __('auth.could_not_update_password'));

            return redirect()->refresh();
        }

        return redirect()->route('profile.security');
    }
}
