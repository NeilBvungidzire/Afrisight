<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::table('projects', static function (Blueprint $table) {

            $table->string('country_code', 2)->change();
            $table->text('description')->nullable()->after('project_code');
            $table->boolean('enabled_via_web_app')->after('is_live');
            $table->boolean('enabled_for_admin')->after('enabled_via_web_app');
            $table->json('targets')->nullable()->after('is_ready_to_run');
            $table->json('targets_relation')->nullable()->after('targets');
            $table->json('configs')->after('targets_relation');

        });

//        $projectsInFile = config('projects');
//        foreach ($projectsInFile as $projectCode => $projectConfigs) {
//            DB::table('projects')
//                ->where('project_code', $projectCode)
//                ->update([
//                    'description'         => $projectConfigs['description'],
//                    'enabled_via_web_app' => $projectConfigs['enabled_via_web_app'],
//                    'enabled_for_admin'   => $projectConfigs['enabled_for_admin'],
//                    'targets'             => isset($projectConfigs['targets']) ? json_encode($projectConfigs['targets']) : null,
//                    'targets_relation'    => isset($projectConfigs['targets_relation']) ? json_encode($projectConfigs['targets_relation']) : null,
//                    'configs'             => json_encode($projectConfigs['configs']),
//                ]);
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table('projects', static function (Blueprint $table) {

            $table->string('country_code')->change();
            $table->dropColumn('description');
            $table->dropColumn('enabled_via_web_app');
            $table->dropColumn('enabled_for_admin');
            $table->dropColumn('targets');
            $table->dropColumn('targets_relation');
            $table->dropColumn('configs');

        });
    }
}
