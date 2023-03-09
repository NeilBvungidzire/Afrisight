<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CintMemberQuestionAnswer extends Model {

    protected $fillable = [
        'person_id',
        'answers',
    ];

    protected $casts = [
        'answers' => 'array',
    ];
}
