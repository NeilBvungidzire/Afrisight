<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\View\View;

class AdminDashboardController extends BaseController {

    /**
     * @return View
     * @throws AuthorizationException
     */
    public function __invoke()
    {
        $this->authorize('administration');

        return view('admin.dashboard.index');
    }
}
