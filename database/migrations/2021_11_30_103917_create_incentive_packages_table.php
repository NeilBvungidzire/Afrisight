<?php

use App\IncentivePackage;
use App\Libraries\Project\ProjectUtils;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncentivePackagesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('incentive_packages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_code');
            $table->smallInteger('reference_id');

            $table->integer('loi');
            $table->float('usd_amount');
            $table->string('local_currency');
            $table->float('local_amount');

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['project_code', 'reference_id']);
        });

        /**
         * !!! IMPORTANT !!!
         *
         * Because this logic is depending on projects table and the related logic is needing country_code this will
         * fail. You can either disable this part to execute or find other solution.
         */
//        $projects = ProjectUtils::getConfigs();
//        foreach ($projects as $projectCode => $projectConfigs) {
//            foreach ($projectConfigs['incentive_packages'] as $incentive_package_id => $incentive_package) {
//                IncentivePackage::create([
//                    'project_code'   => $projectCode,
//                    'reference_id'   => $incentive_package_id,
//                    'loi'            => $incentive_package['loi'],
//                    'usd_amount'     => $incentive_package['usd_amount'],
//                    'local_currency' => $incentive_package['local_currency'],
//                    'local_amount'   => $incentive_package['local_amount'],
//                ]);
//            }
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('incentive_packages');
    }
}
