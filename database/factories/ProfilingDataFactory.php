<?php

/** @var Factory $factory */

use App\Constants\ProfilingQuestionType;
use App\ProfilingQuestion;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(ProfilingQuestion::class, function (Faker $faker) {
    return [
        'title'         => $faker->text(),
        'type'          => $faker->randomElement(config('profiling.supported_question_types')),
        'is_definitive' => true,
        'settings'      => [
            'required' => false,
        ],
    ];
});

$factory->afterCreating(ProfilingQuestion::class, function (ProfilingQuestion $profilingQuestion, Faker $faker) {

    switch ($profilingQuestion->type) {
        case ProfilingQuestionType::MULTIPLE_CHOICE:
            createMultipleChoiceAnswerParameters($profilingQuestion, $faker, $faker->numberBetween(3, 10));
            break;

        case ProfilingQuestionType::CHECKBOXES:
            createCheckboxesAnswerParameters($profilingQuestion, $faker, $faker->numberBetween(2, 10));
            break;

        case ProfilingQuestionType::SINGLE_TEXT_BOX:
            createSingleTextBoxAnswerParameters($profilingQuestion, $faker);
            break;

        case ProfilingQuestionType::DROPDOWN:
            createDropdownAnswerParameters($profilingQuestion, $faker, $faker->numberBetween(2, 10));
            break;
    }
});

function createMultipleChoiceAnswerParameters(ProfilingQuestion $profilingQuestion, Faker $faker, int $number = 1)
{
    $counter = 0;
    while ($counter < $number) {
        $profilingQuestion->profilingAnswerParameters()->create([
            'value'     => "value_${counter}",
            'label'     => $faker->text(),
            'sort'      => $faker->numberBetween(0, $number),
            //            'settings'  => [
            //            ],
        ]);

        $counter++;
    }
}

function createCheckboxesAnswerParameters(ProfilingQuestion $profilingQuestion, Faker $faker, int $number = 1)
{
    $counter = 0;
    while ($counter < $number) {
        $profilingQuestion->profilingAnswerParameters()->create([
            'value'     => "value_${counter}",
            'label'     => $faker->text(),
            'sort'      => $faker->numberBetween(0, $number),
            //            'settings'  => [
            //            ],
        ]);

        $counter++;
    }
}

function createSingleTextBoxAnswerParameters(ProfilingQuestion $profilingQuestion, Faker $faker)
{
    $profilingQuestion->profilingAnswerParameters()->create([
        'value'     => "value",
        //            'settings'  => [
        //            ],
    ]);
}

function createDropdownAnswerParameters(ProfilingQuestion $profilingQuestion, Faker $faker, int $number = 1)
{
    $counter = 0;
    while ($counter < $number) {
        $profilingQuestion->profilingAnswerParameters()->create([
            'value'     => "value_${counter}",
            'label'     => $faker->text(),
            'sort'      => $faker->numberBetween(0, $number),
            //            'settings'  => [
            //            ],
        ]);

        $counter++;
    }
}
