<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', static function (Blueprint $table) {
            $table->string('project_code')->primary();

            $table->string('country_code');
            $table->boolean('is_live');
            $table->boolean('is_ready_to_run');

            $table->softDeletes();
            $table->timestamps();
        });

//        $projectsInFile = config('projects');
//        foreach ($projectsInFile as $projectCode => $projectConfigs) {
//            if ($countryCode = $projectConfigs['targets']['country'] ?? null) {
//                DB::table('projects')
//                    ->where('project_code', $projectCode)
//                    ->update([
//                        'country_code' => $countryCode[0],
//                    ]);
//            }
//        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
