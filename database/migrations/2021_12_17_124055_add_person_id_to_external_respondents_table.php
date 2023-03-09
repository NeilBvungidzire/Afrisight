<?php

use App\Jobs\RetrieveInternalPersonIdForExternalSource;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPersonIdToExternalRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('external_respondents', static function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')
                ->after('id')
                ->nullable();
        });

        $delay = now();
        if (App::environment('local')) {
            $externalRespondents = DB::table('external_respondents')
                ->orderBy('id')
                ->limit(10)
                ->get();

            foreach ($externalRespondents as $externalRespondent) {
                if ($externalRespondent->source !== 'cint') {
                    continue;
                }

                RetrieveInternalPersonIdForExternalSource::dispatch($externalRespondent->id)
                    ->delay($delay->addSeconds(2));
            }
        }

        if (App::environment('production')) {
            $delay = now();
            DB::table('external_respondents')
                ->orderBy('id')
                ->chunk(10, static function ($externalRespondents) use ($delay) {
                    foreach ($externalRespondents as $externalRespondent) {
                        if ($externalRespondent->source !== 'cint') {
                            continue;
                        }

                        RetrieveInternalPersonIdForExternalSource::dispatch($externalRespondent->id)
                            ->delay($delay->addSeconds(5));
                    }
                });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('external_respondents', static function (Blueprint $table) {
            $table->dropColumn('person_id');
        });
    }
}
