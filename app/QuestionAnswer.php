<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model {

    protected $fillable = [
        'person_id',
        'question_id',
        'answer',
    ];
}
