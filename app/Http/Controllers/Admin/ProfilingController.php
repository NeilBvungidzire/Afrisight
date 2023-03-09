<?php

namespace App\Http\Controllers\Admin;

use App\Country;
use App\ProfilingQuestion;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilingController extends BaseController {

    /**
     * Display a listing of the resource.
     *
     * @return View
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('manage-profiling');

        $questions = ProfilingQuestion::all();
        $countries = Country::pluck('name', 'id');

        return view('admin.profiling.index', compact('questions', 'countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('manage-profiling');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return void
     * @throws AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('manage-profiling');
    }

    /**
     * Display the specified resource.
     *
     * @param ProfilingQuestion $profiling
     *
     * @return void
     * @throws AuthorizationException
     */
    public function show(ProfilingQuestion $profiling)
    {
        $this->authorize('manage-profiling');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param ProfilingQuestion $profiling
     *
     * @return View
     * @throws AuthorizationException
     */
    public function edit(ProfilingQuestion $profiling): View
    {
        $this->authorize('manage-profiling');

        return view('admin.profiling.edit', compact('profiling'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request           $request
     * @param ProfilingQuestion $profiling
     *
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, ProfilingQuestion $profiling): RedirectResponse
    {
        $this->authorize('manage-profiling');

        $data = $request->all([
            'title',
            'answer_params',
            'datapoint_identifier',
        ]);
        $profiling->update($data);

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ProfilingQuestion $profiling
     *
     * @return void
     * @throws AuthorizationException
     */
    public function destroy(ProfilingQuestion $profiling)
    {
        $this->authorize('manage-profiling');
    }
}
