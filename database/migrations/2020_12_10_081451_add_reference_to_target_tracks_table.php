<?php

use App\TargetTrack;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceToTargetTracksTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_tracks', function (Blueprint $table) {
            $table->json('reference')
                ->nullable()
                ->after('relation');
        });

        $targetTracks = TargetTrack::query()
            ->with('targets')
            ->get();

        foreach ($targetTracks as $targetTrack) {
            $targetTrack->update([
                'reference' => $targetTrack->targets->pluck('id', 'criteria')->toArray(),
            ]);
        }

        Schema::table('target_tracks', function (Blueprint $table) {
            $table->json('reference')
                ->nullable(false)
                ->change();
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
            $table->dropColumn('reference');
        });
    }
}
