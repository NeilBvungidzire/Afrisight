<?php

namespace App\Libraries\Project;

use App\Project;

class ProjectEditor {

    /**
     * @var string
     */
    private $projectCode;

    public function __construct(string $projectCode) {
        $this->projectCode = $projectCode;
    }

    /**
     * @return bool
     */
    public function create(): bool {
        if ( ! ProjectUtils::getConfigs($this->projectCode)) {
            $config = config('projects.' . $this->projectCode);

            $data = $config;
            $data['project_code'] = $this->projectCode;
            $data['is_live'] = $config['live'];
            $data['country_code'] = $config['targets']['country'][0] ?? null;
            $data['is_ready_to_run'] = false;

            if ($project = Project::create($data)) {
                if (isset($config['incentive_packages'])) {
                    foreach ($config['incentive_packages'] as $key => $incentivePackage) {
                        $project->incentivePackages()->create(array_merge(['reference_id' => $key], $incentivePackage));
                    }
                }

                return true;
            }
        }

        return false;
    }
}