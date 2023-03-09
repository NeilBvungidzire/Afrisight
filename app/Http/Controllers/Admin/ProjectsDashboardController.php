<?php

namespace App\Http\Controllers\Admin;

class ProjectsDashboardController extends BaseController {

    public function index()
    {
        $this->authorize('manage-projects');

        return view('admin.projects.dashboard');
    }
}
