<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Currency;
use App\IncentivePackage;
use App\Http\Controllers\Controller;
use App\Libraries\Project\ProjectUtils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class IncentivePackageController extends Controller {

    public function index(string $projectCode) {
        $this->authorize('manage-projects');
        $title = 'Incentive Packages';

        if ( ! $projectConfigs = ProjectUtils::getConfigs($projectCode)) {
            return redirect()->route('admin.projects.index');
        }

        $packages = IncentivePackage::query()
            ->where('project_code', $projectCode)
            ->get();

        $channelMapping = [
            'default' => 'default_incentive_package_id',
            'inflow'  => 'inflow_incentive_package_id',
        ];
        $channelInfo = [];
        foreach ($channelMapping as $label => $identifier) {
            if ( ! $id = $projectConfigs['configs'][$identifier] ?? null) {
                continue;
            }

            $channelInfo[$label] = $id;
        }

        return view('admin.projects.incentive-package.index', compact('channelInfo',
            'channelMapping', 'projectCode', 'title', 'packages'));
    }

    public function create(string $projectCode) {
        $this->authorize('manage-projects');
        $title = 'Incentive Packages';

        $nextPackageId = DB::table('incentive_packages')
                ->where('project_code', $projectCode)
                ->max('reference_id') + 1;

        $currencies = Currency::getConstants();

        return view('admin.projects.incentive-package.create', compact('projectCode', 'title',
            'nextPackageId', 'currencies'));
    }

    public function store(string $projectCode) {
        $this->authorize('manage-projects');

        $data = request()->all([
            'reference_id',
            'loi',
            'usd_amount',
            'local_currency',
            'local_amount',
        ]);

        $usedIds = DB::table('incentive_packages')
            ->where('project_code', $projectCode)
            ->pluck('reference_id')
            ->toArray();

        Validator::make($data, [
            'reference_id'   => ['required', 'integer', Rule::notIn($usedIds)],
            'loi'            => ['required', 'integer'],
            'usd_amount'     => ['required', 'numeric'],
            'local_currency' => ['required', Rule::in(Currency::getConstants())],
            'local_amount'   => ['required', 'numeric'],
        ])->validate();

        $data['project_code'] = $projectCode;

        IncentivePackage::create($data);

        return redirect()->route('admin.projects.incentive-packages', ['project_code' => $projectCode]);
    }

    public function allocate(string $projectCode, string $channel, int $id): RedirectResponse {
        ProjectUtils::setChannelIncentivePackage($projectCode, $channel, $id);

        return redirect()->back();
    }
}
