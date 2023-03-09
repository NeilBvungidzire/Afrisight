<?php

use App\ExternalRespondent;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProjectIdToExternalRespondentsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('external_respondents', function (Blueprint $table) {
            $table->string('project_id')->after('external_id');
        });

        $projectIdCodeMapping = [
            'e5y1quc6w1nk' => 'tsr_001',
            'yjdoh4pcv1ia' => 'msi_002',
            '3b345l3n3z03' => 'dynata_006_2',
            'ydxt5q64mffq' => 'toluna_001',
            'jsor387axugg' => 'tsr_002_ng_boost',
            'fmytahb5ecyi' => 'tsr_002_za_boost',
            '5nq4aj2bf8hq' => 'borderless_access_004',
            'txd8qitpiy8e' => 'ipsos_022_ke',
            '4drf8jlafq00' => 'ipsos_022_ci',
            'yltdq7aq9imn' => 'dynata_022_ng',
            'yltdq7aq9imx' => 'dynata_022_ng_sample_2',
            'CD9DhWwz8jLW' => 'dynata_013_ng',
            'h3fwOLqvLNq2' => 'skim_001_et',
            'h3fwOLqvLNq1' => 'skim_001_et',
            'h3fwOLqvLNq3' => 'msi_008_ng',
        ];

        ExternalRespondent::query()
            ->each(static function (ExternalRespondent $externalRespondent) use ($projectIdCodeMapping) {
                foreach ($projectIdCodeMapping as $id => $code) {
                    if ($code === $externalRespondent->project_code && $externalRespondent->source === 'datadiggers-mr') {
                        $externalRespondent->project_id = 'h3fwOLqvLNq1';
                        $externalRespondent->save();
                        break;
                    }

                    if ($code === $externalRespondent->project_code) {
                        $externalRespondent->project_id = $id;
                        $externalRespondent->save();
                        break;
                    }
                }
            });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('external_respondents', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
}
