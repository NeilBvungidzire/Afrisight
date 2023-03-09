<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddProjectCodeToTargetTracksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_tracks', function (Blueprint $table) {
            $table->string('project_code')
                ->nullable()
                ->after('id');
        });

        if (DB::table('targets')->exists()) {
            DB::table('target_tracks')
                ->selectRaw('target_tracks.id AS id, targets.project_code AS project_code')
                ->join('target_target_track', 'target_tracks.id', '=', 'target_target_track.target_track_id')
                ->join('targets', 'target_target_track.target_id', '=', 'targets.id')
                ->groupBy(['id', 'project_code'])
                ->get()
                ->each(function ($record) {
                    DB::table('target_tracks')
                        ->where('id', $record->id)
                        ->update(['project_code' => $record->project_code]);
                });
        }

        Schema::table('target_tracks', function (Blueprint $table) {
            $table->string('project_code')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('target_tracks', function (Blueprint $table) {
            $table->dropColumn('project_code');
        });
    }
}
