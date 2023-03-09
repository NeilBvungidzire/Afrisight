<?php

namespace App\Http\Controllers\Admin;

use App\Alert\Facades\Alert;
use App\Constants\RespondentStatus;
use App\Http\Controllers\Controller;
use App\SampleProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SampleProviderController extends Controller {

    public function index()
    {
        $this->authorize('manage-projects');

        $title = 'Sample Suppliers';
        $records = SampleProvider::query()->orderBy('created_at', 'desc')->get();

        return view('admin.projects.sample-provider.index', compact('records', 'title'));
    }

    public function dashboard(int $id)
    {
        $this->authorize('manage-projects');

        if ( ! $sampleProvider = SampleProvider::find($id)) {
            Alert::makeWarning('Could not find the sample supplier for the projects.');

            return redirect()->route('admin.sample-provider.index');
        }

        $title = 'Dashboard of sample supplier project(s)';

        $statsByProject = DB::table('external_respondents')
            ->selectRaw('project_code, status, COUNT(*) AS count')
            ->where('project_id', $sampleProvider->project_id)
            ->groupBy(['project_code','status'])
            ->get()
            ->groupBy('project_code');

        return view('admin.projects.sample-provider.dashboard', compact('statsByProject', 'title'));
    }

    public function create()
    {
        $this->authorize('manage-projects');

        $title = 'Set sample supplier for project(s)';
        $projectId = SampleProvider::generateRandomProjectId();
        $providerSurveyLinkToUse = route('intermediary.start', [
            'project-code' => $projectId,
            'id'           => 'RID',
        ]);
        $providers = $this->getProviders();
        $endStatuses = [
            RespondentStatus::COMPLETED,
            RespondentStatus::QUOTA_FULL,
            RespondentStatus::SCREEN_OUT,
            RespondentStatus::DISQUALIFIED,
        ];

        return view('admin.projects.sample-provider.create', compact('title', 'projectId',
            'providers', 'endStatuses', 'providerSurveyLinkToUse'));
    }

    public function store()
    {
        $this->authorize('manage-projects');

        $data = request()->all([
            'project_id',
            'project_codes',
            'source',
            'end_redirects',
        ]);

        if ($data['project_codes']) {
            $data['project_codes'] = explode(",", str_replace(" ", "", $data['project_codes']));
        }

        Validator::make($data, [
            'project_id'      => ['required', 'string'],
            'project_codes'   => ['required', 'array'],
            'project_codes.*' => ['required', 'string'],
            'source'          => ['required', Rule::in(array_keys($this->getProviders()))],
            'end_redirects'   => ['required', 'array'],
            'end_redirects.*' => ['required', 'url'],
        ])->validate();

        if (SampleProvider::create($data)) {
            Alert::makeSuccess('Sample supplier set-up successfully for projects.');
        } else {
            Alert::makeWarning('Could not set sample supplier for projects.');
        }

        return redirect()->route('admin.sample-provider.index');
    }

    public function edit(int $id)
    {
        $this->authorize('manage-projects');

        if ( ! $record = SampleProvider::find($id)) {
            return redirect()->route('admin.sample-provider.index');
        }

        $title = 'Edit sample supplier for project(s)';
        $providerSurveyLinkToUse = route('intermediary.start', [
            'project-code' => $record->project_id,
            'id'           => 'RID',
        ]);
        $providers = $this->getProviders();
        $endStatuses = [
            RespondentStatus::COMPLETED,
            RespondentStatus::QUOTA_FULL,
            RespondentStatus::SCREEN_OUT,
            RespondentStatus::DISQUALIFIED,
        ];
        $projectCodes = $record->project_codes;
        $record->project_codes = implode(", ", $projectCodes);

        return view('admin.projects.sample-provider.edit', compact('record', 'title',
            'providers', 'endStatuses', 'providerSurveyLinkToUse'));
    }

    public function update(int $id)
    {
        $this->authorize('manage-projects');

        if ( ! $record = SampleProvider::find($id)) {
            Alert::makeWarning('Could not find the sample supplier for the projects.');

            return redirect()->route('admin.sample-provider.index');
        }

        $data = request()->all([
            'project_id',
            'project_codes',
            'source',
            'end_redirects',
        ]);

        if ($data['project_codes']) {
            $data['project_codes'] = explode(",", str_replace(" ", "", $data['project_codes']));
        }

        Validator::make($data, [
            'project_id'      => ['required', 'string'],
            'project_codes'   => ['required', 'array'],
            'project_codes.*' => ['required', 'string'],
            'source'          => ['required', Rule::in(array_keys($this->getProviders()))],
            'end_redirects'   => ['required', 'array'],
            'end_redirects.*' => ['required', 'url'],
        ])->validate();

        if ($record->update($data)) {
            Alert::makeSuccess('Sample supplier set-up successfully for projects.');
        } else {
            Alert::makeWarning('Could not set sample supplier for projects.');
        }

        return redirect()->route('admin.sample-provider.index');
    }

    private function getProviders(): array
    {
        return [
            'cint'           => 'Cint',
            'datadiggers-mr' => 'Datadiggers MR',
            'philomath-research' => 'Philomath Research',
        ];
    }
}
