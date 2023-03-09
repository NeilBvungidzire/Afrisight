<?php

namespace App\Http\Controllers\Admin\AccountQualityManagement;

use App\Http\Controllers\Controller;

class AccountQualityController extends Controller {

    public function index() {
        $this->authorize('account-admin');

        return view('admin.account-quality.index');
    }
}
