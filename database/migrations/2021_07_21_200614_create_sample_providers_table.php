<?php

use App\Constants\RespondentStatus;
use App\SampleProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleProvidersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sample_providers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('project_id')->index();
            $table->json('project_codes');
            $table->string('source');
            $table->json('end_redirects');

            $table->softDeletes();
            $table->timestamps();
        });

        $this->addExistingData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_providers');
    }

    private function addExistingData()
    {
        $list = [
            'e5y1quc6w1nk' => [
                'project_code'  => 'tsr_001',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=0d638b0c-257a-4328-924a-d1f20914568c",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=0cf9a9c2-8da3-ddaa-db50-8aca00c5fb1b",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=0cf9a9c2-8da3-ddaa-db50-8aca00c5fb1b",
                ],
                'source'        => 'cint',
            ],
            'yjdoh4pcv1ia' => [
                'project_code'  => 'msi_002',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=747708d5-6e95-4291-9f68-a31ca1425138",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=2d9690d8-fb56-9e7d-0185-19381cdef024",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=2d9690d8-fb56-9e7d-0185-19381cdef024",
                ],
                'source'        => 'cint',
            ],
            '3b345l3n3z03' => [
                'project_code'  => 'dynata_006_2',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=a2008c60-e99c-45ba-b6dc-3f671dabe011",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=c3fc1730-7863-ddaf-11f4-8f04c82e7526",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=c3fc1730-7863-ddaf-11f4-8f04c82e7526",
                ],
                'source'        => 'cint',
            ],
            'ydxt5q64mffq' => [
                'project_code'  => 'toluna_001',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=3f0d0eb6-d39f-4f67-bf4d-b9acdb735918",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=c3d9e5e9-66ce-a90f-02a7-46fa8b9a746c",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=c3d9e5e9-66ce-a90f-02a7-46fa8b9a746c",
                ],
                'source'        => 'cint',
            ],
            'jsor387axugg' => [
                'project_code'  => 'tsr_002_ng_boost',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=8d729f0f-9fa3-44b9-9873-7816b68a46e6",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=c802508c-d993-bba0-2640-45706537a8d4",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=c802508c-d993-bba0-2640-45706537a8d4",
                ],
                'source'        => 'cint',
            ],
            'fmytahb5ecyi' => [
                'project_code'  => 'tsr_002_za_boost',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=3022f18e-f400-4203-bf2a-64d0bcd7da3c",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=be262a47-1066-f7f4-9d6d-932c6c4d591e",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=be262a47-1066-f7f4-9d6d-932c6c4d591e",
                ],
                'source'        => 'cint',
            ],
            '5nq4aj2bf8hq' => [
                'project_code'  => 'borderless_access_004',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=2d437431-089b-4f1f-aab7-7bbce8cb9a62",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=a90ee59e-e6c7-63cc-a72c-6b61a042096d",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=a90ee59e-e6c7-63cc-a72c-6b61a042096d",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=a90ee59e-e6c7-63cc-a72c-6b61a042096d",
                ],
                'source'        => 'cint',
            ],
            'txd8qitpiy8e' => [
                'project_code'  => 'ipsos_022_ke',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=72d9ff3f-15d3-45aa-a9a1-1a95bb17f22f",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=1b5eeea8-cd51-c6a4-26bb-58bb4f932027",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=1b5eeea8-cd51-c6a4-26bb-58bb4f932027",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=1b5eeea8-cd51-c6a4-26bb-58bb4f932027",
                ],
                'source'        => 'cint',
            ],
            '4drf8jlafq00' => [
                'project_code'  => 'ipsos_022_ci',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=ddfff25d-4a93-4736-8c7b-7493f19c0b65",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=1ac29e3b-726a-2a0d-f8a6-817473e1a833",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=1ac29e3b-726a-2a0d-f8a6-817473e1a833",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=1ac29e3b-726a-2a0d-f8a6-817473e1a833",
                ],
                'source'        => 'cint',
            ],
            'yltdq7aq9imn' => [
                'project_code'  => 'dynata_022_ng',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=a8939cec-ed06-4898-af09-d94051db21bc",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=615dc520-d60c-d37c-9950-d2979cfdd16d",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=615dc520-d60c-d37c-9950-d2979cfdd16d",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=615dc520-d60c-d37c-9950-d2979cfdd16d",
                ],
                'source'        => 'cint',
            ],
            'yltdq7aq9imx' => [
                'project_code'  => 'dynata_022_ng_sample_2',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=4aa39fea-53a7-4ae8-84ba-60d91132bbbc",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=faedb1f4-1aca-b393-7d8e-df72907f9a82",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=faedb1f4-1aca-b393-7d8e-df72907f9a82",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=faedb1f4-1aca-b393-7d8e-df72907f9a82",
                ],
                'source'        => 'cint',
            ],
            'otjzzhuejnjk' => [
                'project_code'  => 'dynata_022_ng_sample_2',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=fa60cc44-ed72-459b-b396-2bcb580601dc",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=64b228d6-df8c-6242-f9dc-9b2ff2ef65a8",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=64b228d6-df8c-6242-f9dc-9b2ff2ef65a8",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=64b228d6-df8c-6242-f9dc-9b2ff2ef65a8",
                ],
                'source'        => 'cint',
            ],
            'rqykz0eht5v6' => [
                'project_code'  => 'dynata_022_ng_sample_2',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=6a4f6260-e850-4449-8eda-beaebf923416",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=fef449af-c51c-a087-ac49-4d67b0ac54c8",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=fef449af-c51c-a087-ac49-4d67b0ac54c8",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=fef449af-c51c-a087-ac49-4d67b0ac54c8",
                ],
                'source'        => 'cint',
            ],
            'sclo9qbytbje' => [
                'project_code'  => [
                    'dynata_022_ng_sample_2',
                    'dynata_013_ng',
                ],
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://api-icontrol.datadiggers-mr.com/processfinish?status=1&dipe=mPgyj4YK&memberid={RID}",
                    RespondentStatus::QUOTA_FULL   => "https://api-icontrol.datadiggers-mr.com/processfinish?status=3&memberid={RID}",
                    RespondentStatus::DISQUALIFIED => "https://api-icontrol.datadiggers-mr.com/processfinish?status=4&memberid={RID}",
                    RespondentStatus::SCREEN_OUT   => "https://api-icontrol.datadiggers-mr.com/processfinish?status=2&memberid={RID}",
                ],
                'source'        => 'datadiggers-mr',
            ],
            'CD9DhWwz8jLW' => [
                'project_code'  => 'dynata_013_ng',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=5abd8ca3-72ce-40f8-8655-6dc5d05915bd",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=6779abdd-0b54-2afb-dc99-dd33d225f103",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=6779abdd-0b54-2afb-dc99-dd33d225f103",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=6779abdd-0b54-2afb-dc99-dd33d225f103",
                ],
                'source'        => 'cint',
            ],
            'pg42vdk3zz8b' => [
                'project_code'  => 'dynata_013_ng',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=58370630-f305-46d1-981a-9cd84a96af7f",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=d55b39b3-fec0-f65d-afaf-887dc79d7314",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=d55b39b3-fec0-f65d-afaf-887dc79d7314",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=d55b39b3-fec0-f65d-afaf-887dc79d7314",
                ],
                'source'        => 'cint',
            ],
            'pn4x7c5avdkj' => [
                'project_code'  => 'dynata_013_ng',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=78d72300-cc40-4f53-acd0-4f683b91ad44",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=504bfb86-5f46-cbdd-19f4-74e862138d71",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=504bfb86-5f46-cbdd-19f4-74e862138d71",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=504bfb86-5f46-cbdd-19f4-74e862138d71",
                ],
                'source'        => 'cint',
            ],
            'h3fwOLqvLNq2' => [
                'project_code'  => 'skim_001_et',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=5c37e7c1-fd70-4705-b21e-0c0b9a332ad1",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=8638c98f-df71-a93d-05a7-dca327f64b16",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=8638c98f-df71-a93d-05a7-dca327f64b16",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=8638c98f-df71-a93d-05a7-dca327f64b16",
                ],
                'source'        => 'cint',
            ],
            'h3fwOLqvLNq1' => [
                'project_code'  => 'skim_001_et',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://api-icontrol.datadiggers-mr.com/processfinish?status=1&dipe=ePr80jYV&memberid={RID}",
                    RespondentStatus::QUOTA_FULL   => "https://api-icontrol.datadiggers-mr.com/processfinish?status=3&memberid={RID}",
                    RespondentStatus::DISQUALIFIED => "https://api-icontrol.datadiggers-mr.com/processfinish?status=4&memberid={RID}",
                    RespondentStatus::SCREEN_OUT   => "https://api-icontrol.datadiggers-mr.com/processfinish?status=2&memberid={RID}",
                ],
                'source'        => 'datadiggers-mr',
            ],
            '7evwyqbi3pts' => [
                'project_code'  => 'skim_001_et',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=2310e677-8d1e-434a-8225-ef63630a2387",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=93660ca2-8526-5876-bba7-f8326396ebf0",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=93660ca2-8526-5876-bba7-f8326396ebf0",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=93660ca2-8526-5876-bba7-f8326396ebf0",
                ],
                'source'        => 'cint',
            ],
            'dvqwsb6aw5j8' => [
                'project_code'  => 'skim_001_et',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=f5b2cdaf-aea7-43e3-805c-0e2c3ee4fdf8",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=889934b2-cf49-69a7-ac67-0604e57d4382",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=889934b2-cf49-69a7-ac67-0604e57d4382",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=889934b2-cf49-69a7-ac67-0604e57d4382",
                ],
                'source'        => 'cint',
            ],
            'h3fwOLqvLNq3' => [
                'project_code'  => 'msi_008_ng',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=e9583a83-7dc8-4d82-997a-9bb961bf3361",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=49810fa3-e7d0-9dc6-ca3c-7ba638a5dce9",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=49810fa3-e7d0-9dc6-ca3c-7ba638a5dce9",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=49810fa3-e7d0-9dc6-ca3c-7ba638a5dce9",
                ],
                'source'        => 'cint',
            ],
            'lquz5lb1rlbu' => [
                'project_code'  => 'ids_001_zm',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=e23f7866-f684-449a-829b-afeebbd6c65b",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=a20c3f5b-de14-1066-5f82-e6e7f011f5f4",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=a20c3f5b-de14-1066-5f82-e6e7f011f5f4",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=a20c3f5b-de14-1066-5f82-e6e7f011f5f4",
                ],
                'source'        => 'cint',
            ],
            '1pj19xxok0hq' => [
                'project_code'  => 'msi_009_za',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=018f7b48-e352-4f3b-928d-fbcb74bf6b19",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=01980436-e46d-d627-d1d3-dae00d098cb5",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=01980436-e46d-d627-d1d3-dae00d098cb5",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=01980436-e46d-d627-d1d3-dae00d098cb5",
                ],
                'source'        => 'cint',
            ],
            'qbpyhdfylw8t' => [
                'project_code'  => 'bs_rg_002_ma',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=23481f23-af8f-47e9-82dc-cf234e9b1dd6",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=b71706e1-b22c-e2d1-e071-af26ed47a5e6",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=b71706e1-b22c-e2d1-e071-af26ed47a5e6",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=b71706e1-b22c-e2d1-e071-af26ed47a5e6",
                ],
                'source'        => 'cint',
            ],
            't4fpj0wohhtw' => [
                'project_code'  => 'dynata_025_ng',
                'end_redirects' => [
                    RespondentStatus::COMPLETED    => "https://s.cint.com/Survey/Complete?ProjectToken=b1ff1087-6349-4681-bb98-d30b0930d02c",
                    RespondentStatus::QUOTA_FULL   => "https://s.cint.com/Survey/QuotaFull?ProjectToken=cfd81e6f-2131-4787-82ad-2513c3a6a35c",
                    RespondentStatus::DISQUALIFIED => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=cfd81e6f-2131-4787-82ad-2513c3a6a35c",
                    RespondentStatus::SCREEN_OUT   => "https://s.cint.com/Survey/EarlyScreenOut?ProjectToken=cfd81e6f-2131-4787-82ad-2513c3a6a35c",
                ],
                'source'        => 'cint',
            ],
        ];

        foreach ($list as $projectId => $item) {
            SampleProvider::create([
                'project_id'    => $projectId,
                'project_codes' => (array)$item['project_code'],
                'source'        => $item['source'],
                'end_redirects' => $item['end_redirects'],
            ]);
        }
    }
}
